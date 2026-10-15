<?php
include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);
if (!$user_data) { header("Location: " . ROOT . "login"); die; }

$DB = new Database();
$User = new User();
$Post = new Post();
$group_class = new Group();
$user_id = $user_data['userid'];
$safe_id = esc($user_id);

$query = "SELECT n.*, ns.id AS seen_id FROM notifications n
          LEFT JOIN notification_seen ns ON n.id = ns.notification_id AND ns.userid = '$safe_id'
          WHERE " . notifications_where($user_id) . "
          ORDER BY n.id DESC LIMIT 30";
$notifications = $DB->read($query);

// mark as seen before header.php counts the badge
if (is_array($notifications)) {
    foreach ($notifications as $notif) {
        if (is_null($notif['seen_id'])) {
            notification_seen($notif['id']);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notifications | Osmos</title>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="page">
        <div class="card">
            <h3 class="card-title">Notifications</h3>

            <?php if (is_array($notifications)): ?>
                <?php foreach ($notifications as $notif): ?>
                    <?php
                        $is_unread = is_null($notif['seen_id']);

                        $actor_id = $notif['user_id'];
                        $actor = $User->get_user($actor_id);

                        $actor_name = $actor ? htmlspecialchars($actor['first_name'] . " " . $actor['last_name']) : "Someone";
                        $actor_image = avatar_url($actor);

                        $msg = "";
                        $link = ROOT . "profile/" . $actor_id;
                        $content_id = $notif['content_id'];
                        $thing = ($notif['content_type'] == "comment") ? "comment" : "post";

                        if ($notif['activity'] == 'like') {
                            $msg = "liked your " . $thing . ".";
                            $link = ROOT . "single_post/" . $content_id . "/" . $notif['id'];
                        } else if ($notif['activity'] == 'comment') {
                            $commented = $Post->get_one_post($content_id);
                            $msg = ($commented && $commented['user_id'] == $user_id) ? "commented on your post." : "commented on a post you follow.";
                            $link = ROOT . "single_post/" . $content_id . "/" . $notif['id'];
                        } else if ($notif['activity'] == 'tag') {
                            $msg = "tagged you in a post.";
                            $link = ROOT . "single_post/" . $content_id . "/" . $notif['id'];
                        } else if ($notif['activity'] == 'friend_request') {
                            $msg = "sent you a friend request.";
                            $link = ROOT . "profile/" . $user_id . "/requests";
                        } else if ($notif['activity'] == 'friend_accept') {
                            $msg = "accepted your friend request.";
                            $link = ROOT . "profile/" . $actor_id;
                        } else if ($notif['activity'] == 'invite' || $notif['activity'] == 'role') {
                            $notif_group = $group_class->get_group($content_id);
                            $group_name = $notif_group ? htmlspecialchars($notif_group['first_name']) : "a group";
                            if ($notif['activity'] == 'invite') {
                                $msg = "invited you to join <strong>" . $group_name . "</strong>.";
                            } else {
                                $role = $notif_group ? ucfirst($group_class->get_member_role($content_id, $user_id)) : "";
                                $msg = "changed your role in <strong>" . $group_name . "</strong>" . ($role != "" && $role != "Unknown" ? " to " . htmlspecialchars($role) : "") . ".";
                            }
                            $link = ROOT . "group/" . $content_id;
                        } else {
                            $msg = htmlspecialchars($notif['activity']) . " notification.";
                        }
                    ?>

                    <div class="notification<?php echo $is_unread ? " unread" : ""; ?>">
                        <a href="<?php echo ROOT; ?>profile/<?php echo $actor_id; ?>">
                            <img src="<?php echo ROOT . $actor_image; ?>">
                        </a>
                        <div>
                            <a href="<?php echo $link; ?>">
                                <strong><?php echo $actor_name; ?></strong> <?php echo $msg; ?>
                            </a>
                            <br>
                            <span style="font-size: 11px; color: #888888;"><?php echo Time::get_time($notif['date']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">No notifications to display.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
