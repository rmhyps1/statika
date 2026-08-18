document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('mobileMenuBtn');
    const nav = document.getElementById('mainNav');

    if (btn && nav) {
        btn.addEventListener('click', () => {
            const isExpanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', !isExpanded);
            nav.classList.toggle('is-expanded');
            btn.setAttribute('aria-label', !isExpanded ? 'Tutup menu navigasi' : 'Buka menu navigasi');
        });

        document.addEventListener('click', (event) => {
            if (!nav.contains(event.target) && !btn.contains(event.target) && nav.classList.contains('is-expanded')) {
                btn.setAttribute('aria-expanded', 'false');
                nav.classList.remove('is-expanded');
                btn.setAttribute('aria-label', 'Buka menu navigasi');
            }
        });
    }

    const exportModalElement = document.getElementById('exportModal');
    const loadingModalElement = document.getElementById('exportLoadingModal');
    const loadingText = document.getElementById('exportLoadingText');

    function showExportLoading(message) {
        if (!loadingModalElement || !window.bootstrap) return;

        if (loadingText) {
            loadingText.textContent = message || 'Mohon tunggu…';
        }

        if (exportModalElement) {
            window.bootstrap.Modal.getOrCreateInstance(exportModalElement).hide();
        }

        const loadingModal = window.bootstrap.Modal.getOrCreateInstance(loadingModalElement);
        loadingModal.show();

        setTimeout(() => loadingModal.hide(), 3500);
    }

    document.querySelectorAll('[data-export-loading-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            const trigger = form.querySelector('[data-export-loading]');
            showExportLoading(trigger?.dataset.exportLoading);
        });
    });

    document.querySelectorAll('a[data-export-loading]').forEach((link) => {
        link.addEventListener('click', () => {
            showExportLoading(link.dataset.exportLoading);
        });
    });
});

document.addEventListener('click', function (event) {
    const toggleBtn = event.target.closest('.btn-detail-toggle');
    if (!toggleBtn) return;

    const targetSelector = toggleBtn.getAttribute('aria-controls');
    const target = document.getElementById(targetSelector);

    if (target) {
        const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';

        document.querySelectorAll('.btn-detail-toggle').forEach(btn => {
            btn.setAttribute('aria-expanded', 'false');
            btn.textContent = 'Lihat Detail';
        });

        document.querySelectorAll('.table-child').forEach(row => {
            row.classList.remove('is-visible');
        });

        if (!isExpanded) {
            toggleBtn.setAttribute('aria-expanded', 'true');
            toggleBtn.textContent = 'Tutup Detail';
            target.classList.add('is-visible');
        }
    }
});
