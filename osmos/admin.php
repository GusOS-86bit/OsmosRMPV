<?php
// posts/comments get deleted with the normal Delete link, not here
include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);
$me = $user_data['userid'];

if (!is_admin($me)) {
    set_flash("You don't have access to that page.", "error");
    redirect(ROOT . "home");
}

$DB = new Database();
$user_class = new User();
$group_class = new Group();
$section = (($URL[1] ?? "") == "groups") ? "groups" : "users";

// csrf token is already checked in autoload
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $action = $_POST['action'] ?? "";

    if ($action == "delete_group") {
        $group = $group_class->get_group($_POST['group_id'] ?? "");
        if ($group && $group_class->delete_group($group['userid'])) {
            set_flash("The group \"" . htmlspecialchars($group['first_name']) . "\" was deleted.");
        }
        redirect(ROOT . "admin/groups");
    }

    $target = $user_class->get_user($_POST['user_id'] ?? "");

    if (!$target || ($target['type'] ?? "") == "group") {
        set_flash("That user was not found.", "error");
    } elseif ($target['userid'] == $me) {
        set_flash("You can't change your own account here.", "error");
    } else {
        $id = esc($target['userid']);
        $name = htmlspecialchars($target['first_name'] . " " . $target['last_name']);

        if ($action == "block") {
            if (!empty($target['is_admin'])) {
                set_flash("Remove the admin rights first, then block the user.", "error");
            } else {
                $DB->save("UPDATE users SET blocked = 1 WHERE userid = '$id' LIMIT 1");
                set_flash($name . " was blocked and can no longer log in.");
            }
        } elseif ($action == "unblock") {
            $DB->save("UPDATE users SET blocked = 0 WHERE userid = '$id' LIMIT 1");
            set_flash($name . " was unblocked.");
        } elseif ($action == "make_admin") {
            if (!empty($target['blocked'])) {
                set_flash("A blocked user can't be an administrator.", "error");
            } else {
                $DB->save("UPDATE users SET is_admin = 1 WHERE userid = '$id' LIMIT 1");
                set_flash($name . " is now an administrator.");
            }
        } elseif ($action == "remove_admin") {
            $DB->save("UPDATE users SET is_admin = 0 WHERE userid = '$id' LIMIT 1");
            set_flash($name . " is no longer an administrator.");
        } elseif ($action == "reset_password") {
            // temp password, the admin passes it on to the user
            $alphabet = "abcdefghjkmnpqrstuvwxyz23456789";
            $temporary = "";
            for ($i = 0; $i < 10; $i++) {
                $temporary .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $hash = esc(password_hash($temporary, PASSWORD_DEFAULT));
            $DB->save("UPDATE users SET password = '$hash' WHERE userid = '$id' LIMIT 1");
            set_flash("The new temporary password for " . $name . " (@" . htmlspecialchars($target['tag_name']) . ") is <b>" . $temporary . "</b><br>Give it to the user and ask them to change it in Settings.", "warning");
        }
    }

    redirect(return_url(ROOT . "admin"));
}

function admin_count($DB, $sql)
{
    $row = $DB->read($sql);
    return is_array($row) ? (int)$row[0]['total'] : 0;
}

$stats = [
    "Users" => admin_count($DB, "SELECT COUNT(*) AS total FROM users WHERE type = 'profile'"),
    "Posts" => admin_count($DB, "SELECT COUNT(*) AS total FROM posts WHERE parent = 0"),
    "Comments" => admin_count($DB, "SELECT COUNT(*) AS total FROM posts WHERE parent > 0"),
    "Groups" => admin_count($DB, "SELECT COUNT(*) AS total FROM users WHERE type = 'group'"),
    "Blocked" => admin_count($DB, "SELECT COUNT(*) AS total FROM users WHERE type = 'profile' AND blocked = 1"),
];

$find = (isset($_GET['find']) && is_string($_GET['find'])) ? trim($_GET['find']) : "";

if ($section == "users") {
    $where = "type = 'profile'";
    if ($find !== "") {
        $f = esc(addcslashes(ltrim($find, "@"), "%_"));
        $where .= " AND (first_name LIKE '%$f%' OR last_name LIKE '%$f%' OR CONCAT(first_name, ' ', last_name) LIKE '%$f%' OR tag_name LIKE '%$f%')";
    }
    $rows = $DB->read("SELECT * FROM users WHERE $where ORDER BY is_admin DESC, first_name, last_name LIMIT 200");
} else {
    $rows = $DB->read("SELECT * FROM users WHERE type = 'group' ORDER BY id DESC LIMIT 200");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin | Osmos</title>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="page">
        <div class="card">
            <h3 class="card-title">Administration</h3>

            <div class="stat-cards">
                <?php foreach ($stats as $label => $value): ?>
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $value; ?></div>
                        <div class="stat-label"><?php echo $label; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="tabs" style="margin: 20px -20px 15px -20px; text-align: left;">
                <a class="menu-button<?php echo $section == "users" ? " active" : ""; ?>" href="<?php echo ROOT; ?>admin">Users</a>
                <a class="menu-button<?php echo $section == "groups" ? " active" : ""; ?>" href="<?php echo ROOT; ?>admin/groups">Groups</a>
            </div>

            <?php if ($section == "users"): ?>

                <form method="get" action="<?php echo ROOT; ?>admin" style="margin-bottom: 15px; display: flex; gap: 8px;">
                    <input type="text" name="find" class="textbox" placeholder="Search by name or @username" value="<?php echo htmlspecialchars($find); ?>">
                    <input type="submit" class="btn btn-primary" value="Search">
                </form>

                <?php if (is_array($rows)): ?>
                    <table class="admin-table">
                        <tr>
                            <th>User</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo ROOT; ?>profile/<?php echo $row['userid']; ?>" class="admin-user">
                                        <img src="<?php echo ROOT . avatar_url($row); ?>">
                                        <span>
                                            <?php echo htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?><br>
                                            <span class="muted">@<?php echo htmlspecialchars($row['tag_name']); ?></span>
                                        </span>
                                    </a>
                                </td>
                                <td><?php echo date("d.m.Y", strtotime($row['date'])); ?></td>
                                <td>
                                    <?php if (!empty($row['is_admin'])): ?><span class="badge badge-admin">Admin</span><?php endif; ?>
                                    <?php if (!empty($row['blocked'])): ?><span class="badge badge-blocked">Blocked</span><?php endif; ?>
                                    <?php if (empty($row['is_admin']) && empty($row['blocked'])): ?><span class="badge">Active</span><?php endif; ?>
                                </td>
                                <td class="admin-actions">
                                    <?php if ($row['userid'] == $me): ?>
                                        <span class="muted">You</span>
                                    <?php else: ?>
                                        <form method="post">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="user_id" value="<?php echo $row['userid']; ?>">
                                            <button class="btn btn-light btn-small" name="action" value="reset_password" onclick="return confirm('Create a new temporary password for this user?');">Reset password</button>
                                            <?php if (!empty($row['blocked'])): ?>
                                                <button class="btn btn-light btn-small" name="action" value="unblock">Unblock</button>
                                            <?php elseif (empty($row['is_admin'])): ?>
                                                <button class="btn btn-danger btn-small" name="action" value="block" onclick="return confirm('Block this user? They will not be able to log in.');">Block</button>
                                                <button class="btn btn-blue btn-small" name="action" value="make_admin">Make admin</button>
                                            <?php else: ?>
                                                <button class="btn btn-light btn-small" name="action" value="remove_admin">Remove admin</button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <div class="empty-state">No users were found.</div>
                <?php endif; ?>

                <p class="muted" style="font-size: 12px; margin-top: 15px;">Posts and comments are removed with the Delete link under them. Administrators see that link on every post.</p>

            <?php else: ?>

                <?php if (is_array($rows)): ?>
                    <table class="admin-table">
                        <tr>
                            <th>Group</th>
                            <th>Privacy</th>
                            <th>Members</th>
                            <th>Owner</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                        <?php foreach ($rows as $row): ?>
                            <?php $owner = $user_class->get_user($row['owner']); ?>
                            <tr>
                                <td><a href="<?php echo ROOT; ?>group/<?php echo $row['userid']; ?>" style="font-weight: bold; text-decoration: none;"><?php echo htmlspecialchars($row['first_name']); ?></a></td>
                                <td><?php echo ($row['group_type'] == "private") ? "Private" : "Public"; ?></td>
                                <td><?php echo $group_class->count_members($row['userid']); ?></td>
                                <td><?php echo $owner ? htmlspecialchars($owner['first_name'] . " " . $owner['last_name']) : "-"; ?></td>
                                <td><?php echo date("d.m.Y", strtotime($row['date'])); ?></td>
                                <td class="admin-actions">
                                    <form method="post">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="group_id" value="<?php echo $row['userid']; ?>">
                                        <button class="btn btn-danger btn-small" name="action" value="delete_group" onclick="return confirm('Delete this group with all its posts?');">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <div class="empty-state">There are no groups yet.</div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>
