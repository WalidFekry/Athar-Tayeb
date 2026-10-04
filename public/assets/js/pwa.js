/**
 * Athar Tayeb - PWA Install Manager
 * Handles app installation prompts, device detection, and install instructions
 * for Android, iOS, Windows, macOS, and other platforms.
 *
 * Vanilla JavaScript — no external dependencies
 * RTL Arabic support — Bootstrap 5 RTL compatible
 */

'use strict';

(function () {

    // ================================================
    // Constants & State
    // ================================================
    const INSTALL_DISMISSED_KEY = 'athar_pwa_dismissed';
    const INSTALL_DONE_KEY = 'athar_pwa_installed';

    let deferredPrompt = null;       // beforeinstallprompt event
    let installState = 'unknown';    // 'unknown' | 'installable' | 'installed' | 'not-supported' | 'ios-safari' | 'ios-other' | 'instructions'
    let modalInstance = null;        // Bootstrap Modal instance

    // ================================================
    // Device & Browser Detection
    // ================================================

    const ua = navigator.userAgent || '';
    const platform = navigator.platform || '';

    const isIOS = /iPad|iPhone|iPod/.test(ua) && !window.MSStream;
    // iPadOS 13+ reports as Mac — detect by touch support
    const isIPadOS = /Macintosh/.test(ua) && navigator.maxTouchPoints > 1;
    const isAnyIOS = isIOS || isIPadOS;

    const isAndroid = /Android/.test(ua);
    const isMacOS = /Macintosh/.test(ua) && !isIPadOS;
    const isWindows = /Win/.test(platform) || /Windows/.test(ua);
    const isLinux = /Linux/.test(platform) && !isAndroid;

    // isSafari: real Safari only — NOT Chrome/Chromium/CriOS/Edge/Samsung
    const isSafari = /Safari/.test(ua) && !/Chrome/.test(ua) && !/Chromium/.test(ua) &&
                     !/CriOS/.test(ua) && !/SamsungBrowser/.test(ua) && !/FBAN/.test(ua) &&
                     !/FBAV/.test(ua) && !/Instagram/.test(ua);
    const isChrome = /Chrome/.test(ua) && !/Chromium/.test(ua) && !/Edg\//.test(ua) &&
                     !/EdgA/.test(ua) && !/OPR/.test(ua) && !/SamsungBrowser/.test(ua);
    const isEdge = /Edg\//.test(ua) || /EdgA/.test(ua);
    const isFirefox = /Firefox/.test(ua) && !/Seamonkey/.test(ua);
    const isOpera = /OPR\//.test(ua) || /Opera/.test(ua);
    const isChromeiOS = /CriOS/.test(ua);          // Chrome on iOS
    const isFirefoxiOS = /FxiOS/.test(ua);         // Firefox on iOS
    const isEdgeiOS = /EdgA/.test(ua);             // Edge on iOS
    const isSamsungBrowser = /SamsungBrowser/.test(ua);

    // ================================================
    // In-App Browser Detection
    // ================================================

    // Known in-app browsers by UA pattern
    const IN_APP_PATTERNS = /FBAN|FBAV|Instagram|Twitter|Snapchat|LinkedInApp|WhatsApp|MicroMessenger|Line\/|musical_ly|BytedanceWebview|TikTok|GSA\/|DuckDuckGo|brave|YaBrowser/i;

    // Android WebView: has 'wv' in UA and is NOT a real Chrome
    const isAndroidWebView = isAndroid && /; wv\)/.test(ua);

    // iOS WebView: not Safari, not Chrome/FF/Edge for iOS — likely in-app
    const isIOSWebView = isAnyIOS && !isSafari && !isChromeiOS && !isFirefoxiOS && !isEdgeiOS;

    const isInAppBrowser = IN_APP_PATTERNS.test(ua) || isAndroidWebView || isIOSWebView;

    function getInAppBrowserName() {
        if (/FBAN|FBAV/i.test(ua)) return 'Facebook';
        if (/Instagram/i.test(ua)) return 'Instagram';
        if (/Twitter/i.test(ua)) return 'Twitter (X)';
        if (/Snapchat/i.test(ua)) return 'Snapchat';
        if (/LinkedInApp/i.test(ua)) return 'LinkedIn';
        if (/WhatsApp/i.test(ua)) return 'WhatsApp';
        if (/MicroMessenger/i.test(ua)) return 'WeChat';
        if (/Line\//i.test(ua)) return 'Line';
        if (/musical_ly|BytedanceWebview|TikTok/i.test(ua)) return 'TikTok';
        if (/GSA\//i.test(ua)) return 'تطبيق Google';
        return null; // unknown in-app or plain WebView
    }


    // ================================================
    // Check if Already Running as Installed PWA
    // ================================================

    function isRunningAsApp() {
        // Standalone display mode (Android/Windows/macOS Chrome, Edge, Samsung)
        if (window.matchMedia && window.matchMedia('(display-mode: standalone)').matches) {
            return true;
        }
        // iOS Safari standalone
        if (typeof navigator.standalone !== 'undefined' && navigator.standalone === true) {
            return true;
        }
        // Fullscreen display mode
        if (window.matchMedia && window.matchMedia('(display-mode: fullscreen)').matches) {
            return true;
        }
        return false;
    }

    // ================================================
    // Determine Install State
    // ================================================

    function detectInstallState() {
        // If already running as installed app — no need to show button logic
        if (isRunningAsApp()) {
            return 'installed';
        }

        // Check if user already confirmed install in this session
        if (sessionStorage.getItem(INSTALL_DONE_KEY)) {
            return 'installed';
        }

        // beforeinstallprompt already captured (Chrome/Edge/Samsung on Android & Desktop)
        if (deferredPrompt) {
            return 'installable';
        }

        // iOS devices — order matters: check in-app first, then Safari, then other browsers
        if (isAnyIOS) {
            // In-app browser WebViews (Facebook, Instagram, WhatsApp, etc.)
            // Must be checked BEFORE isSafari because some in-app browsers spoof Safari UA
            if (isInAppBrowser) return 'ios-inapp';
            // Real Safari (no known 3rd-party browser markers)
            if (isSafari) return 'ios-safari';
            // Chrome/Firefox/Edge for iOS
            if (isChromeiOS || isFirefoxiOS || isEdgeiOS) return 'ios-other';
            // Unknown iOS browser — treat like Safari (safest default)
            return 'ios-safari';
        }

        // Android in-app browsers (Facebook, WhatsApp WebView…)
        if (isAndroid && isInAppBrowser) {
            return 'android-inapp';
        }

        // Desktop browsers that support beforeinstallprompt (Chrome/Edge on Windows/macOS/Linux)
        // — prompt not yet fired but browser supports it
        if (isChrome || isEdge || isSamsungBrowser) {
            return 'instructions';
        }

        // macOS Safari — supports "Add to Dock" from Safari 17+
        if (isMacOS && isSafari) {
            return 'macos-safari';
        }

        // Firefox, Opera, and others — show generic instructions
        return 'not-supported';
    }

    // ================================================
    // Determine Install Instructions Content
    // ================================================

    function getInstructionsHTML() {
        const state = detectInstallState();

        if (state === 'installed') {
            return {
                title: 'التطبيق مثبّت بالفعل',
                body: `
                    <div class="pwa-installed-state text-center py-2">
                        <div class="pwa-check-icon mb-3">✅</div>
                        <p class="mb-0">أنت تستخدم <strong>أثر طيب</strong> بالفعل كتطبيق مثبّت. استمتع بالتجربة!</p>
                    </div>`,
                primaryBtn: null,
                secondaryBtn: { id: 'pwaBtnClose', text: 'حسناً', action: 'close' }
            };
        }

        if (state === 'installable') {
            return {
                title: 'ثبّت تطبيق أثر طيب',
                body: `
                    <div class="pwa-install-content">
                        <div class="pwa-feature-row">
                            <span class="pwa-feature-icon">⚡</span>
                            <span>وصول سريع مباشرة من الشاشة الرئيسية</span>
                        </div>
                        <div class="pwa-feature-row">
                            <span class="pwa-feature-icon">📴</span>
                            <span>يعمل بشكل جزئي حتى بدون إنترنت</span>
                        </div>
                        <div class="pwa-feature-row">
                            <span class="pwa-feature-icon">🌿</span>
                            <span>تجربة تطبيق كاملة بدون شريط العنوان</span>
                        </div>
                        <div class="pwa-feature-row">
                            <span class="pwa-feature-icon">💾</span>
                            <span>لا يحتاج إلى تنزيل من متجر التطبيقات</span>
                        </div>
                    </div>`,
                primaryBtn: { id: 'pwaBtnInstall', text: '📲 تثبيت التطبيق الآن', action: 'install' },
                secondaryBtn: { id: 'pwaBtnClose', text: 'لاحقاً', action: 'close' }
            };
        }

        if (state === 'ios-safari') {
            const shareIcon = isIPadOS
                ? '⬆️ (أعلى الشاشة)'
                : '⬆️ (أسفل الشاشة)';
            return {
                title: 'أضف أثر طيب للشاشة الرئيسية',
                body: `
                    <p class="pwa-subtitle mb-3">اتبع هذه الخطوات في متصفح Safari:</p>
                    <ol class="pwa-steps-list">
                        <li>
                            <span class="step-num">١</span>
                            <span>اضغط على زر المشاركة ${shareIcon} في شريط Safari</span>
                        </li>
                        <li>
                            <span class="step-num">٢</span>
                            <span>مرّر للأسفل وابحث عن <strong>«إضافة إلى الشاشة الرئيسية»</strong></span>
                        </li>
                        <li>
                            <span class="step-num">٣</span>
                            <span>اضغط <strong>«إضافة»</strong> في أعلى الشاشة</span>
                        </li>
                        <li>
                            <span class="step-num">٤</span>
                            <span>افتح أيقونة <strong>🌿 أثر طيب</strong> من شاشتك الرئيسية</span>
                        </li>
                    </ol>
                    <div class="pwa-note mt-3">
                        <span class="pwa-note-icon">ℹ️</span>
                        <span>هذه الميزة متاحة في Safari فقط على iPhone وiPad</span>
                    </div>`,
                primaryBtn: { id: 'pwaBtnCopyLink', text: '🔗 نسخ رابط الموقع', action: 'copy' },
                secondaryBtn: { id: 'pwaBtnClose', text: 'فهمت', action: 'close' }
            };
        }

        if (state === 'ios-inapp') {
            const appName = getInAppBrowserName();
            const appLabel = appName ? `تطبيق ${appName}` : 'تطبيق آخر';
            return {
                title: 'افتح أثر طيب في Safari أولاً',
                body: `
                    <p class="pwa-inapp-desc mb-3">
                        أنت تتصفح من داخل <strong>${appLabel}</strong>، والتثبيت يعمل من Safari فقط.
                        بعد الفتح ستظهر لك خطوات التثبيت مباشرة.
                    </p>`,
                hintText: 'لم يفتح؟ اضغط <strong>⋯</strong> أو <strong>⋮</strong> أعلى الشاشة ثم «فتح في المتصفح» أو «Open in Browser»',
                primaryBtn: { id: 'pwaBtnOpenBrowser', text: '🧭 افتح في Safari', action: 'open-in-browser' },
                secondaryBtn: { id: 'pwaBtnCopyLink', text: '🔗 انسخ الرابط', action: 'copy' }
            };
        }

        if (state === 'android-inapp') {
            const appName = getInAppBrowserName();
            const appLabel = appName ? `تطبيق ${appName}` : 'تطبيق آخر';
            return {
                title: 'افتح أثر طيب في المتصفح أولاً',
                body: `
                    <p class="pwa-inapp-desc mb-3">
                        أنت تتصفح من داخل <strong>${appLabel}</strong>، والتثبيت يعمل من Chrome أو متصفحك الافتراضي فقط.
                        بعد الفتح ستظهر لك خطوات التثبيت مباشرة.
                    </p>`,
                hintText: 'لم يفتح؟ اضغط <strong>⋮</strong> أو <strong>⋯</strong> في الزاوية ثم «فتح في المتصفح» أو «Open in Browser»',
                primaryBtn: { id: 'pwaBtnOpenBrowser', text: '🌐 افتح في المتصفح', action: 'open-in-browser' },
                secondaryBtn: { id: 'pwaBtnCopyLink', text: '🔗 انسخ الرابط', action: 'copy' }
            };
        }

        if (state === 'ios-other') {
            let browserName = 'متصفحك الحالي';
            if (isChromeiOS) browserName = 'Chrome';
            else if (isFirefoxiOS) browserName = 'Firefox';
            else if (isEdgeiOS) browserName = 'Edge';

            return {
                title: 'أضف أثر طيب للشاشة الرئيسية',
                body: `
                    <div class="pwa-note mb-3">
                        <span class="pwa-note-icon">⚠️</span>
                        <span>ميزة إضافة الموقع للشاشة الرئيسية على iPhone وiPad تعمل عبر <strong>Safari</strong> فقط، وليس عبر ${browserName}.</span>
                    </div>
                    <div class="pwa-url-box" id="pwaUrlDisplay">${window.location.origin}</div>
                    <ol class="pwa-steps-list mt-3">
                        <li><span class="step-num">١</span><span>افتح Safari على جهازك</span></li>
                        <li><span class="step-num">٢</span><span>انتقل إلى <strong>${window.location.origin}</strong></span></li>
                        <li><span class="step-num">٣</span><span>اضغط زر المشاركة ⬆️ ثم «إضافة إلى الشاشة الرئيسية»</span></li>
                    </ol>`,
                primaryBtn: { id: 'pwaBtnCopyLink', text: '🔗 نسخ رابط الموقع', action: 'copy' },
                secondaryBtn: { id: 'pwaBtnClose', text: 'إغلاق', action: 'close' }
            };
        }

        if (state === 'macos-safari') {
            return {
                title: 'أضف أثر طيب إلى Dock',
                body: `
                    <p class="pwa-subtitle mb-3">في Safari على macOS Sonoma أو أحدث:</p>
                    <ol class="pwa-steps-list">
                        <li><span class="step-num">١</span><span>افتح قائمة <strong>File</strong> في شريط القوائم</span></li>
                        <li><span class="step-num">٢</span><span>اختر <strong>«Add to Dock»</strong> أو <strong>«Add to Home Screen»</strong></span></li>
                        <li><span class="step-num">٣</span><span>اضغط <strong>«Add»</strong> لتأكيد الإضافة</span></li>
                    </ol>
                    <div class="pwa-note mt-3">
                        <span class="pwa-note-icon">ℹ️</span>
                        <span>هذه الميزة متاحة في Safari 17+ على macOS Sonoma فما فوق</span>
                    </div>`,
                primaryBtn: { id: 'pwaBtnCopyLink', text: '🔗 نسخ رابط الموقع', action: 'copy' },
                secondaryBtn: { id: 'pwaBtnClose', text: 'فهمت', action: 'close' }
            };
        }

        if (state === 'instructions') {
            let browserInstructions = '';
            if (isChrome) {
                browserInstructions = `
                    <ol class="pwa-steps-list">
                        <li><span class="step-num">١</span><span>اضغط على أيقونة <strong>⊕</strong> أو <strong>💻</strong> في شريط العنوان (يمين)</span></li>
                        <li><span class="step-num">٢</span><span>أو افتح قائمة Chrome (⋮) واختر <strong>«تثبيت أثر طيب»</strong></span></li>
                        <li><span class="step-num">٣</span><span>اضغط <strong>«تثبيت»</strong> في نافذة التأكيد</span></li>
                    </ol>`;
            } else if (isEdge) {
                browserInstructions = `
                    <ol class="pwa-steps-list">
                        <li><span class="step-num">١</span><span>اضغط على أيقونة <strong>+</strong> في شريط العنوان</span></li>
                        <li><span class="step-num">٢</span><span>أو افتح قائمة Edge (⋯) واختر <strong>«التطبيقات» ← «تثبيت هذا الموقع كتطبيق»</strong></span></li>
                        <li><span class="step-num">٣</span><span>اضغط <strong>«تثبيت»</strong></span></li>
                    </ol>`;
            } else if (isFirefox) {
                browserInstructions = `
                    <p>Firefox لا يدعم تثبيت التطبيقات مباشرةً حالياً. يمكنك:</p>
                    <ol class="pwa-steps-list">
                        <li><span class="step-num">١</span><span>فتح الموقع في Chrome أو Edge للتثبيت كتطبيق</span></li>
                        <li><span class="step-num">٢</span><span>أو إضافة إشارة مرجعية للوصول السريع</span></li>
                    </ol>`;
            } else {
                browserInstructions = `
                    <p>ابحث في قائمة متصفحك عن خيار:</p>
                    <ul class="pwa-steps-list">
                        <li><span class="step-num">•</span><span>«تثبيت التطبيق» أو «Install App»</span></li>
                        <li><span class="step-num">•</span><span>«إضافة إلى الشاشة الرئيسية» أو «Add to Home Screen»</span></li>
                    </ul>`;
            }
            return {
                title: 'كيفية تثبيت أثر طيب',
                body: `
                    <p class="pwa-subtitle mb-3">للتثبيت على جهازك:</p>
                    ${browserInstructions}`,
                primaryBtn: { id: 'pwaBtnCopyLink', text: '🔗 نسخ رابط الموقع', action: 'copy' },
                secondaryBtn: { id: 'pwaBtnClose', text: 'فهمت', action: 'close' }
            };
        }

        // Default: not supported or unknown
        return {
            title: 'إضافة أثر طيب',
            body: `
                <p class="pwa-subtitle mb-3">يمكنك إضافة أثر طيب للوصول السريع إليه:</p>
                <ul class="pwa-steps-list">
                    <li><span class="step-num">•</span><span>في Chrome أو Edge: ابحث عن أيقونة التثبيت في شريط العنوان</span></li>
                    <li><span class="step-num">•</span><span>في Safari على iPhone: استخدم زر المشاركة ⬆️ ← «إضافة إلى الشاشة الرئيسية»</span></li>
                    <li><span class="step-num">•</span><span>في متصفحات أخرى: افتح القائمة وابحث عن «إضافة إلى الشاشة الرئيسية»</span></li>
                </ul>`,
            primaryBtn: { id: 'pwaBtnCopyLink', text: '🔗 نسخ رابط الموقع', action: 'copy' },
            secondaryBtn: { id: 'pwaBtnClose', text: 'إغلاق', action: 'close' }
        };
    }

    // ================================================
    // Build Modal HTML (injected once)
    // ================================================

    function buildModal() {
        if (document.getElementById('pwaInstallModal')) return;

        const modal = document.createElement('div');
        modal.innerHTML = `
<div class="modal fade" id="pwaInstallModal" tabindex="-1" aria-labelledby="pwaInstallModalLabel" aria-hidden="true" dir="rtl">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content pwa-modal-content">
            <div class="modal-header pwa-modal-header border-0 pb-0">
                <div class="pwa-modal-brand">
                    <span class="pwa-brand-icon" aria-hidden="true">🌿</span>
                    <span class="pwa-brand-name">أثر طيب</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body pwa-modal-body pt-2">
                <h2 class="pwa-modal-title" id="pwaInstallModalLabel"></h2>
                <div id="pwaModalContent"></div>
                <div id="pwaModalHint" class="pwa-open-hint" style="display:none"></div>
            </div>
            <div class="modal-footer pwa-modal-footer border-0 pt-0 gap-2">
                <div id="pwaModalButtons" class="d-flex flex-column flex-sm-row gap-2 w-100 justify-content-end"></div>
            </div>
        </div>
    </div>
</div>`;
        document.body.appendChild(modal.firstElementChild);
    }

    // ================================================
    // Open Modal with Current Install State
    // ================================================

    function openInstallModal() {
        buildModal();

        const content = getInstructionsHTML();
        const titleEl = document.getElementById('pwaInstallModalLabel');
        const bodyEl = document.getElementById('pwaModalContent');
        const hintEl = document.getElementById('pwaModalHint');
        const buttonsEl = document.getElementById('pwaModalButtons');

        if (titleEl) titleEl.textContent = content.title;
        if (bodyEl) bodyEl.innerHTML = content.body;

        // Hint text (shown inside modal body, below content)
        if (hintEl) {
            if (content.hintText) {
                hintEl.innerHTML = content.hintText;
                hintEl.style.display = '';
            } else {
                hintEl.style.display = 'none';
            }
        }

        // Build buttons
        if (buttonsEl) {
            buttonsEl.innerHTML = '';

            if (content.primaryBtn) {
                const btn = document.createElement('button');
                btn.id = content.primaryBtn.id;
                btn.type = 'button';
                btn.className = 'btn btn-primary pwa-btn-primary';
                btn.textContent = content.primaryBtn.text;
                btn.setAttribute('data-action', content.primaryBtn.action);
                btn.addEventListener('click', handleButtonAction);
                buttonsEl.appendChild(btn);
            }

            if (content.secondaryBtn) {
                const btn = document.createElement('button');
                btn.id = content.secondaryBtn.id;
                btn.type = 'button';
                btn.className = 'btn btn-outline-secondary pwa-btn-secondary';
                btn.textContent = content.secondaryBtn.text;
                btn.setAttribute('data-action', content.secondaryBtn.action);
                btn.addEventListener('click', handleButtonAction);
                buttonsEl.appendChild(btn);
            }
        }

        // Show modal via Bootstrap
        const modalEl = document.getElementById('pwaInstallModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            // Always create a fresh instance reference in case it was disposed
            if (!modalInstance || !modalEl._bsModal) {
                modalInstance = new bootstrap.Modal(modalEl, { keyboard: true, backdrop: true });
            }
            modalInstance.show();
        }
    }

    // ================================================
    // Handle Button Actions
    // ================================================

    function handleButtonAction(e) {
        const action = this.getAttribute('data-action');

        if (action === 'install') {
            triggerNativeInstall();
        } else if (action === 'copy') {
            copyPageLink();
        } else if (action === 'open-in-browser') {
            openInNativeBrowser(this);
        } else if (action === 'close') {
            if (modalInstance) modalInstance.hide();
        }
    }

    // ================================================
    // Trigger Native Install Prompt (Android/Desktop)
    // ================================================

    async function triggerNativeInstall() {
        if (!deferredPrompt) {
            // Prompt no longer available — show instructions instead
            openInstallModal();
            return;
        }

        try {
            await deferredPrompt.prompt();
            const choiceResult = await deferredPrompt.userChoice;

            if (choiceResult.outcome === 'accepted') {
                // User accepted — update state
                sessionStorage.setItem(INSTALL_DONE_KEY, '1');
                updateInstallButton('installed');
                if (modalInstance) modalInstance.hide();
            } else {
                // User dismissed — store that and hide button for this session
                sessionStorage.setItem(INSTALL_DISMISSED_KEY, '1');
                if (modalInstance) modalInstance.hide();
            }
        } catch (err) {
            console.warn('[PWA] Install prompt error:', err);
        }

        // Prompt can only be used once
        deferredPrompt = null;
    }

    // ================================================
    // Open in Native Browser (for in-app WebViews)
    // ================================================

    function openInNativeBrowser(btn) {
        const currentUrl = window.location.href;

        // Change button to loading state (preserve inner structure)
        if (btn) {
            btn.disabled = true;
            const origHTML = btn.innerHTML;
            btn.innerHTML = '<span aria-hidden="true">⏳</span> جاري الفتح...';
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = origHTML;
            }, 3000);
        }

        // --- iOS: x-safari-https:// URL scheme ---
        if (isAnyIOS) {
            const safariUrl = currentUrl.replace(/^https?:\/\//, 'x-safari-https://');

            // Watch for page visibility — if page goes hidden, Safari opened successfully
            let safariOpened = false;
            const onVisibilityChange = () => {
                if (document.visibilityState === 'hidden') {
                    safariOpened = true;
                }
            };
            document.addEventListener('visibilitychange', onVisibilityChange, { once: true });

            // Attempt to open Safari
            window.location.href = safariUrl;

            // After 2s: if page is still visible, scheme didn't work — show hint
            setTimeout(() => {
                document.removeEventListener('visibilitychange', onVisibilityChange);
                if (!safariOpened) {
                    const hintEl = document.getElementById('pwaModalHint');
                    if (hintEl) {
                        hintEl.style.display = '';
                        hintEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }
            }, 2000);

            return;
        }

        // --- Android: intent URL to open in default browser / Chrome ---
        if (isAndroid) {
            const intentUrl = 'intent://' +
                currentUrl.replace(/^https?:\/\//, '') +
                '#Intent;scheme=https;action=android.intent.action.VIEW;end';

            // Track if app left (intent worked)
            let browserOpened = false;
            const onVisibilityChange = () => {
                if (document.visibilityState === 'hidden') {
                    browserOpened = true;
                }
            };
            document.addEventListener('visibilitychange', onVisibilityChange, { once: true });

            window.location.href = intentUrl;

            // After 2s: if still here, show hint
            setTimeout(() => {
                document.removeEventListener('visibilitychange', onVisibilityChange);
                if (!browserOpened) {
                    const hintEl = document.getElementById('pwaModalHint');
                    if (hintEl) {
                        hintEl.style.display = '';
                        hintEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                }
            }, 2000);

            return;
        }

        // --- Fallback for other platforms ---
        try {
            window.open(currentUrl, '_blank');
        } catch (e) {
            copyPageLink();
        }
    }

    // ================================================
    // Copy Page Link to Clipboard
    // ================================================

    function copyPageLink() {
        const url = window.location.origin + '/';
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(url).then(() => {
                showCopyFeedback();
            }).catch(() => {
                fallbackCopy(url);
            });
        } else {
            fallbackCopy(url);
        }
    }

    function fallbackCopy(text) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.cssText = 'position:fixed;opacity:0;top:0;left:0;';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try {
            document.execCommand('copy');
            showCopyFeedback();
        } catch (e) {
            console.warn('[PWA] Copy failed:', e);
        }
        document.body.removeChild(textarea);
    }

    function showCopyFeedback() {
        const btn = document.getElementById('pwaBtnCopyLink');
        if (!btn) return;
        const original = btn.textContent;
        btn.textContent = '✅ تم النسخ!';
        btn.disabled = true;
        setTimeout(() => {
            btn.textContent = original;
            btn.disabled = false;
        }, 2500);
    }

    // ================================================
    // Update Navbar Install Button State
    // ================================================

    function updateInstallButton(state) {
        const btn = document.getElementById('pwaInstallBtn');
        if (!btn) return;

        if (state === 'installed') {
            btn.style.display = 'none'; // Hide if already installed
        } else if (state === 'installable') {
            btn.style.display = '';
            btn.setAttribute('aria-label', 'تثبيت تطبيق أثر طيب');
        } else {
            btn.style.display = ''; // Show with instructions
        }
    }

    // ================================================
    // Register Service Worker
    // ================================================

    function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) return;

        navigator.serviceWorker.register('/sw.js', { scope: '/' })
            .then((registration) => {
                // Check for updates periodically
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (!newWorker) return;

                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            // A new version is ready — show subtle update notification
                            showUpdateNotification();
                        }
                    });
                });
            })
            .catch((err) => {
                console.warn('[PWA] Service Worker registration failed:', err);
            });
    }

    // ================================================
    // Show Update Notification (non-intrusive)
    // ================================================

    function showUpdateNotification() {
        if (document.getElementById('pwaUpdateToast')) return;

        const toast = document.createElement('div');
        toast.id = 'pwaUpdateToast';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'polite');
        toast.innerHTML = `
            <div class="pwa-update-toast">
                <span>🌿 يتوفر تحديث جديد لأثر طيب</span>
                <button onclick="window.location.reload()" class="pwa-update-btn">تحديث الآن</button>
                <button onclick="this.parentElement.parentElement.remove()" class="pwa-update-dismiss" aria-label="إغلاق">✕</button>
            </div>`;
        document.body.appendChild(toast);

        // Auto-dismiss after 30 seconds
        setTimeout(() => {
            if (document.getElementById('pwaUpdateToast')) {
                document.getElementById('pwaUpdateToast').remove();
            }
        }, 30000);
    }

    // ================================================
    // Initialize PWA Button in Navbar
    // ================================================

    function initInstallButton() {
        const btn = document.getElementById('pwaInstallBtn');
        if (!btn) return;

        // Detect current state and configure button
        const state = detectInstallState();
        installState = state; // Keep module-level state in sync

        if (state === 'installed') {
            btn.style.display = 'none';
            return;
        }

        btn.style.display = '';

        // Prevent duplicate listeners if init is called more than once
        btn.removeEventListener('click', onInstallBtnClick);
        btn.addEventListener('click', onInstallBtnClick);
    }

    function onInstallBtnClick(e) {
        e.preventDefault();
        // Re-evaluate state each click — deferredPrompt may have arrived since init
        if (deferredPrompt) {
            openInstallModal(); // Will show 'installable' state with native prompt button
        } else {
            openInstallModal();
        }
    }

    // ================================================
    // Listen for beforeinstallprompt (Chrome/Edge)
    // ================================================

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault(); // Don't auto-show the prompt
        deferredPrompt = e;
        installState = 'installable';

        // Update button to reflect installable state
        updateInstallButton('installable');
    });

    // ================================================
    // Listen for appinstalled event
    // ================================================

    window.addEventListener('appinstalled', () => {
        deferredPrompt = null;
        installState = 'installed';
        sessionStorage.setItem(INSTALL_DONE_KEY, '1');
        updateInstallButton('installed');
    });

    // ================================================
    // Initialize on DOM Ready
    // ================================================

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        registerServiceWorker();
        initInstallButton();
    }

})();
