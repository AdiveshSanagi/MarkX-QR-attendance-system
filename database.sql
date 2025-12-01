

CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `usn` VARCHAR(50) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `class` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  `password_hash` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `attendance` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  `date` DATE NOT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'Present',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_attendance_student`
    FOREIGN KEY (`student_id`) REFERENCES `students`(`id`) ON DELETE CASCADE,
  INDEX `idx_attendance_subject_date` (`subject`, `date`),
  UNIQUE KEY `uniq_student_subject_date` (`student_id`, `subject`, `date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Timetable subjects by day/period
CREATE TABLE IF NOT EXISTS `timetable` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `day_name` VARCHAR(20) NOT NULL,
  `period` INT NOT NULL,
  `subject` VARCHAR(100) NOT NULL,
  UNIQUE KEY `uniq_day_period` (`day_name`, `period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;