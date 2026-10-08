/**
 * Category list: GET /api/v1/categories with search, type filter, sorting and pagination.
 * An admin can open /categories?user_id=X to moderate another user's categories.
 */
import { openCategoryForm } from '../forms/category-form';
import { api } from '../lib/api';
import { icon } from '../lib/icons';
import { confirmAction, debounce, h, handleError, qs, renderEmpty, renderPagination, toast, typeBadge } from '../lib/ui';

const state = { page: 1, search: '', type: '', sort: 'name', userId: null };

function categoryCard(category, user) {
    const canUpdate = Boolean(category._links.update);
    const canDelete = Boolean(category._links.delete);

    return h(
        'article',
        { class: 'card category-card card-hover', style: { '--category-color': category.color ?? '#16a34a' } },
        h('div', { class: 'row-between' }, typeBadge(category.type), category.owner && category.owner.id !== user.id
            ? h('span', { class: 'owner-tag' }, icon('user'), category.owner.name)
            : null),
        h('h3', {}, h('a', { href: `/categories/${category.id}` }, category.name)),
        h('p', {}, category.description ?? 'Aprašymo nėra.'),
        h(
            'div',
            { class: 'category-card-footer' },
            h('span', { class: 'row' }, icon('target'), `Biudžetų: ${category.budgets_count}`),
            h(
                'span',
                { class: 'actions-inline' },
                h('a', { class: 'icon-btn', href: `/categories/${category.id}`, title: 'Atidaryti', 'aria-label': `Atidaryti ${category.name}` }, icon('arrow-right')),
                canUpdate &&
                    h('button', {
                        class: 'icon-btn',
                        type: 'button',
                        title: 'Redaguoti',
                        'aria-label': `Redaguoti ${category.name}`,
                        onClick: () => openCategoryForm({ category, onSaved: load }),
                    }, icon('pencil')),
                canDelete &&
                    h('button', {
                        class: 'icon-btn is-danger',
                        type: 'button',
                        title: 'Ištrinti',
                        'aria-label': `Ištrinti ${category.name}`,
                        onClick: () => deleteCategory(category),
                    }, icon('trash')),
            ),
        ),
    );
}

async function deleteCategory(category) {
    const confirmed = await confirmAction({
        title: 'Ištrinti kategoriją?',
        message: `Kategorija „${category.name}“ bus ištrinta kartu su ${category.budgets_count} biudžetais ir visomis jų operacijomis. Šio veiksmo atšaukti negalima.`,
    });

    if (!confirmed) {
        return;
    }

    try {
        await api(`/categories/${category.id}`, { method: 'DELETE' });
        toast(`Kategorija „${category.name}“ ištrinta.`);
        await load();
    } catch (error) {
        handleError(error);
    }
}

let currentUser;

async function load() {
    const list = qs('#category-list');
    list.setAttribute('aria-busy', 'true');

    try {
        const response = await api('/categories', {
            query: { page: state.page, per_page: 9, search: state.search, type: state.type, sort: state.sort, user_id: state.userId },
        });

        if (!response.data.length && state.page > 1) {
            state.page -= 1;

            return load();
        }

        if (!response.data.length) {
            const filtered = state.search || state.type;
            renderEmpty(list, {
                title: filtered ? 'Nieko nerasta' : 'Kategorijų dar nėra',
                text: filtered ? 'Pabandykite pakeisti paieškos ar filtro sąlygas.' : 'Sukurkite pirmąją kategoriją, pvz. „Maistas“ ar „Atlyginimas“.',
                action: !filtered && state.userId === currentUser.id
                    ? h('button', { class: 'btn btn-primary', type: 'button', onClick: () => openCategoryForm({ onSaved: load }) }, icon('plus'), 'Nauja kategorija')
                    : null,
            });
        } else {
            list.replaceChildren(...response.data.map((category) => categoryCard(category, currentUser)));
            list.classList.add('stagger');
        }

        renderPagination(qs('#category-pagination'), response.meta, (page) => {
            state.page = page;
            load();
        });
    } catch (error) {
        handleError(error);
    } finally {
        list.removeAttribute('aria-busy');
    }
}

async function showOwner(userId) {
    try {
        const { data: owner } = await api(`/users/${userId}`);
        qs('[data-page-title]').textContent = `${owner.name} – kategorijos`;
        qs('[data-page-subtitle]').textContent = `${owner.email} · administratorius gali peržiūrėti ir šalinti netinkamus įrašus.`;
        qs('[data-owner-name]').textContent = owner.name;
        qs('[data-admin-breadcrumbs]').hidden = false;
    } catch (error) {
        handleError(error);
    }
}

export default function initCategoriesPage({ user }) {
    currentUser = user;
    const requestedUser = Number(new URLSearchParams(window.location.search).get('user_id'));

    // Members always see their own categories; an admin may look at another user's.
    state.userId = user.isAdmin && requestedUser ? requestedUser : user.id;

    if (state.userId !== user.id) {
        qs('[data-action="create-category"]').hidden = true;
        showOwner(state.userId);
    }

    qs('[data-action="create-category"]').addEventListener('click', () => openCategoryForm({ onSaved: load }));

    const filters = qs('#category-filters');
    filters.addEventListener('submit', (event) => event.preventDefault());
    filters.elements.namedItem('search').addEventListener('input', debounce((event) => {
        state.search = event.target.value.trim();
        state.page = 1;
        load();
    }));
    filters.addEventListener('change', (event) => {
        if (event.target.name === 'type' || event.target.name === 'sort') {
            state[event.target.name] = event.target.value;
            state.page = 1;
            load();
        }
    });

    return load();
}
