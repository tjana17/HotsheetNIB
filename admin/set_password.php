<?php
$passFile = __DIR__ . "/password.txt";
$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['new_pass'])) {
    $newPass = $_POST['new_pass'];
    $hash = password_hash($newPass, PASSWORD_DEFAULT);
    file_put_contents($passFile, $hash);
    $message = "✅ New password saved successfully.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Set Access Password</title>
<style>
    body { font-family:'Segoe UI', sans-serif; background:#edf2f7; margin:0; }
    .box {
        max-width:400px; margin:80px auto; background:#fff; padding:25px;
        border-radius:12px; box-shadow:0 4px 16px rgba(0,0,0,0.15);
    }
    h2 { margin-top:0; color:#2d3748; }
    input[type="password"] {
        width:100%; padding:10px; margin:10px 0; border:1px solid #cbd5e0; border-radius:6px;
    }
    button {
        padding:10px; width:100%; border:none; border-radius:6px;
        background:#2b6cb0; color:#fff; font-weight:600; cursor:pointer; transition:.2s;
    }
    button:hover { background:#234a8b; }
    .msg { margin-top:12px; font-size:14px; color:#2f855a; }
</style>
</head>
<body>
<div class="box">
    <h2>Set / Update Access Password</h2>
    <form method="post">
        <input type="password" name="new_pass" placeholder="Enter New Password" required>
        <button type="submit">Save Password</button>
    </form>
    <?php if ($message): ?><p class="msg"><?= htmlspecialchars($message) ?></p><?php endif; ?>
</div>
</body>
</html>
