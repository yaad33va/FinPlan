@extends('layouts.app')

@section('title', 'Registracija')
@section('page', 'register')
@section('auth', 'guest')

@section('content')
    <div class="container">
        <div class="auth-layout fade-in">
            <aside class="auth-aside">
                <img src="{{ asset('images/auth.svg') }}" alt="Piniginė ir apsaugos skydas" width="340" height="300">
                <h2>Susikurkite paskyrą</h2>
                <p>Po registracijos galėsite iš karto kurti kategorijas, biudžetus ir vesti operacijas.</p>
            </aside>

            <div class="auth-form">
                <form id="register-form" class="form" novalidate>
                    <div>
                        <h1>Registracija</h1>
                        <p class="muted">Visi laukai privalomi.</p>
                    </div>

                    <div class="field">
                        <label class="required" for="register-name">Vardas ir pavardė</label>
                        <div class="input-icon">
                            <i data-lucide="user"></i>
                            <input class="input" id="register-name" name="name" type="text" autocomplete="name" required minlength="2" maxlength="100" placeholder="Vardenis Pavardenis">
                        </div>
                    </div>

                    <div class="field">
                        <label class="required" for="register-email">El. paštas</label>
                        <div class="input-icon">
                            <i data-lucide="mail"></i>
                            <input class="input" id="register-email" name="email" type="email" autocomplete="email" required placeholder="vardas@pastas.lt">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label class="required" for="register-password">Slaptažodis</label>
                            <div class="input-icon">
                                <i data-lucide="lock"></i>
                                <input class="input" id="register-password" name="password" type="password" autocomplete="new-password" required minlength="8">
                                <button class="input-action" type="button" data-toggle-password="register-password" aria-label="Rodyti slaptažodį"><i data-lucide="eye"></i></button>
                            </div>
                            <span class="field-hint">Bent 8 simboliai, raidės ir skaičiai.</span>
                        </div>
                        <div class="field">
                            <label class="required" for="register-password-confirmation">Pakartokite slaptažodį</label>
                            <div class="input-icon">
                                <i data-lucide="lock"></i>
                                <input class="input" id="register-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required>
                            </div>
                        </div>
                    </div>

                    <label class="checkbox">
                        <input type="checkbox" name="terms" required>
                        Sutinku, kad mano duomenys būtų saugomi FinPlan sistemoje
                    </label>

                    <button class="btn btn-primary btn-lg btn-block" type="submit"><i data-lucide="user-plus"></i> Sukurti paskyrą</button>
                </form>

                <p class="auth-switch">Jau turite paskyrą? <a href="{{ route('login') }}">Prisijunkite</a></p>
            </div>
        </div>
    </div>
@endsection
