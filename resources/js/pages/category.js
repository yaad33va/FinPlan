/**
 * One category: its budgets (GET /categories/{id}/budgets) and all of its transactions
 * across budgets (GET /categories/{id}/transactions).
 */
import { openBudgetForm } from '../forms/budget-form';
import { openCategoryForm } from '../forms/category-form';
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
    typeBadge,
} from '../lib/ui';

let category;
let currentUser;
const budgetState = { page: 1 };
const transactionState = { page: 1 };

const can = (action) => Boolean(category?._links[action]);

/* ---------------------------------------------------------------- header */

function renderHeader() {
    const header = qs('#category-header');
    header.style.setProperty('--category-color', category.color ?? '#16a34a');
    document.title = `${category.name} · FinPlan`;
    qs('[data-category-name]').textContent = category.name;

    if (category.user_id !== currentUser.id) {
        qs('[data-categories-link]').href = `/categories?user_id=${category.user_id}`;
    }

    header.replaceChildren(
        h(
            'div',
            {},
            h('h1', {}, h('span', { class: 'color-dot', style: { background: category.color ?? '#16a34a' } }), category.name),
            h('p', {}, category.description ?? 'Aprašymo nėra.'),
            h('div', { class: 'row', style: { marginTop: '8px' } }, typeBadge(category.type), h('span', { class: 'badge badge-neutral' }, `Biudžetų: ${category.budgets_count}`)),
        ),
        h(
            'div',
            { class: 'row' },
            can('update') &&
                h('button', { class: 'btn btn-secondary', type: 'button', onClick: () => openCategoryForm({ category, onSaved: reloadCategory }) }, icon('pencil'), 'Redaguoti'),
            can('delete') &&
                h('button', { class: 'btn btn-danger', type: 'button', onClick: deleteCategory }, icon('trash'), 'Ištrinti'),
        ),
    );
    header.classList.add('fade-in');

    qs('[data-action="create-budget"]').hidden = !can('update');
}

async function reloadCategory() {
    category = (await api(`/categories/${category.id}`)).data;
    renderHeader();
}

async function deleteCategory() {
    const confirmed = await confirmAction({
        title: 'Ištrinti kategoriją?',
        message: `Kategorija „${category.name}“ bus ištrinta kartu su visais biudžetais ir operacijomis.`,
    });

    if (confirmed) {
        try {
            await api(`/categories/${category.id}`, { method: 'DELETE' });
            toast('Kategorija ištrinta.');
            window.location.href = category.user_id === currentUser.id ? '/categories' : `/categories?user_id=${category.user_id}`;
        } catch (error) {
            handleError(error);
        }
    }
}

/* ---------------------------------------------------------------- budgets */

function budgetRow(budget) {
    const open = () => (window.location.href = `/categories/${category.id}/budgets/${budget.id}`);

    return h(
        'tr',
        { class: 'is-clickable', onClick: (event) => !event.target.closest('button') && open() },
        h('td', { 'data-label': 'Mėnuo' }, h('strong', {}, formatMonth(budget.month))),
        h('td', { class: 'num', 'data-label': 'Biudžetas' }, formatMoney(budget.amount)),
        h('td', { class: 'num', 'data-label': category.type === 'income' ? 'Gauta' : 'Išleista' }, formatMoney(budget.spent)),
        h('td', { class: `num ${budget.is_exceeded && category.type === 'expense' ? 'text-danger' : ''}`, 'data-label': 'Likutis' }, formatMoney(budget.remaining)),
        h('td', { 'data-label': 'Išnaudota', style: { minWidth: '140px' } }, progressBar(budget.usage_percent), h('small', { class: 'muted' }, `${budget.usage_percent}%`)),
        h(
            'td',
            { class: 'actions', 'data-label': '' },
            h(
                'span',
                { class: 'actions-inline' },
                can('update') && h('button', { class: 'icon-btn', type: 'button', 'aria-label': 'Redaguoti biudžetą', onClick: () => openBudgetForm({ categoryId: category.id, budget, onSaved: () => (loadBudgets(), reloadCategory()) }) }, icon('pencil')),
                can('delete') && h('button', { class: 'icon-btn is-danger', type: 'button', 'aria-label': 'Ištrinti biudžetą', onClick: () => deleteBudget(budget) }, icon('trash')),
            ),
        ),
    );
}

async function deleteBudget(budget) {
    const confirmed = await confirmAction({
        title: 'Ištrinti biudžetą?',
        message: `${formatMonth(budget.month)} biudžetas bus ištrintas kartu su visomis jo operacijomis.`,
    });

    if (confirmed) {
        try {
            await api(`/categories/${category.id}/budgets/${budget.id}`, { method: 'DELETE' });
            toast('Biudžetas ištrintas.');
            await Promise.all([loadBudgets(), reloadCategory()]);
        } catch (error) {
            handleError(error);
        }
    }
}

async function loadBudgets() {
    const container = qs('#budget-list');
    const filters = formValues(qs('#budget-filters'));

    try {
        const response = await api(`/categories/${category.id}/budgets`, {
            query: {
                page: budgetState.page,
                per_page: 12,
                month_from: filters.month_from,
                month_to: filters.month_to,
                sort: filters.sort,
                exceeded: filters.exceeded ? 'true' : undefined,
            },
        });

        if (!response.data.length) {
            renderEmpty(container, {
                title: 'Biudžetų nėra',
                text: can('update') ? 'Sukurkite mėnesio biudžetą šiai kategorijai.' : 'Pagal pasirinktus filtrus biudžetų nerasta.',
                action: can('update') && h('button', { class: 'btn btn-primary', type: 'button', onClick: createBudget }, icon('plus'), 'Naujas biudžetas'),
            });
        } else {
            container.replaceChildren(
                h(
                    'div',
                    { class: 'table-wrap fade-in' },
                    h(
                        'table',
                        { class: 'data-table' },
                        h('thead', {}, h('tr', {}, ...['Mėnuo', 'Biudžetas', category.type === 'income' ? 'Gauta' : 'Išleista', 'Likutis', 'Išnaudota', ''].map((title, index) => h('th', { class: index > 0 && index < 4 ? 'num' : '' }, title)))),
                        h('tbody', {}, response.data.map(budgetRow)),
                    ),
                ),
            );
        }

        renderPagination(qs('#budget-pagination'), response.meta, (page) => {
            budgetState.page = page;
            loadBudgets();
        });
    } catch (error) {
        handleError(error, qs('#budget-filters'));
    }
}

function createBudget() {
    openBudgetForm({
        categoryId: category.id,
        onSaved: (budget) => (window.location.href = `/categories/${category.id}/budgets/${budget.id}`),
    });
}

/* ---------------------------------------------------------- transactions */

function editTransaction(transaction) {
    openTransactionForm({
        categoryId: category.id,
        budget: { id: transaction.budget_id, month: transaction.occurred_on.slice(0, 7) },
        transaction,
        onSaved: loadTransactions,
    });
}

async function deleteTransaction(transaction) {
    const confirmed = await confirmAction({ title: 'Ištrinti operaciją?', message: `„${transaction.description}“ (${formatMoney(transaction.amount)}) bus ištrinta.` });

    if (confirmed) {
        try {
            await api(`/categories/${category.id}/budgets/${transaction.budget_id}/transactions/${transaction.id}`, { method: 'DELETE' });
            toast('Operacija ištrinta.');
            await loadTransactions();
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
            budgetMonth: transaction.occurred_on.slice(0, 7),
            canEdit: can('update'),
            canDelete: can('delete'),
            onEdit: () => editTransaction(transaction),
            onDelete: () => deleteTransaction(transaction),
        });

    return h(
        'tr',
        { class: 'is-clickable', onClick: details },
        h('td', { 'data-label': 'Data' }, formatDate(transaction.occurred_on)),
        h('td', { 'data-label': 'Aprašymas' }, h('strong', {}, transaction.description)),
        h('td', { 'data-label': 'Pardavėjas' }, transaction.merchant ?? '—'),
        h('td', { 'data-label': 'Būdas' }, h('span', { class: 'row' }, icon(method.icon), method.label)),
        h('td', { class: `num ${category.type === 'income' ? 'text-income' : ''}`, 'data-label': 'Suma' }, h('strong', {}, formatMoney(transaction.amount))),
    );
}

async function loadTransactions() {
    const container = qs('#category-transaction-list');
    const filters = formValues(qs('#category-transaction-filters'));

    try {
        const response = await api(`/categories/${category.id}/transactions`, {
            query: { page: transactionState.page, per_page: 15, ...filters },
        });

        if (!response.data.length) {
            renderEmpty(container, { title: 'Operacijų nerasta', text: 'Operacijos pridedamos atidarius konkretų mėnesio biudžetą.' });
        } else {
            container.replaceChildren(
                h(
                    'div',
                    { class: 'table-wrap fade-in' },
                    h(
                        'table',
                        { class: 'data-table' },
                        h('thead', {}, h('tr', {}, h('th', {}, 'Data'), h('th', {}, 'Aprašymas'), h('th', {}, 'Pardavėjas'), h('th', {}, 'Būdas'), h('th', { class: 'num' }, 'Suma'))),
                        h('tbody', {}, response.data.map(transactionRow)),
                    ),
                ),
            );
        }

        renderPagination(qs('#category-transaction-pagination'), response.meta, (page) => {
            transactionState.page = page;
            loadTransactions();
        });
    } catch (error) {
        handleError(error, qs('#category-transaction-filters'));
    }
}

/* ---------------------------------------------------------------- tabs */

function initTabs() {
    const tabs = qsa('[role=tab]');
    let transactionsLoaded = false;

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((other) => {
                other.setAttribute('aria-selected', String(other === tab));
                qs(`#${other.getAttribute('aria-controls')}`).hidden = other !== tab;
            });

            if (tab.id === 'tab-transactions' && !transactionsLoaded) {
                transactionsLoaded = true;
                renderSkeleton(qs('#category-transaction-list'));
                loadTransactions();
            }
        });
    });
}

function bindFilters(form, state, loader) {
    form.addEventListener('submit', (event) => event.preventDefault());
    const reload = () => {
        state.page = 1;
        loader();
    };
    form.addEventListener('change', reload);
    qsa('input[type=search]', form).forEach((input) => input.addEventListener('input', debounce(reload)));
}

export default async function initCategoryPage({ user }) {
    currentUser = user;
    const categoryId = qs('#category-page').dataset.categoryId;

    try {
        category = (await api(`/categories/${categoryId}`)).data;
    } catch (error) {
        handleError(error);
        renderEmpty(qs('#category-page'), {
            title: error.status === 403 ? 'Prieiga uždrausta' : 'Kategorija nerasta',
            text: error.status === 403 ? 'Ši kategorija priklauso kitam naudotojui.' : 'Tokios kategorijos nėra arba ji ištrinta.',
            action: h('a', { class: 'btn btn-primary', href: '/categories' }, icon('arrow-left'), 'Į kategorijas'),
        });

        return;
    }

    renderHeader();
    initTabs();
    qs('[data-action="create-budget"]').addEventListener('click', createBudget);
    bindFilters(qs('#budget-filters'), budgetState, loadBudgets);
    bindFilters(qs('#category-transaction-filters'), transactionState, loadTransactions);

    renderSkeleton(qs('#budget-list'));
    await loadBudgets();
}
