<?php
require_once __DIR__ . '/db.php';
require_login();
$user = current_user();
// Optional: restrict to teachers only, but allow others gracefully
if (!$user) { header('Location: login.php'); exit; }
$name = h($user['name'] ?? '');
$username = h($user['username'] ?? '');
$role = h($user['role'] ?? '');
// Pull last_login if available
$lastLogin = '';
if (!empty($user['id'])) {
  $uid = (int)$user['id'];
  $stmt = $conn->prepare('SELECT last_login, phone, name FROM users WHERE id = ? LIMIT 1');
  $stmt->bind_param('i', $uid);
  $stmt->execute();
  $stmt->bind_result($lastLoginDb, $phoneDb, $nameDb);
  if ($stmt->fetch()) {
    $lastLogin = $lastLoginDb ? date('Y-m-d H:i', strtotime($lastLoginDb)) : '';
    if (!empty($nameDb)) { $name = h($nameDb); }
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Teacher Dashboard</title>
  <link rel="stylesheet" href="style.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .dashboard {
      max-width: 1200px; margin: 24px auto; padding: 0 16px;
    }
    .dash-header { display:flex; justify-content: space-between; align-items:center; margin-bottom:16px; }
    .dash-header .title { font-size: 24px; font-weight: 700; }
    .dash-header .actions a { margin-left: 8px; }
    .profile-card { background: var(--panel); border:1px solid var(--border); border-radius:16px; padding:16px; margin-bottom:16px; }
    .grid-2 { display:grid; grid-template-columns: 1fr 1fr; gap:16px; }
    .grid-card { background: var(--panel); border:1px solid var(--border); border-radius:16px; padding:20px; }
    .grid-card h3 { margin: 0 0 8px 0; }
    .grid-card p { margin: 0 0 12px 0; color: var(--muted); }
    .btn { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:10px; border:1px solid var(--border); background:#0b1220; color: var(--text); text-decoration:none; }
    .btn-primary { background: var(--primary); color: #fff; border-color: transparent; }
    .muted { color: var(--muted); }
  </style>
</head>
<body class="timetable-page">
  <div class="dashboard">
    <div class="dash-header">
      <div class="title"><i class="fas fa-chalkboard-teacher"></i> Welcome, <?php echo $name ?: $username; ?></div>
      <div class="actions">
        <a class="btn" href="index.php"><i class="fas fa-home"></i> Home</a>
        <a class="btn" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>

    <div class="profile-card">
      <div><strong>Name:</strong> <?php echo $name ?: $username; ?></div>
      <div><strong>Username:</strong> <?php echo $username; ?></div>
      <div><strong>Role:</strong> <?php echo $role; ?></div>
      <?php if ($lastLogin): ?><div class="muted"><i class="fas fa-clock"></i> Last login: <?php echo h($lastLogin); ?></div><?php endif; ?>
    </div>

    <div class="grid-2">
      <div class="grid-card">
        <h3><i class="fas fa-table"></i> Time Table</h3>
        <p>View and access the main class timetable.</p>
        <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-right"></i> Open Timetable</a>
      </div>
      <div class="grid-card">
        <h3><i class="fas fa-calendar-alt"></i> Personal Time Table</h3>
        <p>Manage your own personal teaching schedule.</p>
        <a href="personal_timetable.php" class="btn btn-primary"><i class="fas fa-user"></i> Open Personal Timetable</a>
      </div>
    </div>
  </div>
</body>
</html>
