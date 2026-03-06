<?php
require 'db.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard');
    exit;
}
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sign in — New India Bazar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card shadow-sm">
          <div class="card-body">
            <center>
            <img src="https://www.newindiabazar.com/images/logo.png" alt="Logo" height="50"></a></center>
            <h3 class="card-title mb-3 text-center">Sign in</h3>
            <?php if ($flash): ?><div class="alert alert-danger"><?=htmlspecialchars($flash)?></div><?php endif; ?>
            <form id="loginForm" action="authenticate.php" method="post" novalidate>
              <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input id="email" name="email" type="email" class="form-control" required placeholder="you@company.com">
                <div class="invalid-feedback">Please enter your email address.</div>
              </div>
              <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <div class="input-group">
                  <input id="password" name="password" type="password" class="form-control" required placeholder="Your password">
                  <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye"></i>
                  </button>
                  <div class="invalid-feedback">Please enter your password.</div>
                </div>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Sign in</button>
              </div>
            </form>
            <!-- <p class="text-center mt-3 mb-0">Don't have an account? <a href="signup.php">Create one</a></p> -->
          </div>
        </div>
      </div>
    </div>
  </div>
  <script>
    document.getElementById('togglePassword').addEventListener('click', function() {
      const passwordInput = document.getElementById('password');
      const icon = this.querySelector('i');
      if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
      } else {
        passwordInput.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
      }
    });

    document.getElementById('loginForm').addEventListener('submit', function(e) {
      let isValid = true;
      const email = document.getElementById('email');
      const password = document.getElementById('password');

      [email, password].forEach(el => el.classList.remove('is-invalid'));

      if (!email.value.trim()) {
        email.classList.add('is-invalid');
        isValid = false;
      }
      if (!password.value.trim()) {
        password.classList.add('is-invalid');
        isValid = false;
      }

      if (!isValid) {
        e.preventDefault();
      }
    });
  </script>
</body>
</html>
