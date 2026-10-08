/**
 * One budget and its transactions:
 * GET /categories/{c}/budgets/{b} and GET /categories/{c}/budgets/{b}/transactions (hierarchical URL).
 */
import { openBudgetForm } from '../forms/budget-form';
import { openTransactionForm, showTransactionDetails } from '../forms/transaction-form';
import { api } from '../lib/api';
import { icon } from '../lib/icons';
import {
    PAYMENT_METHODS,
    confirmAction,
    debounce,
    formValues,
    formatDate,
    formatMoney,
    formatMonth,
    h,
    handleError,
    progressBar,
    qs,
    qsa,
    renderEmpty,
    renderPagination,
    renderSkeleton,
    toast,
} from '../lib/ui';

let category;
let budget;
const state = { page: 1 };

const can = (action) => Boolean(category?._links[action]);
const budgetUrl = () => `/categories/${category.id}/budgets/${budget.id}`;

function stat(label, value, extraClass = '') {
    return h('div', {}, h('span', { class: 'stat-label' }, label), h('div', { class: `stat-value ${extraClass}` }, value));
}

function renderHeader() {
    const header = qs('#budget-header');
    const isIncome = category.type === 'income';
    header.style.setProperty('--category-color', category.color ?? '#16a34a');
    document.title = `${category.name}: ${formatMonth(budget.month)} · FinPlan`;
    qs('[data-category-name]').textContent = category.name;
    qs('[data-budget-month]').textContent = formatMonth(budget.month);

    header.replaceChildren(
        h(
            'div',
            { style: { flex: '1 1 480px' } },
            h('h1', {}, icon('target'), formatMonth(budget.month)),
            h('p', {}, `${category.name}${budget.note ? ` · ${budget.note}` : ''}`),
            h(
                'div',
                { class: 'budget-summary' },
                stat(isIncome ? 'Planuota' : 'Biudžetas', formatMoney(budget.amount)),
                stat(isIncome ? 'Gauta' : 'Išleista', formatMoney(budget.spent)),
                stat('Likutis', formatMoney(budget.remaining), budget.is_exceeded && !isIncome ? 'text-danger' : ''),
                stat('Išnaudota', `${budget.usage_percent}%`),
            ),
            progressBar(budget.usage_percent),
            budget.is_exceeded && !isIncome
                ? h('p', { class: 'text-danger', style: { marginTop: '8px', fontWeight: 700 } }, `Biudžetas viršytas ${formatMoney(-budget.remaining)}!`)
                : null,
        ),
        h(
            'div',
            { class: 'row' },
            can('update') && h('button', { class: 'btn btn-secondary', type: 'button', onClick: editBudget }, icon('pencil'), 'Redaguoti'),
            can('delete') && h('button', { class: 'btn btn-danger', type: 'button', onClick: deleteBudget }, icon('trash'), 'Ištrinti'),
        ),
    );
    header.classList.add('fade-in');
    qs('[data-action="create-transaction"]').hidden = !can('update');
}

async function reloadBudget() {
    budget = (await api(budgetUrl())).data;
    renderHeader();
}

function editBudget() {
    openBudgetForm({ categoryId: category.id, budget, onSaved: (saved) => ((budget = saved), renderHeader()) });
}

async function deleteBudget() {
    const confirmed = await confirmAction({
        title: 'Ištrinti biudžetą?',
        message: `${formatMonth(budget.month)} biudžetas bus ištrintas kartu su visomis jo operacijomis.`,
    });

    if (confirmed) {
        try {
            await api(budgetUrl(), { method: 'DELETE' });
            toast('Biudžetas ištrintas.');
            window.location.href = `/categories/${category.id}`;
        } catch (error) {
            handleError(error);
        }
    }
}

function createTransaction() {
    openTransactionForm({ categoryId: category.id, budget, onSaved: refresh });
}

function editTransaction(transaction) {
    openTransactionForm({ categoryId: category.id, budget, transaction, onSaved: refresh });
}

async function deleteTransaction(transaction) {
    const confirmed = await confirmAction({ title: 'Ištrinti operaciją?', message: `„${transaction.description}“ (${formatMoney(transaction.amount)}) bus ištrinta.` });

    if (confirmed) {
        try {
            await api(`${budgetUrl()}/transactions/${transaction.id}`, { method: 'DELETE' });
            toast('Operacija ištrinta.');
            await refresh();
        } catch (error) {
            handleError(error);
        }
    }
}

function transactionRow(transaction) {
    const method = PAYMENT_METHODS[transaction.payment_method];
    const details = () =>
        showTransactionDetails({
            transaction,
            categoryName: category.name,
            budgetMonth: budget.month,
            canEdit: can('update'),
            canDelete: can('delete'),
            onEdit: () => editTransaction(transaction),
            onDelete: () => deleteTransaction(transaction),
        });

    return h(
        'tr',
        { class: 'is-clickable', onClick: (event) => !event.target.closest('button') && details() },
        h('td', { 'data-label': 'Data' }, formatDate(transaction.occurred_on)),
        h('td', { 'data-label': 'Aprašymas' }, h('strong', {}, transaction.description)),
        h('td', { 'data-label': 'Pardavėjas' }, transaction.merchant ?? '—'),
        h('td', { 'data-label': 'Būdas' }, h('span', { class: 'row' }, icon(method.icon), method.label)),
        h('td', { class: `num ${category.type === 'income' ? 'text-income' : ''}`, 'data-label': 'Suma' }, h('strong', {}, formatMoney(transaction.amount))),
        h(
            'td',
            { class: 'actions', 'data-label': '' },
            h(
                'span',
                { class: 'actions-inline' },
                can('update') && h('button', { class: 'icon-btn', type: 'button', 'aria-label': 'Redaguoti operaciją', onClick: () => editTransaction(transaction) }, icon('pencil')),
                can('delete') && h('button', { class: 'icon-btn is-danger', type: 'button', 'aria-label': 'Ištrinti operaciją', onClick: () => deleteTransaction(transaction) }, icon('trash')),
            ),
        ),
    );
}

async function loadTransactions() {
    const container = qs('#transaction-list');
    const form = qs('#transaction-filters');
    const filters = formValues(form);

    try {
        const response = await api(`${budgetUrl()}/transactions`, { query: { page: state.page, per_page: 10, ...filters } });

        if (!response.data.length && state.page > 1) {
            state.page -= 1;

            return loadTransactions();
        }

        if (!response.data.length) {
            const filtered = Object.values(filters).some((value) => value !== null && value !== '-occurred_on');
            renderEmpty(container, {
                title: filtered ? 'Nieko nerasta' : 'Operacijų dar nėra',
                text: filtered ? 'Pakeiskite filtrus.' : 'Pridėkite pirmąją šio mėnesio operaciją.',
                action: !filtered && can('update') && h('button', { class: 'btn btn-primary', type: 'button', onClick: createTransaction }, icon('plus'), 'Nauja operacija'),
            });
        } else {
            container.replaceChildren(
                h(
                    'div',
                    { class: 'table-wrap fade-in' },
                    h(
                        'table',
                        { class: 'data-table' },
                        h('thead', {}, h('tr', {}, h('th', {}, 'Data'), h('th', {}, 'Aprašymas'), h('th', {}, 'Pardavėjas'), h('th', {}, 'Būdas'), h('th', { class: 'num' }, 'Suma'), h('th', {}, ''))),
                        h('tbody', {}, response.data.map(transactionRow)),
                    ),
                ),
            );
        }

        renderPagination(qs('#transaction-pagination'), response.meta, (page) => {
            state.page = page;
            loadTransactions();
        });
    } catch (error) {
        handleError(error, form);
    }
}

async function refresh() {
    await Promise.all([reloadBudget(), loadTransactions()]);
}

export default async function initBudgetPage() {
    const { categoryId, budgetId } = qs('#budget-page').dataset;

    try {
        [category, budget] = await Promise.all([
            api(`/categories/${categoryId}`).then((response) => response.data),
            api(`/categories/${categoryId}/budgets/${budgetId}`).then((response) => response.data),
        ]);
    } catch (error) {
        handleError(error);
        renderEmpty(qs('#budget-page'), {
            title: error.status === 403 ? 'Prieiga uždrausta' : 'Biudžetas nerastas',
            text: error.status === 403 ? 'Šis biudžetas priklauso kitam naudotojui.' : 'Toks biudžetas neegzistuoja šioje kategorijoje.',
            action: h('a', { class: 'btn btn-primary', href: '/categories' }, icon('arrow-left'), 'Į kategorijas'),
        });

        return;
    }

    renderHeader();
    qs('[data-action="create-transaction"]').addEventListener('click', createTransaction);

    const form = qs('#transaction-filters');
    const reload = () => {
        state.page = 1;
        loadTransactions();
    };
    form.addEventListener('submit', (event) => event.preventDefault());
    form.addEventListener('change', reload);
    qsa('input[type=search]', form).forEach((input) => input.addEventListener('input', debounce(reload)));

    renderSkeleton(qs('#transaction-list'));
    await loadTransactions();
}
