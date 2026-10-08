import { completeTwoFactorLogin, login } from '../lib/auth';
import { flash } from '../lib/session';
import { bindPasswordToggles, fillForm, formValues, handleError, qs, qsa, showFormErrors, toast, validateForm, withBusyButton } from '../lib/ui';

/** Only local paths are allowed as the redirect target (no open redirect to other sites). */
function redirectTarget() {
    const next = new URLSearchParams(window.location.search).get('next');

    return next && next.startsWith('/') && !next.startsWith('//') ? next : '/dashboard';
}

function finishLogin(user) {
    flash(`Sveiki, ${user.name.split(' ')[0]}! Sėkmingai prisijungėte.`);
    window.location.href = redirectTarget();
}

export default function initLoginPage() {
    const loginForm = qs('#login-form');
    const twoFactorForm = qs('#two-factor-form');
    let twoFactorToken = null;

    bindPasswordToggles(loginForm);

    qsa('[data-demo]').forEach((button) => {
        button.addEventListener('click', () => {
            fillForm(loginForm, { email: button.dataset.demo, password: button.dataset.password });
            qs('button[type=submit]', loginForm).focus();
        });
    });

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!validateForm(loginForm)) {
            return;
        }

        const { email, password } = formValues(loginForm);

        await withBusyButton(qs('button[type=submit]', loginForm), async () => {
            try {
                const result = await login(email, password);

                if (result.twoFactorToken) {
                    twoFactorToken = result.twoFactorToken;
                    loginForm.hidden = true;
                    twoFactorForm.hidden = false;
                    twoFactorForm.classList.add('fade-in');
                    qs('input', twoFactorForm).focus();

                    return;
                }

                finishLogin(result.user);
            } catch (error) {
                if (error.status === 401) {
                    showFormErrors(loginForm, { password: 'Neteisingas el. paštas arba slaptažodis.' });
                    toast('Neteisingas el. paštas arba slaptažodis.', 'error');
                } else {
                    handleError(error, loginForm);
                }
            }
        });
    });

    twoFactorForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!validateForm(twoFactorForm)) {
            return;
        }

        await withBusyButton(qs('button[type=submit]', twoFactorForm), async () => {
            try {
                finishLogin((await completeTwoFactorLogin(twoFactorToken, formValues(twoFactorForm).code)).user);
            } catch (error) {
                if (error.status === 401) {
                    toast('Laikas baigėsi. Prisijunkite iš naujo.', 'error');
                    showLoginStep();
                } else {
                    handleError(error, twoFactorForm);
                }
            }
        });
    });

    const showLoginStep = () => {
        twoFactorForm.hidden = true;
        twoFactorForm.reset();
        loginForm.hidden = false;
        twoFactorToken = null;
    };

    qs('[data-action="back-to-login"]').addEventListener('click', showLoginStep);
}
