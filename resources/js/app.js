import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
document.querySelector('[data-theme-toggle]')?.setAttribute('aria-pressed', String(document.documentElement.dataset.theme === 'dark'));

const enableMatchFilters = () => document.querySelectorAll('[data-match-filter-control]').forEach(label => { label.hidden = false; });
enableMatchFilters();
document.addEventListener('pitchticker:matches', enableMatchFilters);

document.addEventListener('error', (event) => {
    if (event.target instanceof HTMLImageElement) {
        event.target.dataset.imageFailed = 'true';
        event.target.hidden = true;
    }
}, true);
document.addEventListener('load', (event) => {
    if (event.target instanceof HTMLImageElement && event.target.dataset.imageFailed) {
        event.target.hidden = false;
        delete event.target.dataset.imageFailed;
    }
}, true);
document.querySelectorAll('img').forEach(img => {
    if (img.complete && img.currentSrc && !img.naturalWidth) {
        img.dataset.imageFailed = 'true';
        img.hidden = true;
    }
});

document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
    const dark = document.documentElement.dataset.theme !== 'dark';
    document.documentElement.dataset.theme = dark ? 'dark' : 'light';
    document.querySelector('[data-theme-toggle]').setAttribute('aria-pressed', String(dark));
    try { localStorage.setItem('pitchticker-theme', dark ? 'dark' : 'light'); } catch {}
});

document.addEventListener('input', (event) => {
    if (!event.target.matches('[data-match-filter]')) return;
    const root = event.target.closest('[data-match-board]');
    const query = event.target.value.trim().toLocaleLowerCase();
    root.querySelectorAll('[data-match-row]').forEach(row => {
        row.hidden = !row.dataset.matchSearch.toLocaleLowerCase().includes(query);
    });
    root.querySelectorAll('[data-match-group]').forEach(group => {
        group.hidden = !group.querySelector('[data-match-row]:not([hidden])');
        if (query) group.open = true;
    });
    const empty = root.querySelector('[data-filter-empty]');
    if (empty) empty.hidden = !!root.querySelector('[data-match-row]:not([hidden])');
});
