function supportsNotifications() {
    return 'Notification' in window;
}

function permissionLabel(permission) {
    switch (permission) {
        case 'granted':
            return { text: 'Permiso concedido', tone: 'text-emerald-600' };
        case 'denied':
            return { text: 'Permiso bloqueado por el navegador', tone: 'text-red-600' };
        case 'unsupported':
            return { text: 'Este navegador no soporta notificaciones', tone: 'text-red-600' };
        default:
            return { text: 'Permiso pendiente', tone: 'text-amber-600' };
    }
}

async function getServiceWorkerRegistration() {
    if (!('serviceWorker' in navigator)) {
        return null;
    }

    try {
        return await navigator.serviceWorker.ready;
    } catch (e) {
        return null;
    }
}

async function showNotification(title, body) {
    const options = {
        body: body,
        icon: '/icons/icon-192.png',
        badge: '/icons/icon-192.png',
        tag: 'pomodoro-test',
    };

    const registration = await getServiceWorkerRegistration();

    if (registration && typeof registration.showNotification === 'function') {
        try {
            await registration.showNotification(title, options);

            return true;
        } catch (e) {
            // fall back below
        }
    }

    try {
        new Notification(title, options);

        return true;
    } catch (e) {
        return false;
    }
}

function initNotificationSettings() {
    const panel = document.querySelector('[data-role="notifications-panel"]');

    if (!panel) {
        return;
    }

    const status = panel.querySelector('[data-role="notification-status"]');
    const testButton = panel.querySelector('[data-role="test-notification"]');
    const requestButton = panel.querySelector('[data-role="request-notifications"]');
    const toggle = panel.querySelector('[data-role="notifications-toggle"]');

    function render() {
        const permission = supportsNotifications() ? Notification.permission : 'unsupported';
        const label = permissionLabel(permission);

        if (status) {
            status.textContent = label.text;
            status.className = 'text-xs font-medium ' + label.tone;
        }

        if (requestButton) {
            requestButton.classList.toggle('hidden', permission === 'granted' || permission === 'unsupported');
        }

        if (testButton) {
            testButton.classList.toggle('hidden', permission !== 'granted');
        }

        if (toggle && permission === 'denied') {
            toggle.checked = false;
        }
    }

    if (requestButton) {
        requestButton.addEventListener('click', async () => {
            if (!supportsNotifications()) {
                return;
            }

            try {
                const permission = await Notification.requestPermission();
                render();

                if (permission === 'granted') {
                    await showNotification('Notificaciones activadas', 'Avisaremos cuando termine cada bloque.');
                }
            } catch (e) {
                // ignore
            }
        });
    }

    if (testButton) {
        testButton.addEventListener('click', () => {
            showNotification('Notificación de prueba', 'Todo listo: así se verán tus avisos.');
        });
    }

    render();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNotificationSettings);
} else {
    initNotificationSettings();
}
