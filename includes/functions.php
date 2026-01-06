<?php
/**
 * Utility Functions
 * Common helper functions used throughout the application
 */

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 * @return bool
 */
function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Redirect to a specific page
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Escape HTML to prevent XSS
 * @param string $string
 * @return string
 */
function escape_html($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date to Indonesian format
 * @param string $date
 * @param bool $withTime
 * @return string
 */
function format_date($date, $withTime = false) {
    $timestamp = strtotime($date);
    $months = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $day = date('d', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    $formatted = "$day $month $year";
    
    if ($withTime) {
        $time = date('H:i', $timestamp);
        $formatted .= " $time";
    }
    
    return $formatted;
}

/**
 * Format day to Indonesian
 * @param string $day English day name
 * @return string
 */
function format_day($day) {
    $days = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu'
    ];
    
    return $days[$day] ?? $day;
}

/**
 * Get user info from session
 * @param string $key
 * @return mixed
 */
function get_user_info($key = null) {
    if ($key === null) {
        return [
            'user_id' => $_SESSION['user_id'] ?? null,
            'nama' => $_SESSION['nama'] ?? null,
            'email' => $_SESSION['email'] ?? null,
            'nim' => $_SESSION['nim'] ?? null,
            'role' => $_SESSION['role'] ?? null
        ];
    }
    
    return $_SESSION[$key] ?? null;
}

/**
 * Calculate time difference (for deadlines)
 * @param string $datetime
 * @return string
 */
function time_difference($datetime) {
    $now = new DateTime();
    $target = new DateTime($datetime);
    $diff = $now->diff($target);
    
    if ($diff->invert) {
        // Past date
        if ($diff->days > 0) {
            return $diff->days . " hari yang lalu";
        } elseif ($diff->h > 0) {
            return $diff->h . " jam yang lalu";
        } else {
            return $diff->i . " menit yang lalu";
        }
    } else {
        // Future date
        if ($diff->days > 0) {
            return $diff->days . " hari lagi";
        } elseif ($diff->h > 0) {
            return $diff->h . " jam lagi";
        } else {
            return $diff->i . " menit lagi";
        }
    }
}

/**
 * Generate CSRF token
 * @return string
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token
 * @return bool
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format file size
 * @param int $bytes
 * @return string
 */
function format_file_size($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $index = 0;
    
    while ($bytes >= 1024 && $index < count($units) - 1) {
        $bytes /= 1024;
        $index++;
    }
    
    return round($bytes, 2) . ' ' . $units[$index];
}

/**
 * Get MIME type description
 * @param string $mime
 * @return string
 */
function get_mime_description($mime) {
    $descriptions = [
        'application/pdf' => 'PDF Document',
        'image/jpeg' => 'JPEG Image',
        'image/png' => 'PNG Image',
        'image/gif' => 'GIF Image',
        'video/mp4' => 'MP4 Video',
        'video/mpeg' => 'MPEG Video',
        'application/msword' => 'Word Document',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'Word Document',
        'application/vnd.ms-excel' => 'Excel Spreadsheet',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'Excel Spreadsheet',
    ];
    
    return $descriptions[$mime] ?? 'Unknown File Type';
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Sanitize input
 * @param string $data
 * @return string
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
