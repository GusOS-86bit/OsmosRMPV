<?php
if (!defined("ROOT")) { http_response_code(404); die; }

$member_role = $member_row['role'] ?? "member";
$member_is_owner = ($FRIEND_ROW['userid'] == $group_data['owner']);
?>
<div class="member-card">
    <a href="<?php echo ROOT; ?>profile/<?php echo $FRIEND_ROW['userid']; ?>" style="text-decoration: none;">
        <img src="<?php echo ROOT . avatar_url($FRIEND_ROW); ?>">
        <span class="member-card-name"><?php echo htmlspecialchars($FRIEND_ROW['first_name'] . " " . $FRIEND_ROW['last_name']); ?></span>
    </a>

    <div>
        <span class="badge badge-<?php echo htmlspecialchars($member_role); ?>"><?php echo htmlspecialchars(ucfirst($member_role)); ?></span>
    </div>

    <?php if ($is_admin && !$member_is_owner && $FRIEND_ROW['userid'] != $me): ?>
        <div class="member-card-buttons">
            <!-- these only open a confirm screen -->
            <a class="btn btn-blue btn-small" href="<?php echo ROOT; ?>group/<?php echo $group_data['userid']; ?>/members?edit_access=<?php echo $FRIEND_ROW['userid']; ?>">Edit Access</a>
            <a class="btn btn-danger btn-small" href="<?php echo ROOT; ?>group/<?php echo $group_data['userid']; ?>/members?remove=<?php echo $FRIEND_ROW['userid']; ?>">Remove</a>
        </div>
    <?php endif; ?>
</div>
