const TOAST_ICONS = {
    default: 'info',
    success: 'check_circle',
    danger: 'error',
};

function toastStack() {
    let stack = document.querySelector('[data-toast-stack]');

    if (!stack) {
        stack = document.createElement('div');
        stack.setAttribute('data-toast-stack', '');
        stack.className =
            'pointer-events-none fixed inset-x-0 bottom-0 z-[60] flex flex-col items-center gap-2 p-4 sm:items-end sm:p-6';
        document.body.appendChild(stack);
    }

    return stack;
}

export function perkociToast(message, type = 'default', duration = 3600) {
    if (!message) return;

    const el = document.createElement('div');
    el.className =
        'toast' +
        (type === 'success' ? ' toast-success' : type === 'danger' ? ' toast-danger' : '');
    el.setAttribute('role', 'status');
    el.setAttribute('aria-live', 'polite');

    const icon = document.createElement('span');
    icon.className = 'ms mt-px text-[18px] leading-none';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = TOAST_ICONS[type] || TOAST_ICONS.default;

    const text = document.createElement('p');
    text.className = 'flex-1 pt-px';
    text.textContent = message;

    el.append(icon, text);
    toastStack().appendChild(el);

    window.setTimeout(() => {
        el.style.transition = 'opacity .18s ease, transform .18s ease';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-6px)';
        window.setTimeout(() => el.remove(), 200);
    }, duration);
}

window.perkociToast = perkociToast;

/* ---------- Dialog (modal konfirmasi) ---------- */

function openModal(id) {
    const dialog = id ? document.getElementById(id) : null;

    if (dialog && typeof dialog.showModal === 'function') {
        dialog.showModal();
    }
}

document.addEventListener('click', (event) => {
    const opener = event.target.closest('[data-modal-open]');

    if (opener) {
        event.preventDefault();
        openModal(opener.getAttribute('data-modal-open'));
        return;
    }

    const closer = event.target.closest('[data-modal-close]');

    if (closer) {
        const dialog = closer.closest('dialog');
        if (dialog) dialog.close();
        return;
    }

    // Klik pada area backdrop (target = <dialog> itu sendiri) menutup modal.
    if (event.target instanceof HTMLDialogElement && event.target.hasAttribute('data-modal')) {
        event.target.close();
    }
});

/* ---------- Drawer sidebar (mobile) ---------- */

const drawerPanel = () => document.querySelector('[data-drawer-panel]');
const drawerBackdrop = () => document.querySelector('[data-drawer-backdrop]');

function setDrawer(open) {
    const panel = drawerPanel();
    const backdrop = drawerBackdrop();

    if (!panel) return;

    const isDesktop = window.matchMedia('(min-width: 1024px)').matches;
    const shouldOpen = open && !isDesktop;

    panel.classList.toggle('-translate-x-full', !shouldOpen);

    if (backdrop) {
        backdrop.classList.toggle('hidden', !shouldOpen);
        backdrop.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
    }

    document.documentElement.classList.toggle('overflow-hidden', shouldOpen);
}

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-drawer-toggle]')) {
        const panel = drawerPanel();
        setDrawer(panel ? panel.classList.contains('-translate-x-full') : false);
        return;
    }

    if (
        event.target.closest('[data-drawer-close]') ||
        event.target.closest('[data-drawer-backdrop]') ||
        event.target.closest('[data-drawer-panel] a')
    ) {
        setDrawer(false);
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setDrawer(false);
});

/* ---------- Toggle password ---------- */

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');

    if (!toggle) return;

    const input = document.getElementById(toggle.getAttribute('data-password-toggle'));

    if (!input) return;

    const show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    toggle.setAttribute('aria-pressed', show ? 'true' : 'false');

    const icon = toggle.querySelector('.ms');
    if (icon) icon.textContent = show ? 'visibility_off' : 'visibility';
});

/* ---------- Toast deklaratif ---------- */

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-toast-message]');

    if (!trigger) return;

    if (trigger.tagName === 'A') event.preventDefault();

    perkociToast(trigger.getAttribute('data-toast-message'), trigger.getAttribute('data-toast-type') || 'default');
});

/* ---------- Validasi & loading form login preview ---------- */

document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-preview-auth]');

    if (!form) return;

    event.preventDefault();

    const email = form.querySelector('[name="email"]');
    const password = form.querySelector('[name="password"]');
    const emailError = document.getElementById('login-email-error');
    const passwordError = document.getElementById('login-password-error');
    const alertWrap = form.querySelector('[data-login-alert-wrap]');

    const clearField = (input, errorEl) => {
        if (!input) return;

        input.classList.remove('field-invalid');
        input.removeAttribute('aria-invalid');

        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
        }
    };

    const showFieldError = (input, errorEl, message) => {
        if (!input) return;

        input.classList.add('field-invalid');
        input.setAttribute('aria-invalid', 'true');

        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
            input.setAttribute('aria-describedby', errorEl.id);
        }
    };

    clearField(email, emailError);
    clearField(password, passwordError);

    if (alertWrap) alertWrap.classList.add('hidden');

    const emailValue = (email && email.value ? email.value : '').trim();
    const passwordValue = password ? password.value : '';

    let firstInvalid = null;

    if (!emailValue) {
        showFieldError(email, emailError, 'Email atau username wajib diisi.');
        firstInvalid = firstInvalid || email;
    } else if (emailValue.includes('@') && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
        showFieldError(email, emailError, 'Format email tidak valid.');
        firstInvalid = firstInvalid || email;
    }

    if (!passwordValue) {
        showFieldError(password, passwordError, 'Password wajib diisi.');
        firstInvalid = firstInvalid || password;
    }

    if (firstInvalid) {
        firstInvalid.focus();
        return;
    }

    const button = form.querySelector('[type="submit"]');

    if (!button || button.disabled) return;

    const originalContent = button.innerHTML;

    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    button.innerHTML = '<span class="login-spinner" aria-hidden="true"></span><span>Memproses...</span>';

    window.setTimeout(() => {
        button.disabled = false;
        button.removeAttribute('aria-busy');
        button.innerHTML = originalContent;

        if (alertWrap) {
            alertWrap.classList.remove('hidden');

            const alert = alertWrap.querySelector('[role="alert"]');
            if (alert) alert.focus();
        }
    }, 1200);
});

