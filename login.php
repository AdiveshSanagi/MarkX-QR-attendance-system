<?php
require_once __DIR__ . '/db.php';
// If a user is logged in, we normally redirect to the timetable.
// But when explicitly visiting with ?home=1, always show the landing page.
if (current_user() && !isset($_GET['home'])) {
  header('Location: index.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Attendance System</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="landing-page">
  <div class="hero-section">
    <div class="hero-content">
      <div class="logo-container">
        <div class="logo-icon">
          <i class="fas fa-qrcode"></i>
        </div>
        <h1 class="hero-title">QR Attendance System</h1>
        <p class="hero-subtitle">Smart, Fast & Reliable Student Attendance Management</p>
      </div>
      
      <?php if (isset($_GET['registered'])): ?>
        <div class="notification success">
          <i class="fas fa-check-circle"></i>
          Account created successfully. Please log in.
        </div>
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
          Invalid admin password. Only authorized teachers can access.
        </div>
      <?php endif; ?>

      <div class="portal-grid">
        <div class="portal-card student">
          <div class="card-icon">
            <i class="fas fa-graduation-cap"></i>
          </div>
          <h3>Student Portal</h3>
          <p>Access your attendance records and mark attendance</p>
          <div class="card-actions">
            <a href="student_signup.php" class="btn btn-primary">
              <i class="fas fa-user-plus"></i>
              Sign Up
            </a>
            <a href="student_login.php" class="btn btn-outline">
              <i class="fas fa-sign-in-alt"></i>
              Login
            </a>
          </div>
        </div>

        <div class="portal-card teacher">
          <div class="card-icon">
            <i class="fas fa-chalkboard-teacher"></i>
          </div>
          <h3>Teacher Portal</h3>
          <p>Manage classes, generate QR codes, and track attendance</p>
          <div class="card-actions">
            <a href="teacher_signup.php" class="btn btn-primary">
              <i class="fas fa-user-plus"></i>
              Sign Up
            </a>
            <a href="teacher_login.php" class="btn btn-outline">
              <i class="fas fa-sign-in-alt"></i>
              Login
            </a>
          </div>
        </div>

        <div class="portal-card curriculum">
          <div class="card-icon">
            <i class="fas fa-book-open"></i>
          </div>
          <h3>Curriculum Activity</h3>
          <p>Training and placement resources with interactive content</p>
          <div class="card-actions">
            <a href="curriculum_portal.php" class="btn btn-primary full-width">
              <i class="fas fa-play"></i>
              Access Portal
            </a>
          </div>
        </div>
      </div>

      <div class="features">
        <div class="feature">
          <i class="fas fa-mobile-alt"></i>
          <span>Mobile Friendly</span>
        </div>
        <div class="feature">
          <i class="fas fa-shield-alt"></i>
          <span>Secure</span>
        </div>
        <div class="feature">
          <i class="fas fa-clock"></i>
          <span>Real-time</span>
        </div>
        <div class="feature">
          <i class="fas fa-chart-line"></i>
          <span>Analytics</span>
        </div>
      </div>
    </div>
  </div>

  <div class="floating-shapes">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
  </div>
</body>
</html>

