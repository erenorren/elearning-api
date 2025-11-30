-- db_fixed.sql
CREATE DATABASE IF NOT EXISTS elearning_db;
USE elearning_db;

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    max_students INT NOT NULL DEFAULT 0,
    current_enrolled INT NOT NULL DEFAULT 0,
    status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_title (title),
    INDEX idx_category (category),
    INDEX idx_course_code (course_code),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_number VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    enroll_limit INT DEFAULT 5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_student_number (student_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE instructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    instructor_code VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    expertise VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_instructor_code (instructor_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    student_id INT NOT NULL,
    enrolled_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    grade DECIMAL(5,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE RESTRICT,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE RESTRICT,
    CONSTRAINT uc_enrollment_student_course UNIQUE (course_id, student_id),
    INDEX idx_course_id (course_id),
    INDEX idx_student_id (student_id),
    INDEX idx_status (status),
    INDEX idx_enrolled_at (enrolled_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO instructors (instructor_code, email, password, name, phone, expertise) VALUES
('INS2024001', 'alice.instructor@elearning.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice Instructor', '081200000001', 'Programming'),
('INS2024002', 'bob.instructor@elearning.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bob Instructor',   '081200000002', 'Software Engineering');

INSERT INTO courses (course_code, title, description, category, max_students, current_enrolled, status) VALUES
('CSE101', 'Dasar Pemrograman', 'Pengenalan dasar logika dan sintaks pemrograman.', 'Programming', 30, 0, 'published'),
('CSE201', 'OOP dengan PHP', 'Penerapan konsep OOP di PHP untuk membangun REST API.', 'Programming', 25, 0, 'published'),
('CSE301', 'Design Patterns', 'Pengenalan design patterns umum dalam pengembangan software.', 'Programming', 20, 0, 'draft'),
('CSE401', 'Software Architecture', 'Membahas arsitektur perangkat lunak modern.', 'Software Engineering', 20, 0, 'published');

INSERT INTO students (student_number, email, password, name, phone, enroll_limit) VALUES
('STU20250001', 'student1@elearning.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Udin',  '081210000001', 5),
('STU20250002', 'student2@elearning.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nayla', '081210000002', 5),
('STU20250003', 'student3@elearning.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Tika',  '081210000003', 3);

INSERT INTO enrollments (course_id, student_id, enrolled_at, status) VALUES
(1, 1, NOW(), 'active'),
(1, 2, NOW(), 'active'),
(2, 1, NOW(), 'active'),
(2, 3, DATE_SUB(NOW(), INTERVAL 60 DAY), 'active');

-- TRIGGERS: use DELIMITER directives (works in MySQL CLI)
DELIMITER //

CREATE TRIGGER trg_after_enrollment_insert
AFTER INSERT ON enrollments
FOR EACH ROW
BEGIN
    IF NEW.status = 'active' THEN
        UPDATE courses
        SET current_enrolled = current_enrolled + 1
        WHERE id = NEW.course_id;
    END IF;
END;
//

CREATE TRIGGER trg_after_enrollment_update
AFTER UPDATE ON enrollments
FOR EACH ROW
BEGIN
    IF OLD.status = 'active'
       AND NEW.status IN ('completed','cancelled') THEN
        UPDATE courses
        SET current_enrolled = current_enrolled - 1
        WHERE id = NEW.course_id;
    END IF;
END;
//

DELIMITER ;