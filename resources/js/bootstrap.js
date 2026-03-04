import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbConfig = {
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'localkey',
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT || 443,
    wssPort: import.meta.env.VITE_REVERB_PORT || 443,
    forceTLS: true,
    enabledTransports: ['wss'],
};

window.Echo = new Echo(reverbConfig);

const badgeEl = () => document.getElementById('notification-badge');
const listEl = () => document.getElementById('notification-list');

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
}

async function markNotificationAsRead(notificationId, triggerEl, { keepalive = false, skipDom = false } = {}) {
    if (!notificationId) return;
    const token = getCsrfToken();
    try {
        const res = await fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                ...(token ? { 'X-CSRF-TOKEN': token } : {}),
                'X-Requested-With': 'XMLHttpRequest',
            },
            keepalive,
        });
        if (!res.ok) throw new Error(`Failed with status ${res.status}`);
        if (!skipDom) {
            if (triggerEl) {
                triggerEl.remove();
            }
            decrementBadge();
            const item = triggerEl?.closest?.('[data-notification-id]');
            if (item) {
                item.classList.remove('bg-indigo-50');
                item.classList.add('bg-white');
            }
        }
        return true;
    } catch (e) {
        console.error('Failed to mark notification as read', e);
        return false;
    }
}

window.markNotificationAsRead = markNotificationAsRead;

function decrementBadge() {
    const el = badgeEl();
    if (!el) return;
    const current = Math.max(Number(el.dataset.count || 0) - 1, 0);
    el.dataset.count = current;
    el.textContent = current;
    if (current === 0) {
        el.classList.add('hidden');
    }
}

window.viewNotification = function (event, id, link) {
    if (event?.preventDefault) event.preventDefault();
    if (id) {
        // Fire-and-forget mark as read; navigation happens immediately.
        markNotificationAsRead(id, event?.target, { keepalive: true });
    }
    if (link) {
        window.location.href = link;
    }
    return false;
};

function incrementBadge() {
    const el = badgeEl();
    if (!el) return;
    const current = Number(el.dataset.count || 0) + 1;
    el.dataset.count = current;
    el.textContent = current;
    el.classList.remove('hidden');
}

function prependNotification({ id, title, message, link }) {
    const container = listEl();
    if (!container) return;

    const wrapper = document.createElement('div');
    wrapper.className = 'px-6 py-5 bg-indigo-50 hover:bg-indigo-50/70 transition';
    if (id) wrapper.setAttribute('data-notification-id', id);
    wrapper.innerHTML = `
        <div class="flex items-start gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-3">
                    <div class="text-sm font-semibold text-gray-900 leading-snug">${title || 'Notification'}</div>
                    <span class="text-[11px] text-gray-400 shrink-0">Just now</span>
                </div>
                <div class="flex items-start justify-between gap-3">
                    <div class="text-xs text-gray-600 mt-1 leading-relaxed">${message || ''}</div>
                    <div class="mt-3 flex items-center gap-4 text-xs font-semibold">
                        ${link ? `<a href="${link}" onclick="return viewNotification(event,'${id || ''}','${link || ''}')" class="text-indigo-600 hover:text-indigo-800">View</a>` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;

    container.prepend(wrapper);
}

function initNotificationChannel() {
    if (window.currentAdminId && window.Echo) {
        window.Echo.private(`App.Models.AdminUser.${window.currentAdminId}`)
            .notification((notification) => {
                incrementBadge();
                prependNotification(notification);
            });
    }

    if (window.currentUserId && window.Echo) {
        window.Echo.private(`App.Models.User.${window.currentUserId}`)
            .notification((notification) => {
                incrementBadge();
                prependNotification(notification);
            });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNotificationChannel);
} else {
    initNotificationChannel();
}
