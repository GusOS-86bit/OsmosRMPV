<?php
// small user card, needs $FRIEND_ROW
if (!defined("ROOT")) { http_response_code(404); die; }

if (!is_array($FRIEND_ROW)) {
    return;
}

$friend_image = avatar_url($FRIEND_ROW);
$friend_link = (($FRIEND_ROW['type'] ?? "") == "group" ? "group/" : "profile/") . $FRIEND_ROW['userid'];

$online = "Last seen: <br> Unknown";
if (isset($FRIEND_ROW['online']) && $FRIEND_ROW['online'] > 0) {
    $online_time = $FRIEND_ROW['online'];
    $current_time = time();
    if (($current_time - $online_time) < (60 * 2)) {
        $online = "<span style='color:green;'>Online</span>";
    } else {
        $online = "Last seen: <br>" . Time::get_time(date("Y-m-d H:i:s", $online_time));
    }
}
?>
<div class="friends">
    <a href="<?php echo ROOT . $friend_link; ?>">
        <img class="friends-img" src="<?php echo ROOT . $friend_image; ?>">
        <?php echo htmlspecialchars(trim($FRIEND_ROW['first_name'] . " " . $FRIEND_ROW['last_name'])); ?>
        <span class="friends-status"><?php echo $online; ?></span>
    </a>
</div>
