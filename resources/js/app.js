import './bootstrap';

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
    const confirmMessage = form.getAttribute('data-confirm');
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

