import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('pdsReview', (initialSubmissions = []) => ({
        search: '',
        activeTab: 'all',
        modalOpen: false,
        selected: null,
        confirmOpen: false,
        confirmAction: null,

        submissions: (initialSubmissions || []).map((s) => {
            const statusKey = (s.status ?? '').toString().trim().toLowerCase();
            return {
                ...s,
                status_key: ['approved', 'pending', 'rejected'].includes(statusKey)
                    ? statusKey
                    : 'pending',
            };
        }),

        normalized(v) {
            return (v ?? '').toString().trim().toLowerCase();
        },

        tabCount(tab) {
            if (tab === 'all') return this.submissions.length;
            return this.submissions.filter((s) => s.status_key === tab).length;
        },

        filtered() {
            const q = this.normalized(this.search);
            const tab = this.normalized(this.activeTab);

            return this.submissions.filter((s) => {
                if (tab !== 'all' && s.status_key !== tab) return false;
                if (!q) return true;

                return (
                    this.normalized(s.name).includes(q) ||
                    this.normalized(s.email).includes(q) ||
                    this.normalized(s.unit).includes(q)
                );
            });
        },

        open(submission) {
            this.selected = submission;
            this.modalOpen = true;
        },

        requestConfirm(newStatus) {
            this.confirmAction = newStatus;
            this.confirmOpen = true;
        },

        confirmStatus() {
            if (!this.confirmAction) return;
            this.setStatus(this.confirmAction);
            this.confirmAction = null;
            this.confirmOpen = false;
        },

        cancelConfirm() {
            this.confirmAction = null;
            this.confirmOpen = false;
        },

        async setStatus(newStatus) {
            if (!this.selected) return;
            const statusKey = this.normalized(newStatus);
            const statusLabel = statusKey === 'approved'
                ? 'Approved'
                : statusKey === 'rejected'
                    ? 'Rejected'
                    : 'Pending';

            try {
                const response = await fetch(`/pds-form/${this.selected.id}/status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ status: statusLabel }),
                });

                if (!response.ok) throw new Error('Failed to update status');

                const updated = { ...this.selected, status_key: statusKey, status: statusLabel };
                this.selected = updated;

                this.submissions = this.submissions.map((s) =>
                    s.id === updated.id
                        ? { ...s, status_key: statusKey, status: statusLabel }
                        : s
                );
            } catch (error) {
                console.error('Error updating status:', error);
                alert('Failed to update status. Please try again.');
            }
        },

        downloadPds() {
            if (!this.selected?.key) return;
            const key = encodeURIComponent(this.selected.key);
            window.location = `/pds-form/${key}/download`;
        },

        close() {
            this.modalOpen = false;
            this.selected = null;
        },
    }));
});

Alpine.start();
