// ========== INISIALISASI APLIKASI ==========
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 EduLearn Platform loaded');
    
    // Cek jika sudah login sebelumnya
    checkPreviousLogin();
    
    // Setup semua event listeners
    setupEventListeners();
    
    // Setup password toggles
    setupPasswordToggles();
});

// ========== VARIABEL GLOBAL ==========
let currentUser = null;
let currentRole = null;

// ========== FUNGSI UTAMA ==========
function checkPreviousLogin() {
    const savedUser = localStorage.getItem('edulearn_user');
    const savedRole = localStorage.getItem('edulearn_role');
    
    if (savedUser && savedRole) {
        currentUser = JSON.parse(savedUser);
        currentRole = savedRole;
        
        // Tampilkan dashboard sesuai role
        if (currentRole === 'user') {
            showUserDashboard();
        } else if (currentRole === 'admin') {
            showAdminDashboard();
        }
    }
}

function setupEventListeners() {
    // ===== HALAMAN PILIHAN =====
    const userOption = document.getElementById('login-as-user');
    const adminOption = document.getElementById('login-as-admin');
    const registerLink = document.getElementById('register-link');
    const helpLink = document.getElementById('help-link');
    
    if (userOption) {
        userOption.addEventListener('click', function() {
            showPage('user-login-page');
            showNotification('Login sebagai User/Mahasiswa', 'info');
        });
    }
    
    if (adminOption) {
        adminOption.addEventListener('click', function() {
            showPage('admin-login-page');
            showNotification('Login sebagai Administrator', 'warning');
        });
    }
    
    if (registerLink) {
        registerLink.addEventListener('click', function(e) {
            e.preventDefault();
            showNotification('Fitur pendaftaran akan segera tersedia', 'info');
        });
    }
    
    if (helpLink) {
        helpLink.addEventListener('click', function(e) {
            e.preventDefault();
            showNotification('Membuka panduan pengguna', 'info');
        });
    }
    
    // ===== HALAMAN LOGIN USER =====
    const backFromUser = document.getElementById('back-from-user');
    const userLoginForm = document.getElementById('user-login-form');
    const registerNow = document.getElementById('register-now');
    const socialButtons = document.querySelectorAll('.social-btn');
    
    if (backFromUser) {
        backFromUser.addEventListener('click', function() {
            showPage('selection-page');
        });
    }
    
    if (userLoginForm) {
        userLoginForm.addEventListener('submit', handleUserLogin);
    }
    
    if (registerNow) {
        registerNow.addEventListener('click', function(e) {
            e.preventDefault();
            showNotification('Halaman pendaftaran akan segera tersedia', 'info');
        });
    }
    
    socialButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const platform = this.classList.contains('google') ? 'Google' : 'Microsoft';
            showNotification(`Login dengan ${platform} belum tersedia`, 'warning');
        });
    });
    
    // ===== HALAMAN LOGIN ADMIN =====
    const backFromAdmin = document.getElementById('back-from-admin');
    const adminLoginForm = document.getElementById('admin-login-form');
    
    if (backFromAdmin) {
        backFromAdmin.addEventListener('click', function() {
            showPage('selection-page');
        });
    }
    
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', handleAdminLogin);
    }
    
    // ===== HALAMAN DASHBOARD =====
    const logoutUser = document.getElementById('logout-user');
    const logoutAdmin = document.getElementById('logout-admin');
    
    if (logoutUser) {
        logoutUser.addEventListener('click', handleLogout);
    }
    
    if (logoutAdmin) {
        logoutAdmin.addEventListener('click', handleLogout);
    }
}

function setupPasswordToggles() {
    const toggles = document.querySelectorAll('.password-toggle');
    
    toggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

// ========== FUNGSI LOGIN ==========
function handleUserLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('user-email').value;
    const password = document.getElementById('user-password').value;
    const remember = document.getElementById('remember-user').checked;
    
    // Validasi sederhana
    if (!email || !password) {
        showNotification('Email dan password harus diisi', 'error');
        return;
    }
    
    // Simulasi login user
    const mockUsers = [
        { email: 'bayu@email.ac.id', password: 'password123', name: 'Bayu Muda Herlambang', nim: '707082400072' },
        { email: 'student@email.ac.id', password: 'student123', name: 'Student Demo', nim: '123456789' }
    ];
    
    const foundUser = mockUsers.find(user => 
        user.email === email && user.password === password
    );
    
    if (foundUser) {
        // Simpan data user
        currentUser = {
            id: Date.now(),
            name: foundUser.name,
            email: foundUser.email,
            nim: foundUser.nim,
            role: 'user'
        };
        
        currentRole = 'user';
        
        // Simpan ke localStorage jika remember me dicentang
        if (remember) {
            localStorage.setItem('edulearn_user', JSON.stringify(currentUser));
            localStorage.setItem('edulearn_role', 'user');
        }
        
        showNotification(`Login berhasil! Selamat datang ${foundUser.name}`, 'success');
        
        // Redirect ke dashboard user setelah delay
        setTimeout(() => {
            showUserDashboard();
        }, 1500);
    } else {
        showNotification('Email atau password salah', 'error');
    }
}

function handleAdminLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('admin-email').value;
    const password = document.getElementById('admin-password').value;
    const twoFA = document.getElementById('admin-2fa').value;
    const remember = document.getElementById('remember-admin').checked;
    
    // Validasi
    if (!email || !password) {
        showNotification('ID Admin dan password harus diisi', 'error');
        return;
    }
    
    // Simulasi login admin
    const mockAdmins = [
        { email: 'admin@edulearn.ac.id', password: 'Admin@123', name: 'Super Admin', role: 'superadmin' },
        { email: 'operator@edulearn.ac.id', password: 'Operator@123', name: 'System Operator', role: 'operator' }
    ];
    
    const foundAdmin = mockAdmins.find(admin => 
        admin.email === email && admin.password === password
    );
    
    if (foundAdmin) {
        // Jika 2FA diisi, validasi (dalam simulasi, 2FA apa saja diterima)
        if (twoFA && twoFA.length !== 6) {
            showNotification('Kode 2FA harus 6 digit', 'warning');
            return;
        }
        
        // Simpan data admin
        currentUser = {
            id: Date.now(),
            name: foundAdmin.name,
            email: foundAdmin.email,
            role: foundAdmin.role,
            loginTime: new Date().toISOString()
        };
        
        currentRole = 'admin';
        
        // Simpan ke localStorage jika remember dicentang
        if (remember) {
            localStorage.setItem('edulearn_user', JSON.stringify(currentUser));
            localStorage.setItem('edulearn_role', 'admin');
        }
        
        showNotification(`Login admin berhasil! Selamat bekerja ${foundAdmin.name}`, 'success');
        
        // Log aktivitas admin
        console.log(`🔐 Admin login: ${foundAdmin.name} (${foundAdmin.role})`);
        
        // Redirect ke dashboard admin setelah delay
        setTimeout(() => {
            showAdminDashboard();
        }, 1500);
    } else {
        showNotification('Kredensial admin tidak valid', 'error');
    }
}

// ========== FUNGSI LOGOUT ==========
function handleLogout() {
    if (confirm('Apakah Anda yakin ingin logout?')) {
        // Hapus data dari localStorage
        localStorage.removeItem('edulearn_user');
        localStorage.removeItem('edulearn_role');
        
        // Reset variabel
        currentUser = null;
        currentRole = null;
        
        // Tampilkan halaman pilihan
        showPage('selection-page');
        
        // Tampilkan notifikasi
        showNotification('Anda telah logout dari sistem', 'info');
    }
}

// ========== FUNGSI NAVIGASI HALAMAN ==========
function showPage(pageId) {
    // Sembunyikan semua halaman
    const pages = document.querySelectorAll('.page');
    pages.forEach(page => page.classList.remove('active'));
    
    // Tampilkan halaman yang dipilih
    const targetPage = document.getElementById(pageId);
    if (targetPage) {
        targetPage.classList.add('active');
        targetPage.classList.add('animate__animated', 'animate__fadeIn');
        
        // Scroll ke atas
        window.scrollTo(0, 0);
        
        console.log(`📄 Navigasi ke: ${pageId}`);
    }
}

function showUserDashboard() {
    showPage('user-dashboard-page');
    
    // Load data user dashboard
    loadUserDashboardData();
}

function showAdminDashboard() {
    showPage('admin-dashboard-page');
    
    // Load data admin dashboard
    loadAdminDashboardData();
}

// ========== FUNGSI LOAD DATA DASHBOARD ==========
function loadUserDashboardData() {
    // Dalam implementasi nyata, ini akan fetch dari API
    console.log('📊 Loading user dashboard data...');
    
    // Update placeholder dengan nama user
    const placeholder = document.querySelector('#user-dashboard-page .dashboard-placeholder');
    if (placeholder && currentUser) {
        placeholder.innerHTML = `
            <h2>Selamat Datang, ${currentUser.name}!</h2>
            <p>NIM: ${currentUser.nim || 'Tidak tersedia'}</p>
            <p>Dashboard User/Mahasiswa EduLearn</p>
            <div style="margin: 30px 0;">
                <div style="background: white; color: #333; padding: 20px; border-radius: 12px; margin-bottom: 15px;">
                    <h3>🎯 Fitur yang tersedia:</h3>
                    <ul style="text-align: left; margin-top: 15px;">
                        <li>📚 Materi Pembelajaran</li>
                        <li>📝 Tugas & Assignment</li>
                        <li>📅 Jadwal Kuliah</li>
                        <li>📊 Progress Belajar</li>
                        <li>💬 Diskusi dengan Dosen</li>
                    </ul>
                </div>
            </div>
            <button class="btn-logout" id="logout-user">Logout</button>
        `;
        
        // Re-attach event listener
        document.getElementById('logout-user').addEventListener('click', handleLogout);
    }
}

function loadAdminDashboardData() {
    // Dalam implementasi nyata, ini akan fetch dari API
    console.log('📊 Loading admin dashboard data...');
    
    // Update placeholder dengan info admin
    const placeholder = document.querySelector('#admin-dashboard-page .dashboard-placeholder');
    if (placeholder && currentUser) {
        placeholder.innerHTML = `
            <h2>Admin Dashboard</h2>
            <p>Selamat datang, ${currentUser.name} (${currentUser.role})</p>
            <p>Terakhir login: ${new Date().toLocaleString('id-ID')}</p>
            <div style="margin: 30px 0;">
                <div style="background: rgba(255,255,255,0.2); padding: 20px; border-radius: 12px; margin-bottom: 15px;">
                    <h3>🛠️ Panel Administrasi:</h3>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-top: 20px;">
                        <div style="background: rgba(255,255,255,0.3); padding: 15px; border-radius: 8px;">
                            <h4>👥 Manajemen User</h4>
                            <p>5,240 pengguna</p>
                        </div>
                        <div style="background: rgba(255,255,255,0.3); padding: 15px; border-radius: 8px;">
                            <h4>📚 Manajemen Kursus</h4>
                            <p>42 kursus aktif</p>
                        </div>
                        <div style="background: rgba(255,255,255,0.3); padding: 15px; border-radius: 8px;">
                            <h4>📝 Manajemen Tugas</h4>
                            <p>127 tugas tertunda</p>
                        </div>
                        <div style="background: rgba(255,255,255,0.3); padding: 15px; border-radius: 8px;">
                            <h4>📊 Laporan Sistem</h4>
                            <p>24 laporan bulanan</p>
                        </div>
                    </div>
                </div>
            </div>
            <button class="btn-logout" id="logout-admin">Logout</button>
        `;
        
        // Re-attach event listener
        document.getElementById('logout-admin').addEventListener('click', handleLogout);
    }
}

// ========== FUNGSI NOTIFIKASI ==========
function showNotification(message, type = 'info') {
    const container = document.getElementById('notification-container');
    
    // Buat elemen notifikasi
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <span>${message}</span>
        <button class="notification-close">&times;</button>
    `;
    
    // Tambahkan ke container
    container.appendChild(notification);
    
    // Setup close button
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', function() {
        notification.style.animation = 'slideOutRight 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    });
    
    // Auto remove setelah 5 detik
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

// ========== FUNGSI TAMBAHAN ==========
function simulateLoading(action) {
    showNotification(`Memproses ${action}...`, 'info');
    
    // Return promise untuk simulasi async
    return new Promise(resolve => {
        setTimeout(() => {
            resolve();
        }, 1000);
    });
}

// ========== EXPORT FUNGSI (jika menggunakan modules) ==========
// export {
//     showNotification,
//     handleUserLogin,
//     handleAdminLogin,
//     handleLogout
// };