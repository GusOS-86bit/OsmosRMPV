<?php
include_once("classes/autoload.php");
http_response_code(404);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Page not found | Osmos</title>
</head>
<body>
    <?php include("header.php"); ?>

    <div class="page">
        <div class="card empty-state" style="padding: 50px 20px;">
            <div style="font-size: 60px; font-weight: bold; color: #FFC10F;">404</div>
            <h2 style="color: #405d9b; margin-top: 5px;">Page not found</h2>
            <p>The page you are looking for doesn't exist or was moved.</p>
            <a class="btn btn-primary" href="<?php echo ROOT; ?>home">Back to Timeline</a>
        </div>
    </div>
</body>
</html>
