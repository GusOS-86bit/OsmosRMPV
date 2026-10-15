<?php
// delete/POSTID, delete/msg/ID or delete/thread/MSGID
include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);
$USER = $user_data;
$me = $user_data['userid'];

// the templates below need $image_class
if (!isset($image_class)) {
    $image_class = new Image();
}

$Post = new Post();
$msg_class = new Messages();
$user_class = new User();

if (isset($_SERVER['HTTP_REFERER']) && !strstr($_SERVER['HTTP_REFERER'], "/delete/")) {
    $_SESSION['return_to'] = safe_return_url($_SERVER['HTTP_REFERER']);
}
$return_to = safe_return_url($_SESSION['return_to'] ?? "", ROOT . "home");

$ERROR = "";
$MESSAGE = false;
$ROW = false;

// whole chat
if (isset($URL[1]) && $URL[1] == "thread" && isset($URL[2])) {

    $MESSAGE = $msg_class->read_one_thread($URL[2]);

    if (!$MESSAGE) {
        $ERROR = "Access denied! You cannot delete this thread or it does not exist!";
    }

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if ($MESSAGE) {
            $msg_class->delete_one_thread($URL[2]);
            set_flash("The conversation was deleted.");
        }
        header("Location: " . ROOT . "messages");
        die;
    }

// one message
} elseif (isset($URL[1]) && $URL[1] == "msg" && isset($URL[2]) && is_numeric($URL[2])) {

    $MESSAGE = $msg_class->read_one($URL[2]);

    if (!$MESSAGE) {
        $ERROR = "Access denied! You cannot delete this message or it does not exist!";
    }

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if ($MESSAGE) {
            $msg_class->delete_one($MESSAGE['id']);
        }
        header("Location: " . $return_to);
        die;
    }

// post
} elseif (isset($URL[1]) && is_numeric($URL[1])) {

    $ROW = $Post->get_one_post($URL[1]);

    if (!$ROW) {
        $ERROR = "No such post was found!";
    } elseif (!$Post->can_delete_post($ROW, $me)) {
        $ERROR = "Access denied! You cannot delete this post!";
    }

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        if ($ROW && $Post->can_delete_post($ROW, $me)) {
            $Post->delete_post($ROW['post_id']);
            set_flash("The post was deleted.");

            // the post's page is gone now, go somewhere else
            if (strpos($return_to, "/single_post/" . $ROW['post_id']) !== false || strpos($return_to, "/image_view/" . $ROW['post_id']) !== false) {
                $return_to = ($ROW['parent'] > 0) ? ROOT . "single_post/" . $ROW['parent'] : ROOT . "home";
            }
        }
        header("Location: " . $return_to);
        die;
    }

} else {
    $ERROR = "No such item was found!";
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delete | Osmos</title>
</head>
<body>
    <?php include("header.php") ?>
    <div class="page">
        <div class="card" style="overflow: hidden;">
            <form method="post">
                <?php echo csrf_field(); ?>
                <?php
                    if ($ERROR != "") {
                        echo "<div class='empty-state'>" . $ERROR . "</div>";
                    } else {
                        if ($URL[1] == "thread") {
                            echo "<h3 class='card-title'>Are you sure you want to delete this entire conversation?</h3>";

                            $ROW_USER = $user_class->get_user($MESSAGE['sender']);
                            include("message_left.php");

                            echo '<div class="clear"></div><br>';
                            echo "<input type='submit' class='btn btn-danger' value='Delete Thread' style='float: right;'>";
                            echo "<a class='btn btn-light' href='" . htmlspecialchars($return_to, ENT_QUOTES) . "' style='float: right; margin-right: 8px;'>Cancel</a>";

                        } elseif ($URL[1] == "msg") {
                            echo "<h3 class='card-title'>Are you sure you want to delete this message?</h3>";

                            $ROW_USER = $user_class->get_user($MESSAGE['sender']);
                            include("message_left.php");

                            echo '<div class="clear"></div><br>';
                            echo "<input type='submit' class='btn btn-danger' value='Delete' style='float: right;'>";
                            echo "<a class='btn btn-light' href='" . htmlspecialchars($return_to, ENT_QUOTES) . "' style='float: right; margin-right: 8px;'>Cancel</a>";

                        } else {
                            echo "<h3 class='card-title'>Are you sure you want to delete this post?</h3>";
                            if ((int)$ROW['comments'] > 0) {
                                echo "<div class='alert alert-warning'>Its comments will be deleted too.</div>";
                            }

                            $ROW_USER = $user_class->get_user($ROW['user_id']);
                            include("post_delete.php");

                            echo "<input type='submit' class='btn btn-danger' value='Delete' style='float: right; margin-top: 10px;'>";
                            echo "<a class='btn btn-light' href='" . htmlspecialchars($return_to, ENT_QUOTES) . "' style='float: right; margin: 10px 8px 0 0;'>Cancel</a>";
                        }
                    }
                ?>
            </form>
        </div>
    </div>
</body>
</html>
