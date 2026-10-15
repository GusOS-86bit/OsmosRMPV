<?php
include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);
$USER = $user_data;
$me = $user_data['userid'];

if (isset($_GET['notif'])) {
    notification_seen($_GET['notif']);
}

$ERROR = "";

// the templates below need $image_class
if (!isset($image_class)) {
    $image_class = new Image();
}

$user_class = new User();
$msg_class = new Messages();

$mode = $URL[1] ?? "";
$other_id = (isset($URL[2]) && is_numeric($URL[2])) ? $URL[2] : 0;

// has to be a real person, not a group or me
$friend_row = false;
if (($mode == "new" || $mode == "read") && $other_id) {
    $friend_row = $user_class->get_user($other_id);
    if (!is_array($friend_row) || ($friend_row['type'] ?? "") == "group" || $friend_row['userid'] == $me) {
        $friend_row = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (is_array($friend_row)) {
        $ERROR = $msg_class->send($_POST, $_FILES, $friend_row['userid']);
        if ($ERROR == "") {
            header("Location: " . ROOT . "messages/read/" . $friend_row['userid']);
            die;
        }
    } else {
        $ERROR = "You can't send a message to this user.<br>";
    }
}

// chat already exists, open that one instead
if ($mode == "new" && is_array($friend_row) && $_SERVER['REQUEST_METHOD'] != "POST" && $msg_class->has_thread($friend_row['userid'])) {
    header("Location: " . ROOT . "messages/read/" . $friend_row['userid']);
    die;
}

$message_form = '
    <div class="message-composer">
        <form method="post" enctype="multipart/form-data">
            ' . csrf_field() . '
            <textarea name="message" placeholder="Write your message here">' . (($ERROR != "" && isset($_POST['message']) && is_string($_POST['message'])) ? htmlspecialchars($_POST['message']) : "") . '</textarea>
            <input type="file" name="file" accept="image/jpeg,image/png" style="margin-top: 10px; font-size: 12px;">
            <input type="submit" value="Send" class="btn btn-orange" style="float: right; margin-top: 8px;">
        </form>
    </div>';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Messages | Osmos</title>
    </head>
    <body>
        <?php include("header.php") ?>

        <div class="page">
            <div class="card">

                <?php
                if ($ERROR != "") echo "<div class='alert alert-error'>" . $ERROR . "</div>";

                if ($mode == "new" || $mode == "read") {

                    if (!is_array($friend_row)) {

                        echo "<div class='empty-state'>That user was not found.<br><br><a class='btn btn-orange' href='" . ROOT . "messages'>All Messages</a></div>";

                    } elseif ($mode == "new") {

                        echo "<h3 class='card-title'>New message to " . htmlspecialchars($friend_row['first_name'] . " " . $friend_row['last_name']) . "</h3>";
                        echo $message_form;

                    } else {

                        $data = $msg_class->read($friend_row['userid']);

                        $thread_msgid = "";
                        if (is_array($data)) {
                            $thread_msgid = $data[count($data) - 1]['msgid'];
                        }

                        echo '<div style="text-align: left; margin-bottom: 15px;">';
                        echo '<a class="btn btn-orange" href="' . ROOT . 'messages" style="margin-right: 4px;">All Messages</a>';

                        if ($thread_msgid != "") {
                            echo '<a class="btn btn-danger" href="' . ROOT . 'delete/thread/' . urlencode($thread_msgid) . '">Delete Thread</a>';
                        }
                        echo '</div>';

                        echo "<h3 class='card-title'>Chatting with <a href='" . ROOT . "profile/" . $friend_row['userid'] . "' style='text-decoration: none;'>" . htmlspecialchars($friend_row['first_name'] . " " . $friend_row['last_name']) . "</a></h3>";

                        if (is_array($data)) {
                            foreach ($data as $MESSAGE) {
                                $ROW_USER = $user_class->get_user($MESSAGE['sender']);

                                if ($MESSAGE['sender'] == $me) {
                                    include("message_right.php");
                                } else {
                                    include("message_left.php");
                                }
                            }
                        } else {
                            echo "<div class='empty-state'>No messages yet. Say hi!</div>";
                        }

                        echo '<div class="clear"></div>';
                        echo $message_form;
                    }

                // chat list
                } else {
                    echo "<h3 class='card-title'>Messages</h3>";

                    $data = $msg_class->read_threads();

                    if (is_array($data)) {
                        foreach ($data as $MESSAGE) {
                            $other_id = ($MESSAGE['sender'] == $me) ? $MESSAGE['receiver'] : $MESSAGE['sender'];
                            $ROW_USER = $user_class->get_user($other_id);

                            include("thread.php");
                        }
                    } else {
                        echo "<div class='empty-state'>You have no messages.</div>";
                    }
                }
                ?>
            </div>
        </div>
    </body>
</html>
