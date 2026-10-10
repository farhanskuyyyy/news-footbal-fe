export function registerCms(Alpine) {
    Alpine.data('cmsMonitor', (running, sync, unavailable) => ({
        running, sync, unavailable,
        timer: null,
        controller: null,
        init() { this.timer = setTimeout(() => this.poll(), 5000); },
        destroy() { clearTimeout(this.timer); this.controller?.abort(); },
        fmt(value) {
            if (!value) return document.body.dataset.cmsUnknown;
            const date = new Date(value);
            return Number.isNaN(date.getTime()) ? document.body.dataset.cmsUnknown : new Intl.DateTimeFormat(document.documentElement.lang, {
                timeZone: 'Asia/Jakarta', dateStyle: 'medium', timeStyle: 'short',
            }).format(date) + ' WIB';
        },
        async poll() {
            try {
                if (document.hidden) return;
                this.controller = new AbortController();
                const response = await fetch(document.body.dataset.cmsStatus, { headers: { Accept: 'application/json' }, signal: this.controller.signal });
                const data = await response.json();
                if (!response.ok || data.unavailable) throw new Error('status unavailable');
                this.running = data.running || [];
                this.sync = data.sync || [];
                this.unavailable = false;
            } catch (error) {
                if (error.name !== 'AbortError') this.unavailable = true;
            } finally {
                if (!this.controller?.signal.aborted) this.timer = setTimeout(() => this.poll(), 5000);
            }
        },
    }));

    Alpine.data('cmsFootballSeasons', (endpoint) => ({
        leagueId: '', seasonId: '', seasons: [], loading: false, failed: false, controller: null,
        destroy() { this.controller?.abort(); },
        async loadSeasons() {
            this.controller?.abort();
            const controller = new AbortController();
            this.controller = controller;
            this.seasonId = '';
            this.seasons = [];
            this.failed = false;
            this.loading = false;
            if (!this.leagueId) return;
            this.loading = true;
            try {
                const response = await fetch(`${endpoint}/${encodeURIComponent(this.leagueId)}/seasons`, { headers: { Accept: 'application/json' }, signal: controller.signal });
                if (!response.ok) throw new Error(response.status);
                const data = await response.json();
                if (!controller.signal.aborted) this.seasons = data.data || [];
            } catch (error) {
                if (error.name !== 'AbortError') this.failed = true;
            } finally {
                if (this.controller === controller) this.loading = false;
            }
        },
    }));
}

export function enableCmsNavigation() {
    document.querySelectorAll('.cms-drawer, .cms-confirm-dialog').forEach(modal => {
        modal.addEventListener('keydown', event => {
            if (event.key !== 'Tab') return;
            const controls = [...modal.querySelectorAll('a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex="0"]')].filter(element => element.checkVisibility());
            const first = controls[0];
            const last = controls.at(-1);
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault(); last?.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault(); first?.focus();
            }
        });
    });
    const dialog = document.querySelector('#cms-navigation');
    const opener = document.querySelector('[data-cms-open]');
    if (dialog && opener) {
        opener.hidden = false;
        opener.addEventListener('click', () => { dialog.showModal(); opener.setAttribute('aria-expanded', 'true'); });
        dialog.addEventListener('close', () => { opener.setAttribute('aria-expanded', 'false'); opener.focus(); });
        dialog.addEventListener('click', event => { if (event.target === dialog) {
            const bounds = dialog.getBoundingClientRect();
            if (event.clientX > bounds.right || event.clientX < bounds.left || event.clientY > bounds.bottom || event.clientY < bounds.top) dialog.close();
        } });
        window.matchMedia('(min-width: 1024px)').addEventListener('change', event => { if (event.matches && dialog.open) dialog.close(); });
    }
    const deleteOpener = document.querySelector('[data-cms-delete-open]');
    const deleteDialog = document.querySelector('#delete-user-dialog');
    if (deleteOpener && deleteDialog) {
        deleteOpener.hidden = false;
        deleteOpener.addEventListener('click', () => deleteDialog.showModal());
        deleteDialog.addEventListener('close', () => deleteOpener.focus());
    }
}
