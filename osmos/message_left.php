<?php
// their message, needs $MESSAGE and $ROW_USER
if (!defined("ROOT")) { http_response_code(404); die; }
if (!isset($image_class)) $image_class = new Image();
?>
<div class="message-left">
    <div style="width: 100%;">
        <img class="message-avatar" src="<?php echo ROOT . avatar_url($ROW_USER); ?>">

        <div>
            <?php if (is_array($ROW_USER)): ?>
                <a class="message-name" href="<?php echo ROOT; ?>profile/<?php echo $MESSAGE['sender']; ?>"><?php echo htmlspecialchars($ROW_USER['first_name'] . " " . $ROW_USER['last_name']); ?></a>
            <?php else: ?>
                <span class="message-name">Deleted user</span>
            <?php endif; ?>
        </div>

        <div class="message-text">
            <?php echo nl2br(htmlspecialchars($MESSAGE['message'] ?? "")); ?>
        </div>

        <?php if (is_image_file($MESSAGE['file'] ?? "")): ?>
            <div class="message-image">
                <a href="<?php echo ROOT . $MESSAGE['file']; ?>" target="_blank">
                    <img src="<?php echo ROOT . $image_class->get_thumb_post($MESSAGE['file']); ?>">
                </a>
            </div>
        <?php endif; ?>

        <div class="message-meta">
            <a href="<?php echo ROOT; ?>delete/msg/<?php echo $MESSAGE['id']; ?>" style="float: left;" title="Delete message">
                <svg fill="orange" width="14" height="14" viewBox="0 0 24 24"><path d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z"/></svg>
            </a>
            <span style="float: right;"><?php echo Time::get_time($MESSAGE['date']); ?></span>
        </div>
    </div>
</div>
