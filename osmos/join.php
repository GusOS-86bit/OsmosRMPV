<?php
// join/GROUPID or join/GROUPID/leave
include_once("classes/autoload.php");

if (!isset($_SESSION['osmos_userid'])) {
    header("Location: " . ROOT . "login");
    die;
}

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid']);

$return_to = return_url(ROOT . "profile/" . $user_data['userid']);

if (!csrf_check($_GET['token'] ?? "")) {
    set_flash("That link has expired. Please try again.", "error");
    redirect($return_to);
}

if (isset($URL[1]) && is_numeric($URL[1])) {
    $group_id = $URL[1];
    $user_id = $user_data['userid'];
    $group_class = new Group();

    if (($URL[2] ?? "") == "leave") {
        if ($group_class->leave_group($group_id, $user_id)) {
            set_flash("You left the group.");
        }
        redirect(ROOT . "group/" . $group_id);
    }

    $result = $group_class->join_group($group_id, $user_id);
    if ($result == "joined") {
        set_flash("Welcome! You are now a member of this group.");
    } elseif ($result == "requested") {
        set_flash("Your request to join was sent to the group admins.", "info");
    }
}

redirect($return_to);
