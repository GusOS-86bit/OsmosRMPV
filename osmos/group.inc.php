<?php
// group card, needs $GROUP_ROW
if (!defined("ROOT")) { http_response_code(404); die; }
if (!is_array($GROUP_ROW)) return;

if (!isset($image_class)) $image_class = new Image();
$card_group_class = new Group();

$card_members = $card_group_class->count_members($GROUP_ROW['userid']);
$card_private = ($GROUP_ROW['group_type'] ?? "public") == "private";
$card_name = trim($GROUP_ROW['first_name']);

// no cover = coloured box with the first letter
$card_cover = "";
if (is_image_file($GROUP_ROW['cover_image'] ?? "")) {
    $card_cover = $image_class->get_thumb_cover($GROUP_ROW['cover_image']);
}

$card_role = isset($GROUP_ROLE) ? (string)$GROUP_ROLE : "";
?>
<a class="group-card" href="<?php echo ROOT; ?>group/<?php echo $GROUP_ROW['userid']; ?>">
    <?php if ($card_cover != ""): ?>
        <div class="group-card-cover" style="background-image: url('<?php echo ROOT . $card_cover; ?>');"></div>
    <?php else: ?>
        <div class="group-card-cover">
            <div class="group-card-initial"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($card_name, 0, 1))); ?></div>
        </div>
    <?php endif; ?>

    <div class="group-card-body">
        <div class="group-card-name"><?php echo htmlspecialchars($card_name); ?></div>
        <div class="group-card-meta">
            <?php echo $card_private ? "Private group" : "Public group"; ?> &middot; <?php echo $card_members . " member" . ($card_members == 1 ? "" : "s"); ?>
        </div>
        <?php if ($card_role != ""): ?>
            <span class="badge badge-<?php echo htmlspecialchars($card_role); ?>"><?php echo htmlspecialchars(ucfirst($card_role)); ?></span>
        <?php endif; ?>
    </div>
</a>
