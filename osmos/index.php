<?php
// .htaccess sends every request here

include_once("classes/autoload.php");

// allowed pages, anything else is a 404
$routes = [
    "home",
    "profile",
    "login",
    "logout",
    "signup",
    "search",
    "single_post",
    "image_view",
    "like",
    "friend",
    "admin",
    "likes",
    "edit",
    "delete",
    "messages",
    "notifications",
    "group",
    "create_group",
    "join",
    "invite",
    "group_request_accept",
    "change_profile_image",
    "tags",
    "ajax",
];

$page = $URL[0] ?? "home";

if (in_array($page, $routes, true) && file_exists($page . ".php")) {
    require($page . ".php");
} else {
    require("404.php");
}
