<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<?php
    $settings_class = new Settings();
    $settings = $is_own_profile ? $settings_class->get_settings($me) : false;
?>
<div class="card" style="margin-top: 20px;">

    <?php if (!is_array($settings)): ?>

        <div class="empty-state">You can only change your own settings.</div>

    <?php else: ?>

    <h3 class="card-title">Settings</h3>

    <form method="post" style="max-width: 460px; margin: 0 auto;">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="settings">

        <div class="form-section">Profile</div>

        <div style="display: flex; gap: 12px;">
            <div class="form-row" style="flex: 1;">
                <label for="first_name">First name</label>
                <input type="text" id="first_name" name="first_name" class="textbox" maxlength="50" value="<?php echo htmlspecialchars($settings['first_name']); ?>" required>
            </div>
            <div class="form-row" style="flex: 1;">
                <label for="last_name">Last name</label>
                <input type="text" id="last_name" name="last_name" class="textbox" maxlength="50" value="<?php echo htmlspecialchars($settings['last_name']); ?>" required>
            </div>
        </div>

        <div class="form-row">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="textbox" maxlength="20" value="<?php echo htmlspecialchars($settings['tag_name']); ?>" pattern="[a-z][a-z0-9_]{2,19}" title="3 to 20 characters: lowercase letters, numbers and _, starting with a letter" required>
            <div class="form-hint">Others tag you with @<?php echo htmlspecialchars($settings['tag_name']); ?>. 3 to 20 characters: lowercase letters (a-z), numbers and _, starting with a letter.</div>
        </div>

        <div class="form-row">
            <label for="gender">Gender</label>
            <select id="gender" name="gender" class="textbox">
                <?php if (!in_array($settings['gender'], ["Male", "Female"])): ?>
                    <option value="" selected disabled>Select gender</option>
                <?php endif; ?>
                <option value="Male" <?php echo ($settings['gender'] == "Male") ? "selected" : ""; ?>>Male</option>
                <option value="Female" <?php echo ($settings['gender'] == "Female") ? "selected" : ""; ?>>Female</option>
            </select>
        </div>

        <div class="form-row">
            <label for="about">About me</label>
            <textarea id="about" name="about" class="textbox" maxlength="1024" placeholder="Tell people a little about yourself"><?php echo htmlspecialchars($settings['about'] ?? ""); ?></textarea>
            <div class="form-hint">Shown on your About tab. Up to 1024 characters.</div>
        </div>

        <div class="form-section">Privacy</div>

        <div class="form-row">
            <label for="privacy">Who can see my posts, photos and friends?</label>
            <select id="privacy" name="privacy" class="textbox">
                <option value="public" <?php echo (($settings['privacy'] ?? "public") == "public") ? "selected" : ""; ?>>Public: everyone on Osmos</option>
                <option value="friends" <?php echo (($settings['privacy'] ?? "public") == "friends") ? "selected" : ""; ?>>Friends only</option>
            </select>
            <div class="form-hint">Your name, profile picture and About tab are always visible, so people can find you and send a friend request.</div>
        </div>

        <div class="form-section">Account</div>

        <div class="form-row">
            <label for="password">New password</label>
            <input type="password" id="password" name="password" class="textbox" placeholder="Leave empty to keep your current password" autocomplete="new-password">
        </div>

        <div class="form-row">
            <label for="password2">Retype new password</label>
            <input type="password" id="password2" name="password2" class="textbox" placeholder="Retype the new password" autocomplete="new-password">
        </div>

        <input type="submit" class="btn btn-primary btn-block" value="Save" style="padding: 11px; font-size: 14px; margin-top: 10px;">
    </form>

    <?php endif; ?>
</div>
