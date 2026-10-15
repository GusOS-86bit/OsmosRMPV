<?php
include_once("classes/autoload.php");
$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);
$USER = $user_data;

$POST_ERROR = "";
if($_SERVER['REQUEST_METHOD'] == "POST") {
    $post = new Post();
    $result = $post->create_post($user_data['userid'], $_POST, $_FILES);
    if($result == "") {
        header("Location: " . ROOT . "home");
        die;
    } else {
        $POST_ERROR = $result;
    }
}

$limit = 10;
$page_number = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if($page_number < 1) $page_number = 1;
$offset = ($page_number - 1) * $limit;

// my + friends' posts, group posts stay in the groups
$DB = new Database();
$user_class = new User();
$my_userid = esc($user_data['userid']);
$friend_ids = $user_class->get_friend_ids($user_data['userid']);
$friend_list = implode("','", array_map('esc', $friend_ids));

// +1 to know if there's a next page
$fetch = $limit + 1;
if ($friend_list != "") {
    $sql = "select * from posts where parent = 0 and owner = 0 and (user_id = '$my_userid' || user_id in ('" . $friend_list . "')) order by id desc limit $fetch offset $offset";
} else {
    $sql = "select * from posts where parent = 0 and owner = 0 and user_id = '$my_userid' order by id desc limit $fetch offset $offset";
}
$posts = $DB->read($sql);

$has_next = false;
if (is_array($posts) && count($posts) > $limit) {
    $has_next = true;
    $posts = array_slice($posts, 0, $limit);
}

$image_class = new Image();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Timeline | Osmos</title>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="page">
        <div class="two-col">

            <!-- left side -->
            <div class="col-side">
                <div class="friends-bar" style="min-height: 0; padding-bottom: 20px;">
                    <img class="sidebar-pic" src="<?php echo ROOT . avatar_url($user_data); ?>">
                    <a class="sidebar-name" href="<?php echo ROOT; ?>profile/<?php echo $user_data['userid']; ?>">
                        <?php echo htmlspecialchars($user_data['first_name'] . " " . $user_data['last_name']); ?>
                    </a>
                    <div style="font-size: 14px; color: #333; margin-top: 4px;">@<?php echo htmlspecialchars($user_data['tag_name']); ?></div>
                </div>

                <?php $hashtags = popular_hashtags($user_data['userid']); ?>
                <div class="card" style="padding: 15px; margin-top: 20px;">
                    <h4 class="card-subtitle">Popular hashtags</h4>
                    <?php if (count($hashtags) > 0): ?>
                        <ul class="about-list">
                            <?php foreach ($hashtags as $tag => $count): ?>
                                <li>
                                    <a class="tag-link" href="<?php echo ROOT; ?>tags?tag=<?php echo urlencode($tag); ?>">#<?php echo htmlspecialchars($tag); ?></a>
                                    <span class="muted" style="float: right;"><?php echo $count; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="muted" style="font-size: 13px;">No hashtags yet. Write #php or #mysql in a post.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- right side -->
            <div class="col-main">
                <?php if ($POST_ERROR != ""): ?>
                    <div class="alert alert-error"><?php echo $POST_ERROR; ?></div>
                <?php endif; ?>

                <div class="composer">
                    <form method="post" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <textarea class="tag-input" name="post" placeholder="What's on your mind? Use @username to tag someone..."><?php echo ($POST_ERROR != "" && isset($_POST['post']) && is_string($_POST['post'])) ? htmlspecialchars($_POST['post']) : ""; ?></textarea>
                        <div class="tag-suggestions"></div>

                        <input type="file" name="file" accept="image/jpeg,video/mp4">
                        <input class="post-button" type="submit" value="Post">
                    </form>
                </div>

                <div class="posts-bar">
                    <?php
                        if($posts) {
                            foreach ($posts as $ROW) {
                                $ROW_USER = $user_class->get_user($ROW['user_id']);
                                include("post.php");
                            }
                        } else {
                            echo "<div class='empty-state'>" . ($page_number > 1 ? "No more posts." : "No posts found! Write your first post or add some friends.") . "</div>";
                        }
                    ?>
                </div>

                <?php $paging_links = pagination_link(); ?>
                <div class="page-nav">
                    <?php if ($page_number > 1): ?>
                        <a href="<?php echo $paging_links['prev']; ?>" class="post-button" style="float: left;">Previous page</a>
                    <?php endif; ?>
                    <?php if ($has_next): ?>
                        <a href="<?php echo $paging_links['next']; ?>" class="post-button" style="float: right; text-decoration: none;">Next page</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
