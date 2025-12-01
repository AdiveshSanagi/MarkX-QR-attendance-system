<?php require_once __DIR__ . '/db.php'; if (current_user()) { header('Location: index.php'); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Login - QR Attendance</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page student">
  <div class="auth-container">
    <a href="login.php" class="back-btn">⟵ Back to Main</a>
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-icon">🎓</div>
        <h1>Student Login</h1>
        <p>Access your dashboard and mark attendance</p>
      </div>

      <div class="tabs" role="tablist" aria-label="Login options">
        <button type="button" class="tab active" data-tab="form" aria-selected="true">Login with USN</button>
        <a href="qr_scan.php" class="tab" data-tab="qr" style="text-decoration:none;">Scan QR</a>
      </div>

      <?php if (isset($_GET['registered'])): ?>
        <div class="notification success">Account created successfully! Please log in below.</div>
      <?php endif; ?>
      <?php if (isset($_GET['error'])): ?>
        <div class="error">Invalid USN or password. Please try again.</div>
      <?php endif; ?>

      <div id="tab-form" class="tab-panel active">
        <form method="POST" action="auth_login.php" class="auth-form">
          <input type="hidden" name="mode" value="student" />

          <div class="form-group">
            <label for="ls-usn">USN</label>
            <div class="input-wrapper">
              <i>🆔</i>
              <input type="text" id="ls-usn" name="username" required autofocus placeholder="Enter your USN" />
            </div>
          </div>

          <div class="form-group">
            <label for="ls-pass">Password</label>
            <div class="input-wrapper">
              <i>🔒</i>
              <input type="password" id="ls-pass" name="password" required placeholder="Enter your password" />
            </div>
          </div>

          <div class="form-actions">
            <button type="submit" class="button">Login</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>

