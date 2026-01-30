<?php
session_start();
include 'db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin'){ header('Location: login.php'); exit; }

// handle admin actions
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(isset($_POST['delete_user'])){
        $uid = (int)$_POST['uid'];
        // log before deletion
        log_activity($conn, $_SESSION['user_id'], 'Admin deleted user '.$uid);
        $conn->query("DELETE FROM users WHERE id=$uid");
    }
    if(isset($_POST['update_pass'])){
        // $uid = (int)$_POST['uid'];
        // $newpass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        // $stmt = $conn->prepare("UPDATE users SET password_hash=? WHERE id=?");
        // $stmt->bind_param("si",$newpass,$uid);
        // $stmt->execute();
        $uid = (int)$_POST['uid'];
        $username = $_POST['username'];
        $newpass = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password_hash=? WHERE id=?");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param("si", $newpass, $uid);

        if ($stmt->execute()) {
            echo "<script>alert('" . $username . "`s Password updated successfully.');</script>";
        } else {
            echo "Error updating password: " . $stmt->error;
        }
        // log
        log_activity($conn, $_SESSION['user_id'], $_SESSION['user_name'] .' - Admin updated password for user '.$uid);
        $stmt->close();
    }
}

$users = $conn->query("SELECT id,name,email,role,created_at,last_login FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard - New India Bazar</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="dashboard"><img src="https://www.newindiabazar.com/images/logo.png" alt="Logo" height="50"></a>
    <div class="d-flex">
      <a href="activities" class="btn btn-outline-primary me-2">View Activities</a>
      <a href="logout" class="btn btn-outline-secondary">Signout</a>
    </div>
  </div>
</nav>
<div class="container py-4">
  <h4>Users</h4>
  <table class="table table-bordered">
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Created</th><th>Last Login</th><th>Actions</th></tr>
    <?php while($user = $users->fetch_assoc()): ?>
      <tr>
        <td><?= $user['id'] ?></td>
        <td><?= htmlspecialchars($user['name']) ?></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
        <td><?= $user['role'] ?></td>
        <td><?= $user['created_at'] ?></td>
        <td><?= $user['last_login'] ?></td>
        <td>
          <form method="post" style="display:inline;">
            <input type="hidden" name="uid" value="<?= $user['id'] ?>">
            <input type="hidden" name="username" value="<?= $user['name'] ?>">
            <input type="password" name="new_password" class="form-control form-control-sm d-inline-block" style="width:150px" placeholder="New password" required>
            <button name="update_pass" class="btn btn-sm btn-warning">Update</button>
          </form>
          <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to Delete this user?');">
            <input type="hidden" name="uid" value="<?= $user['id'] ?>">
            <button name="delete_user" class="btn btn-sm btn-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
  <a href="dashboard" class="btn btn-secondary">Back</a>
</div>
</body>
</html>