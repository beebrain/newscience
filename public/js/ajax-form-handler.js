/**
 * Ajax Form Handler for Admin Modal Forms
 * 
 * Usage:
 * 1. Add data-ajax="true" to form
 * 2. Add data-modal="modalId" to close modal on success
 * 3. Add data-reload="true" to reload page on success
 * 4. Add data-redirect="url" to redirect on success
 * 5. Add data-toast="message" for success toast
 */

(function() {
    'use strict';

    // Initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        initAjaxForms();
        initToastContainer();
    });

    function initAjaxForms() {
        document.addEventListener('submit', function(e) {
            const form = e.target;
            
            // Check if this is an ajax form
            if (!form.hasAttribute('data-ajax')) return;
            
            e.preventDefault();
            submitAjaxForm(form);
        });
    }

    function initToastContainer() {
        if (!document.getElementById('toast-container')) {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = `
                position: fixed;
                top: 1rem;
                right: 1rem;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            `;
            document.body.appendChild(container);
        }
    }

    async function submitAjaxForm(form) {
        const submitBtn = form.querySelector('[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : '';
        const modal = form.closest('.modal');
        
        // Show loading
        setLoading(form, true);
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span> กำลังบันทึก...';
        }
        
        // Clear previous errors
        clearErrors(form);
        
        try {
            const formData = new FormData(form);
            const method = form.getAttribute('method') || 'POST';
            const action = form.getAttribute('action') || window.location.href;
            
            // Add CSRF token if exists
            const csrfToken = document.querySelector('input[name="csrf_token"]');
            if (csrfToken && !formData.has('csrf_token')) {
                formData.append('csrf_token', csrfToken.value);
            }
            
            const response = await fetch(action, {
                method: method,
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                handleSuccess(form, data);
            } else {
                handleErrors(form, data);
            }
        } catch (error) {
            console.error('Ajax form error:', error);
            showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
        } finally {
            setLoading(form, false);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    }

    function handleSuccess(form, data) {
        const modalId = form.getAttribute('data-modal');
        const shouldReload = form.hasAttribute('data-reload');
        const redirectUrl = form.getAttribute('data-redirect');
        const toastMessage = form.getAttribute('data-toast') || data.message || 'บันทึกสำเร็จ';
        
        // Show success toast
        showToast(toastMessage, 'success');
        
        // Close modal if specified
        if (modalId && typeof closeModal === 'function') {
            closeModal(modalId);
        }
        
        // Redirect if specified
        if (redirectUrl || data.redirect) {
            window.location.href = redirectUrl || data.redirect;
            return;
        }
        
        // Reload page if specified
        if (shouldReload || data.reload) {
            window.location.reload();
            return;
        }
        
        // Trigger custom event
        form.dispatchEvent(new CustomEvent('ajax:success', { detail: data }));
        
        // Reset form
        form.reset();
    }

    function handleErrors(form, data) {
        if (data.errors) {
            // Show field-specific errors
            Object.keys(data.errors).forEach(function(field) {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    showFieldError(input, data.errors[field]);
                }
            });
        }
        
        // Show general error message
        const message = data.message || data.error || 'กรุณาตรวจสอบข้อมูลอีกครั้ง';
        showToast(message, 'error');
        
        // Trigger custom event
        form.dispatchEvent(new CustomEvent('ajax:error', { detail: data }));
    }

    function showFieldError(input, message) {
        input.classList.add('is-invalid');
        
        // Remove existing error
        const existing = input.parentElement.querySelector('.invalid-feedback');
        if (existing) existing.remove();
        
        // Add error message
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        errorDiv.style.cssText = 'color: #dc3545; font-size: 12px; margin-top: 4px;';
        
        input.parentElement.appendChild(errorDiv);
    }

    function clearErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(function(el) {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.invalid-feedback').forEach(function(el) {
            el.remove();
        });
    }

    function setLoading(form, isLoading) {
        const modal = form.closest('.modal');
        if (modal) {
            if (isLoading) {
                modal.classList.add('modal-loading');
            } else {
                modal.classList.remove('modal-loading');
            }
        }
    }

    function showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        
        const toast = document.createElement('div');
        const colors = {
            success: '#10b981',
            error: '#ef4444',
            warning: '#f59e0b',
            info: '#3b82f6'
        };
        
        const icons = {
            success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px"><polyline points="20 6 9 17 4 12"></polyline></svg>',
            error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
            warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
            info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
        };
        
        toast.style.cssText = `
            background: white;
            border-left: 4px solid ${colors[type]};
            border-radius: 4px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 300px;
            max-width: 500px;
            animation: slideInRight 0.3s ease-out;
        `;
        
        toast.innerHTML = `
            <span style="color: ${colors[type]}">${icons[type]}</span>
            <span style="flex: 1; color: #374151;">${message}</span>
            <button onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;color:#9ca3af;padding:0;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        `;
        
        container.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    // Add animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 2px solid #e5e7eb;
            border-top-color: currentColor;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 0.5rem;
            vertical-align: middle;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

    // Expose global functions
    window.AjaxForm = {
        submit: submitAjaxForm,
        showToast: showToast,
        clearErrors: clearErrors
    };
})();
