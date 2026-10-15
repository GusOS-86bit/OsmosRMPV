<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<!-- group_content_invite.php -->
<div class="card" style="margin-top: 20px; text-align: center;">
    <h3 class="card-title text-left">Invite your friends</h3>
    <?php
        $invites = $group_class->get_invites($gid, $me);

        if (is_array($invites)) {
            foreach ($invites as $FRIEND_ROW) {
                include("user_invite.inc.php");
            }
        } else {
            echo "<div class='empty-state'>No friends to invite were found!<br><span class='muted' style='font-size: 12px;'>All your friends are already in this group, or you haven't added any friends yet.</span></div>";
        }
    ?>
</div>
