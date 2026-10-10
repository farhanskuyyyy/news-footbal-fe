export function registerHomeStandings(Alpine) {
    Alpine.data('homeStandings', (url) => ({
        ready: false,
        loading: false,
        failed: false,
        card: null,
        controller: null,
        async load(leagueId) {
            this.controller?.abort();
            const controller = new AbortController();
            this.controller = controller;
            this.ready = true;
            this.loading = true;
            this.failed = false;
            this.card = null;
            const timeout = setTimeout(() => controller.abort(), 12000);
            try {
                const endpoint = new URL(url, location.origin);
                endpoint.searchParams.set('league_id', leagueId);
                const response = await fetch(endpoint, { signal: controller.signal, headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Standings unavailable');
                const result = await response.json();
                if (this.controller !== controller) return;
                this.card = result.data;
            } catch {
                if (this.controller === controller) this.failed = true;
            } finally {
                clearTimeout(timeout);
                if (this.controller === controller) this.loading = false;
            }
        },
        destroy() {
            this.controller?.abort();
        },
    }));
}
