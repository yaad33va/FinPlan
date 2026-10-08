/**
 * Dashboard: GET /api/v1/dashboard?month=YYYY-MM (user + categories + budgets + transactions).
 */
import { api } from '../lib/api';
import { icon } from '../lib/icons';
import { PAYMENT_METHODS, currentMonth, formatDate, formatMoney, formatMonth, h, handleError, progressBar, qs, qsa, renderEmpty } from '../lib/ui';

const monthInput = () => qs('#dashboard-month');

function shiftMonth(month, step) {
    const [year, monthIndex] = month.split('-').map(Number);
    const date = new Date(year, monthIndex - 1 + step, 1);

    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
}

function statCard({ label, value, sub, iconName, variant = '' }) {
    return h(
        'article',
        { class: `card stat-card card-hover ${variant}` },
        h('span', { class: 'stat-icon' }, icon(iconName)),
        h('span', { class: 'stat-label' }, label),
        h('span', { class: 'stat-value' }, value),
        h('span', { class: 'stat-sub' }, sub),
    );
}

function renderStats({ summary }) {
    const { income, expenses } = summary;

    qs('#dashboard-stats').replaceChildren(
        statCard({ label: 'Pajamos', value: formatMoney(income.actual), sub: `Planuota ${formatMoney(income.budgeted)}`, iconName: 'trending-up' }),
        statCard({
            label: 'Išlaidos',
            value: formatMoney(expenses.actual),
            sub: `Biudžetas ${formatMoney(expenses.budgeted)}, liko ${formatMoney(expenses.remaining)}`,
            iconName: 'trending-down',
            variant: 'is-blue',
        }),
        statCard({
            label: 'Balansas',
            value: formatMoney(summary.net_balance),
            sub: summary.net_balance >= 0 ? 'Pajamos viršija išlaidas' : 'Išlaidos viršija pajamas',
            iconName: 'scale',
            variant: summary.net_balance >= 0 ? '' : 'is-warning',
        }),
        statCard({
            label: 'Viršyti biudžetai',
            value: `${summary.exceeded_budgets_count} / ${summary.budgets_count}`,
            sub: `Kategorijų: ${summary.categories_count}`,
            iconName: 'triangle-alert',
            variant: summary.exceeded_budgets_count > 0 ? 'is-warning' : 'is-blue',
        }),
    );
    qs('#dashboard-stats').classList.add('stagger');
}

function renderBudgets({ budgets, month }) {
    const container = qs('#dashboard-budgets');

    if (!budgets.length) {
        renderEmpty(container, {
            title: 'Šiam mėnesiui biudžetų nėra',
            text: `${formatMonth(month)} dar neturi biudžetų. Atidarykite kategoriją ir sukurkite biudžetą.`,
            action: h('a', { class: 'btn btn-primary', href: '/categories' }, icon('plus'), 'Kurti biudžetą'),
        });

        return;
    }

    container.replaceChildren(
        h(
            'ul',
            { class: 'budget-list' },
            budgets.map((budget) =>
                h(
                    'li',
                    { class: 'budget-item fade-in' },
                    h(
                        'a',
                        { href: `/categories/${budget.category.id}/budgets/${budget.budget_id}` },
                        h(
                            'div',
                            { class: 'budget-item-top' },
                            h(
                                'span',
                                { class: 'budget-item-name' },
                                h('span', { class: 'color-dot', style: { background: budget.category.color ?? '#16a34a' } }),
                                budget.category.name,
                            ),
                            h(
                                'span',
                                { class: 'budget-item-numbers' },
                                `${formatMoney(budget.spent)} / ${formatMoney(budget.amount)}`,
                            ),
                        ),
                        progressBar(budget.usage_percent),
                        h(
                            'small',
                            { class: budget.remaining < 0 && budget.category.type === 'expense' ? 'text-danger' : 'muted' },
                            budget.category.type === 'income'
                                ? `Gauta ${budget.usage_percent}% planuotų pajamų`
                                : budget.remaining < 0
                                  ? `Viršyta ${formatMoney(-budget.remaining)}`
                                  : `Liko ${formatMoney(budget.remaining)} (${budget.usage_percent}% išnaudota)`,
                        ),
                    ),
                ),
            ),
        ),
    );
}

function renderTransactions({ recent_transactions: transactions, budgets }) {
    const container = qs('#dashboard-transactions');
    const categories = Object.fromEntries(budgets.map((budget) => [budget.category.id, budget.category]));

    if (!transactions.length) {
        container.replaceChildren(h('p', { class: 'muted' }, 'Šį mėnesį operacijų dar nėra.'));

        return;
    }

    container.replaceChildren(
        h(
            'ul',
            { class: 'tx-list' },
            transactions.map((transaction) => {
                const isIncome = categories[transaction.category_id]?.type === 'income';

                return h(
                    'li',
                    { class: 'fade-in' },
                    h('span', { class: `tx-icon ${isIncome ? 'is-income' : ''}` }, icon(PAYMENT_METHODS[transaction.payment_method].icon)),
                    h(
                        'div',
                        { class: 'tx-main' },
                        h('strong', {}, transaction.description),
                        h('small', {}, `${categories[transaction.category_id]?.name ?? ''} · ${formatDate(transaction.occurred_on)}`),
                    ),
                    h(
                        'span',
                        { class: `tx-amount ${isIncome ? 'text-income' : 'text-expense'}` },
                        `${isIncome ? '+' : '−'}${formatMoney(transaction.amount)}`,
                    ),
                );
            }),
        ),
    );
}

async function load(month) {
    monthInput().value = month;
    window.history.replaceState(null, '', `?month=${month}`);

    try {
        const { data } = await api('/dashboard', { query: { month } });
        renderStats(data);
        renderBudgets(data);
        renderTransactions(data);
    } catch (error) {
        handleError(error);
    }
}

export default function initDashboardPage() {
    const requested = new URLSearchParams(window.location.search).get('month');
    const month = /^\d{4}-\d{2}$/.test(requested ?? '') ? requested : currentMonth();

    monthInput().addEventListener('change', () => monthInput().value && load(monthInput().value));
    qsa('[data-month-step]').forEach((button) => {
        button.addEventListener('click', () => load(shiftMonth(monthInput().value, Number(button.dataset.monthStep))));
    });

    return load(month);
}
