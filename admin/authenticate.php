<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
    $_SESSION['flash'] = 'Invalid credentials.';
    header('Location: login.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, name, email, password_hash,role FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password_hash'])) {
    $_SESSION['flash'] = 'Invalid email or password.';
    header('Location: login.php');
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['role'] = $user['role'];
$conn->query("UPDATE users SET last_login=NOW() WHERE id=".$user['id']);
// log activity
log_activity($conn, $user['id'], 'Logged in');

header('Location: dashboard.php');
exit;
?>
