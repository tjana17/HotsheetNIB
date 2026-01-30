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
              </div>
              <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input id="password" name="password" type="password" class="form-control" required placeholder="Your password">
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
</body>
</html>
