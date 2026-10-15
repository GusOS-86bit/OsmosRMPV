<?php
include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);
$USER = $user_data;

$Post = new Post();
$ERROR = "";
$row = false;

if (isset($URL[1]) && is_numeric($URL[1])) {
    $row = $Post->get_one_post($URL[1]);
    if (!$row) {
        $ERROR = "No such post was found!";
    } else {
        if ($row['user_id'] != $user_data['userid']) {
            $ERROR = "Access denied! You cannot edit this post.";
        }
    }
} else {
    $ERROR = "No such post was found!";
}

if (isset($_SERVER['HTTP_REFERER']) && !strstr($_SERVER['HTTP_REFERER'], "/edit/")) {
    $_SESSION['return_to'] = safe_return_url($_SERVER['HTTP_REFERER']);
}

$SAVE_ERROR = "";
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (!$row || $row['user_id'] != $user_data['userid']) {
        die("Access Denied");
    }

    $SAVE_ERROR = $Post->edit_post($row['post_id'], $_POST, $_FILES);

    if ($SAVE_ERROR == "") {
        $return_to = safe_return_url($_SESSION['return_to'] ?? "", ROOT . "single_post/" . $row['post_id']);
        header("Location: " . $return_to);
        die;
    }
}

if (!isset($image_class)) $image_class = new Image();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Post | Osmos</title>
</head>
<body>
    <?php include("header.php"); ?>
    <div class="page">
        <div class="card">
            <?php if ($ERROR != ""): ?>
                <div class="empty-state"><?php echo $ERROR; ?></div>
            <?php else: ?>
                <h3 class="card-title">Edit Post</h3>

                <?php if ($SAVE_ERROR != ""): ?>
                    <div class="alert alert-error"><?php echo $SAVE_ERROR; ?></div>
                <?php endif; ?>

                <div class="composer" style="margin-bottom: 0;">
                    <form method="post" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <textarea class="tag-input" name="post" placeholder="What's on your mind?"><?php echo htmlspecialchars(($SAVE_ERROR != "" && isset($_POST['post']) && is_string($_POST['post'])) ? $_POST['post'] : $row['post']); ?></textarea>
                        <div class="tag-suggestions"></div>

                        <?php if (is_video_file($row['image'] ?? "")): ?>
                            <video controls preload="metadata" src="<?php echo ROOT . $row['image']; ?>" class="post-video" style="margin-top: 10px;"></video>
                        <?php elseif (is_image_file($row['image'] ?? "")): ?>
                            <div style="text-align: center; margin-top: 10px;">
                                <img src="<?php echo ROOT . $image_class->get_thumb_post($row['image']); ?>" style="width: 50%; border-radius: 4px;">
                            </div>
                        <?php endif; ?>

                        <div class="form-hint" style="margin-top: 10px;">Choose a new file to replace the picture/video (JPEG or MP4).</div>
                        <input type="file" name="file" accept="image/jpeg,video/mp4">
                        <input class="post-button" type="submit" value="Save">
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
