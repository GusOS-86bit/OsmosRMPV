<?php


date_default_timezone_set('Europe/Riga');

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

include_once("classes/connect.php");
include_once("classes/login.php");
include_once("classes/user.php");
include_once("classes/post.php");
include_once("classes/image.php");
include_once("classes/profile.php");
include_once("classes/settings.php");
include_once("classes/time.php");
include_once("classes/functions.php");
include_once("classes/signup.php");
include_once("classes/messages.php");
include_once("classes/group.php");

if (!defined("ROOT")) {
    $scheme = $_SERVER['REQUEST_SCHEME'] ?? ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
    $root = $scheme . "://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'];
    
    $root = trim(str_replace(basename($_SERVER['PHP_SELF']), "", $root), "/");
    define("ROOT", $root . "/");
}

// $URL isn't set when a page is opened directly
if (!isset($URL)) {
    $URL = split_url();
}

// csrf check for every POST
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $is_ajax = (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? "") == "XMLHttpRequest");

    // over post_max_size php just gives us an empty $_POST and $_FILES
    if (empty($_POST) && empty($_FILES) && !empty($_SERVER['CONTENT_LENGTH'])) {
        if ($is_ajax) {
            echo json_encode(["action" => "error", "message" => "That upload is too large."]);
            die;
        }
        set_flash("That upload is too large. Please choose a smaller file.", "error");
        redirect_back();
    }

    if (!csrf_check($_POST['csrf_token'] ?? "")) {
        if ($is_ajax) {
            echo json_encode(["action" => "error", "message" => "Your session expired. Please reload the page."]);
            die;
        }
        set_flash("Your session expired. Please try again.", "error");
        redirect_back();
    }
}
