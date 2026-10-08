@extends('layouts.app')

@section('title', 'Kategorija')
@section('page', 'category')
@section('auth', 'user')

@section('content')
    <div class="container" id="category-page" data-category-id="{{ $categoryId }}">
        <ol class="breadcrumbs">
            <li><a href="{{ route('pages.categories') }}" data-categories-link>Kategorijos</a></li>
            <li data-category-name>…</li>
        </ol>

        <div id="category-header" class="card entity-header">
            <div class="skeleton skeleton-row" style="width: 60%"></div>
        </div>

        <div class="tabs" role="tablist">
            <button class="tab" type="button" role="tab" id="tab-budgets" aria-selected="true" aria-controls="panel-budgets"><i data-lucide="target"></i> Biudžetai</button>
            <button class="tab" type="button" role="tab" id="tab-transactions" aria-selected="false" aria-controls="panel-transactions"><i data-lucide="receipt"></i> Visos operacijos</button>
        </div>

        {{-- Budgets of the category --}}
        <section id="panel-budgets" role="tabpanel" aria-labelledby="tab-budgets">
            <form id="budget-filters" class="toolbar">
                <div class="field">
                    <label for="filter-month-from">Nuo mėnesio</label>
                    <input class="input" id="filter-month-from" name="month_from" type="month">
                </div>
                <div class="field">
                    <label for="filter-month-to">Iki mėnesio</label>
                    <input class="input" id="filter-month-to" name="month_to" type="month">
                </div>
                <div class="field">
                    <label for="filter-budget-sort">Rikiavimas</label>
                    <select class="select" id="filter-budget-sort" name="sort">
                        <option value="-month">Naujausi mėnesiai</option>
                        <option value="month">Seniausi mėnesiai</option>
                        <option value="-amount">Didžiausia suma</option>
                        <option value="amount">Mažiausia suma</option>
                    </select>
                </div>
                <div class="field">
                    <span class="label">Būsena</span>
                    <label class="checkbox"><input type="checkbox" name="exceeded" value="true"> Tik viršyti</label>
                </div>
                <div class="field field-grow" style="text-align: right">
                    <button class="btn btn-primary" type="button" data-action="create-budget"><i data-lucide="plus"></i> Naujas biudžetas</button>
                </div>
            </form>

            <div id="budget-list"></div>
            <nav id="budget-pagination" aria-label="Biudžetų puslapiai"></nav>
        </section>

        {{-- All transactions of the category --}}
        <section id="panel-transactions" role="tabpanel" aria-labelledby="tab-transactions" hidden>
            <form id="category-transaction-filters" class="toolbar">
                <div class="field field-grow">
                    <label for="filter-tx-search">Paieška</label>
                    <div class="input-icon">
                        <i data-lucide="search"></i>
                        <input class="input" id="filter-tx-search" name="search" type="search" placeholder="Aprašymas arba pardavėjas">
                    </div>
                </div>
                <div class="field">
                    <label for="filter-tx-from">Nuo</label>
                    <input class="input" id="filter-tx-from" name="date_from" type="date">
                </div>
                <div class="field">
                    <label for="filter-tx-to">Iki</label>
                    <input class="input" id="filter-tx-to" name="date_to" type="date">
                </div>
                <div class="field">
                    <label for="filter-tx-sort">Rikiavimas</label>
                    <select class="select" id="filter-tx-sort" name="sort">
                        <option value="-occurred_on">Naujausios</option>
                        <option value="occurred_on">Seniausios</option>
                        <option value="-amount">Didžiausia suma</option>
                        <option value="amount">Mažiausia suma</option>
                    </select>
                </div>
            </form>

            <div id="category-transaction-list"></div>
            <nav id="category-transaction-pagination" aria-label="Operacijų puslapiai"></nav>
        </section>
    </div>

    @include('partials.category-modal')
    @include('partials.budget-modal')
    @include('partials.transaction-modals')
@endsection
