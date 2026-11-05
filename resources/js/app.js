import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/*
|--------------------------------------------------------------------------
| Confirmation dialog behaviour  (used by <x-confirm-submit>)
|--------------------------------------------------------------------------
| The component sits INSIDE a <form>. We intercept that form's "submit"
| event: the first time, we cancel it and open a native <dialog> instead.
| When the user clicks the confirm button we submit the form for real.
|
|  - Cancel / Esc / click outside  -> dialog closes, nothing is submitted.
|  - Enter key, submit button, requestSubmit() all go through the same
|    check, so the dialog cannot be bypassed from the keyboard.
|  - After confirming, "busy" blocks any further submit (double-click safe).
|  - Without JavaScript the form simply submits as before; the server-side
|    validation and role checks are unchanged.
*/
Alpine.data('confirmAction', (config = {}) => ({
    busy: false,
    confirmed: false,
    vals: {},        // current form values (strings), available to the dialog text
    labels: {},      // visible text of every <select> in the form
    initial: config.initial || null,   // optional: only confirm if a value changed

    init() {
        this.form = this.$el.closest('form');
        if (!this.form) return;

        this.form.addEventListener('submit', (event) => {
            if (this.busy) {                 // already submitting
                event.preventDefault();
                return;
            }

            if (this.confirmed || !this.needsConfirmation()) {
                this.busy = true;            // go ahead, exactly once
                return;
            }

            event.preventDefault();          // stop, ask first
            this.openDialog();
        });

        // Coming back with the browser Back button must not leave a stuck dialog.
        window.addEventListener('pageshow', () => {
            this.busy = false;
            this.confirmed = false;
            if (this.$refs.dlg && this.$refs.dlg.open) this.$refs.dlg.close();
        });
    },

    snapshot() {
        const data = {};
        new FormData(this.form).forEach((value, key) => {
            if (typeof value === 'string') data[key] = value;   // last value wins
        });
        this.vals = data;

        const labels = {};
        this.form.querySelectorAll('select[name]').forEach((select) => {
            const option = select.selectedOptions[0];
            labels[select.name] = option ? option.text.trim() : '';
        });
        this.labels = labels;
    },

    needsConfirmation() {
        if (!this.initial) return true;      // no "initial" given -> always confirm
        this.snapshot();
        return Object.keys(this.initial).some(
            (key) => (this.vals[key] ?? '') !== String(this.initial[key])
        );
    },

    openDialog() {
        this.snapshot();
        this.$refs.dlg.showModal();
    },

    cancel() {
        this.$refs.dlg.close();
    },

    onClose() {
        this.confirmed = false;              // closed without confirming
    },

    confirm() {
        if (this.busy) return;
        this.confirmed = true;
        this.form.requestSubmit();           // runs validation + our submit listener
    },
}));

/*
| Stock-In form: live total / balance / status.
| This is only a convenience display. The server recalculates everything
| and is the source of truth (see StockLog::calculatePurchase).
*/
Alpine.data('stockInForm', (initial = {}) => ({
    qty: initial.qty ?? '1',
    unitCost: initial.unitCost ?? '',
    amountPaid: initial.amountPaid ?? '0',

    cents(value) {
        const n = parseFloat(value);
        return Number.isFinite(n) && n > 0 ? Math.round(n * 100) : 0;
    },

    get totalCents() {
        const q = parseInt(this.qty, 10);
        return (Number.isFinite(q) && q > 0 ? q : 0) * this.cents(this.unitCost);
    },

    get paidCents() {
        return this.cents(this.amountPaid);
    },

    get balanceCents() {
        return this.totalCents - this.paidCents;
    },

    get overpaid() {
        return this.paidCents > this.totalCents;
    },

    get statusLabel() {
        if (this.totalCents === 0) return '—';
        if (this.paidCents <= 0) return 'Unpaid';
        return this.paidCents >= this.totalCents ? 'Fully Paid' : 'Partially Paid';
    },

    peso(cents) {
        return '₱' + (cents / 100).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    },
}));

/*
| Back button (see components/back-button.blade.php)
| Uses the browser history when the user came from another CitiTrack page.
| Otherwise the link's own href is followed: a safe page in the same section.
| History is NOT used when the previous page was this same page (e.g. after a
| validation error or a filter), a create/edit form (e.g. just after saving),
| a login page, or another website.
*/
window.citiTrackBack = function (event, link) {
    try {
        if (document.referrer) {
            const ref = new URL(document.referrer);
            const here = new URL(window.location.href);

            const sameSite = ref.origin === here.origin;
            const samePage = ref.pathname === here.pathname;
            const fromForm = /\/(create|edit)(\/|$)/.test(ref.pathname);
            const fromAuth = /^\/(login|register|forgot-password|reset-password|confirm-password|verify-email)(\/|$)/.test(ref.pathname);

            if (sameSite && !samePage && !fromForm && !fromAuth && window.history.length > 1) {
                event.preventDefault();
                window.history.back();
                return false;
            }
        }
    } catch (e) {
        // fall through to the normal link
    }

    return true;
};

/*
|--------------------------------------------------------------------------
| Live search ("search as you type")
|--------------------------------------------------------------------------
| Any <input data-live-search> inside a GET form becomes a live search:
|   - typing waits 300 ms, then fetches the same page with the form's current
|     values (search text + filters) and swaps in the new results;
|   - "results" = everything that follows the form in the page, so the page
|     itself is not reloaded and the search box keeps focus;
|   - the page number is dropped, so every new search starts on page 1;
|   - emptying the box shows the full list again;
|   - pressing Enter or a Filter button also updates the results in place;
|   - if anything goes wrong (network, session expired...) it falls back to a
|     normal page load, so the old behaviour is always available.
| The server decides what each user may see; nothing here changes that.
*/
function initLiveSearch(input) {
    const form = input.closest('form');
    if (!form || (form.getAttribute('method') || 'get').toLowerCase() !== 'get') return;

    let timer = null;
    let controller = null;
    let latest = 0;

    const followingElements = () => {
        const list = [];
        for (let node = form.nextElementSibling; node; node = node.nextElementSibling) list.push(node);
        return list;
    };

    const buildUrl = () => {
        const params = new URLSearchParams();
        new FormData(form).forEach((value, key) => {
            if (typeof value === 'string' && value.trim() !== '') params.append(key, value.trim());
        });
        const url = new URL(form.getAttribute('action') || window.location.href, window.location.href);
        url.search = params.toString();     // no "page": a new search starts on page 1
        return url;
    };

    const updateClearButtons = () => {
        form.querySelectorAll('[data-live-clear]').forEach((el) => {
            el.classList.toggle('hidden', input.value.trim() === '');
        });
    };

    const run = async () => {
        const url = buildUrl();

        if (controller) controller.abort();     // forget the previous, slower request
        controller = new AbortController();
        const mine = ++latest;

        const before = followingElements();
        before.forEach((el) => {
            el.classList.add('live-search-busy');
            el.setAttribute('aria-busy', 'true');
        });

        try {
            const response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (!response.ok || new URL(response.url).pathname !== url.pathname) {
                window.location.href = url;     // error or redirect (e.g. session expired)
                return;
            }

            const html = await response.text();
            if (mine !== latest) return;        // a newer search already took over

            const doc = new DOMParser().parseFromString(html, 'text/html');
            const position = [...document.querySelectorAll('input[data-live-search]')].indexOf(input);
            const freshInput = [...doc.querySelectorAll('input[data-live-search]')][position];
            const freshForm = freshInput && freshInput.closest('form');

            if (!freshForm) {
                window.location.href = url;
                return;
            }

            const fresh = [];
            for (let node = freshForm.nextElementSibling; node; node = node.nextElementSibling) {
                fresh.push(document.importNode(node, true));
            }

            before.forEach((el) => el.remove());
            form.after(...fresh);               // Alpine picks up the new elements by itself
            window.history.replaceState(null, '', url);
        } catch (error) {
            if (error.name === 'AbortError') return;
            window.location.href = url;
        }
    };

    input.addEventListener('input', () => {
        updateClearButtons();
        clearTimeout(timer);
        timer = setTimeout(run, 300);           // debounce
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        clearTimeout(timer);
        run();
    });

    form.querySelectorAll('[data-live-clear]').forEach((el) => {
        el.addEventListener('click', (event) => {
            event.preventDefault();
            input.value = '';
            updateClearButtons();
            input.focus();
            clearTimeout(timer);
            run();
        });
    });

    // The Search button is redundant now (it stays for browsers without JavaScript)
    form.querySelectorAll('[data-live-search-button]').forEach((el) => el.classList.add('hidden'));
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('input[data-live-search]').forEach(initLiveSearch);
});

Alpine.start();