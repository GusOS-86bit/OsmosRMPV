<?php
// likes/post/ID - list of people who liked it

include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);
$USER = $user_data;

$Post = new Post();
$likes = false;
$can_view = true;

$type = $URL[1] ?? "";
if (in_array($type, ["post", "comment"], true) && isset($URL[2]) && is_numeric($URL[2])) {
    $row = $Post->get_one_post($URL[2]);
    $can_view = $row && $Post->can_view_post($row, $user_data['userid']);
    if ($can_view) {
        $likes = $Post->get_likes($URL[2], $type);
    }
}

$User = new User();
$image_class = new Image();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>People who liked | Osmos</title>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="page">
        <div class="card" style="text-align: center;">
            <h3 class="card-title text-left">People who liked this</h3>
            <?php
                if (is_array($likes)) {
                    foreach ($likes as $row) {
                        $FRIEND_ROW = $User->get_user($row['user_id'] ?? 0);
                        if ($FRIEND_ROW) {
                            include("user.php");
                        }
                    }
                } else {
                    echo "<div class='empty-state'>No information was found</div>";
                }
            ?>
            <?php if (isset($URL[2]) && is_numeric($URL[2]) && $can_view): ?>
                <div class="text-left" style="margin-top: 15px;"><a href="<?php echo ROOT; ?>single_post/<?php echo $URL[2]; ?>" style="text-decoration: none;">&larr; Back to the post</a></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
