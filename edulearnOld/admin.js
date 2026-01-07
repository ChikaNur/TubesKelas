// DOM Elements
const loginPage = document.getElementById('login-page');
const dashboardPage = document.getElementById('dashboard-page');
const loginForm = document.getElementById('admin-login-form');
const logoutBtn = document.getElementById('logout-btn');
const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.getElementById('admin-sidebar');
const navItems = document.querySelectorAll('.nav-item');
const pageContents = document.querySelectorAll('.page-content');
const passwordToggles = document.querySelectorAll('.password-toggle');
const switchToUserBtn = document.getElementById('switch-to-user');

// Toggle password visibility
passwordToggles.forEach(toggle => {
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

// Login Form Submission
if (loginForm) {
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const email = document.getElementById('admin-email').value;
        const password = document.getElementById('admin-password').value;
        
        // Simulate login validation
        if (email && password) {
            // In a real app, you would send this to a server
            console.log('Admin login attempt:', { email, password });
            
            // Simulate successful login
            showNotification('Login berhasil! Mengarahkan ke dashboard...', 'success');
            
            // Switch to dashboard after delay
            setTimeout(() => {
                loginPage.classList.remove('active');
                dashboardPage.classList.add('active');
                showNotification('Selamat datang di Dashboard Admin EduLearn!', 'success');
            }, 1500);
        } else {
            showNotification('Harap isi email dan password!', 'error');
        }
    });
}

// Logout Functionality
if (logoutBtn) {
    logoutBtn.addEventListener('click', function() {
        if (confirm('Apakah Anda yakin ingin logout?')) {
            dashboardPage.classList.remove('active');
            loginPage.classList.add('active');
            
            // Reset login form
            if (loginForm) {
                loginForm.reset();
            }
            
            showNotification('Anda telah logout dari sistem admin.', 'info');
        }
    });
}

// Sidebar Toggle
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
    });
}

// Navigation between pages
navItems.forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Get the page to show
        const pageName = this.getAttribute('data-page');
        
        // Update active nav item
        navItems.forEach(nav => nav.classList.remove('active'));
        this.classList.add('active');
        
        // Show the selected page
        pageContents.forEach(content => {
            content.classList.remove('active');
            if (content.id === `${pageName}-content`) {
                content.classList.add('active');
            }
        });
        
        // Close sidebar on mobile
        if (window.innerWidth <= 992) {
            sidebar.classList.remove('active');
        }
    });
});

// Switch to User Login
if (switchToUserBtn) {
    switchToUserBtn.addEventListener('click', function(e) {
        e.preventDefault();
        showNotification('Mengalihkan ke login user...', 'info');
        // In a real app, this would redirect to user login page
    });
}

// Notification System
function showNotification(message, type = 'info') {
    // Check if notification container exists
    let notificationContainer = document.querySelector('.notification-container');
    
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.className = 'notification-container';
        notificationContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
        `;
        document.body.appendChild(notificationContainer);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.style.cssText = `
        background-color: ${getNotificationColor(type)};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        max-width: 400px;
        animation: slideIn 0.3s ease;
    `;
    
    // Add icon based on type
    let icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    if (type === 'error') icon = 'exclamation-circle';
    if (type === 'warning') icon = 'exclamation-triangle';
    
    notification.innerHTML = `
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-${icon}" style="font-size: 18px;"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" style="background: none; border: none; color: white; cursor: pointer; font-size: 16px;">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    notificationContainer.appendChild(notification);
    
    // Add close functionality
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    });
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
    
    // Add CSS for animations
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
}

function getNotificationColor(type) {
    switch(type) {
        case 'success': return '#28a745';
        case 'error': return '#dc3545';
        case 'warning': return '#ffc107';
        default: return '#17a2b8';
    }
}

// Mobile sidebar toggle
if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function() {
        if (window.innerWidth <= 992) {
            sidebar.classList.toggle('active');
        } else {
            sidebar.classList.toggle('collapsed');
        }
    });
}

// Close sidebar when clicking outside on mobile
document.addEventListener('click', function(e) {
    if (window.innerWidth <= 992 && sidebar.classList.contains('active')) {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
            sidebar.classList.remove('active');
        }
    }
});

// Sample data for user management table
document.addEventListener('DOMContentLoaded', function() {
    // Add more sample users to the table
    const usersTable = document.querySelector('.data-table tbody');
    
    if (usersTable) {
        const sampleUsers = [
            {
                id: '#002',
                name: 'Siti Nurhaliza',
                email: 'siti@email.ac.id',
                role: 'Dosen',
                status: 'Aktif',
                registered: '15 Jan 2024'
            },
            {
                id: '#003',
                name: 'Ahmad Darmawan',
                email: 'ahmad@email.ac.id',
                role: 'Mahasiswa',
                status: 'Aktif',
                registered: '20 Jan 2024'
            },
            {
                id: '#004',
                name: 'Rina Melati',
                email: 'rina@email.ac.id',
                role: 'Dosen',
                status: 'Tidak Aktif',
                registered: '5 Jan 2024'
            },
            {
                id: '#005',
                name: 'Budi Santoso',
                email: 'budi@email.ac.id',
                role: 'Mahasiswa',
                status: 'Aktif',
                registered: '25 Jan 2024'
            }
        ];
        
        sampleUsers.forEach(user => {
            const row = document.createElement('tr');
            
            // Determine badge classes
            let roleClass = 'role-student';
            if (user.role === 'Dosen') roleClass = 'role-instructor';
            
            let statusClass = 'status-active';
            if (user.status === 'Tidak Aktif') statusClass = 'status-inactive';
            
            row.innerHTML = `
                <td>${user.id}</td>
                <td>
                    <div class="user-cell">
                        <img src="https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=2d6cdf&color=fff" alt="${user.name}">
                        <span>${user.name}</span>
                    </div>
                </td>
                <td>${user.email}</td>
                <td><span class="badge ${roleClass}">${user.role}</span></td>
                <td><span class="badge ${statusClass}">${user.status}</span></td>
                <td>${user.registered}</td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon edit"><i class="fas fa-edit"></i></button>
                        <button class="btn-icon delete"><i class="fas fa-trash"></i></button>
                        <button class="btn-icon view"><i class="fas fa-eye"></i></button>
                    </div>
                </td>
            `;
            
            usersTable.appendChild(row);
        });
        
        // Add event listeners to action buttons
        const actionButtons = document.querySelectorAll('.action-buttons .btn-icon');
        actionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const userName = row.querySelector('.user-cell span').textContent;
                
                if (this.classList.contains('edit')) {
                    showNotification(`Edit user: ${userName}`, 'info');
                } else if (this.classList.contains('delete')) {
                    if (confirm(`Hapus user ${userName}?`)) {
                        row.remove();
                        showNotification(`User ${userName} telah dihapus`, 'success');
                    }
                } else if (this.classList.contains('view')) {
                    showNotification(`Lihat detail user: ${userName}`, 'info');
                }
            });
        });
    }
    
    // Initialize with dashboard active
    if (dashboardPage.classList.contains('active')) {
        document.querySelector('.nav-item[data-page="dashboard"]').classList.add('active');
        document.getElementById('dashboard-content').classList.add('active');
    }
});