<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<!-- group_content_members.php -->
<div class="card" style="margin-top: 20px; text-align: center;">
    <h3 class="card-title text-left">Members</h3>

    <?php
    $user_class = new User();

    $remove_id = (isset($_GET['remove']) && is_numeric($_GET['remove'])) ? $_GET['remove'] : 0;
    $edit_id = (isset($_GET['edit_access']) && is_numeric($_GET['edit_access'])) ? $_GET['edit_access'] : 0;

    // not the owner and not yourself
    if ($remove_id == $group_data['owner'] || $remove_id == $me) $remove_id = 0;
    if ($edit_id == $group_data['owner'] || $edit_id == $me) $edit_id = 0;

    // confirm first, group.php does the removing
    if ($is_admin && $remove_id && $group_class->get_member_role($gid, $remove_id) != ""):
        $FRIEND_ROW = $user_class->get_user($remove_id);
    ?>
        <div class="alert alert-error">
            <b>Are you sure you want to remove this user from the group?</b>
        </div>

        <?php include("user.php"); ?>

        <form method="post" style="margin-top: 20px;">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="remove_member">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($remove_id); ?>">
            <input type="submit" class="btn btn-danger" value="Remove">
            <a class="btn btn-light" href="<?php echo ROOT; ?>group/<?php echo $gid; ?>/members" style="margin-left: 10px;">Cancel</a>
        </form>

    <?php
    // role form, group.php saves it
    elseif ($is_admin && $edit_id && $group_class->get_member_role($gid, $edit_id) != ""):
        $FRIEND_ROW = $user_class->get_user($edit_id);
        $current_role = $group_class->get_member_role($gid, $edit_id);
    ?>
        <div class="alert alert-warning">
            <b>WARNING:</b> Giving users Admin access also gives them the power to remove you as Admin.
        </div>

        <?php include("user.php"); ?>

        <form method="post" style="margin-top: 20px;">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="action" value="edit_role">
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($edit_id); ?>">

            <select name="role" class="textbox" style="width: 220px;">
                <option value="member" <?php echo ($current_role == "member") ? "selected" : ""; ?>>Member</option>
                <option value="moderator" <?php echo ($current_role == "moderator") ? "selected" : ""; ?>>Moderator (can accept requests and remove posts)</option>
                <option value="admin" <?php echo ($current_role == "admin") ? "selected" : ""; ?>>Admin (can also manage members and settings)</option>
            </select>
            <br><br>
            <input type="submit" class="btn btn-blue" value="Save Changes">
            <a class="btn btn-light" href="<?php echo ROOT; ?>group/<?php echo $gid; ?>/members" style="margin-left: 10px;">Cancel</a>
        </form>

    <?php
    // member list
    else:
        $members = $group_class->get_members($gid);

        if (is_array($members)) {
            foreach ($members as $member_row) {
                $FRIEND_ROW = $user_class->get_user($member_row['userid']);
                if ($FRIEND_ROW) {
                    include("user_group_member.inc.php");
                }
            }
        } else {
            echo "<div class='empty-state'>This group has no members!</div>";
        }
    endif;
    ?>
</div>
