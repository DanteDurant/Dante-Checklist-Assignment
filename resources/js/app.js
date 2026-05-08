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

