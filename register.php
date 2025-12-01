<?php
require_once __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: login.php');
  exit;
}

$mode = ($_POST['mode'] ?? '') === 'student' ? 'student' : 'teacher';

if ($mode === 'teacher') {
  $name = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $confirm = trim($_POST['confirm'] ?? '');
  if ($name === '' || $phone === '' || $password === '' || $confirm === '' || $password !== $confirm) {
    header('Location: login.php?error=1');
    exit;
  }
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $role = 'teacher';
  $username = $phone; // use phone as login identifier
  $stmt = $conn->prepare('INSERT INTO users (username, password_hash, role, name, phone) VALUES (?, ?, ?, ?, ?)');
  $stmt->bind_param('sssss', $username, $hash, $role, $name, $phone);
  if (!$stmt->execute()) {
    // duplicate phone
    if ($conn->errno === 1062) {
      header('Location: login.php?error=teacher_exists');
      exit;
    }
    header('Location: login.php?error=1');
    exit;
  }
  $stmt->close();
  header('Location: login.php?registered=teacher&action=login');
  exit;
}

// student registration
$name = trim($_POST['name'] ?? '');
$usn = trim($_POST['usn'] ?? '');
$class = trim($_POST['class'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm = trim($_POST['confirm'] ?? '');

if ($name === '' || $usn === '' || $class === '' || $phone === '' || $password === '' || $confirm === '' || $password !== $confirm) {
  header('Location: login.php?error=1');
  exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);
// Upsert student on duplicate USN
$sql = 'INSERT INTO students (usn, name, class, phone, password_hash) VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE name = VALUES(name), class = VALUES(class), phone = VALUES(phone), password_hash = VALUES(password_hash)';
$stmt = $conn->prepare($sql);
$stmt->bind_param('sssss', $usn, $name, $class, $phone, $hash);
if (!$stmt->execute()) {
  if ($conn->errno === 1062) {
    header('Location: login.php?error=student_exists');
    exit;
  }
  header('Location: login.php?error=1');
  exit;
}
$stmt->close();

header('Location: student_signup.php?registered=student');
exit;
?>


