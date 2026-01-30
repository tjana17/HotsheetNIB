<?php
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.php');
    exit;
}
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';
$role = $_POST['role'] ?? 'user';

$errors = [];
if (strlen($name) < 2) $errors[] = 'Name must be at least 2 characters.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email address.';
if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
if ($password !== $confirm) $errors[] = 'Passwords do not match.';

if ($errors) {
    $_SESSION['form_errors'] = $errors;
    header('Location: signup.php');
    exit;
}

// check existing email
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => $email]);
if ($stmt->fetch()) {
    $_SESSION['form_errors'] = ['Email already registered.'];
    header('Location: signup.php');
    exit;
}

// insert
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash,role) VALUES (:name, :email, :hash, :role)');
$stmt->execute(['name' => $name, 'email' => $email, 'hash' => $hash, 'role' => $role]);

$userId = $pdo->lastInsertId();
session_regenerate_id(true);
$_SESSION['user_id'] = $userId;
$_SESSION['user_name'] = $name;
$_SESSION['user_email'] = $email;
$conn->query("UPDATE users SET last_login=NOW() WHERE id=".$user['id']);
// log activity
log_activity($conn, $user['id'], 'Logged in');

header('Location: dashboard.php');
exit;
?>
