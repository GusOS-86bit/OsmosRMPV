<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<!-- group_content_photos.php -->
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title">Photos</h3>
    <?php
        $DB = new Database();
        $id = esc($gid);
        // no videos
        $sql = "SELECT image, post_id FROM posts WHERE has_image = 1 AND owner = '$id' AND image NOT LIKE '%.mp4' ORDER BY id DESC LIMIT 30";
        $images = $DB->read($sql);
        $shown = 0;

        echo "<div class='photo-grid'>";
        if (is_array($images)) {
            foreach ($images as $image_row) {
                if (is_image_file($image_row['image'])) {
                    $thumb = $image_class->get_thumb_post($image_row['image']);
                    echo "<a href='" . ROOT . "single_post/" . $image_row['post_id'] . "'>";
                    echo "<img src='" . ROOT . $thumb . "' />";
                    echo "</a>";
                    $shown++;
                }
            }
        }
        echo "</div>";

        if ($shown == 0) {
            echo "<div class='empty-state'>No images found in this group!</div>";
        }
    ?>
</div>
