<?php
require 'db.php';
if (empty($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}
$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;

// helper: create thumbnail using GD
function create_thumbnail($srcPath, $destPath, $maxW = 400, $maxH = 300) {
    $info = getimagesize($srcPath);
    if (!$info) return false;
    list($w, $h) = $info;
    $mime = $info['mime'];
    switch ($mime) {
        case 'image/jpeg': $img = imagecreatefromjpeg($srcPath); break;
        case 'image/png': $img = imagecreatefrompng($srcPath); break;
        case 'image/gif': $img = imagecreatefromgif($srcPath); break;
        case 'image/webp': $img = imagecreatefromwebp($srcPath); break;
        default: return false;
    }
    $ratio = min($maxW / $w, $maxH / $h, 1);
    $nw = (int)($w * $ratio);
    $nh = (int)($h * $ratio);
    $thumb = imagecreatetruecolor($nw, $nh);
    // preserve PNG transparency
    if ($mime === 'image/png' || $mime === 'image/gif') {
        imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }
    imagecopyresampled($thumb, $img, 0,0,0,0, $nw, $nh, $w, $h);
    $ok = false;
    switch ($mime) {
        case 'image/jpeg': $ok = imagejpeg($thumb, $destPath, 85); break;
        case 'image/png': $ok = imagepng($thumb, $destPath); break;
        case 'image/gif': $ok = imagegif($thumb, $destPath); break;
        case 'image/webp': $ok = imagewebp($thumb, $destPath, 85); break;
    }
    imagedestroy($img);
    imagedestroy($thumb);
    return $ok;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        $error = 'No file uploaded or upload error.';
    } else {
        $file = $_FILES['image'];
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/webp', 'image/gif',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes)) {
            $error = 'Only images (JPG, PNG, GIF, WEBP) and documents (PDF, DOC, Excel) are allowed.';
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $timestamp = time();
            $rand = bin2hex(random_bytes(5));
            $filename = $timestamp . '_' . $rand . '.' . $ext;
            $target = IMAGES_DIR . $filename;
            if (!is_dir(IMAGES_DIR)) mkdir(IMAGES_DIR, 0755, true);
            if (move_uploaded_file($file['tmp_name'], $target)) {
                // handle scheduling
                $postOption = $_POST['post_option'] ?? 'now';
                $startTime = $_POST['start_time'] ?? null;
                $expiry = $_POST['expiry_time'] ?? null;
                
                if ($expiry) {
                    try {
                        // datetime-local gives YYYY-MM-DDTHH:MM, MySQL needs YYYY-MM-DD HH:MM:SS
                        $formatted_expiry = str_replace('T', ' ', $expiry);
                        if (strlen($formatted_expiry) == 16) $formatted_expiry .= ':00';
                        
                        $formatted_start = null;
                        if ($postOption === 'later' && !empty($startTime)) {
                            $formatted_start = str_replace('T', ' ', $startTime);
                            if (strlen($formatted_start) == 16) $formatted_start .= ':00';
                        } else {
                            // Current time for "Now"
                            $formatted_start = date('Y-m-d H:i:s');
                        }

                        $stmt = $pdo->prepare("INSERT INTO offer_metadata (filename, start_time, expiry_time) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE start_time = VALUES(start_time), expiry_time = VALUES(expiry_time)");
                        $stmt->execute([$filename, $formatted_start, $formatted_expiry]);
                        $isScheduled = true;
                    } catch (Exception $e) {
                        $error = "Database error: " . $e->getMessage();
                        error_log("Offer Metadata Insert Error: " . $e->getMessage());
                    }
                }

                // generate thumbnail only for images
                if (strpos($mime, 'image/') === 0) {
                    $thumbDir = IMAGES_DIR . 'thumbs/';
                    if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);
                    $thumbPath = $thumbDir . $filename;
                    create_thumbnail($target, $thumbPath, 480, 320);
                }
                
                $success = (strpos($mime, 'image/') === 0) ? 'Image uploaded successfully.' : 'Document uploaded successfully.';
                if ($isScheduled) $success .= ' Scheduled for deletion at ' . htmlspecialchars($expiry) . '.';

                // log activity
                log_activity($conn, $_SESSION['user_id'], 'Uploaded file: '.$filename . ($isScheduled ? ' (Scheduled: '.$expiry.')' : ''));
            } else {
                $error = 'Failed to move uploaded file.';
            }
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                echo json_encode(['success' => $success, 'error' => $error]);
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Upload Offer Image — New India Bazar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="dashboard"><img src="https://www.newindiabazar.com/images/logo.png" alt="Logo" height="50"></a>
      <div class="d-flex">
        <div class="me-3 text-muted"><?=htmlspecialchars($_SESSION['user_name'])?></div>
        <a class="btn btn-outline-secondary" href="logout">Sign out</a>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <nav aria-label="breadcrumb">
  <ol class="breadcrumb">
    <li class="breadcrumb-item active" aria-current="page"><a href="dashboard">Dashboard</a></li>
  </ol>
</nav>
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title">Upload file for offer</h5>
        <?php if ($success): ?><div class="alert alert-success"><?=$success?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?=htmlspecialchars($error)?></div><?php endif; ?>
        <form action="upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
          <div class="mb-3">
            <label for="image" class="form-label">Select file (Image or Document)</label>
            <input id="image" name="image" type="file" class="form-control" accept="image/*,.pdf,.doc,.docx,.xls,.xlsx" required>
            <div class="form-text">Allowed: JPG, PNG, WEBP, GIF, PDF. Max recommended size 10MB.</div>
          </div>
          <div class="mb-3">
            <label class="form-label d-block">Post Offer</label>
            <div class="btn-group w-75 mb-3" role="group" aria-label="Posting time toggle">
              <input type="radio" class="btn-check" name="post_option" id="post_now" value="now" checked>
              <label class="btn btn-outline-primary" for="post_now">Now</label>

              <input type="radio" class="btn-check" name="post_option" id="post_later" value="later">
              <label class="btn btn-outline-primary" for="post_later">Schedule Later</label>
            </div>
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" id="enable_expiry" name="enable_expiry" checked disabled>
              <label class="form-check-label" for="enable_expiry">Automatic delete schedule (Required)</label>
            </div>

            <div id="start_time_container" style="display:none;" class="mb-3">
              <label for="start_time" class="form-label">Start Date & Time</label>
              <input type="datetime-local" id="start_time" name="start_time" class="form-control">
              <div class="form-text">The offer will become visible at this time.</div>
            </div>

            
            <div id="expiry_options">
              <label for="expiry_time" class="form-label">Expiry Date & Time</label>
              <input type="datetime-local" id="expiry_time" name="expiry_time" class="form-control" required>
              <div class="form-text">The offer will be automatically hidden and deleted after this time.</div>
            </div>
          </div>
          <p>Note: PDF files will be displayed in a viewer, while images will be displayed as thumbnails.</p>
          <div id="progressContainer" class="mt-3" style="display:none;">
            <div class="progress" style="height: 25px;">
              <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
            </div>
            <div class="text-center mt-1 small text-muted">Uploading file, please wait...</div>
          </div>
          <div class="d-grid mt-3">
            <button type="submit" class="btn btn-primary" id="uploadBtn">
              <span class="spinner-border spinner-border-sm d-none" id="btnSpinner" role="status" aria-hidden="true"></span>
              <span id="btnText">Upload</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </main>
<script>
document.querySelectorAll('input[name="post_option"]').forEach(radio => {
  radio.addEventListener('change', function(){
    const isLater = document.getElementById('post_later').checked;
    document.getElementById('start_time_container').style.display = isLater ? 'block' : 'none';
    document.getElementById('start_time').required = isLater;
  });
});

document.getElementById('uploadForm').addEventListener('submit', function(e){
  e.preventDefault();
  const file = document.getElementById('image').files[0];
  if (!file) { alert('Select a file to upload.'); return; }
  
  const formData = new FormData(this);
  const xhr = new XMLHttpRequest();
  
  const btn = document.getElementById('uploadBtn');
  const btnText = document.getElementById('btnText');
  const btnSpinner = document.getElementById('btnSpinner');
  const progressContainer = document.getElementById('progressContainer');
  const progressBar = document.getElementById('uploadProgressBar');
  
  btn.disabled = true;
  btnSpinner.classList.remove('d-none');
  btnText.textContent = 'Uploading...';
  progressContainer.style.display = 'block';

  xhr.upload.addEventListener('progress', function(e) {
    if (e.lengthComputable) {
      const percent = Math.round((e.loaded / e.total) * 100);
      progressBar.style.width = percent + '%';
      progressBar.textContent = percent + '%';
      progressBar.setAttribute('aria-valuenow', percent);
    }
  });

  xhr.onload = function() {
    if (xhr.status === 200) {
        try {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                window.location.href = 'upload.php?success=' + encodeURIComponent(res.success);
            } else {
                alert(res.error || 'Upload failed.');
                resetBtn();
            }
        } catch (err) {
            console.error(err);
            alert('An unexpected error occurred.');
            resetBtn();
        }
    } else {
        alert('Upload failed with status: ' + xhr.status);
        resetBtn();
    }
  };

  xhr.onerror = function() {
    alert('Network error or upload cancelled.');
    resetBtn();
  };

  function resetBtn() {
    btn.disabled = false;
    btnSpinner.classList.add('d-none');
    btnText.textContent = 'Upload';
    progressContainer.style.display = 'none';
  }

  xhr.open('POST', 'upload.php', true);
  xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
  xhr.send(formData);
});
</script>
</body>
</html>
