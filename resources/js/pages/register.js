import { register } from '../lib/auth';
import { flash } from '../lib/session';
import { bindPasswordToggles, formValues, handleError, qs, showFormErrors, validateForm, withBusyButton } from '../lib/ui';

export default function initRegisterPage() {
    const form = qs('#register-form');
    bindPasswordToggles(form);

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!validateForm(form)) {
            return;
        }

        const values = formValues(form);

        if (values.password !== values.password_confirmation) {
            showFormErrors(form, { password_confirmation: 'Slaptažodžiai nesutampa.' });

            return;
        }

        await withBusyButton(qs('button[type=submit]', form), async () => {
            try {
                const user = await register({
                    name: values.name,
                    email: values.email,
                    password: values.password,
                    password_confirmation: values.password_confirmation,
                });
                flash(`Sveiki, ${user.name.split(' ')[0]}! Paskyra sukurta – pradėkite nuo kategorijų.`);
                window.location.href = '/categories';
            } catch (error) {
                handleError(error, form);
            }
        });
    });
}
