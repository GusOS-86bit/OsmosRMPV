<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<!-- group_content_invited.php -->
<div class="card" style="margin-top: 20px; text-align: center;">
    <h3 class="card-title text-left">Invitations</h3>
    <?php
        $invites = $group_class->get_invited($gid, $me);
        $user_class = new User();

        if (is_array($invites) && !$is_member) {
            foreach ($invites as $invite_row) {
                $FRIEND_ROW = $user_class->get_user($invite_row['userid']);
                $INVITER_ROW = $user_class->get_user($invite_row['inviter']);

                if ($FRIEND_ROW) {
                    // same card as join requests
                    include("user_group_request.inc.php");
                }
            }
        } else {
            echo "<div class='empty-state'>No invitations were found!</div>";
        }
    ?>
</div>
