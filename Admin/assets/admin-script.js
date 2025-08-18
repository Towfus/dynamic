// admin/assets/admin-script.js - Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            if (alert.classList.contains('show')) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    });

    // Form validation enhancement
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });
    });

    // Image preview for file uploads
    const imageInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    imageInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Find existing preview or create new one
                    let preview = input.parentNode.querySelector('.image-preview');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'image-preview mt-2';
                        input.parentNode.appendChild(preview);
                    }
                    
                    preview.innerHTML = `
                        <img src="${e.target.result}" 
                             class="img-thumbnail" 
                             style="max-width: 200px; max-height: 200px;">
                        <small class="text-muted d-block mt-1">Preview of new image</small>
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Confirm delete actions
    const deleteLinks = document.querySelectorAll('a[href*="action=delete"]');
    deleteLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    // Character counter for textareas
    const textareas = document.querySelectorAll('textarea[maxlength]');
    textareas.forEach(function(textarea) {
        const maxLength = textarea.getAttribute('maxlength');
        const counter = document.createElement('small');
        counter.className = 'text-muted mt-1';
        textarea.parentNode.appendChild(counter);
        
        function updateCounter() {
            const remaining = maxLength - textarea.value.length;
            counter.textContent = `${textarea.value.length}/${maxLength} characters`;
            
            if (remaining < 50) {
                counter.classList.add('text-warning');
                counter.classList.remove('text-muted');
            } else {
                counter.classList.add('text-muted');
                counter.classList.remove('text-warning');
            }
        }
        
        textarea.addEventListener('input', updateCounter);
        updateCounter(); // Initial count
    });

    // Auto-resize textareas
    const autoResizeTextareas = document.querySelectorAll('textarea');
    autoResizeTextareas.forEach(function(textarea) {
        function resize() {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        }
        
        textarea.addEventListener('input', resize);
        resize(); // Initial resize
    });

    // Table search functionality
    const searchInputs = document.querySelectorAll('.table-search');
    searchInputs.forEach(function(searchInput) {
        const table = searchInput.closest('.card').querySelector('table');
        if (!table) return;
        
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');
            
            rows.forEach(function(row) {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });

    // Sortable table headers
    const sortableHeaders = document.querySelectorAll('.sortable');
    sortableHeaders.forEach(function(header) {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const table = header.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const headerIndex = Array.from(header.parentNode.children).indexOf(header);
            
            // Toggle sort direction
            const isAscending = !header.classList.contains('sort-desc');
            
            // Remove existing sort classes
            sortableHeaders.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
            
            // Add new sort class
            header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
            
            // Sort rows
            rows.sort(function(a, b) {
                const aText = a.children[headerIndex].textContent.trim();
                const bText = b.children[headerIndex].textContent.trim();
                
                // Check if values are numbers
                const aNum = parseFloat(aText);
                const bNum = parseFloat(bText);
                
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return isAscending ? aNum - bNum : bNum - aNum;
                } else {
                    return isAscending 
                        ? aText.localeCompare(bText)
                        : bText.localeCompare(aText);
                }
            });
            
            // Re-append sorted rows
            rows.forEach(row => tbody.appendChild(row));
        });
    });

    // AJAX form submission for quick actions
    const ajaxForms = document.querySelectorAll('.ajax-form');
    ajaxForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.classList.add('loading');
            submitBtn.disabled = true;
            
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showNotification('Success!', data.message, 'success');
                    
                    // Optionally reload or update UI
                    if (data.reload) {
                        setTimeout(() => location.reload(), 1500);
                    }
                } else {
                    showNotification('Error!', data.message || 'An error occurred', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error!', 'An unexpected error occurred', 'danger');
            })
            .finally(() => {
                submitBtn.classList.remove('loading');
                submitBtn.disabled = false;
            });
        });
    });

    // Notification function
    function showNotification(title, message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <strong>${title}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                const bsAlert = new bootstrap.Alert(notification);
                bsAlert.close();
            }
        }, 5000);
    }

    // Bulk actions functionality
    const bulkCheckboxes = document.querySelectorAll('.bulk-checkbox');
    const bulkSelectAll = document.querySelector('.bulk-select-all');
    const bulkActions = document.querySelector('.bulk-actions');
    
    if (bulkCheckboxes.length > 0) {
        // Select all functionality
        if (bulkSelectAll) {
            bulkSelectAll.addEventListener('change', function() {
                bulkCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActions();
            });
        }
        
        // Individual checkbox change
        bulkCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActions);
        });
        
        function updateBulkActions() {
            const checkedBoxes = document.querySelectorAll('.bulk-checkbox:checked');
            
            if (bulkActions) {
                if (checkedBoxes.length > 0) {
                    bulkActions.style.display = 'block';
                    bulkActions.querySelector('.selected-count').textContent = checkedBoxes.length;
                } else {
                    bulkActions.style.display = 'none';
                }
            }
            
            // Update select all checkbox state
            if (bulkSelectAll) {
                if (checkedBoxes.length === 0) {
                    bulkSelectAll.indeterminate = false;
                    bulkSelectAll.checked = false;
                } else if (checkedBoxes.length === bulkCheckboxes.length) {
                    bulkSelectAll.indeterminate = false;
                    bulkSelectAll.checked = true;
                } else {
                    bulkSelectAll.indeterminate = true;
                }
            }
        }
    }

    // Draggable sortable lists
    const sortableLists = document.querySelectorAll('.sortable-list');
    sortableLists.forEach(function(list) {
        // Simple drag and drop implementation
        let draggedElement = null;
        
        list.addEventListener('dragstart', function(e) {
            draggedElement = e.target;
            e.target.style.opacity = '0.5';
        });
        
        list.addEventListener('dragend', function(e) {
            e.target.style.opacity = '';
            draggedElement = null;
        });
        
        list.addEventListener('dragover', function(e) {
            e.preventDefault();
        });
        
        list.addEventListener('drop', function(e) {
            e.preventDefault();
            if (draggedElement !== e.target && e.target.draggable) {
                // Swap elements
                const draggedIndex = Array.from(list.children).indexOf(draggedElement);
                const targetIndex = Array.from(list.children).indexOf(e.target);
                
                if (draggedIndex < targetIndex) {
                    list.insertBefore(draggedElement, e.target.nextSibling);
                } else {
                    list.insertBefore(draggedElement, e.target);
                }
                
                // Update sort order (implement AJAX call to save new order)
                updateSortOrder(list);
            }
        });
    });
    
    function updateSortOrder(list) {
        const items = Array.from(list.children);
        const orderData = items.map((item, index) => ({
            id: item.dataset.id,
            order: index + 1
        }));
        
        // Implement AJAX call to save order
        console.log('New order:', orderData);
    }
});