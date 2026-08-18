<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Informasi Data Statistik — Kabupaten Malang</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Merriweather:wght@400;700;900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="landing">
        <div class="landing-inner">
            <header class="brand">
                <div class="brand-eyebrow">Pemerintah Kabupaten Malang</div>
                <h1>Sistem Informasi Data Statistik Sektoral Daerah</h1>
                <p>Portal resmi pengelolaan, pemantauan, dan inventarisasi data statistik lintas Organisasi Perangkat Daerah.</p>
            </header>

            <nav class="nav-grid" aria-label="Menu Utama">
                <a href="{{ route('dssd') }}" class="nav-card" aria-labelledby="card-title-1" aria-describedby="card-desc-1">
                    <div class="card-header-wrapper">
                        <div class="card-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </div>
                        <span class="card-number" aria-hidden="true">01</span>
                    </div>
                    <div>
                        <h2 id="card-title-1" class="card-title">DSSD OPD</h2>
                        <p id="card-desc-1" class="card-desc">Manajemen Daftar Data Statistik Sektoral Daerah. Fasilitas import batch via CSV/XLSX.</p>
                    </div>
                    <div class="card-action" aria-hidden="true">Buka Portal <span class="arrow">&rarr;</span></div>
                </a>

                <a href="{{ route('kamasuta') }}" class="nav-card" aria-labelledby="card-title-2" aria-describedby="card-desc-2">
                    <div class="card-header-wrapper">
                        <div class="card-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75 16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" />
                            </svg>
                        </div>
                        <span class="card-number" aria-hidden="true">02</span>
                    </div>
                    <div>
                        <h2 id="card-title-2" class="card-title">Kamasuta</h2>
                        <p id="card-desc-2" class="card-desc">Katalog Metadata Statistik Terintegrasi. Sinkronisasi data real-time via API eksternal.</p>
                    </div>
                    <div class="card-action" aria-hidden="true">Buka Katalog <span class="arrow">&rarr;</span></div>
                </a>

                <a href="{{ route('laporan.index') }}" class="nav-card" aria-labelledby="card-title-3" aria-describedby="card-desc-3">
                    <div class="card-header-wrapper">
                        <div class="card-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                            </svg>
                        </div>
                        <span class="card-number" aria-hidden="true">03</span>
                    </div>
                    <div>
                        <h2 id="card-title-3" class="card-title">Laporan LPPD</h2>
                        <p id="card-desc-3" class="card-desc">Kalkulasi persentase ketersediaan data dan pembuatan laporan format PDF resmi.</p>
                    </div>
                    <div class="card-action" aria-hidden="true">Buat Laporan <span class="arrow">&rarr;</span></div>
                </a>
            </nav>

            <footer class="landing-footer">
                <p>&copy; {{ date('Y') }} Sistem Informasi DSSD Kab. Malang</p>
            </footer>
        </div>
    </div>
</body>
</html>
