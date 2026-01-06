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
    
    // Validasi
    if (!fullName || !nim || !email || !password) {
        alert('Semua field harus diisi!');
        return;
    }
    
    // Simpan data user (dalam aplikasi nyata, ini akan dikirim ke server)
    const userData = {
        fullName: fullName,
        nim: nim,
        email: email,
        password: password
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
    
    // Jika belum login dan berada di halaman yang membutuhkan login
    if (!isLoggedIn && 
        !window.location.pathname.includes('index.html') && 
        !window.location.pathname.includes('register.html')) {
        window.location.href = 'index.html';
    }
    
    // Jika sudah login dan berada di halaman login/register
    if (isLoggedIn && 
        (window.location.pathname.includes('index.html') || 
         window.location.pathname.includes('register.html'))) {
        window.location.href = 'dashboard.html';
    }
}

// Fungsi untuk logout
function handleLogout() {
    localStorage.removeItem('isLoggedIn');
    localStorage.removeItem('userEmail');
    window.location.href = 'index.html';
}

// Fungsi untuk load user data di dashboard
function loadUserData() {
    const userEmail = localStorage.getItem('userEmail');
    const userData = JSON.parse(localStorage.getItem('userData') || '{}');
    
    // Update welcome message
    const welcomeElement = document.getElementById('welcome-message');
    if (welcomeElement && userData.fullName) {
        welcomeElement.textContent = `Halo, ${userData.fullName}`;
    }
    
    // Update profile info
    const profileName = document.getElementById('profile-name');
    const profileEmail = document.getElementById('profile-email');
    
    if (profileName && userData.fullName) {
        profileName.textContent = userData.fullName;
    }
    
    if (profileEmail && userEmail) {
        profileEmail.textContent = userEmail;
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
    
    // Load user data di dashboard/profile
    if (window.location.pathname.includes('dashboard.html') || 
        window.location.pathname.includes('profile.html')) {
        loadUserData();
    }
});