@extends('layouts.app')

@section('title', 'Kategorijos')
@section('page', 'categories')
@section('auth', 'user')

@section('content')
    <div class="container">
        <ol class="breadcrumbs" data-admin-breadcrumbs hidden>
            <li><a href="{{ route('pages.admin.users') }}">Naudotojai</a></li>
            <li data-owner-name></li>
        </ol>

        <div class="page-header">
            <div>
                <h1 data-page-title>Mano kategorijos</h1>
                <p data-page-subtitle>Pajamų ir išlaidų kategorijos su mėnesio biudžetais.</p>
            </div>
            <button class="btn btn-primary" type="button" data-action="create-category">
                <i data-lucide="plus"></i> Nauja kategorija
            </button>
        </div>

        <form id="category-filters" class="toolbar" role="search">
            <div class="field field-grow">
                <label for="filter-search">Paieška</label>
                <div class="input-icon">
                    <i data-lucide="search"></i>
                    <input class="input" id="filter-search" name="search" type="search" placeholder="Kategorijos pavadinimas">
                </div>
            </div>
            <div class="field">
                <span class="label">Tipas</span>
                <div class="segmented">
                    <input type="radio" id="filter-type-all" name="type" value="" checked>
                    <label for="filter-type-all">Visos</label>
                    <input type="radio" id="filter-type-income" name="type" value="income">
                    <label for="filter-type-income"><i data-lucide="trending-up"></i> Pajamos</label>
                    <input type="radio" id="filter-type-expense" name="type" value="expense">
                    <label for="filter-type-expense"><i data-lucide="trending-down"></i> Išlaidos</label>
                </div>
            </div>
            <div class="field">
                <label for="filter-sort">Rikiavimas</label>
                <select class="select" id="filter-sort" name="sort">
                    <option value="name">Pavadinimas A–Z</option>
                    <option value="-name">Pavadinimas Z–A</option>
                    <option value="-created_at">Naujausios</option>
                    <option value="created_at">Seniausios</option>
                </select>
            </div>
        </form>

        <div id="category-list" class="grid grid-cards">
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
        </div>
        <nav id="category-pagination" aria-label="Puslapiai"></nav>
    </div>

    @include('partials.category-modal')
@endsection
