document.addEventListener('DOMContentLoaded', () => {
  const timetable = document.getElementById('timetable');
  if (timetable) {
    timetable.addEventListener('click', (e) => {
      // disable navigation when editing timetable
      if (document.body && document.body.getAttribute('data-tt-edit') === '1') {
        return;
      }
      const cell = e.target.closest('td[data-subject]');
      if (!cell) return;
      const subject = cell.getAttribute('data-subject');
      const period = cell.getAttribute('data-period');
      const day = cell.getAttribute('data-day');
      const url = `generate_qr.php?subject=${encodeURIComponent(subject)}&period=${encodeURIComponent(period)}&day=${encodeURIComponent(day)}`;
      window.location.href = url;
    });
  }

  // Password visibility toggles
  document.querySelectorAll('input[type="password"]').forEach((input) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'input-group';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'toggle-eye';
    btn.setAttribute('aria-label', 'Toggle password visibility');
    btn.textContent = '👁';
    wrapper.appendChild(btn);
    btn.addEventListener('click', () => {
      input.type = input.type === 'password' ? 'text' : 'password';
    });
  });

  // Simple tabs for student login
  const tabs = document.querySelectorAll('.tab');
  const panels = {
    form: document.getElementById('tab-form'),
    qr: document.getElementById('tab-qr')
  };
  tabs.forEach((tab) => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      Object.values(panels).forEach(p => p && p.classList.remove('active'));
      const key = tab.getAttribute('data-tab');
      const panel = panels[key];
      if (panel) panel.classList.add('active');
    });
  });
});

