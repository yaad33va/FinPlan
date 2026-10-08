@extends('layouts.app')

@section('title', 'Asmeninių finansų planavimas')
@section('page', 'landing')

@section('content')
    <section class="hero">
        <div class="container hero-grid">
            <div class="fade-in">
                <span class="hero-eyebrow"><i data-lucide="sparkles"></i> Biudžetas be skaičiuoklių</span>
                <h1>Žinok, kur keliauja <span class="accent">kiekvienas euras</span></h1>
                <p class="hero-lead">
                    FinPlan padeda susikurti pajamų ir išlaidų kategorijas, kiekvienai nusistatyti mėnesio biudžetą
                    ir sekti, kiek jau išleista ir kiek dar liko.
                </p>
                <div class="hero-actions">
                    <a class="btn btn-primary btn-lg" href="{{ route('register') }}" data-auth="guest">
                        Pradėti nemokamai <i data-lucide="arrow-right" class="icon-arrow"></i>
                    </a>
                    <a class="btn btn-secondary btn-lg" href="{{ route('login') }}" data-auth="guest">
                        <i data-lucide="log-in"></i> Prisijungti
                    </a>
                    <a class="btn btn-primary btn-lg" href="{{ route('pages.dashboard') }}" data-auth="user">
                        Eiti į apžvalgą <i data-lucide="arrow-right" class="icon-arrow"></i>
                    </a>
                </div>
            </div>
            <img class="hero-image" src="{{ asset('images/hero.svg') }}" alt="Biudžeto apžvalgos langas su pajamų ir išlaidų diagramomis" width="560" height="440">
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-title">
                <h2>Viskas, ko reikia asmeniniam biudžetui</h2>
                <p>Paprasta struktūra: kategorija → mėnesio biudžetas → operacijos.</p>
            </div>
            <div class="grid grid-3 stagger">
                <article class="card feature-card card-hover">
                    <span class="feature-icon"><i data-lucide="folder-open"></i></span>
                    <h3>Kategorijos</h3>
                    <p>Suskirstyk pajamas ir išlaidas: maistas, būstas, transportas, atlyginimas ir kt. Kiekviena kategorija turi savo spalvą.</p>
                </article>
                <article class="card feature-card card-hover">
                    <span class="feature-icon"><i data-lucide="target"></i></span>
                    <h3>Mėnesio biudžetai</h3>
                    <p>Kiekvienai kategorijai nustatyk sumą mėnesiui ir matyk išnaudotą dalį bei likutį. Viršijus biudžetą – iškart pamatysi.</p>
                </article>
                <article class="card feature-card card-hover">
                    <span class="feature-icon"><i data-lucide="receipt"></i></span>
                    <h3>Operacijos</h3>
                    <p>Įvesk pirkinius ir pajamas su data, pardavėju ir mokėjimo būdu. Filtruok pagal sumą, datą ar paiešką.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container steps-grid">
            <img class="steps-image" src="{{ asset('images/steps.svg') }}" alt="Telefono ekrane rodomi biudžetai su išnaudojimo juostomis" width="480" height="380" loading="lazy">
            <div>
                <h2>Kaip tai veikia?</h2>
                <ol class="steps">
                    <li>
                        <h3>Prisiregistruok</h3>
                        <p>Užtenka vardo, el. pašto ir slaptažodžio. Papildomai saugai gali įjungti dviejų faktorių autentifikaciją.</p>
                    </li>
                    <li>
                        <h3>Susikurk kategorijas ir biudžetus</h3>
                        <p>Nuspręsk, kiek gali išleisti kiekvienai sričiai per mėnesį.</p>
                    </li>
                    <li>
                        <h3>Vesk operacijas ir stebėk apžvalgą</h3>
                        <p>Apžvalgoje matysi mėnesio pajamas, išlaidas, balansą ir viršytus biudžetus.</p>
                    </li>
                </ol>
            </div>
        </div>
    </section>

    <section class="container">
        <div class="cta-band">
            <div>
                <h2>Pradėk planuoti jau šiandien</h2>
                <p>Registracija užtrunka mažiau nei minutę.</p>
            </div>
            <a class="btn btn-lg" href="{{ route('register') }}"><i data-lucide="user-plus"></i> Sukurti paskyrą</a>
        </div>
    </section>
@endsection
