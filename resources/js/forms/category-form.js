/**
 * Create / edit category modal (partials/category-modal.blade.php).
 */
import { api } from '../lib/api';
import { clearFormErrors, closeModal, fillForm, formValues, handleError, openModal, qs, toast, validateForm, withBusyButton } from '../lib/ui';

export function openCategoryForm({ category = null, onSaved }) {
    const dialog = qs('#category-modal');
    const form = qs('#category-form');
    const colorInput = form.elements.namedItem('color');
    const colorLabel = qs('[data-color-value]', form);

    form.reset();
    clearFormErrors(form);
    qs('[data-modal-title]', dialog).textContent = category ? 'Redaguoti kategoriją' : 'Nauja kategorija';
    fillForm(form, {
        name: category?.name ?? '',
        type: category?.type ?? 'expense',
        color: category?.color ?? '#16a34a',
        description: category?.description ?? '',
    });

    colorLabel.textContent = colorInput.value.toUpperCase();
    colorInput.oninput = () => (colorLabel.textContent = colorInput.value.toUpperCase());

    form.onsubmit = async (event) => {
        event.preventDefault();
        if (!validateForm(form)) {
            return;
        }

        const values = formValues(form);
        const body = { name: values.name, type: values.type, color: values.color.toUpperCase(), description: values.description };

        await withBusyButton(qs('button[type=submit]', form), async () => {
            try {
                const response = category
                    ? await api(`/categories/${category.id}`, { method: 'PUT', body })
                    : await api('/categories', { method: 'POST', body });

                closeModal(dialog);
                toast(category ? 'Kategorija atnaujinta.' : `Kategorija „${response.data.name}“ sukurta.`);
                onSaved(response.data);
            } catch (error) {
                handleError(error, form);
            }
        });
    };

    openModal(dialog);
}
