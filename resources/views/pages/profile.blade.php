@extends('layouts.app')

@section('title', 'Profilis')
@section('page', 'profile')
@section('auth', 'user')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Profilis</h1>
                <p>Paskyros informacija ir saugumo nustatymai.</p>
            </div>
        </div>

        <div class="profile-grid">
            <section class="card fade-in" id="profile-card">
                <div class="skeleton skeleton-row"></div>
                <div class="skeleton skeleton-row"></div>
            </section>

            <section class="card fade-in">
                <div class="card-header">
                    <h2><i data-lucide="shield-check"></i> Dviejų faktorių autentifikacija</h2>
                    <span class="badge badge-neutral" data-two-factor-badge>…</span>
                </div>
                <p class="muted">
                    Prisijungiant be slaptažodžio reikės ir 6 skaitmenų kodo iš autentifikavimo programėlės
                    (Google Authenticator, Microsoft Authenticator, Authy). Net sužinojus slaptažodį, be telefono prisijungti nepavyks.
                </p>
                <button class="btn btn-primary" type="button" data-action="enable-2fa" hidden><i data-lucide="qr-code"></i> Įjungti 2FA</button>
                <button class="btn btn-secondary" type="button" data-action="disable-2fa" hidden><i data-lucide="shield-off"></i> Išjungti 2FA</button>
            </section>
        </div>
    </div>

    {{-- Enable 2FA: QR code + confirmation code --}}
    <dialog id="enable-2fa-modal" class="modal" aria-labelledby="enable-2fa-title">
        <form id="enable-2fa-form" class="form" novalidate>
            <div class="modal-header">
                <h2 id="enable-2fa-title"><i data-lucide="qr-code"></i> 2FA įjungimas</h2>
                <button class="icon-btn" type="button" data-close-modal aria-label="Uždaryti"><i data-lucide="x"></i></button>
            </div>
            <div class="modal-body form">
                <ol class="muted" style="margin: 0; padding-left: 20px">
                    <li>Atidarykite autentifikavimo programėlę telefone.</li>
                    <li>Nuskenuokite QR kodą arba įveskite slaptą raktą ranka.</li>
                    <li>Įveskite programėlės rodomą 6 skaitmenų kodą.</li>
                </ol>
                <div class="qr-box">
                    <canvas id="two-factor-qr" width="200" height="200" aria-label="2FA QR kodas"></canvas>
                    <span class="secret-code" data-two-factor-secret></span>
                </div>
                <div class="field">
                    <label class="required" for="enable-2fa-code">Patvirtinimo kodas</label>
                    <input class="input code-input" id="enable-2fa-code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" pattern="[0-9]{6}" required placeholder="000000">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" type="button" data-close-modal>Atšaukti</button>
                <button class="btn btn-primary" type="submit"><i data-lucide="shield-check"></i> Įjungti</button>
            </div>
        </form>
    </dialog>

    {{-- Disable 2FA: password confirmation --}}
    <dialog id="disable-2fa-modal" class="modal modal-sm" aria-labelledby="disable-2fa-title">
        <form id="disable-2fa-form" class="form" novalidate>
            <div class="modal-header">
                <h2 id="disable-2fa-title"><i data-lucide="shield-off"></i> 2FA išjungimas</h2>
                <button class="icon-btn" type="button" data-close-modal aria-label="Uždaryti"><i data-lucide="x"></i></button>
            </div>
            <div class="modal-body form">
                <p class="muted">Saugumo sumetimais įveskite savo slaptažodį.</p>
                <div class="field">
                    <label class="required" for="disable-2fa-password">Slaptažodis</label>
                    <input class="input" id="disable-2fa-password" name="password" type="password" autocomplete="current-password" required>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" type="button" data-close-modal>Atšaukti</button>
                <button class="btn btn-danger" type="submit"><i data-lucide="shield-off"></i> Išjungti</button>
            </div>
        </form>
    </dialog>
@endsection
