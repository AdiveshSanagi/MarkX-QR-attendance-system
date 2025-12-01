<?php
require_once __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: teacher_login.php');
  exit;
}

$adminPassword = trim($_POST['admin_password'] ?? '');
$adminName = trim($_POST['admin_name'] ?? '');
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Check admin code
if ($adminPassword !== 'kartik') {
  header('Location: teacher_login.php?admin_error=1');
  exit;
}

// teacher login by email (stored as username)
$stmt = $conn->prepare('SELECT id, username, password_hash, role, name FROM users WHERE username = ? OR phone = ? LIMIT 1');
$stmt->bind_param('ss', $username, $username);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if ($user && password_verify($password, $user['password_hash'])) {
  // Update last login timestamp
  $uid = (int)$user['id'];
  $upd = $conn->prepare('UPDATE users SET last_login = NOW() WHERE id = ?');
  $upd->bind_param('i', $uid);
  $upd->execute();
  $upd->close();

  $_SESSION['user'] = [
    'id' => $uid,
    'username' => $user['username'],
    'name' => $user['name'] ?? $user['username'],
    'role' => $user['role'],
  ];
  header('Location: teacher_dashboard.php');
  exit;
}

header('Location: teacher_login.php?error=1');
exit;
?>

