<?php
// join request card, or my invitation if $INVITER_ROW is set
if (!defined("ROOT")) { http_response_code(404); die; }

$card_is_invitation = isset($INVITER_ROW) && is_array($INVITER_ROW);
$card_base = ROOT . "group_request_accept/" . $group_data['userid'] . "/" . $FRIEND_ROW['userid'];
?>
<div class="member-card">
    <a href="<?php echo ROOT; ?>profile/<?php echo $FRIEND_ROW['userid']; ?>" style="text-decoration: none;">
        <img src="<?php echo ROOT . avatar_url($FRIEND_ROW); ?>">
        <span class="member-card-name"><?php echo htmlspecialchars($FRIEND_ROW['first_name'] . " " . $FRIEND_ROW['last_name']); ?></span>
    </a>

    <?php if ($card_is_invitation): ?>
        <div style="margin-top: 5px; font-weight: normal; color: #777; font-size: 11px;">
            You were invited by<br>
            <span style="font-weight: bold;"><?php echo htmlspecialchars($INVITER_ROW['first_name'] . " " . $INVITER_ROW['last_name']); ?></span>
        </div>
    <?php else: ?>
        <div style="margin-top: 5px; color: #777; font-size: 11px;">wants to join</div>
    <?php endif; ?>

    <div class="member-card-buttons">
        <a class="btn btn-blue btn-small" href="<?php echo csrf_url($card_base . "/accept"); ?>">Accept</a>
        <a class="btn btn-danger btn-small" href="<?php echo csrf_url($card_base . "/decline"); ?>">Decline</a>
    </div>
</div>
