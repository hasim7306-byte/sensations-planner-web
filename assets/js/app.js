// Sensations To Go Planner Enhanced v2.0 JavaScript
document.addEventListener("DOMContentLoaded", function() {
    console.log("🚀 Sensations To Go Planner Enhanced v2.0 loaded successfully!");
    
    // Initialize app
    initializeApp();
    
    // Add smooth transitions
    document.body.style.opacity = "0";
    setTimeout(() => {
        document.body.style.transition = "opacity 0.3s ease";
        document.body.style.opacity = "1";
    }, 100);
});

function initializeApp() {
    // Enhanced form validation
    initializeFormValidation();
    
    // Initialize mobile menu
    initializeMobileMenu();
    
    // Initialize tooltips and interactions
    initializeInteractions();
    
    // Initialize real-time features
    initializeRealTimeFeatures();
}

function initializeFormValidation() {
    const forms = document.querySelectorAll("form");
    forms.forEach(form => {
        form.addEventListener("submit", function(e) {
            const requiredFields = form.querySelectorAll("[required]");
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = "#ef4444";
                    field.style.boxShadow = "0 0 0 3px rgba(239, 68, 68, 0.1)";
                    
                    // Add shake animation
                    field.classList.add("shake");
                    setTimeout(() => field.classList.remove("shake"), 500);
                } else {
                    field.style.borderColor = "#e5e7eb";
                    field.style.boxShadow = "none";
                }
            });
            
            // Password validation
            const passwordFields = form.querySelectorAll('input[type="password"]');
            passwordFields.forEach(field => {
                if (field.value && field.value.length < 6) {
                    isValid = false;
                    field.style.borderColor = "#ef4444";
                    showToast("Wachtwoord moet minimaal 6 karakters zijn", "error");
                }
            });
            
            // Email validation
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (field.value && !isValidEmail(field.value)) {
                    isValid = false;
                    field.style.borderColor = "#ef4444";
                    showToast("Voer een geldig e-mailadres in", "error");
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showToast("Vul alle verplichte velden correct in", "error");
            }
        });
        
        // Real-time validation
        const inputs = form.querySelectorAll("input, select, textarea");
        inputs.forEach(input => {
            input.addEventListener("blur", function() {
                validateField(this);
            });
            
            input.addEventListener("input", function() {
                // Clear error state on input
                if (this.style.borderColor === "rgb(239, 68, 68)") {
                    this.style.borderColor = "#e5e7eb";
                    this.style.boxShadow = "none";
                }
            });
        });
    });
}

function validateField(field) {
    if (field.hasAttribute("required") && !field.value.trim()) {
        field.style.borderColor = "#ef4444";
        return false;
    }
    
    if (field.type === "email" && field.value && !isValidEmail(field.value)) {
        field.style.borderColor = "#ef4444";
        return false;
    }
    
    if (field.type === "password" && field.value && field.value.length < 6) {
        field.style.borderColor = "#ef4444";
        return false;
    }
    
    field.style.borderColor = "#e5e7eb";
    return true;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function initializeMobileMenu() {
    // Mobile menu functionality is handled by toggleMobileMenu function
    // This function can be extended for more mobile-specific features
}

function toggleMobileMenu() {
    const navMenu = document.getElementById('navMenu');
    const mobileToggle = document.querySelector('.mobile-toggle');
    
    if (navMenu) {
        navMenu.classList.toggle('active');
        mobileToggle.classList.toggle('active');
    }
}

function initializeInteractions() {
    // Add hover effects and interactions
    const cards = document.querySelectorAll('.stat-card, .action-card, .management-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
    
    // Enhanced button interactions
    const buttons = document.querySelectorAll('.btn, .action-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Add ripple effect
            createRipple(e, this);
        });
    });
}

function createRipple(event, element) {
    const ripple = document.createElement('span');
    const rect = element.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = event.clientX - rect.left - size / 2;
    const y = event.clientY - rect.top - size / 2;
    
    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple');
    
    // Add ripple styles
    ripple.style.position = 'absolute';
    ripple.style.borderRadius = '50%';
    ripple.style.background = 'rgba(255, 255, 255, 0.5)';
    ripple.style.transform = 'scale(0)';
    ripple.style.animation = 'ripple 0.6s ease-out';
    ripple.style.pointerEvents = 'none';
    
    element.style.position = 'relative';
    element.style.overflow = 'hidden';
    element.appendChild(ripple);
    
    setTimeout(() => {
        ripple.remove();
    }, 600);
}

function initializeRealTimeFeatures() {
    // Update time displays
    updateTimeDisplays();
    setInterval(updateTimeDisplays, 1000);
    
    // Auto-refresh certain data
    if (window.location.search.includes('page=dashboard')) {
        // Refresh dashboard stats every 30 seconds
        setInterval(() => {
            refreshDashboardStats();
        }, 30000);
    }
}

function updateTimeDisplays() {
    const timeElement = document.getElementById('currentTime');
    const dateElement = document.getElementById('currentDate');
    
    if (timeElement && dateElement) {
        const now = new Date();
        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        
        timeElement.textContent = now.toLocaleTimeString('nl-NL', timeOptions);
        dateElement.textContent = now.toLocaleDateString('nl-NL', dateOptions);
    }
}

function refreshDashboardStats() {
    // This would normally make an AJAX call to refresh stats
    // For now, we'll just add a subtle animation to indicate refresh
    const statCards = document.querySelectorAll('.stat-card');
    statCards.forEach(card => {
        card.style.opacity = '0.8';
        setTimeout(() => {
            card.style.opacity = '1';
        }, 200);
    });
}

// Enhanced Password Modal Functions
function showPasswordModal(userId, userName) {
    const modal = document.getElementById('passwordModal');
    const userIdField = document.getElementById('modal_user_id');
    const userNameField = document.getElementById('modal_user_name');
    const passwordField = modal.querySelector('input[name="new_password"]');
    
    if (modal && userIdField && userNameField) {
        userIdField.value = userId;
        userNameField.textContent = userName;
        passwordField.value = '';
        modal.style.display = 'block';
        
        // Focus on password field
        setTimeout(() => passwordField.focus(), 100);
        
        // Add modal animation
        const modalContent = modal.querySelector('.modal-content');
        modalContent.style.transform = 'translateY(-50px)';
        modalContent.style.opacity = '0';
        setTimeout(() => {
            modalContent.style.transition = 'all 0.3s ease';
            modalContent.style.transform = 'translateY(0)';
            modalContent.style.opacity = '1';
        }, 10);
    }
}

function closePasswordModal() {
    const modal = document.getElementById('passwordModal');
    if (modal) {
        const modalContent = modal.querySelector('.modal-content');
        modalContent.style.transform = 'translateY(-50px)';
        modalContent.style.opacity = '0';
        
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

function setPassword(password) {
    const passwordField = document.querySelector('input[name="new_password"]');
    if (passwordField) {
        passwordField.value = password;
        passwordField.focus();
        
        // Add highlight animation
        passwordField.style.background = 'rgba(16, 185, 129, 0.1)';
        setTimeout(() => {
            passwordField.style.background = '';
        }, 1000);
    }
}

// Toast Notification System
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-icon">${getToastIcon(type)}</span>
            <span class="toast-message">${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
    `;
    
    // Add toast styles
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        z-index: 3000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        max-width: 500px;
        border-left: 5px solid ${getToastColor(type)};
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, 5000);
}

function getToastIcon(type) {
    const icons = {
        success: '✅',
        error: '❌',
        warning: '⚠️',
        info: 'ℹ️'
    };
    return icons[type] || icons.info;
}

function getToastColor(type) {
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    return colors[type] || colors.info;
}

// Enhanced Table Interactions
function initializeTableFeatures() {
    const tables = document.querySelectorAll('.users-table');
    tables.forEach(table => {
        // Add row hover effects
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.background = 'rgba(220, 38, 38, 0.05)';
                this.style.transform = 'scale(1.01)';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.background = '';
                this.style.transform = 'scale(1)';
            });
        });
    });
}

// Enhanced Form Features
function generateStrongPassword() {
    const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    let password = '';
    for (let i = 0; i < 12; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return password;
}

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K to focus search (if exists)
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchField = document.querySelector('input[type="search"], input[placeholder*="zoek"]');
        if (searchField) {
            searchField.focus();
        }
    }
    
    // Escape to close modals
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal[style*="block"]');
        modals.forEach(modal => {
            if (modal.id === 'passwordModal') {
                closePasswordModal();
            } else {
                modal.style.display = 'none';
            }
        });
    }
});

// Add CSS animations
const animationStyles = document.createElement('style');
animationStyles.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    @keyframes slideOutRight {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
    
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
    }
    
    @keyframes ripple {
        to { transform: scale(4); opacity: 0; }
    }
    
    .shake {
        animation: shake 0.5s ease-in-out;
    }
    
    .toast-content {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .toast-close {
        background: none;
        border: none;
        font-size: 1.25rem;
        cursor: pointer;
        color: #6b7280;
        padding: 0;
        margin-left: 1rem;
    }
    
    .toast-close:hover {
        color: #374151;
    }
`;
document.head.appendChild(animationStyles);

// Initialize table features when DOM is ready
document.addEventListener('DOMContentLoaded', initializeTableFeatures);

// Export functions for global use
window.toggleMobileMenu = toggleMobileMenu;
window.showPasswordModal = showPasswordModal;
window.closePasswordModal = closePasswordModal;
window.setPassword = setPassword;
window.showToast = showToast;