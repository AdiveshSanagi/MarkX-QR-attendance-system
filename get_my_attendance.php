<?php
require_once __DIR__ . '/db.php';
require_login();
$u = current_user();
if (($u['role'] ?? '') !== 'student') {
  http_response_code(403);
  header('Content-Type: application/json');
  echo json_encode(['error' => 'Forbidden']);
  exit;
}

header('Content-Type: application/json');
$start = isset($_GET['start']) ? trim($_GET['start']) : '';
$end = isset($_GET['end']) ? trim($_GET['end']) : '';
$subject = isset($_GET['subject']) ? trim($_GET['subject']) : '';

// Build dynamic WHERE
$where = ['s.usn = ?'];
$params = [$u['username']];
$types = 's';

if ($start !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) {
  $where[] = 'a.date >= ?';
  $params[] = $start;
  $types .= 's';
}
if ($end !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
  $where[] = 'a.date <= ?';
  $params[] = $end;
  $types .= 's';
}
if ($subject !== '') {
  $where[] = 'a.subject = ?';
  $params[] = $subject;
  $types .= 's';
}

$sql = 'SELECT a.date, a.subject, a.status
        FROM attendance a
        JOIN students s ON s.id = a.student_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY a.date DESC, a.subject ASC';

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$out = [];
while ($row = $res->fetch_assoc()) {
  $out[] = [
    'date' => $row['date'],
    'subject' => $row['subject'],
    'status' => $row['status'],
  ];
}
$stmt->close();

echo json_encode($out);
