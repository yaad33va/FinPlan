/**
 * Client-side session state.
 *
 * - The access token (JWT, 15 min) is kept in sessionStorage: it survives page navigation in
 *   the same tab, but is removed when the tab is closed. It is sent in the Authorization header.
 * - The refresh token (7 days) is an HttpOnly cookie set by the server; JavaScript cannot read it.
 *   POST /api/v1/auth/refresh sends it automatically and returns a new access token.
 * - localStorage only remembers *whether* the user logged in, so guests do not trigger
 *   a pointless refresh request on every page.
 */
const ACCESS_TOKEN_KEY = 'finplan.accessToken';
const HAS_SESSION_KEY = 'finplan.hasSession';

let refreshPromise = null;

export function getAccessToken() {
    return sessionStorage.getItem(ACCESS_TOKEN_KEY);
}

export function setAccessToken(token) {
    sessionStorage.setItem(ACCESS_TOKEN_KEY, token);
    localStorage.setItem(HAS_SESSION_KEY, '1');
}

export function clearSession() {
    sessionStorage.removeItem(ACCESS_TOKEN_KEY);
    localStorage.removeItem(HAS_SESSION_KEY);
}

/** Reads the (unverified) claims from the JWT payload – used only to adapt the UI. */
export function decodeToken(token) {
    try {
        const base64 = token.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');
        const bytes = Uint8Array.from(atob(base64), (char) => char.charCodeAt(0));

        return JSON.parse(new TextDecoder().decode(bytes));
    } catch {
        return null;
    }
}

/** The logged-in user taken from the token claims: { id, name, role, expiresAt }. */
export function currentUser() {
    const token = getAccessToken();
    const claims = token ? decodeToken(token) : null;

    if (!claims) {
        return null;
    }

    return {
        id: Number(claims.sub),
        name: claims.name,
        role: claims.role,
        isAdmin: claims.role === 'admin',
        expiresAt: claims.exp * 1000,
    };
}

/**
 * Gets a new access token with the refresh token cookie. Parallel callers share one request,
 * because the server rotates the refresh token on every call.
 */
export function refreshSession() {
    refreshPromise ??= fetch('/api/v1/auth/refresh', {
        method: 'POST',
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    })
        .then(async (response) => {
            if (!response.ok) {
                clearSession();

                return false;
            }

            setAccessToken((await response.json()).access_token);

            return true;
        })
        .catch(() => false)
        .finally(() => {
            refreshPromise = null;
        });

    return refreshPromise;
}

/**
 * Called on every page load: keeps a still valid access token, otherwise tries the refresh token.
 * Returns the current user or null (guest).
 */
export async function restoreSession() {
    const user = currentUser();

    if (user && user.expiresAt - Date.now() > 30_000) {
        return user;
    }

    if (!user && localStorage.getItem(HAS_SESSION_KEY) !== '1') {
        return null;
    }

    return (await refreshSession()) ? currentUser() : null;
}

/** One-time message shown on the next page (e.g. after a redirect). */
export function flash(message, type = 'success') {
    sessionStorage.setItem('finplan.flash', JSON.stringify({ message, type }));
}

export function takeFlash() {
    const value = sessionStorage.getItem('finplan.flash');
    sessionStorage.removeItem('finplan.flash');

    return value ? JSON.parse(value) : null;
}
