<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<div class="two-col">
    <div class="col-side">
        <div class="friends-bar">
            <div class="friends-bar-title">Friends</div>
            <?php
                if (is_array($friends)) {
                    foreach ($friends as $FRIEND_ROW) {
                        include("user.php");
                    }
                } else {
                    echo "<div style='font-size: 13px; color: #555;'>No friends yet.</div>";
                }
            ?>
        </div>
    </div>

    <div class="col-main">

        <?php if ($is_own_profile): ?>
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
        <?php endif; ?>

        <div class="posts-bar">
            <?php
            if ($posts) {
                foreach ($posts as $ROW) {
                    $ROW_USER = $user_class->get_user($ROW['user_id']);
                    include("post.php");
                }
            } else {
                echo "<div class='empty-state'>No posts yet.</div>";
            }
            ?>
        </div>
    </div>
</div>
