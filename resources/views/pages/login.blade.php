@extends('layouts.app')

@section('title', 'Prisijungimas')
@section('page', 'login')
@section('auth', 'guest')

@section('content')
    <div class="container">
        <div class="auth-layout fade-in">
            <aside class="auth-aside">
                <img src="{{ asset('images/auth.svg') }}" alt="Piniginė ir apsaugos skydas" width="340" height="300">
                <h2>Sveiki sugrįžę!</h2>
                <p>Prisijunkite ir peržiūrėkite savo mėnesio biudžetus bei operacijas.</p>
            </aside>

            <div class="auth-form">
                {{-- Step 1: email + password --}}
                <form id="login-form" class="form" novalidate>
                    <div>
                        <h1>Prisijungimas</h1>
                        <p class="muted">Įveskite savo el. paštą ir slaptažodį.</p>
                    </div>

                    <div class="field">
                        <label class="required" for="login-email">El. paštas</label>
                        <div class="input-icon">
                            <i data-lucide="mail"></i>
                            <input class="input" id="login-email" name="email" type="email" autocomplete="email" required placeholder="vardas@pastas.lt">
                        </div>
                    </div>

                    <div class="field">
                        <label class="required" for="login-password">Slaptažodis</label>
                        <div class="input-icon">
                            <i data-lucide="lock"></i>
                            <input class="input" id="login-password" name="password" type="password" autocomplete="current-password" required>
                            <button class="input-action" type="button" data-toggle-password="login-password" aria-label="Rodyti slaptažodį"><i data-lucide="eye"></i></button>
                        </div>
                    </div>

                    <button class="btn btn-primary btn-lg btn-block" type="submit"><i data-lucide="log-in"></i> Prisijungti</button>

                    <div class="demo-accounts">
                        <strong>Demonstracinės paskyros:</strong>
                        <button type="button" data-demo="jonas@finplan.lt" data-password="Jonas12345">Jonas (narys)</button>,
                        <button type="button" data-demo="ona@finplan.lt" data-password="Ona123456">Ona (narė)</button>,
                        <button type="button" data-demo="admin@finplan.lt" data-password="Admin12345">administratorius</button>
                    </div>
                </form>

                {{-- Step 2: two-factor code (shown only when 2FA is enabled) --}}
                <form id="two-factor-form" class="form" novalidate hidden>
                    <div>
                        <h1><i data-lucide="smartphone"></i> Patvirtinimo kodas</h1>
                        <p class="muted">Įveskite 6 skaitmenų kodą iš autentifikavimo programėlės (Google Authenticator, Authy ir kt.).</p>
                    </div>
                    <div class="field">
                        <label class="required" for="two-factor-code">Kodas</label>
                        <input class="input code-input" id="two-factor-code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" required placeholder="000000">
                    </div>
                    <button class="btn btn-primary btn-lg btn-block" type="submit"><i data-lucide="shield-check"></i> Patvirtinti</button>
                    <button class="btn btn-ghost btn-block" type="button" data-action="back-to-login"><i data-lucide="arrow-left"></i> Grįžti</button>
                </form>

                <p class="auth-switch">Neturite paskyros? <a href="{{ route('register') }}">Registruokitės</a></p>
            </div>
        </div>
    </div>
@endsection
