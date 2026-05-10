import './bootstrap';

// Theme (light/dark)
function applyTheme(theme) {
    const root = document.documentElement;
    const isDark = theme === 'dark';
    root.classList.toggle('dark', isDark);
    root.style.colorScheme = isDark ? 'dark' : 'light';
}

function getPreferredTheme() {
    const stored = window.localStorage?.getItem?.('theme');
    if (stored === 'light' || stored === 'dark') return stored;
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

function setTheme(theme) {
    window.localStorage?.setItem?.('theme', theme);
    applyTheme(theme);
    document.querySelectorAll('[data-theme-icon]').forEach((el) => {
        el.classList.toggle('hidden', el.getAttribute('data-theme-icon') !== theme);
    });
    document.querySelectorAll('[data-theme-toggle-label]').forEach((el) => {
        el.textContent = theme === 'dark' ? 'Dark mode' : 'Light mode';
    });
}

document.addEventListener('click', (event) => {
    const btn = event.target?.closest?.('[data-theme-toggle]');
    if (!btn) return;

    const next = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
    setTheme(next);
});

/** Fixed-position toast; auto-dismiss after a few seconds. */
function showToast(message, type = 'info') {
    const root = document.getElementById('ui-toast-root');
    if (!root || !message) return;

    const styles = {
        error: 'border-rose-300/80 bg-rose-50 text-rose-950 dark:border-rose-800 dark:bg-rose-950/80 dark:text-rose-50',
        success:
            'border-emerald-300/80 bg-emerald-50 text-emerald-950 dark:border-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-50',
        info: 'border-ui-border bg-ui-surface text-ui-fg shadow-ui-sm',
    };

    const el = document.createElement('div');
    el.setAttribute('role', 'status');
    el.className = `pointer-events-auto max-w-sm rounded-lg border px-4 py-3 text-sm font-medium leading-relaxed shadow-lg ${
        styles[type] || styles.info
    }`;
    el.textContent = message;
    root.appendChild(el);

    window.setTimeout(() => {
        el.classList.add('opacity-0', 'transition-opacity', 'duration-300');
        window.setTimeout(() => el.remove(), 350);
    }, 4500);
}

window.showToast = showToast;

function normalizeQuestionTextClient(value) {
    return String(value ?? '')
        .trim()
        .replace(/\s+/g, ' ')
        .toLowerCase();
}

function initDuplicateQuestionHint() {
    const form = document.querySelector('form[data-question-duplicate-check]');
    if (!form) return;

    let existing = [];
    try {
        existing = JSON.parse(form.getAttribute('data-existing-normalized') || '[]');
    } catch {
        existing = [];
    }
    if (!Array.isArray(existing)) existing = [];

    const input = form.querySelector('[data-question-duplicate-input]');
    const hint = form.querySelector('[data-question-duplicate-hint]');
    if (!(input instanceof HTMLElement) || !(hint instanceof HTMLElement)) return;

    const refresh = () => {
        const n = normalizeQuestionTextClient(input.value);
        const dup = n !== '' && existing.includes(n);
        if (dup) {
            hint.textContent = 'That question already exists in this template.';
            hint.classList.remove('hidden');
            input.setAttribute('aria-invalid', 'true');
        } else {
            hint.classList.add('hidden');
            hint.textContent = '';
            input.removeAttribute('aria-invalid');
        }
    };

    input.addEventListener('input', refresh);
    refresh();
}

document.addEventListener('DOMContentLoaded', () => {
    initDuplicateQuestionHint();

    // Ensure icons reflect whichever theme was applied pre-load.
    const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
    applyTheme(current);
    document.querySelectorAll('[data-theme-icon]').forEach((el) => {
        el.classList.toggle('hidden', el.getAttribute('data-theme-icon') !== current);
    });
    document.querySelectorAll('[data-theme-toggle-label]').forEach((el) => {
        el.textContent = current === 'dark' ? 'Dark mode' : 'Light mode';
    });

    // If no stored preference, follow system changes live.
    try {
        const stored = window.localStorage?.getItem?.('theme');
        if (!stored && window.matchMedia) {
            const mq = window.matchMedia('(prefers-color-scheme: dark)');
            mq.addEventListener?.('change', (e) => setTheme(e.matches ? 'dark' : 'light'));
        }
    } catch (e) {}
});

// Forms: data-confirm, double-submit guard, submit loading state.
// PDF downloads use data-pdf-export (fetch) so the page does not hang on blob/new-tab flows.

function setButtonLoading(button, isLoading) {
    const loadingText = button.getAttribute('data-loading-text') || 'Working...';
    const original = button.getAttribute('data-original-text') || button.textContent?.trim() || '';

    if (isLoading) {
        button.setAttribute('data-original-text', original);
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-not-allowed');

        if (button.tagName === 'BUTTON') {
            button.textContent = loadingText;
        }
    } else {
        button.disabled = false;
        button.classList.remove('opacity-75', 'cursor-not-allowed');

        if (button.tagName === 'BUTTON') {
            button.textContent = button.getAttribute('data-original-text') || original;
        }
    }
}

/** Clear stuck submit locks after bfcache/navigation quirks */
function resetAllSubmitLocks() {
    document.querySelectorAll('form[data-submitting="1"]').forEach((form) => {
        form.dataset.submitting = '';
        form.querySelectorAll('button[type="submit"]').forEach((btn) => setButtonLoading(btn, false));
    });
}

window.addEventListener('pageshow', () => {
    resetAllSubmitLocks();
});

const PDF_FETCH_TIMEOUT_MS = 120000;

const PDF_POLL_INTERVAL_MS = 1500;

const PDF_POLL_MAX_MS = 300000;
const PDF_QUEUE_STALL_MS = 75000;
const PDF_POLL_FETCH_TIMEOUT_MS = 25000;
const PDF_PROCESSING_STALL_MS = 180000;

function pdfExportDebugEnabled() {
    try {
        return window.localStorage?.getItem?.('PDF_EXPORT_DEBUG') === '1';
    } catch (e) {
        return false;
    }
}

function resolveAbsoluteUrl(url) {
    if (!url || typeof url !== 'string') {
        return url;
    }
    try {
        return new URL(url, window.location.origin).href;
    } catch {
        return url;
    }
}

async function fetchJsonWithTimeout(url, options, timeoutMs) {
    const ctrl = new AbortController();
    const timer = window.setTimeout(() => ctrl.abort(), timeoutMs);

    try {
        return await fetch(url, {
            ...options,
            signal: ctrl.signal,
        });
    } finally {
        window.clearTimeout(timer);
    }
}

async function pollExportUntilReady(statusUrl) {
    const absoluteUrl = resolveAbsoluteUrl(statusUrl);
    const started = Date.now();
    let processingSince = null;

    while (Date.now() - started < PDF_POLL_MAX_MS) {
        let res;
        try {
            res = await fetchJsonWithTimeout(
                absoluteUrl,
                {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                },
                PDF_POLL_FETCH_TIMEOUT_MS,
            );
        } catch (ee) {
            if (pdfExportDebugEnabled()) {
                console.warn('[pdf-export] poll fetch error', ee);
            }
            if (ee?.name === 'AbortError') {
                throw new Error(
                    'Status check timed out. The server may be overloaded or unreachable — refresh and try again.',
                );
            }
            throw ee;
        }

        if (!res.ok) {
            const text = await res.text().catch(() => '');
            if (pdfExportDebugEnabled()) {
                console.warn('[pdf-export] poll non-OK', res.status, text.slice(0, 400));
            }
            throw new Error(`Status check failed (${res.status})`);
        }

        const payload = await res.json();

        if (payload.success === false) {
            throw new Error(payload.message || 'Export status request failed.');
        }

        const data = payload.data ?? payload;
        const status = data?.status;

        if (typeof status !== 'string') {
            throw new Error('Invalid export status response. Refresh and try again.');
        }

        const known = ['queued', 'processing', 'completed', 'failed'];
        if (!known.includes(status)) {
            throw new Error(`Unexpected export status: ${status}`);
        }

        if (pdfExportDebugEnabled()) {
            console.debug('[pdf-export] poll status', status, data);
        }

        if (status === 'completed') {
            if (!data.download_url) {
                throw new Error(
                    'Export finished but no download link was returned. Check storage permissions and server logs.',
                );
            }
            window.location.assign(data.download_url);

            return;
        }

        if (status === 'failed') {
            throw new Error(data.error || 'Export failed.');
        }

        if (status === 'queued' && Date.now() - started > PDF_QUEUE_STALL_MS) {
            throw new Error(
                'Export is still queued — no background worker picked up the job. Run `php artisan queue:work`, or set QUEUE_CONNECTION=sync (or APP_ENV=local with default snapshot sync) in `.env`.',
            );
        }

        if (status === 'processing') {
            processingSince ??= Date.now();
            if (Date.now() - processingSince > PDF_PROCESSING_STALL_MS) {
                throw new Error(
                    'PDF generation is taking unusually long (>3 min processing). Your queue worker may be stuck, or DomPDF timed out — check storage/logs/laravel.log and php artisan queue:failed.',
                );
            }
        } else {
            processingSince = null;
        }

        await new Promise((r) => setTimeout(r, PDF_POLL_INTERVAL_MS));
    }

    throw new Error(
        'Export is taking longer than expected. Ensure `php artisan queue:work` is running, then retry.',
    );
}

async function handlePdfExport(form) {
    if (form.dataset.pdfInFlight === '1') {
        return;
    }

    form.dataset.pdfInFlight = '1';

    const buttons = form.querySelectorAll('button[type="submit"]');
    const resetFlight = () => {
        form.dataset.pdfInFlight = '';
    };

    const resetButtons = () => {
        buttons.forEach((btn) => setButtonLoading(btn, false));
    };

    buttons.forEach((btn) => setButtonLoading(btn, true));

    const method = (form.getAttribute('method') || 'GET').toUpperCase();

    const controller = new AbortController();
    const abortTimer = window.setTimeout(() => controller.abort(), PDF_FETCH_TIMEOUT_MS);
    let reachedResponse = false;

    try {
        if (pdfExportDebugEnabled()) {
            console.debug('[pdf-export] submit', method, form.action);
        }

        const fetchInit = {
            credentials: 'same-origin',
            signal: controller.signal,
            headers: {
                Accept: 'application/json,application/pdf;q=0.9,application/octet-stream;q=0.8,*/*;q=0.7',
                'X-Requested-With': 'XMLHttpRequest',
            },
        };

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        let res;

        if (method === 'GET') {
            const url = new URL(form.action, window.location.origin);
            const fd = new FormData(form);
            fd.forEach((value, key) => {
                url.searchParams.set(key, value);
            });

            res = await fetch(url.toString(), {
                ...fetchInit,
                method: 'GET',
            });
        } else {
            const fd = new FormData(form);
            res = await fetch(form.action, {
                ...fetchInit,
                method,
                body: fd,
                headers: {
                    ...fetchInit.headers,
                    ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                },
            });
        }

        reachedResponse = true;
        window.clearTimeout(abortTimer);

        const ct = (res.headers.get('Content-Type') || '').toLowerCase();

        if (ct.includes('application/json')) {
            const json = await res.json();

            if (!res.ok) {
                let msg = json.message || json.error || `Server responded with ${res.status}`;
                if (json.errors && typeof json.errors === 'object') {
                    const flat = Object.values(json.errors).flat();
                    if (flat.length > 0 && typeof flat[0] === 'string') {
                        msg = flat[0];
                    }
                }
                throw new Error(msg);
            }

            const data = json.data ?? {};

            if (data.download_url && data.status === 'completed') {
                window.location.assign(data.download_url);
                return;
            }

            const statusUrl = data.status_url || json.status_url;
            const needsPoll =
                Boolean(statusUrl) &&
                (data.async === true ||
                    json.async === true ||
                    res.status === 202 ||
                    data.status === 'queued' ||
                    data.status === 'processing');

            if (needsPoll) {
                buttons.forEach((btn) => {
                    if (btn.tagName === 'BUTTON') {
                        btn.setAttribute('data-loading-text', 'Preparing export…');
                    }
                });
                buttons.forEach((btn) => setButtonLoading(btn, true));
                await pollExportUntilReady(statusUrl);

                return;
            }

            throw new Error(data.message || json.message || 'Unexpected JSON response from export.');
        }

        if (!res.ok) {
            throw new Error(`Server responded with ${res.status}`);
        }

        if (!ct.includes('pdf') && !ct.includes('octet-stream') && !ct.includes('application/download')) {
            await res.text();
            throw new Error('Unexpected response — not a PDF');
        }

        const blob = await res.blob();

        const blobUrl = window.URL.createObjectURL(blob);
        // Avoid `noopener` in the window.open features string: in Chromium it returns `null` even when
        // a new tab opened, which incorrectly triggered `location.assign` and navigated THIS tab too.
        const newWin = window.open(blobUrl, '_blank');
        if (newWin) {
            try {
                newWin.opener = null;
            } catch (e) {
                /* ignore */
            }
        } else {
            // Popup blocked: same-tab fallback only when no window was created.
            window.location.assign(blobUrl);
        }
        // Revoke after the viewer has time to read the blob (avoid revoking while a new tab loads).
        window.setTimeout(() => window.URL.revokeObjectURL(blobUrl), 600000);
    } catch (err) {
        if (!reachedResponse) {
            window.clearTimeout(abortTimer);
        }
        if (err?.name === 'AbortError') {
            window.showToast?.(
                'PDF export timed out. Try again with fewer filters or a smaller scope.',
                'error',
            );
        } else {
            console.error(err);
            window.showToast?.(err?.message || 'PDF export failed. Please try again.', 'error');
        }
    } finally {
        resetFlight();
        resetButtons();
    }
}

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    if (form.hasAttribute('data-pdf-export')) {
        event.preventDefault();
        void handlePdfExport(form);
        return;
    }

    // Confirmation dialog (opt-in).
    const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
    const confirmMessage =
        submitter?.getAttribute?.('data-confirm') ||
        form.getAttribute('data-confirm');
    if (confirmMessage && !window.confirm(confirmMessage)) {
        event.preventDefault();
        return;
    }

    // Prevent double submits.
    if (form.dataset.submitting === '1') {
        event.preventDefault();
        return;
    }
    form.dataset.submitting = '1';

    // Disable submit buttons to show a basic loading state (safe: page will reload or navigate).
    const submitButtons = form.querySelectorAll('button[type="submit"]');
    submitButtons.forEach((btn) => setButtonLoading(btn, true));
});

// Mobile navigation toggle
document.addEventListener('click', (event) => {
    const button = event.target?.closest?.('[data-mobile-menu-button]');
    const menu = document.querySelector('[data-mobile-menu]');
    if (!menu) return;

    // Toggle when clicking the hamburger button.
    if (button) {
        const isOpen = !menu.classList.contains('hidden');
        menu.classList.toggle('hidden', isOpen);
        button.setAttribute('aria-expanded', String(!isOpen));
        return;
    }

    // Close when clicking outside the menu + button.
    const isOpen = !menu.classList.contains('hidden');
    if (!isOpen) return;

    const anyButton = document.querySelector('[data-mobile-menu-button]');
    const clickedInsideMenu = !!event.target?.closest?.('[data-mobile-menu]');
    const clickedButton = !!event.target?.closest?.('[data-mobile-menu-button]');

    if (!clickedInsideMenu && !clickedButton) {
        menu.classList.add('hidden');
        anyButton?.setAttribute('aria-expanded', 'false');
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;

    const menu = document.querySelector('[data-mobile-menu]');
    if (!menu || menu.classList.contains('hidden')) return;

    menu.classList.add('hidden');
    document.querySelector('[data-mobile-menu-button]')?.setAttribute('aria-expanded', 'false');
});

// Copy-to-clipboard buttons (opt-in via data-copy)
document.addEventListener('click', async (event) => {
    const button = event.target?.closest?.('[data-copy]');
    if (!button) return;

    const text = button.getAttribute('data-copy') || '';
    if (!text) return;

    const original = button.textContent?.trim() || 'Copy';
    const label = button.getAttribute('data-copy-label') || 'Value';

    try {
        await navigator.clipboard.writeText(text);
        button.textContent = 'Copied';
        setTimeout(() => {
            button.textContent = original;
        }, 1200);
    } catch (e) {
        window.prompt(`Copy ${label}:`, text);
    }
});

// Password visibility toggle (opt-in via data-toggle-password)
document.addEventListener('click', (event) => {
    const button = event.target?.closest?.('[data-toggle-password]');
    if (!button) return;

    const inputId = button.getAttribute('data-toggle-password');
    if (!inputId) return;

    const input = document.getElementById(inputId);
    if (!(input instanceof HTMLInputElement)) return;

    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';

    button.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');

    const showIcon = button.querySelector('[data-password-icon="show"]');
    const hideIcon = button.querySelector('[data-password-icon="hide"]');
    showIcon?.classList.toggle('hidden', isHidden);
    hideIcon?.classList.toggle('hidden', !isHidden);
});

// Credential autofill (login page)
document.addEventListener('click', (event) => {
    const btn = event.target?.closest?.('[data-fill-credentials]');
    if (!btn) return;

    const email = btn.getAttribute('data-fill-email') || '';
    const password = btn.getAttribute('data-fill-password') || '';

    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    if (emailInput instanceof HTMLInputElement) {
        emailInput.value = email;
        emailInput.dispatchEvent(new Event('input', { bubbles: true }));
        emailInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    if (passwordInput instanceof HTMLInputElement) {
        passwordInput.value = password;
        passwordInput.dispatchEvent(new Event('input', { bubbles: true }));
        passwordInput.dispatchEvent(new Event('change', { bubbles: true }));
        passwordInput.focus();
    }
});
