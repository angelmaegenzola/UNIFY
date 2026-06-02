    /* ============================================================
    UNIFY — Reports  (reports.js)
    Reports are server-rendered — JS handles minor UI only.
    ============================================================ */

    document.addEventListener('DOMContentLoaded', () => {

    /* Animate bar fills on load */
    document.querySelectorAll('.bar-fill').forEach(bar => {
        const target = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => { bar.style.transition = 'width 0.7s ease'; bar.style.width = target; }, 100);
    });

    /* Highlight negative balances */
    document.querySelectorAll('td').forEach(td => {
        if (td.textContent.includes('Deficit')) td.style.color = 'var(--red)';
    });

    /* Print button already wired via onclick in PHP */

    });