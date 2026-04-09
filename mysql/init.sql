CREATE DATABASE IF NOT EXISTS attendance_db;
USE attendance_db;

-- Users table (lecturers and students)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('lecturer', 'student') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes table
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_code VARCHAR(20) NOT NULL UNIQUE,
    class_name VARCHAR(100) NOT NULL,
    lecturer_id INT,
    FOREIGN KEY (lecturer_id) REFERENCES users(id)
);

-- Students in classes
CREATE TABLE class_students (
    class_id INT,
    student_id INT,
    PRIMARY KEY (class_id, student_id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (student_id) REFERENCES users(id)
);

-- Attendance records
CREATE TABLE attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    student_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') DEFAULT 'absent',
    marked_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (marked_by) REFERENCES users(id)
);

-- Seed: Lecturers (password is MD5 hash - intentionally weak)
INSERT INTO users (username, password, role, full_name) VALUES
('dr.smith', MD5('password123'), 'lecturer', 'Dr. John Smith'),
('prof.tan', MD5('letmein'), 'lecturer', 'Prof. Alice Tan');

-- Seed: Students
INSERT INTO users (username, password, role, full_name) VALUES
('s001', MD5('student1'), 'student', 'Benjamin Lim'),
('s002', MD5('student2'), 'student', 'Chloe Ng'),
('s003', MD5('student3'), 'student', 'Daniel Koh'),
('s004', MD5('student4'), 'student', 'Emma Tay'),
('s005', MD5('student5'), 'student', 'Farid Hassan'),
('s006', MD5('student6'), 'student', 'Grace Lee');

-- Seed: Classes
INSERT INTO classes (class_code, class_name, lecturer_id) VALUES
('CS101', 'Introduction to Programming', 1),
('CS202', 'Data Structures & Algorithms', 1),
('NET301', 'Network Security', 2);

-- Seed: Enroll students in classes
INSERT INTO class_students (class_id, student_id) VALUES
(1, 3), (1, 4), (1, 5),
(2, 4), (2, 5), (2, 6),
(3, 3), (3, 6), (3, 7), (3, 8);
