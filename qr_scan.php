<?php require_once __DIR__ . '/db.php'; require_login(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Scan QR - QR Attendance</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="auth-page student qr-scan-page">
  <div class="auth-container">
    <a href="login.php?home=1" class="back-btn">⟵ Back to Home</a>
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-icon"><span>📷</span></div>
        <h1>Scan QR Code</h1>
        <p>Align the QR code within the frame to open the attendance form instantly</p>
      </div>

      <div id="reader" style="width:100%; max-width: 360px; margin: 0 auto;"></div>
      <div class="qr-actions">
        <a href="scan.php" class="button">Open Form Manually</a>
        <a href="login.php?home=1" class="button" style="background:#94a3b8; color:#0b1220;">Back to Home</a>
      </div>
      <div class="auth-hint" style="text-align:center; margin-top:12px;">Tip: Grant camera permission when prompted. Works best with good lighting.</div>
    </div>
  </div>

  <script>
    function onScanSuccess(decodedText) {
      if (decodedText) {
        window.location.href = decodedText;
      }
    }
    function onScanFailure(error) {
      // no-op, keeps scanning
    }
    const html5QrcodeScanner = new Html5QrcodeScanner('reader', { fps: 10, qrbox: 250 });
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);
  </script>
</body>
</html>


