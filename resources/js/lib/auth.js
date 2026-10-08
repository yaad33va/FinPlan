/**
 * Register, login (with optional 2FA step) and logout.
 */
import { api } from './api';
import { clearSession, flash, setAccessToken } from './session';

export async function register(values) {
    const response = await api('/auth/register', { method: 'POST', body: values, auth: false });
    setAccessToken(response.access_token);

    return response.data;
}

/**
 * Returns { twoFactorToken } when a 2FA code is still needed, otherwise { user }.
 */
export async function login(email, password) {
    const response = await api('/auth/login', { method: 'POST', body: { email, password }, auth: false });

    if (response.two_factor_required) {
        return { twoFactorToken: response.two_factor_token };
    }

    setAccessToken(response.access_token);

    return { user: response.data };
}

export async function completeTwoFactorLogin(twoFactorToken, code) {
    const response = await api('/auth/two-factor/challenge', {
        method: 'POST',
        body: { two_factor_token: twoFactorToken, code },
        auth: false,
    });
    setAccessToken(response.access_token);

    return { user: response.data };
}

export async function logout() {
    try {
        await api('/auth/logout', { method: 'POST' });
    } finally {
        clearSession();
        flash('Atsijungėte. Iki pasimatymo!', 'info');
        window.location.href = '/';
    }
}
