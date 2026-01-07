// router.js - Handle navigation and authentication
class AppRouter {
    constructor() {
        this.init();
    }
    
    init() {
        this.protectPages();
        this.setupNavigation();
        this.checkAutoRedirect();
    }
    
    protectPages() {
        const currentPage = window.location.pathname.split('/').pop();
        const userRole = localStorage.getItem('user_role');
        const userData = localStorage.getItem('user_data');
        
        // Pages that require authentication
        const protectedPages = {
            'login.html': 'user',
            'Allpageadmin.html': 'admin'
        };
        
        // Check if current page is protected
        if (protectedPages[currentPage]) {
            const requiredRole = protectedPages[currentPage];
            
            if (!userRole || userRole !== requiredRole || !userData) {
                // Redirect to login page
                if (requiredRole === 'user') {
                    window.location.href = 'login.html';
                } else {
                    window.location.href = 'signup.html';
                }
                return;
            }
        }
        
        // Redirect if already logged in
        if (userRole && userData) {
            if (currentPage === 'adminuser.html' || 
                currentPage === 'login.html' || 
                currentPage === 'Allpaheadmin.html') {
                
                if (userRole === 'user') {
                    window.location.href = 'user-dashboard.html';
                } else if (userRole === 'admin') {
                    window.location.href = 'admin-dashboard.html';
                }
            }
        }
    }
    
    setupNavigation() {
        // Global navigation functions
        window.goToUserLogin = () => {
            window.location.href = 'login-user.html';
        };
        
        window.goToAdminLogin = () => {
            window.location.href = 'login-admin.html';
        };
        
        window.goBackToSelection = () => {
            window.location.href = 'index.html';
        };
        
        window.logout = () => {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                localStorage.removeItem('user_data');
                localStorage.removeItem('user_role');
                window.location.href = 'index.html';
            }
        };
    }
    
    checkAutoRedirect() {
        // Check URL parameters for auto redirect
        const urlParams = new URLSearchParams(window.location.search);
        const redirectTo = urlParams.get('redirect');
        
        if (redirectTo) {
            setTimeout(() => {
                window.location.href = redirectTo;
            }, 100);
        }
    }
    
    // Helper function to get user data
    getUserData() {
        const data = localStorage.getItem('user_data');
        return data ? JSON.parse(data) : null;
    }
    
    // Helper function to get user role
    getUserRole() {
        return localStorage.getItem('user_role');
    }
    
    // Function to simulate login (for testing)
    simulateLogin(role, data) {
        localStorage.setItem('user_role', role);
        localStorage.setItem('user_data', JSON.stringify(data));
        
        if (role === 'user') {
            window.location.href = 'user-dashboard.html';
        } else {
            window.location.href = 'admin-dashboard.html';
        }
    }
}

// Initialize router
const appRouter = new AppRouter();