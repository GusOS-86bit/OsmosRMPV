<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<div class="card" style="margin-top: 20px; text-align: center;">
    <h3 class="card-title text-left">Friends</h3>
    <?php
        if (is_array($friends)) {
            foreach ($friends as $FRIEND_ROW) {
                include("user.php");
            }
        } else {
            echo "<div class='empty-state'>" . ($is_own_profile ? "You have no friends yet. Find people with the search box and send them a friend request." : "No friends were found!") . "</div>";
        }
    ?>
</div>
