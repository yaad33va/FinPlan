@extends('layouts.app')

@section('title', 'Apžvalga')
@section('page', 'dashboard')
@section('auth', 'user')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Labas, <span data-user-name>naudotojau</span>!</h1>
                <p>Mėnesio pajamos, išlaidos ir biudžetų būklė.</p>
            </div>
            <div class="month-switcher" role="group" aria-label="Mėnesio pasirinkimas">
                <button class="icon-btn" type="button" data-month-step="-1" aria-label="Ankstesnis mėnuo"><i data-lucide="chevron-left"></i></button>
                <label class="sr-only" for="dashboard-month">Mėnuo</label>
                <input class="input" id="dashboard-month" type="month">
                <button class="icon-btn" type="button" data-month-step="1" aria-label="Kitas mėnuo"><i data-lucide="chevron-right"></i></button>
            </div>
        </div>

        <div id="dashboard-stats" class="grid grid-4">
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
            <div class="skeleton skeleton-card"></div>
        </div>

        <div class="dashboard-columns">
            <section class="card">
                <div class="card-header">
                    <h2>Biudžetai</h2>
                    <a class="btn btn-ghost" href="{{ route('pages.categories') }}">Visos kategorijos <i data-lucide="arrow-right" class="icon-arrow"></i></a>
                </div>
                <div id="dashboard-budgets">
                    <div class="skeleton skeleton-row"></div>
                    <div class="skeleton skeleton-row"></div>
                    <div class="skeleton skeleton-row"></div>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <h2>Naujausios operacijos</h2>
                </div>
                <div id="dashboard-transactions">
                    <div class="skeleton skeleton-row"></div>
                    <div class="skeleton skeleton-row"></div>
                    <div class="skeleton skeleton-row"></div>
                </div>
            </section>
        </div>
    </div>
@endsection
