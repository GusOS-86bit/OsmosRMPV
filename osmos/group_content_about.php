<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<?php $group_owner_row = $user->get_user($group_data['owner']); ?>
<!-- group_content_about.php -->
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title">About this group</h3>

    <div style="font-size: 15px; line-height: 1.5; color: #333; word-wrap: break-word; margin-bottom: 20px;">
        <?php if (trim($group_data['about'] ?? "") !== ""): ?>
            <?php echo nl2br(htmlspecialchars($group_data['about'])); ?>
        <?php else: ?>
            <span class="muted">No description yet.</span>
            <?php if ($is_admin): ?>
                <a href="<?php echo ROOT; ?>group/<?php echo $gid; ?>/settings">Add one in Settings</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <ul class="about-list">
        <li>
            <?php if ($is_private): ?>
                <strong>Private</strong> &mdash; only members can see who's in the group and what they post.
            <?php else: ?>
                <strong>Public</strong> &mdash; anyone can see who's in the group and what they post.
            <?php endif; ?>
        </li>
        <li><strong><?php echo $member_count; ?></strong> member<?php echo $member_count == 1 ? "" : "s"; ?></li>
        <li>Created <?php echo date("F jS, Y", strtotime($group_data['date'])); ?></li>
        <?php if (is_array($group_owner_row)): ?>
            <li>Owner: <a href="<?php echo ROOT; ?>profile/<?php echo $group_owner_row['userid']; ?>" style="text-decoration: none; font-weight: bold;"><?php echo htmlspecialchars($group_owner_row['first_name'] . " " . $group_owner_row['last_name']); ?></a></li>
        <?php endif; ?>
    </ul>
</div>
