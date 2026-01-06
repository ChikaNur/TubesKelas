# EduLearn - Learning Management System

Platform pembelajaran digital berbasis PHP Native untuk manajemen perkuliahan.

## 📋 Features

### For Students (Mahasiswa)
- ✅ Login & Registration with secure password hashing
- ✅ Dashboard with course overview and progress tracking
- ✅ View enrolled courses and materials
- ✅ Assignment management with deadline tracking
- ✅ Weekly timetable view
- ✅ Profile management

### For Admin
- ✅ Admin dashboard with statistics
- ✅ Course management (Create, Read, Update, Delete)
- ✅ User management
- ✅ Assignment management
- ✅ Upload course materials (PDF, images, videos) stored as BLOB
- ✅ View/download materials

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+ (Native)
- **Database**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Server**: Laragon (recommended) or XAMPP/WAMP

## 📁 Project Structure

```
c:/laragon/www/tubes pwi/
├── config/                   # Configuration files
│   ├── database.php          # Database connection (PDO)
│   └── session.php           # Session configuration
├── includes/                 # Helper functions
│   └── functions.php         # Utility functions
├── auth/                     # Authentication handlers
│   ├── login_process.php     # Login handler
│   ├── register_process.php  # Registration handler
│   └── logout.php            # Logout handler
├── admin/                    # Admin CRUD handlers
│   └── courses_process.php   # Course CRUD handler
├── materials/                # File upload system (BLOB)
│   ├── upload_materi.php     # Upload materials
│   ├── view_materi.php       # View/download materials
│   └── delete_materi.php     # Delete materials
├── database/                 # SQL schema
│   └── database.sql          # Database schema + dummy data
├── assets/                   # Static assets
│   ├── css/                  # All CSS files
│   │   ├── index.css         # Login/signup styles
│   │   ├── dashboard.css     # Student dashboard styles
│   │   └── adminindex.css    # Admin panel styles
│   └── js/                   # JavaScript files
│       └── navigation.js     # Navigation helper
├── old_html_backup/          # Backup of old HTML files (can be deleted)
├── index.php                 # Entry point (redirects to login)
├── login.php                 # Login page
├── signup.php                # Registration page
├── dashboard.php             # Student dashboard
├── admindashboard.php        # Admin dashboard
├── admincourses.php          # Admin course management
└── README.md                 # This file
```

## 🚀 Setup Instructions

### 1. Prerequisites

- Laragon (or XAMPP/WAMP) installed
- PHP 7.4 or higher
- MySQL 5.7 or MariaDB
- Web browser (Chrome, Firefox, Edge)

### 2. Database Setup

1. Open **phpMyAdmin** or **MySQL client**
2. Create a new database or use the SQL script:

```sql
-- Via command line:
mysql -u root -p < database/database.sql

-- Or import via phpMyAdmin:
-- 1. Click "Import" tab
-- 2. Choose file: database/database.sql
-- 3. Click "Go"
```

3. The script will:
   - Create database `edulearn`
   - Create 8 tables (users, dosen, mata_kuliah, enrollments, jadwal, tugas, materi, submissions)
   - Insert dummy data (users, courses, assignments, etc.)

### 3. Configuration

1. Open `config/database.php`
2. Update database credentials if needed:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'edulearn');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default Laragon is empty
```

### 4. File Upload Settings (Optional)

For large file uploads (>2MB), edit `php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

Restart Apache after changes.

### 5. Access the Application

1. Start Laragon/XAMPP
2. Open browser and go to:
   - **Main Entry**: `http://localhost/tubes%20pwi/` (auto-redirects to login)
   - **Direct Login**: `http://localhost/tubes%20pwi/login.php`

## 👤 Demo Credentials

### Admin Account
- Email: `admin@edulearn.ac.id`
- Password: `password`

### Student Accounts
- Email: `bayu@student.telkomuniversity.ac.id` | Password: `password`
- Email: `ramdhanu@student.telkomuniversity.ac.id` | Password: `password`
- Email: `alka@student.telkomuniversity.ac.id` | Password: `password`

## 📊 Database Schema

### Main Tables

1. **users** - Student and admin accounts
   - user_id, nama, nim, email, password (hashed), role

2. **dosen** - Instructor information
   - dosen_id, nama_dosen, email, bidang_keahlian

3. **mata_kuliah** - Course information
   - mk_id, kode_mk, nama_mk, deskripsi, sks, semester, dosen_id

4. **enrollments** - Student course registrations
   - enrollment_id, user_id, mk_id, progress

5. **jadwal** - Course schedules
   - jadwal_id, mk_id, hari, jam_mulai, jam_selesai, ruangan

6. **tugas** - Assignments
   - tugas_id, mk_id, judul, deskripsi, deadline

7. **materi** - Course materials (BLOB storage)
   - materi_id, mk_id, judul, file (LONGBLOB), tipe_file, ukuran_file

8. **submissions** - Assignment submissions
   - submission_id, tugas_id, user_id, file (LONGBLOB), score

## 🔒 Security Features

- ✅ Password hashing using `bcrypt` (password_hash/password_verify)
- ✅ SQL Injection prevention with PDO prepared statements
- ✅ XSS prevention with `htmlspecialchars()`
- ✅ Session security (httponly, secure cookies)
- ✅ CSRF protection (session tokens)
- ✅ Input validation and sanitization
- ✅ Role-based access control

## 📝 Usage Guide

### For Students

1. **Register**: Create account via signup page
2. **Login**: Use your credentials
3. **Dashboard**: View enrolled courses, assignments, progress
4. **Courses**: Access course materials and resources
5. **Assignments**: Submit assignments before deadline
6. **Timetable**: Check weekly schedule

### For Admin

1. **Login**: Use admin credentials
2. **Manage Courses**: Add/edit/delete courses
3. **Upload Materials**: Upload PDF, images, videos for courses
4. **Manage Users**: View and manage student accounts
5. **View Reports**: Monitor system activity

## 🐛 Troubleshooting

### Database Connection Error
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database `edulearn` exists

### File Upload Error
- Check `php.ini` file upload settings
- Ensure `materials/` folder has write permissions
- Check file size and type restrictions

### Session Issues
- Clear browser cookies
- Check if session files directory has write permissions

### Page Not Loading
- Check Apache is running
- Verify file permissions
- Check PHP error logs

## 📚 Additional Information

### File Upload Supported Types
- PDF documents
- Images (JPEG, PNG, GIF)
- Videos (MP4, MPEG)
- Microsoft Office (Word, PowerPoint)

### Maximum File Size
- Default: 10MB
- Configurable in `php.ini` and `materials/upload_materi.php`

## 👨‍💻 Development Team

- Muhammad Ramdhanu Damardjati
- Mohamad Alka Wngasadibrata
- Bayu Muda Herlambang

**Telkom University**  
Mata Kuliah: Desain Antarmuka Pengguna

## 📄 License

Educational project for Tugas Besar (Final Project)

---

**Last Updated**: January 2026
