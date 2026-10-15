<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<!-- group_content_requests.php -->
<div class="card" style="margin-top: 20px; text-align: center;">
    <h3 class="card-title text-left">Join requests</h3>
    <?php
        $requests = $group_class->get_requests($gid);
        $user_class = new User();
        $shown = 0;

        if (is_array($requests)) {
            foreach ($requests as $request_row) {
                $FRIEND_ROW = $user_class->get_user($request_row['userid']);
                if ($FRIEND_ROW) {
                    $INVITER_ROW = false;
                    include("user_group_request.inc.php");
                    $shown++;
                }
            }
        }

        if ($shown == 0) {
            echo "<div class='empty-state'>No requests were found!</div>";
        }
    ?>
</div>
