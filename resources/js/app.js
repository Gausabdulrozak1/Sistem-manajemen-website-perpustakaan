const toggle = document.querySelector('[data-sidebar-toggle]');
const sidebar = document.querySelector('#main-sidebar');
const backdrop = document.querySelector('.sidebar-backdrop');

const setSidebarOpen = (isOpen) => {
    sidebar?.classList.toggle('is-open', isOpen);
    backdrop?.classList.toggle('is-visible', isOpen);
    toggle?.setAttribute('aria-expanded', String(isOpen));
};

toggle?.addEventListener('click', () => {
    setSidebarOpen(toggle.getAttribute('aria-expanded') !== 'true');
});

backdrop?.addEventListener('click', () => setSidebarOpen(false));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setSidebarOpen(false);
});

document.querySelectorAll('[data-confirm-delete]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const confirmationMessage = form.dataset.confirmDelete;

        if (!window.confirm(confirmationMessage)) {
            event.preventDefault();
        }
    });
});
