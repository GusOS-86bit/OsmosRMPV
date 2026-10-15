<?php

include_once("classes/autoload.php");

if (isset($_SESSION['osmos_userid'])) {
    $check = new Login();
    if ($check->check_login($_SESSION['osmos_userid'], false)) {
        header("Location: " . ROOT . "profile");
        die;
    }
}

$username = "";
$ERROR = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $login = new Login();
    $result = $login->evaluate($_POST);

    if ($result != "")
    {
        $ERROR = $result;
    } else {
        header("Location: " . ROOT . "profile");
        die;
    }

    // refill the username, not the password
    $username = (isset($_POST['username']) && is_string($_POST['username'])) ? $_POST['username'] : "";
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Osmos | Log in</title>
</head>
<style>
    * { box-sizing: border-box; }
    #bar {
        height: 100px;
        background-color: #FFC10F;
        color: #000000;
        padding: 4px 20px;
    }
    #signup_button {
        background-color: #800000;
        width: 70px;
        text-align: center;
        padding: 4px;
        border-radius: 4px;
        float: right;
        color: white;
        margin-top: -45px;
    }
    #login_bar {
        background-color: white;
        width: 800px;
        margin: auto;
        margin-top: 50px;
        padding: 10px;
        text-align: center;
        padding-top: 50px;
        padding-bottom: 30px;
        font-weight: bold;
        border-radius: 6px;
    }
    #text {
        height: 40px;
        width: 300px;
        border-radius: 4px;
        border: solid 1px #aaa;
        padding: 4px 8px;
        font-size: 14px;
    }
    #text:focus { border-color: #e67e22; outline: none; }
    #button {
        width: 300px;
        height: 40px;
        border-radius: 4px;
        font-weight: bold;
        border: none;
        background-color: #800000;
        color: white;
        cursor: pointer;
    }
    .alert { width: 300px; margin: 0 auto 15px auto; padding: 10px; border-radius: 4px; font-size: 12px; font-weight: normal; line-height: 1.5; }
    .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .alert-success, .alert-info { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
</style>
<body style="font-family: tahoma, sans-serif; background-color: #e9ebee; margin: 0;">
    <div id="bar">
        <div style="font-size: 40px;">Osmos</div>
        <a href="<?php echo ROOT; ?>signup">
            <div id="signup_button">Signup</div>
        </a>
    </div>

    <div id="login_bar">
        <form method="post">
            <?php echo csrf_field(); ?>
            Log in to Osmos <br><br>

            <?php render_flash(); ?>
            <?php if ($ERROR != ""): ?>
                <div class="alert alert-error"><?php echo $ERROR; ?></div>
            <?php endif; ?>

            <input name="username" value="<?php echo htmlspecialchars($username); ?>" type="text" id="text" placeholder="Username" autocomplete="username" autocapitalize="none"><br><br>
            <input name="password" type="password" id="text" placeholder="Password" autocomplete="current-password"><br><br>
            <input type="submit" id="button" value="Login"><br><br>
        </form>
    </div>
</body>
</html>
