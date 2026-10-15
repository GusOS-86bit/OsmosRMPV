<?php
// needs $ROW and $ROW_USER
if (!defined("ROOT")) { http_response_code(404); die; }

$post_viewer = $_SESSION['osmos_userid'] ?? 0;
$post_class = new Post();
if (!isset($image_class)) $image_class = new Image();

$post_avatar = avatar_url($ROW_USER);
$post_author_name = is_array($ROW_USER) ? htmlspecialchars(trim($ROW_USER['first_name'] . " " . $ROW_USER['last_name'])) : "Unknown user";

$post_note = "";
if (!empty($ROW['is_profile_image'])) {
    $post_note = "updated " . pronoun($ROW_USER) . " profile image";
}
if (!empty($ROW['is_cover_image'])) {
    $post_note = ($ROW['owner'] > 0) ? "updated the group cover image" : "updated " . pronoun($ROW_USER) . " cover image";
}

// "in <group>" label, not needed on the group's own page
$post_group = false;
if ($ROW['owner'] > 0 && (!isset($CURRENT_GROUP_ID) || $CURRENT_GROUP_ID != $ROW['owner'])) {
    $post_group_class = new Group();
    $post_group = $post_group_class->get_group($ROW['owner']);
}

$post_likes = (int)$ROW['likes'];
$post_i_liked = ($post_viewer && $post_likes > 0) ? $post_class->user_liked($ROW['post_id'], "post", $post_viewer) : false;
$post_likes_info = likes_info_text($post_likes, $post_i_liked);
$post_comment_count = (int)$ROW['comments'];

$post_image_path = $ROW['image'] ?? "";
?>
<div class="post">
    <div>
        <?php if (is_array($ROW_USER)): ?>
            <a href="<?php echo ROOT; ?>profile/<?php echo $ROW_USER['userid']; ?>"><img class="post-avatar" src="<?php echo ROOT . $post_avatar; ?>"></a>
        <?php else: ?>
            <img class="post-avatar" src="<?php echo ROOT . $post_avatar; ?>">
        <?php endif; ?>
    </div>
    <div class="post-body">
        <div>
            <?php if (is_array($ROW_USER)): ?>
                <a class="post-author" href="<?php echo ROOT; ?>profile/<?php echo $ROW_USER['userid']; ?>"><?php echo $post_author_name; ?></a>
            <?php else: ?>
                <span class="post-author"><?php echo $post_author_name; ?></span>
            <?php endif; ?>

            <?php if ($post_note != ""): ?>
                <span class="post-note"><?php echo $post_note; ?></span>
            <?php endif; ?>

            <?php if (is_array($post_group)): ?>
                <span class="post-group">&#9656; in <a href="<?php echo ROOT; ?>group/<?php echo $post_group['userid']; ?>"><?php echo htmlspecialchars($post_group['first_name']); ?></a></span>
            <?php endif; ?>
        </div>

        <?php if (isset($ROW['post']) && trim($ROW['post']) !== ""): ?>
            <div class="post-text">
                <?php echo nl2br($post_class->get_tags(htmlspecialchars($ROW['post']))); ?>
            </div>
        <?php endif; ?>

        <?php if (is_video_file($post_image_path)): ?>
            <div class="post-media">
                <video class="post-video" controls preload="metadata" src="<?php echo ROOT . $post_image_path; ?>"></video>
            </div>
        <?php elseif (is_image_file($post_image_path)): ?>
            <div class="post-media">
                <a href="<?php echo ROOT; ?>single_post/<?php echo $ROW['post_id']; ?>"><img class="post-image" src="<?php echo ROOT . $image_class->get_thumb_post($post_image_path); ?>"></a>
                <a class="post-media-link" href="<?php echo ROOT; ?>image_view/<?php echo $ROW['post_id']; ?>">View Full Image</a>
            </div>
        <?php endif; ?>

        <div class="post-actions">
            <a onclick="ajax_data(event)" href="<?php echo csrf_url(ROOT . "like/post/" . $ROW['post_id']); ?>"><span id="like_label_<?php echo $ROW['post_id']; ?>"><?php echo $post_i_liked ? "Unlike" : "Like"; ?></span> <span id="like_count_<?php echo $ROW['post_id']; ?>"><?php echo ($post_likes > 0) ? "(" . $post_likes . ")" : ""; ?></span></a>
            &middot;
            <a href="<?php echo ROOT; ?>single_post/<?php echo $ROW['post_id']; ?>">Comment<?php echo ($post_comment_count > 0) ? " (" . $post_comment_count . ")" : ""; ?></a>
            &middot;
            <span><?php echo Time::get_time($ROW['date'] ?? ""); ?></span>

            <?php if ($post_viewer && $ROW['user_id'] == $post_viewer): ?>
                &middot; <a class="post-manage" href="<?php echo ROOT; ?>edit/<?php echo $ROW['post_id']; ?>">Edit</a>
            <?php endif; ?>

            <?php if ($post_class->can_delete_post($ROW, $post_viewer)): ?>
                &middot; <a class="post-manage" href="<?php echo ROOT; ?>delete/<?php echo $ROW['post_id']; ?>">Delete</a>
            <?php endif; ?>

            <a id="info_row_<?php echo $ROW['post_id']; ?>" class="post-info" href="<?php echo ROOT; ?>likes/post/<?php echo $ROW['post_id']; ?>" style="<?php echo ($post_likes_info == "") ? "display: none;" : ""; ?>"><?php echo $post_likes_info; ?></a>
        </div>
    </div>
</div>
