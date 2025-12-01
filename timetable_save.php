<?php
require_once __DIR__ . '/db.php';
require_login();
$u = current_user();
if (($u['role'] ?? '') !== 'teacher') {
  http_response_code(403);
  echo 'Forbidden';
  exit;
}

$payload = file_get_contents('php://input');
$data = json_decode($payload, true);
if (!$data || !isset($data['rows']) || !is_array($data['rows'])) {
  http_response_code(400);
  echo 'Bad Request';
  exit;
}

$stmt = $conn->prepare('INSERT INTO timetable (day_name, period, subject) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE subject = VALUES(subject)');
foreach ($data['rows'] as $row) {
  $day = trim($row['day'] ?? '');
  $period = (int)($row['period'] ?? 0);
  $subject = trim($row['subject'] ?? '');
  if ($day === '' || $period <= 0) { continue; }
  $stmt->bind_param('sis', $day, $period, $subject);
  $stmt->execute();
}
$stmt->close();

header('Content-Type: application/json');
echo json_encode(['ok' => true]);
?>


