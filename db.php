<?php
// Database connection and bootstrap (creates DB and tables if missing)

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';
$dbName = getenv('DB_NAME') ?: 'attendance_db';

// Connect to MySQL server (without specifying DB first) to allow DB creation
$serverConn = new mysqli($dbHost, $dbUser, $dbPass);
if ($serverConn->connect_error) {
  http_response_code(500);
  die('Database connection failed: ' . $serverConn->connect_error);
}

// Create database if it doesn't exist
if (!$serverConn->query("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
  http_response_code(500);
  die('Failed to ensure database: ' . $serverConn->error);
}

// Connect to the specific database
$conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if ($conn->connect_error) {
  http_response_code(500);
  die('Database selection failed: ' . $conn->connect_error);
}

// Ensure required tables exist
$createStudents = <<<SQL
CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usn VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  class VARCHAR(50) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  password_hash VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

$createAttendance = <<<SQL
CREATE TABLE IF NOT EXISTS attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  subject VARCHAR(100) NOT NULL,
  date DATE NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'Present',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_attendance_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
  INDEX idx_attendance_subject_date (subject, date),
  UNIQUE KEY uniq_student_subject_date (student_id, subject, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if (!$conn->query($createStudents)) {
  http_response_code(500);
  die('Failed to ensure students table: ' . $conn->error);
}

// Backfill migration: ensure students.password_hash exists for logins
$colCheck = $conn->query("SHOW COLUMNS FROM students LIKE 'password_hash'");
if ($colCheck && $colCheck->num_rows === 0) {
  if (!$conn->query("ALTER TABLE students ADD COLUMN password_hash VARCHAR(255) NULL AFTER phone")) {
    http_response_code(500);
    die('Failed to migrate students table (password_hash): ' . $conn->error);
  }
}

if (!$conn->query($createAttendance)) {
  http_response_code(500);
  die('Failed to ensure attendance table: ' . $conn->error);
}

// Rotating QR sessions table
$createQrSessions = <<<SQL
CREATE TABLE IF NOT EXISTS qr_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  subject VARCHAR(100) NOT NULL,
  period VARCHAR(20) NOT NULL,
  day_name VARCHAR(20) NULL,
  start_time DATETIME NOT NULL,
  expires_at DATETIME NOT NULL,
  secret VARBINARY(64) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_qr_sessions_subject_period_time (subject, period, start_time),
  INDEX idx_qr_sessions_expires_at (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if (!$conn->query($createQrSessions)) {
  http_response_code(500);
  die('Failed to ensure qr_sessions table: ' . $conn->error);
}

// Auth: users table (teacher accounts)
$createUsers = <<<SQL
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(50) NOT NULL DEFAULT 'teacher',
  name VARCHAR(100) NULL,
  phone VARCHAR(20) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_users_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if (!$conn->query($createUsers)) {
  http_response_code(500);
  die('Failed to ensure users table: ' . $conn->error);
}

// Backfill migration: add name, phone, and unique index if missing
$colName = $conn->query("SHOW COLUMNS FROM users LIKE 'name'");
if ($colName && $colName->num_rows === 0) {
  $conn->query("ALTER TABLE users ADD COLUMN name VARCHAR(100) NULL AFTER role");
}
$colPhone = $conn->query("SHOW COLUMNS FROM users LIKE 'phone'");
if ($colPhone && $colPhone->num_rows === 0) {
  $conn->query("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL AFTER name");
}
// Ensure unique index on phone
$idxPhone = $conn->query("SHOW INDEX FROM users WHERE Key_name = 'uniq_users_phone'");
if ($idxPhone && $idxPhone->num_rows === 0) {
  $conn->query("ALTER TABLE users ADD UNIQUE INDEX uniq_users_phone (phone)");
}
// Personal timetable table per teacher
$createPersonalTT = <<<SQL
CREATE TABLE IF NOT EXISTS teacher_personal_timetable (
  id INT AUTO_INCREMENT PRIMARY KEY,
  teacher_id INT NOT NULL,
  day_name VARCHAR(20) NOT NULL,
  slot_index TINYINT NOT NULL, -- 0..4 for the 5 time slots
  value VARCHAR(255) NOT NULL DEFAULT '',
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_teacher_day_slot (teacher_id, day_name, slot_index),
  INDEX idx_teacher (teacher_id),
  CONSTRAINT fk_tt_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if (!$conn->query($createPersonalTT)) {
  http_response_code(500);
  die('Failed to ensure teacher_personal_timetable table: ' . $conn->error);
}
// Add last_login column if missing
$colLastLogin = $conn->query("SHOW COLUMNS FROM users LIKE 'last_login'");
if ($colLastLogin && $colLastLogin->num_rows === 0) {
  $conn->query("ALTER TABLE users ADD COLUMN last_login DATETIME NULL AFTER phone");
}

// Seed default admin if none exists
$res = $conn->query("SELECT COUNT(*) AS c FROM users");
if ($res && ($row = $res->fetch_assoc()) && (int)$row['c'] === 0) {
  $defaultUsername = 'admin';
  $defaultPasswordHash = password_hash('admin123', PASSWORD_DEFAULT);
  $defaultRole = 'teacher';
  $stmt = $conn->prepare('INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)');
  $stmt->bind_param('sss', $defaultUsername, $defaultPasswordHash, $defaultRole);
  $stmt->execute();
  $stmt->close();
}

// Sessions
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function current_user() {
  return $_SESSION['user'] ?? null;
}

function require_login() {
  if (!current_user()) {
    header('Location: login.php');
    exit;
  }
}

// Helper: sanitize output
function h($value) {
  return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

?>

