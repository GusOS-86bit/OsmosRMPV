<?php
include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);
$USER = $user_data;
$me = is_array($user_data) ? $user_data['userid'] : 0;

// group/cover/ID opens the cover change page
if (isset($URL[1]) && $URL[1] == "cover" && isset($URL[2]) && is_numeric($URL[2])) {
    header("Location: " . ROOT . "change_profile_image/cover/" . $URL[2]);
    die;
}

if (!isset($URL[1]) || $URL[1] === "") {
    header("Location: " . ROOT . "home");
    die;
}

$group_class = new Group();
$group_data = $group_class->get_group($URL[1]);

$section = "default";
if (isset($URL[2]) && !is_numeric($URL[2])) {
    $section = $URL[2];
}

// permissions
$is_member = false;
$is_moderator = false;
$is_admin = false;
$is_owner = false;
$can_view = false;
$has_request = false;
$is_invited = false;

if (is_array($group_data)) {
    $gid = $group_data['userid'];
    $CURRENT_GROUP_ID = $gid; // post.php skips the "in <group>" label with this

    $is_owner = $me && $group_data['owner'] == $me;
    $is_member = group_access($me, $group_data, 'member');
    $is_moderator = group_access($me, $group_data, 'moderator');
    $is_admin = group_access($me, $group_data, 'admin');
    $can_view = group_can_view($me, $group_data);
    $has_request = $me && !$is_member && group_access($me, $group_data, 'request');
    $is_invited = $me && !$is_member && $group_class->is_invited($gid, $me);
}

// forms go before any output so the redirects work
$POST_ERROR = "";
if ($_SERVER['REQUEST_METHOD'] == "POST" && is_array($group_data)) {

    if (!$me) {
        header("Location: " . ROOT . "login");
        die;
    }

    $action = $_POST['action'] ?? "post";

    if ($action == "settings") {

        if ($is_admin) {
            $error = $group_class->save_settings($gid, $_POST);
            set_flash($error == "" ? "The group settings were saved." : $error, $error == "" ? "success" : "error");
        } else {
            set_flash("Only group admins can change the settings.", "error");
        }
        header("Location: " . ROOT . "group/" . $gid . "/settings");
        die;

    } elseif ($action == "edit_role" || $action == "remove_member") {

        $target = $_POST['user_id'] ?? "";

        if (!$is_admin) {
            set_flash("Only group admins can manage members.", "error");
        } elseif (!is_numeric($target) || $target == $group_data['owner']) {
            set_flash("The group owner can't be changed or removed.", "error");
        } elseif ($target == $me) {
            set_flash("You can't change your own role. Use Leave Group instead.", "error");
        } elseif ($group_class->get_member_role($gid, $target) == "") {
            set_flash("That person is not a member of this group.", "error");
        } elseif ($action == "edit_role") {
            if ($group_class->edit_member_access($gid, $target, $_POST['role'] ?? "")) {
                set_flash("The member's access was updated.");
            } else {
                set_flash("Please choose a valid role.", "error");
            }
        } else {
            $group_class->remove_member($gid, $target);
            set_flash("This user was successfully removed from the group!");
        }
        header("Location: " . ROOT . "group/" . $gid . "/members");
        die;

    } else {

        // new post
        if (!$is_member) {
            set_flash("Only members can post in this group.", "error");
            header("Location: " . ROOT . "group/" . $gid);
            die;
        }

        $post = new Post();
        $result = $post->create_post($me, $_POST, $_FILES, $gid);
        if ($result == "") {
            header("Location: " . ROOT . "group/" . $gid);
            die;
        }
        $POST_ERROR = $result;
    }
}

$denied_reason = "";
if (is_array($group_data)) {
    if (!in_array($section, ["default", "about", "members", "photos", "requests", "invite", "invited", "settings"])) {
        $section = "default";
    }

    if (in_array($section, ["default", "members", "photos"]) && !$can_view) {
        $denied_reason = "private";
    } elseif ($section == "requests" && !$is_moderator) {
        $denied_reason = "no_access";
    } elseif ($section == "settings" && !$is_admin) {
        $denied_reason = "no_access";
    } elseif ($section == "invite" && !$is_member) {
        $denied_reason = "invite";
    } elseif ($section == "invited" && !$me) {
        $denied_reason = "login";
    }
}

$posts = false;
$member_count = 0;
$image_class = new Image();
$user = new User();

if (is_array($group_data)) {
    $member_count = $group_class->count_members($gid);
    if ($can_view) {
        $post = new Post();
        $posts = $post->get_post($gid, "group");
    }
}

$is_private = is_array($group_data) && ($group_data['group_type'] ?? "public") == "private";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo is_array($group_data) ? htmlspecialchars($group_data['first_name']) : "Group"; ?> | Osmos</title>
    </head>
    <body>
        <?php include("header.php") ?>

        <div class="page">

            <?php if (is_array($group_data)): ?>
            <div class="profile-header">

                <?php
                $cover = "images/cover_image.JPG";
                if (is_image_file($group_data['cover_image'] ?? "")) {
                    $cover = $image_class->get_thumb_cover($group_data['cover_image']);
                }
                ?>
                <img class="cover-image" src="<?php echo ROOT . $cover; ?>">

                <?php if ($is_admin): ?>
                    <div class="cover-links">
                        <a href="<?php echo ROOT; ?>change_profile_image/cover/<?php echo $gid; ?>">Change Cover</a>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 12px;">
                    <a class="profile-name" href="<?php echo ROOT; ?>group/<?php echo $gid; ?>"><?php echo htmlspecialchars($group_data['first_name']); ?></a>
                    <div class="group-meta">
                        <?php echo $is_private ? "Private group" : "Public group"; ?>
                        &middot; <?php echo $member_count . " member" . ($member_count == 1 ? "" : "s"); ?>
                    </div>

                    <div class="profile-actions">
                        <?php if (!$me): ?>
                            <a class="btn btn-primary" href="<?php echo ROOT; ?>login">Log in to join</a>
                        <?php elseif ($is_member): ?>
                            <a class="btn btn-blue" href="<?php echo ROOT; ?>group/<?php echo $gid; ?>/invite">Invite Members</a>
                            <?php if (!$is_owner): ?>
                                <a class="btn btn-light" href="<?php echo csrf_url(ROOT . "join/" . $gid . "/leave"); ?>" onclick="return confirm('Leave this group?');">Leave Group</a>
                            <?php endif; ?>
                        <?php elseif ($is_invited): ?>
                            <span style="font-size: 13px; color: #555; margin-right: 6px;">You were invited to this group.</span>
                            <a class="btn btn-primary" href="<?php echo csrf_url(ROOT . "group_request_accept/" . $gid . "/" . $me . "/accept"); ?>">Accept Invite</a>
                            <a class="btn btn-light" href="<?php echo csrf_url(ROOT . "group_request_accept/" . $gid . "/" . $me . "/decline"); ?>">Decline</a>
                        <?php elseif ($has_request): ?>
                            <span class="btn btn-muted">Request Sent</span>
                        <?php else: ?>
                            <a class="btn btn-primary" href="<?php echo csrf_url(ROOT . "join/" . $gid); ?>"><?php echo $is_private ? "Request to Join" : "Join Group"; ?></a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="tabs">
                    <?php
                    $tabs = [
                        "default" => "Discussion",
                        "about" => "About",
                        "members" => "Members (" . $member_count . ")",
                        "photos" => "Photos",
                    ];

                    // requests for mods and up, settings for admins and the owner
                    if ($is_moderator) {
                        $requests = $group_class->get_requests($gid);
                        $tabs["requests"] = "Requests" . (is_array($requests) ? " (" . count($requests) . ")" : "");
                    }
                    if ($is_admin) {
                        $tabs["settings"] = "Settings";
                    }

                    foreach ($tabs as $tab_key => $tab_label) {
                        $tab_url = ROOT . "group/" . $gid . ($tab_key == "default" ? "" : "/" . $tab_key);
                        $active = ($section == $tab_key) ? " active" : "";
                        echo '<a class="menu-button' . $active . '" href="' . $tab_url . '">' . $tab_label . '</a>';
                    }
                    ?>
                </div>
            </div>

            <?php
            if ($denied_reason != "") include("group_content_denied.php");
            elseif ($section == "default") include("group_content_default.php");
            elseif ($section == "about") include("group_content_about.php");
            elseif ($section == "photos") include("group_content_photos.php");
            elseif ($section == "members") include("group_content_members.php");
            elseif ($section == "requests") include("group_content_requests.php");
            elseif ($section == "invite") include("group_content_invite.php");
            elseif ($section == "invited") include("group_content_invited.php");
            elseif ($section == "settings") include("group_content_settings.php");
            ?>

            <?php else: ?>
                <div class="card empty-state">
                    <h2 style="color: #405d9b;">That group was not found!</h2>
                    <br>
                    <?php if ($me): ?>
                        <a class="btn btn-primary" href="<?php echo ROOT; ?>profile/<?php echo $me; ?>/groups">Back to My Groups</a>
                    <?php else: ?>
                        <a class="btn btn-primary" href="<?php echo ROOT; ?>login">Log in</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        </div>
    </body>
</html>
