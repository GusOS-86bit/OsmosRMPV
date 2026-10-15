<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<!-- profile_content_private.php -->
<div class="card empty-state" style="margin-top: 20px; padding: 40px;">
    <h3 style="color: #405d9b; margin-top: 0;">This profile is visible only to friends</h3>
    <p><?php echo htmlspecialchars($user_data['first_name']); ?> shares posts, photos and friends only with friends.</p>
    <?php if ($friend_status == "none" && empty($user_data['blocked'])): ?>
        <a class="btn btn-primary" href="<?php echo csrf_url(ROOT . "friend/add/" . $user_data['userid']); ?>">Add friend</a>
    <?php elseif ($friend_status == "sent"): ?>
        <span class="btn btn-muted">Request sent</span>
    <?php elseif ($friend_status == "received"): ?>
        <a class="btn btn-primary" href="<?php echo csrf_url(ROOT . "friend/accept/" . $user_data['userid']); ?>">Accept friend request</a>
    <?php endif; ?>
</div>
