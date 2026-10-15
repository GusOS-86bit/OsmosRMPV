<?php
if (!defined("ROOT")) { http_response_code(404); die; }

$card_already_invited = $group_class->is_invited($group_data['userid'], $FRIEND_ROW['userid']);
?>
<div class="member-card">
    <a href="<?php echo ROOT; ?>profile/<?php echo $FRIEND_ROW['userid']; ?>" style="text-decoration: none;">
        <img src="<?php echo ROOT . avatar_url($FRIEND_ROW); ?>">
        <span class="member-card-name"><?php echo htmlspecialchars($FRIEND_ROW['first_name'] . " " . $FRIEND_ROW['last_name']); ?></span>
    </a>

    <div class="member-card-buttons">
        <?php if ($card_already_invited): ?>
            <span class="btn btn-muted btn-small">Invited</span>
        <?php else: ?>
            <a class="btn btn-blue btn-small" href="<?php echo csrf_url(ROOT . "invite/" . $group_data['userid'] . "/" . $FRIEND_ROW['userid']); ?>">Invite</a>
        <?php endif; ?>
    </div>
</div>
