<?php if (!defined("ROOT")) { http_response_code(404); die; } ?>
<div class="card" style="margin-top: 20px;">
    <h3 class="card-title">About <?php echo htmlspecialchars($user_data['first_name']); ?></h3>

    <?php if (trim($user_data['about'] ?? "") !== ""): ?>
        <div style="font-size: 15px; line-height: 1.5; color: #333; word-wrap: break-word;">
            <?php echo nl2br(htmlspecialchars($user_data['about'])); ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            Nothing here yet.
            <?php if ($is_own_profile): ?>
                <br><br><a class="btn btn-primary" href="<?php echo ROOT; ?>profile/<?php echo $user_data['userid']; ?>/settings">Write something about yourself</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <ul class="about-list" style="margin-top: 20px;">
        <?php if (in_array($user_data['gender'] ?? "", ["Male", "Female"])): ?>
            <li><strong>Gender:</strong> <?php echo htmlspecialchars($user_data['gender']); ?></li>
        <?php endif; ?>
        <li><strong>Joined:</strong> <?php echo date("F jS, Y", strtotime($user_data['date'])); ?></li>
    </ul>
</div>
