<?php require_once __DIR__ . '/db.php'; require_login(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Generate QR - Attendance</title>
  <link rel="stylesheet" href="style.css" />
  <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
</head>
<body>
  <?php
    $subject = isset($_GET['subject']) ? trim($_GET['subject']) : '';
    $period = isset($_GET['period']) ? trim($_GET['period']) : '';
    $day = isset($_GET['day']) ? trim($_GET['day']) : '';
    $subjectSafe = h($subject);
    $periodSafe = h($period);
    $daySafe = h($day);
    $today = date('Y-m-d');

    // Create a new rotating QR session: valid for 1 minute from now, split into 4 slots of 15 seconds each
    $now = new DateTime('now');
    $startTime = $now->format('Y-m-d H:i:s');
    $expires = (clone $now)->modify('+1 minutes')->format('Y-m-d H:i:s');
    // Epoch timestamps (seconds) to avoid client timezone parsing issues
    $startEpoch = time();
    $expiresEpoch = $startEpoch + 60; // 1 minute
    $serverNowEpoch = time();
    $secret = random_bytes(32); // 256-bit secret per session

    $ins = $conn->prepare('INSERT INTO qr_sessions (subject, period, day_name, start_time, expires_at, secret) VALUES (?, ?, ?, ?, ?, ?)');
    $ins->bind_param('ssssss', $subject, $period, $day, $startTime, $expires, $secret);
    if (!$ins->execute()) {
      http_response_code(500);
      die('Failed to create QR session: ' . h($conn->error));
    }
    $sessionId = $conn->insert_id;
    $ins->close();

    // Precompute 20 signed URLs for each 15-second slot
    $base = sprintf(
      '%s://%s%s/scan.php',
      (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http',
      $_SERVER['HTTP_HOST'],
      rtrim(dirname($_SERVER['PHP_SELF']), '/\\')
    );

    $slotCount = 4; // 1 minute / 15 seconds
    $slotUrls = [];
    for ($slot = 0; $slot < $slotCount; $slot++) {
      // token = HMAC(secret, subject|period|date|slot|sessionId)
      $payload = $subject . '|' . $period . '|' . $today . '|' . $slot . '|' . $sessionId;
      $token = hash_hmac('sha256', $payload, $secret, false);
      $url = $base
        . '?subject=' . rawurlencode($subject)
        . '&period=' . rawurlencode($period)
        . '&day=' . rawurlencode($day)
        . '&date=' . rawurlencode($today)
        . '&sid=' . rawurlencode((string)$sessionId)
        . '&slot=' . rawurlencode((string)$slot)
        . '&rt=1'
        . '&token=' . $token;
      $slotUrls[] = $url;
    }
    $initialUrl = $slotUrls[0] ?? '';
  ?>

  <header>
    <h1>QR for <?php echo $subjectSafe; ?> (Period <?php echo $periodSafe; ?>)</h1>
    <a class="button" href="index.php">Back to Timetable</a>
  </header>
  <div class="hint" style="padding: 0 24px;">This QR rotates every <strong>15 seconds</strong> and is valid for <strong>1 minute</strong> (today: <?php echo h($today); ?>).</div>

  <main class="qr-layout">
    <section class="qr-panel">
      <h2>Scan to Mark Attendance</h2>
      <div id="qr">
        <canvas id="qrCanvas" width="320" height="320" style="display:none;"></canvas>
        <?php if (!empty($initialUrl)): ?>
          <img id="qrFallbackImg" alt="QR Code" width="320" height="320" src="<?php echo 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' . urlencode($initialUrl); ?>" />
        <?php else: ?>
          <div class="notification error">Unable to generate QR at this time.</div>
        <?php endif; ?>
      </div>
      <div id="qr-timer" class="muted" style="text-align:center; margin-top:8px;">Preparing...</div>
      <div id="qr-offline" class="notification error" style="display:none; text-align:center; margin-top:8px;">No internet connection. Connect to show QR.</div>
      <div class="qr-meta">
        <div><strong>Subject:</strong> <?php echo $subjectSafe; ?></div>
        <div><strong>Day:</strong> <?php echo $daySafe; ?></div>
        <div><strong>Period:</strong> <?php echo $periodSafe; ?></div>
        <div><strong>Date:</strong> <?php echo h($today); ?></div>
      </div>
      <div class="qr-actions" style="margin-top:12px; display:grid; gap:8px;">
        <a class="button" id="openLinkBtn" href="<?php echo h($initialUrl); ?>" target="_blank" rel="noopener">Open Current Link</a>
        <input type="text" id="scanUrlInput" value="<?php echo h($initialUrl); ?>" readonly style="width:100%; padding:8px; border-radius:8px; border:1px solid var(--border); background:#0b1220; color:var(--text);"/>
        <button class="button" id="copyBtn" type="button" style="justify-self:start;">Copy Current Link</button>
        <button class="button" id="refreshBtn" type="button" style="justify-self:start; background: var(--primary);">Refresh QR (new 1-minute window)</button>
      </div>
    </section>

    <section class="attendance-panel">
      <h2>Live Attendance (Today)</h2>
      <div id="attendance-status" class="muted">Waiting for scans...</div>
      <ul id="attendance-list" class="list"></ul>
    </section>
  </main>

  <script>
    const slotUrls = <?php echo json_encode($slotUrls); ?>;
    const subject = <?php echo json_encode($subject); ?>;
    const sessionMeta = {
      // Use server epoch ms to avoid timezone parse issues
      startTsMs: <?php echo json_encode($startEpoch * 1000); ?>,
      expiresTsMs: <?php echo json_encode($expiresEpoch * 1000); ?>,
      slotSeconds: 15
    };
    const serverNowAtLoadMs = <?php echo json_encode($serverNowEpoch * 1000); ?>;
    const clientNowAtLoadMs = Date.now();
    // Map client time to server time using monotonic delta since load
    function nowServerMs() {
      return serverNowAtLoadMs + (Date.now() - clientNowAtLoadMs);
    }

    // Render QR code (canvas first). If library missing, fallback to image API
    const canvas = document.getElementById('qrCanvas');
    const timerEl = document.getElementById('qr-timer');
    const offlineEl = document.getElementById('qr-offline');

    function renderValue(value) {
      try {
        if (typeof QRCode !== 'undefined' && QRCode.toCanvas) {
          // Prefer canvas: show it and remove server-side fallback img if present
          try {
            canvas.style.display = '';
            const fb = document.getElementById('qrFallbackImg');
            if (fb && fb.parentNode) fb.parentNode.removeChild(fb);
          } catch (e) {}
          QRCode.toCanvas(canvas, value, { width: 320, margin: 1 }, function (error) {
            if (error) {
              console.error(error);
              QRCode.toDataURL(value, { width: 320, margin: 1 }, function (err, url) {
                if (err) { console.error(err); return; }
                const img = new Image();
                img.width = 320;
                img.height = 320;
                img.src = url;
                const container = document.getElementById('qr');
                container.innerHTML = '';
                container.appendChild(img);
              });
            }
          });
        } else {
          const img = new Image();
          img.width = 320;
          img.height = 320;
          img.alt = 'QR Code';
          img.src = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' + encodeURIComponent(value);
          const container = document.getElementById('qr');
          container.innerHTML = '';
          container.appendChild(img);
        }
      } catch (e) {
        console.error('Render failed:', e);
      }
    }

    // Rotation controller
    const startMs = Number(sessionMeta.startTsMs);
    const endMs = Number(sessionMeta.expiresTsMs);
    const slotMs = sessionMeta.slotSeconds * 1000;

    let expiredShown = false;
    function setActionsEnabled(enabled) {
      const openBtn = document.getElementById('openLinkBtn');
      const copyBtn = document.getElementById('copyBtn');
      if (enabled) {
        if (openBtn) { openBtn.removeAttribute('disabled'); openBtn.classList.remove('disabled'); }
        if (copyBtn) { copyBtn.removeAttribute('disabled'); copyBtn.classList.remove('disabled'); }
      } else {
        if (openBtn) { openBtn.setAttribute('disabled', 'disabled'); openBtn.classList.add('disabled'); }
        if (copyBtn) { copyBtn.setAttribute('disabled', 'disabled'); copyBtn.classList.add('disabled'); }
      }
    }

    function update() {
      const now = nowServerMs();
      if (now >= endMs) {
        timerEl.textContent = 'Time is over';
        if (!expiredShown) {
          expiredShown = true;
          const container = document.getElementById('qr');
          if (container) {
            container.innerHTML = '<div class="notification error" style="text-align:center; padding:12px;">Time is over</div>';
          }
          // Disable buttons
          const openBtn = document.getElementById('openLinkBtn');
          if (openBtn) { openBtn.href = '#'; }
          setActionsEnabled(false);
        }
        return;
      }

      // If offline, show notice but still run timer; rendering may fail until back online
      if (!navigator.onLine) {
        if (offlineEl) offlineEl.style.display = '';
        setActionsEnabled(false);
      } else {
        if (offlineEl) offlineEl.style.display = 'none';
        setActionsEnabled(true);
      }
      const elapsed = Math.max(0, now - startMs);
      const slotIndex = Math.min(slotUrls.length - 1, Math.floor(elapsed / slotMs));
      const remaining = Math.max(0, Math.ceil((endMs - now) / 1000));
      const mins = Math.floor(remaining / 60);
      const secs = remaining % 60;
      const slotRemaining = sessionMeta.slotSeconds - (Math.floor((elapsed / 1000) % sessionMeta.slotSeconds));
      const slotText = `${slotRemaining}s`;
      timerEl.textContent = `Expires in ${mins}m ${String(secs).padStart(2, '0')}s · Next refresh in ${slotText}`;
      const currentUrl = slotUrls[slotIndex];
      // Render current slot QR
      renderValue(currentUrl);
      // Update helper UI
      const input = document.getElementById('scanUrlInput');
      const openBtn = document.getElementById('openLinkBtn');
      if (input) input.value = currentUrl;
      if (openBtn) openBtn.href = currentUrl;
    }

    update();
    // Refresh QR every second for smooth timer and every 15s slot it will re-render with the same call
    const iv = setInterval(() => {
      const now = nowServerMs();
      if (now >= endMs) {
        clearInterval(iv);
        update();
        return;
      }
      update();
    }, 1000);

    // React to connectivity changes
    window.addEventListener('online', () => update());
    window.addEventListener('offline', () => update());

    // Live attendance polling
    const listEl = document.getElementById('attendance-list');
    const statusEl = document.getElementById('attendance-status');

    async function refreshAttendance() {
      try {
        const res = await fetch(`get_attendance.php?subject=${encodeURIComponent(subject)}`);
        if (!res.ok) throw new Error('Network error');
        const data = await res.json();

        listEl.innerHTML = '';
        if (!Array.isArray(data) || data.length === 0) {
          statusEl.textContent = 'No attendance yet.';
          return;
        }
        statusEl.textContent = `${data.length} present`;

        for (const row of data) {
          const li = document.createElement('li');
          li.textContent = `${row.usn} - ${row.name} (${row.class})`;
          listEl.appendChild(li);
        }
      } catch (e) {
        console.error(e);
      }
    }

    refreshAttendance();
    setInterval(refreshAttendance, 5000);

    // Copy link
    const copyBtn = document.getElementById('copyBtn');
    if (copyBtn) {
      copyBtn.addEventListener('click', async () => {
        const input = document.getElementById('scanUrlInput');
        const value = input ? input.value : '';
        try {
          await navigator.clipboard.writeText(value);
          copyBtn.textContent = 'Copied!';
          setTimeout(() => (copyBtn.textContent = 'Copy Current Link'), 1500);
        } catch (e) {
          if (input) {
            input.select();
            document.execCommand('copy');
            copyBtn.textContent = 'Copied!';
            setTimeout(() => (copyBtn.textContent = 'Copy Current Link'), 1500);
          }
        }
      });
    }

    // Refresh QR session (reload page to create new session)
    const refreshBtn = document.getElementById('refreshBtn');
    if (refreshBtn) {
      refreshBtn.addEventListener('click', () => {
        const url = new URL(window.location.href);
        // Cache-bust
        url.searchParams.set('_', Date.now().toString());
        window.location.href = url.toString();
      });
    }

    // Removed page auto-refresh so that after one minute, the QR no longer reappears automatically.
  </script>
</body>
</html>

