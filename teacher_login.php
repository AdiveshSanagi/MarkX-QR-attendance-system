<?php require_once __DIR__ . '/db.php'; if (current_user()) { header('Location: teacher_dashboard.php'); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Login - QR Attendance</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="auth-page teacher">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-icon">
          <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <h1>Teacher Portal</h1>
        <p>Manage classes, generate QR codes, and track attendance</p>
      </div>
      
      <a href="login.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        Back to Main
      </a>
      
      <?php if (isset($_GET['registered'])): ?>
        <div class="notification success">
          <i class="fas fa-check-circle"></i>
          Account created successfully! Please log in below.
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['error'])): ?>
        <div class="notification error">
          <i class="fas fa-exclamation-circle"></i>
          Invalid email or password. Please try again.
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['admin_error'])): ?>
        <div class="notification error">
          <i class="fas fa-shield-alt"></i>
          Invalid admin code. Only authorized teachers can access.
        </div>
      <?php endif; ?>

      <form method="POST" action="teacher_auth.php" class="auth-form">
        <div class="form-group">
          <label for="t-admin-name">Admin Name</label>
          <div class="input-wrapper">
            <i class="fas fa-user-tie"></i>
            <input type="text" id="t-admin-name" name="admin_name" required placeholder="Enter admin name" />
          </div>
        </div>
        <div class="form-group">
          <label for="lt-email">Gmail</label>
          <div class="input-wrapper">
            <i class="fas fa-envelope"></i>
            <input type="email" id="lt-email" name="username" required placeholder="Enter your Gmail" />
          </div>
        </div>
        <div class="form-group">
          <label for="lt-pass">Password</label>
          <div class="input-wrapper">
            <i class="fas fa-lock"></i>
            <input type="password" id="lt-pass" name="password" required placeholder="Enter your password" />
          </div>
        </div>
        <div class="form-group">
          <label for="lt-admin-code">Admin Code</label>
          <div class="input-wrapper">
            <i class="fas fa-key"></i>
            <input type="password" id="lt-admin-code" name="admin_password" placeholder="Enter admin code" required />
          </div>
        </div>
        <button type="submit" class="btn btn-primary full-width">
          <i class="fas fa-sign-in-alt"></i>
          Login
        </button>
        <div class="auth-hint">
          <i class="fas fa-info-circle"></i>
          Only authorized admins can login.
        </div>
      </form>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>

