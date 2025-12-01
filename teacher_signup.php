<?php require_once __DIR__ . '/db.php'; if (current_user()) { header('Location: index.php'); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Teacher Sign Up - QR Attendance</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="auth-page teacher">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-icon">
          <i class="fas fa-user-plus"></i>
        </div>
        <h1>Teacher Registration</h1>
        <p>Create your teacher account to manage attendance</p>
      </div>
      
      <a href="login.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        Back to Main
      </a>
      
      <?php if (isset($_GET['registered'])): ?>
        <div class="notification success">
          <i class="fas fa-check-circle"></i>
          Account created successfully! Redirecting to login...
        </div>
        <script>
          setTimeout(() => {
            window.location.href = 'teacher_login.php?registered=teacher';
          }, 2000);
        </script>
      <?php endif; ?>
      <?php if (isset($_GET['error'])): ?>
        <div class="notification error">
          <i class="fas fa-exclamation-circle"></i>
          There was a problem. Please check your inputs.
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['admin_error'])): ?>
        <div class="notification error">
          <i class="fas fa-shield-alt"></i>
          Invalid admin code. Only authorized teachers can access.
        </div>
      <?php endif; ?>

      <form method="POST" action="teacher_register.php" class="auth-form">
        <div class="form-group">
          <label for="t-admin-name">Admin Name</label>
          <div class="input-wrapper">
            <i class="fas fa-user-tie"></i>
            <input type="text" id="t-admin-name" name="admin_name" required placeholder="Enter admin name" />
          </div>
        </div>
        <div class="form-group">
          <label for="t-email">Gmail</label>
          <div class="input-wrapper">
            <i class="fas fa-envelope"></i>
            <input type="email" id="t-email" name="email" required placeholder="Enter your Gmail" />
          </div>
        </div>
        <div class="form-group">
          <label for="t-pass">Password</label>
          <div class="input-wrapper">
            <i class="fas fa-lock"></i>
            <input type="password" id="t-pass" name="password" required placeholder="Create a password" />
          </div>
        </div>
        <div class="form-group">
          <label for="t-cpass">Confirm Password</label>
          <div class="input-wrapper">
            <i class="fas fa-lock"></i>
            <input type="password" id="t-cpass" name="confirm" required placeholder="Confirm your password" />
          </div>
        </div>
        <div class="form-group">
          <label for="t-admin-code">Admin Code</label>
          <div class="input-wrapper">
            <i class="fas fa-key"></i>
            <input type="password" id="t-admin-code" name="admin_code" placeholder="Enter admin code" required />
          </div>
        </div>
        <button type="submit" class="btn btn-primary full-width">
          <i class="fas fa-user-plus"></i>
          Create Account
        </button>
      </form>
    </div>
  </div>

  <script src="script.js"></script>
</body>
</html>

