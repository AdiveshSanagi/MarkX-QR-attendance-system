<?php
require_once __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$subject = isset($_REQUEST['subject']) ? trim($_REQUEST['subject']) : '';
$period = isset($_REQUEST['period']) ? trim($_REQUEST['period']) : '';
$dateParam = isset($_REQUEST['date']) ? trim($_REQUEST['date']) : date('Y-m-d');
$token = isset($_REQUEST['token']) ? trim($_REQUEST['token']) : '';

// Rotating token parameters
$isRotating = isset($_REQUEST['rt']) && $_REQUEST['rt'] == '1';
$sessionId = isset($_REQUEST['sid']) ? (int)$_REQUEST['sid'] : 0;
$slot = isset($_REQUEST['slot']) ? (int)$_REQUEST['slot'] : -1;

$validLink = false;
$invalidReason = '';

// Validate link
if ($isRotating && $sessionId > 0 && $slot >= 0) {
  // Validate against qr_sessions table
  $stmt = $conn->prepare('SELECT start_time, expires_at, secret FROM qr_sessions WHERE id = ? LIMIT 1');
  $stmt->bind_param('i', $sessionId);
  $stmt->execute();
  $stmt->bind_result($startTime, $expiresAt, $secret);
  if ($stmt->fetch()) {
    $stmt->close();
    // Compute expected token
    $payload = $subject . '|' . $period . '|' . $dateParam . '|' . $slot . '|' . $sessionId;
    $expectedHmac = hash_hmac('sha256', $payload, $secret, false);
    $now = time();
    $startTs = strtotime($startTime);
    $expTs = strtotime($expiresAt);
    // Determine current slot index since start, 15s steps
    $currentSlot = ($startTs !== false && $now >= $startTs) ? (int)floor(($now - $startTs) / 15) : -1;
    if (!hash_equals((string)$expectedHmac, (string)$token)) {
      $invalidReason = 'Signature mismatch';
    } elseif ($startTs === false || $expTs === false) {
      $invalidReason = 'Invalid session time';
    } elseif ($now < $startTs) {
      $invalidReason = 'Session not started';
    } elseif ($now >= $expTs) {
      $invalidReason = 'Session expired';
    } elseif ($slot !== $currentSlot) {
      $invalidReason = 'Slot expired';
    } else {
      $validLink = true;
      // Mark session as validated in PHP session for this sid until expiry to allow POST submission
      if (!isset($_SESSION['rt_qr'])) { $_SESSION['rt_qr'] = []; }
      $_SESSION['rt_qr'][$sessionId] = $expiresAt;
    }
  } else {
    $stmt->close();
    $invalidReason = 'Session not found';
  }
} else {
  // Fallback: basic token validation for legacy/static links (valid for date only)
  $expectedToken = hash('sha256', $subject . '|' . $period . '|' . $dateParam);
  if ($subject && $period && $token && hash_equals($expectedToken, $token)) {
    $validLink = true;
  } else {
    $validLink = false;
    $invalidReason = 'Invalid or expired link';
  }
}

if ($method === 'POST') {
  header('Content-Type: text/html; charset=UTF-8');

  $usn = trim($_POST['usn'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $class = trim($_POST['class'] ?? '');
  $phone = trim($_POST['phone'] ?? '');

  // Re-validate for rotating sessions: allow if either current token still valid OR a previously validated session exists and not expired
  if ($isRotating && $sessionId > 0) {
    $allowPost = false;
    // Check stored session permit
    if (isset($_SESSION['rt_qr'][$sessionId])) {
      $expTs = strtotime($_SESSION['rt_qr'][$sessionId]);
      if ($expTs !== false && time() < $expTs) {
        $allowPost = true;
      }
    }
    // If no stored permit, try a fresh validation (student might reload directly on POST)
    if (!$allowPost) {
      $stmt = $conn->prepare('SELECT start_time, expires_at, secret FROM qr_sessions WHERE id = ? LIMIT 1');
      $stmt->bind_param('i', $sessionId);
      $stmt->execute();
      $stmt->bind_result($startTime2, $expiresAt2, $secret2);
      if ($stmt->fetch()) {
        $stmt->close();
        $now = time();
        $startTs2 = strtotime($startTime2);
        $expTs2 = strtotime($expiresAt2);
        $currentSlot2 = ($startTs2 !== false && $now >= $startTs2) ? (int)floor(($now - $startTs2) / 15) : -1;
        $payload2 = $subject . '|' . $period . '|' . $dateParam . '|' . $currentSlot2 . '|' . $sessionId;
        $expectedHmac2 = hash_hmac('sha256', $payload2, $secret2, false);
        if ($now >= $startTs2 && $now < $expTs2 && hash_equals($expectedHmac2, $token)) {
          $allowPost = true;
        }
      } else {
        $stmt->close();
      }
    }
    if (!$allowPost) {
      $error = 'This QR session is no longer valid. Please rescan a fresh QR.';
    }
  }

  if ($usn === '' || $name === '' || $class === '' || $phone === '') {
    $error = 'All fields are required.';
  } else {
    // Upsert student
    $stmt = $conn->prepare('SELECT id FROM students WHERE usn = ? LIMIT 1');
    $stmt->bind_param('s', $usn);
    $stmt->execute();
    $stmt->bind_result($studentId);
    if ($stmt->fetch()) {
      $stmt->close();
    } else {
      $stmt->close();
      $ins = $conn->prepare('INSERT INTO students (usn, name, class, phone) VALUES (?, ?, ?, ?)');
      $ins->bind_param('ssss', $usn, $name, $class, $phone);
      if (!$ins->execute()) {
        $error = 'Failed to register student: ' . h($conn->error);
      }
      $studentId = $conn->insert_id;
      $ins->close();
    }

    // If no insert id (existing), fetch it now
    if (empty($studentId)) {
      $stmt2 = $conn->prepare('SELECT id FROM students WHERE usn = ? LIMIT 1');
      $stmt2->bind_param('s', $usn);
      $stmt2->execute();
      $stmt2->bind_result($studentId);
      $stmt2->fetch();
      $stmt2->close();
    }

    // Mark attendance with time restriction (e.g., one submission per subject every 30 minutes)
    if (!isset($error)) {
      $today = $dateParam;
      $status = 'Present';

      // Check last submission time for this student & subject today
      $check = $conn->prepare('SELECT created_at FROM attendance WHERE student_id = ? AND subject = ? AND date = ? ORDER BY created_at DESC LIMIT 1');
      $check->bind_param('iss', $studentId, $subject, $today);
      $check->execute();
      $check->bind_result($lastCreatedAt);
      $hasPrev = $check->fetch();
      $check->close();

      $cooldownMinutes = 30; // adjust as needed
      if ($hasPrev) {
        $lastTs = strtotime($lastCreatedAt);
        if ($lastTs !== false && (time() - $lastTs) < ($cooldownMinutes * 60)) {
          $remaining = ($cooldownMinutes * 60) - (time() - $lastTs);
          $mins = ceil($remaining / 60);
          $error = 'You can submit attendance again in approximately ' . h($mins) . ' minute(s).';
        }
      }

      if (!isset($error)) {
        $ins2 = $conn->prepare('INSERT INTO attendance (student_id, subject, date, status) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE status = VALUES(status)');
        $ins2->bind_param('isss', $studentId, $subject, $today, $status);
        if (!$ins2->execute()) {
          $error = 'Failed to record attendance: ' . h($conn->error);
        }
        $ins2->close();
      }
    }

    if (!isset($error)) {
      echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"/><link rel="stylesheet" href="style.css"><title>Attendance Marked</title></head><body>';
      echo '<div class="container">';
      echo '<h1>Attendance Marked</h1>';
      echo '<p>Thank you, ' . h($name) . ' (' . h($usn) . '). Your attendance for <strong>' . h($subject) . '</strong> on ' . h($today) . ' is recorded.</p>';
      echo '</div></body></html>';
      exit;
    }
  }
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mark Attendance - QR Attendance</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="auth-page student">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-icon">
          <i class="fas fa-qrcode"></i>
        </div>
        <h1>Mark Attendance</h1>
        <p>Submit your attendance details</p>
      </div>
      
      <?php if (!$validLink): ?>
        <div class="notification error">
          <i class="fas fa-exclamation-triangle"></i>
          Warning: Invalid or expired QR link. You can still submit the form below if the subject is correct.
        </div>
      <?php endif; ?>

      <?php if (isset($error)): ?>
        <div class="notification error">
          <i class="fas fa-exclamation-circle"></i>
          <?php echo h($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="scan.php" class="auth-form">
        <?php if ($subject !== ''): ?>
          <input type="hidden" name="subject" value="<?php echo h($subject); ?>" />
          <div class="form-group">
            <label>Subject</label>
            <div class="input-wrapper">
              <i class="fas fa-book"></i>
              <input type="text" value="<?php echo h($subject); ?>" readonly style="background: rgba(255, 255, 255, 0.05);" />
            </div>
          </div>
        <?php else: ?>
          <div class="form-group">
            <label for="subject">Subject</label>
            <div class="input-wrapper">
              <i class="fas fa-book"></i>
              <input type="text" id="subject" name="subject" required placeholder="Enter subject name" />
            </div>
          </div>
        <?php endif; ?>
        <input type="hidden" name="period" value="<?php echo h($period); ?>" />
        <input type="hidden" name="date" value="<?php echo h($dateParam); ?>" />
        <input type="hidden" name="token" value="<?php echo h($token); ?>" />
        <?php if ($isRotating): ?>
          <input type="hidden" name="rt" value="1" />
          <input type="hidden" name="sid" value="<?php echo h($sessionId); ?>" />
          <input type="hidden" name="slot" value="<?php echo h($slot); ?>" />
        <?php endif; ?>

        <div class="form-group">
          <label for="usn">USN</label>
          <div class="input-wrapper">
            <i class="fas fa-id-card"></i>
            <input type="text" id="usn" name="usn" required autocomplete="off" placeholder="Enter your USN" />
          </div>
        </div>
        <div class="form-group">
          <label for="name">Full Name</label>
          <div class="input-wrapper">
            <i class="fas fa-user"></i>
            <input type="text" id="name" name="name" required autocomplete="off" placeholder="Enter your full name" />
          </div>
        </div>
        <div class="form-group">
          <label for="class">Class/Section</label>
          <div class="input-wrapper">
            <i class="fas fa-graduation-cap"></i>
            <input type="text" id="class" name="class" required autocomplete="off" placeholder="Enter your class/section" />
          </div>
        </div>
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <div class="input-wrapper">
            <i class="fas fa-phone"></i>
            <input type="tel" id="phone" name="phone" required inputmode="numeric" autocomplete="tel" placeholder="Enter your phone number" />
          </div>
        </div>
        <button type="submit" class="btn btn-primary full-width">
          <i class="fas fa-check-circle"></i>
          Submit Attendance
        </button>
      </form>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>

