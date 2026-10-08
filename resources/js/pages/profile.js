/**
 * Profile (GET /auth/me) and two-factor authentication settings.
 */
import QRCode from 'qrcode';
import { api } from '../lib/api';
import { icon } from '../lib/icons';
import { closeModal, formatDate, formValues, h, handleError, openModal, qs, toast, validateForm, withBusyButton } from '../lib/ui';

let me;

function renderProfile() {
    const initials = me.name.split(' ').map((part) => part[0]).join('').slice(0, 2).toUpperCase();

    qs('#profile-card').replaceChildren(
        h(
            'div',
            { class: 'row', style: { marginBottom: '16px' } },
            h('span', { class: 'avatar' }, initials),
            h('div', {}, h('h2', { style: { margin: 0 } }, me.name), h('span', { class: `badge ${me.role === 'admin' ? 'badge-admin' : 'badge-member'}` }, me.role === 'admin' ? 'Administratorius' : 'Narys')),
        ),
        h(
            'dl',
            { class: 'details-list' },
            h('dt', {}, 'El. paštas'),
            h('dd', {}, me.email),
            h('dt', {}, 'Kategorijų'),
            h('dd', {}, String(me.categories_count ?? 0)),
            h('dt', {}, 'Užsiregistravo'),
            h('dd', {}, formatDate(me.created_at)),
        ),
    );

    const badge = qs('[data-two-factor-badge]');
    badge.className = `badge ${me.two_factor_enabled ? 'badge-income' : 'badge-neutral'}`;
    badge.replaceChildren(icon(me.two_factor_enabled ? 'shield-check' : 'shield-off'), me.two_factor_enabled ? 'Įjungta' : 'Išjungta');
    qs('[data-action="enable-2fa"]').hidden = me.two_factor_enabled;
    qs('[data-action="disable-2fa"]').hidden = !me.two_factor_enabled;
}

async function startTwoFactorSetup(button) {
    await withBusyButton(button, async () => {
        try {
            const setup = await api('/auth/two-factor', { method: 'POST' });
            await QRCode.toCanvas(qs('#two-factor-qr'), setup.otpauth_url, { width: 200, margin: 1, color: { dark: '#0a2240' } });
            qs('[data-two-factor-secret]').textContent = setup.secret.match(/.{1,4}/g).join(' ');
            qs('#enable-2fa-form').reset();
            openModal(qs('#enable-2fa-modal'));
        } catch (error) {
            handleError(error);
        }
    });
}

export default async function initProfilePage() {
    try {
        me = (await api('/auth/me')).data;
        renderProfile();
    } catch (error) {
        handleError(error);

        return;
    }

    qs('[data-action="enable-2fa"]').addEventListener('click', (event) => startTwoFactorSetup(event.currentTarget));
    qs('[data-action="disable-2fa"]').addEventListener('click', () => {
        qs('#disable-2fa-form').reset();
        openModal(qs('#disable-2fa-modal'));
    });

    const enableForm = qs('#enable-2fa-form');
    enableForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!validateForm(enableForm)) {
            return;
        }

        await withBusyButton(qs('button[type=submit]', enableForm), async () => {
            try {
                me = { ...me, ...(await api('/auth/two-factor/confirm', { method: 'POST', body: formValues(enableForm) })).data };
                closeModal(qs('#enable-2fa-modal'));
                renderProfile();
                toast('Dviejų faktorių autentifikacija įjungta.');
            } catch (error) {
                handleError(error, enableForm);
            }
        });
    });

    const disableForm = qs('#disable-2fa-form');
    disableForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!validateForm(disableForm)) {
            return;
        }

        await withBusyButton(qs('button[type=submit]', disableForm), async () => {
            try {
                await api('/auth/two-factor', { method: 'DELETE', body: formValues(disableForm) });
                me.two_factor_enabled = false;
                closeModal(qs('#disable-2fa-modal'));
                renderProfile();
                toast('Dviejų faktorių autentifikacija išjungta.', 'info');
            } catch (error) {
                handleError(error, disableForm);
            }
        });
    });
}
