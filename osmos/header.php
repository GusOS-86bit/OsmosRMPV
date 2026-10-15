<?php
if (!defined("ROOT")) { http_response_code(404); die; }

$header_user = false;

if (isset($_SESSION['osmos_userid'])) {
    $header_user_class = new User();
    $header_user = $header_user_class->get_user($_SESSION['osmos_userid']);
}

$corner_image = avatar_url($header_user);

$notif_count = check_notifications();
$msg_notif_count = check_messages();
?>

<style type="text/css">
    :root {
        --osmos-yellow: #FFC10F;      /* top bar, main buttons */
        --osmos-yellow-dark: #e6ac00;
        --osmos-orange: #e67e22;      /* messages */
        --osmos-orange-dark: #cf6d17;
        --osmos-blue: #405d9b;        /* names and links */
        --osmos-red: #d9534f;
        --osmos-bg: #d0d8e4;
        --osmos-border: #e3e3e3;
        --osmos-text: #222222;
        --osmos-muted: #888888;
    }

    * { box-sizing: border-box; }
    body { font-family: tahoma, sans-serif; background-color: var(--osmos-bg); margin: 0; color: var(--osmos-text); }
    a { color: var(--osmos-blue); }
    textarea, input, select, button { font-family: tahoma, sans-serif; }

    /* top bar */
    #orange_bar { height: 50px; background-color: var(--osmos-yellow); color: #000000; padding: 4px; }
    #search_box { width: 230px; height: 26px; border-radius: 4px; border: none; padding: 4px 30px 4px 8px; font-size: 14px; background-color: #fff; background-image: url(<?php echo ROOT; ?>SearchIcon.png); background-repeat: no-repeat; background-position: right 6px center; background-size: 16px 16px; }
    .header-badge { background-color: red; color: white; position: absolute; top: -5px; min-width: 16px; height: 16px; border-radius: 8px; padding: 0 3px; font-size: 11px; text-align: center; line-height: 16px; font-weight: bold; }
    .flash-area { width: 800px; margin: 15px auto 0 auto; }

    /* layout */
    .page { width: 800px; margin: 20px auto; min-height: 400px; }
    .card { background-color: #fff; border-radius: 6px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); margin-bottom: 20px; }
    .card-title { margin: 0 0 15px 0; color: #333; font-size: 18px; border-bottom: 3px solid var(--osmos-yellow); padding-bottom: 10px; }
    .card-subtitle { margin: 0 0 10px 0; color: #333; font-size: 15px; }
    .two-col { display: flex; gap: 20px; align-items: flex-start; margin-top: 20px; }
    .col-side { flex: 1; min-width: 0; }
    .col-main { flex: 2.5; min-width: 0; }
    .empty-state { text-align: center; color: #777; padding: 30px 10px; font-size: 14px; }
    .muted { color: var(--osmos-muted); }
    .text-left { text-align: left; }
    .clear { clear: both; }

    /* buttons */
    .btn { display: inline-block; border: none; padding: 7px 14px; border-radius: 4px; font-weight: bold; font-size: 13px; cursor: pointer; text-decoration: none; line-height: 1.4; vertical-align: middle; }
    .btn-primary { background-color: var(--osmos-yellow); color: #000; }
    .btn-primary:hover { background-color: var(--osmos-yellow-dark); }
    .btn-orange { background-color: var(--osmos-orange); color: #fff; }
    .btn-orange:hover { background-color: var(--osmos-orange-dark); }
    .btn-blue { background-color: var(--osmos-blue); color: #fff; }
    .btn-danger { background-color: var(--osmos-red); color: #fff; }
    .btn-muted { background-color: #aaa; color: #fff; cursor: default; }
    .btn-light { background-color: #eef0f4; color: #333; }
    .btn-small { padding: 4px 8px; font-size: 11px; }
    .btn-block { display: block; width: 100%; text-align: center; }
    .post-button { float: right; background-color: var(--osmos-yellow); color: black; border: none; padding: 10px; font-size: 14px; border-radius: 4px; margin-top: 10px; min-width: 70px; cursor: pointer; text-align: center; font-weight: bold; text-decoration: none; }
    .post-button:hover { background-color: var(--osmos-yellow-dark); }

    /* alerts */
    .alert { padding: 12px 15px; border-radius: 4px; margin-bottom: 15px; font-size: 13px; line-height: 1.5; }
    .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
    .alert-info { background-color: #e8eef9; color: #2b3f6b; border: 1px solid #cdd8ee; }

    /* forms */
    .form-row { margin-bottom: 15px; text-align: left; }
    .form-row label { display: block; font-weight: bold; color: #333; margin-bottom: 5px; font-size: 13px; }
    .textbox { width: 100%; padding: 9px 10px; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; background-color: #fff; color: #222; }
    .textbox:focus { border-color: var(--osmos-orange); outline: none; box-shadow: 0 0 0 2px rgba(230,126,34,0.15); }
    textarea.textbox { height: 120px; resize: vertical; }
    select.textbox { height: 38px; }
    .form-hint { font-size: 11px; color: var(--osmos-muted); margin-top: 4px; font-weight: normal; }
    .form-section { margin: 25px 0 10px 0; font-size: 13px; color: #555; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; }

    /* profile/group header */
    .profile-header { background-color: #fff; text-align: center; color: var(--osmos-blue); border-radius: 0 0 6px 6px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.08); }
    .cover-image { display: block; width: 100%; height: 280px; object-fit: cover; background-color: #c5cfdd; }
    .profile-cover-pic { width: 150px; height: 150px; object-fit: cover; margin-top: -90px; border-radius: 50%; border: 4px solid #fff; position: relative; background-color: #fff; }
    .cover-links { font-size: 11px; margin-top: 4px; }
    .cover-links a { text-decoration: none; color: var(--osmos-blue); }
    .profile-name { font-size: 20px; font-weight: bold; color: var(--osmos-blue); text-decoration: none; }
    .profile-tag { font-size: 14px; color: #777; margin-top: 2px; }
    .profile-actions { margin-top: 12px; }
    .profile-actions .btn { margin: 0 3px; }
    .tabs { border-top: 1px solid #eee; margin-top: 15px; padding: 0 10px; }
    .menu-button { display: inline-block; padding: 10px 14px; margin: 0 2px; color: var(--osmos-blue); text-decoration: none; font-size: 14px; border-bottom: 3px solid transparent; }
    .menu-button:hover { background-color: #f3f5f9; }
    .menu-button.active { border-bottom-color: var(--osmos-yellow); font-weight: bold; color: #222; }

    /* yellow sidebar */
    .friends-bar { background-color: var(--osmos-yellow); min-height: 400px; color: #333; padding: 10px; text-align: center; font-size: 20px; border-radius: 6px; }
    .friends-bar-title { font-weight: bold; margin: 5px 0 10px 0; }
    .sidebar-pic { display: block; width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #fff; margin: 10px auto; background-color: #fff; }
    .sidebar-name { color: #000; font-weight: bold; text-decoration: none; font-size: 18px; }
    .sidebar-name:hover { text-decoration: underline; }

    /* user cards (user.php) */
    .friends { display: inline-block; vertical-align: top; width: 100px; text-align: center; margin: 4px; font-size: 12px; font-weight: bold; color: var(--osmos-blue); }
    .friends a { text-decoration: none; color: inherit; }
    .friends-img { display: block; width: 75px; height: 75px; object-fit: cover; margin: 8px auto; border-radius: 50%; background-color: #fff; }
    .friends-status { color: grey; font-size: 11px; font-weight: normal; display: block; margin-top: 3px; }

    /* post box + @tag suggestions */
    .composer { background-color: #fff; border: 1px solid var(--osmos-border); border-radius: 6px; padding: 10px; position: relative; margin-bottom: 20px; }
    .composer:after { content: ""; display: block; clear: both; }
    .composer textarea { width: 100%; border: none; font-size: 14px; height: 60px; resize: vertical; outline: none; }
    .composer input[type=file] { margin-top: 14px; font-size: 12px; max-width: 70%; }
    .tag-suggestions { display: none; position: absolute; left: 10px; top: 72px; background-color: #fff; border: 1px solid #ccc; width: 260px; border-radius: 4px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1000; max-height: 200px; overflow-y: auto; text-align: left; }
    .tag-suggestion { padding: 8px; cursor: pointer; border-bottom: 1px solid #eee; font-size: 13px; color: #222; }
    .tag-suggestion:last-child { border-bottom: none; }
    .tag-suggestion:hover, .tag-suggestion.active { background-color: #f0f0f0; }
    .tag-suggestion-name { font-size: 11px; color: #777; }

    /* posts */
    .posts-bar { background-color: #fff; padding: 10px; border-radius: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); }
    .post { display: flex; padding: 12px 10px; font-size: 13px; border-bottom: 1px solid #eee; background-color: #fff; text-align: left; }
    .post:last-child { border-bottom: none; }
    .post-avatar { width: 60px; height: 60px; border-radius: 50%; object-fit: cover; margin-right: 12px; flex-shrink: 0; background-color: #eee; }
    .post-body { flex: 1; min-width: 0; }
    .post-author { font-weight: bold; color: var(--osmos-blue); text-decoration: none; }
    .post-note { font-weight: normal; color: #aaa; }
    .post-group { font-weight: normal; color: #777; font-size: 12px; }
    .post-group a { color: var(--osmos-blue); text-decoration: none; font-weight: bold; }
    .post-text { margin-top: 5px; line-height: 1.45; word-wrap: break-word; overflow-wrap: anywhere; }
    .post-media { margin-top: 10px; }
    .post-image { width: 80%; border-radius: 4px; display: block; }
    .post-video { width: 100%; border-radius: 4px; display: block; background-color: #000; }
    .post-media-link { color: var(--osmos-blue); text-decoration: none; font-size: 11px; display: inline-block; margin-top: 4px; }
    .post-actions { margin-top: 12px; color: #999; }
    .post-actions a { color: var(--osmos-blue); text-decoration: none; }
    .post-actions a:hover { text-decoration: underline; }
    .post-actions a.post-manage { color: #999; }
    .post-info { display: block; margin-top: 6px; font-size: 11px; color: var(--osmos-blue); text-decoration: none; }
    .tag-link { color: var(--osmos-blue); text-decoration: none; font-weight: bold; }
    .comments { margin-top: 10px; }
    .comment { display: flex; background-color: #f2f3f5; border-radius: 6px; padding: 10px; margin-top: 10px; font-size: 13px; text-align: left; }
    .comment .post-avatar { width: 45px; height: 45px; }
    .page-nav { margin-top: 10px; }
    .page-nav:after { content: ""; display: block; clear: both; }

    /* messages */
    .message-left, .message-right { padding: 8px; font-size: 13px; display: flex; margin: 8px; width: 60%; border-radius: 10px; }
    .message-left { float: left; background-color: #f1f2f6; border: 1px solid #dcdde1; text-align: left; }
    .message-right { float: right; background-color: #fce4d6; border: 1px solid #f8cbad; text-align: right; }
    .message-left .message-avatar { float: left; margin-right: 10px; }
    .message-right .message-avatar { float: right; margin-left: 10px; }
    .message-avatar { width: 50px; height: 50px; object-fit: cover; border-radius: 50%; border: 2px solid #fff; }
    .message-name { font-weight: bold; color: var(--osmos-blue); text-decoration: none; }
    .message-text { margin-top: 5px; color: #444; word-wrap: break-word; overflow-wrap: anywhere; line-height: 1.4; }
    .message-image { margin: 10px 0; }
    .message-image img { width: 80%; border-radius: 4px; }
    .message-meta { clear: both; color: #999; font-size: 11px; padding-top: 4px; }
    .message-thread { padding: 10px 70px 10px 10px; font-size: 13px; display: flex; margin: 8px 0; width: 100%; border-radius: 8px; background-color: #f8f9fa; border: 1px solid #dae1e9; position: relative; text-align: left; }
    .message-thread.unread { background-color: #fae8d6; border-color: #f3c9a0; }
    .thread-arrow { background-color: var(--osmos-orange); position: absolute; right: 0; top: 0; bottom: 0; width: 55px; border-top-right-radius: 8px; border-bottom-right-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .message-composer { border: 1px solid #ccc; padding: 10px; background-color: #fff; margin-top: 10px; text-align: left; border-radius: 6px; }
    .message-composer textarea { width: 100%; height: 80px; border: 1px solid #ccc; padding: 8px; font-size: 14px; border-radius: 4px; resize: vertical; }
    .message-composer:after { content: ""; display: block; clear: both; }

    /* groups */
    .group-meta { color: #666; font-size: 13px; margin-top: 4px; }
    .group-grid { display: flex; flex-wrap: wrap; gap: 15px; justify-content: flex-start; }
    .group-card { display: block; width: 236px; background-color: #fff; border: 1px solid var(--osmos-border); border-radius: 6px; overflow: hidden; text-decoration: none; color: #222; text-align: left; }
    .group-card:hover { box-shadow: 0 3px 8px rgba(0,0,0,0.12); }
    .group-card-cover { height: 90px; background-color: var(--osmos-yellow); background-size: cover; background-position: center; display: flex; align-items: center; justify-content: center; }
    .group-card-initial { width: 54px; height: 54px; border-radius: 50%; background-color: #fff; color: var(--osmos-blue); font-size: 26px; font-weight: bold; display: flex; align-items: center; justify-content: center; }
    .group-card-body { padding: 10px 12px 12px 12px; }
    .group-card-name { font-weight: bold; color: var(--osmos-blue); font-size: 15px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .group-card-meta { font-size: 12px; color: #777; margin-top: 3px; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; background-color: #eef0f4; color: #555; margin-top: 6px; }
    .badge-owner { background-color: var(--osmos-yellow); color: #000; }
    .badge-admin { background-color: var(--osmos-blue); color: #fff; }
    .badge-moderator { background-color: #6c8ac9; color: #fff; }
    .about-list { list-style: none; padding: 0; margin: 0; font-size: 13px; }
    .about-list li { padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
    .about-list li:last-child { border-bottom: none; }
    .member-grid { display: flex; flex-wrap: wrap; gap: 6px; }
    .member-thumb { width: calc(33.333% - 4px); text-align: center; text-decoration: none; color: var(--osmos-blue); font-size: 11px; }
    .member-thumb img { width: 54px; height: 54px; border-radius: 50%; object-fit: cover; display: block; margin: 0 auto 3px auto; background-color: #eee; }
    .member-thumb span { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .member-card { display: inline-block; vertical-align: top; width: 160px; margin: 8px; background-color: #f2f3f5; padding: 12px 10px; border-radius: 6px; text-align: center; }
    .member-card img { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; display: block; margin: 0 auto 6px auto; background-color: #fff; }
    .member-card-name { color: var(--osmos-blue); font-weight: bold; font-size: 13px; text-decoration: none; }
    .member-card-buttons { margin-top: 10px; display: flex; gap: 4px; }
    .member-card-buttons .btn { flex: 1; }

    /* admin panel */
    .stat-cards { display: flex; gap: 10px; }
    .stat-card { flex: 1; background-color: #f6f7f9; border-radius: 6px; padding: 12px; text-align: center; }
    .stat-value { font-size: 24px; font-weight: bold; color: var(--osmos-blue); }
    .stat-label { font-size: 12px; color: #666; margin-top: 2px; }
    .admin-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .admin-table th { text-align: left; color: #555; font-size: 12px; border-bottom: 2px solid #eee; padding: 8px 6px; }
    .admin-table td { border-bottom: 1px solid #f0f0f0; padding: 8px 6px; vertical-align: middle; }
    .admin-user { display: flex; align-items: center; gap: 8px; text-decoration: none; color: #222; }
    .admin-user img { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; }
    .admin-actions { text-align: right; white-space: nowrap; }
    .admin-actions form { display: inline; }
    .badge-blocked { background-color: var(--osmos-red); color: #fff; }

    /* photos */
    .photo-grid { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }
    .photo-grid img { width: 170px; height: 170px; object-fit: cover; border-radius: 5px; display: block; }

    /* notifications */
    .notification { padding: 12px; margin-bottom: 10px; border-radius: 4px; display: flex; align-items: center; gap: 12px; border: 1px solid #f0f0f0; border-left: 5px solid #e0e0e0; background-color: #fdfdfd; }
    .notification.unread { background-color: #FFF8E7; border-left-color: var(--osmos-yellow); }
    .notification img { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; }
    .notification a { text-decoration: none; color: #222; font-size: 14px; }
</style>

<div id="orange_bar">
    <div style="width: 800px; margin: auto; display: flex; justify-content: space-between; align-items: center; height: 100%;">

        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="<?php echo ROOT; ?>home" style="color: black; text-decoration: none; font-size: 30px; font-weight: bold;">Osmos</a>
            <form method="get" action="<?php echo ROOT; ?>search" style="margin: 0;">
                <input type="text" id="search_box" name="find" placeholder="Search people or #tags" value="<?php echo (isset($_GET['find']) && is_string($_GET['find'])) ? htmlspecialchars($_GET['find']) : ''; ?>">
            </form>
        </div>

        <div style="display: flex; align-items: center; gap: 15px;">
            <?php if (is_array($header_user)): ?>

                <a href="<?php echo ROOT; ?>messages" style="text-decoration: none;" title="Messages">
                    <span style="display: inline-block; position: relative;">
                        <svg fill="#e67e22" width="25" height="25" viewBox="0 0 24 24" style="vertical-align: middle; margin-right: 5px;"><path d="M0 3v18h24v-18h-24zm6.623 7.929l-4.623 5.712v-9.458l4.623 3.746zm-4.141-5.929h19.035l-9.517 7.713-9.518-7.713zm5.694 7.188l3.824 3.099 3.83-3.104 5.612 6.817h-18.779l5.513-6.812zm9.208-1.264l4.616-3.741v9.348l-4.616-5.607z"/></svg>
                        <?php if ($msg_notif_count > 0): ?>
                            <div class="header-badge" style="right: -2px;">
                                <?php echo ($msg_notif_count > 99) ? '99+' : $msg_notif_count; ?>
                            </div>
                        <?php endif; ?>
                    </span>
                </a>

                <a href="<?php echo ROOT; ?>notifications" style="text-decoration: none;" title="Notifications">
                    <span style="display: inline-block; position: relative;">
                        <img src="<?php echo ROOT; ?>notif.svg" style="width: 25px; vertical-align: middle;">
                        <?php if ($notif_count > 0): ?>
                            <div class="header-badge" style="right: -8px;">
                                <?php echo ($notif_count > 99) ? '99+' : $notif_count; ?>
                            </div>
                        <?php endif; ?>
                    </span>
                </a>

                <?php if (!empty($header_user['is_admin'])): ?>
                    <a href="<?php echo ROOT; ?>admin" style="font-size: 13px; color: black; text-decoration: none; font-weight: bold;">Admin</a>
                <?php endif; ?>

                <a href="<?php echo ROOT; ?>profile/<?php echo $header_user['userid']; ?>" title="My profile">
                    <img src="<?php echo ROOT . $corner_image; ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; vertical-align: middle;">
                </a>
                <a href="<?php echo ROOT; ?>logout" style="font-size: 13px; color: black; text-decoration: none; font-weight: bold;">Logout</a>
            <?php else: ?>
                <a href="<?php echo ROOT; ?>login" style="color: black; text-decoration: none; font-size: 13px; background: white; padding: 5px 12px; border-radius: 4px; font-weight: bold;">Login</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash-area"><?php render_flash(); ?></div>
<?php endif; ?>

<script type="text/javascript">
    var OSMOS_ROOT = "<?php echo ROOT; ?>";
    var OSMOS_TOKEN = "<?php echo csrf_token(); ?>";

    function osmos_post(url, body, callback) {
        var ajax = new XMLHttpRequest();
        ajax.open("POST", url, true);
        ajax.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        ajax.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        ajax.onreadystatechange = function () {
            if (ajax.readyState == 4) {
                callback(ajax.status, ajax.responseText);
            }
        };
        ajax.send(body + "&csrf_token=" + encodeURIComponent(OSMOS_TOKEN));
        return ajax;
    }

    // like/unlike without a reload
    function ajax_data(e) {
        var el = e.currentTarget || e.target;
        var link = el.href || (el.parentNode ? el.parentNode.href : "");
        if (!link) return;

        e.preventDefault();
        osmos_post(OSMOS_ROOT + "ajax.php", "link=" + encodeURIComponent(link), function (status, text) {
            if (status == 200) {
                response(text);
            } else {
                window.location = link; // fallback to the normal like page
            }
        });
    }

    function response(result) {
        if (result == "") return;

        var data;
        try { data = JSON.parse(result); } catch (err) { return; }

        if (data.action == "login") {
            window.location = OSMOS_ROOT + "login";
            return;
        }

        if (data.action == "error") {
            alert(data.message);
            return;
        }

        if (data.action == "like_post") {
            var count = document.getElementById("like_count_" + data.id);
            if (count) {
                count.innerHTML = (data.likes > 0) ? "(" + data.likes + ")" : "";
            }

            var label = document.getElementById("like_label_" + data.id);
            if (label) {
                label.innerHTML = data.liked ? "Unlike" : "Like";
            }

            var info = document.getElementById("info_row_" + data.id);
            if (info) {
                info.innerHTML = data.info;
                info.style.display = data.info ? "block" : "none";
            }
        }
    }

    // @tag suggestions for every textarea.tag-input
    document.addEventListener("DOMContentLoaded", function () {
        var inputs = document.querySelectorAll("textarea.tag-input");
        for (var i = 0; i < inputs.length; i++) {
            setup_tag_input(inputs[i]);
        }
    });

    function setup_tag_input(textarea) {
        var box = textarea.parentNode.querySelector(".tag-suggestions");
        if (!box) return;

        var request = null;
        var active = -1;

        function hide() {
            box.style.display = "none";
            box.innerHTML = "";
            active = -1;
        }

        function items() {
            return box.querySelectorAll(".tag-suggestion");
        }

        function highlight(index) {
            var list = items();
            for (var i = 0; i < list.length; i++) {
                list[i].className = (i == index) ? "tag-suggestion active" : "tag-suggestion";
            }
            active = index;
        }

        function choose(tag) {
            var cursor = textarea.selectionStart;
            var before = textarea.value.substring(0, cursor);
            var after = textarea.value.substring(cursor);
            var new_before = before.replace(/@([a-zA-Z0-9_]*)$/, "@" + tag + " ");
            textarea.value = new_before + after;
            textarea.focus();
            textarea.setSelectionRange(new_before.length, new_before.length);
            hide();
        }

        function check() {
            var before = textarea.value.substring(0, textarea.selectionStart);
            var match = before.match(/(^|\s)@([a-zA-Z0-9_]*)$/);

            if (!match) {
                hide();
                return;
            }

            if (request) request.abort();
            request = osmos_post(OSMOS_ROOT + "ajax.php", "tag_search=" + encodeURIComponent(match[2]), function (status, text) {
                if (status == 200 && text.trim() != "" && text.trim().charAt(0) == "<") {
                    box.innerHTML = text;
                    box.style.display = "block";
                    highlight(-1);
                } else if (status != 0) {
                    hide();
                }
            });
        }

        textarea.addEventListener("input", check);
        textarea.addEventListener("click", check);

        textarea.addEventListener("keydown", function (e) {
            if (box.style.display != "block") return;
            var list = items();
            if (e.key == "ArrowDown") {
                e.preventDefault();
                highlight(Math.min(active + 1, list.length - 1));
            } else if (e.key == "ArrowUp") {
                e.preventDefault();
                highlight(Math.max(active - 1, 0));
            } else if ((e.key == "Enter" || e.key == "Tab") && active >= 0 && list[active]) {
                e.preventDefault();
                choose(list[active].getAttribute("data-tag"));
            } else if (e.key == "Escape") {
                hide();
            }
        });

        textarea.addEventListener("blur", function () {
            setTimeout(hide, 200);
        });

        // mousedown + preventDefault so the textarea keeps focus
        box.addEventListener("mousedown", function (e) {
            var item = e.target.closest(".tag-suggestion");
            if (!item) return;
            e.preventDefault();
            choose(item.getAttribute("data-tag"));
        });
    }
</script>
