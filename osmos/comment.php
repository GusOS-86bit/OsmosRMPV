<?php
// needs $COMMENT
if (!defined("ROOT")) { http_response_code(404); die; }

if (!isset($COMMENT)) {
    if (isset($comment)) $COMMENT = $comment;
    else if (isset($ROW_COMMENT)) $COMMENT = $ROW_COMMENT;
}

$comment_user_class = new User();
$COMMENT_USER = $comment_user_class->get_user($COMMENT['user_id'] ?? 0);

$comment_viewer = $_SESSION['osmos_userid'] ?? 0;
$comment_post_class = new Post();
if (!isset($image_class)) $image_class = new Image();

$comment_likes = (int)($COMMENT['likes'] ?? 0);
$comment_i_liked = ($comment_viewer && $comment_likes > 0) ? $comment_post_class->user_liked($COMMENT['post_id'], "post", $comment_viewer) : false;
$comment_replies = (int)($COMMENT['comments'] ?? 0);
$comment_image = $COMMENT['image'] ?? "";
?>
<div class="comment">
    <div>
        <img class="post-avatar" src="<?php echo ROOT . avatar_url($COMMENT_USER); ?>">
    </div>
    <div class="post-body">
        <div>
            <?php if (is_array($COMMENT_USER)): ?>
                <a class="post-author" href="<?php echo ROOT; ?>profile/<?php echo $COMMENT_USER['userid']; ?>"><?php echo htmlspecialchars($COMMENT_USER['first_name'] . " " . $COMMENT_USER['last_name']); ?></a>
            <?php else: ?>
                <span class="post-author">Unknown user</span>
            <?php endif; ?>
        </div>

        <?php if (trim($COMMENT['post'] ?? "") !== ""): ?>
            <div class="post-text">
                <?php echo nl2br($comment_post_class->get_tags(htmlspecialchars($COMMENT['post']))); ?>
            </div>
        <?php endif; ?>

        <?php if (is_video_file($comment_image)): ?>
            <div class="post-media">
                <video class="post-video" controls preload="metadata" src="<?php echo ROOT . $comment_image; ?>"></video>
            </div>
        <?php elseif (is_image_file($comment_image)): ?>
            <div class="post-media">
                <a href="<?php echo ROOT; ?>image_view/<?php echo $COMMENT['post_id']; ?>"><img class="post-image" src="<?php echo ROOT . $image_class->get_thumb_post($comment_image); ?>"></a>
            </div>
        <?php endif; ?>

        <div class="post-actions">
            <a onclick="ajax_data(event)" href="<?php echo csrf_url(ROOT . "like/post/" . $COMMENT['post_id']); ?>"><span id="like_label_<?php echo $COMMENT['post_id']; ?>"><?php echo $comment_i_liked ? "Unlike" : "Like"; ?></span> <span id="like_count_<?php echo $COMMENT['post_id']; ?>"><?php echo ($comment_likes > 0) ? "(" . $comment_likes . ")" : ""; ?></span></a>
            &middot;
            <a href="<?php echo ROOT; ?>single_post/<?php echo $COMMENT['post_id']; ?>">Reply<?php echo ($comment_replies > 0) ? " (" . $comment_replies . ")" : ""; ?></a>
            &middot;
            <span><?php echo Time::get_time($COMMENT['date'] ?? ""); ?></span>

            <?php if ($comment_viewer && $COMMENT['user_id'] == $comment_viewer): ?>
                &middot; <a class="post-manage" href="<?php echo ROOT; ?>edit/<?php echo $COMMENT['post_id']; ?>">Edit</a>
            <?php endif; ?>

            <?php if ($comment_post_class->can_delete_post($COMMENT, $comment_viewer)): ?>
                &middot; <a class="post-manage" href="<?php echo ROOT; ?>delete/<?php echo $COMMENT['post_id']; ?>">Delete</a>
            <?php endif; ?>
        </div>
    </div>
</div>
