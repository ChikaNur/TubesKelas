-- Migration: Add materials (materi) table
-- Date: 2026-01-08
-- Description: Creates table to store course materials/resources

CREATE TABLE IF NOT EXISTS `materi` (
  `materi_id` int(11) NOT NULL AUTO_INCREMENT,
  `mk_id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tipe_file` varchar(100) DEFAULT NULL,
  `nama_file` varchar(255) NOT NULL,
  `ukuran_file` int(11) NOT NULL COMMENT 'File size in bytes',
  `file_path` varchar(500) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`materi_id`),
  KEY `idx_mk_id` (`mk_id`),
  KEY `idx_uploaded_at` (`uploaded_at`),
  CONSTRAINT `fk_materi_mk` FOREIGN KEY (`mk_id`) REFERENCES `mata_kuliah` (`mk_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create directory for materials if not exists
-- Materials will be stored in: /uploads/materials/

-- Sample data (optional - uncomment to insert sample materials)
/*
INSERT INTO `materi` (`mk_id`, `judul`, `deskripsi`, `tipe_file`, `nama_file`, `ukuran_file`, `file_path`) VALUES
(1, 'Pengenalan Pemrograman Berorientasi Objek', 'Materi perkuliahan minggu 1 tentang konsep dasar OOP', 'application/pdf', 'PBO_Week1_Introduction.pdf', 2458624, 'uploads/materials/pbo_week1.pdf'),
(1, 'Slide: Class dan Object', 'Presentasi tentang class dan object dalam Java', 'application/pdf', 'PBO_Week2_ClassObject.pdf', 1856421, 'uploads/materials/pbo_week2.pdf'),
(2, 'Kecerdasan Buatan: Pengantar', 'Konsep dasar AI dan machine learning', 'application/pdf', 'AI_Introduction.pdf', 3245789, 'uploads/materials/ai_intro.pdf');
*/
