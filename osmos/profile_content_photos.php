<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title">Photos</h3>
    <?php
        $Post = new Post();
        $images = $Post->get_images($user_data['userid']);
        $shown = 0;

        echo "<div class='photo-grid'>";
        if (is_array($images)) {
            foreach ($images as $image_row) {
                // skip videos, they break the thumbnails
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
            echo "<div class='empty-state'>No images were found!</div>";
        }
    ?>
</div>
