<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<!-- group_content_denied.php -->
<div class="card empty-state" style="margin-top: 20px; padding: 40px;">
    <?php if ($denied_reason == "private"): ?>

        <h2 style="color: #405d9b; font-size: 24px; margin: 0 0 10px 0;">Private Group</h2>
        <div style="font-size: 16px; margin-bottom: 20px; color: #444;">
            Join the group to see what the members are up to.
        </div>
        <?php if (!$me): ?>
            <a class="btn btn-primary" href="<?php echo ROOT; ?>login">Log in to join</a>
        <?php elseif ($is_invited): ?>
            <a class="btn btn-primary" href="<?php echo csrf_url(ROOT . "group_request_accept/" . $gid . "/" . $me . "/accept"); ?>">Accept Invite</a>
        <?php elseif ($has_request): ?>
            <span class="btn btn-muted">Request Sent</span>
        <?php else: ?>
            <a class="btn btn-primary" href="<?php echo csrf_url(ROOT . "join/" . $gid); ?>">Request to Join</a>
        <?php endif; ?>

    <?php elseif ($denied_reason == "invite"): ?>

        <h3 style="color: #405d9b; margin-top: 0;">Only members can invite people</h3>
        <div style="color: #555;">Join this group first, then you can invite your friends.</div>

    <?php elseif ($denied_reason == "login"): ?>

        <h3 style="color: #405d9b; margin-top: 0;">Please log in</h3>
        <a class="btn btn-primary" href="<?php echo ROOT; ?>login">Log in</a>

    <?php else: ?>

        <h3 style="color: #405d9b; margin-top: 0;">You don't have access to see this content!</h3>

    <?php endif; ?>
</div>
