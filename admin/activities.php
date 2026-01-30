<?php
session_start();
include 'db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role']!='admin'){ header('Location: login.php'); exit; }

// optional filtering
$where = '';
$params = [];
if(!empty($_GET['user_id'])){ $uid = (int)$_GET['user_id']; $where = "WHERE ua.user_id=$uid"; }

$sql = "SELECT ua.id, ua.user_id, ua.action, ua.created_at, u.name, u.email FROM user_activity ua LEFT JOIN users u ON ua.user_id = u.id $where ORDER BY ua.created_at DESC LIMIT 500";
$res = $conn->query($sql);

// get users list for filter
$usr = $conn->query("SELECT id, name FROM users ORDER BY name ASC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>User Activities - New India Bazar</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand" href="dashboard"><img src="https://www.newindiabazar.com/images/logo.png" alt="Logo" height="50"></a>
    <div class="d-flex">
      <a href="admin_dashboard" class="btn btn-outline-secondary">Back</a>
    </div>
  </div>
</nav>
<div class="container py-4">
  <h4>User activities</h4>
  <form class="row g-2 mb-3" method="get">
    <div class="col-auto">
      <select name="user_id" class="form-select">
        <option value="">All users</option>
        <?php while($r = $usr->fetch_assoc()): ?>
          <option value="<?=$r['id']?>" <?=(!empty($_GET['user_id']) && $_GET['user_id']==$r['id'])?'selected':''?>><?=htmlspecialchars($r['name'])?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-primary">Filter</button>
    </div>
  </form>

  <table class="table table-striped">
    <tr><th>Time</th><th>User</th><th>Email</th><th>Action</th></tr>
    <?php while($a = $res->fetch_assoc()): ?>
      <tr>
        <td><?= $a['created_at'] ?></td>
        <td><?= htmlspecialchars($a['name'] ?? '—') ?> (<?= $a['user_id'] ?>)</td>
        <td><?= htmlspecialchars($a['email'] ?? '—') ?></td>
        <td><?= htmlspecialchars($a['action']) ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>
</body>
</html>