/**
 * Forum Kopo - Main JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const mobileNavToggle = document.getElementById('mobile-nav-toggle');
    const mainNav = document.getElementById('main-nav');
    
    if (mobileNavToggle && mainNav) {
        mobileNavToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
            const expanded = mainNav.classList.contains('active');
            mobileNavToggle.setAttribute('aria-expanded', expanded);
            
            // Change icon based on state
            const icon = mobileNavToggle.querySelector('i');
            if (expanded) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        });
    }
    
    // Dark mode toggle
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const htmlElement = document.documentElement;
    
    // Check for saved theme preference or respect OS preference
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        htmlElement.classList.add('dark-mode');
        updateDarkModeIcon(true);
    }
    
    // Toggle dark mode on click
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            htmlElement.classList.toggle('dark-mode');
            const isDarkMode = htmlElement.classList.contains('dark-mode');
            localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
            updateDarkModeIcon(isDarkMode);
        });
    }
    
    function updateDarkModeIcon(isDark) {
        if (!darkModeToggle) return;
        
        const icon = darkModeToggle.querySelector('i');
        if (isDark) {
            icon.classList.remove('fa-moon');
            icon.classList.add('fa-sun');
        } else {
            icon.classList.remove('fa-sun');
            icon.classList.add('fa-moon');
        }
    }
    
    // Automatically close notifications
    const notifications = document.querySelectorAll('.notification');
    notifications.forEach(notification => {
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                notification.style.display = 'none';
            }, 300);
        }, 5000);
    });
    
    // Code syntax highlighting for tech content
    document.querySelectorAll('pre code').forEach(block => {
        // Add data-language attribute if not present
        if (!block.getAttribute('data-language')) {
            // Try to guess language from class
            const classes = block.className.split(' ');
            let language = 'code';
            
            for (const cls of classes) {
                if (cls.startsWith('language-')) {
                    language = cls.replace('language-', '');
                    break;
                }
            }
            
            block.setAttribute('data-language', language);
        }
        
        // Create code header
        const language = block.getAttribute('data-language');
        const pre = block.parentElement;
        
        if (pre && pre.tagName === 'PRE' && !pre.querySelector('.code-header')) {
            const header = document.createElement('div');
            header.className = 'code-header';
            
            const languageSpan = document.createElement('span');
            languageSpan.className = 'code-language';
            languageSpan.textContent = language;
            
            const copyButton = document.createElement('button');
            copyButton.className = 'code-copy-btn';
            copyButton.innerHTML = '<i class="far fa-copy"></i> Copier';
            copyButton.addEventListener('click', () => {
                navigator.clipboard.writeText(block.textContent).then(() => {
                    copyButton.innerHTML = '<i class="fas fa-check"></i> Copié!';
                    setTimeout(() => {
                        copyButton.innerHTML = '<i class="far fa-copy"></i> Copier';
                    }, 2000);
                });
            });
            
            header.appendChild(languageSpan);
            header.appendChild(copyButton);
            
            pre.insertBefore(header, block);
            pre.style.paddingTop = '0'; // Remove padding as it's now in the header
        }
    });
    
    // Add image fullscreen preview for article images
    document.querySelectorAll('.article-content img').forEach(image => {
        image.style.cursor = 'pointer';
        image.addEventListener('click', () => {
            const overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(0,0,0,0.9)';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            overlay.style.zIndex = '9999';
            
            const img = document.createElement('img');
            img.src = image.src;
            img.style.maxWidth = '90%';
            img.style.maxHeight = '90%';
            img.style.objectFit = 'contain';
            
            overlay.appendChild(img);
            
            overlay.addEventListener('click', () => {
                overlay.remove();
            });
            
            document.body.appendChild(overlay);
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            let isValid = true;
            
            // Password strength check
            const password = form.querySelector('input[type="password"]');
            if (password && password.getAttribute('data-check-strength') !== null) {
                const value = password.value;
                
                // Simple strength check
                const hasUpper = /[A-Z]/.test(value);
                const hasLower = /[a-z]/.test(value);
                const hasNumber = /[0-9]/.test(value);
                const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(value);
                const isLongEnough = value.length >= 8;
                
                if (!(hasUpper && hasLower && hasNumber && hasSpecial && isLongEnough)) {
                    isValid = false;
                    
                    // Show error message if not already shown
                    let error = password.parentElement.querySelector('.password-error');
                    if (!error) {
                        error = document.createElement('div');
                        error.className = 'form-text password-error';
                        error.style.color = 'var(--accent-red)';
                        error.innerHTML = 'Le mot de passe doit contenir au moins 8 caractères, des majuscules, minuscules, chiffres et caractères spéciaux.';
                        password.parentElement.appendChild(error);
                    }
                } else {
                    // Remove error if present
                    const error = password.parentElement.querySelector('.password-error');
                    if (error) {
                        error.remove();
                    }
                }
            }
            
            // Required fields check
            form.querySelectorAll('[required]').forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    
                    // Add error message
                    let errorMsg = field.parentElement.querySelector('.field-error');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'form-text field-error';
                        errorMsg.style.color = 'var(--accent-red)';
                        errorMsg.textContent = 'Ce champ est requis.';
                        field.parentElement.appendChild(errorMsg);
                    }
                } else {
                    field.classList.remove('is-invalid');
                    const errorMsg = field.parentElement.querySelector('.field-error');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            });
            
            // Email validation
            const emailField = form.querySelector('input[type="email"]');
            if (emailField && emailField.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailField.value)) {
                    isValid = false;
                    emailField.classList.add('is-invalid');
                    
                    // Add error message
                    let errorMsg = emailField.parentElement.querySelector('.field-error');
                    if (!errorMsg) {
                        errorMsg = document.createElement('div');
                        errorMsg.className = 'form-text field-error';
                        errorMsg.style.color = 'var(--accent-red)';
                        errorMsg.textContent = 'Veuillez entrer une adresse email valide.';
                        emailField.parentElement.appendChild(errorMsg);
                    }
                }
            }
            
            if (!isValid) {
                event.preventDefault();
            }
        });
    });
});