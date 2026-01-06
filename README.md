# 📚 EduLearn - Learning Management System

> Sistem Manajemen Pembelajaran berbasis PHP Native dengan MySQL untuk Tugas Besar Pemrograman Web Interaktif

![PHP](https://img.shields.io/badge/PHP-7.4+-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)
![Status](https://img.shields.io/badge/Status-Production%20Ready-brightgreen)

---

## 📖 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Instalasi](#-instalasi)
- [Cara Akses](#-cara-akses)
- [Akun Demo](#-akun-demo)
- [Struktur Database](#-struktur-database)
- [Struktur Folder](#-struktur-folder)
- [Keamanan](#-keamanan)
- [Dokumentasi API](#-dokumentasi-api)
- [Troubleshooting](#-troubleshooting)
- [Kontributor](#-kontributor)

---

## 🎯 Tentang Proyek

EduLearn adalah Learning Management System (LMS) yang dikembangkan menggunakan **PHP Native** dan **MySQL**. Sistem ini dirancang untuk memfasilitasi proses pembelajaran online dengan fitur-fitur lengkap untuk mahasiswa dan administrator.

**Developed by:**
- Bayu Muda Herlambang (707082400072)
- Muhammad Ramdhanu Damardjati (707082400001)
- Mohamad Alka Wngasadibrata (707082400002)

**Course:** Pemrograman Web Interaktif  
**Year:** 2025/2026

---

## ✨ Fitur Utama

### 👨‍🎓 Untuk Mahasiswa:
- ✅ **Dashboard** dengan statistik pembelajaran
- ✅ **Manajemen Courses** - Lihat mata kuliah yang diambil
- ✅ **Course Materials** - Akses materi pembelajaran (PDF, Video, Dokumen)
- ✅ **Assignments** - Lihat dan upload tugas
- ✅ **Timetable** - Jadwal mingguan perkuliahan
- ✅ **Profile Management** - Kelola informasi akun

### 👨‍💼 Untuk Admin:
- ✅ **Dashboard** dengan statistik sistem
- ✅ **User Management** - CRUD pengguna (mahasiswa & admin)
- ✅ **Course Management** - CRUD mata kuliah
- ✅ **Assignment Management** - Buat dan kelola tugas
- ✅ **Grading System** - Nilai submission mahasiswa
- ✅ **Material Upload** - Upload materi ke database (BLOB)

### 🔐 Keamanan:
- ✅ Password Hashing (Bcrypt)
- ✅ SQL Injection Prevention (PDO Prepared Statements)
- ✅ XSS Protection (Input Sanitization)
- ✅ Session Security (HttpOnly, Secure Cookies)
- ✅ File Upload Validation
- ✅ Role-Based Access Control

---

## 🛠️ Teknologi

| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 7.4+ | Backend Logic |
| **MySQL** | 5.7+ | Database |
| **HTML5** | - | Structure |
| **CSS3** | - | Styling |
| **JavaScript** | ES6+ | Interactivity |
| **PDO** | - | Database Access |

**No Framework Used** - Pure PHP Native!

---

## 📥 Instalasi

### Prerequisites:
- XAMPP / Laragon / WAMP Server
- PHP 7.4 atau lebih baru
- MySQL 5.7 atau lebih baru
- Browser modern (Chrome, Firefox, Edge)

### Langkah Instalasi:

#### 1️⃣ Clone atau Download Project
```bash
# Clone repository
git clone <repository-url>

# Atau download dan extract ke folder htdocs/www
# Contoh: C:/laragon/www/tubes pwi
```

#### 2️⃣ Import Database
```bash
1. Buka phpMyAdmin (http://localhost/phpmyadmin)
2. Klik "New" untuk membuat database baru
3. Nama database: edulearn
4. Klik "Import"
5. Pilih file: database/database.sql
6. Klik "Go"
```

**ATAU** via Command Line:
```bash
mysql -u root -p
CREATE DATABASE edulearn;
USE edulearn;
SOURCE database/database.sql;
```

#### 3️⃣ Konfigurasi Database (Opsional)
Jika menggunakan username/password MySQL berbeda dari default, edit file:

**File:** `config/database.php`
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'edulearn');
define('DB_USER', 'root');        // <- Ganti jika perlu
define('DB_PASS', '');            // <- Ganti jika perlu
```

#### 4️⃣ Start Server
- **Laragon:** Klik "Start All"
- **XAMPP:** Start Apache & MySQL
- **WAMP:** Start All Services

#### 5️⃣ Selesai! 🎉
Aplikasi siap digunakan di browser.

---

## 🌐 Cara Akses

### 📍 URL Akses Aplikasi:

**Entry Point (Auto-redirect ke Login):**
```
http://localhost/tubes pwi/
atau
http://localhost/tubes pwi/index.php
```

**Halaman Login:**
```
http://localhost/tubes pwi/login.php
```

**Halaman Registrasi:**
```
http://localhost/tubes pwi/signup.php
```

### 📂 Akses Berdasarkan Role:

#### Student Dashboard:
```
http://localhost/tubes pwi/student/dashboard.php
```

**Student Pages:**
- Courses: `/student/courses.php`
- Assignments: `/student/assignments.php`
- Timetable: `/student/timetable.php`
- Profile: `/student/profile.php`

#### Admin Dashboard:
```
http://localhost/tubes pwi/admin/dashboard.php
```

**Admin Pages:**
- Manage Users: `/admin/users.php`
- Manage Courses: `/admin/courses.php`
- Manage Assignments: `/admin/assignments.php`

---

## 👤 Akun Demo

### 🔑 Admin Account:
```
Email: admin@edulearn.ac.id
Password: password
```
**Akses:** Full system management

### 👨‍🎓 Student Accounts:

**Account 1 (Bayu):**
```
Email: bayu@student.telkomuniversity.ac.id
Password: password
NIM: 707082400072
```

**Account 2 (Ramdhanu):**
```
Email: ramdhanu@student.telkomuniversity.ac.id
Password: password
NIM: 707082400001
```

**Account 3 (Alka):**
```
Email: alka@student.telkomuniversity.ac.id
Password: password
NIM: 707082400002
```

> ⚠️ **Important:** Untuk production, segera ganti password default!

---

## 🗄️ Struktur Database

### Database Name: `edulearn`

### Tabel Utama:

| Table | Purpose | Records |
|-------|---------|---------|
| `users` | Menyimpan data mahasiswa & admin | 6 rows |
| `dosen` | Data dosen/pengajar | 6 rows |
| `mata_kuliah` | Data mata kuliah | 7 rows |
| `enrollments` | Relasi mahasiswa-mata kuliah | 15 rows |
| `jadwal` | Jadwal perkuliahan mingguan | 9 rows |
| `tugas` | Daftar assignments | 8 rows |
| `materi` | Course materials (BLOB storage) | Dynamic |
| `submissions` | Assignment submissions (BLOB) | Dynamic |

### Entity Relationship:
```
users (1) ----< (N) enrollments (N) >---- (1) mata_kuliah
                                                    |
                                                    | (1)
                                                    |
                                                    v (N)
                                            tugas/materi/jadwal
```

### Key Fields:

**users table:**
- `role`: ENUM('mahasiswa', 'admin')
- `password`: Bcrypt hashed
- `nim`: Unique identifier untuk mahasiswa

**submissions table:**
- `file`: LONGBLOB (stores actual file)
- `is_late`: Tracking keterlambatan
- `score`: Nilai assignment

---

## 📁 Struktur Folder

```
tubes pwi/
│
├── 📂 config/              # Konfigurasi sistem
│   ├── database.php        # Database connection
│   └── session.php         # Session management
│
├── 📂 includes/            # Helper functions
│   ├── functions.php       # Utility functions
│   └── messages.php        # Message display component
│
├── 📂 auth/                # Authentication handlers
│   ├── login_process.php
│   ├── register_process.php
│   └── logout.php
│
├── 📂 student/             # Student module
│   ├── dashboard.php       # Student dashboard
│   ├── courses.php         # Course listing
│   ├── course_detail.php   # Course materials
│   ├── assignments.php     # Assignment list
│   ├── submit_assignment.php
│   ├── timetable.php       # Weekly schedule
│   └── profile.php         # User profile
│
├── 📂 admin/               # Admin module
│   ├── dashboard.php       # Admin dashboard
│   ├── users.php           # User management
│   ├── users_process.php
│   ├── courses.php         # Course management
│   ├── courses_process.php
│   ├── assignments.php     # Assignment management
│   ├── assignments_process.php
│   ├── view_submissions.php
│   └── grade_submission.php
│
├── 📂 materials/           # File management
│   ├── upload_materi.php   # Upload materials
│   ├── view_materi.php     # Download materials/submissions
│   └── delete_materi.php
│
├── 📂 database/            # SQL files
│   └── database.sql        # Full database schema + data
│
├── 📂 assets/              # Static files
│   ├── 📂 css/            # Stylesheets
│   └── 📂 js/             # JavaScript files
│
├── 📂 old_html_backup/     # Original HTML files (for reference)
│
├── index.php               # Entry point
├── login.php               # Login page
├── signup.php              # Registration page
└── README.md               # This file
```

---

## 🔐 Keamanan

### Implemented Security Measures:

#### 1. **Password Security**
```php
// Hashing saat registrasi
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Verifikasi saat login
password_verify($inputPassword, $hashedPassword);
```

#### 2. **SQL Injection Prevention**
```php
// Menggunakan PDO Prepared Statements
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
```

#### 3. **XSS Protection**
```php
// Sanitize input
function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Escape output
echo escape_html($userInput);
```

#### 4. **File Upload Validation**
- ✅ Type checking (PDF, DOC, ZIP, RAR only)
- ✅ Size limit (5MB for assignments, 10MB for materials)
- ✅ MIME type validation
- ✅ Enrollment verification

#### 5. **Session Security**
```php
session_set_cookie_params([
    'httponly' => true,
    'secure' => false,  // Set true in production with HTTPS
    'samesite' => 'Strict'
]);
```

---

## 📚 Dokumentasi API

### Authentication Endpoints:

#### Login
```
POST /auth/login_process.php
Parameters:
  - email (string)
  - password (string)
Response: Redirect to role-specific dashboard
```

#### Register
```
POST /auth/register_process.php
Parameters:
  - nama (string)
  - nim (string)
  - email (string)
  - password (string)
  - confirm_password (string)
Response: Auto-login & redirect to student dashboard
```

### File Operations:

#### Upload Assignment
```
POST /student/submit_assignment.php
Parameters:
  - tugas_id (int)
  - file (file) - Max 5MB
Allowed: PDF, DOC, DOCX, ZIP, RAR
```

#### Download Material
```
GET /materials/view_materi.php?id={materi_id}
or
GET /materials/view_materi.php?type=submission&id={submission_id}
```

---

## ⚠️ Troubleshooting

### Problem: "Database connection failed"
**Solution:**
1. Pastikan MySQL server running
2. Check credentials di `config/database.php`
3. Pastikan database `edulearn` sudah di-import

### Problem: "Page not found" atau "404 Error"
**Solution:**
1. Check URL path, pastikan sesuai dengan folder project
2. Untuk Laragon: `http://localhost/tubes pwi/`
3. Clear browser cache

### Problem: "Login failed" dengan akun demo
**Solution:**
1. Pastikan database sudah di-import
2. Password default: `password` (lowercase, tanpa spasi)
3. Check console browser untuk error

### Problem: File upload gagal
**Solution:**
1. Check `php.ini`:
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```
2. Restart server setelah edit php.ini
3. Check file type (hanya PDF, DOC, DOCX, ZIP, RAR)

### Problem: Session error atau auto-logout
**Solution:**
1. Clear browser cookies
2. Check folder `tmp` writable
3. Restart browser

---

## 📊 Fitur Highlight

### 🎨 User Experience:
- ✅ **Responsive Design** - Mobile-friendly
- ✅ **Real-time Feedback** - Success/error messages
- ✅ **Progress Tracking** - Visual progress bars
- ✅ **Auto-hide Messages** - Messages hilang otomatis setelah 5 detik
- ✅ **Color Coding** - Status badges dengan warna berbeda
- ✅ **Modal Forms** - Clean add/edit forms

### 💾 File Management:
- ✅ **BLOB Storage** - Files disimpan langsung di database
- ✅ **Access Control** - Mahasiswa hanya bisa akses materi dari course yang diambil
- ✅ **Download Tracking** - Admin bisa lihat siapa yang download
- ✅ **Late Submission** - Auto-detect submission terlambat

### 📈 Analytics:
- ✅ **Dashboard Statistics** - Real-time data
- ✅ **Progress Tracking** - Per-course progress percentage
- ✅ **Deadline Warnings** - Alert untuk deadline mendekat
- ✅ **Submission Status** - Track assignment completion

---

## 🚀 Deployment Guide

### For Production:

1. **Update Database Config:**
   ```php
   define('DB_HOST', 'your-host');
   define('DB_NAME', 'your-db');
   define('DB_USER', 'your-user');
   define('DB_PASS', 'your-password');
   ```

2. **Enable HTTPS:**
   ```php
   // In config/session.php
   'secure' => true,  // Changed from false
   ```

3. **Change Default Passwords:**
   - Admin password
   - All demo accounts

4. **Set Error Reporting:**
   ```php
   // In production
   error_reporting(0);
   ini_set('display_errors', 0);
   ```

5. **Backup Database:**
   ```bash
   mysqldump -u root -p edulearn > backup.sql
   ```

---

## 📝 License

This project is developed for educational purposes as part of Web Programming coursework at Telkom University.

---

## 🤝 Kontributor

- **Bayu Muda Herlambang** - 707082400072 - Lead Developer
- **Muhammad Ramdhanu Damardjati** - 707082400001 - Developer
- **Mohamad Alka Wngasadibrata** - 707082400002 - Developer

**Dosen Pengampu:** [Nama Dosen]  
**Mata Kuliah:** Pemrograman Web Interaktif  
**Fakultas:** Fakultas Informatika  
**Universitas:** Telkom University

---

## 📞 Support

Jika menemukan bug atau ingin request fitur:
1. Buat issue di repository
2. Atau hubungi developer

---

## 🎓 Acknowledgments

Terima kasih kepada:
- Dosen Pemrograman Web Interaktif
- Teman-teman yang telah membantu testing
- StackOverflow community untuk references

---

**Last Updated:** January 6, 2026  
**Version:** 1.0.0  
**Status:** ✅ Production Ready

---

<div align="center">

### ⭐ Jika project ini membantu, berikan star!

**Made with ❤️ by Telkom University Students**

</div>
