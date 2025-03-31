/**
 * Main JavaScript file for the User Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Password strength meter
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirm_password');
    const strengthMeter = document.getElementById('password-strength-meter-fill');
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = passwordInput.value;
            const strength = calculatePasswordStrength(password);
            
            if (strengthMeter) {
                updatePasswordStrengthMeter(strength);
            }
            
            // If confirm password field exists and has value, check match
            if (confirmPasswordInput && confirmPasswordInput.value) {
                checkPasswordMatch();
            }
        });
    }
    
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }
    
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Handle "Remember Me" checkbox
    const rememberCheckbox = document.getElementById('remember_me');
    if (rememberCheckbox) {
        // Check if there's a saved preference in localStorage
        const savedPreference = localStorage.getItem('remember_me_preference');
        if (savedPreference === 'true') {
            rememberCheckbox.checked = true;
        }
        
        // Save preference when changed
        rememberCheckbox.addEventListener('change', function() {
            localStorage.setItem('remember_me_preference', this.checked);
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
});

/**
 * Calculate password strength on a scale of 0-100
 * 
 * @param {string} password - The password to evaluate
 * @return {number} - Strength value from 0-100
 */
function calculatePasswordStrength(password) {
    if (!password) {
        return 0;
    }
    
    let strength = 0;
    
    // Length check
    if (password.length >= 8) {
        strength += 25;
    } else {
        strength += (password.length / 8) * 25;
    }
    
    // Check for lowercase letters
    if (/[a-z]/.test(password)) {
        strength += 15;
    }
    
    // Check for uppercase letters
    if (/[A-Z]/.test(password)) {
        strength += 15;
    }
    
    // Check for numbers
    if (/[0-9]/.test(password)) {
        strength += 15;
    }
    
    // Check for special characters
    if (/[^a-zA-Z0-9]/.test(password)) {
        strength += 15;
    }
    
    // Length bonus for passwords longer than 12 characters
    if (password.length > 12) {
        strength += 15;
    }
    
    // Cap at 100
    return Math.min(100, strength);
}

/**
 * Update the password strength meter display
 * 
 * @param {number} strength - Password strength from 0-100
 */
function updatePasswordStrengthMeter(strength) {
    const meterFill = document.getElementById('password-strength-meter-fill');
    if (!meterFill) return;
    
    // Update width
    meterFill.style.width = strength + '%';
    
    // Update color based on strength
    let color = '';
    if (strength < 30) {
        color = 'var(--bs-danger)';
    } else if (strength < 60) {
        color = 'var(--bs-warning)';
    } else if (strength < 80) {
        color = 'var(--bs-info)';
    } else {
        color = 'var(--bs-success)';
    }
    
    meterFill.style.backgroundColor = color;
    
    // Update strength text if it exists
    const strengthText = document.getElementById('password-strength-text');
    if (strengthText) {
        let text = '';
        if (strength < 30) {
            text = 'Weak';
        } else if (strength < 60) {
            text = 'Moderate';
        } else if (strength < 80) {
            text = 'Strong';
        } else {
            text = 'Very Strong';
        }
        
        strengthText.textContent = text;
        strengthText.style.color = color;
    }
}

/**
 * Check if password and confirm password match
 */
function checkPasswordMatch() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const matchMessage = document.getElementById('password-match-message');
    
    if (!password || !confirmPassword || !matchMessage) return;
    
    if (password.value === confirmPassword.value) {
        confirmPassword.setCustomValidity('');
        matchMessage.textContent = 'Passwords match';
        matchMessage.className = 'text-success mt-1';
    } else {
        confirmPassword.setCustomValidity('Passwords do not match');
        matchMessage.textContent = 'Passwords do not match';
        matchMessage.className = 'text-danger mt-1';
    }
}