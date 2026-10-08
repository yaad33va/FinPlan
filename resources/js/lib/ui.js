/**
 * Shared UI helpers: DOM building, toasts, modals, forms, formatting and pagination.
 * User data is always inserted with textContent (never innerHTML) to prevent XSS.
 */
import { icon } from './icons';

/* ------------------------------------------------------------------------
 * DOM
 * --------------------------------------------------------------------- */

/**
 * h('a', { class: 'btn', href: '/x', onClick: fn }, 'Text', childNode)
 */
export function h(tag, props = {}, ...children) {
    const element = document.createElement(tag);

    Object.entries(props ?? {}).forEach(([key, value]) => {
        if (value === undefined || value === null || value === false) {
            return;
        }
        if (key === 'class') {
            element.className = value;
        } else if (key === 'dataset') {
            Object.assign(element.dataset, value);
        } else if (key === 'style' && typeof value === 'object') {
            Object.entries(value).forEach(([property, propertyValue]) => {
                if (property.startsWith('--')) {
                    element.style.setProperty(property, propertyValue);
                } else {
                    element.style[property] = propertyValue;
                }
            });
        } else if (key.startsWith('on') && typeof value === 'function') {
            element.addEventListener(key.slice(2).toLowerCase(), value);
        } else {
            element.setAttribute(key, value === true ? '' : value);
        }
    });

    element.append(...children.flat().filter((child) => child !== null && child !== undefined && child !== false));

    return element;
}

export function qs(selector, root = document) {
    return root.querySelector(selector);
}

export function qsa(selector, root = document) {
    return [...root.querySelectorAll(selector)];
}

export function debounce(callback, delay = 350) {
    let timer;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => callback(...args), delay);
    };
}

/* ------------------------------------------------------------------------
 * Toast notifications (feedback instead of window.alert)
 * --------------------------------------------------------------------- */

const TOAST_ICONS = { success: 'circle-check', error: 'circle-x', info: 'info' };

export function toast(message, type = 'success', duration = 4500) {
    const container = qs('.toast-container');
    const element = h(
        'div',
        { class: `toast is-${type}`, role: type === 'error' ? 'alert' : 'status' },
        icon(TOAST_ICONS[type] ?? 'info'),
        h('span', {}, message),
        h('button', { class: 'toast-close', type: 'button', 'aria-label': 'Uždaryti', onClick: () => remove() }, icon('x')),
        h('span', { class: 'toast-timer', style: { animationDuration: `${duration}ms` } }),
    );

    const remove = () => {
        element.classList.add('is-leaving');
        element.addEventListener('animationend', () => element.remove(), { once: true });
    };

    container.append(element);
    setTimeout(remove, duration);
}

/* ------------------------------------------------------------------------
 * Modal windows (<dialog>)
 * --------------------------------------------------------------------- */

export function openModal(dialog) {
    if (!dialog.dataset.bound) {
        dialog.dataset.bound = '1';
        qsa('[data-close-modal]', dialog).forEach((button) => button.addEventListener('click', () => dialog.close()));
        // Click on the dark backdrop closes the modal
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    }

    dialog.showModal();
    qs('input:not([type=hidden]):not([type=radio]), textarea, select', dialog)?.focus();
}

export function closeModal(dialog) {
    dialog.close();
}

/** Shared confirmation modal; resolves to true when the user confirms. */
export function confirmAction({ title, message, confirmText = 'Ištrinti' }) {
    const dialog = qs('#confirm-modal');
    qs('[data-confirm-title]', dialog).textContent = title;
    qs('[data-confirm-message]', dialog).textContent = message;
    qs('[data-confirm-button]', dialog).lastChild.textContent = ` ${confirmText}`;

    return new Promise((resolve) => {
        dialog.addEventListener('close', () => resolve(dialog.returnValue === 'confirm'), { once: true });
        dialog.returnValue = '';
        dialog.showModal();
    });
}

/* ------------------------------------------------------------------------
 * Forms
 * --------------------------------------------------------------------- */

export function fillForm(form, values) {
    Object.entries(values).forEach(([name, value]) => {
        const fields = form.elements.namedItem(name);
        if (!fields) {
            return;
        }
        if (fields instanceof RadioNodeList) {
            fields.forEach((radio) => {
                radio.checked = radio.value === String(value);
            });
        } else if (fields.type === 'checkbox') {
            fields.checked = Boolean(value);
        } else {
            fields.value = value ?? '';
        }
    });
}

/** Form values as an object; empty optional text fields become null. */
export function formValues(form) {
    const values = {};

    [...form.elements].forEach((field) => {
        if (!field.name || field.disabled || field.type === 'submit' || field.type === 'button') {
            return;
        }
        if (field.type === 'radio') {
            if (field.checked) {
                values[field.name] = field.value;
            }
        } else if (field.type === 'checkbox') {
            values[field.name] = field.checked;
        } else if (field.type === 'number') {
            values[field.name] = field.value === '' ? null : Number(field.value);
        } else {
            values[field.name] = field.value.trim() === '' ? null : field.value.trim();
        }
    });

    return values;
}

export function clearFormErrors(form) {
    qsa('.field-error', form).forEach((element) => element.remove());
    qsa('.has-error', form).forEach((element) => element.classList.remove('has-error'));
}

export function showFormErrors(form, errors) {
    clearFormErrors(form);

    Object.entries(errors).forEach(([name, messages]) => {
        const field = form.elements.namedItem(name);
        const element = field instanceof RadioNodeList ? field[0] : field;
        const wrapper = element?.closest('.field') ?? element?.closest('label') ?? form;

        wrapper.classList.add('has-error');
        wrapper.append(h('span', { class: 'field-error' }, translate([].concat(messages)[0])));
    });

    qs('.has-error input, .has-error select, .has-error textarea', form)?.focus();
}

/** Browser validation with Lithuanian messages (the server validates again). */
export function validateForm(form) {
    const errors = {};

    [...form.elements].forEach((field) => {
        if (!field.name || field.validity.valid || errors[field.name]) {
            return;
        }
        const v = field.validity;
        errors[field.name] =
            (v.valueMissing && (field.type === 'checkbox' ? 'Šis laukas turi būti pažymėtas.' : 'Šis laukas privalomas.')) ||
            (v.typeMismatch && field.type === 'email' && 'Įveskite teisingą el. pašto adresą.') ||
            (v.tooShort && `Turi būti bent ${field.minLength} simboliai.`) ||
            (v.tooLong && `Ne daugiau kaip ${field.maxLength} simbolių.`) ||
            (v.rangeUnderflow && `Mažiausia reikšmė – ${field.min}.`) ||
            (v.rangeOverflow && `Didžiausia reikšmė – ${field.max}.`) ||
            (v.stepMismatch && 'Daugiausia 2 skaitmenys po kablelio.') ||
            (v.patternMismatch && 'Neteisingas formatas.') ||
            'Neteisinga reikšmė.';
    });

    if (Object.keys(errors).length) {
        showFormErrors(form, errors);

        return false;
    }

    clearFormErrors(form);

    return true;
}

/** Shows a spinner in the submit button while a request is running. */
export async function withBusyButton(button, callback) {
    const original = [...button.childNodes];
    button.disabled = true;
    button.replaceChildren(h('span', { class: 'spinner' }), ' Palaukite…');

    try {
        return await callback();
    } finally {
        button.disabled = false;
        button.replaceChildren(...original);
    }
}

/* ------------------------------------------------------------------------
 * Errors
 * --------------------------------------------------------------------- */

const MESSAGES = {
    'This category already has a budget for the given month.': 'Ši kategorija jau turi šio mėnesio biudžetą.',
    'An account with this email already exists.': 'Paskyra su šiuo el. paštu jau egzistuoja.',
    'The month cannot be changed because the budget already has transactions.': 'Mėnesio pakeisti negalima – biudžetas jau turi operacijų.',
    'The two-factor code is invalid.': 'Neteisingas patvirtinimo kodas.',
    'The password is incorrect.': 'Neteisingas slaptažodis.',
    'The password field confirmation does not match.': 'Slaptažodžiai nesutampa.',
    'The password field must contain at least one letter.': 'Slaptažodyje turi būti bent viena raidė.',
    'The password field must contain at least one number.': 'Slaptažodyje turi būti bent vienas skaičius.',
    'The password field must be at least 8 characters.': 'Slaptažodis turi būti bent 8 simbolių.',
};

function translate(message) {
    if (MESSAGES[message]) {
        return MESSAGES[message];
    }
    if (message?.startsWith('The occurred on date must be within the budget month')) {
        return 'Data turi būti biudžeto mėnesio ribose.';
    }

    return message;
}

/** Shows an API error: field errors in the form (422) or a toast. */
export function handleError(error, form = null) {
    if (error.status === 422 && form) {
        showFormErrors(form, error.errors);
        toast('Patikrinkite pažymėtus laukus.', 'error');

        return;
    }

    const messages = {
        0: error.message,
        403: 'Neturite teisių atlikti šio veiksmo.',
        404: 'Įrašas nerastas – galbūt jis jau ištrintas.',
        409: translate(error.message),
        422: translate(Object.values(error.errors)[0]?.[0] ?? error.message),
        429: 'Per daug bandymų. Palaukite minutę ir bandykite vėl.',
    };

    toast(messages[error.status] ?? 'Įvyko netikėta klaida. Bandykite dar kartą.', 'error');
}

/* ------------------------------------------------------------------------
 * Formatting
 * --------------------------------------------------------------------- */

const money = new Intl.NumberFormat('lt-LT', { style: 'currency', currency: 'EUR' });

export const formatMoney = (value) => money.format(value ?? 0);

export const formatDate = (value) =>
    new Intl.DateTimeFormat('lt-LT', { year: 'numeric', month: 'long', day: 'numeric' }).format(new Date(value));

export function formatMonth(month) {
    const [year, monthIndex] = month.split('-').map(Number);
    const name = new Intl.DateTimeFormat('lt-LT', { month: 'long' }).format(new Date(year, monthIndex - 1, 1));

    return `${year} m. ${name}`;
}

export function currentMonth() {
    const today = new Date();

    return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}`;
}

export const PAYMENT_METHODS = {
    card: { label: 'Kortele', icon: 'credit-card' },
    cash: { label: 'Grynais', icon: 'banknote' },
    bank_transfer: { label: 'Pavedimu', icon: 'landmark' },
};

export function typeBadge(type) {
    return type === 'income'
        ? h('span', { class: 'badge badge-income' }, icon('trending-up'), 'Pajamos')
        : h('span', { class: 'badge badge-expense' }, icon('trending-down'), 'Išlaidos');
}

/** Usage bar; the width is set a moment later so the CSS transition animates it. */
export function progressBar(percent) {
    const bar = h('div', {
        class: `progress-bar ${percent > 100 ? 'is-danger' : percent >= 85 ? 'is-warning' : ''}`,
    });
    requestAnimationFrame(() => requestAnimationFrame(() => (bar.style.width = `${Math.min(percent, 100)}%`)));

    return h(
        'div',
        { class: 'progress', role: 'progressbar', 'aria-valuenow': Math.round(percent), 'aria-valuemin': 0, 'aria-valuemax': 100 },
        bar,
    );
}

/* ------------------------------------------------------------------------
 * Loading, empty state, pagination
 * --------------------------------------------------------------------- */

export function renderSkeleton(container, rows = 4) {
    container.replaceChildren(...Array.from({ length: rows }, () => h('div', { class: 'skeleton skeleton-row' })));
}

export function renderEmpty(container, { title, text, action = null }) {
    container.replaceChildren(
        h(
            'div',
            { class: 'empty-state fade-in' },
            h('img', { src: '/images/empty.svg', alt: '', width: 180, height: 144 }),
            h('h3', {}, title),
            h('p', {}, text),
            action,
        ),
    );
}

/**
 * Renders page buttons from the Laravel pagination "meta" object.
 */
export function renderPagination(container, meta, onPageChange) {
    if (!meta || meta.last_page <= 1) {
        container.replaceChildren(
            meta?.total ? h('p', { class: 'pagination' }, `Iš viso: ${meta.total}`) : '',
        );

        return;
    }

    const page = meta.current_page;
    const pages = new Set([1, meta.last_page, page - 1, page, page + 1].filter((p) => p >= 1 && p <= meta.last_page));
    const buttons = [];
    let previous = 0;

    [...pages].sort((a, b) => a - b).forEach((p) => {
        if (p - previous > 1) {
            buttons.push(h('span', { class: 'muted' }, '…'));
        }
        buttons.push(
            h(
                'button',
                {
                    class: `page-btn ${p === page ? 'is-current' : ''}`,
                    type: 'button',
                    'aria-current': p === page ? 'page' : null,
                    onClick: () => onPageChange(p),
                },
                String(p),
            ),
        );
        previous = p;
    });

    container.replaceChildren(
        h(
            'div',
            { class: 'pagination' },
            h('span', {}, `Rodoma ${meta.from}–${meta.to} iš ${meta.total}`),
            h(
                'div',
                { class: 'pagination-pages' },
                h('button', { class: 'page-btn', type: 'button', disabled: page === 1, 'aria-label': 'Ankstesnis', onClick: () => onPageChange(page - 1) }, icon('chevron-left')),
                ...buttons,
                h('button', { class: 'page-btn', type: 'button', disabled: page === meta.last_page, 'aria-label': 'Kitas', onClick: () => onPageChange(page + 1) }, icon('chevron-right')),
            ),
        ),
    );
}

/** Eye button next to a password field shows / hides the password. */
export function bindPasswordToggles(root = document) {
    qsa('[data-toggle-password]', root).forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.togglePassword);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-label', show ? 'Slėpti slaptažodį' : 'Rodyti slaptažodį');
            button.replaceChildren(icon(show ? 'eye-off' : 'eye'));
        });
    });
}
