<?php
require 'db.php';
$dir = IMAGES_DIR;
$latestFile = '';
$latestTime = 0;
if (is_dir($dir)) {
    $files = array_diff(scandir($dir), ['.','..','thumbs']);
    foreach ($files as $file) {
        $filePath = $dir . $file;
        if (is_file($filePath)) {
            $fileTime = filemtime($filePath);
            if ($fileTime > $latestTime) {
                $latestTime = $fileTime;
                $latestFile = $file;
            }
        }
    }
}
// if (!$latestFile) {
//     echo 'No image found.';
//     exit;
// }
$imgUrl = IMAGES_URL . $latestFile . '?t=' . time();
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Hot Sheet - New India Bazar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="card shadow-sm mx-auto">
      <div class="card-body text-center">
        <h5 class="card-title"></h5>
        <?php if (!$latestFile): ?>
        <p><img src="offer_img.png" style="width:150px;"></p>
        <p style="font-size:18px;">Currently, no offers are available. Stay tuned...<br/> Exciting promotions will be coming your way shortly!</p>
        <?php else: ?>
        <img src="<?=htmlspecialchars($imgUrl)?>" alt="Recent" class="img-fluid" style="object-fit:contain;">
        <?php endif; ?>
        <div class="mt-3"><a class="btn btn-secondary" href="recent">Refresh</a></div>
      </div>
    </div>
  </div>
</body>
</html>
