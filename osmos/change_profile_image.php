<?php
// urls: /profile, /cover, /cover/GROUPID, /profile/post/POSTID, /cover/post/POSTID
include_once("classes/autoload.php");

$login = new Login();
$user_data = $login->check_login($_SESSION['osmos_userid'] ?? 0);
$me = $user_data['userid'];
$DB = new Database();
$Post = new Post();
$image = new Image();

$mode = (isset($URL[1]) && $URL[1] == "cover") ? "cover" : "profile";

$target_id = $me;
$is_group = false;
$group_data = false;
$source_post = false;
$ERROR = "";

if (isset($URL[2]) && $URL[2] == "post") {
    // reuse one of my photos
    $source_post = (isset($URL[3]) && is_numeric($URL[3])) ? $Post->get_one_post($URL[3]) : false;
    if (!$source_post || $source_post['user_id'] != $me || !is_jpeg_file($source_post['image'])) {
        set_flash("That picture can't be used.", "error");
        redirect(ROOT . "profile/" . $me);
    }
} elseif ($mode == "cover" && isset($URL[2]) && is_numeric($URL[2])) {
    // group cover, owner and admins only
    $group_class = new Group();
    $group_data = $group_class->get_group($URL[2]);

    if (is_array($group_data) && group_access($me, $group_data, 'admin')) {
        $target_id = $group_data['userid'];
        $is_group = true;
    } else {
        set_flash("You can't change this group's cover.", "error");
        redirect(is_array($group_data) ? ROOT . "group/" . $group_data['userid'] : ROOT . "home");
    }
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $filename = "";
    $reused = false;

    if ($source_post) {
        // point to the same file, no copy and no new post
        // delete_upload_if_unused() leaves it alone while it's a profile/cover pic
        $current = ($mode == "cover") ? $user_data['cover_image'] : $user_data['profile_image'];
        if ($current == $source_post['image']) {
            set_flash("That picture is already your " . ($mode == "cover" ? "cover image." : "profile picture."), "info");
            redirect(ROOT . "profile/" . $me);
        }
        $filename = $source_post['image'];
        $reused = true;
    } else {
        $upload = store_upload($_FILES['file'] ?? null, $target_id, ["image/jpeg" => "jpg"]);
        if ($upload['error'] != "") {
            $ERROR = $upload['error'];
        } elseif ($upload['path'] == "") {
            $ERROR = "Please select an image file first!<br>";
        }
        $filename = $upload['path'];
    }

    if ($ERROR == "" && $filename != "") {
        $safe_file = esc($filename);
        $safe_target = esc($target_id);

        if ($mode == "cover") {
            $image->get_thumb_cover($filename);
            $DB->save("update users set cover_image = '$safe_file' where userid = '$safe_target' limit 1");

            // group covers get posted in the group
            if ($is_group) {
                $Post->create_post($me, ['post' => '', 'is_cover_image' => 1], $filename, $target_id);
                redirect(ROOT . "group/" . $target_id);
            }

            // reused photos are already in the feed
            if (!$reused) {
                $Post->create_post($me, ['post' => '', 'is_cover_image' => 1], $filename);
            }
            set_flash("Your cover image was changed.");
            redirect(ROOT . "profile/" . $me);
        } else {
            $image->get_thumb_profile($filename);
            $DB->save("update users set profile_image = '$safe_file' where userid = '$safe_target' limit 1");
            if (!$reused) {
                $Post->create_post($me, ['post' => '', 'is_profile_image' => 1], $filename);
            }
            set_flash("Your profile picture was changed.");
            redirect(ROOT . "profile/" . $me);
        }
    }
}

if ($is_group) {
    $title = "Change Group Cover";
    $current = is_image_file($group_data['cover_image'] ?? "") ? $image->get_thumb_cover($group_data['cover_image']) : "images/cover_image.JPG";
    $back = ROOT . "group/" . $group_data['userid'];
} elseif ($mode == "cover") {
    $title = "Change Cover Image";
    $current = is_image_file($user_data['cover_image'] ?? "") ? $image->get_thumb_cover($user_data['cover_image']) : "images/cover_image.JPG";
    $back = ROOT . "profile/" . $me;
} else {
    $title = "Change Profile Image";
    $current = avatar_url($user_data);
    $back = ROOT . "profile/" . $me;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?> | Osmos</title>
</head>
<body>
    <?php include("header.php"); ?>
    <div class="page">
        <div class="card" style="text-align: center;">
            <h3 class="card-title text-left"><?php echo $title; ?></h3>

            <?php if ($ERROR != ""): ?>
                <div class="alert alert-error text-left"><?php echo $ERROR; ?></div>
            <?php endif; ?>

            <?php if ($source_post): ?>
                <p>Use this picture as your <?php echo ($mode == "cover") ? "cover image" : "profile picture"; ?>?</p>
                <img src="<?php echo ROOT . $image->get_thumb_post($source_post['image']); ?>" style="width: 250px; height: 250px; object-fit: cover; border-radius: <?php echo ($mode == "cover") ? "6px" : "50%"; ?>;">
                <form method="post" style="margin-top: 15px;">
                    <?php echo csrf_field(); ?>
                    <a class="btn btn-light" href="<?php echo ROOT; ?>image_view/<?php echo $source_post['post_id']; ?>">Cancel</a>
                    <input type="submit" class="btn btn-primary" value="<?php echo ($mode == "cover") ? "Set as Cover Image" : "Set as Profile Picture"; ?>">
                </form>
            <?php else: ?>
                <div class="muted" style="font-size: 12px; margin-bottom: 8px;">Current picture</div>
                <?php if ($mode == "cover"): ?>
                    <img src="<?php echo ROOT . $current; ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 6px;">
                <?php else: ?>
                    <img src="<?php echo ROOT . $current; ?>" style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%;">
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" style="margin-top: 15px;">
                    <?php echo csrf_field(); ?>
                    <input type="file" name="file" accept="image/jpeg">
                    <input type="submit" class="btn btn-primary" value="Upload New Image">
                    <div class="form-hint">JPEG images only.</div>
                </form>
                <div style="margin-top: 15px;"><a href="<?php echo $back; ?>" style="text-decoration: none;">&larr; Back</a></div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
