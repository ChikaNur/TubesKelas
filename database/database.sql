-- =============================================
-- Database: edulearn - COMPREHENSIVE VERSION
-- Description: Complete database with enriched data (20+ records per table)
-- Date: 2026-01-08
-- =============================================

DROP DATABASE IF EXISTS edulearn;
CREATE DATABASE edulearn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edulearn;

-- =============================================
-- TABLES
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

CREATE TABLE dosen (
    dosen_id INT AUTO_INCREMENT PRIMARY KEY,
    nama_dosen VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    no_telepon VARCHAR(20),
    bidang_keahlian VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_nama (nama_dosen)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- UPDATED materi table (merged from migration)
CREATE TABLE materi (
    materi_id INT AUTO_INCREMENT PRIMARY KEY,
    mk_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT DEFAULT NULL,
    tipe_file VARCHAR(100) DEFAULT NULL,
    nama_file VARCHAR(255) NOT NULL,
    ukuran_file INT NOT NULL COMMENT 'File size in bytes',
    file_path VARCHAR(500) NOT NULL,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (mk_id) REFERENCES mata_kuliah(mk_id) ON DELETE CASCADE,
    INDEX idx_mk_id (mk_id),
    INDEX idx_uploaded_at (uploaded_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE submissions (
    submission_id INT AUTO_INCREMENT PRIMARY KEY,
    tugas_id INT NOT NULL,
    user_id INT NOT NULL,
    file_path VARCHAR(500),
    file_name VARCHAR(255),
    file_size INT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    score DECIMAL(5,2),
    feedback TEXT,
    graded_at TIMESTAMP NULL,
    graded_by INT,
    FOREIGN KEY (tugas_id) REFERENCES tugas(tugas_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (graded_by) REFERENCES users(user_id) ON DELETE SET NULL,
    INDEX idx_tugas (tugas_id),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DATA (20+ records per table)
-- =============================================

-- Users (21 records: 1 admin + 20 students)
INSERT INTO users (nama, nim, email, password, role) VALUES
('Admin EduLearn', NULL, 'admin@edulearn.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Bayu Muda Herlambang', '707082400001', 'bayu@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Muhammad Ramdhanu', '707082400002', 'ramdhanu@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Mohamad Alka', '707082400003', 'alka@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Siti Nurhaliza', '707082400004', 'siti@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Ahmad Fadil', '707082400005', 'ahmad@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Dewi Lestari', '707082400006', 'dewi@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Rudi Hartono', '707082400007', 'rudi@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Rina Wulandari', '707082400008', 'rina@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Budi Setiawan', '707082400009', 'budi@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Yuni Kartika', '707082400010', 'yuni@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Andi Pratama', '707082400011', 'andi@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Maya Sari', '707082400012', 'maya@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Indra Gunawan', '707082400013', 'indra@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Lina Marlina', '707082400014', 'lina@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Hendra Kusuma', '707082400015', 'hendra@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Nina Aprilia', '707082400016', 'nina@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Arif Rahman', '707082400017', 'arif@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Putri Ayu', '707082400018', 'putri@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Dika Prabowo', '707082400019', 'dika@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa'),
('Sinta Dewi', '707082400020', 'sinta@student.telkomuniversity.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mahasiswa');

-- Dosen (20 records)
INSERT INTO dosen (nama_dosen, email, no_telepon, bidang_keahlian) VALUES
('Prof. Andi Wijaya', 'andi.wijaya@telkomuniversity.ac.id', '081234567801', 'Web Development'),
('Dr. Rina Sari', 'rina.sari@telkomuniversity.ac.id', '081234567802', 'Database Systems'),
('Dr. Budi Santoso', 'budi.santoso@telkomuniversity.ac.id', '081234567803', 'UI/UX Design'),
('Prof. Maya Kusuma', 'maya.kusuma@telkomuniversity.ac.id', '081234567804', 'Artificial Intelligence'),
('Dr. Indra Gunawan', 'indra.gunawan@telkomuniversity.ac.id', '081234567805', 'Object-Oriented Programming'),
('Dr. Dewi Lestari', 'dewi.lestari@telkomuniversity.ac.id', '081234567806', 'Pancasila Studies'),
('Prof. Hendra Kusuma', 'hendra.kusuma@telkomuniversity.ac.id', '081234567807', 'Data Structures'),
('Dr. Nina Aprilia', 'nina.aprilia@telkomuniversity.ac.id', '081234567808', 'Computer Networks'),
('Dr. Arif Rahman', 'arif.rahman@telkomuniversity.ac.id', '081234567809', 'Operating Systems'),
('Prof. Putri Anggraeni', 'putri.anggraeni@telkomuniversity.ac.id', '081234567810', 'Software Engineering'),
('Dr. Dika Prasetyo', 'dika.prasetyo@telkomuniversity.ac.id', '081234567811', 'Mobile Development'),
('Dr. Sinta Maharani', 'sinta.maharani@telkomuniversity.ac.id', '081234567812', 'Cloud Computing'),
('Prof. Bambang Sutrisno', 'bambang.sutrisno@telkomuniversity.ac.id', '081234567813', 'Information Security'),
('Dr. Citra Lestari', 'citra.lestari@telkomuniversity.ac.id', '081234567814', 'Data Science'),
('Dr. Eko Prasetyo', 'eko.prasetyo@telkomuniversity.ac.id', '081234567815', 'Computer Graphics'),
('Prof. Fitri Handayani', 'fitri.handayani@telkomuniversity.ac.id', '081234567816', 'English'),
('Dr. Galih Pratama', 'galih.pratama@telkomuniversity.ac.id', '081234567817', 'Mathematics'),
('Dr. Hani Nurlaila', 'hani.nurlaila@telkomuniversity.ac.id', '081234567818', 'Statistics'),
('Prof. Irfan Hakim', 'irfan.hakim@telkomuniversity.ac.id', '081234567819', 'Physics'),
('Dr. Jasmine Putri', 'jasmine.putri@telkomuniversity.ac.id', '081234567820', 'Algorithm Design');

-- Mata Kuliah (20 records)
INSERT INTO mata_kuliah (kode_mk, nama_mk, deskripsi, sks, semester, dosen_id, status) VALUES
('WEB101', 'Pemrograman Web Interaktif', 'Learn HTML, CSS, JavaScript, and modern web frameworks', 3, 3, 1, 'aktif'),
('DB202', 'Basis Data', 'Database design, SQL, and database management systems', 3, 3, 2, 'aktif'),
('UX301', 'Desain Antarmuka Pengguna', 'User interface and user experience design principles', 3, 3, 3, 'aktif'),
('AI401', 'Kecerdasan Buatan', 'AI fundamentals and machine learning', 3, 5, 4, 'aktif'),
('PBO201', 'Pemrograman Berorientasi Objek', 'Object-oriented programming with Java', 4, 3, 5, 'aktif'),
('PANC101', 'Pendidikan Pancasila', 'Pancasila education and Indonesian ideology', 2, 1, 6, 'aktif'),
('DP102', 'Dasar Pemrograman', 'Programming fundamentals with Python', 3, 1, 1, 'aktif'),
('DS301', 'Struktur Data', 'Data structures and algorithms', 4, 3, 7, 'aktif'),
('NET302', 'Jaringan Komputer', 'Computer networks and protocols', 3, 4, 8, 'aktif'),
('OS303', 'Sistem Operasi', 'Operating system concepts and design', 3, 4, 9, 'aktif'),
('SE401', 'Rekayasa Perangkat Lunak', 'Software engineering methodologies', 3, 5, 10, 'aktif'),
('MOB402', 'Pemrograman Mobile', 'Mobile app development for Android/iOS', 3, 5, 11, 'aktif'),
('CLOUD501', 'Cloud Computing', 'Cloud platforms and services', 3, 6, 12, 'aktif'),
('SEC502', 'Keamanan Informasi', 'Information security principles', 3, 6, 13, 'aktif'),
('DATSCI503', 'Data Science', 'Data analysis and visualization', 3, 6, 14, 'aktif'),
('CG304', 'Grafika Komputer', 'Computer graphics fundamentals', 3, 4, 15, 'aktif'),
('ENG101', 'Bahasa Inggris', 'English for academic purposes', 2, 1, 16, 'aktif'),
('MTK102', 'Matematika Diskrit', 'Discrete mathematics for computer science', 3, 1, 17, 'aktif'),
('STAT201', 'Statistika', 'Statistics and probability', 3, 2, 18, 'aktif'),
('ALGO401', 'Analisis dan Desain Algoritma', 'Advanced algorithm design', 4, 5, 20, 'aktif');

-- Enrollments (100+ records - each student enrolled in 5 courses)
INSERT INTO enrollments (user_id, mk_id, tahun_ajaran, progress) VALUES
(2,1,'2025/2026',75),(2,2,'2025/2026',80),(2,5,'2025/2026',70),(2,7,'2025/2026',85),(2,8,'2025/2026',60),
(3,1,'2025/2026',65),(3,3,'2025/2026',70),(3,5,'2025/2026',55),(3,7,'2025/2026',75),(3,8,'2025/2026',50),
(4,2,'2025/2026',80),(4,3,'2025/2026',85),(4,5,'2025/2026',65),(4,6,'2025/2026',90),(4,8,'2025/2026',70),
(5,1,'2025/2026',90),(5,2,'2025/2026',85),(5,7,'2025/2026',80),(5,8,'2025/2026',75),(5,9,'2025/2026',70),
(6,3,'2025/2026',60),(6,5,'2025/2026',68),(6,7,'2025/2026',72),(6,8,'2025/2026',55),(6,9,'2025/2026',50),
(7,1,'2025/2026',78),(7,2,'2025/2026',82),(7,5,'2025/2026',75),(7,6,'2025/2026',88),(7,8,'2025/2026',65),
(8,3,'2025/2026',70),(8,7,'2025/2026',76),(8,8,'2025/2026',68),(8,9,'2025/2026',60),(8,10,'2025/2026',55),
(9,1,'2025/2026',85),(9,2,'2025/2026',80),(9,5,'2025/2026',77),(9,7,'2025/2026',90),(9,8,'2025/2026',72),
(10,3,'2025/2026',65),(10,5,'2025/2026',70),(10,7,'2025/2026',68),(10,8,'2025/2026',58),(10,9,'2025/2026',52),
(11,1,'2025/2026',72),(11,2,'2025/2026',78),(11,5,'2025/2026',65),(11,6,'2025/2026',85),(11,8,'2025/2026',60),
(12,3,'2025/2026',88),(12,7,'2025/2026',92),(12,8,'2025/2026',80),(12,9,'2025/2026',75),(12,10,'2025/2026',70),
(13,1,'2025/2026',68),(13,2,'2025/2026',72),(13,5,'2025/2026',60),(13,7,'2025/2026',78),(13,8,'2025/2026',55),
(14,3,'2025/2026',82),(14,5,'2025/2026',80),(14,7,'2025/2026',85),(14,8,'2025/2026',72),(14,9,'2025/2026',68),
(15,1,'2025/2026',76),(15,2,'2025/2026',80),(15,5,'2025/2026',70),(15,6,'2025/2026',82),(15,8,'2025/2026',65),
(16,3,'2025/2026',75),(16,7,'2025/2026',80),(16,8,'2025/2026',70),(16,9,'2025/2026',65),(16,10,'2025/2026',58),
(17,1,'2025/2026',82),(17,2,'2025/2026',85),(17,5,'2025/2026',77),(17,7,'2025/2026',88),(17,8,'2025/2026',72),
(18,3,'2025/2026',70),(18,5,'2025/2026',75),(18,7,'2025/2026',72),(18,8,'2025/2026',62),(18,9,'2025/2026',55),
(19,1,'2025/2026',88),(19,2,'2025/2026',90),(19,5,'2025/2026',82),(19,6,'2025/2026',92),(19,8,'2025/2026',80),
(20,3,'2025/2026',78),(20,7,'2025/2026',82),(20,8,'2025/2026',75),(20,9,'2025/2026',68),(20,10,'2025/2026',62),
(21,1,'2025/2026',80),(21,2,'2025/2026',85),(21,5,'2025/2026',75),(21,7,'2025/2026',82),(21,8,'2025/2026',70);

-- Jadwal (30 records - multiple sessions per course)
INSERT INTO jadwal (mk_id, hari, jam_mulai, jam_selesai, ruangan, tahun_ajaran) VALUES
(1,'Senin','08:00:00','10:30:00','Lab Komputer 1','2025/2026'),
(1,'Kamis','13:00:00','15:30:00','Lab Komputer 1','2025/2026'),
(2,'Selasa','08:00:00','10:30:00','Ruang 301','2025/2026'),
(2,'Jumat','10:00:00','12:30:00','Lab Database','2025/2026'),
(3,'Rabu','08:00:00','10:30:00','Studio Design','2025/2026'),
(3,'Jumat','13:00:00','15:30:00','Studio Design','2025/2026'),
(4,'Senin','10:00:00','12:30:00','Lab AI','2025/2026'),
(4,'Kamis','08:00:00','10:30:00','Lab AI','2025/2026'),
(5,'Senin','13:00:00','16:30:00','Lab Komputer 2','2025/2026'),
(5,'Rabu','13:00:00','15:30:00','Lab Komputer 2','2025/2026'),
(6,'Kamis','08:00:00','09:40:00','Ruang 201','2025/2026'),
(7,'Selasa','13:00:00','15:30:00','Lab Komputer 3','2025/2026'),
(7,'Jumat','08:00:00','10:30:00','Lab Komputer 3','2025/2026'),
(8,'Senin','08:00:00','11:30:00','Lab Komputer 4','2025/2026'),
(8,'Rabu','10:00:00','12:30:00','Lab Komputer 4','2025/2026'),
(9,'Selasa','10:00:00','12:30:00','Lab Jaringan','2025/2026'),
(9,'Kamis','13:00:00','15:30:00','Lab Jaringan','2025/2026'),
(10,'Senin','13:00:00','15:30:00','Ruang 302','2025/2026'),
(10,'Rabu','13:00:00','15:30:00','Ruang 302','2025/2026'),
(11,'Selasa','08:00:00','10:30:00','Ruang 303','2025/2026'),
(12,'Rabu','08:00:00','10:30:00','Lab Mobile','2025/2026'),
(12,'Kamis','10:00:00','12:30:00','Lab Mobile','2025/2026'),
(13,'Senin','10:00:00','12:30:00','Lab Cloud','2025/2026'),
(14,'Selasa','13:00:00','15:30:00','Lab Security','2025/2026'),
(15,'Rabu','10:00:00','12:30:00','Lab Data','2025/2026'),
(15,'Jumat','13:00:00','15:30:00','Lab Data','2025/2026'),
(16,'Kamis','08:00:00','10:30:00','Lab Graphics','2025/2026'),
(17,'Senin','08:00:00','09:40:00','Ruang 101','2025/2026'),
(18,'Selasa','10:00:00','12:30:00','Ruang 202','2025/2026'),
(19,'Rabu','08:00:00','10:30:00','Ruang 203','2025/2026');

-- Tugas (40 records - 2 per course)
INSERT INTO tugas (mk_id, judul, deskripsi, deadline, max_score) VALUES
(1,'Website Portfolio','Buat website portfolio menggunakan HTML, CSS, dan JavaScript','2026-01-15 23:59:59',100),
(1,'Form Interaktif','Buat form dengan validasi JavaScript','2026-01-25 23:59:59',100),
(2,'Database E-Commerce','Rancang database untuk sistem e-commerce','2026-01-18 23:59:59',100),
(2,'Query Optimization','Optimasi query untuk performa database','2026-01-28 23:59:59',100),
(3,'UI Mobile App','Desain antarmuka aplikasi mobile dengan Figma','2026-01-20 23:59:59',100),
(3,'Usability Testing','Lakukan usability testing dan analisis','2026-02-05 23:59:59',100),
(4,'AI Chatbot','Implementasi chatbot sederhana','2026-02-10 23:59:59',100),
(4,'Machine Learning Model','Buat model ML untuk klasifikasi','2026-02-20 23:59:59',100),
(5,'Java OOP Project','Implementasi aplikasi Java dengan OOP','2026-01-17 23:59:59',100),
(5,'Design Patterns','Implementasi 3 design patterns','2026-01-30 23:59:59',100),
(6,'Analisis Pancasila','Analisis implementasi nilai Pancasila','2026-01-22 23:59:59',100),
(6,'Essay Ideologi','Essay tentang ideologi Pancasila','2026-02-08 23:59:59',100),
(7,'Python Basic','Buat 10 program Python dasar','2026-01-12 23:59:59',100),
(7,'Python Project','Project aplikasi Python sederhana','2026-01-26 23:59:59',100),
(8,'Implementasi Stack Queue','Implementasi struktur data stack dan queue','2026-01-24 23:59:59',100),
(8,'Binary Tree','Implementasi binary tree dan traversal','2026-02-12 23:59:59',100),
(9,'Network Configuration','Konfigurasi jaringan komputer','2026-02-15 23:59:59',100),
(9,'Packet Analysis','Analisis packet menggunakan Wireshark','2026-02-25 23:59:59',100),
(10,'Process Scheduling','Simulasi algoritma scheduling','2026-02-10 23:59:59',100),
(10,'Memory Management','Implementasi algoritma memory management','2026-02-20 23:59:59',100),
(11,'Software Design','Rancang software menggunakan UML','2026-02-18 23:59:59',100),
(11,'Testing Plan','Buat test plan dan test case','2026-02-28 23:59:59',100),
(12,'Android App','Buat aplikasi Android sederhana','2026-02-22 23:59:59',100),
(12,'iOS App','Buat aplikasi iOS sederhana','2026-03-05 23:59:59',100),
(13,'Deploy to AWS','Deploy aplikasi ke AWS','2026-03-10 23:59:59',100),
(13,'Serverless Function','Buat serverless function','2026-03-20 23:59:59',100),
(14,'Security Audit','Lakukan security audit aplikasi','2026-03-15 23:59:59',100),
(14,'Penetration Testing','Lakukan penetration testing','2026-03-25 23:59:59',100),
(15,'Data Analysis','Analisis dataset menggunakan Python','2026-03-12 23:59:59',100),
(15,'Visualization','Buat visualisasi data interaktif','2026-03-22 23:59:59',100),
(16,'3D Rendering','Buat scene 3D sederhana','2026-02-28 23:59:59',100),
(16,'Animation','Buat animasi komputer','2026-03-10 23:59:59',100),
(17,'English Presentation','Presentasi dalam Bahasa Inggris','2026-01-30 23:59:59',100),
(17,'Writing Exercise','Menulis essay akademik','2026-02-15 23:59:59',100),
(18,'Logic Problems','Selesaikan 20 soal logika','2026-02-05 23:59:59',100),
(18,'Proof Techniques','Buktikan 10 teorema matematika','2026-02-18 23:59:59',100),
(19,'Statistical Analysis','Analisis statistik dataset','2026-02-10 23:59:59',100),
(19,'Probability Problems','Selesaikan soal probabilitas','2026-02-22 23:59:59',100),
(20,'Algorithm Design','Rancang algoritma efisien','2026-03-08 23:59:59',100),
(20,'Complexity Analysis','Analisis kompleksitas algoritma','2026-03-18 23:59:59',100);

-- Materi (40 records - 2 per course)
INSERT INTO materi (mk_id, judul, deskripsi, tipe_file, nama_file, ukuran_file, file_path) VALUES
(1,'Pengenalan HTML & CSS','Materi dasar HTML dan CSS','application/pdf','WEB101_Week1.pdf',2458624,'uploads/materials/web101_1.pdf'),
(1,'JavaScript Fundamentals','Konsep dasar JavaScript','application/pdf','WEB101_Week2.pdf',1856421,'uploads/materials/web101_2.pdf'),
(2,'Database Design Basics','Perancangan database','application/pdf','DB202_Week1.pdf',3245789,'uploads/materials/db202_1.pdf'),
(2,'SQL Advanced','SQL lanjutan dan optimization','application/pdf','DB202_Week2.pdf',2845123,'uploads/materials/db202_2.pdf'),
(3,'UI Design Principles','Prinsip desain UI/UX','application/pdf','UX301_Week1.pdf',3124567,'uploads/materials/ux301_1.pdf'),
(3,'Prototyping dengan Figma','Tutorial Figma','application/pdf','UX301_Week2.pdf',2678945,'uploads/materials/ux301_2.pdf'),
(4,'Introduction to AI','Pengenalan kecerdasan buatan','application/pdf','AI401_Week1.pdf',4123456,'uploads/materials/ai401_1.pdf'),
(4,'Machine Learning Basics','Dasar machine learning','application/pdf','AI401_Week2.pdf',3856789,'uploads/materials/ai401_2.pdf'),
(5,'OOP Concepts','Konsep OOP dalam Java','application/pdf','PBO201_Week1.pdf',2967834,'uploads/materials/pbo201_1.pdf'),
(5,'Inheritance & Polymorphism','Inheritance dan polymorphism','application/pdf','PBO201_Week2.pdf',3145678,'uploads/materials/pbo201_2.pdf'),
(6,'Sejarah Pancasila','Sejarah dan filosofi Pancasila','application/pdf','PANC101_Week1.pdf',1856234,'uploads/materials/panc101_1.pdf'),
(6,'Implementasi Pancasila','Implementasi nilai Pancasila','application/pdf','PANC101_Week2.pdf',1967845,'uploads/materials/panc101_2.pdf'),
(7,'Python Syntax','Sintaks dasar Python','application/pdf','DP102_Week1.pdf',2456789,'uploads/materials/dp102_1.pdf'),
(7,'Python Data Structures','Struktur data Python','application/pdf','DP102_Week2.pdf',2678934,'uploads/materials/dp102_2.pdf'),
(8,'Arrays and Linked Lists','Array dan linked list','application/pdf','DS301_Week1.pdf',3145789,'uploads/materials/ds301_1.pdf'),
(8,'Trees and Graphs','Trees dan graphs','application/pdf','DS301_Week2.pdf',3456812,'uploads/materials/ds301_2.pdf'),
(9,'Network Fundamentals','Dasar jaringan komputer','application/pdf','NET302_Week1.pdf',3234567,'uploads/materials/net302_1.pdf'),
(9,'TCP/IP Protocol','Protokol TCP/IP','application/pdf','NET302_Week2.pdf',2945678,'uploads/materials/net302_2.pdf'),
(10,'OS Concepts','Konsep sistem operasi','application/pdf','OS303_Week1.pdf',3567890,'uploads/materials/os303_1.pdf'),
(10,'Process Management','Manajemen proses','application/pdf','OS303_Week2.pdf',3234589,'uploads/materials/os303_2.pdf'),
(11,'SDLC Models','Model SDLC','application/pdf','SE401_Week1.pdf',2845678,'uploads/materials/se401_1.pdf'),
(11,'Agile Methodology','Metodologi Agile','application/pdf','SE401_Week2.pdf',2678945,'uploads/materials/se401_2.pdf'),
(12,'Android Development','Pengembangan aplikasi Android','application/pdf','MOB402_Week1.pdf',3456789,'uploads/materials/mob402_1.pdf'),
(12,'iOS Development','Pengembangan aplikasi iOS','application/pdf','MOB402_Week2.pdf',3245678,'uploads/materials/mob402_2.pdf'),
(13,'Cloud Computing Intro','Pengenalan cloud computing','application/pdf','CLOUD501_Week1.pdf',3567845,'uploads/materials/cloud501_1.pdf'),
(13,'AWS Services','Layanan AWS','application/pdf','CLOUD501_Week2.pdf',3234567,'uploads/materials/cloud501_2.pdf'),
(14,'Security Fundamentals','Dasar keamanan informasi','application/pdf','SEC502_Week1.pdf',2956789,'uploads/materials/sec502_1.pdf'),
(14,'Cryptography','Kriptografi','application/pdf','SEC502_Week2.pdf',3145678,'uploads/materials/sec502_2.pdf'),
(15,'Data Science Intro','Pengenalan data science','application/pdf','DATSCI503_Week1.pdf',3456789,'uploads/materials/datsci503_1.pdf'),
(15,'Data Visualization','Visualisasi data','application/pdf','DATSCI503_Week2.pdf',3234567,'uploads/materials/datsci503_2.pdf'),
(16,'Computer Graphics Basics','Dasar grafika komputer','application/pdf','CG304_Week1.pdf',3567890,'uploads/materials/cg304_1.pdf'),
(16,'3D Rendering','Rendering 3D','application/pdf','CG304_Week2.pdf',3456789,'uploads/materials/cg304_2.pdf'),
(17,'English Grammar','Tata bahasa Inggris','application/pdf','ENG101_Week1.pdf',1856234,'uploads/materials/eng101_1.pdf'),
(17,'Academic Writing','Penulisan akademik','application/pdf','ENG101_Week2.pdf',1967845,'uploads/materials/eng101_2.pdf'),
(18,'Discrete Math Basics','Dasar matematika diskrit','application/pdf','MTK102_Week1.pdf',2456789,'uploads/materials/mtk102_1.pdf'),
(18,'Graph Theory','Teori graf','application/pdf','MTK102_Week2.pdf',2678934,'uploads/materials/mtk102_2.pdf'),
(19,'Descriptive Statistics','Statistika deskriptif','application/pdf','STAT201_Week1.pdf',2345678,'uploads/materials/stat201_1.pdf'),
(19,'Inferential Statistics','Statistika inferensial','application/pdf','STAT201_Week2.pdf',2567890,'uploads/materials/stat201_2.pdf'),
(20,'Algorithm Analysis','Analisis algoritma','application/pdf','ALGO401_Week1.pdf',3245678,'uploads/materials/algo401_1.pdf'),
(20,'Dynamic Programming','Dynamic programming','application/pdf','ALGO401_Week2.pdf',3456789,'uploads/materials/algo401_2.pdf');

-- Submissions (50 records)
INSERT INTO submissions (tugas_id, user_id, file_path, file_name, file_size, score, feedback, graded_at, graded_by) VALUES
(1,2,'uploads/submissions/sub001.zip','portfolio_bayu.zip',1234567,85.00,'Good work! Clean code.',NOW(),1),
(1,3,'uploads/submissions/sub002.zip','portfolio_ramdhanu.zip',1456789,78.00,'Nice design, improve JS.',NOW(),1),
(2,2,'uploads/submissions/sub003.zip','form_bayu.zip',987654,90.00,'Excellent validation!',NOW(),1),
(3,4,'uploads/submissions/sub004.pdf','db_design_alka.pdf',2345678,88.00,'Well structured design.',NOW(),1),
(3,5,'uploads/submissions/sub005.pdf','db_design_siti.pdf',2123456,92.00,'Perfect normalization!',NOW(),1),
(4,4,'uploads/submissions/sub006.sql','queries_alka.sql',456789,85.00,'Good optimization.',NOW(),1),
(5,3,'uploads/submissions/sub007.fig','ui_ramdhanu.fig',3456789,75.00,'Good start, more polish needed.',NOW(),1),
(5,4,'uploads/submissions/sub008.fig','ui_alka.fig',3234567,93.00,'Excellent UI!',NOW(),1),
(9,2,'uploads/submissions/sub009.zip','java_bayu.zip',4567890,82.00,'Good OOP principles.',NOW(),1),
(9,3,'uploads/submissions/sub010.zip','java_ramdhanu.zip',4234567,77.00,'Improve encapsulation.',NOW(),1),
(9,4,'uploads/submissions/sub011.zip','java_alka.zip',4456789,88.00,'Great implementation!',NOW(),1),
(10,2,'uploads/submissions/sub012.zip','patterns_bayu.zip',3456789,86.00,'Nice pattern usage.',NOW(),1),
(11,5,'uploads/submissions/sub013.pdf','pancasila_siti.pdf',1234567,90.00,'Deep analysis!',NOW(),1),
(13,2,'uploads/submissions/sub014.zip','python_bayu.zip',2345678,88.00,'Clean code!',NOW(),1),
(13,6,'uploads/submissions/sub015.zip','python_ahmad.zip',2456789,81.00,'Good logic.',NOW(),1),
(14,2,'uploads/submissions/sub016.zip','python_proj_bayu.zip',3456789,85.00,'Nice project!',NOW(),1),
(15,7,'uploads/submissions/sub017.zip','stack_dewi.zip',2345678,80.00,'Correct implementation.',NOW(),1),
(15,8,'uploads/submissions/sub018.zip','stack_rudi.zip',2456789,75.00,'Improve efficiency.',NOW(),1),
(17,9,'uploads/submissions/sub019.pdf','network_rina.pdf',3456789,87.00,'Good configuration!',NOW(),1),
(19,10,'uploads/submissions/sub020.pdf','scheduling_budi.pdf',2345678,83.00,'Good simulation.',NOW(),1),
(21,12,'uploads/submissions/sub021.pdf','design_maya.pdf',3456789,91.00,'Excellent UML!',NOW(),1),
(23,13,'uploads/submissions/sub022.zip','android_indra.apk',5678901,79.00,'Works well!',NOW(),1),
(25,14,'uploads/submissions/sub023.pdf','aws_lina.pdf',2345678,86.00,'Good deployment!',NOW(),1),
(27,15,'uploads/submissions/sub024.pdf','security_hendra.pdf',3456789,84.00,'Thorough audit.',NOW(),1),
(29,16,'uploads/submissions/sub025.ipynb','analysis_nina.ipynb',2345678,88.00,'Great analysis!',NOW(),1),
(31,17,'uploads/submissions/sub026.blend','3d_arif.blend',6789012,80.00,'Nice rendering!',NOW(),1),
(33,18,'uploads/submissions/sub027.pptx','pres_putri.pptx',3456789,85.00,'Clear presentation.',NOW(),1),
(35,19,'uploads/submissions/sub028.pdf','logic_dika.pdf',1234567,92.00,'Perfect answers!',NOW(),1),
(37,20,'uploads/submissions/sub029.xlsx','stats_sinta.xlsx',2345678,87.00,'Good analysis.',NOW(),1),
(39,21,'uploads/submissions/sub030.pdf','algo_design.pdf',2456789,89.00,'Efficient algorithm!',NOW(),1),
(1,7,'uploads/submissions/sub031.zip','portfolio_dewi.zip',1345678,82.00,'Good structure.',NOW(),1),
(2,8,'uploads/submissions/sub032.zip','form_rudi.zip',1123456,79.00,'Add more validation.',NOW(),1),
(3,9,'uploads/submissions/sub033.pdf','db_rina.pdf',2456789,90.00,'Excellent design!',NOW(),1),
(5,12,'uploads/submissions/sub034.fig','ui_maya.fig',3345678,94.00,'Outstanding design!',NOW(),1),
(9,15,'uploads/submissions/sub035.zip','java_hendra.zip',4678901,84.00,'Good practices.',NOW(),1),
(13,18,'uploads/submissions/sub036.zip','python_putri.zip',2567890,86.00,'Clean code!',NOW(),1),
(15,19,'uploads/submissions/sub037.zip','stack_dika.zip',2567890,88.00,'Perfect!',NOW(),1),
(17,20,'uploads/submissions/sub038.pdf','network_sinta.pdf',3567890,85.00,'Good work.',NOW(),1),
(19,21,'uploads/submissions/sub039.pdf','sched_bayu2.pdf',2456789,81.00,'Decent simulation.',NOW(),1),
(21,7,'uploads/submissions/sub040.pdf','design_dewi.pdf',3567890,89.00,'Great diagrams!',NOW(),1),
(23,11,'uploads/submissions/sub041.zip','android_andi.apk',5789012,82.00,'Good functionality.',NOW(),1),
(25,12,'uploads/submissions/sub042.pdf','aws_maya2.pdf',2456789,88.00,'Well deployed!',NOW(),1),
(27,14,'uploads/submissions/sub043.pdf','sec_lina.pdf',3567890,86.00,'Comprehensive audit.',NOW(),1),
(29,16,'uploads/submissions/sub044.ipynb','viz_nina.ipynb',2456789,91.00,'Beautiful viz!',NOW(),1),
(31,17,'uploads/submissions/sub045.blend','3d_arif2.blend',6890123,83.00,'Good scene!',NOW(),1),
(33,19,'uploads/submissions/sub046.pptx','pres_dika.pptx',3567890,87.00,'Excellent delivery.',NOW(),1),
(35,20,'uploads/submissions/sub047.pdf','logic_sinta.pdf',1345678,90.00,'All correct!',NOW(),1),
(37,21,'uploads/submissions/sub048.xlsx','stats_bayu2.xlsx',2456789,85.00,'Good insights.',NOW(),1),
(39,6,'uploads/submissions/sub049.pdf','algo_ahmad.pdf',2567890,87.00,'Efficient solution!',NOW(),1),
(40,10,'uploads/submissions/sub050.pdf','complexity_budi.pdf',2345678,88.00,'Thorough analysis!',NOW(),1);
