// navigation.js
// Fungsi untuk handle login
function handleLogin(event) {
    event.preventDefault();
    
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    
    // Validasi sederhana
    if (!email || !password) {
        alert('Email dan password harus diisi!');
        return;
    }
    
    // Simpan status login di localStorage
    localStorage.setItem('isLoggedIn', 'true');
    localStorage.setItem('userEmail', email);
    
    // Redirect ke dashboard
    window.location.href = 'dashboard.html';
}

// Fungsi untuk handle register
function handleRegister(event) {
    event.preventDefault();
    
    const fullName = document.getElementById('fullName').value;
    const nim = document.getElementById('nim').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    // Validasi
    if (!fullName || !nim || !email || !password) {
        alert('Semua field harus diisi!');
        return;
    }
    
    // Validasi password match
    if (password !== confirmPassword) {
        alert('Password dan konfirmasi password tidak cocok!');
        return;
    }
    
    // Simpan data user
    const userData = {
        fullName: fullName,
        nim: nim,
        email: email
    };
    
    localStorage.setItem('userData', JSON.stringify(userData));
    localStorage.setItem('isLoggedIn', 'true');
    localStorage.setItem('userEmail', email);
    
    // Redirect ke dashboard
    window.location.href = 'dashboard.html';
}

// Fungsi untuk check login status
function checkLoginStatus() {
    const isLoggedIn = localStorage.getItem('isLoggedIn');
    const currentPage = window.location.pathname.split('/').pop();
    
    // Jika belum login dan berada di halaman yang membutuhkan login
    if (!isLoggedIn && 
        currentPage !== 'index.html' && 
        currentPage !== 'register.html') {
        window.location.href = 'index.html';
    }
    
    // Jika sudah login dan berada di halaman login/register
    if (isLoggedIn && 
        (currentPage === 'index.html' || 
         currentPage === 'register.html')) {
        window.location.href = 'dashboard.html';
    }
}

// Fungsi untuk logout
function handleLogout() {
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userData');
    window.location.href = 'index.html';
}

// Fungsi untuk load user data
function loadUserData() {
    const userEmail = localStorage.getItem('userEmail');
    const userData = JSON.parse(localStorage.getItem('userData') || '{}');
    
    // Update welcome message di dashboard
    const welcomeElement = document.getElementById('welcome-message');
    if (welcomeElement && userData.fullName) {
        welcomeElement.textContent = `Halo, ${userData.fullName}`;
    }
    
    // Update profile info
    const profileName = document.getElementById('profile-name');
    const profileEmail = document.getElementById('profile-email');
    const profileNIM = document.getElementById('profile-nim');
    
    if (profileName && userData.fullName) {
        profileName.textContent = userData.fullName;
    }
    
    if (profileEmail && userEmail) {
        profileEmail.textContent = userEmail;
    }
    
    if (profileNIM && userData.nim) {
        profileNIM.textContent = userData.nim;
    }
}

// Fungsi untuk toggle password visibility
function setupPasswordToggle() {
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '👁️‍🗨️';
        });
    });
}

// Fungsi untuk validasi password match
function setupPasswordValidation() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');
    
    if (password && confirmPassword) {
        function validatePassword() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Password tidak cocok');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
        
        password.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);
    }
}

// Event Listeners saat DOM siap
document.addEventListener('DOMContentLoaded', function() {
    // Check login status
    checkLoginStatus();
    
    // Setup login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    // Setup register form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
    
    // Setup logout button
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', handleLogout);
    }
    
    // Setup password toggles
    setupPasswordToggle();
    
    // Setup password validation
    setupPasswordValidation();
    
    // Load user data di dashboard/profile
    loadUserData();
});