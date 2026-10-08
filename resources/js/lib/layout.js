/**
 * Header behaviour: hamburger menu, active menu item, role based visibility, logout.
 */
import { logout } from './auth';
import { qs, qsa } from './ui';

export function initNavigation() {
    const toggle = qs('.nav-toggle');
    const setOpen = (open) => {
        document.body.classList.toggle('nav-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', open ? 'Uždaryti meniu' : 'Atidaryti meniu');
    };

    toggle.addEventListener('click', () => setOpen(!document.body.classList.contains('nav-open')));
    qsa('.main-nav a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
    window.matchMedia('(min-width: 769px)').addEventListener('change', (event) => event.matches && setOpen(false));
    document.addEventListener('keydown', (event) => event.key === 'Escape' && setOpen(false));

    const page = document.body.dataset.page;
    qsa('[data-nav]').forEach((link) => {
        link.classList.toggle('is-active', link.dataset.nav.split(' ').includes(page));
    });

    qsa('[data-action="logout"]').forEach((button) => button.addEventListener('click', () => logout()));
}

/**
 * Shows the elements meant for the current role: data-auth="guest" | "user" | "admin".
 */
export function applyAuthState(user) {
    qsa('[data-auth]').forEach((element) => {
        const roles = element.dataset.auth.split(' ');
        const visible =
            (roles.includes('guest') && !user) ||
            (roles.includes('user') && Boolean(user)) ||
            (roles.includes('admin') && user?.isAdmin);

        element.classList.toggle('auth-visible', Boolean(visible));
    });

    qsa('[data-user-name]').forEach((element) => {
        element.textContent = user ? user.name.split(' ')[0] : element.textContent;
    });
}
