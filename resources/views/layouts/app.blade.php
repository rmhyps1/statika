<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f8fafc">
    <title>{{ $title ?? 'DSSD OPD' }} — Kabupaten Malang</title>
    
    <!-- Navigation Progress Bar -->
    <style>
        #nav-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 0;
            height: 3px;
            background: var(--primary, #0f766e);
            z-index: 99999999;
            transition: none;
            pointer-events: none;
        }
        #nav-progress.is-running {
            animation: nav-progress-run 12s cubic-bezier(0.1, 0.4, 0.2, 1) forwards;
        }
        #nav-progress.is-done {
            width: 100% !important;
            animation: none;
            transition: opacity 0.3s ease 0.1s;
            opacity: 0;
        }
        @keyframes nav-progress-run {
            0%   { width: 0; opacity: 1; }
            15%  { width: 35%; }
            40%  { width: 55%; }
            65%  { width: 72%; }
            85%  { width: 86%; }
            100% { width: 92%; opacity: 1; }
        }
        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            #nav-progress.is-running { animation-duration: 0.01ms; }
        }
    </style>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Merriweather:wght@400;700;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="pt-5 mt-4" style="overflow-x: hidden; max-width: 100vw; margin: 0; padding: 0;">
<!-- Navigation Progress Bar -->
<div id="nav-progress"></div>

<a href="#main-content" class="skip-link">Lewati ke konten utama</a>

<header class="app-topbar fixed-top shadow-sm" role="banner" style="width: 100vw; max-width: 100%; right: 0;">
    <div class="app-topbar-inner">
        <a class="app-brand" href="{{ route('landing') }}" aria-label="Beranda DSSD OPD Kabupaten Malang">
            <span class="app-brand-mark" aria-hidden="true">SI</span>
            DSSD Kab. Malang
        </a>
        
        <button class="btn-ghost d-block d-md-none" id="mobileMenuBtn" data-open="false" aria-expanded="false" aria-controls="mainNav" aria-label="Buka menu navigasi" style="padding: 0.5rem; z-index: 999999; position: relative;">
            <svg id="menuIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" style="pointer-events: none;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg id="closeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true" style="pointer-events: none; display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        <nav class="app-nav" id="mainNav" role="navigation" aria-label="Navigasi Utama">
            <a href="{{ route('landing') }}" class="app-nav-link">Beranda</a>
            <a href="{{ route('dssd') }}" class="app-nav-link {{ request()->routeIs('dssd') ? 'active' : '' }}" {{ request()->routeIs('dssd') ? 'aria-current="page"' : '' }}>Data DSSD</a>
            <a href="{{ route('kamasuta') }}" class="app-nav-link {{ request()->routeIs('kamasuta') ? 'active' : '' }}" {{ request()->routeIs('kamasuta') ? 'aria-current="page"' : '' }}>Kamasuta</a>
            <a href="{{ route('laporan.index') }}" class="app-nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}" {{ request()->routeIs('laporan.*') ? 'aria-current="page"' : '' }}>Laporan LPPD</a>
        </nav>
    </div>
</header>

<main id="main-content" class="app-main" tabindex="-1">
    @yield('content')
</main>

<!-- Floating Toast Notification -->
<div aria-live="polite" aria-atomic="true" class="position-relative">
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999; margin-top: 64px;">
        @if(session('success'))
            <div class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="toast align-items-center text-bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Tutup"></button>
                </div>
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Script inisialisasi auto-hide Toast Bootstrap (hide setelah 5 detik) -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var toastElList = [].slice.call(document.querySelectorAll('.toast'))
        var toastList = toastElList.map(function (toastEl) {
            return new bootstrap.Toast(toastEl, { delay: 5000 });
        });
        toastList.forEach(toast => toast.show());

        // Mobile Menu Toggle
        const mobileBtn = document.getElementById('mobileMenuBtn');
        const mainNav = document.getElementById('mainNav');
        const menuIcon = document.getElementById('menuIcon');
        const closeIcon = document.getElementById('closeIcon');
        
        if(mobileBtn && mainNav) {
            mobileBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Use a strict dataset toggle instead of aria-expanded to bypass Bootstrap/cache race conditions
                const isExpanded = mobileBtn.getAttribute('data-open') === 'true';
                
                if (!isExpanded) {
                    mobileBtn.setAttribute('data-open', 'true');
                    mobileBtn.setAttribute('aria-expanded', 'true');
                    mainNav.classList.add('is-expanded');
                    mainNav.style.setProperty('display', 'flex', 'important');
                    if (menuIcon) menuIcon.style.display = 'none';
                    if (closeIcon) closeIcon.style.display = 'block';
                } else {
                    mobileBtn.setAttribute('data-open', 'false');
                    mobileBtn.setAttribute('aria-expanded', 'false');
                    mainNav.classList.remove('is-expanded');
                    mainNav.style.setProperty('display', 'none', 'important');
                    if (menuIcon) menuIcon.style.display = 'block';
                    if (closeIcon) closeIcon.style.display = 'none';
                }
            });
        }
    });
</script>

<script>
(function() {
    var bar = document.getElementById('nav-progress');
    if (!bar) return;

    // --- Progress bar helpers ---
    function startProgress() {
        bar.classList.remove('is-done');
        bar.style.width = '0';
        // Force reflow so animation restarts cleanly
        void bar.offsetWidth;
        bar.classList.add('is-running');
    }

    function finishProgress() {
        bar.classList.remove('is-running');
        bar.classList.add('is-done');
    }

    // --- Trigger on navigation (link clicks) ---
    document.addEventListener('click', function(e) {
        var link = e.target.closest('a[href]');
        if (!link) return;

        var href = link.getAttribute('href');
        // Skip: anchors, new tabs, javascript:, mailto:, external, modals
        if (!href || href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:')) return;
        if (link.target === '_blank' || link.hasAttribute('download')) return;
        if (link.getAttribute('data-bs-toggle')) return;

        startProgress();
    });

    // --- Trigger on form submissions ---
    document.addEventListener('submit', function(e) {
        startProgress();
    });

    // --- Trigger on select[onchange] that submits a form ---
    document.addEventListener('change', function(e) {
        var select = e.target.closest('select[onchange]');
        if (select) {
            startProgress();
        }
    });

    // --- Finish when page fully loads ---
    window.addEventListener('load', function() {
        finishProgress();
    });

    // --- Handle browser back/forward ---
    window.addEventListener('pageshow', function(e) {
        if (e.persisted) {
            finishProgress();
        }
    });

    // --- Restart on beforeunload (covers F5 / Refresh) ---
    window.addEventListener('beforeunload', function() {
        startProgress();
    });
})();
</script>
@stack('scripts')
</body>
</html>
