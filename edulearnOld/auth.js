// Base URL untuk API
const API_BASE_URL = 'http://localhost/edulearn-project/backend/api';

// Fungsi untuk login user/mahasiswa
async function loginUser(email, password) {
    try {
        const response = await fetch(`${API_BASE_URL}/auth.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                password: password,
                role: 'user'
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Simpan data user di localStorage
            localStorage.setItem('user_token', data.data.token);
            localStorage.setItem('user_data', JSON.stringify(data.data.user));
            localStorage.setItem('user_role', data.data.user.role);
            
            return {
                success: true,
                data: data.data
            };
        } else {
            return {
                success: false,
                message: data.message
            };
        }
    } catch (error) {
        console.error('Login error:', error);
        return {
            success: false,
            message: 'Terjadi kesalahan koneksi'
        };
    }
}

// Fungsi untuk login admin
async function loginAdmin(email, password) {
    try {
        const response = await fetch(`${API_BASE_URL}/auth.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                email: email,
                password: password,
                role: 'admin'
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Simpan data admin di localStorage
            localStorage.setItem('admin_token', data.data.token);
            localStorage.setItem('admin_data', JSON.stringify(data.data.admin));
            localStorage.setItem('user_role', 'admin'); // Simpan sebagai admin
            
            return {
                success: true,
                data: data.data
            };
        } else {
            return {
                success: false,
                message: data.message
            };
        }
    } catch (error) {
        console.error('Admin login error:', error);
        return {
            success: false,
            message: 'Terjadi kesalahan koneksi'
        };
    }
}

// Fungsi untuk check session
async function checkSession() {
    const token = localStorage.getItem('user_token') || localStorage.getItem('admin_token');
    const role = localStorage.getItem('user_role');
    
    if (!token) {
        return { loggedIn: false };
    }
    
    try {
        const response = await fetch(`${API_BASE_URL}/auth.php?token=${token}`);
        const data = await response.json();
        
        if (data.success) {
            return {
                loggedIn: true,
                role: role,
                data: role === 'admin' 
                    ? JSON.parse(localStorage.getItem('admin_data'))
                    : JSON.parse(localStorage.getItem('user_data'))
            };
        } else {
            return { loggedIn: false };
        }
    } catch (error) {
        console.error('Session check error:', error);
        return { loggedIn: false };
    }
}

// Fungsi untuk logout
function logout() {
    // Hapus semua data dari localStorage
    localStorage.removeItem('user_token');
    localStorage.removeItem('admin_token');
    localStorage.removeItem('user_data');
    localStorage.removeItem('admin_data');
    localStorage.removeItem('user_role');
    
    // Redirect ke halaman utama
    window.location.href = '../index.html';
}

// Fungsi untuk get data dengan token
async function fetchWithAuth(url, options = {}) {
    const token = localStorage.getItem('user_token') || localStorage.getItem('admin_token');
    
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        ...options
    };
    
    const response = await fetch(url, defaultOptions);
    
    if (response.status === 401) {
        // Token expired, redirect to login
        logout();
        throw new Error('Session expired');
    }
    
    return response;
}

// Export fungsi
window.auth = {
    loginUser,
    loginAdmin,
    checkSession,
    logout,
    fetchWithAuth
};