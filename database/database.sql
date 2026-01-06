-- =============================================
-- Database: edulearn
-- Description: Database schema for EduLearn LMS
-- Author: Generated for Tugas Besar PWI
-- Date: 2026-01-06
-- =============================================

-- Drop database if exists and create fresh
DROP DATABASE IF EXISTS edulearn;
CREATE DATABASE edulearn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edulearn;

-- =============================================
-- Table: users
-- Description: Stores student and admin accounts
-- =============================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nim VARCHAR(20) UNIQUE,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('mahasiswa', 'admin') DEFAULT 'mahasiswa',
    foto_profile VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: dosen
-- Description: Stores instructor information
-- =============================================
CREATE TABLE dosen (
    dosen_id INT AUTO_INCREMENT PRIMARY KEY,
    nama_dosen VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    no_telepon VARCHAR(20),
    bidang_keahlian VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nama (nama_dosen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: mata_kuliah
-- Description: Course information
-- =============================================
CREATE TABLE mata_kuliah (
    mk_id INT AUTO_INCREMENT PRIMARY KEY,
    kode_mk VARCHAR(20) UNIQUE NOT NULL,
    nama_mk VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    sks INT NOT NULL,
    semester INT NOT NULL,
    dosen_id INT,
    status ENUM('aktif', 'draft', 'arsip') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (dosen_id) REFERENCES dosen(dosen_id) ON DELETE SET NULL,
    INDEX idx_semester (semester),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: enrollments
-- Description: Many-to-many relationship between users and courses
-- =============================================
CREATE TABLE enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    mk_id INT NOT NULL,
    tahun_ajaran VARCHAR(20) NOT NULL,
    progress DECIMAL(5,2) DEFAULT 0.00,
    enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (mk_id) REFERENCES mata_kuliah(mk_id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, mk_id, tahun_ajaran),
    INDEX idx_user (user_id),
    INDEX idx_mk (mk_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: jadwal
-- Description: Weekly schedule for courses
-- =============================================
CREATE TABLE jadwal (
    jadwal_id INT AUTO_INCREMENT PRIMARY KEY,
    mk_id INT NOT NULL,
    hari ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    jam_mulai TIME NOT NULL,
    jam_selesai TIME NOT NULL,
    ruangan VARCHAR(50),
    tahun_ajaran VARCHAR(20) NOT NULL,
    FOREIGN KEY (mk_id) REFERENCES mata_kuliah(mk_id) ON DELETE CASCADE,
    INDEX idx_hari (hari),
    INDEX idx_mk (mk_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: tugas
-- Description: Assignments for courses
-- =============================================
CREATE TABLE tugas (
    tugas_id INT AUTO_INCREMENT PRIMARY KEY,
    mk_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    deadline DATETIME NOT NULL,
    max_score INT DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mk_id) REFERENCES mata_kuliah(mk_id) ON DELETE CASCADE,
    INDEX idx_deadline (deadline),
    INDEX idx_mk (mk_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: materi
-- Description: Course materials stored as BLOB
-- =============================================
CREATE TABLE materi (
    materi_id INT AUTO_INCREMENT PRIMARY KEY,
    mk_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT,
    tipe_file VARCHAR(50) NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    file LONGBLOB NOT NULL,
    ukuran_file INT NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mk_id) REFERENCES mata_kuliah(mk_id) ON DELETE CASCADE,
    INDEX idx_mk (mk_id),
    INDEX idx_tipe (tipe_file)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Table: submissions
-- Description: Student assignment submissions
-- =============================================
CREATE TABLE submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    tugas_id INT NOT NULL,
    user_id INT NOT NULL,
    file LONGBLOB,
    nama_file VARCHAR(255),
    ukuran_file INT,
    tipe_file VARCHAR(100),
    is_late TINYINT(1) DEFAULT 0,
    komentar TEXT,
    score INT DEFAULT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    graded_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (tugas_id) REFERENCES tugas(tugas_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_submission (tugas_id, user_id),
    INDEX idx_tugas (tugas_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DUMMY DATA
-- =============================================

-- Insert Admin and Students
INSERT INTO users (nama, nim, email, password, role) VALUES
('Admin EduLearn', NULL, 'admin@edulearn.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'), -- password: password
('Bayu Muda Herlambang', '707082400072', 'bayu@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Muhammad Ramdhanu Damardjati', '707082400001', 'ramdhanu@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Mohamad Alka Wngasadibrata', '707082400002', 'alka@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Siti Nurhaliza', '707082400003', 'siti@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Ahmad Fadil', '707082400004', 'ahmad@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa');

-- Insert Dosen (Instructors)
INSERT INTO dosen (nama_dosen, email, no_telepon, bidang_keahlian) VALUES
('Prof. Andi Wijaya', 'andi.wijaya@telkomuniversity.ac.id', '081234567890', 'Web Development'),
('Dr. Rina Sari', 'rina.sari@telkomuniversity.ac.id', '081234567891', 'Database Systems'),
('Dr. Budi Santoso', 'budi.santoso@telkomuniversity.ac.id', '081234567892', 'UI/UX Design'),
('Prof. Maya Kusuma', 'maya.kusuma@telkomuniversity.ac.id', '081234567893', 'Artificial Intelligence'),
('Dr. Indra Gunawan', 'indra.gunawan@telkomuniversity.ac.id', '081234567894', 'Object-Oriented Programming'),
('Dr. Dewi Lestari', 'dewi.lestari@telkomuniversity.ac.id', '081234567895', 'Pancasila Studies');

-- Insert Mata Kuliah (Courses)
INSERT INTO mata_kuliah (kode_mk, nama_mk, deskripsi, sks, semester, dosen_id, status) VALUES
('WEB101', 'Pemrograman Web Interaktif', 'Learn HTML, CSS, JavaScript, and modern web frameworks', 3, 3, 1, 'aktif'),
('DB202', 'Basis Data', 'Database design, SQL, and database management systems', 3, 3, 2, 'aktif'),
('UX301', 'Desain Antarmuka Pengguna', 'User interface and user experience design principles', 3, 3, 3, 'aktif'),
('AI401', 'Kecerdasan Buatan', 'AI fundamentals and machine learning', 3, 4, 4, 'draft'),
('PBO201', 'Pemrograman Berorientasi Objek', 'Object-oriented programming with Java', 4, 3, 5, 'aktif'),
('PANC101', 'Pendidikan Pancasila', 'Pancasila education and Indonesian ideology', 2, 1, 6, 'aktif'),
('DP202', 'Dasar Pemrograman', 'Programming fundamentals with Python', 3, 2, 1, 'aktif');

-- Insert Enrollments
INSERT INTO enrollments (user_id, mk_id, tahun_ajaran, progress) VALUES
-- Bayu enrolled in 5 courses
(2, 1, '2025/2026', 80.00),
(2, 2, '2025/2026', 75.00),
(2, 5, '2025/2026', 70.00),
(2, 6, '2025/2026', 60.00),
(2, 7, '2025/2026', 50.00),
-- Ramdhanu enrolled in 4 courses
(3, 1, '2025/2026', 65.00),
(3, 2, '2025/2026', 70.00),
(3, 3, '2025/2026', 55.00),
(3, 5, '2025/2026', 60.00),
-- Alka enrolled in 4 courses
(4, 1, '2025/2026', 75.00),
(4, 3, '2025/2026', 80.00),
(4, 5, '2025/2026', 65.00),
(4, 6, '2025/2026', 50.00),
-- Siti enrolled in 3 courses
(5, 2, '2025/2026', 85.00),
(5, 3, '2025/2026', 90.00),
(5, 7, '2025/2026', 75.00),
-- Ahmad enrolled in 3 courses
(6, 1, '2025/2026', 70.00),
(6, 5, '2025/2026', 68.00),
(6, 7, '2025/2026', 72.00);

-- Insert Jadwal (Schedule)
INSERT INTO jadwal (mk_id, hari, jam_mulai, jam_selesai, ruangan, tahun_ajaran) VALUES
-- Semester 3 courses
(1, 'Senin', '08:00:00', '10:30:00', 'Lab Komputer 1', '2025/2026'),
(1, 'Kamis', '13:00:00', '15:30:00', 'Lab Komputer 1', '2025/2026'),
(2, 'Selasa', '08:00:00', '10:30:00', 'Ruang 301', '2025/2026'),
(2, 'Jumat', '10:00:00', '12:30:00', 'Lab Database', '2025/2026'),
(3, 'Rabu', '08:00:00', '10:30:00', 'Studio Design', '2025/2026'),
(5, 'Senin', '13:00:00', '16:30:00', 'Lab Komputer 2', '2025/2026'),
(5, 'Rabu', '13:00:00', '15:30:00', 'Lab Komputer 2', '2025/2026'),
(6, 'Kamis', '08:00:00', '09:40:00', 'Ruang 201', '2025/2026'),
(7, 'Selasa', '13:00:00', '15:30:00', 'Lab Komputer 3', '2025/2026');

-- Insert Tugas (Assignments)
INSERT INTO tugas (mk_id, judul, deskripsi, deadline, max_score) VALUES
(1, 'Website Portfolio Pribadi', 'Buat website portfolio menggunakan HTML, CSS, dan JavaScript', '2026-01-15 23:59:59', 100),
(1, 'Implementasi Form Interaktif', 'Buat form dengan validasi menggunakan JavaScript', '2026-01-20 23:59:59', 100),
(2, 'Database Design E-Commerce', 'Rancang database untuk sistem e-commerce', '2026-01-18 23:59:59', 100),
(2, 'Query Optimization', 'Optimasi query untuk performa database', '2026-01-25 23:59:59', 100),
(3, 'UI Design Mobile App', 'Desain antarmuka aplikasi mobile menggunakan Figma', '2026-01-22 23:59:59', 100),
(5, 'Java OOP Project', 'Implementasi aplikasi Java dengan konsep OOP', '2026-01-17 23:59:59', 100),
(5, 'Design Patterns Implementation', 'Implementasi 3 design patterns dalam Java', '2026-01-30 23:59:59', 100),
(7, 'Python Basic Programs', 'Buat 10 program Python dasar', '2026-01-12 23:59:59', 100);

-- Note: BLOB data for 'materi' table will be inserted via PHP upload interface
-- as inserting binary data directly in SQL is not practical

-- =============================================
-- VERIFICATION QUERIES
-- =============================================

-- Count records in each table
SELECT 'users' as tabel, COUNT(*) as jumlah FROM users
UNION ALL
SELECT 'dosen', COUNT(*) FROM dosen
UNION ALL
SELECT 'mata_kuliah', COUNT(*) FROM mata_kuliah
UNION ALL
SELECT 'enrollments', COUNT(*) FROM enrollments
UNION ALL
SELECT 'jadwal', COUNT(*) FROM jadwal
UNION ALL
SELECT 'tugas', COUNT(*) FROM tugas;

-- Show sample data
SELECT * FROM users LIMIT 5;
SELECT * FROM mata_kuliah LIMIT 5;
