<?php
// group_request_accept/GROUPID/USERID/accept|decline
include_once("classes/autoload.php");

if (!isset($_SESSION['osmos_userid'])) {
    header("Location: " . ROOT . "login");
    die;
}

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid']);
$me = $user_data['userid'];

$return_to = return_url(ROOT . "home");

if (!csrf_check($_GET['token'] ?? "")) {
    set_flash("That link has expired. Please try again.", "error");
    redirect($return_to);
}

if (isset($URL[1]) && isset($URL[2]) && isset($URL[3]) && is_numeric($URL[1]) && is_numeric($URL[2]) && in_array($URL[3], ["accept", "decline"], true)) {
    $group_id = $URL[1];
    $target_userid = $URL[2];
    $action = $URL[3];

    $group_class = new Group();
    $group_data = $group_class->get_group($group_id);

    if ($group_data) {
        // mod/admin answering a join request
        if (group_access($me, $group_data, 'moderator') && $group_class->has_request($group_id, $target_userid)) {
            $group_class->accept_request($group_id, $target_userid, $action);
            set_flash($action == "accept" ? "The request was accepted." : "The request was declined.");
        }
        // invited user answering their invite
        elseif ($target_userid == $me && $group_class->is_invited($group_id, $me)) {
            $group_class->accept_request($group_id, $me, $action);
            if ($action == "accept") {
                set_flash("Welcome! You are now a member of this group.");
                redirect(ROOT . "group/" . $group_id);
            }
            set_flash("You declined the invitation.", "info");
        }
    }
}

redirect($return_to);
