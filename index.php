<?php
require 'db.php';
$dir = IMAGES_DIR;
$latestFile = '';
$latestTime = 0;

// Try to fetch the latest offer from metadata first
if (isset($pdo)) {
    try {
        $stmt = $pdo->prepare("SELECT filename FROM offer_metadata WHERE start_time <= ? AND expiry_time > ? ORDER BY id DESC LIMIT 1");
        $now = date('Y-m-d H:i:s');
        $stmt->execute([$now, $now]);
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
    $fileUrl = IMAGES_URL . $latestFile . '?t=' . $latestTime;
    $fileExt = strtolower(pathinfo($latestFile, PATHINFO_EXTENSION));
}

$isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
$isPDF = ($fileExt === 'pdf');
$isDoc = in_array($fileExt, ['doc', 'docx', 'xls', 'xlsx']);
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Weekly Sale - New India Bazar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<style>
  .pdf-wrapper { position: relative; width: 100%; height: 1100px; display: flex; flex-direction: column; }
  .pdf-container { position: relative; width: 100%; height: 100%; border: 1px solid #dee2e6; border-radius: 8px; overflow-y: auto; overflow-x: hidden; background: #525659; scroll-behavior: smooth; }
  .pdf-loader { position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; justify-content: flex-start; align-items: center; padding-top: 200px; background: #fff; border: 1px solid #dee2e6; border-radius: 8px; z-index: 10; transition: opacity 0.5s ease; }
  
  .page-placeholder { 
    margin: 20px auto; 
    background: #fff; 
    box-shadow: 0 4px 8px rgba(0,0,0,0.2); 
    display: flex; 
    align-items: center; 
    justify-content: center;
    position: relative;
    max-width: 95%;
  }
  .page-placeholder canvas { display: block; max-width: 100%; height: auto; }
  .page-loading-status { color: #6c757d; font-size: 14px; position: absolute; }
  
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
          <div class="pdf-wrapper">
            <div id="pdfLoader" class="pdf-loader text-center">
              <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading your Offer...</span>
              </div>
              <p id="loaderStatus" class="mt-3 text-muted">Loading your Offer, please wait...</p>
            </div>
            <div id="pdfContainer" class="pdf-container">
              <div id="pdfViewer"></div>
              <div id="pdfErrorFallback" class="mt-4 text-center" style="display:none;">
                 <p class="text-danger mb-3">We're having trouble displaying the flyer directly on this device.</p>
                 <a href="<?=htmlspecialchars($fileUrl)?>" target="_blank" class="btn btn-primary btn-lg shadow-sm">
                   <i class="bi bi-file-earmark-pdf"></i> Open Full Flyer (New Tab)
                 </a>
              </div>
            </div>
          </div>
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
  <script>
    const url = '<?=htmlspecialchars($fileUrl)?>';
    let pdfDoc = null;
    const container = document.getElementById('pdfViewer');
    const statusText = document.getElementById('loaderStatus');
    const loader = document.getElementById('pdfLoader');
    const errorFallback = document.getElementById('pdfErrorFallback');
    const renderedPages = new Set();

    if (url && container) {
      pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

      function updateStatus(text, delay = 0) {
        if (delay > 0) {
          setTimeout(() => { if(statusText) statusText.innerText = text; }, delay);
        } else if(statusText) {
          statusText.innerText = text;
        }
      }

      updateStatus("Preparing flyer...", 1000);

      pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
        pdfDoc = pdfDoc_;
        setupLazyRendering();
      }).catch(err => {
        console.error('Error loading PDF: ', err);
        if(statusText) statusText.innerText = "Compatibility issue detected.";
        if(errorFallback) errorFallback.style.display = 'block';
        setTimeout(() => { if(loader) loader.style.display = 'none'; }, 500);
      });

      async function setupLazyRendering() {
        const totalPages = pdfDoc.numPages;
        const firstPage = await pdfDoc.getPage(1).catch(err => {
           if(errorFallback) errorFallback.style.display = 'block';
           throw err;
        });
        const viewport = firstPage.getViewport({scale: 1.0});
        const aspectRatio = viewport.height / viewport.width;
        
        // Create all placeholders immediately
        for (let i = 1; i <= totalPages; i++) {
          const placeholder = document.createElement('div');
          placeholder.className = 'page-placeholder';
          placeholder.id = 'page-' + i;
          placeholder.dataset.pageNumber = i;
          
          // Estimate height based on container width to prevent layout shifts
          const width = container.clientWidth || 800;
          placeholder.style.height = (width * aspectRatio) + 'px';
          placeholder.style.width = '100%';
          
          placeholder.innerHTML = `<div class="page-loading-status">Loading page ${i}...</div>`;
          container.appendChild(placeholder);
        }

        // Setup observer
        const observer = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const pageNum = parseInt(entry.target.dataset.pageNumber);
              if (!renderedPages.has(pageNum)) {
                renderPage(pageNum, entry.target);
              }
            }
          });
        }, { root: document.getElementById('pdfContainer'), rootMargin: '500px' });

        document.querySelectorAll('.page-placeholder').forEach(p => observer.observe(p));

        // Ensure Page 1 renders immediately to hide main loader
        renderPage(1, document.getElementById('page-1')).then(() => {
          setTimeout(() => {
            if(loader) {
              loader.style.opacity = '0';
              setTimeout(() => { loader.style.display = 'none'; }, 300);
            }
          }, 300);
        }).catch(err => {
            if(errorFallback) errorFallback.style.display = 'block';
            if(loader) loader.style.display = 'none';
        });
      }

      async function renderPage(num, placeholder) {
        if (renderedPages.has(num)) return;
        renderedPages.add(num);

        try {
          const page = await pdfDoc.getPage(num);
          const isMobile = window.innerWidth < 768;
          const scale = isMobile ? 1.2 : 1.5; 
          
          const viewport = page.getViewport({scale: scale});
          const containerWidth = container.clientWidth || 800;
          const dynamicScale = (containerWidth - 20) / viewport.width;
          const finalViewport = page.getViewport({scale: dynamicScale * scale});

          const canvas = document.createElement('canvas');
          const context = canvas.getContext('2d');
          canvas.height = finalViewport.height;
          canvas.width = finalViewport.width;

          placeholder.innerHTML = '';
          placeholder.style.height = 'auto'; 
          placeholder.appendChild(canvas);

          await page.render({ canvasContext: context, viewport: finalViewport }).promise;
        } catch (err) {
          console.error(`Page ${num} render error:`, err);
          placeholder.innerHTML = `<div class="text-danger py-4">Failed to load page ${num}. <a href="<?=htmlspecialchars($fileUrl)?>" target="_blank">Try opening full page.</a></div>`;
        }
      }
    }

    // Safety fallback
    setTimeout(() => {
      if (loader && loader.style.display !== 'none') {
        loader.style.opacity = '0';
        setTimeout(() => { loader.style.display = 'none'; }, 300);
      }
    }, 15000);
  </script>
</body>
</html>
