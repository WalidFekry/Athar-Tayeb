/**
 * Athar Tayeb - Main JavaScript
 * Handles theme toggle, tasbeeh counters, search, and UI interactions
 */

(function() {
    'use strict';
    
    // Theme Management
    const themeToggle = document.getElementById('themeToggle');
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Apply saved theme
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeIcon(currentTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const theme = document.documentElement.getAttribute('data-theme');
            const newTheme = theme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    }
    
    function updateThemeIcon(theme) {
        const icon = document.querySelector('.theme-icon');
        if (icon) {
            icon.textContent = theme === 'dark' ? '☀️' : '🌙';
        }
    }
    
    // Tasbeeh Counter Management
    const tasbeehButtons = document.querySelectorAll('.tasbeeh-card');
    
    tasbeehButtons.forEach(button => {
        button.addEventListener('click', function() {
            const field = this.dataset.field;
            const memorialId = this.dataset.memorialId;
            const countElement = this.querySelector('.tasbeeh-count');
            const localCountElement = this.querySelector('.tasbeeh-local');
            
            if (!field || !memorialId) return;
            
            // Increment local counter immediately
            let localCount = parseInt(localCountElement?.textContent || '0');
            localCount++;
            if (localCountElement) {
                localCountElement.textContent = localCount;
            }
            
            // Add animation
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = '';
            }, 100);
            
            // Send to server
            incrementTasbeeh(memorialId, field, countElement);
        });
    });
    
    function incrementTasbeeh(memorialId, field, countElement) {
        const csrfToken = document.querySelector('input[name="csrf_token"]')?.value;
        
        fetch('/athar-tayeb/public/api/tasbeeh.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${memorialId}&field=${field}&csrf_token=${encodeURIComponent(csrfToken)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.counts) {
                // Update total count
                if (countElement && data.counts[field]) {
                    countElement.textContent = formatNumber(data.counts[field]);
                }
            } else if (data.error) {
                console.error('Tasbeeh error:', data.error);
            }
        })
        .catch(error => {
            console.error('Network error:', error);
        });
    }
    
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }
    
    // Search Functionality
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;
    
    if (searchInput && searchResults) {
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 2) {
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';
                return;
            }
            
            searchTimeout = setTimeout(() => {
                performSearch(query);
            }, 300);
        });
        
        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    }
    
    function performSearch(query) {
        fetch(`/athar-tayeb/public/api/search.php?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.results) {
                    displaySearchResults(data.results);
                } else {
                    searchResults.innerHTML = '<div class="search-result-item">لا توجد نتائج</div>';
                    searchResults.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                searchResults.innerHTML = '<div class="search-result-item">حدث خطأ في البحث</div>';
                searchResults.style.display = 'block';
            });
    }
    
    function displaySearchResults(results) {
        if (results.length === 0) {
            searchResults.innerHTML = '<div class="search-result-item">لا توجد نتائج</div>';
            searchResults.style.display = 'block';
            return;
        }
        
        let html = '';
        results.forEach(result => {
            const url = `/athar-tayeb/public/memorial.php?id=${result.id}`;
            html += `
                <a href="${url}" class="search-result-item text-decoration-none">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="fw-bold">${escapeHtml(result.name)}</div>
                            ${result.death_date ? `<small class="text-muted">${result.death_date}</small>` : ''}
                        </div>
                    </div>
                </a>
            `;
        });
        
        searchResults.innerHTML = html;
        searchResults.style.display = 'block';
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Image Preview on Upload
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            
            if (file) {
                // Validate file size
                if (file.size > 2 * 1024 * 1024) {
                    alert('حجم الملف كبير جداً (الحد الأقصى 2 ميجابايت)');
                    this.value = '';
                    imagePreview.innerHTML = '';
                    return;
                }
                
                // Validate file type
                if (!['image/jpeg', 'image/png'].includes(file.type)) {
                    alert('نوع الملف غير مسموح (فقط JPG و PNG)');
                    this.value = '';
                    imagePreview.innerHTML = '';
                    return;
                }
                
                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.innerHTML = `
                        <img src="${e.target.result}" alt="معاينة الصورة" class="memorial-image">
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.innerHTML = '';
            }
        });
    }
    
    // Copy to Clipboard
    const copyButtons = document.querySelectorAll('.copy-link-btn');
    
    copyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    showNotification('تم نسخ الرابط بنجاح ✓');
                }).catch(err => {
                    fallbackCopy(url);
                });
            } else {
                fallbackCopy(url);
            }
        });
    });
    
    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        try {
            document.execCommand('copy');
            showNotification('تم نسخ الرابط بنجاح ✓');
        } catch (err) {
            showNotification('فشل نسخ الرابط');
        }
        
        document.body.removeChild(textarea);
    }
    
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x mt-3';
        notification.style.zIndex = '9999';
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Ruqyah PDF Toggle
    const ruqyahToggle = document.getElementById('ruqyahToggle');
    const ruqyahFrame = document.getElementById('ruqyahFrame');
    
    if (ruqyahToggle && ruqyahFrame) {
        ruqyahToggle.addEventListener('click', function() {
            if (ruqyahFrame.style.display === 'none' || !ruqyahFrame.style.display) {
                ruqyahFrame.style.display = 'block';
                this.textContent = '✖ إغلاق الرقية الشرعية';
            } else {
                ruqyahFrame.style.display = 'none';
                this.textContent = '📖 تشغيل الرقية الشرعية';
            }
        });
    }
    
    // Asma Allah "Show More" functionality
    const showMoreBtn = document.getElementById('showMoreAsma');
    const hiddenAsma = document.querySelectorAll('.asma-item.hidden');
    
    if (showMoreBtn && hiddenAsma.length > 0) {
        showMoreBtn.addEventListener('click', function() {
            hiddenAsma.forEach(item => {
                item.classList.remove('hidden');
                item.style.display = 'block';
            });
            this.style.display = 'none';
        });
    }
    
    // Audio Player Management (ensure only one plays at a time)
    const audioPlayers = document.querySelectorAll('audio');
    
    audioPlayers.forEach(player => {
        player.addEventListener('play', function() {
            audioPlayers.forEach(otherPlayer => {
                if (otherPlayer !== player) {
                    otherPlayer.pause();
                }
            });
        });
    });
    
    // Form Validation Enhancement
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Smooth Scroll for Anchor Links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#!') {
                e.preventDefault();
                const target = document.querySelector(href);
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Initialize tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
})();
