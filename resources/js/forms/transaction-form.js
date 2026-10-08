/**
 * Create / edit transaction modal and the transaction details modal
 * (partials/transaction-modals.blade.php).
 */
import { api } from '../lib/api';
import { icon } from '../lib/icons';
import {
    PAYMENT_METHODS,
    clearFormErrors,
    closeModal,
    fillForm,
    formValues,
    formatDate,
    formatMoney,
    formatMonth,
    h,
    handleError,
    openModal,
    qs,
    toast,
    validateForm,
    withBusyButton,
} from '../lib/ui';

/** First and last day (YYYY-MM-DD) of a budget month. */
function monthBounds(month) {
    const [year, monthIndex] = month.split('-').map(Number);
    const lastDay = new Date(year, monthIndex, 0).getDate();

    return { first: `${month}-01`, last: `${month}-${String(lastDay).padStart(2, '0')}` };
}

function defaultDate(month) {
    const today = new Date();
    const todayString = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

    return todayString.startsWith(month) ? todayString : `${month}-01`;
}

export function openTransactionForm({ categoryId, budget, transaction = null, onSaved }) {
    const dialog = qs('#transaction-modal');
    const form = qs('#transaction-form');
    const dateInput = form.elements.namedItem('occurred_on');
    const { first, last } = monthBounds(budget.month);

    form.reset();
    clearFormErrors(form);
    qs('[data-modal-title]', dialog).textContent = transaction ? 'Redaguoti operaciją' : 'Nauja operacija';
    dateInput.min = first;
    dateInput.max = last;
    fillForm(form, {
        amount: transaction?.amount ?? '',
        occurred_on: transaction?.occurred_on ?? defaultDate(budget.month),
        description: transaction?.description ?? '',
        merchant: transaction?.merchant ?? '',
        payment_method: transaction?.payment_method ?? 'card',
    });

    form.onsubmit = async (event) => {
        event.preventDefault();
        if (!validateForm(form)) {
            return;
        }

        const body = formValues(form);
        const url = `/categories/${categoryId}/budgets/${budget.id}/transactions`;

        await withBusyButton(qs('button[type=submit]', form), async () => {
            try {
                const response = transaction
                    ? await api(`${url}/${transaction.id}`, { method: 'PUT', body })
                    : await api(url, { method: 'POST', body });

                closeModal(dialog);
                toast(transaction ? 'Operacija atnaujinta.' : 'Operacija pridėta.');
                onSaved(response.data);
            } catch (error) {
                handleError(error, form);
            }
        });
    };

    openModal(dialog);
}

/**
 * Details modal with all information about a transaction and its place in the hierarchy.
 */
export function showTransactionDetails({ transaction, categoryName, budgetMonth, canEdit, canDelete, onEdit, onDelete }) {
    const dialog = qs('#transaction-details-modal');
    const method = PAYMENT_METHODS[transaction.payment_method];
    const rows = [
        ['Suma', formatMoney(transaction.amount)],
        ['Data', formatDate(transaction.occurred_on)],
        ['Aprašymas', transaction.description],
        ['Pardavėjas', transaction.merchant ?? '—'],
        ['Mokėjimo būdas', h('span', { class: 'row' }, icon(method.icon), method.label)],
        ['Kategorija', categoryName],
        ['Biudžetas', formatMonth(budgetMonth)],
        ['Sukurta', formatDate(transaction.created_at)],
        ['Atnaujinta', formatDate(transaction.updated_at)],
    ];

    qs('[data-details]', dialog).replaceChildren(...rows.flatMap(([label, value]) => [h('dt', {}, label), h('dd', {}, value)]));

    qs('[data-details-actions]', dialog).replaceChildren(
        h('button', { class: 'btn btn-ghost', type: 'button', onClick: () => dialog.close() }, 'Uždaryti'),
        canDelete &&
            h(
                'button',
                { class: 'btn btn-secondary', type: 'button', onClick: () => (dialog.close(), onDelete()) },
                icon('trash'),
                'Ištrinti',
            ),
        canEdit &&
            h(
                'button',
                { class: 'btn btn-primary', type: 'button', onClick: () => (dialog.close(), onEdit()) },
                icon('pencil'),
                'Redaguoti',
            ),
    );

    openModal(dialog);
}
