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

document.addEventListener('DOMContentLoaded', () => {
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

// Basic UI enhancements (no framework):
// - Disable submit buttons while submitting (full page navigations only)
// - Standard confirmation dialogs via data-confirm
// - Prevent double submits
//
// IMPORTANT: GET forms that return Content-Disposition file downloads do NOT unload the page.
// Those forms are handled separately via data-pdf-export (fetch + blob) so loading state always clears.

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

/** Restore stuck loading states after back-forward cache or interrupted navigations */
function resetAllSubmitLocks() {
    document.querySelectorAll('form[data-submitting="1"]').forEach((form) => {
        form.dataset.submitting = '';
        form.querySelectorAll('button[type="submit"]').forEach((btn) => setButtonLoading(btn, false));
    });
}

window.addEventListener('pageshow', () => {
    resetAllSubmitLocks();
});

function parseFilenameFromContentDisposition(header) {
    if (!header || typeof header !== 'string') {
        return 'export.pdf';
    }
    const utf8 = /filename\*=UTF-8''([^;\n]+)/i.exec(header);
    if (utf8) {
        try {
            return decodeURIComponent(utf8[1].trim());
        } catch {
            return 'export.pdf';
        }
    }
    const quoted = /filename="([^"]+)"/i.exec(header);
    if (quoted) {
        return quoted[1];
    }
    const loose = /filename=([^;\n]+)/i.exec(header);
    if (loose) {
        return loose[1].replace(/^["']|["']$/g, '').trim();
    }
    return 'export.pdf';
}

const PDF_FETCH_TIMEOUT_MS = 120000;

async function handlePdfExport(form) {
    if (form.dataset.pdfInFlight === '1') {
        return;
    }

    const method = (form.getAttribute('method') || 'GET').toUpperCase();
    if (method !== 'GET') {
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

    const controller = new AbortController();
    const abortTimer = window.setTimeout(() => controller.abort(), PDF_FETCH_TIMEOUT_MS);

    try {
        const url = new URL(form.action, window.location.origin);
        const fd = new FormData(form);
        fd.forEach((value, key) => {
            url.searchParams.set(key, value);
        });

        const res = await fetch(url.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            signal: controller.signal,
            headers: {
                Accept: 'application/pdf,application/octet-stream;q=0.9,*/*;q=0.8',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        window.clearTimeout(abortTimer);

        if (!res.ok) {
            throw new Error(`Server responded with ${res.status}`);
        }

        const ct = (res.headers.get('Content-Type') || '').toLowerCase();
        if (!ct.includes('pdf') && !ct.includes('octet-stream') && !ct.includes('application/download')) {
            await res.text();
            throw new Error('Unexpected response — not a PDF');
        }

        const blob = await res.blob();
        const disposition = res.headers.get('Content-Disposition') || '';
        const filename = parseFilenameFromContentDisposition(disposition);

        const blobUrl = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = blobUrl;
        a.download = filename;
        a.rel = 'noopener';
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.setTimeout(() => URL.revokeObjectURL(blobUrl), 60000);
    } catch (err) {
        window.clearTimeout(abortTimer);
        if (err?.name === 'AbortError') {
            window.alert('PDF export timed out. Please try again with fewer filters or a smaller scope.');
        } else {
            console.error(err);
            window.alert('PDF export failed. Please try again.');
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
