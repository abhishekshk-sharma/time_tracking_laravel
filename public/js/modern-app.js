/**
 * Modern Time Tracking System - Enhanced JavaScript
 * Professional interactions and animations
 */

class TimeTrackingApp {
    constructor() {
        this.init();
    }

    init() {
        this.setupGlobalEventListeners();
        this.setupAnimations();
        this.setupNotifications();
        this.setupLoadingStates();
        this.setupFormEnhancements();
        this.setupTableEnhancements();
        this.setupModalEnhancements();
    }

    // Global Event Listeners
    setupGlobalEventListeners() {
        // Enhanced AJAX setup
        $(document).ajaxStart(() => this.showGlobalLoading());
        $(document).ajaxStop(() => this.hideGlobalLoading());
        
        // Error handling
        $(document).ajaxError((event, xhr, settings, error) => {
            this.handleAjaxError(xhr, error);
        });

        // Smooth scrolling for anchor links
        this.setupSmoothScrolling();
        
        // Keyboard shortcuts
        this.setupKeyboardShortcuts();
        
        // Auto-save functionality
        this.setupAutoSave();
    }

    // Animation System
    setupAnimations() {
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        // Observe all cards and forms
        document.querySelectorAll('.card, .form-enhanced, .summary-card').forEach(el => {
            observer.observe(el);
        });

        // Stagger animations for lists
        this.staggerAnimations('.nav-menu .nav-item', 100);
        this.staggerAnimations('.application-cards .app-card', 150);
    }

    staggerAnimations(selector, delay) {
        const elements = document.querySelectorAll(selector);
        elements.forEach((el, index) => {
            el.style.animationDelay = `${index * delay}ms`;
            el.classList.add('stagger-animation');
        });
    }

    // Enhanced Notification System
    setupNotifications() {
        this.notificationQueue = [];
        this.maxNotifications = 3;
    }

    showNotification(message, type = 'success', duration = 4000) {
        const notification = this.createNotification(message, type);
        
        // Manage queue
        if (this.notificationQueue.length >= this.maxNotifications) {
            this.removeNotification(this.notificationQueue[0]);
        }
        
        this.notificationQueue.push(notification);
        document.body.appendChild(notification);
        
        // Animate in
        requestAnimationFrame(() => {
            notification.classList.add('show');
        });
        
        // Auto remove
        setTimeout(() => {
            this.removeNotification(notification);
        }, duration);
        
        // Click to dismiss
        notification.addEventListener('click', () => {
            this.removeNotification(notification);
        });
    }

    createNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification-toast-enhanced ${type}`;
        
        const icon = this.getNotificationIcon(type);
        notification.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        return notification;
    }

    getNotificationIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }

    removeNotification(notification) {
        if (notification && notification.parentElement) {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
                this.notificationQueue = this.notificationQueue.filter(n => n !== notification);
            }, 300);
        }
    }

    // Loading States
    setupLoadingStates() {
        this.createGlobalLoader();
    }

    createGlobalLoader() {
        const loader = document.createElement('div');
        loader.id = 'globalLoader';
        loader.className = 'loading-overlay-enhanced';
        loader.innerHTML = '<div class="loading-spinner-enhanced"></div>';
        document.body.appendChild(loader);
    }

    showGlobalLoading() {
        const loader = document.getElementById('globalLoader');
        if (loader) {
            loader.classList.add('show');
        }
    }

    hideGlobalLoading() {
        const loader = document.getElementById('globalLoader');
        if (loader) {
            loader.classList.remove('show');
        }
    }

    showButtonLoading(button, text = 'Loading...') {
        const $btn = $(button);
        if (!$btn.data('original-html')) {
            $btn.data('original-html', $btn.html());
        }
        
        $btn.prop('disabled', true)
            .html(`<i class="fas fa-spinner fa-spin"></i> ${text}`)
            .addClass('loading');
    }

    hideButtonLoading(button) {
        const $btn = $(button);
        const originalHtml = $btn.data('original-html');
        
        if (originalHtml) {
            $btn.prop('disabled', false)
                .html(originalHtml)
                .removeClass('loading');
        }
    }

    // Form Enhancements
    setupFormEnhancements() {
        // Real-time validation
        this.setupRealTimeValidation();
        
        // Auto-resize textareas
        this.setupAutoResizeTextareas();
        
        // Enhanced file uploads
        this.setupFileUploadEnhancements();
        
        // Form progress indicators
        this.setupFormProgress();
    }

    setupRealTimeValidation() {
        document.querySelectorAll('input[required], select[required], textarea[required]').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => this.clearFieldError(field));
        });
    }

    validateField(field) {
        const isValid = field.checkValidity();
        const errorElement = field.parentElement.querySelector('.field-error');
        
        if (!isValid) {
            field.classList.add('error');
            if (!errorElement) {
                const error = document.createElement('div');
                error.className = 'field-error';
                error.textContent = field.validationMessage;
                field.parentElement.appendChild(error);
            }
        } else {
            this.clearFieldError(field);
        }
        
        return isValid;
    }

    clearFieldError(field) {
        field.classList.remove('error');
        const errorElement = field.parentElement.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    setupAutoResizeTextareas() {
        document.querySelectorAll('textarea').forEach(textarea => {
            textarea.addEventListener('input', () => {
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            });
        });
    }

    setupFileUploadEnhancements() {
        document.querySelectorAll('input[type="file"]').forEach(input => {
            const wrapper = document.createElement('div');
            wrapper.className = 'file-upload-wrapper';
            
            const label = document.createElement('label');
            label.className = 'file-upload-label';
            label.innerHTML = `
                <i class="fas fa-cloud-upload-alt"></i>
                <span>Choose file or drag here</span>
            `;
            
            input.parentElement.insertBefore(wrapper, input);
            wrapper.appendChild(label);
            wrapper.appendChild(input);
            
            // Drag and drop
            wrapper.addEventListener('dragover', (e) => {
                e.preventDefault();
                wrapper.classList.add('dragover');
            });
            
            wrapper.addEventListener('dragleave', () => {
                wrapper.classList.remove('dragover');
            });
            
            wrapper.addEventListener('drop', (e) => {
                e.preventDefault();
                wrapper.classList.remove('dragover');
                input.files = e.dataTransfer.files;
                this.updateFileLabel(input, label);
            });
            
            input.addEventListener('change', () => {
                this.updateFileLabel(input, label);
            });
        });
    }

    updateFileLabel(input, label) {
        const fileName = input.files[0]?.name || 'Choose file or drag here';
        label.querySelector('span').textContent = fileName;
    }

    setupFormProgress() {
        document.querySelectorAll('form').forEach(form => {
            const fields = form.querySelectorAll('input[required], select[required], textarea[required]');
            if (fields.length > 0) {
                const progress = this.createProgressBar();
                form.insertBefore(progress, form.firstChild);
                
                fields.forEach(field => {
                    field.addEventListener('input', () => this.updateFormProgress(form, fields, progress));
                });
            }
        });
    }

    createProgressBar() {
        const container = document.createElement('div');
        container.className = 'form-progress';
        container.innerHTML = `
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <span class="progress-text">0% Complete</span>
        `;
        return container;
    }

    updateFormProgress(form, fields, progressContainer) {
        const filledFields = Array.from(fields).filter(field => field.value.trim() !== '').length;
        const percentage = Math.round((filledFields / fields.length) * 100);
        
        const progressFill = progressContainer.querySelector('.progress-fill');
        const progressText = progressContainer.querySelector('.progress-text');
        
        progressFill.style.width = `${percentage}%`;
        progressText.textContent = `${percentage}% Complete`;
        
        if (percentage === 100) {
            progressContainer.classList.add('complete');
        } else {
            progressContainer.classList.remove('complete');
        }
    }

    // Table Enhancements
    setupTableEnhancements() {
        document.querySelectorAll('table').forEach(table => {
            this.makeTableResponsive(table);
            this.addTableSorting(table);
            this.addTableFiltering(table);
        });
    }

    makeTableResponsive(table) {
        const wrapper = document.createElement('div');
        wrapper.className = 'table-responsive';
        table.parentElement.insertBefore(wrapper, table);
        wrapper.appendChild(table);
    }

    addTableSorting(table) {
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            if (header.textContent.trim()) {
                header.classList.add('sortable');
                header.addEventListener('click', () => this.sortTable(table, index));
            }
        });
    }

    sortTable(table, columnIndex) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const isAscending = !table.dataset.sortAsc || table.dataset.sortAsc === 'false';
        
        rows.sort((a, b) => {
            const aText = a.cells[columnIndex].textContent.trim();
            const bText = b.cells[columnIndex].textContent.trim();
            
            if (isAscending) {
                return aText.localeCompare(bText, undefined, { numeric: true });
            } else {
                return bText.localeCompare(aText, undefined, { numeric: true });
            }
        });
        
        rows.forEach(row => tbody.appendChild(row));
        table.dataset.sortAsc = isAscending.toString();
        
        // Update header indicators
        table.querySelectorAll('th').forEach(th => th.classList.remove('sort-asc', 'sort-desc'));
        table.querySelectorAll('th')[columnIndex].classList.add(isAscending ? 'sort-asc' : 'sort-desc');
    }

    addTableFiltering(table) {
        const wrapper = table.closest('.table-responsive') || table.parentElement;
        const filterInput = document.createElement('input');
        filterInput.type = 'text';
        filterInput.placeholder = 'Filter table...';
        filterInput.className = 'table-filter';
        
        wrapper.insertBefore(filterInput, table);
        
        filterInput.addEventListener('input', (e) => {
            this.filterTable(table, e.target.value);
        });
    }

    filterTable(table, searchTerm) {
        const rows = table.querySelectorAll('tbody tr');
        const term = searchTerm.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    }

    // Modal Enhancements
    setupModalEnhancements() {
        // Auto-focus first input in modals
        document.addEventListener('show.modal', (e) => {
            const modal = e.target;
            const firstInput = modal.querySelector('input, select, textarea');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        });
        
        // Close modal on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const openModal = document.querySelector('.modal[style*="display: flex"], .modal.show');
                if (openModal) {
                    this.closeModal(openModal);
                }
            }
        });
        
        // Close modal on backdrop click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal(modal);
                }
            });
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('show');
            document.body.classList.add('modal-open');
            
            // Dispatch custom event
            modal.dispatchEvent(new CustomEvent('show.modal'));
        }
    }

    closeModal(modal) {
        if (typeof modal === 'string') {
            modal = document.getElementById(modal);
        }
        
        if (modal) {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
            }, 300);
            
            // Dispatch custom event
            modal.dispatchEvent(new CustomEvent('hide.modal'));
        }
    }

    // Utility Functions
    setupSmoothScrolling() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const target = document.querySelector(anchor.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + K for search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('input[type="search"], .table-filter');
                if (searchInput) {
                    searchInput.focus();
                }
            }
            
            // Ctrl/Cmd + Enter to submit forms
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                const activeForm = document.activeElement.closest('form');
                if (activeForm) {
                    const submitBtn = activeForm.querySelector('button[type="submit"], input[type="submit"]');
                    if (submitBtn) {
                        submitBtn.click();
                    }
                }
            }
        });
    }

    setupAutoSave() {
        let autoSaveTimeout;
        
        document.querySelectorAll('form[data-autosave]').forEach(form => {
            const inputs = form.querySelectorAll('input, select, textarea');
            
            inputs.forEach(input => {
                input.addEventListener('input', () => {
                    clearTimeout(autoSaveTimeout);
                    autoSaveTimeout = setTimeout(() => {
                        this.autoSaveForm(form);
                    }, 2000);
                });
            });
        });
    }

    autoSaveForm(form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        
        // Save to localStorage
        localStorage.setItem(`autosave_${form.id}`, JSON.stringify(data));
        
        // Show subtle notification
        this.showNotification('Draft saved', 'info', 2000);
    }

    restoreAutoSave(formId) {
        const saved = localStorage.getItem(`autosave_${formId}`);
        if (saved) {
            const data = JSON.parse(saved);
            const form = document.getElementById(formId);
            
            Object.entries(data).forEach(([name, value]) => {
                const field = form.querySelector(`[name="${name}"]`);
                if (field) {
                    field.value = value;
                }
            });
            
            this.showNotification('Draft restored', 'info', 2000);
        }
    }

    clearAutoSave(formId) {
        localStorage.removeItem(`autosave_${formId}`);
    }

    // Error Handling
    handleAjaxError(xhr, error) {
        let message = 'An error occurred. Please try again.';
        
        if (xhr.status === 422) {
            // Validation errors
            const errors = xhr.responseJSON?.errors;
            if (errors) {
                message = Object.values(errors).flat().join(', ');
            }
        } else if (xhr.status === 500) {
            message = 'Server error. Please contact support if this persists.';
        } else if (xhr.status === 0) {
            message = 'Network error. Please check your connection.';
        }
        
        this.showNotification(message, 'error', 6000);
    }

    // Public API
    static getInstance() {
        if (!TimeTrackingApp.instance) {
            TimeTrackingApp.instance = new TimeTrackingApp();
        }
        return TimeTrackingApp.instance;
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.timeTrackingApp = TimeTrackingApp.getInstance();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TimeTrackingApp;
}

// Add CSS for animations
const animationStyles = `
<style>
.stagger-animation {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease-out forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-in {
    animation: slideInFromBottom 0.6s ease-out;
}

@keyframes slideInFromBottom {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-progress {
    margin-bottom: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.5);
    border-radius: var(--radius-md);
    border: 1px solid var(--gray-200);
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: var(--gray-200);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
    width: 0%;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.875rem;
    color: var(--gray-600);
    font-weight: 500;
}

.form-progress.complete .progress-fill {
    background: linear-gradient(90deg, var(--success), #059669);
}

.field-error {
    color: var(--danger);
    font-size: 0.75rem;
    margin-top: 0.25rem;
}

.form-input-enhanced.error {
    border-color: var(--danger);
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.file-upload-wrapper {
    position: relative;
    border: 2px dashed var(--gray-300);
    border-radius: var(--radius-md);
    padding: 2rem;
    text-align: center;
    transition: var(--transition);
}

.file-upload-wrapper.dragover {
    border-color: var(--primary);
    background: rgba(79, 70, 229, 0.05);
}

.file-upload-label {
    cursor: pointer;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    color: var(--gray-600);
}

.file-upload-wrapper input[type="file"] {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.table-responsive {
    overflow-x: auto;
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-md);
}

.table-filter {
    width: 100%;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    border: 1px solid var(--gray-300);
    border-radius: var(--radius-md);
    font-size: 0.875rem;
}

.sortable {
    cursor: pointer;
    user-select: none;
    position: relative;
}

.sortable:hover {
    background: var(--gray-100);
}

.sortable.sort-asc::after {
    content: '↑';
    position: absolute;
    right: 0.5rem;
}

.sortable.sort-desc::after {
    content: '↓';
    position: absolute;
    right: 0.5rem;
}

.notification-close {
    background: none;
    border: none;
    color: inherit;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.7;
    transition: var(--transition);
}

.notification-close:hover {
    opacity: 1;
    background: rgba(0, 0, 0, 0.1);
}

body.modal-open {
    overflow: hidden;
}
</style>
`;

document.head.insertAdjacentHTML('beforeend', animationStyles);