<?php
// post preview for the delete page, needs $ROW and $ROW_USER
if (!defined("ROOT")) { http_response_code(404); die; }
if (!isset($image_class)) $image_class = new Image();
?>
<div class="post" style="border: 1px solid #ddd; border-radius: 6px;">
    <div>
        <img class="post-avatar" src="<?php echo ROOT . avatar_url($ROW_USER); ?>">
    </div>
    <div class="post-body">
        <div class="post-author">
            <?php echo is_array($ROW_USER) ? htmlspecialchars($ROW_USER['first_name'] . " " . $ROW_USER['last_name']) : "Unknown user"; ?>
            <?php
            if (!empty($ROW['is_profile_image'])) {
                echo " <span class='post-note'>updated " . pronoun($ROW_USER) . " profile image</span>";
            }
            if (!empty($ROW['is_cover_image'])) {
                echo " <span class='post-note'>" . ($ROW['owner'] > 0 ? "updated the group cover image" : "updated " . pronoun($ROW_USER) . " cover image") . "</span>";
            }
            ?>
        </div>
        <div class="post-text">
            <?php echo nl2br(htmlspecialchars($ROW['post'] ?? "")); ?>
        </div>
        <?php
        if (is_video_file($ROW['image'] ?? "")) {
            echo "<div class='post-media'><video class='post-video' controls preload='metadata' src='" . ROOT . $ROW['image'] . "'></video></div>";
        } elseif (is_image_file($ROW['image'] ?? "")) {
            echo "<div class='post-media'><img src='" . ROOT . $image_class->get_thumb_post($ROW['image']) . "' style='width: 50%; border-radius: 4px;' /></div>";
        }
        ?>
    </div>
</div>
