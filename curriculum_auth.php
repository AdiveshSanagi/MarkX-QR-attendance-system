<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'POST') { header('Location: curriculum_portal.php'); exit; }

$mode = ($_POST['mode'] ?? '') === 'signup' ? 'signup' : 'login';
$name = trim($_POST['name'] ?? '');
$password = trim($_POST['password'] ?? '');
$adminCode = trim($_POST['admin_code'] ?? '');

if ($adminCode !== 'akash') { header('Location: curriculum_portal.php?admin_error=1'); exit; }

if ($mode === 'signup') {
  $phone = trim($_POST['phone'] ?? '');
  $confirm = trim($_POST['confirm'] ?? '');
  if ($name === '' || $phone === '' || $password === '' || $confirm === '' || $password !== $confirm) {
    header('Location: curriculum_portal.php?error=1'); exit;
  }
  $hash = password_hash($password, PASSWORD_DEFAULT);
  // store as a user with role curriculum
  $username = 'curr_' . preg_replace('/\D+/', '', $phone);
  $role = 'curriculum';
  $stmt = $conn->prepare('INSERT INTO users (username, password_hash, role, name, phone) VALUES (?, ?, ?, ?, ?) 
                          ON DUPLICATE KEY UPDATE role = VALUES(role), name = VALUES(name), password_hash = VALUES(password_hash)');
  $stmt->bind_param('sssss', $username, $hash, $role, $name, $phone);
  if (!$stmt->execute()) { header('Location: curriculum_portal.php?error=1'); exit; }
  $stmt->close();
  header('Location: curriculum_portal.php?registered=1'); exit;
}

// login
if ($name === '' || $password === '') { header('Location: curriculum_portal.php?error=1'); exit; }
$stmt = $conn->prepare('SELECT id, username, password_hash, role, name FROM users WHERE role = "curriculum" AND name = ? LIMIT 1');
$stmt->bind_param('s', $name);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();
$stmt->close();

if ($user && password_verify($password, $user['password_hash'])) {
  $_SESSION['user'] = [
    'id' => (int)$user['id'],
    'username' => $user['username'],
    'name' => $user['name'],
    'role' => 'curriculum',
  ];
  header('Location: curriculum_timetable.php');
  exit;
}

header('Location: curriculum_portal.php?error=1');
exit;
?>


