<?php
require 'db.php';
$dir = IMAGES_DIR;
$latestFile = '';
$latestTime = 0;

// Try to fetch the latest offer from metadata first
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT filename FROM offer_metadata WHERE start_time <= NOW() AND expiry_time > NOW() ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $dbFile = $stmt->fetchColumn();
        
        if ($dbFile && is_file($dir . $dbFile)) {
            $latestFile = $dbFile;
            $latestTime = filemtime($dir . $dbFile);
        }
    } catch (Exception $e) {
        error_log("Error fetching offer from metadata: " . $e->getMessage());
    }
}

// No fallback to filesystem scan anymore. 
// Offers must be explicitly scheduled in the offer_metadata table to be displayed.

$fileUrl = '';
$fileExt = '';
if ($latestFile) {
    $fileUrl = IMAGES_URL . $latestFile . '?t=' . time();
    $fileExt = strtolower(pathinfo($latestFile, PATHINFO_EXTENSION));
}

$isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
$isPDF = ($fileExt === 'pdf');
$isDoc = in_array($fileExt, ['doc', 'docx', 'xls', 'xlsx']);
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Hot Sheet - New India Bazar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  .pdf-container { width: 100%; height: 100%; min-height: 1280px; border: none; }
  .doc-preview { padding: 40px; border: 2px dashed #dee2e6; border-radius: 10px; }
</style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <div class="card shadow-sm mx-auto" style="max-width: 1000px;">
      <div class="card-body text-center">
        <?php if (!$latestFile): ?>
          <p><img src="offer_img.png" style="width:150px;" alt="No Offer"></p>
          <p style="font-size:18px;">Currently, no offers are available. Stay tuned...<br/> Exciting promotions will be coming your way shortly!</p>
        <?php elseif ($isImage): ?>
          <img src="<?=htmlspecialchars($fileUrl)?>" alt="Latest Offer" class="img-fluid rounded" style="max-height: 100%; object-fit: contain;">
        <?php elseif ($isPDF): ?>
          <div class="ratio ratio-16x9 pdf-container">
            <embed src="<?=htmlspecialchars($fileUrl)?>" type="application/pdf" width="100%" height="100%" />
          </div>
          <!-- <div class="mt-2">
            <a href="<?=htmlspecialchars($fileUrl)?>" target="_blank" class="btn btn-sm btn-outline-primary">Open PDF in new window</a>
          </div> -->
        <?php elseif ($isDoc): ?>
          <div class="doc-preview bg-white">
            <div class="mb-3">
              <img src="https://cdn-icons-png.flaticon.com/512/281/281760.png" alt="Document" style="width: 80px; opacity: 0.6;">
            </div>
            <h5 class="mb-3"><?=htmlspecialchars($latestFile)?></h5>
            <p class="text-muted">This document cannot be previewed directly.</p>
            <a href="<?=htmlspecialchars($fileUrl)?>" class="btn btn-primary btn-lg" download>Download / View Document</a>
          </div>
        <?php else: ?>
          <p class="text-muted">Unsupported file format.</p>
          <a href="<?=htmlspecialchars($fileUrl)?>" class="btn btn-secondary" download>Download File</a>
        <?php endif; ?>
        
        <div class="mt-4 pt-3 border-top">
          <a class="btn btn-secondary" href="https://www.newindiabazar.com/weeklysale/">Refresh Page</a>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
