<?php require_once __DIR__ . '/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Curriculum Activity</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="auth-page curriculum">
  <div class="auth-container">
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-icon">
          <i class="fas fa-book-open"></i>
        </div>
        <h1>Curriculum Activity</h1>
        <p>Training and placement resources with interactive content</p>
      </div>
      
      <a href="login.php" class="back-btn">
        <i class="fas fa-arrow-left"></i>
        Back to Main
      </a>

      <div class="tabs">
        <button class="tab active" type="button" data-tab="c-login">
          <i class="fas fa-sign-in-alt"></i>
          Login
        </button>
        <button class="tab" type="button" data-tab="c-signup">
          <i class="fas fa-user-plus"></i>
          Sign Up
        </button>
      </div>

      <div class="tab-panel active" id="tab-c-login">
        <form method="POST" action="curriculum_auth.php" class="auth-form">
          <input type="hidden" name="mode" value="login" />
          <div class="form-group">
            <label for="c-name-login">Name</label>
            <div class="input-wrapper">
              <i class="fas fa-user"></i>
              <input type="text" id="c-name-login" name="name" required placeholder="Enter your name" />
            </div>
          </div>
          <div class="form-group">
            <label for="c-pass-login">Password</label>
            <div class="input-wrapper">
              <i class="fas fa-lock"></i>
              <input type="password" id="c-pass-login" name="password" required placeholder="Enter your password" />
            </div>
          </div>
          <div class="form-group">
            <label for="c-code-login">Admin Code</label>
            <div class="input-wrapper">
              <i class="fas fa-key"></i>
              <input type="password" id="c-code-login" name="admin_code" placeholder="akash" required />
            </div>
          </div>
          <button type="submit" class="btn btn-primary full-width">
            <i class="fas fa-sign-in-alt"></i>
            Login
          </button>
        </form>
      </div>

      <div class="tab-panel" id="tab-c-signup">
        <form method="POST" action="curriculum_auth.php" class="auth-form">
          <input type="hidden" name="mode" value="signup" />
          <div class="form-group">
            <label for="c-name">Name</label>
            <div class="input-wrapper">
              <i class="fas fa-user"></i>
              <input type="text" id="c-name" name="name" required placeholder="Enter your name" />
            </div>
          </div>
          <div class="form-group">
            <label for="c-phone">Phone Number</label>
            <div class="input-wrapper">
              <i class="fas fa-phone"></i>
              <input type="tel" id="c-phone" name="phone" required placeholder="Enter your phone number" />
            </div>
          </div>
          <div class="form-group">
            <label for="c-pass">Password</label>
            <div class="input-wrapper">
              <i class="fas fa-lock"></i>
              <input type="password" id="c-pass" name="password" required placeholder="Create a password" />
            </div>
          </div>
          <div class="form-group">
            <label for="c-cpass">Confirm Password</label>
            <div class="input-wrapper">
              <i class="fas fa-lock"></i>
              <input type="password" id="c-cpass" name="confirm" required placeholder="Confirm your password" />
            </div>
          </div>
          <div class="form-group">
            <label for="c-code">Admin Code</label>
            <div class="input-wrapper">
              <i class="fas fa-key"></i>
              <input type="password" id="c-code" name="admin_code" placeholder="akash" required />
            </div>
          </div>
          <button type="submit" class="btn btn-primary full-width">
            <i class="fas fa-user-plus"></i>
            Sign Up
          </button>
        </form>
      </div>
    </div>
  </div>

  <script src="script.js"></script>
  <script>
    // wire tabs
    (function(){
      const tabs = document.querySelectorAll('.tab');
      const map = { 'c-login': document.getElementById('tab-c-login'), 'c-signup': document.getElementById('tab-c-signup') };
      tabs.forEach(t => t.addEventListener('click', () => {
        tabs.forEach(x => x.classList.remove('active'));
        t.classList.add('active');
        Object.values(map).forEach(p => p.classList.remove('active'));
        const k = t.getAttribute('data-tab');
        (map[k]||map['c-login']).classList.add('active');
      }));
    })();
  </script>
</body>
</html>


