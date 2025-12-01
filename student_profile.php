<?php
require_once __DIR__ . '/db.php';
require_login();
$u=current_user();
if (($u['role'] ?? '') !== 'student') { header('Location: index.php'); exit; }
// Fetch student's class (optional)
$studentClass = '';
if (!empty($u['username'])) {
  $stmt = $conn->prepare('SELECT class FROM students WHERE usn = ? LIMIT 1');
  $stmt->bind_param('s', $u['username']);
  if ($stmt->execute()) {
    $stmt->bind_result($cls);
    if ($stmt->fetch()) { $studentClass = $cls; }
  }
  $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Profile - QR Attendance</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
</head>
<body class="landing-page student-profile-page">
  <header class="sp-header">
    <div class="sp-user">
      <div class="sp-avatar" aria-hidden="true"><?php echo strtoupper(substr(h($u['name'] ?? $u['username']),0,1)); ?></div>
      <div class="sp-meta">
        <h1><?php echo h($u['name'] ?? $u['username']); ?></h1>
        <div class="sp-usn">USN: <?php echo h($u['username']); ?><?php if ($studentClass!==''): ?> • Class: <?php echo h($studentClass); ?><?php endif; ?></div>
        <div class="sp-date">Today: <?php echo h(date('Y-m-d')); ?></div>
      </div>
    </div>
    <div class="sp-actions">
      <a class="btn btn-outline" href="login.php?home=1"><i class="fas fa-home"></i> Home</a>
      <a class="btn btn-primary" href="logout.php"><i class="fas fa-right-from-bracket"></i> Logout</a>
    </div>
  </header>
  <div class="sp-title">Student Dashboard</div>

  <main class="qr-layout" style="align-items:start;">
    <section class="qr-panel">
      <h2>Scan QR to Mark Attendance</h2>
      <div id="reader" class="qr-reader"></div>
      <div class="auth-hint" style="margin-top:12px;">Align the QR code within the frame. Grant camera permission when prompted.</div>
      <div class="qr-actions">
        <a href="scan.php" class="btn btn-primary"><i class="fas fa-edit"></i> Open Form Manually</a>
      </div>
      <div id="scan-toast" class="toast" role="status" aria-live="polite" style="display:none;">Opening attendance form…</div>
    </section>

    <section class="attendance-panel">
      <h2>Your Attendance</h2>
      <div class="sp-filters">
        <div class="f-row">
          <label>Start</label>
          <input type="date" id="f-start" />
        </div>
        <div class="f-row">
          <label>End</label>
          <input type="date" id="f-end" />
        </div>
        <div class="f-row">
          <label>Subject</label>
          <select id="f-subject">
            <option value="">All</option>
            <option>CN</option>
            <option>TOC</option>
            <option>RMIPR</option>
            <option>FM</option>
            <option>ESWM</option>
            <option>Mini Project</option>
            <option>NSS/YOGA/PE</option>
          </select>
        </div>
        <div class="f-actions">
          <button type="button" class="button" id="f-apply">Apply</button>
          <button type="button" class="button" id="f-clear" style="background:#94a3b8;">Clear</button>
        </div>
      </div>

      <div class="summary-cards">
        <div class="summary-card present"><div class="label">Present</div><div class="value" id="sum-present">0</div></div>
        <div class="summary-card absent"><div class="label">Absent</div><div class="value" id="sum-absent">0</div></div>
        <div class="summary-card total"><div class="label">Total</div><div class="value" id="sum-total">0</div></div>
      </div>

      <div class="tt-legend" aria-label="Legend" style="margin-top:6px;">
        <span class="badge sub-cn">CN</span>
        <span class="badge sub-toc">TOC</span>
        <span class="badge sub-rmipr">RMIPR</span>
        <span class="badge sub-fm">FM</span>
        <span class="badge sub-eswm">ESWM</span>
        <span class="badge sub-mini">Mini Project</span>
        <span class="badge sub-nss">NSS/YOGA/PE</span>
      </div>
      <div id="my-attendance-status" class="muted">Loading...</div>
      <ul id="my-attendance-list" class="list att-list"></ul>
    </section>
  </main>

  <script>
    // QR Scanner
    function onScanSuccess(decodedText) {
      if (decodedText) {
        const t = document.getElementById('scan-toast');
        if (t) { t.style.display='block'; t.classList.add('show'); setTimeout(()=>{ t.classList.remove('show'); }, 1500); }
        window.location.href = decodedText;
      }
    }
    function onScanFailure(error) { /* keep scanning */ }
    const html5QrcodeScanner = new Html5QrcodeScanner('reader', { fps: 10, qrbox: 250 });
    html5QrcodeScanner.render(onScanSuccess, onScanFailure);

    // My attendance fetch
    const listEl = document.getElementById('my-attendance-list');
    const statusEl = document.getElementById('my-attendance-status');
    const sumPresent = document.getElementById('sum-present');
    const sumAbsent = document.getElementById('sum-absent');
    const sumTotal = document.getElementById('sum-total');
    const fStart = document.getElementById('f-start');
    const fEnd = document.getElementById('f-end');
    const fSubject = document.getElementById('f-subject');
    const fApply = document.getElementById('f-apply');
    const fClear = document.getElementById('f-clear');

    function statusBadge(status){
      const s = (status || '').toLowerCase();
      if (s === 'present') return '<span class="badge status-present">Present</span>';
      if (s === 'absent') return '<span class="badge status-absent">Absent</span>';
      return `<span class="badge">${status || '—'}</span>`;
    }

    function subClass(name){
      const map = {
        'CN':'sub-cn','TOC':'sub-toc','RMIPR':'sub-rmipr','FM':'sub-fm','ESWM':'sub-eswm','Mini Project':'sub-mini','NSS/YOGA/PE':'sub-nss'
      };
      return map[name] || '';
    }

    function updateSummary(data){
      let p=0,a=0; for (const r of data){ if ((r.status||'').toLowerCase()==='present') p++; else a++; }
      const total = data.length; if (sumPresent) sumPresent.textContent=p; if (sumAbsent) sumAbsent.textContent=a; if (sumTotal) sumTotal.textContent=total;
    }

    async function loadMyAttendance(){
      try {
        const params = new URLSearchParams();
        if (fStart && fStart.value) params.set('start', fStart.value);
        if (fEnd && fEnd.value) params.set('end', fEnd.value);
        if (fSubject && fSubject.value) params.set('subject', fSubject.value);
        const url = 'get_my_attendance.php' + (params.toString()? ('?'+params.toString()) : '');
        const res = await fetch(url);
        if (!res.ok) throw new Error('Network error');
        const data = await res.json();
        listEl.innerHTML = '';
        if (!Array.isArray(data) || data.length === 0) {
          statusEl.textContent = 'No attendance records yet.';
          return;
        }
        statusEl.textContent = `${data.length} record(s)`;
        updateSummary(data);
        for (const row of data) {
          const li = document.createElement('li');
          li.className = 'att-item';
          li.innerHTML = `
            <div class="att-left">
              <span class="subject-pill ${subClass(row.subject)}">${row.subject}</span>
            </div>
            <div class="att-right">
              <span class="att-date">${row.date}</span>
              ${statusBadge(row.status)}
            </div>`;
          listEl.appendChild(li);
        }
      } catch (e) {
        statusEl.textContent = 'Failed to load attendance.';
        console.error(e);
      }
    }

    loadMyAttendance();
    setInterval(loadMyAttendance, 7000);

    if (fApply) fApply.addEventListener('click', loadMyAttendance);
    if (fClear) fClear.addEventListener('click', ()=>{ if(fStart)fStart.value=''; if(fEnd)fEnd.value=''; if(fSubject)fSubject.value=''; loadMyAttendance(); });
  </script>
</body>
</html>
