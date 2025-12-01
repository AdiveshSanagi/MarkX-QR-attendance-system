<?php
require_once __DIR__ . '/db.php';
require_login();
$user = current_user();
if (!$user) { header('Location: login.php'); exit; }
$teacherId = (int)$user['id'];

// Define days and slot labels
$days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
$slotLabels = [
  '10:00 - 10:55',
  '10:55 - 11:50',
  '11:50 - 12:10',
  '12:10 - 1:05',
  '1:05 - 2:00'
];

// Handle POST save
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $values = $_POST['value'] ?? [];
  // Upsert each provided cell
  $ins = $conn->prepare('INSERT INTO teacher_personal_timetable (teacher_id, day_name, slot_index, value) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
  foreach ($days as $d) {
    if (!isset($values[$d])) continue;
    for ($i=0; $i<count($slotLabels); $i++) {
      $v = trim($values[$d][$i] ?? '');
      $ins->bind_param('isis', $teacherId, $d, $i, $v);
      $ins->execute();
    }
  }
  $ins->close();
  header('Location: personal_timetable.php?saved=1');
  exit;
}

// Load existing entries
$tt = [];
foreach ($days as $d) { $tt[$d] = array_fill(0, count($slotLabels), ''); }
$stmt = $conn->prepare('SELECT day_name, slot_index, value FROM teacher_personal_timetable WHERE teacher_id = ?');
$stmt->bind_param('i', $teacherId);
$stmt->execute();
$stmt->bind_result($dayNameRow, $slotIndexRow, $valueRow);
while ($stmt->fetch()) {
  if (isset($tt[$dayNameRow]) && isset($tt[$dayNameRow][(int)$slotIndexRow])) {
    $tt[$dayNameRow][(int)$slotIndexRow] = $valueRow;
  }
}
$stmt->close();

// Seed defaults if empty (based on the image provided)
$hasAny = false;
foreach ($days as $d) { foreach ($tt[$d] as $v) { if ($v !== '') { $hasAny = true; break 2; } } }
if (!$hasAny) {
  $defaults = [
    'Monday'    => ['', '', '', '', ''],
    'Tuesday'   => ['', 'RMIPR (5A)', '', '', 'RMIPR (5B)'],
    'Wednesday' => ['RMIPR (5B)', '', '', '', ''],
    'Thursday'  => ['', '', '', 'RMIPR (5A)', ''],
    'Friday'    => ['', 'RMIPR (5A)', '', '', ''],
    'Saturday'  => ['RMIPR (5B)', '', '', '', 'RMIPR (5B)'],
  ];
  $ins = $conn->prepare('INSERT INTO teacher_personal_timetable (teacher_id, day_name, slot_index, value) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)');
  foreach ($defaults as $d => $arr) {
    for ($i=0; $i<count($slotLabels); $i++) {
      $v = $arr[$i] ?? '';
      $ins->bind_param('isis', $teacherId, $d, $i, $v);
      $ins->execute();
      $tt[$d][$i] = $v;
    }
  }
  $ins->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Personal Time Table</title>
  <link rel="stylesheet" href="style.css" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <style>
    .container { max-width: 1100px; margin: 24px auto; padding: 0 16px; }
    .header { display:flex; justify-content: space-between; align-items:center; margin-bottom: 16px; }
    .card { background: var(--panel); border:1px solid var(--border); border-radius:16px; padding:20px; }
    .muted { color: var(--muted); }
    .btn { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:10px; border:1px solid var(--border); background:#0b1220; color: var(--text); text-decoration:none; }
    .tt { width:100%; border-collapse: collapse; }
    .tt th, .tt td { border:1px solid #cbd5e1; padding:10px; text-align:center; min-width:120px; }
    .tt th { background:#f1f5f9; font-weight:700; }
    .tt .day { text-align:left; font-weight:600; background:#f8fafc; }
    .tt input { width:100%; padding:6px 8px; border-radius:8px; border:1px solid var(--border); background:#0b1220; color:var(--text); text-align:center; }
    @media (max-width: 900px) {
      .tt th, .tt td { padding:8px; min-width:auto; font-size: 14px; }
    }
  </style>
</head>
<body class="timetable-page">
  <div class="container">
    <div class="header">
      <h1><i class="fas fa-user"></i> Personal Time Table</h1>
      <div>
        <a class="btn" href="teacher_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
      </div>
    </div>

    <?php if (isset($_GET['saved'])): ?>
      <div class="notification success"><i class="fas fa-check-circle"></i> Personal timetable saved.</div>
    <?php endif; ?>

    <div class="card">
      <form method="POST" action="personal_timetable.php">
      <table class="tt">
        <thead>
          <tr>
            <th>Day/Hour</th>
            <?php foreach ($slotLabels as $label): ?>
              <th><?php echo h($label); ?></th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($days as $d): ?>
            <tr>
              <td class="day"><?php echo h($d); ?></td>
              <?php for ($i=0; $i<count($slotLabels); $i++): ?>
                <td>
                  <input type="text" name="value[<?php echo h($d); ?>][<?php echo $i; ?>]" value="<?php echo h($tt[$d][$i]); ?>" />
                </td>
              <?php endfor; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div style="margin-top:12px; display:flex; gap:8px;">
        <button type="submit" class="btn"><i class="fas fa-save"></i> Save</button>
        <a class="btn" href="teacher_dashboard.php"><i class="fas fa-arrow-left"></i> Back</a>
      </div>
      </form>
    </div>
  </div>
</body>
</html>
