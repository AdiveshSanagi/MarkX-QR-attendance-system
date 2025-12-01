<?php
require_once __DIR__ . '/db.php'; require_login();
header('Content-Type: application/json');

$subject = isset($_GET['subject']) ? trim($_GET['subject']) : '';
$today = date('Y-m-d');

if ($subject === '') {
  echo json_encode([]);
  exit;
}

$sql = 'SELECT s.usn, s.name, s.class
        FROM attendance a
        JOIN students s ON s.id = a.student_id
        WHERE a.subject = ? AND a.date = ? AND a.status = "Present"
        ORDER BY s.usn ASC';

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $subject, $today);
$stmt->execute();
$result = $stmt->get_result();
$rows = [];
while ($row = $result->fetch_assoc()) {
  $rows[] = [
    'usn' => $row['usn'],
    'name' => $row['name'],
    'class' => $row['class'],
  ];
}
$stmt->close();

echo json_encode($rows);
?>

