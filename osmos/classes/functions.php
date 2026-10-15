<?php

// routing, redirects, flash messages

function split_url()
{
    $url = (isset($_GET['url']) && is_string($_GET['url']) && $_GET['url'] != "") ? $_GET['url'] : "home";
    $url = explode("/", filter_var(trim($url, "/"), FILTER_SANITIZE_URL));

    return $url;
}

function split_url_from_string($str)
{
    $url = explode("/", filter_var(trim($str, "/"), FILTER_SANITIZE_URL));
    return $url;
}

// only redirect to our own pages
function safe_return_url($url, $fallback = "")
{
    if ($fallback == "") {
        $fallback = ROOT . "home";
    }

    if (is_string($url) && $url !== "" && strpos($url, ROOT) === 0) {
        return $url;
    }

    return $fallback;
}

function return_url($fallback = "")
{
    return safe_return_url($_SERVER['HTTP_REFERER'] ?? "", $fallback);
}

function redirect($url)
{
    header("Location: " . $url);
    die;
}

function redirect_back($fallback = "")
{
    redirect(return_url($fallback));
}

// one-time message shown on the next page
function set_flash($message, $type = "success")
{
    $_SESSION['flash'] = ["message" => $message, "type" => $type];
}

function render_flash()
{
    if (empty($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    $type = in_array($flash['type'], ["success", "error", "warning", "info"]) ? $flash['type'] : "info";
    // not escaped on purpose, flash messages are ours and some have <br>
    echo "<div class='alert alert-" . $type . "'>" . $flash['message'] . "</div>";
}



function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_url($url)
{
    return $url . (strpos($url, "?") === false ? "?" : "&") . "token=" . csrf_token();
}

function csrf_check($token)
{
    return is_string($token)
        && $token !== ""
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// helpers

function pagination_link()
{
    $page_number = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($page_number < 1) $page_number = 1;

    $next_page = $page_number + 1;
    $prev_page = $page_number - 1;
    if ($prev_page < 1) $prev_page = 1;

    $path = (isset($_GET['url']) && is_string($_GET['url']) && $_GET['url'] != "") ? $_GET['url'] : "home";
    $url = ROOT . htmlspecialchars($path, ENT_QUOTES);

    // keep other params like ?find=
    $params = $_GET;
    unset($params['url'], $params['page']);

    $query = http_build_query($params);
    $url .= "?" . ($query != "" ? htmlspecialchars($query, ENT_QUOTES) . "&amp;" : "") . "page=";

    return [
        'next' => $url . $next_page,
        'prev' => $url . $prev_page
    ];
}

function i_own_content($row)
{
    $myid = $_SESSION['osmos_userid'] ?? 0;
    if (!$myid || !is_array($row)) {
        return false;
    }
    if (isset($row['gender']) && ($row['type'] ?? '') == 'profile' && $myid == $row['userid']) {
        return true;
    }
    if (isset($row['gender']) && ($row['type'] ?? '') == 'group' && $myid == $row['owner']) {
        return true;
    }
    if (isset($row['post_id']) && $myid == $row['user_id']) {
        return true;
    }
    return false;
}

function esc($value)
{
    $DB = new Database();
    return $DB->escape($value);
}

// random 12-17 digit id that isn't taken yet
// 19 digits can overflow BIGINT
function generate_unique_id($table, $column)
{
    $DB = new Database();
    $id = "";

    for ($attempt = 0; $attempt < 20; $attempt++) {
        $length = random_int(12, 17);
        $id = (string)random_int(1, 9);
        for ($i = 1; $i < $length; $i++) {
            $id .= random_int(0, 9);
        }

        $check = $DB->read("SELECT 1 FROM `$table` WHERE `$column` = '$id' LIMIT 1");
        if (empty($check)) {
            return $id;
        }
    }

    return $id;
}

// ucfirst() that also works with ā, ģ, š
function osmos_ucfirst($text)
{
    $text = (string)$text;
    return mb_strtoupper(mb_substr($text, 0, 1)) . mb_substr($text, 1);
}

// "Jānis Bērziņš" -> "janisberzins"
function ascii_slug($text)
{
    $ascii = function_exists('iconv') ? @iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", (string)$text) : (string)$text;
    if ($ascii === false) {
        $ascii = (string)$text;
    }
    return preg_replace("/[^a-z0-9_]/", "", strtolower($ascii));
}

// 3-20 chars, a-z 0-9 and _, starts with a letter
define("USERNAME_PATTERN", "/^[a-z][a-z0-9_]{2,19}$/");

// @username, but not the @ in an email
define("MENTION_PATTERN", "/(^|[^\w&@])@([a-zA-Z0-9_]+)/");

// #php, #school_2026 but not C# or &#039;
define("HASHTAG_PATTERN", "/(^|[^\w&#])#([a-zA-Z_][a-zA-Z0-9_]*)/");

function extract_hashtags($text)
{
    $matches = [];
    preg_match_all(HASHTAG_PATTERN, (string)$text, $matches);
    return array_values(array_unique(array_map('strtolower', $matches[2])));
}

// ["php" => 5, "mysql" => 3], only counts posts the viewer can see
function popular_hashtags($viewer_id, $limit = 8)
{
    $DB = new Database();
    $posts = $DB->read("SELECT * FROM posts WHERE post LIKE '%#%' ORDER BY id DESC LIMIT 300");
    if (!is_array($posts)) {
        return [];
    }

    $post_class = new Post();
    $counts = [];
    foreach ($posts as $row) {
        if (!$post_class->can_view_post($row, $viewer_id)) {
            continue;
        }
        foreach (extract_hashtags($row['post']) as $tag) {
            $counts[$tag] = ($counts[$tag] ?? 0) + 1;
        }
    }

    arsort($counts);
    return array_slice($counts, 0, $limit, true);
}

define("PASSWORD_MIN_LENGTH", 6);

// "" if it's fine, otherwise the error
function username_error($username, $exclude_userid = 0)
{
    if ($username === "") {
        return "Username is empty!<br>";
    }

    if (!preg_match(USERNAME_PATTERN, $username)) {
        return "The username must be 3 to 20 characters long, start with a letter and contain only lowercase letters (a-z), numbers and _<br>";
    }

    $DB = new Database();
    $safe_name = esc($username);
    $safe_id = esc($exclude_userid);
    $taken = $DB->read("SELECT id FROM users WHERE tag_name = '$safe_name' AND userid != '$safe_id' LIMIT 1");

    if (!empty($taken)) {
        return "That username is already taken!<br>";
    }

    return "";
}

// for "updated his/her/their profile image"
function pronoun($user)
{
    $gender = is_array($user) ? strtolower((string)($user['gender'] ?? "")) : "";
    if ($gender == "female") return "her";
    if ($gender == "male") return "his";
    return "their";
}

// "You and 2 others liked this post" etc.
function likes_info_text($likes_count, $i_liked)
{
    $likes_count = (int)$likes_count;
    if ($likes_count < 1) {
        return "";
    }

    if ($likes_count == 1) {
        return $i_liked ? "You liked this post" : "1 person liked this post";
    }

    if ($i_liked) {
        $others = $likes_count - 1;
        return "You and " . $others . " other" . ($others == 1 ? "" : "s") . " liked this post";
    }

    return $likes_count . " people liked this post";
}

// images and uploads

function is_image_file($path)
{
    if (empty($path) || !is_string($path) || !is_file($path)) {
        return false;
    }
    $info = @getimagesize($path);
    return $info && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF]);
}

function is_jpeg_file($path)
{
    if (empty($path) || !is_string($path) || !is_file($path)) {
        return false;
    }
    $info = @getimagesize($path);
    return $info && $info[2] == IMAGETYPE_JPEG;
}

function is_video_file($path)
{
    return !empty($path) && is_string($path) && is_file($path) && strtolower(pathinfo($path, PATHINFO_EXTENSION)) == "mp4";
}

// image path without ROOT, falls back to the placeholders
function avatar_url($row)
{
    $image = "images/placeholder_male.jpg";

    if (!is_array($row)) {
        return $image;
    }

    $image_class = new Image();

    if (($row['type'] ?? "") == "group") {
        if (is_image_file($row['cover_image'] ?? "")) {
            $thumb = $image_class->get_thumb_profile($row['cover_image']);
            if (is_image_file($thumb)) {
                return $thumb;
            }
        }
        return "images/cover_image.JPG";
    }

    if (strtolower((string)($row['gender'] ?? "")) == "female") {
        $image = "images/placeholder_female.jpg";
    }

    if (is_image_file($row['profile_image'] ?? "")) {
        $thumb = $image_class->get_thumb_profile($row['profile_image']);
        if (is_image_file($thumb)) {
            $image = $thumb;
        }
    }

    return $image;
}

// don't trust the type the browser sends
function detect_mime($path)
{
    $mime = "";

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = (string)finfo_file($finfo, $path);
            finfo_close($finfo);
        }
    }

    if ($mime == "" || $mime == "application/octet-stream") {
        $info = @getimagesize($path);
        if ($info && !empty($info['mime'])) {
            $mime = $info['mime'];
        } elseif (@file_get_contents($path, false, null, 4, 4) === "ftyp") {
            $mime = "video/mp4";
        }
    }

    if ($mime == "application/mp4") {
        $mime = "video/mp4";
    }

    return $mime;
}

// creates uploads/<id>/, false if it's not writable
// on mac/linux uploads/ needs write permissions
function prepare_upload_folder($owner_id)
{
    if (!is_numeric($owner_id)) {
        return false;
    }

    $folder = "uploads/" . $owner_id . "/";
    if (!is_dir($folder)) {
        if (!@mkdir($folder, 0777, true)) {
            error_log("Osmos: could not create " . $folder . " (check the permissions of the uploads folder)");
            return false;
        }
        @chmod($folder, 0777);
        @file_put_contents($folder . "index.php", "");
    }

    return is_writable($folder) ? $folder : false;
}

// $allowed is mime => extension, e.g. ["image/jpeg" => "jpg"]
// returns ["path" => ..., "error" => ...]
function store_upload($file, $owner_id, $allowed)
{
    $result = ["path" => "", "error" => ""];

    if (!is_array($file) || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE || empty($file['name'])) {
        return $result; // nothing was uploaded
    }

    if (is_array($file['name'])) {
        $result['error'] = "Please upload one file at a time.<br>";
        return $result;
    }

    $labels = ["jpg" => "JPEG", "png" => "PNG", "mp4" => "MP4"];
    $allowed_text = implode(" and ", array_unique(array_map(function ($ext) use ($labels) {
        return $labels[$ext] ?? strtoupper($ext);
    }, array_values($allowed))));

    if ($file['error'] !== UPLOAD_ERR_OK) {
        if ($file['error'] == UPLOAD_ERR_INI_SIZE || $file['error'] == UPLOAD_ERR_FORM_SIZE) {
            $result['error'] = "That file is too large.<br>";
        } else {
            $result['error'] = "The upload failed, please try again.<br>";
        }
        return $result;
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        $result['error'] = "Invalid upload.<br>";
        return $result;
    }

    $mime = detect_mime($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        $result['error'] = "Only " . $allowed_text . " files are allowed!<br>";
        return $result;
    }

    // has to actually open as an image
    if (strpos($mime, "image/") === 0 && !@getimagesize($file['tmp_name'])) {
        $result['error'] = "That image file is damaged.<br>";
        return $result;
    }

    if (!is_numeric($owner_id)) {
        $result['error'] = "Invalid upload folder.<br>";
        return $result;
    }

    $folder = prepare_upload_folder($owner_id);
    if ($folder === false) {
        $result['error'] = "The server can't save uploaded files. The uploads folder must be writable.<br>";
        return $result;
    }

    $image_class = new Image();
    $path = $folder . $image_class->generate_filename(15) . "." . $allowed[$mime];

    if (!@move_uploaded_file($file['tmp_name'], $path)) {
        error_log("Osmos: could not move upload to " . $path);
        $result['error'] = "The server can't save uploaded files. The uploads folder must be writable.<br>";
        return $result;
    }

    if ($allowed[$mime] == "jpg") {
        $image_class->resize_image($path, $path, 1500, 1500);
    }

    $result['path'] = $path;
    return $result;
}

// profile/cover posts share the file with the profile
function delete_upload_if_unused($path, $ignore_post_id = 0)
{
    if (empty($path) || !is_string($path) || strpos($path, "uploads/") !== 0 || !is_file($path)) {
        return;
    }

    $DB = new Database();
    $p = esc($path);
    $ignore = esc($ignore_post_id);

    if ($DB->read("SELECT id FROM users WHERE profile_image = '$p' OR cover_image = '$p' LIMIT 1")) {
        return;
    }
    if ($DB->read("SELECT id FROM posts WHERE image = '$p' AND post_id != '$ignore' LIMIT 1")) {
        return;
    }

    @unlink($path);
    @unlink($path . "_post_thumb_.jpg");
    @unlink($path . "_cover_thumb_.jpg");
    @unlink($path . "_profile_thumb.jpg");
}

// notifications

function add_notification($userid, $activity, $post_data)
{
    if (empty($post_data)) return;
    if (is_object($post_data)) $post_data = (array)$post_data;
    if (!is_array($post_data)) return;

    $DB = new Database();
    $content_id = 0;
    $content_owner = 0;
    $content_type = "";

    if ($activity == 'like' || $activity == 'comment' || $activity == 'tag') {
        if (isset($post_data['postid']) || isset($post_data['post_id'])) {
            $content_id = $post_data['postid'] ?? $post_data['post_id'];
            $content_owner = $post_data['userid'] ?? $post_data['user_id'] ?? 0;

            $content_type = "post";
            if ($activity == 'comment' || (isset($post_data['parent']) && $post_data['parent'] > 0)) {
                $content_type = "comment";
            }
        }
    } else if ($activity == 'friend_request' || $activity == 'friend_accept') {
        // sender = who did it, userid = who gets notified
        $content_id = $post_data['sender'] ?? 0;
        $content_owner = $post_data['userid'] ?? 0;
        $content_type = "profile";
    } else if ($activity == 'invite' || $activity == 'role') {
        // $post_data is the group row, target_user gets notified
        $content_id = $post_data['userid'] ?? 0;
        $content_owner = $post_data['target_user'] ?? 0;
        $content_type = "group";
    }

    $userid = esc($userid);
    $activity = esc($activity);
    $content_id = esc($content_id);
    $content_owner = esc($content_owner);
    $content_type = esc($content_type);

    // no notifications for your own stuff
    if ($userid != $content_owner && $content_owner > 0 && $content_id > 0) {

        // only once, so unlike + like doesn't spam
        if (in_array($activity, ['like', 'tag', 'invite', 'friend_request'])) {
            $check = $DB->read("SELECT id FROM notifications WHERE user_id = '$userid' AND activity = '$activity' AND content_id = '$content_id' AND content_owner = '$content_owner' LIMIT 1");
            if (!empty($check)) {
                return;
            }
        }

        $date = date("Y-m-d H:i:s");
        $query = "insert into notifications (user_id, activity, content_owner, content_id, content_type, date)
                  values ('$userid', '$activity', '$content_owner', '$content_id', '$content_type', '$date')";
        $DB->save($query);
    }
}

function notification_seen($id)
{
    if (!is_numeric($id)) {
        return;
    }

    $id = esc($id);
    $user_id = esc($_SESSION['osmos_userid'] ?? 0);
    if (!$user_id) {
        return;
    }

    $DB = new Database();
    $query = "select id from notification_seen where userid = '$user_id' && notification_id = '$id' limit 1";
    $check = $DB->read($query);

    if (empty($check)) {
        $query = "insert into notification_seen (userid, notification_id) values ('$user_id', '$id')";
        $DB->save($query);
    }
}

function content_i_follow($user_id, $row)
{
    $content_id = 0;
    $content_type = "";
    $date = date("Y-m-d H:i:s");

    if (isset($row['postid'])) {
        $content_id = $row['postid'];
        $content_type = "post";

        if (isset($row['parent']) && $row['parent'] > 0) {
            $content_type = "comment";
        }
    } elseif (isset($row['post_id'])) {
        $content_id = $row['post_id'];
        $content_type = "post";

        if (isset($row['parent']) && $row['parent'] > 0) {
            $content_type = "comment";
        }
    } elseif (isset($row['userid'])) {
        $content_id = $row['userid'];
        $content_type = "profile";
    }

    $user_id = esc($user_id);
    $content_id = esc($content_id);
    $content_type = esc($content_type);

    if ($user_id > 0 && $content_id > 0) {
        $DB = new Database();

        $query = "INSERT IGNORE INTO content_i_follow (user_id, content_id, content_type, disabled, date)
                  VALUES ('$user_id', '$content_id', '$content_type', 0, '$date')";

        $DB->save($query);
    }
}

// used for the badge count and the notifications page
// my content + new comments on posts I follow
function notifications_where($user_id)
{
    $DB = new Database();
    $user_id = esc($user_id);

    $follow = [];
    $i_follow = $DB->read("select content_id from content_i_follow where disabled = 0 && user_id = '$user_id' limit 100");
    if (is_array($i_follow)) {
        $follow = array_map('esc', array_column($i_follow, "content_id"));
    }

    $where = "n.user_id != '$user_id' AND (n.content_owner = '$user_id'";
    if (count($follow) > 0) {
        $where .= " OR (n.activity = 'comment' AND n.content_id IN ('" . implode("','", $follow) . "'))";
    }
    $where .= ")";

    return $where;
}

function check_notifications()
{
    $DB = new Database();
    $user_id = $_SESSION['osmos_userid'] ?? 0;

    if (empty($user_id)) {
        return 0;
    }

    $safe_id = esc($user_id);
    $query = "SELECT COUNT(n.id) as total
              FROM notifications n
              LEFT JOIN notification_seen ns
              ON n.id = ns.notification_id AND ns.userid = '$safe_id'
              WHERE " . notifications_where($user_id) . "
              AND ns.id IS NULL";

    $data = $DB->read($query);

    if (is_array($data) && isset($data[0]['total'])) {
        return (int)$data[0]['total'];
    }

    return 0;
}

// online status

function set_online($id)
{
    if (!is_numeric($id)) {
        return;
    }

    $online = time();
    $query = "update users set online = '$online' where userid = '$id' limit 1";

    $DB = new Database();
    $DB->save($query);
}

// runs on every request
if (isset($_SESSION['osmos_userid'])) {
    set_online($_SESSION['osmos_userid']);
}

function show($data)
{
    echo "<pre>";
    print_r($data);
    echo "</pre>";
}

// messages

function check_messages()
{
    $me = $_SESSION['osmos_userid'] ?? 0;
    if (empty($me)) {
        return 0;
    }

    $DB = new Database();
    $me = esc($me);

    $query = "SELECT COUNT(id) AS total FROM messages WHERE receiver = '$me' AND deleted_receiver = 0 AND seen = 0";
    $data = $DB->read($query);

    if (is_array($data)) {
        return (int)$data[0]['total'];
    }
    return 0;
}

function check_seen_thread($msgid)
{
    $me = $_SESSION['osmos_userid'] ?? 0;
    if (empty($me)) {
        return 0;
    }

    $DB = new Database();
    $me = esc($me);
    $msgid = esc($msgid);

    $query = "SELECT COUNT(id) AS total FROM messages WHERE receiver = '$me' AND deleted_receiver = 0 AND seen = 0 AND msgid = '$msgid'";
    $data = $DB->read($query);

    if (is_array($data)) {
        return (int)$data[0]['total'];
    }
    return 0;
}

// by sender, because one chat can have more than one msgid
function check_unread_from($sender_id)
{
    $me = $_SESSION['osmos_userid'] ?? 0;
    if (empty($me)) {
        return 0;
    }

    $DB = new Database();
    $me = esc($me);
    $sender_id = esc($sender_id);

    $data = $DB->read("SELECT COUNT(id) AS total FROM messages WHERE receiver = '$me' AND sender = '$sender_id' AND deleted_receiver = 0 AND seen = 0");

    if (is_array($data)) {
        return (int)$data[0]['total'];
    }
    return 0;
}

// admin

function is_admin($user_id)
{
    static $cache = [];

    if (empty($user_id) || !is_numeric($user_id)) {
        return false;
    }

    if (!array_key_exists($user_id, $cache)) {
        $DB = new Database();
        $id = esc($user_id);
        $row = $DB->read("SELECT is_admin FROM users WHERE userid = '$id' AND blocked = 0 LIMIT 1");
        $cache[$user_id] = is_array($row) && $row[0]['is_admin'] == 1;
    }

    return $cache[$user_id];
}

// groups

function group_access($user_id, $group_data, $access)
{
    if (empty($user_id) || !is_array($group_data) || empty($group_data['userid'])) {
        return false;
    }

    if (isset($group_data['owner']) && $group_data['owner'] == $user_id) {
        return true;
    }

    $DB = new Database();
    $group_id = esc($group_data['userid']);
    $user_id = esc($user_id);

    if ($access == "member") {
        $query = "SELECT userid FROM group_members WHERE groupid = '$group_id' AND userid = '$user_id' AND disabled = 0 AND (role = 'member' OR role = 'admin' OR role = 'moderator') LIMIT 1";
        $data = $DB->read($query);
        if (is_array($data)) return true;
    }
    elseif ($access == "moderator") {
        $query = "SELECT userid FROM group_members WHERE groupid = '$group_id' AND userid = '$user_id' AND disabled = 0 AND (role = 'admin' OR role = 'moderator') LIMIT 1";
        $data = $DB->read($query);
        if (is_array($data)) return true;
    }
    elseif ($access == "admin") {
        $query = "SELECT userid FROM group_members WHERE groupid = '$group_id' AND userid = '$user_id' AND disabled = 0 AND role = 'admin' LIMIT 1";
        $data = $DB->read($query);
        if (is_array($data)) return true;
    }
    // not really access, just "has a pending request"
    elseif ($access == "request") {
        $query = "SELECT userid FROM group_requests WHERE groupid = '$group_id' AND userid = '$user_id' AND disabled = 0 LIMIT 1";
        $data = $DB->read($query);
        if (is_array($data)) return true;
    }

    return false;
}

function group_can_view($user_id, $group_data)
{
    if (!is_array($group_data) || empty($group_data['userid'])) {
        return false;
    }

    if (($group_data['group_type'] ?? "public") != "private") {
        return true;
    }

    if (is_admin($user_id)) {
        return true;
    }

    return group_access($user_id, $group_data, 'member');
}
