{{-- Create / edit category (POST or PUT /api/v1/categories) --}}
<dialog id="category-modal" class="modal" aria-labelledby="category-modal-title">
    <form id="category-form" class="form" novalidate>
        <div class="modal-header">
            <h2 id="category-modal-title"><i data-lucide="folder-open"></i> <span data-modal-title>Nauja kategorija</span></h2>
            <button class="icon-btn" type="button" data-close-modal aria-label="Uždaryti"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body form">
            <div class="field">
                <label class="required" for="category-name">Pavadinimas</label>
                <input class="input" id="category-name" name="name" type="text" required minlength="2" maxlength="100" placeholder="pvz. Maistas ir buities prekės">
            </div>

            <div class="field">
                <span class="label required" id="category-type-label">Tipas</span>
                <div class="choice-group" role="radiogroup" aria-labelledby="category-type-label">
                    <div class="choice">
                        <input type="radio" id="category-type-expense" name="type" value="expense" checked>
                        <label for="category-type-expense"><i data-lucide="trending-down"></i> Išlaidos</label>
                    </div>
                    <div class="choice">
                        <input type="radio" id="category-type-income" name="type" value="income">
                        <label for="category-type-income"><i data-lucide="trending-up"></i> Pajamos</label>
                    </div>
                </div>
            </div>

            <div class="field">
                <label for="category-color">Spalva</label>
                <div class="row">
                    <input class="input-color" id="category-color" name="color" type="color" value="#16a34a">
                    <span class="muted" data-color-value>#16A34A</span>
                </div>
            </div>

            <div class="field">
                <label for="category-description">Aprašymas</label>
                <textarea class="textarea" id="category-description" name="description" maxlength="1000" placeholder="Kas priskiriama šiai kategorijai?"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" type="button" data-close-modal>Atšaukti</button>
            <button class="btn btn-primary" type="submit"><i data-lucide="save"></i> Išsaugoti</button>
        </div>
    </form>
</dialog>
