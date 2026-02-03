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
            <a class="btn btn-secondary" onclick="openTour()">Offer Guide</a>
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

// --- Offer Guide Slider Logic ---
let currentSlide = 0;
const slides = [
  {
    title: "Get Started",
    description: "Click the 'Create Offer' button on your dashboard to begin uploading your first promotion or announcement.",
    image: "guide/step_1.png"
  },
  {
    title: "Quick Upload",
    description: "Choose your offer image or PDF. You can select 'Now' to publish it immediately or use the scheduling options for later.",
    image: "guide/step_2.png"
  },
  {
    title: "Set Your Schedule",
    description: "Define when your offer should become visible and when it should automatically expire. This helps automate your marketing campaigns.",
    image: "guide/step_3.png"
  },
  {
    title: "Confirmation",
    description: "Once uploaded, you'll receive a confirmation. Your offer is now safely stored and will follow the schedule you've set.",
    image: "guide/step_4.png"
  },
  {
    title: "Manage Your Offers",
    description: "All your offers appear on the dashboard. You can view the details, monitor their active dates, or delete them anytime.",
    image: "guide/step_5.png"
  }
];

function openTour() {
  const overlay = document.getElementById('guideOverlay');
  overlay.classList.add('active');
  currentSlide = 0;
  renderSlide();
  document.body.style.overflow = 'hidden'; // Prevent scrolling
  
  // Focus management for accessibility
  document.getElementById('nextBtn').focus();
}

function closeTour() {
  const overlay = document.getElementById('guideOverlay');
  overlay.classList.remove('active');
  document.body.style.overflow = '';
}

function nextSlide() {
  if (currentSlide < slides.length - 1) {
    currentSlide++;
    renderSlide('next');
  } else {
    closeTour();
  }
}

function prevSlide() {
  if (currentSlide > 0) {
    currentSlide--;
    renderSlide('prev');
  }
}

function renderSlide(direction = 'next') {
  const container = document.getElementById('sliderContainer');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const indicators = document.querySelector('.slide-indicators');
  
  // Create slide content
  const slideData = slides[currentSlide];
  container.innerHTML = ''; // Clear existing
  
  const slide = document.createElement('div');
  slide.className = `slide ${direction === 'prev' ? 'prev' : ''}`;
  slide.innerHTML = `
    <div class="slide-img-wrapper">
      <img src="${slideData.image}" alt="${slideData.title}">
    </div>
    <h2>${slideData.title}</h2>
    <p>${slideData.description}</p>
  `;
  
  container.appendChild(slide);
  
  // Trigger transition
  requestAnimationFrame(() => {
    slide.classList.add('active');
  });
  
  // Update Buttons
  prevBtn.className = `btn-guide btn-prev ${currentSlide === 0 ? 'btn-hidden' : ''}`;
  nextBtn.innerText = currentSlide === slides.length - 1 ? "Finished" : "Next";
  
  // Update Indicators
  indicators.innerHTML = slides.map((_, i) => `
    <div class="indicator ${i === currentSlide ? 'active' : ''}"></div>
  `).join('');
}

// Keyboard Navigation
document.addEventListener('keydown', (e) => {
  const overlay = document.getElementById('guideOverlay');
  if (!overlay.classList.contains('active')) return;
  
  if (e.key === 'Escape') closeTour();
  if (e.key === 'ArrowRight') nextSlide();
  if (e.key === 'ArrowLeft') prevSlide();
});

// Close on overlay click
document.getElementById('guideOverlay').addEventListener('click', (e) => {
  if (e.target.id === 'guideOverlay') closeTour();
});
</script>

<!-- Offer Guide Modal -->
<div id="guideOverlay" class="modal-overlay" role="dialog" aria-labelledby="guideTitle" aria-modal="true">
  <div class="guide-modal">
    <button class="close-modal" onclick="closeTour()" aria-label="Close Guide">&times;</button>
    
    <div id="sliderContainer" class="slider-container">
      <!-- Slides injected by JS -->
    </div>
    
    <div class="slider-controls">
      <button id="prevBtn" class="btn-guide btn-prev" onclick="prevSlide()">Previous</button>
      <div class="slide-indicators">
        <!-- Indicators injected by JS -->
      </div>
      <button id="nextBtn" class="btn-guide btn-next" onclick="nextSlide()">Next</button>
    </div>
  </div>
</div>
</body>
</html>
