<?php

include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);
$me = is_array($user_data) ? $user_data['userid'] : 0;

$Post = new Post();

$ROW = false;

if (isset($URL[1]) && is_numeric($URL[1])) {
    $ROW = $Post->get_one_post($URL[1]);
}

$can_view = is_array($ROW) && $Post->can_view_post($ROW, $me);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Image View | Osmos</title>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="page">
        <div class="card" style="text-align: center;">
            <?php
                if ($ROW && $can_view) {
                    echo "<div style='text-align: left; margin-bottom: 15px;'><a href='" . ROOT . "single_post/" . $ROW['post_id'] . "' style='text-decoration: none; font-weight: bold;'>&larr; Back to Post</a></div>";

                    if (is_video_file($ROW['image'])) {
                        echo "<video controls src='" . ROOT . $ROW['image'] . "' class='post-video'></video>";
                    } elseif (is_image_file($ROW['image'])) {
                        echo "<img src='" . ROOT . $ROW['image'] . "' style='max-width: 100%; border-radius: 4px;' />";

                        if ($me && $ROW['user_id'] == $me && is_jpeg_file($ROW['image'])) {
                            echo "<div style='margin-top: 15px;'>";
                            echo "<a class='btn btn-primary' href='" . ROOT . "change_profile_image/profile/post/" . $ROW['post_id'] . "'>Set as Profile Picture</a> ";
                            echo "<a class='btn btn-light' href='" . ROOT . "change_profile_image/cover/post/" . $ROW['post_id'] . "'>Set as Cover Image</a>";
                            echo "</div>";
                        }
                    } else {
                        echo "<div class='empty-state'>Image not found!</div>";
                    }
                } elseif ($ROW) {
                    echo "<div class='empty-state'>Sorry, you do not have access to this content.<br>It is shared only with friends or with the members of a private group.</div>";
                } else {
                    echo "<div class='empty-state'>No image found!</div>";
                }
            ?>
        </div>
    </div>
</body>
</html>
