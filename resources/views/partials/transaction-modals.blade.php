{{-- Create / edit transaction (POST or PUT .../budgets/{id}/transactions) --}}
<dialog id="transaction-modal" class="modal" aria-labelledby="transaction-modal-title">
    <form id="transaction-form" class="form" novalidate>
        <div class="modal-header">
            <h2 id="transaction-modal-title"><i data-lucide="receipt"></i> <span data-modal-title>Nauja operacija</span></h2>
            <button class="icon-btn" type="button" data-close-modal aria-label="Uždaryti"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body form">
            <div class="form-row">
                <div class="field">
                    <label class="required" for="transaction-amount">Suma (€)</label>
                    <input class="input" id="transaction-amount" name="amount" type="number" min="0.01" step="0.01" required placeholder="25.50">
                </div>
                <div class="field">
                    <label class="required" for="transaction-date">Data</label>
                    <input class="input" id="transaction-date" name="occurred_on" type="date" required>
                    <span class="field-hint">Turi būti biudžeto mėnesio data.</span>
                </div>
            </div>
            <div class="field">
                <label class="required" for="transaction-description">Aprašymas</label>
                <input class="input" id="transaction-description" name="description" type="text" required minlength="2" maxlength="255" placeholder="pvz. Savaitės maisto pirkiniai">
            </div>
            <div class="field">
                <label for="transaction-merchant">Pardavėjas / mokėtojas</label>
                <input class="input" id="transaction-merchant" name="merchant" type="text" maxlength="100" placeholder="pvz. Maxima" list="merchant-suggestions">
                <datalist id="merchant-suggestions">
                    <option value="Maxima"></option>
                    <option value="Lidl"></option>
                    <option value="Rimi"></option>
                    <option value="Iki"></option>
                    <option value="Circle K"></option>
                    <option value="Bolt"></option>
                </datalist>
            </div>
            <div class="field">
                <span class="label required" id="transaction-method-label">Mokėjimo būdas</span>
                <div class="choice-group" role="radiogroup" aria-labelledby="transaction-method-label">
                    <div class="choice">
                        <input type="radio" id="method-card" name="payment_method" value="card" checked>
                        <label for="method-card"><i data-lucide="credit-card"></i> Kortele</label>
                    </div>
                    <div class="choice">
                        <input type="radio" id="method-cash" name="payment_method" value="cash">
                        <label for="method-cash"><i data-lucide="banknote"></i> Grynais</label>
                    </div>
                    <div class="choice">
                        <input type="radio" id="method-bank" name="payment_method" value="bank_transfer">
                        <label for="method-bank"><i data-lucide="landmark"></i> Pavedimu</label>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" type="button" data-close-modal>Atšaukti</button>
            <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Išsaugoti</button>
        </div>
    </form>
</dialog>

{{-- Transaction details --}}
<dialog id="transaction-details-modal" class="modal" aria-labelledby="transaction-details-title">
    <div class="modal-header">
        <h2 id="transaction-details-title"><i data-lucide="receipt"></i> Operacijos informacija</h2>
        <button class="icon-btn" type="button" data-close-modal aria-label="Uždaryti"><i data-lucide="x"></i></button>
    </div>
    <div class="modal-body">
        <dl class="details-list" data-details></dl>
    </div>
    <div class="modal-footer" data-details-actions></div>
</dialog>
