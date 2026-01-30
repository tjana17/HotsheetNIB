<?php
require 'db.php';
if (empty($_SESSION['user_id'])) {
    header('Location: login');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard — New India Bazar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="dashboard"><img src="https://www.newindiabazar.com/images/logo.png" alt="Logo" height="50"></a>
      <div class="d-flex align-items-center">
        <div class="me-3 text-muted"><?=htmlspecialchars($_SESSION['user_name'])?></div>
        <a class="btn btn-outline-secondary me-2" href="logout">Sign out</a>
      </div>
    </div>
  </nav>

  <main class="container py-4">
    <div class="d-flex justify-content-between mb-3">
      <h4 class="mb-0">Offers</h4>
        <div>
          <?php if($_SESSION['role']=='admin'): ?>
            <a href="admin_dashboard.php" class="btn btn-warning">Admin Dashboard</a>
          <?php endif; ?>
            <a href="upload" class="btn btn-primary">Create Offer</a>
      </div>
    </div>
    <!-- <div><a href="recent" class="btn btn-secondary">Recent</a> </div> -->
    <br/>
    <div id="imagesContainer" class="row gy-3">
      <!-- images will be injected by JS -->
    </div>
  </main>

<script>
async function fetchImages(){
  try {
    const res = await fetch('fetch_images.php?t=' + Date.now(), {cache: 'no-store'});
    const data = await res.json();
    const container = document.getElementById('imagesContainer');
    container.innerHTML = '';
    if (!data.length) {
      container.innerHTML = '<div class="col-12"><div class="alert alert-info">No offers yet. Click "Create Offer" to upload.</div></div>';
      return;
    }
    data.forEach(img => {
      const col = document.createElement('div');
      col.className = 'col-sm-6 col-md-4';
      col.innerHTML = `
        <div class="card h-100 shadow-sm">
          <img src="${img.url}?t=${Date.now()}" class="card-img-top" style="height:180px;object-fit:cover;">
          <div class="card-body d-flex flex-column">
            <h6 class="card-title mb-1">${img.name}</h6>
            <p class="text-muted small mb-1">From: ${img.start_time}</p>
            <p class="text-muted small mb-2">To: ${img.expiry_time}</p>
            <div class="mt-auto d-flex justify-content-between">
              <a class="btn btn-sm btn-outline-primary" href="${img.url}?t=${Date.now()}" target="_blank">View</a>
              <button class="btn btn-sm btn-danger" onclick="deleteImage('${img.filename}', this)">Delete</button>
            </div>
          </div>
        </div>`;
      container.appendChild(col);
    });
  } catch (e) {
    console.error(e);
  }
}

async function deleteImage(filename, btn){
  if (!confirm('Delete this offer?')) return;
  try {
    btn.disabled = true;
    const res = await fetch('delete_image.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({filename})
    });
    const r = await res.json();
    if (r.success) {
      fetchImages();
    } else {
      alert(r.message || 'Could not delete image.');
      btn.disabled = false;
    }
  } catch (err) {
    console.error(err);
    alert('Error deleting image.');
    btn.disabled = false;
  }
}

fetchImages();
setInterval(fetchImages, 8000);
</script>
</body>
</html>
