<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<div class="card" style="margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center;" class="card-title">
        <span>Groups</span>
        <?php if ($is_own_profile): ?>
            <a class="btn btn-primary" href="<?php echo ROOT; ?>create_group">+ Create Group</a>
        <?php endif; ?>
    </div>

    <?php
        $group_class = new Group();
        $groups = $group_class->get_my_groups($user_data['userid']);

        if (is_array($groups)) {
            echo "<div class='group-grid'>";
            foreach ($groups as $GROUP_ROW) {
                // role shows up as a badge on the card
                $GROUP_ROLE = $group_class->get_member_role($GROUP_ROW['userid'], $user_data['userid']);
                include("group.inc.php");
            }
            echo "</div>";
        } else {
            echo "<div class='empty-state'>No groups were found!</div>";
        }
    ?>
</div>
