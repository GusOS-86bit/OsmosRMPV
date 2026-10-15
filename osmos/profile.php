<?php
include_once("classes/autoload.php");

$login = new Login();
$USER = $login->check_login($_SESSION['osmos_userid'] ?? 0);
$me = $USER['userid'];

// no id, go to my own profile
// not using empty() because empty("0") is true
if (!isset($URL[1]) || $URL[1] === "") {
    header("Location: " . ROOT . "profile/" . $me);
    die;
}

// $user_data = the profile we're looking at, $USER = me
$profile = new Profile();
$profile_data = $profile->get_profile($URL[1]);
$user_data = (is_array($profile_data) && !empty($profile_data)) ? $profile_data[0] : false;

if (is_array($user_data) && ($user_data['type'] ?? "") == "group") {
    header("Location: " . ROOT . "group/" . $user_data['userid']);
    die;
}

$is_own_profile = is_array($user_data) && $user_data['userid'] == $me;

$POST_ERROR = "";
if ($_SERVER['REQUEST_METHOD'] == "POST" && is_array($user_data)) {

    if (($_POST['action'] ?? "") == "settings") {
        if ($is_own_profile) {
            $settings_class = new Settings();
            $error = $settings_class->save_settings($me, $_POST);

            if ($error == "") {
                set_flash("Your settings were saved.");
            } else {
                set_flash($error, "error");
            }
        }

        header("Location: " . ROOT . "profile/" . $me . "/settings");
        die;
    } else {
        // new post
        if (!$is_own_profile) {
            set_flash("You can only post on your own profile.", "error");
            header("Location: " . ROOT . "profile/" . $user_data['userid']);
            die;
        }

        $post = new Post();
        $result = $post->create_post($me, $_POST, $_FILES);
        if ($result == "") {
            header("Location: " . ROOT . "profile/" . $me);
            die;
        }
        $POST_ERROR = $result;
    }
}

if (isset($URL[2]) && is_numeric($URL[2])) {
    notification_seen($URL[2]);
}

$section = "default";
if (isset($URL[2]) && !is_numeric($URL[2])) {
    $section = $URL[2];
}
if (!in_array($section, ["default", "photos", "friends", "requests", "groups", "about", "settings"])) {
    $section = "default";
}
if (($section == "settings" || $section == "requests") && !$is_own_profile) {
    $section = "default";
}

$posts = false;
$friends = false;
$image_class = new Image();
$user_class = new User();
$can_view = false;
$friend_status = "none";
$friend_count = 0;
$request_count = 0;

if (is_array($user_data)) {
    $can_view = $user_class->can_view_profile($user_data, $me);
    $friend_status = $user_class->friendship_status($me, $user_data['userid']);
    $friend_count = $user_class->count_friends($user_data['userid']);

    if ($is_own_profile) {
        $request_count = $user_class->count_received_requests($me);
    }

    if ($can_view) {
        $post = new Post();
        $posts = $post->get_post($user_data['userid']);
        $friends = $user_class->get_friends($user_data['userid']);
    }
}

// header and About tab are always visible
$restricted = is_array($user_data) && !$can_view && in_array($section, ["default", "photos", "friends", "groups"]);

$page_title = is_array($user_data) ? htmlspecialchars($user_data['first_name'] . " " . $user_data['last_name']) : "Profile";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?php echo $page_title; ?> | Osmos</title>
    </head>
    <body>
        <?php include("header.php") ?>

        <div class="page">

            <?php if (!is_array($user_data)): ?>

                <div class="card empty-state">
                    <h2 style="color: #405d9b;">That profile was not found!</h2>
                    <a class="btn btn-primary" href="<?php echo ROOT; ?>home">Back to Timeline</a>
                </div>

            <?php else: ?>

            <div class="profile-header">

                <?php
                $cover = "images/cover_image.jpg";
                if (is_image_file($user_data['cover_image'] ?? "")) {
                    $cover = $image_class->get_thumb_cover($user_data['cover_image']);
                }
                ?>
                <img class="cover-image" src="<?php echo ROOT . $cover; ?>">

                <img class="profile-cover-pic" src="<?php echo ROOT . avatar_url($user_data); ?>">

                <?php if ($is_own_profile) : ?>
                    <div class="cover-links">
                        <a href="<?php echo ROOT; ?>change_profile_image/profile">Change Profile Image</a> |
                        <a href="<?php echo ROOT; ?>change_profile_image/cover">Change Cover</a>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 8px;">
                    <a class="profile-name" href="<?php echo ROOT; ?>profile/<?php echo $user_data['userid']; ?>">
                        <?php echo htmlspecialchars($user_data['first_name'] . " " . $user_data['last_name']); ?>
                    </a>
                    <div class="profile-tag">
                        @<?php echo htmlspecialchars($user_data['tag_name'] ?? ''); ?>
                        &middot; <?php echo $friend_count . " friend" . ($friend_count == 1 ? "" : "s"); ?>
                        <?php if (($user_data['privacy'] ?? "public") == "friends"): ?>
                            &middot; Friends only
                        <?php endif; ?>
                        <?php if (!empty($user_data['blocked']) && is_admin($me)): ?>
                            &middot; <span style="color: #d9534f; font-weight: bold;">Blocked</span>
                        <?php endif; ?>
                    </div>

                    <?php if (!$is_own_profile): ?>
                        <div class="profile-actions">
                            <?php if ($friend_status == "friends"): ?>
                                <span class="btn btn-light">Friends</span>
                                <a class="btn btn-light" href="<?php echo csrf_url(ROOT . "friend/remove/" . $user_data['userid']); ?>" onclick="return confirm('Remove from your friends?');">Unfriend</a>
                            <?php elseif ($friend_status == "sent"): ?>
                                <span class="btn btn-muted">Request sent</span>
                                <a class="btn btn-light" href="<?php echo csrf_url(ROOT . "friend/cancel/" . $user_data['userid']); ?>">Cancel request</a>
                            <?php elseif ($friend_status == "received"): ?>
                                <a class="btn btn-primary" href="<?php echo csrf_url(ROOT . "friend/accept/" . $user_data['userid']); ?>">Accept friend request</a>
                                <a class="btn btn-light" href="<?php echo csrf_url(ROOT . "friend/decline/" . $user_data['userid']); ?>">Decline</a>
                            <?php elseif (empty($user_data['blocked'])): ?>
                                <a class="btn btn-primary" href="<?php echo csrf_url(ROOT . "friend/add/" . $user_data['userid']); ?>">Add friend</a>
                            <?php endif; ?>
                            <a class="btn btn-orange" href="<?php echo ROOT; ?>messages/new/<?php echo $user_data['userid']; ?>">Message</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tabs">
                    <?php
                    $tabs = [
                        "default" => "Timeline",
                        "about" => "About",
                        "friends" => "Friends (" . $friend_count . ")",
                        "photos" => "Photos",
                        "groups" => "Groups",
                    ];
                    if ($is_own_profile) {
                        $tabs["requests"] = "Friend requests" . ($request_count > 0 ? " (" . $request_count . ")" : "");
                        $tabs["settings"] = "Settings";
                    }

                    foreach ($tabs as $tab_key => $tab_label) {
                        $tab_url = ROOT . "profile/" . $user_data['userid'] . ($tab_key == "default" ? "" : "/" . $tab_key);
                        $active = ($section == $tab_key) ? " active" : "";
                        echo '<a class="menu-button' . $active . '" href="' . $tab_url . '">' . $tab_label . '</a>';
                    }
                    ?>
                </div>
            </div>

            <?php
            if ($restricted) include("profile_content_private.php");
            elseif ($section == "default") include("profile_content_default.php");
            elseif ($section == "photos") include("profile_content_photos.php");
            elseif ($section == "friends") include("profile_content_friends.php");
            elseif ($section == "requests") include("profile_content_requests.php");
            elseif ($section == "groups") include("profile_content_groups.php");
            elseif ($section == "about") include("profile_content_about.php");
            elseif ($section == "settings") include("profile_content_settings.php");
            ?>

            <?php endif; ?>
        </div>
    </body>
</html>
