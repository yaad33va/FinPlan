@extends('layouts.app')

@section('title', 'Biudžetas')
@section('page', 'budget')
@section('auth', 'user')

@section('content')
    <div class="container" id="budget-page" data-category-id="{{ $categoryId }}" data-budget-id="{{ $budgetId }}">
        <ol class="breadcrumbs">
            <li><a href="{{ route('pages.categories') }}">Kategorijos</a></li>
            <li><a href="{{ route('pages.category', $categoryId) }}" data-category-name>…</a></li>
            <li data-budget-month>…</li>
        </ol>

        <section id="budget-header" class="card entity-header">
            <div class="skeleton skeleton-row" style="width: 60%"></div>
        </section>

        <div class="page-header">
            <div>
                <h2>Operacijos</h2>
            </div>
            <button class="btn btn-primary" type="button" data-action="create-transaction"><i data-lucide="plus"></i> Nauja operacija</button>
        </div>

        <form id="transaction-filters" class="toolbar">
            <div class="field field-grow">
                <label for="filter-search">Paieška</label>
                <div class="input-icon">
                    <i data-lucide="search"></i>
                    <input class="input" id="filter-search" name="search" type="search" placeholder="Aprašymas arba pardavėjas">
                </div>
            </div>
            <div class="field">
                <label for="filter-method">Mokėjimo būdas</label>
                <select class="select" id="filter-method" name="payment_method">
                    <option value="">Visi</option>
                    <option value="card">Kortele</option>
                    <option value="cash">Grynais</option>
                    <option value="bank_transfer">Pavedimu</option>
                </select>
            </div>
            <div class="field">
                <label for="filter-min">Suma nuo (€)</label>
                <input class="input" id="filter-min" name="min_amount" type="number" min="0" step="0.01">
            </div>
            <div class="field">
                <label for="filter-max">Suma iki (€)</label>
                <input class="input" id="filter-max" name="max_amount" type="number" min="0" step="0.01">
            </div>
            <div class="field">
                <label for="filter-sort">Rikiavimas</label>
                <select class="select" id="filter-sort" name="sort">
                    <option value="-occurred_on">Naujausios</option>
                    <option value="occurred_on">Seniausios</option>
                    <option value="-amount">Didžiausia suma</option>
                    <option value="amount">Mažiausia suma</option>
                </select>
            </div>
        </form>

        <div id="transaction-list"></div>
        <nav id="transaction-pagination" aria-label="Operacijų puslapiai"></nav>
    </div>

    @include('partials.budget-modal')
    @include('partials.transaction-modals')
@endsection
