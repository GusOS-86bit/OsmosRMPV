<?php
    include_once("classes/autoload.php");

    $first_name = "";
    $last_name = "";
    $username = "";
    $gender = "";
    $ERROR = "";

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $signup = new Signup();
        $result = $signup->evaluate($_POST);

        if($result != "") {
            $ERROR = $result;
        } else {
            set_flash("Your account was created. You can log in now.");
            header("Location: " . ROOT . "login");
            die;
        }

        $first_name = (isset($_POST['first_name']) && is_string($_POST['first_name'])) ? $_POST['first_name'] : "";
        $last_name = (isset($_POST['last_name']) && is_string($_POST['last_name'])) ? $_POST['last_name'] : "";
        $username = (isset($_POST['username']) && is_string($_POST['username'])) ? $_POST['username'] : "";
        $gender = (isset($_POST['gender']) && is_string($_POST['gender'])) ? $_POST['gender'] : "";
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Osmos | Sign up</title>
    </head>
    <style>
        * { box-sizing: border-box; }
        #bar{ height:100px; background-color: #FFC10F; color: #000000; padding: 4px 20px; }
        #signup_button{ background-color: #800000; width: 70px; text-align: center; padding: 4px; border-radius: 4px; float: right; color: white; margin-top: -45px; }
        #login_bar{ background-color: white; width:800px; margin: auto; margin-top: 50px; padding:10px; text-align: center; padding-top: 50px; padding-bottom: 30px; font-weight: bold; border-radius: 6px; }
        #text{ height : 40px; width : 300px; border-radius:4px; border:solid 1px #aaa; padding: 4px 8px; font-size: 14px; background-color: white; }
        #text:focus { border-color: #e67e22; outline: none; }
        #button{ width: 300px; height: 40px; border-radius: 4px; font-weight: bold; border: none; background-color: #800000; color: white; cursor: pointer; }
        .alert { width: 300px; margin: 0 auto 15px auto; padding: 10px; border-radius: 4px; font-size: 12px; font-weight: normal; text-align: left; line-height: 1.5; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
    <body style="font-family: tahoma, sans-serif; background-color: #e9ebee; margin: 0;">
        <div id="bar">
            <div style="font-size: 40px;">Osmos</div>
            <a href="<?php echo ROOT; ?>login"><div id="signup_button">Log in</div></a>
        </div>

        <div id="login_bar">
            Sign up to Osmos <br><br>

            <?php if ($ERROR != ""): ?>
                <div class="alert alert-error"><b>The following errors occurred:</b><br><?php echo $ERROR; ?></div>
            <?php endif; ?>

            <form method="post" action="">
                <?php echo csrf_field(); ?>
                <input value="<?php echo htmlspecialchars($first_name); ?>" name="first_name" type="text" id="text" placeholder="First Name" maxlength="50"><br><br>
                <input value="<?php echo htmlspecialchars($last_name); ?>" name="last_name" type="text" id="text" placeholder="Last Name" maxlength="50"><br><br>
                <input value="<?php echo htmlspecialchars($username); ?>" name="username" type="text" id="text" placeholder="Username (e.g. janis_b)" maxlength="20" autocomplete="username"><br>
                <span style="font-weight: normal; font-size: 11px; color: #777;">3 to 20 characters: a-z, 0-9 and _, starting with a letter</span><br><br>

                <span style="font-weight: normal;">Gender:</span><br>
                <select name="gender" id="text">
                    <option value="" <?php echo ($gender != "Male" && $gender != "Female") ? "selected" : ""; ?> disabled>Select Gender</option>
                    <option value="Male" <?php echo ($gender == "Male") ? "selected" : ""; ?>>Male</option>
                    <option value="Female" <?php echo ($gender == "Female") ? "selected" : ""; ?>>Female</option>
                </select>
                <br><br>
                <input name="password" type="password" id="text" placeholder="Password (at least 6 characters)" autocomplete="new-password"><br><br>
                <input name="confirm_password" type="password" id="text" placeholder="Confirm Password" autocomplete="new-password"><br><br>
                <input type="submit" id="button" value="Sign up"><br><br>
            </form>
        </div>
    </body>
</html>
