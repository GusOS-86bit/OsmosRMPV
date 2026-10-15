<?php

include_once("classes/autoload.php");
$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);

$return_to = return_url(ROOT . "profile");

// like/post/123
$type = $URL[1] ?? null;
$id = $URL[2] ?? null;

if (!csrf_check($_GET['token'] ?? "")) {
    set_flash("That link has expired. Please try again.", "error");
    redirect($return_to);
}

if ($type !== null && is_numeric($id)) {

    $user_id = $user_data['userid'];

    if ($type == "post" || $type == "comment") {
        $post = new Post();
        $row = $post->get_one_post($id);
        if ($row && $post->can_view_post($row, $user_id)) {
            $post->like_post($id, $type, $user_id);
        }
    }
}

redirect($return_to);
