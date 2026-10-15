<?php
// one row in the chat list, needs $MESSAGE, $ROW_USER and $other_id
if (!defined("ROOT")) { http_response_code(404); die; }

$thread_unread = check_unread_from($other_id) > 0;
$thread_name = is_array($ROW_USER) ? htmlspecialchars($ROW_USER['first_name'] . " " . $ROW_USER['last_name']) : "Deleted user";
$thread_preview = trim($MESSAGE['message'] ?? "");
?>
<div class="message-thread<?php echo $thread_unread ? " unread" : ""; ?>">
    <div style="width: 100%;">
        <img class="message-avatar" src="<?php echo ROOT . avatar_url($ROW_USER); ?>" style="float: left; margin-right: 10px;">

        <div>
            <a class="message-name" href="<?php echo ROOT; ?>profile/<?php echo $other_id; ?>"><?php echo $thread_name; ?></a>
            <?php if ($MESSAGE['sender'] == ($_SESSION['osmos_userid'] ?? 0)): ?>
                <span class="muted" style="font-size: 11px;">&middot; you wrote</span>
            <?php endif; ?>
        </div>

        <div class="message-text">
            <?php echo ($thread_preview !== "") ? htmlspecialchars(mb_strimwidth($thread_preview, 0, 140, "...")) : ""; ?>
        </div>

        <?php if (!empty($MESSAGE['file']) && is_file($MESSAGE['file'])): ?>
            <div style="margin-top: 6px;">
                <span style="color: #999; font-style: italic;">[Image Attached]</span>
            </div>
        <?php endif; ?>

        <div class="message-meta"><?php echo Time::get_time($MESSAGE['date']); ?></div>
    </div>

    <a class="thread-arrow" href="<?php echo ROOT; ?>messages/read/<?php echo $other_id; ?>" title="Open conversation">
        <svg fill="white" width="24" height="24" viewBox="0 0 24 24">
            <path d="M5 3l3.057-3 11.943 12-11.943 12-3.057-3 9-9z"/>
        </svg>
    </a>
</div>
