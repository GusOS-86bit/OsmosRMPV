<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<!-- profile_content_requests.php -->
<div class="card" style="margin-top: 20px; text-align: center;">
    <h3 class="card-title text-left">Friend requests</h3>
    <?php
        $received = $user_class->get_received_requests($me);
        $shown = 0;

        if (is_array($received)) {
            foreach ($received as $request_row) {
                $FRIEND_ROW = $user_class->get_user($request_row['sender']);
                if (!is_array($FRIEND_ROW)) continue;
                $shown++;
                ?>
                <div class="member-card">
                    <a href="<?php echo ROOT; ?>profile/<?php echo $FRIEND_ROW['userid']; ?>" style="text-decoration: none;">
                        <img src="<?php echo ROOT . avatar_url($FRIEND_ROW); ?>">
                        <span class="member-card-name"><?php echo htmlspecialchars($FRIEND_ROW['first_name'] . " " . $FRIEND_ROW['last_name']); ?></span>
                    </a>
                    <div class="muted" style="font-size: 11px; margin-top: 4px;"><?php echo Time::get_time($request_row['date']); ?></div>
                    <div class="member-card-buttons">
                        <a class="btn btn-blue btn-small" href="<?php echo csrf_url(ROOT . "friend/accept/" . $FRIEND_ROW['userid']); ?>">Accept</a>
                        <a class="btn btn-danger btn-small" href="<?php echo csrf_url(ROOT . "friend/decline/" . $FRIEND_ROW['userid']); ?>">Decline</a>
                    </div>
                </div>
                <?php
            }
        }

        if ($shown == 0) {
            echo "<div class='empty-state'>You have no new friend requests.</div>";
        }

        $sent = $user_class->get_sent_requests($me);
        if (is_array($sent)) {
            echo "<h4 class='card-subtitle text-left' style='margin-top: 25px;'>Requests you sent</h4>";
            foreach ($sent as $request_row) {
                $FRIEND_ROW = $user_class->get_user($request_row['receiver']);
                if (!is_array($FRIEND_ROW)) continue;
                ?>
                <div class="member-card">
                    <a href="<?php echo ROOT; ?>profile/<?php echo $FRIEND_ROW['userid']; ?>" style="text-decoration: none;">
                        <img src="<?php echo ROOT . avatar_url($FRIEND_ROW); ?>">
                        <span class="member-card-name"><?php echo htmlspecialchars($FRIEND_ROW['first_name'] . " " . $FRIEND_ROW['last_name']); ?></span>
                    </a>
                    <div class="member-card-buttons">
                        <a class="btn btn-light btn-small" href="<?php echo csrf_url(ROOT . "friend/cancel/" . $FRIEND_ROW['userid']); ?>">Cancel request</a>
                    </div>
                </div>
                <?php
            }
        }
    ?>
</div>
