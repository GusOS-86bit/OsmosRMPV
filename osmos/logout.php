<?php
include_once("classes/autoload.php");

if (isset($_SESSION['osmos_userid']))
{
    $_SESSION['osmos_userid'] = NULL;
    unset($_SESSION['osmos_userid']);
}

// new session id and csrf token
session_regenerate_id(true);
unset($_SESSION['csrf_token'], $_SESSION['return_to']);

header("Location: " . ROOT . "login");
die;
