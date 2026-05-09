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
// - Disable submit buttons while submitting
// - Standard confirmation dialogs via data-confirm
// - Prevent double submits

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

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

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

    // Disable submit buttons to show a basic loading state.
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

