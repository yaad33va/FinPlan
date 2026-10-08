<!DOCTYPE html>
<html lang="lt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="FinPlan – asmeninių finansų planavimas: kategorijos, mėnesio biudžetai ir operacijos vienoje vietoje.">
    <title>@yield('title') · FinPlan</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/logo.svg') }}">

    {{-- Non-standard fonts from Google Fonts: Poppins (headings) and Nunito (text) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{--
    data-page:  which JavaScript module to run (resources/js/pages/*.js)
    data-access: who may open the page – guest (only logged out), user (member or admin), admin, any
--}}
<body class="page-@yield('page')" data-page="@yield('page')" data-access="@yield('auth', 'any')">
    <a class="sr-only" href="#main">Pereiti prie turinio</a>

    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="{{ route('home') }}">
                <img class="brand-logo" src="{{ asset('images/logo.svg') }}" alt="" width="38" height="38">
                <span class="brand-name">Fin<span>Plan</span></span>
            </a>

            <button class="nav-toggle" type="button" aria-label="Atidaryti meniu" aria-expanded="false" aria-controls="main-nav">
                <span></span><span></span><span></span>
            </button>

            <nav id="main-nav" class="main-nav" aria-label="Pagrindinis meniu">
                <ul>
                    <li data-auth="guest"><a href="{{ route('home') }}" data-nav="landing"><i data-lucide="house"></i> Pradžia</a></li>
                    <li data-auth="user"><a href="{{ route('pages.dashboard') }}" data-nav="dashboard"><i data-lucide="layout-dashboard"></i> Apžvalga</a></li>
                    <li data-auth="user"><a href="{{ route('pages.categories') }}" data-nav="categories category budget"><i data-lucide="folder-open"></i> Kategorijos</a></li>
                    <li data-auth="admin"><a href="{{ route('pages.admin.users') }}" data-nav="admin-users"><i data-lucide="shield-check"></i> Naudotojai</a></li>
                    <li data-auth="user">
                        <a href="{{ route('pages.profile') }}" data-nav="profile">
                            <i data-lucide="circle-user"></i>
                            <span class="nav-user-name" data-user-name>Profilis</span>
                            <span class="role-pill" data-auth="admin">admin</span>
                        </a>
                    </li>
                    <li data-auth="user"><button class="nav-button nav-cta" type="button" data-action="logout"><i data-lucide="log-out"></i> Atsijungti</button></li>
                    <li data-auth="guest"><a href="{{ route('login') }}" data-nav="login"><i data-lucide="log-in"></i> Prisijungti</a></li>
                    <li data-auth="guest"><a class="nav-cta" href="{{ route('register') }}" data-nav="register"><i data-lucide="user-plus"></i> Registruotis</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main id="main" class="site-main">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <a class="brand" href="{{ route('home') }}">
                    <img class="brand-logo" src="{{ asset('images/logo.svg') }}" alt="" width="32" height="32">
                    <span class="brand-name">Fin<span>Plan</span></span>
                </a>
                <p>Asmeninių finansų planavimas: kategorijos, mėnesio biudžetai ir operacijos vienoje vietoje.</p>
            </div>
            <div>
                <h4>Navigacija</h4>
                <ul>
                    <li><a href="{{ route('home') }}"><i data-lucide="house"></i> Pradžia</a></li>
                    <li><a href="{{ route('pages.dashboard') }}"><i data-lucide="layout-dashboard"></i> Apžvalga</a></li>
                    <li><a href="{{ route('pages.categories') }}"><i data-lucide="folder-open"></i> Kategorijos</a></li>
                </ul>
            </div>
            <div>
                <h4>Kūrėjams</h4>
                <ul>
                    <li><a href="{{ route('docs') }}"><i data-lucide="book-open"></i> API dokumentacija</a></li>
                    <li><a href="{{ url('/api/v1') }}"><i data-lucide="code-xml"></i> REST API</a></li>
                    <li><a href="mailto:info@finplan.lt"><i data-lucide="mail"></i> info@finplan.lt</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container row-between">
                <span>© {{ date('Y') }} FinPlan · Denisa Valinčiūtė, KTU</span>
                <span>T120B165 Saityno taikomųjų programų projektavimas</span>
            </div>
        </div>
    </footer>

    {{-- Shared confirmation modal (used before every delete) --}}
    <dialog id="confirm-modal" class="modal modal-sm modal-danger" aria-labelledby="confirm-title">
        <form method="dialog">
            <div class="modal-header">
                <h2 id="confirm-title"><i data-lucide="triangle-alert"></i> <span data-confirm-title>Ar tikrai?</span></h2>
            </div>
            <div class="modal-body">
                <p data-confirm-message></p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-ghost" value="cancel" type="submit">Atšaukti</button>
                <button class="btn btn-danger" value="confirm" type="submit" data-confirm-button><i data-lucide="trash"></i> Ištrinti</button>
            </div>
        </form>
    </dialog>

    <div class="toast-container" aria-live="polite" aria-atomic="false"></div>
</body>
</html>
