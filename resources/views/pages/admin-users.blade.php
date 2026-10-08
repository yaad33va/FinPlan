@extends('layouts.app')

@section('title', 'Naudotojai')
@section('page', 'admin-users')
@section('auth', 'admin')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Naudotojai</h1>
                <p>Administratorius gali peržiūrėti naudotojų duomenis ir šalinti narių paskyras.</p>
            </div>
        </div>

        <form id="user-filters" class="toolbar" role="search">
            <div class="field field-grow">
                <label for="filter-user-search">Paieška</label>
                <div class="input-icon">
                    <i data-lucide="search"></i>
                    <input class="input" id="filter-user-search" name="search" type="search" placeholder="Vardas arba el. paštas">
                </div>
            </div>
            <div class="field">
                <label for="filter-role">Rolė</label>
                <select class="select" id="filter-role" name="role">
                    <option value="">Visos</option>
                    <option value="member">Narys</option>
                    <option value="admin">Administratorius</option>
                </select>
            </div>
            <div class="field">
                <label for="filter-user-sort">Rikiavimas</label>
                <select class="select" id="filter-user-sort" name="sort">
                    <option value="name">Vardas A–Z</option>
                    <option value="-name">Vardas Z–A</option>
                    <option value="-created_at">Naujausi</option>
                    <option value="created_at">Seniausi</option>
                </select>
            </div>
        </form>

        <div id="user-list"></div>
        <nav id="user-pagination" aria-label="Puslapiai"></nav>
    </div>
@endsection
