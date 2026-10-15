<?php
include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);

$group = new Group();
$ERROR = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $result = $group->evaluate($_POST);

    if ($result != "") {
        $ERROR = $result;
    } else {
        set_flash("Your group was created!");
        header("Location: " . ROOT . "group/" . $group->created_id);
        die;
    }
}

$old_name = (isset($_POST['group_name']) && is_string($_POST['group_name'])) ? $_POST['group_name'] : "";
$old_about = (isset($_POST['about']) && is_string($_POST['about'])) ? $_POST['about'] : "";
$old_type = (isset($_POST['group_type']) && $_POST['group_type'] == "private") ? "private" : "public";
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Create Group | Osmos</title>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="page">
        <div class="card" style="max-width: 520px; margin: 0 auto;">
            <h3 class="card-title">Create a New Group</h3>

            <?php if ($ERROR != ""): ?>
                <div class="alert alert-error"><?php echo $ERROR; ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <?php echo csrf_field(); ?>

                <div class="form-row">
                    <label for="group_name">Group name</label>
                    <input value="<?php echo htmlspecialchars($old_name); ?>" type="text" class="textbox" id="group_name" name="group_name" maxlength="50" placeholder="Group Name" autofocus required>
                </div>

                <div class="form-row">
                    <label for="about">Description <span class="muted" style="font-weight: normal;">(optional)</span></label>
                    <textarea class="textbox" id="about" name="about" maxlength="1024" placeholder="What is this group about?" style="height: 90px;"><?php echo htmlspecialchars($old_about); ?></textarea>
                </div>

                <div class="form-row">
                    <label for="group_type">Who can see the group?</label>
                    <select class="textbox" id="group_type" name="group_type">
                        <option value="public" <?php echo $old_type == "public" ? "selected" : ""; ?>>Public: anyone can see members and posts</option>
                        <option value="private" <?php echo $old_type == "private" ? "selected" : ""; ?>>Private: only members see posts</option>
                    </select>
                    <div class="form-hint">A cover picture is optional. You can add one later.</div>
                </div>

                <input type="submit" class="btn btn-primary btn-block" value="Create Group" style="padding: 11px; font-size: 14px;">
            </form>
        </div>
    </div>
</body>
</html>
