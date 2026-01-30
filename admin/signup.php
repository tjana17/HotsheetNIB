<?php
session_start();

$passFile = __DIR__ . "/password.txt";
$storedHash = file_exists($passFile) ? trim(file_get_contents($passFile)) : '';
$showForm = false;
$error = "";

// Handle Lock (clear session)
if (isset($_GET['lock'])) {
    unset($_SESSION['unlocked']);
    session_destroy();
    header("Location: signup");
    exit;
}

// Handle Unlock attempt
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['access_pass'])) {
    $inputPassword = $_POST['access_pass'];
    if ($storedHash && password_verify($inputPassword, $storedHash)) {
        $_SESSION['unlocked'] = true;
        $showForm = true;
    } else {
        $error = "Invalid password!";
    }
} elseif (isset($_SESSION['unlocked']) && $_SESSION['unlocked'] === true) {
    $showForm = true;
}
?>
<!-- Lock / Unlock Signup form -->
<?php
require 'db.php';
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard');
    exit;
}
$errors = $_SESSION['form_errors'] ?? [];
unset($_SESSION['form_errors']);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Sign up — New India Bazar</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
   <style>
    .error { color:#e53e3e; font-size:14px; margin-bottom:8px; }
    .lock-btn {
        display:block; margin-top:15px; text-align:center;
        background:#e53e3e; padding:8px; border-radius:6px; text-decoration:none; color:#fff; font-weight:600;
        transition:0.2s;
    }
    .lock-btn:hover { background:#c53030; }
</style> 
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <center><img src="https://www.newindiabazar.com/images/logo.png" alt="Logo" height="50"></a></center>
            <p>&nbsp;</p>
            <?php if (!$showForm): ?>
                
                        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
                        <form method="post">
                          <div class="mb-3">
                            <label class="form-label" for="name">🔒 Enter Access Password</label>
                            <input type="password" name="access_pass" placeholder="Password" class="form-control" required>
                          </div>
                          <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Unlock</button>
                          </div>
                        </form>
                    
            <?php else: ?>
            <h3 class="card-title mb-3 text-center">Create your account</h3>
            <?php if ($errors): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $e): ?>
                    <li><?=htmlspecialchars($e)?></li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
            <form id="signupForm" action="register.php" method="post" novalidate>
              <div class="mb-3">
                <label class="form-label" for="name">Full name</label>
                <input id="name" name="name" type="text" class="form-control" required minlength="2" maxlength="100" placeholder="John Doe">
              </div>
              <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input id="email" name="email" type="email" class="form-control" required placeholder="you@company.com">
              </div>
              <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input id="password" name="password" type="password" class="form-control" required minlength="8" placeholder="At least 8 characters">
              </div>
              <div class="mb-3">
                <label class="form-label" for="confirm_password">Confirm password</label>
                <input id="confirm_password" name="confirm_password" type="password" class="form-control" required minlength="8" placeholder="Retype password">
              </div>
              <div class="mb-3">
              <label>Role</label>
              <select name="role" class="form-select" id="role">
                <option value="user">User</option>
                <option value="admin">Admin</option>
              </select>
            </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Create account</button>
              </div>
            </form>
            <p class="text-center mt-3 mb-0">Already have an account? <a href="login">Sign in</a></p>
          </div>
          <a href="signup?lock=1" class="lock-btn">🔒 Lock</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<script>
document.getElementById('signupForm').addEventListener('submit', function(e){
  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const pw = document.getElementById('password').value;
  const cpw = document.getElementById('confirm_password').value;
  const role = document.getElementById('role').value;

  if (name.length < 2 || !email || pw.length < 8 || pw !== cpw) {
    e.preventDefault();
    alert('Please fix the form fields before submitting.');
  }
});
</script>
</body>
</html>
