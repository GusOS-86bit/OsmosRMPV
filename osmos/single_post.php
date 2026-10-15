<?php
// a post with its comments, replies use this page too
include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);
$USER = $user_data;
$me = is_array($user_data) ? $user_data['userid'] : 0;

$Post = new Post();
$user_class = new User();
$group_class = new Group();
$image_class = new Image();

$ROW = false;
if (isset($URL[1]) && is_numeric($URL[1])) {
    $ROW = $Post->get_one_post($URL[1]);
}

// from a notification: single_post/POSTID/NOTIFICATIONID
if ($me && isset($URL[2]) && is_numeric($URL[2])) {
    notification_seen($URL[2]);
}

// group posts: only members can comment
$is_group_post = false;
$group_data = false;
if (is_array($ROW) && $ROW['owner'] > 0) {
    $group_data = $group_class->get_group($ROW['owner']);
    $is_group_post = is_array($group_data);
}

$show_content = is_array($ROW) && $Post->can_view_post($ROW, $me);

$can_comment = $show_content && $me;
if ($can_comment && $is_group_post && !group_access($me, $group_data, 'member')) {
    $can_comment = false;
}

$COMMENT_ERROR = "";
if ($_SERVER['REQUEST_METHOD'] == "POST" && is_array($ROW)) {
    if (!$can_comment) {
        set_flash("You can't comment on this post.", "error");
        redirect(ROOT . "single_post/" . $ROW['post_id']);
    }

    $data = $_POST;
    $data['parent'] = $ROW['post_id']; // don't trust parent from the form
    unset($data['is_profile_image'], $data['is_cover_image']);

    // group comments get the group as owner
    $result = $Post->create_post($me, $data, $_FILES, $ROW['owner']);
    if ($result == "") {
        redirect(ROOT . "single_post/" . $ROW['post_id']);
    }
    $COMMENT_ERROR = $result;
}

$ROW_USER = is_array($ROW) ? $user_class->get_user($ROW['user_id']) : false;
$comments = $show_content ? $Post->get_comments($ROW['post_id']) : false;

$parent_row = (is_array($ROW) && $ROW['parent'] > 0) ? $Post->get_one_post($ROW['parent']) : false;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Post | Osmos</title>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="page">

        <?php if (!is_array($ROW)): ?>

            <div class="card empty-state">
                <h3 style="color: #405d9b;">No such post was found!</h3>
                <p>It may have been deleted.</p>
                <a class="btn btn-primary" href="<?php echo ROOT; ?>home">Back to Timeline</a>
            </div>

        <?php elseif (!$show_content): ?>

            <div class="card empty-state">
                <h3 style="color: #405d9b;">Sorry, you do not have access to this content.</h3>
                <?php if ($is_group_post): ?>
                    <p>This post belongs to a private group. You must be a member to view it.</p>
                    <a class="btn btn-primary" href="<?php echo ROOT; ?>group/<?php echo $group_data['userid']; ?>">Go to the group</a>
                <?php else: ?>
                    <p>The author shares posts only with friends.</p>
                    <a class="btn btn-primary" href="<?php echo ROOT; ?>profile/<?php echo $ROW_USER['userid']; ?>">Go to the profile</a>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <?php if (is_array($parent_row)): ?>
                <div style="margin-bottom: 10px;">
                    <a href="<?php echo ROOT; ?>single_post/<?php echo $parent_row['post_id']; ?>" style="text-decoration: none; font-weight: bold;">&larr; Back to the original post</a>
                </div>
            <?php endif; ?>

            <div class="posts-bar">
                <?php include("post.php"); ?>

                <div style="padding: 0 10px 10px 10px;">

                    <?php if ($COMMENT_ERROR != ""): ?>
                        <div class="alert alert-error" style="margin-top: 10px;"><?php echo $COMMENT_ERROR; ?></div>
                    <?php endif; ?>

                    <?php if ($can_comment): ?>
                        <div class="composer" style="margin: 10px 0 0 0;">
                            <form method="post" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                <textarea class="tag-input" name="post" placeholder="Post a comment... Use @username to tag someone"><?php echo ($COMMENT_ERROR != "" && isset($_POST['post']) && is_string($_POST['post'])) ? htmlspecialchars($_POST['post']) : ""; ?></textarea>
                                <div class="tag-suggestions"></div>
                                <input type="file" name="file" accept="image/jpeg,video/mp4">
                                <input class="post-button" type="submit" value="Comment">
                            </form>
                        </div>
                    <?php elseif (!$me): ?>
                        <div class="alert alert-info" style="margin-top: 10px;"><a href="<?php echo ROOT; ?>login">Log in</a> to comment on this post.</div>
                    <?php else: ?>
                        <div class="alert alert-info" style="margin-top: 10px;">Only members of this group can comment. <a href="<?php echo ROOT; ?>group/<?php echo $group_data['userid']; ?>">Join the group</a> to take part.</div>
                    <?php endif; ?>

                    <div class="comments">
                        <?php
                            if (is_array($comments)) {
                                foreach ($comments as $COMMENT) {
                                    include("comment.php");
                                }
                            } else {
                                echo "<div class='empty-state' style='padding: 15px;'>No comments yet.</div>";
                            }
                        ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </div>
</body>
</html>
