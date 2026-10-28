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

Alpine.start();