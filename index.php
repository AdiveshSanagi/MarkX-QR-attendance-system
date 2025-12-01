<?php require_once __DIR__ . '/db.php'; require_login(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Attendance - Timetable (Academic Year 2025-26 R1)</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="timetable-page">
  <header>
    <h1>Automatic Student Attendance (QR)</h1>
    <div>
      <span class="muted">Logged in as <?php $u=current_user(); echo h($u['name'] ?? $u['username']); ?></span>
      <a class="button" style="margin-left:10px;" href="logout.php">Logout</a>
    </div>
  </header>

  <main>
    <section class="timetable-section">
      <h2>Timetable - Academic Year 2025-26 (R1)</h2>
      <div class="hint">Click a subject to generate a QR for that period. Teachers can also edit and save timetable.</div>
      <div class="tt-legend" aria-label="Legend">
        <span class="badge">Subject</span>
        <span class="badge badge-break">Short Break</span>
        <span class="badge badge-lunch">Lunch</span>
        <span class="badge badge-lab">Lab</span>
        <span class="badge badge-project">Mini Project</span>
      </div>
      <?php $u=current_user(); if (($u['role'] ?? '') === 'teacher'): ?>
      <div class="tt-toolbar">
        <button id="tt-edit" class="button" type="button">Edit</button>
        <button id="tt-save" class="button" type="button" style="display:none; background:#22c55e;">Save</button>
        <button id="tt-cancel" class="button" type="button" style="display:none; background:#ef4444;">Cancel</button>
      </div>
      <?php endif; ?>
      <div class="tt-card">
      <table class="timetable" id="timetable">
        <thead>
          <tr>
            <th>Day \ Time</th>
            <th>10:00 - 10:55</th>
            <th>10:55 - 11:50</th>
            <th>Short Break<br>11:50 - 12:10</th>
            <th>12:10 - 1:05</th>
            <th>1:05 - 2:00</th>
            <th>Lunch Break<br>2:00 - 3:10</th>
            <th>3:10 - 4:05</th>
            <th>4:05 - 5:00</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th class="day-col">Monday</th>
            <td class="lab" data-subject="CN LAB (A2)/CSL LAB (A1)" data-day="Monday" data-period="1">CN LAB (A2)/CSL LAB (A1)</td>
            <td data-subject="" data-day="Monday" data-period="2"></td>
            <td class="break" aria-hidden="true">Short Break</td>
            <td data-subject="MR&MM" data-day="Monday" data-period="3">MR&amp;MM</td>
            <td data-subject="TOC" data-day="Monday" data-period="4">TOC</td>
            <td class="break" aria-hidden="true">Lunch</td>
            <td class="library break" aria-hidden="true">Library Hour</td>
            <td class="project" data-subject="Mini Project" data-day="Monday" data-period="5">Mini Project</td>
          </tr>
          <tr>
            <th class="day-col">Tuesday</th>
            <td data-subject="RMIPR" data-day="Tuesday" data-period="1">RMIPR</td>
            <td data-subject="MR&MM" data-day="Tuesday" data-period="2">MR&amp;MM</td>
            <td class="break" aria-hidden="true">Short Break</td>
            <td data-subject="TOC" data-day="Tuesday" data-period="3">TOC</td>
            <td data-subject="CN" data-day="Tuesday" data-period="4">CN</td>
            <td class="break" aria-hidden="true">Lunch</td>
            <td class="project" data-subject="Mini Project" data-day="Tuesday" data-period="5">Mini Project</td>
            <td></td>
          </tr>
          <tr>
            <th class="day-col">Wednesday</th>
            <td data-subject="MR&MM" data-day="Wednesday" data-period="1">MR&amp;MM</td>
            <td data-subject="FM" data-day="Wednesday" data-period="2">FM</td>
            <td class="break" aria-hidden="true">Short Break</td>
            <td class="lab" data-subject="CN LAB (A2)/CSL LAB (A1)" data-day="Wednesday" data-period="3">CN LAB (A2)/CSL LAB (A1)</td>
            <td data-subject="" data-day="Wednesday" data-period="4"></td>
            <td class="break" aria-hidden="true">Lunch</td>
            <td class="break" aria-hidden="true">Aparahna</td>
            <td></td>
          </tr>
          <tr>
            <th class="day-col">Thursday</th>
            <td data-subject="FM" data-day="Thursday" data-period="1">FM</td>
            <td data-subject="TOC" data-day="Thursday" data-period="2">TOC</td>
            <td class="break" aria-hidden="true">Short Break</td>
            <td data-subject="CN" data-day="Thursday" data-period="3">CN</td>
            <td data-subject="RMIPR" data-day="Thursday" data-period="4">RMIPR</td>
            <td class="break" aria-hidden="true">Lunch</td>
            <td class="project" data-subject="Mini Project" data-day="Thursday" data-period="5">Mini Project</td>
            <td></td>
          </tr>
          <tr>
            <th class="day-col">Friday</th>
            <td data-subject="CN" data-day="Friday" data-period="1">CN</td>
            <td data-subject="RMIPR" data-day="Friday" data-period="2">RMIPR</td>
            <td class="break" aria-hidden="true">Short Break</td>
            <td data-subject="FM" data-day="Friday" data-period="3">FM</td>
            <td data-subject="TOC" data-day="Friday" data-period="4">TOC</td>
            <td class="break" aria-hidden="true">Lunch</td>
            <td class="project" data-subject="NSS/YOGA/PE" data-day="Friday" data-period="5">NSS/YOGA/PE</td>
            <td></td>
          </tr>
          <tr>
            <th class="day-col">Saturday</th>
            <td data-subject="TOC" data-day="Saturday" data-period="1">TOC</td>
            <td data-subject="FM" data-day="Saturday" data-period="2">FM</td>
            <td class="break" aria-hidden="true">Short Break</td>
            <td data-subject="RMIPR" data-day="Saturday" data-period="3">RMIPR</td>
            <td data-subject="ESWM" data-day="Saturday" data-period="4">ESWM</td>
            <td class="break" aria-hidden="true">Lunch</td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
      </table>
      </div>
    </section>
  </main>

  <script src="script.js"></script>
  <script>
  (function(){
    const editBtn = document.getElementById('tt-edit');
    const saveBtn = document.getElementById('tt-save');
    const cancelBtn = document.getElementById('tt-cancel');
    if (!editBtn) return;
    const cells = Array.from(document.querySelectorAll('#timetable td'))
      .filter(td => !td.classList.contains('break'));
    const originals = new Map();
    function toEdit(){
      editBtn.style.display='none'; saveBtn.style.display='inline-block'; cancelBtn.style.display='inline-block';
      document.body.setAttribute('data-tt-edit','1');
      cells.forEach(td=>{ originals.set(td, td.textContent); const v=td.textContent.trim(); td.innerHTML='<input type="text" value="'+v.replace(/"/g,'&quot;')+'" style="width:100%">'; });
    }
    function cancelEdit(){
      saveBtn.style.display='none'; cancelBtn.style.display='none'; editBtn.style.display='inline-block';
      document.body.removeAttribute('data-tt-edit');
      cells.forEach(td=>{ td.textContent = originals.get(td) || ''; });
      originals.clear();
    }
    async function saveEdit(){
      const rows = cells.map(td=>{
        const input = td.querySelector('input');
        const subject = input ? input.value.trim() : td.textContent.trim();
        return { day: td.getAttribute('data-day'), period: parseInt(td.getAttribute('data-period')), subject };
      });
      try{
        const res = await fetch('timetable_save.php',{method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({rows})});
        if(!res.ok){ throw new Error('Save failed'); }
        document.body.removeAttribute('data-tt-edit');
        location.reload();
      }catch(e){ alert(e.message); }
    }
    editBtn.addEventListener('click', toEdit);
    cancelBtn.addEventListener('click', cancelEdit);
    saveBtn.addEventListener('click', saveEdit);
  })();
  </script>
</body>
</html>

