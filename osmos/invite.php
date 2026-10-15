 <?php
// invite/GROUPID/USERID
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

if (isset($URL[1]) && isset($URL[2]) && is_numeric($URL[1]) && is_numeric($URL[2])) {
    $group_id = $URL[1];
    $user_id = $URL[2];
    $me = $user_data['userid'];

    $group_class = new Group();
    $group_data = $group_class->get_group($group_id);

    if (is_array($group_data) && group_access($me, $group_data, 'member')) {
        if ($group_class->invite_to_group($group_id, $user_id, $me)) {
            set_flash("Invitation sent.");
        }
    }
}

redirect($return_to);
