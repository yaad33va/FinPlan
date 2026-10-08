/**
 * Admin: user list (GET /users) with search, role filter, pagination and deletion.
 */
import { api } from '../lib/api';
import { icon } from '../lib/icons';
import { confirmAction, debounce, formValues, formatDate, h, handleError, qs, renderEmpty, renderPagination, renderSkeleton, toast } from '../lib/ui';

const state = { page: 1 };

async function deleteUser(user) {
    const confirmed = await confirmAction({
        title: 'Ištrinti naudotoją?',
        message: `${user.name} (${user.email}) paskyra bus ištrinta kartu su ${user.categories_count} kategorijomis, jų biudžetais ir operacijomis.`,
    });

    if (confirmed) {
        try {
            await api(`/users/${user.id}`, { method: 'DELETE' });
            toast(`Naudotojas ${user.name} ištrintas.`);
            await load();
        } catch (error) {
            handleError(error);
        }
    }
}

function userRow(user) {
    return h(
        'tr',
        {},
        h('td', { 'data-label': 'Vardas' }, h('strong', {}, user.name)),
        h('td', { 'data-label': 'El. paštas' }, user.email),
        h('td', { 'data-label': 'Rolė' }, h('span', { class: `badge ${user.role === 'admin' ? 'badge-admin' : 'badge-member'}` }, user.role === 'admin' ? 'Administratorius' : 'Narys')),
        h('td', { 'data-label': '2FA' }, user.two_factor_enabled ? h('span', { class: 'badge badge-income' }, icon('shield-check'), 'Taip') : h('span', { class: 'badge badge-neutral' }, 'Ne')),
        h('td', { class: 'num', 'data-label': 'Kategorijų' }, String(user.categories_count)),
        h('td', { 'data-label': 'Užsiregistravo' }, formatDate(user.created_at)),
        h(
            'td',
            { class: 'actions', 'data-label': '' },
            h(
                'span',
                { class: 'actions-inline' },
                h('a', { class: 'icon-btn', href: `/categories?user_id=${user.id}`, title: 'Kategorijos', 'aria-label': `${user.name} kategorijos` }, icon('folder-open')),
                user._links.delete &&
                    h('button', { class: 'icon-btn is-danger', type: 'button', title: 'Ištrinti', 'aria-label': `Ištrinti ${user.name}`, onClick: () => deleteUser(user) }, icon('trash')),
            ),
        ),
    );
}

async function load() {
    const container = qs('#user-list');
    const filters = formValues(qs('#user-filters'));

    try {
        const response = await api('/users', { query: { page: state.page, per_page: 10, ...filters } });

        if (!response.data.length) {
            renderEmpty(container, { title: 'Naudotojų nerasta', text: 'Pakeiskite paieškos sąlygas.' });
        } else {
            container.replaceChildren(
                h(
                    'div',
                    { class: 'table-wrap fade-in' },
                    h(
                        'table',
                        { class: 'data-table' },
                        h('thead', {}, h('tr', {}, ...['Vardas', 'El. paštas', 'Rolė', '2FA', 'Kategorijų', 'Užsiregistravo', ''].map((title) => h('th', { class: title === 'Kategorijų' ? 'num' : '' }, title)))),
                        h('tbody', {}, response.data.map(userRow)),
                    ),
                ),
            );
        }

        renderPagination(qs('#user-pagination'), response.meta, (page) => {
            state.page = page;
            load();
        });
    } catch (error) {
        handleError(error);
    }
}

export default function initAdminUsersPage() {
    const form = qs('#user-filters');
    const reload = () => {
        state.page = 1;
        load();
    };

    form.addEventListener('submit', (event) => event.preventDefault());
    form.addEventListener('change', reload);
    form.elements.namedItem('search').addEventListener('input', debounce(reload));

    renderSkeleton(qs('#user-list'));

    return load();
}
