<?php
// friend/add|accept|decline|cancel|remove/USERID
include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);
$me = $user_data['userid'];

$return_to = return_url(ROOT . "profile/" . $me);

if (!csrf_check($_GET['token'] ?? "")) {
    set_flash("That link has expired. Please try again.", "error");
    redirect($return_to);
}

$action = $URL[1] ?? "";
$other = $URL[2] ?? "";

if (is_numeric($other)) {
    $user_class = new User();
    $status = $user_class->friendship_status($me, $other);

    if ($action == "add") {
        if ($user_class->send_friend_request($me, $other)) {
            set_flash($status == "received" ? "You are now friends!" : "Friend request sent.");
        }
    } elseif ($action == "accept" && $status == "received") {
        $user_class->accept_friend_request($me, $other);
        set_flash("You are now friends!");
    } elseif ($action == "decline" && $status == "received") {
        $user_class->remove_friend_row($me, $other);
        set_flash("Friend request declined.", "info");
    } elseif ($action == "cancel" && $status == "sent") {
        $user_class->remove_friend_row($me, $other);
        set_flash("Friend request cancelled.", "info");
    } elseif ($action == "remove" && $status == "friends") {
        $user_class->remove_friend_row($me, $other);
        set_flash("Removed from your friends.", "info");
    }
}

redirect($return_to);
