<?php
require_once __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: teacher_signup.php');
  exit;
}

$adminPassword = trim($_POST['admin_code'] ?? '');
$name = trim($_POST['admin_name'] ?? '');
$phone = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm = trim($_POST['confirm'] ?? '');

// Check admin code
if ($adminPassword !== 'kartik') {
  header('Location: teacher_signup.php?admin_error=1');
  exit;
}

if ($name === '' || $phone === '' || $password === '' || $confirm === '' || $password !== $confirm) {
  header('Location: teacher_signup.php?error=1');
  exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$role = 'teacher';
$username = $phone; // use email as login identifier here
$stmt = $conn->prepare('INSERT INTO users (username, password_hash, role, name, phone) VALUES (?, ?, ?, ?, ?)');
$stmt->bind_param('sssss', $username, $hash, $role, $name, $phone);
if (!$stmt->execute()) {
  // duplicate phone
  if ($conn->errno === 1062) {
    header('Location: teacher_signup.php?error=teacher_exists');
    exit;
  }
  header('Location: teacher_signup.php?error=1');
  exit;
}
$stmt->close();

header('Location: teacher_signup.php?registered=teacher');
exit;
?>

