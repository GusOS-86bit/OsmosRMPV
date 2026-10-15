<?php
include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);

$tag = (isset($_GET['tag']) && is_string($_GET['tag'])) ? trim($_GET['tag']) : "";
$tag = ltrim($tag, "#@");
if (!preg_match("/^[a-zA-Z0-9_]+$/", $tag)) {
    $tag = "";
}

$posts = false;

if ($tag != "") {
    $DB = new Database();

    // whole word matches only
    $pattern = esc("(^|[^a-zA-Z0-9_])[#@]" . $tag . "([^a-zA-Z0-9_]|$)");
    $found = $DB->read("SELECT * FROM posts WHERE post REGEXP '$pattern' ORDER BY id DESC LIMIT 200");

    if (is_array($found)) {
        $post_class = new Post();
        $posts = [];
        foreach ($found as $row) {
            if ($post_class->can_view_post($row, $user_data['userid'])) {
                $posts[] = $row;
            }
            if (count($posts) >= 30) break;
        }
        if (count($posts) == 0) $posts = false;
    }
}

$user = new User();
$image_class = new Image();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tag: #<?php echo htmlspecialchars($tag); ?> | Osmos</title>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="page">
        <div class="card" style="padding: 15px 20px;">
            <h3 style="margin: 0; color: #333;">Posts tagged with: #<?php echo htmlspecialchars($tag); ?></h3>
        </div>

        <?php if (is_array($posts)): ?>
            <div class="posts-bar">
                <?php
                foreach ($posts as $ROW) {
                    $ROW_USER = $user->get_user($ROW['user_id']);
                    include("post.php");
                }
                ?>
            </div>
        <?php else: ?>
            <div class="card empty-state">No posts found for this tag.</div>
        <?php endif; ?>
    </div>

</body>
</html>
