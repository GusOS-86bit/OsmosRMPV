<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<?php
    $group_owner_row = $user->get_user($group_data['owner']);
    // first 9 for the sidebar, owner first
    $preview_members = $group_class->get_members($gid, 9);
?>
<div class="two-col">

    <!-- left side -->
    <div class="col-side">
        <div class="card" style="padding: 15px;">
            <h4 class="card-subtitle">About</h4>
            <div style="font-size: 13px; color: #444; line-height: 1.45; word-wrap: break-word;">
                <?php if (trim($group_data['about'] ?? "") !== ""): ?>
                    <?php echo nl2br(htmlspecialchars(mb_strimwidth($group_data['about'], 0, 300, "..."))); ?>
                <?php else: ?>
                    <span class="muted">No description yet.</span>
                <?php endif; ?>
            </div>
            <ul class="about-list" style="margin-top: 10px; font-size: 12px;">
                <li><?php echo $is_private ? "<strong>Private</strong> &middot; only members see posts" : "<strong>Public</strong> &middot; anyone can see posts"; ?></li>
                <li>Created <?php echo date("F jS, Y", strtotime($group_data['date'])); ?></li>
                <?php if (is_array($group_owner_row)): ?>
                    <li>Owner: <a href="<?php echo ROOT; ?>profile/<?php echo $group_owner_row['userid']; ?>" style="text-decoration: none; font-weight: bold;"><?php echo htmlspecialchars($group_owner_row['first_name'] . " " . $group_owner_row['last_name']); ?></a></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="card" style="padding: 15px;">
            <h4 class="card-subtitle">Members &middot; <?php echo $member_count; ?></h4>
            <div class="member-grid">
                <?php
                    if (is_array($preview_members)) {
                        foreach ($preview_members as $member_row) {
                            $FRIEND_ROW = $user->get_user($member_row['userid']);
                            if (is_array($FRIEND_ROW)) {
                                echo "<a class='member-thumb' href='" . ROOT . "profile/" . $FRIEND_ROW['userid'] . "' title='" . htmlspecialchars($FRIEND_ROW['first_name'] . " " . $FRIEND_ROW['last_name'], ENT_QUOTES) . "'>";
                                echo "<img src='" . ROOT . avatar_url($FRIEND_ROW) . "'>";
                                echo "<span>" . htmlspecialchars($FRIEND_ROW['first_name']) . "</span>";
                                echo "</a>";
                            }
                        }
                    } else {
                        echo "<span class='muted' style='font-size: 13px;'>This group has no members!</span>";
                    }
                ?>
            </div>
            <div style="margin-top: 10px; font-size: 12px;">
                <a href="<?php echo ROOT; ?>group/<?php echo $gid; ?>/members" style="text-decoration: none;">See all members &rarr;</a>
            </div>
        </div>
    </div>

    <!-- right side -->
    <div class="col-main">
        <?php if ($is_member): ?>
            <?php if ($POST_ERROR != ""): ?>
                <div class="alert alert-error"><?php echo $POST_ERROR; ?></div>
            <?php endif; ?>

            <div class="composer">
                <form method="post" enctype="multipart/form-data">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="post">
                    <textarea class="tag-input" name="post" placeholder="Post to the group... Use @username to tag someone"><?php echo ($POST_ERROR != "" && isset($_POST['post']) && is_string($_POST['post'])) ? htmlspecialchars($_POST['post']) : ""; ?></textarea>
                    <div class="tag-suggestions"></div>
                    <input type="file" name="file" accept="image/jpeg,video/mp4">
                    <input class="post-button" type="submit" value="Post">
                </form>
            </div>
        <?php elseif ($me): ?>
            <div class="alert alert-info">Join this group to write posts and comments.</div>
        <?php endif; ?>

        <div class="posts-bar">
            <?php
                if ($posts) {
                    foreach ($posts as $ROW) {
                        $ROW_USER = $user->get_user($ROW['user_id']);
                        include("post.php");
                    }
                } else {
                    echo "<div class='empty-state'>No posts to display in this group yet!</div>";
                }
            ?>
        </div>
    </div>
</div>
