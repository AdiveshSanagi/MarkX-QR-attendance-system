<?php require_once __DIR__ . '/db.php'; if (current_user()) { header('Location: index.php'); exit; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Sign Up - QR Attendance</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="auth-page student">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-icon">
          <i class="fas fa-user-plus"></i>
        </div>
        <h1>Student Registration</h1>
        <p>Create your account to access the attendance system</p>
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
            window.location.href = 'student_login.php?registered=student';
          }, 2000);
        </script>
      <?php endif; ?>
      <?php if (isset($_GET['error'])): ?>
        <div class="notification error">
          <i class="fas fa-exclamation-circle"></i>
          There was a problem. Please check your inputs.
        </div>
      <?php endif; ?>

      <form method="POST" action="register.php" class="auth-form">
        <input type="hidden" name="mode" value="student" />
        <div class="form-group">
          <label for="s-name">Full Name</label>
          <div class="input-wrapper">
            <i class="fas fa-user"></i>
            <input type="text" id="s-name" name="name" required placeholder="Enter your full name" />
          </div>
        </div>
        <div class="form-group">
          <label for="s-usn">USN</label>
          <div class="input-wrapper">
            <i class="fas fa-id-card"></i>
            <input type="text" id="s-usn" name="usn" required placeholder="Enter your USN" />
          </div>
        </div>
        <div class="form-group">
          <label for="s-phone">Phone Number</label>
          <div class="input-wrapper">
            <i class="fas fa-phone"></i>
            <input type="tel" id="s-phone" name="phone" required placeholder="Enter your phone number" />
          </div>
        </div>
        <div class="form-group">
          <label for="s-class">Class/Section</label>
          <div class="input-wrapper">
            <i class="fas fa-graduation-cap"></i>
            <input type="text" id="s-class" name="class" required placeholder="Enter your class/section" />
          </div>
        </div>
        <div class="form-group">
          <label for="s-pass">Set Password</label>
          <div class="input-wrapper">
            <i class="fas fa-lock"></i>
            <input type="password" id="s-pass" name="password" required placeholder="Create a password" />
          </div>
        </div>
        <div class="form-group">
          <label for="s-cpass">Confirm Password</label>
          <div class="input-wrapper">
            <i class="fas fa-lock"></i>
            <input type="password" id="s-cpass" name="confirm" required placeholder="Confirm your password" />
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

