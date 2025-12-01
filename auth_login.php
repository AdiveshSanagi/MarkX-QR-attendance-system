<?php
require_once __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
  header('Location: login.php');
  exit;
}

// mode: teacher or student
$mode = ($_POST['mode'] ?? 'teacher') === 'student' ? 'student' : 'teacher';
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($mode === 'teacher') {
  // teacher login by phone (stored as username for simplicity)
  $stmt = $conn->prepare('SELECT id, username, password_hash, role, name FROM users WHERE username = ? OR phone = ? LIMIT 1');
  $stmt->bind_param('ss', $username, $username);
  $stmt->execute();
  $res = $stmt->get_result();
  $user = $res->fetch_assoc();
  $stmt->close();

  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user'] = [
      'id' => (int)$user['id'],
      'username' => $user['username'],
      'name' => $user['name'] ?? $user['username'],
      'role' => $user['role'],
    ];
    header('Location: index.php');
    exit;
  }
} else {
  // student login by USN
  $stmt = $conn->prepare('SELECT id, usn, name, password_hash FROM students WHERE usn = ? LIMIT 1');
  $stmt->bind_param('s', $username);
  $stmt->execute();
  $res = $stmt->get_result();
  $student = $res->fetch_assoc();
  $stmt->close();

  if ($student && !empty($student['password_hash']) && password_verify($password, $student['password_hash'])) {
    $_SESSION['user'] = [
      'id' => (int)$student['id'],
      'username' => $student['usn'],
      'name' => $student['name'],
      'role' => 'student',
    ];
    // After student login, open Student Profile (scanner + my attendance)
    header('Location: student_profile.php');
    exit;
  }
}

header('Location: login.php?error=1');
exit;
?>

