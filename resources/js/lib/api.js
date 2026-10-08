/**
 * Small fetch() wrapper for the REST API: adds the Bearer token, sends/parses JSON and,
 * when the access token has expired (401), refreshes it once and repeats the request.
 */
import { clearSession, flash, getAccessToken, refreshSession } from './session';

const BASE_URL = '/api/v1';

export class ApiError extends Error {
    constructor(status, body) {
        super(body?.message ?? `HTTP ${status}`);
        this.status = status;
        this.errors = body?.errors ?? {};
        this.body = body;
    }
}

/**
 * @param {string} path      e.g. "/categories/3/budgets"
 * @param {object} options   { method, body, query, auth }
 */
export async function api(path, options = {}) {
    const { method = 'GET', body, query, auth = true } = options;

    const url = new URL(BASE_URL + path, window.location.origin);
    Object.entries(query ?? {}).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            url.searchParams.set(key, value);
        }
    });

    const headers = { Accept: 'application/json' };
    if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
    }
    if (auth && getAccessToken()) {
        headers.Authorization = `Bearer ${getAccessToken()}`;
    }

    let response;
    try {
        response = await fetch(url, {
            method,
            headers,
            body: body !== undefined ? JSON.stringify(body) : undefined,
            credentials: 'same-origin',
        });
    } catch {
        throw new ApiError(0, { message: 'Nepavyko susisiekti su serveriu. Patikrinkite interneto ryšį.' });
    }

    if (response.status === 401 && auth && !options.retried) {
        if (await refreshSession()) {
            return api(path, { ...options, retried: true });
        }

        clearSession();
        flash('Sesija baigėsi. Prisijunkite iš naujo.', 'info');
        window.location.replace(`/login?next=${encodeURIComponent(window.location.pathname + window.location.search)}`);

        return new Promise(() => {});
    }

    const data = response.status === 204 ? null : await response.json().catch(() => null);

    if (!response.ok) {
        throw new ApiError(response.status, data);
    }

    return data;
}
