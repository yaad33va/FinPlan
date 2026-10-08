/**
 * Create / edit budget modal (partials/budget-modal.blade.php).
 */
import { api } from '../lib/api';
import { clearFormErrors, closeModal, currentMonth, fillForm, formValues, handleError, openModal, qs, toast, validateForm, withBusyButton } from '../lib/ui';

export function openBudgetForm({ categoryId, budget = null, onSaved }) {
    const dialog = qs('#budget-modal');
    const form = qs('#budget-form');

    form.reset();
    clearFormErrors(form);
    qs('[data-modal-title]', dialog).textContent = budget ? 'Redaguoti biudžetą' : 'Naujas biudžetas';
    fillForm(form, {
        month: budget?.month ?? currentMonth(),
        amount: budget?.amount ?? '',
        note: budget?.note ?? '',
    });

    form.onsubmit = async (event) => {
        event.preventDefault();
        if (!validateForm(form)) {
            return;
        }

        const body = formValues(form);
        const url = `/categories/${categoryId}/budgets`;

        await withBusyButton(qs('button[type=submit]', form), async () => {
            try {
                const response = budget
                    ? await api(`${url}/${budget.id}`, { method: 'PUT', body })
                    : await api(url, { method: 'POST', body });

                closeModal(dialog);
                toast(budget ? 'Biudžetas atnaujintas.' : 'Biudžetas sukurtas.');
                onSaved(response.data);
            } catch (error) {
                handleError(error, form);
            }
        });
    };

    openModal(dialog);
}
