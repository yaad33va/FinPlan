{{-- Create / edit budget (POST or PUT /api/v1/categories/{id}/budgets) --}}
<dialog id="budget-modal" class="modal" aria-labelledby="budget-modal-title">
    <form id="budget-form" class="form" novalidate>
        <div class="modal-header">
            <h2 id="budget-modal-title"><i data-lucide="target"></i> <span data-modal-title>Naujas biudžetas</span></h2>
            <button class="icon-btn" type="button" data-close-modal aria-label="Uždaryti"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body form">
            <div class="form-row">
                <div class="field">
                    <label class="required" for="budget-month">Mėnuo</label>
                    <input class="input" id="budget-month" name="month" type="month" required>
                </div>
                <div class="field">
                    <label class="required" for="budget-amount">Suma (€)</label>
                    <input class="input" id="budget-amount" name="amount" type="number" min="0.01" step="0.01" required placeholder="400.00">
                </div>
            </div>
            <div class="field">
                <label for="budget-note">Pastaba</label>
                <textarea class="textarea" id="budget-note" name="note" maxlength="255" placeholder="pvz. Atostogų mėnuo"></textarea>
            </div>
            <p class="field-hint"><i data-lucide="info"></i> Jei biudžetas jau turi operacijų, jo mėnesio pakeisti negalima.</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" type="button" data-close-modal>Atšaukti</button>
            <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Išsaugoti</button>
        </div>
    </form>
</dialog>
