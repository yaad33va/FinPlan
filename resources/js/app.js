/**
 * FinPlan front-end entry point (bundled by Vite).
 *
 * Every Blade page has <body data-page="..." data-access="...">. On load we:
 * 1. restore the session (access token or refresh cookie),
 * 2. redirect if the page is not meant for the current role,
 * 3. run the page module from ./pages, which loads its data from the REST API.
 */
import { renderIcons } from './lib/icons';
import { applyAuthState, initNavigation } from './lib/layout';
import { flash, restoreSession, takeFlash } from './lib/session';
import { toast } from './lib/ui';

// Page modules are loaded lazily: each page downloads only its own code.
const pages = import.meta.glob('./pages/*.js');

async function boot() {
    const { page, access } = document.body.dataset;

    initNavigation();
    renderIcons();

    const user = await restoreSession();
    applyAuthState(user);

    if (access === 'guest' && user) {
        window.location.replace('/dashboard');

        return;
    }

    if ((access === 'user' || access === 'admin') && !user) {
        flash('Prisijunkite, kad galėtumėte tęsti.', 'info');
        window.location.replace(`/login?next=${encodeURIComponent(window.location.pathname + window.location.search)}`);

        return;
    }

    if (access === 'admin' && !user.isAdmin) {
        flash('Šis puslapis skirtas tik administratoriui.', 'error');
        window.location.replace('/dashboard');

        return;
    }

    const message = takeFlash();
    if (message) {
        toast(message.message, message.type);
    }

    const loadPage = pages[`./pages/${page}.js`];
    if (loadPage) {
        const module = await loadPage();
        await module.default({ user });
    }
}

document.addEventListener('DOMContentLoaded', boot);
