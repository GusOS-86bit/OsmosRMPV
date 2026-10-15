<?php
// likes and @tag suggestions
// csrf is checked in autoload
include_once("classes/autoload.php");

$me = $_SESSION['osmos_userid'] ?? 0;

if (isset($_POST['link'])) {

    header("Content-Type: application/json");

    if (empty($me)) {
        echo json_encode(["action" => "login"]);
        die;
    }

    // the link looks like ROOT/like/post/123?token=...
    $data_link = is_string($_POST['link']) ? $_POST['link'] : "";
    $path = parse_url($data_link, PHP_URL_PATH);
    $parts = split_url_from_string((string)$path);

    $type = null;
    $id = null;
    $key = array_search('like', $parts);
    if ($key !== false) {
        $type = $parts[$key + 1] ?? null;
        $id = $parts[$key + 2] ?? null;
    }

    if (in_array($type, ["post", "comment"], true) && is_numeric($id)) {
        $post = new Post();
        $row = $post->get_one_post($id);

        if (!$row || !$post->can_view_post($row, $me)) {
            echo json_encode(["action" => "error", "message" => "You can't like this post."]);
            die;
        }

        // like_post() sends the notification
        $post->like_post($id, $type, $me);

        $likes = $post->get_likes($id, $type);
        $likes_count = is_array($likes) ? count($likes) : 0;
        $liked = $post->user_liked($id, $type, $me);

        $obj = (object)[];
        $obj->likes = $likes_count;
        $obj->liked = $liked;
        $obj->info = likes_info_text($likes_count, $liked);
        $obj->action = "like_post";
        $obj->id = $id;

        echo json_encode($obj);
    }
    die;
}

if (isset($_POST['tag_search'])) {

    if (empty($me) || !is_string($_POST['tag_search'])) {
        die;
    }

    $query_tag = trim($_POST['tag_search']);
    if (!preg_match("/^[a-zA-Z0-9_]*$/", $query_tag)) {
        die;
    }

    $DB = new Database();
    // escape % and _ for LIKE
    $like = esc(addcslashes($query_tag, "%_"));

    // people only, best match first
    $sql = "select tag_name, first_name, last_name from users
            where (type is null or type = '' or type = 'profile')
            and blocked = 0
            and tag_name != ''
            and (tag_name like '%$like%' or first_name like '$like%' or last_name like '$like%')
            order by (tag_name like '$like%') desc, tag_name asc
            limit 6";
    $result = $DB->read($sql);

    if (is_array($result)) {
        foreach ($result as $row) {
            $tag = htmlspecialchars($row['tag_name'], ENT_QUOTES);
            $name = htmlspecialchars($row['first_name'] . " " . $row['last_name']);
            echo "<div class='tag-suggestion' data-tag='" . $tag . "'>";
            echo "<strong>@" . $tag . "</strong> <span class='tag-suggestion-name'>(" . $name . ")</span>";
            echo "</div>";
        }
    }
    die;
}
