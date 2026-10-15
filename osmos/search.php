<?php
    include_once("classes/autoload.php");

    $login = new Login();
    $user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);

    $people = false;
    $groups = false;
    $find = (isset($_GET['find']) && is_string($_GET['find'])) ? trim($_GET['find']) : "";

    // #php goes to the tag page
    if (preg_match("/^#([a-zA-Z_][a-zA-Z0-9_]*)$/", $find, $match)) {
        header("Location: " . ROOT . "tags?tag=" . urlencode($match[1]));
        die;
    }

    // so @janis works too
    $find = ltrim($find, "@");

    if ($find !== "")
    {
        $DB = new Database();
        // escape % and _ for LIKE
        $f = esc(addcslashes($find, "%_"));

        $people = $DB->read("select * from users
                             where (type is null or type = '' or type = 'profile') and blocked = 0
                             and (first_name like '%$f%' or last_name like '%$f%' or concat(first_name, ' ', last_name) like '%$f%' or tag_name like '%$f%')
                             limit 30");

        $groups = $DB->read("select * from users where type = 'group' and first_name like '%$f%' limit 12");
    }

    $image_class = new Image();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Search | Osmos</title>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="page">

        <div class="card" style="text-align: center;">
            <h3 class="card-title text-left">
                <?php echo ($find !== "") ? "People matching &ldquo;" . htmlspecialchars($find) . "&rdquo;" : "Search"; ?>
            </h3>
            <?php
                if ($find === "") {
                    echo "<div class='empty-state'>Type a name in the search box at the top.</div>";
                } elseif (is_array($people)) {
                    foreach ($people as $FRIEND_ROW) {
                        include("user.php");
                    }
                } else {
                    echo "<div class='empty-state'>No people were found</div>";
                }
            ?>
        </div>

        <?php if (is_array($groups)): ?>
            <div class="card">
                <h3 class="card-title">Groups</h3>
                <div class="group-grid">
                    <?php
                        $GROUP_ROLE = "";
                        foreach ($groups as $GROUP_ROW) {
                            include("group.inc.php");
                        }
                    ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
