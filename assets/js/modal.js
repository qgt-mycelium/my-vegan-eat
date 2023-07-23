// Show modal
document.querySelectorAll('[data-modal-target]').forEach(button => {
    button.addEventListener('click', () => {
        const modal = document.getElementById(button.dataset.modalTarget);
        if (modal) {
            modal.classList.remove('hidden');
            modal.removeAttribute('aria-hidden');
            modal.setAttribute('aria-modal', 'true');
            document.body.classList.add('overflow-hidden');
        }
    });
});

// Hide modal
document.querySelectorAll('[data-modal-hide]').forEach(hide => {
    hide.addEventListener('click', () => {
        const modal = hide.closest('aside');
        modal.classList.add('hidden');
        modal.removeAttribute('aria-modal');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    });
});