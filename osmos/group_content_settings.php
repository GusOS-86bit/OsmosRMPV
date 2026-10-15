<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<!-- group_content_settings.php -->
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title">Group settings</h3>

    <form method="post" style="max-width: 460px; margin: 0 auto;">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="action" value="settings">

        <div class="form-row">
            <label for="group_name">Group name</label>
            <input type="text" id="group_name" name="group_name" class="textbox" maxlength="50" value="<?php echo htmlspecialchars($group_data['first_name']); ?>" required>
        </div>

        <div class="form-row">
            <label for="about">Description</label>
            <textarea id="about" name="about" class="textbox" maxlength="1024" placeholder="What is this group about?"><?php echo htmlspecialchars($group_data['about'] ?? ""); ?></textarea>
        </div>

        <div class="form-row">
            <label for="group_type">Who can see the group?</label>
            <select id="group_type" name="group_type" class="textbox">
                <option value="public" <?php echo !$is_private ? "selected" : ""; ?>>Public: anyone can see members and posts, and join straight away</option>
                <option value="private" <?php echo $is_private ? "selected" : ""; ?>>Private: only members see posts, people must request to join</option>
            </select>
        </div>

        <input type="submit" class="btn btn-primary btn-block" value="Save" style="padding: 11px; font-size: 14px; margin-top: 10px;">
    </form>

    <div style="text-align: center; margin-top: 20px; font-size: 13px;">
        <a href="<?php echo ROOT; ?>change_profile_image/cover/<?php echo $gid; ?>" style="text-decoration: none;">Change the group cover picture &rarr;</a>
    </div>
</div>
