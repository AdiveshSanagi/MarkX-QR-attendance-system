<?php require_once __DIR__ . '/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Curriculum Timetable</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="curriculum-page">
  <header class="curriculum-header">
    <div class="header-content">
      <div class="header-icon">
        <i class="fas fa-book-open"></i>
      </div>
      <div class="header-text">
        <h1>Training and Placement Action Plan</h1>
        <p>Interactive Learning Resources</p>
      </div>
      <a class="btn btn-outline" href="logout.php">
        <i class="fas fa-sign-out-alt"></i>
        Logout
      </a>
    </div>
  </header>

  <main class="curriculum-main">
    <section class="timetable-section">
      <div class="section-header">
        <h2>Action Plan</h2>
        <div class="hint">
          <i class="fas fa-info-circle"></i>
          Click a session to open related learning videos
        </div>
      </div>
      <div class="table-container">
        <table class="timetable" id="timetable">
        <thead>
          <tr>
            <th>Month</th>
            <th>Week</th>
            <th>Semester</th>
            <th>Action Plan</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>September</th>
            <td>First</td>
            <td>V</td>
            <td data-subject="Workshop on Machine Learning" data-day="September" data-period="First">Workshop on Machine Learning</td>
          </tr>
          <tr>
            <th></th>
            <td>Second</td>
            <td>V</td>
            <td data-subject="Workshop on Machine Learning" data-day="September" data-period="Second">Workshop on Machine Learning</td>
          </tr>
          <tr>
            <th></th>
            <td>Third</td>
            <td>V, VII</td>
            <td data-subject="Apptitude Test (MCQs)" data-day="September" data-period="Third">Apptitude Test (MCQs)</td>
          </tr>
          <tr>
            <th></th>
            <td>Four</td>
            <td>III</td>
            <td data-subject="Workshop (Tools on Data Analysis)" data-day="September" data-period="Four">Workshop (Tools on Data Analysis)</td>
          </tr>
          <tr>
            <th>October</th>
            <td>First</td>
            <td>VII</td>
            <td data-subject="Reasoning Test (MCQs)" data-day="October" data-period="First">Reasoning Test (MCQs)</td>
          </tr>
          <tr>
            <th></th>
            <td>Second</td>
            <td>III, V</td>
            <td data-subject="Career Management (Job Application Essentials + Soft Skills)" data-day="October" data-period="Second">Career Management (Job Application Essentials + Soft Skills)</td>
          </tr>
          <tr>
            <th></th>
            <td>Third</td>
            <td>VII</td>
            <td data-subject="Domain Specific Workshop" data-day="October" data-period="Third">Domain Specific Workshop</td>
          </tr>
          <tr>
            <th></th>
            <td>Four</td>
            <td>III, V</td>
            <td data-subject="Resume Building Workshop" data-day="October" data-period="Four">Resume Building Workshop</td>
          </tr>
          <tr>
            <th>November</th>
            <td>First</td>
            <td>III</td>
            <td data-subject="Addressing about Placement Process" data-day="November" data-period="First">Addressing about Placement Process</td>
          </tr>
          <tr>
            <th></th>
            <td>Second</td>
            <td>V</td>
            <td data-subject="Core Subject Refreshers Session" data-day="November" data-period="Second">Core Subject Refreshers Session</td>
          </tr>
          <tr>
            <th></th>
            <td>Third</td>
            <td>VII</td>
            <td data-subject="Interview Preparation Session" data-day="November" data-period="Third">Interview Preparation Session</td>
          </tr>
          <tr>
            <th></th>
            <td>Four</td>
            <td>III, V</td>
            <td data-subject="Core Subject Refreshers Session" data-day="November" data-period="Four">Core Subject Refreshers Session</td>
          </tr>
          <tr>
            <th>December</th>
            <td>First</td>
            <td>III</td>
            <td data-subject="Soft Skill Training" data-day="December" data-period="First">Soft Skill Training</td>
          </tr>
        </tbody>
        </table>
      </div>
    </section>
  </main>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const timetable = document.getElementById('timetable');
      if (!timetable) return;

      timetable.addEventListener('click', (e) => {
        const cell = e.target.closest('td[data-subject]');
        if (!cell) return;
        const subject = cell.getAttribute('data-subject');
        const period = cell.getAttribute('data-period');
        const day = cell.getAttribute('data-day');

        // Map subjects to YouTube videos
        const videoMap = {
          'Workshop on Machine Learning': 'https://youtu.be/VjdD56dN8aU?si=kL6g2o5kSSZh4DBb',
          'Apptitude Test (MCQs)': 'https://youtu.be/z-EtmaFJieY?si=qqPc9EQRaY8FNk1Z',
          'Workshop (Tools on Data Analysis)': 'https://youtu.be/VjdD56dN8aU?si=kL6g2o5kSSZh4DBb',
          'Reasoning Test (MCQs)': 'https://youtu.be/z-EtmaFJieY?si=qqPc9EQRaY8FNk1Z',
          'Career Management (Job Application Essentials + Soft Skills)': 'https://youtu.be/VjdD56dN8aU?si=kL6g2o5kSSZh4DBb',
          'Domain Specific Workshop': 'https://youtu.be/z-EtmaFJieY?si=qqPc9EQRaY8FNk1Z',
          'Resume Building Workshop': 'https://youtu.be/VjdD56dN8aU?si=kL6g2o5kSSZh4DBb',
          'Addressing about Placement Process': 'https://youtu.be/z-EtmaFJieY?si=qqPc9EQRaY8FNk1Z',
          'Core Subject Refreshers Session': 'https://youtu.be/VjdD56dN8aU?si=kL6g2o5kSSZh4DBb',
          'Interview Preparation Session': 'https://youtu.be/z-EtmaFJieY?si=qqPc9EQRaY8FNk1Z',
          'Soft Skill Training': 'https://youtu.be/VjdD56dN8aU?si=kL6g2o5kSSZh4DBb'
        };

        const videoUrl = videoMap[subject];
        if (videoUrl) {
          // Open video in new tab
          window.open(videoUrl, '_blank');
        } else {
          // Fallback for unmapped subjects
          alert('Video not available for: ' + subject);
        }
      });
    });
  </script>
</body>
</html>


