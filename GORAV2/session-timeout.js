(function () {
    if (window.goraSessionTimeoutStarted) return;
    window.goraSessionTimeoutStarted = true;

    const idleLimit = 600000;
    const heartbeatInterval = 60000;
    let authenticated = false;
    let idleTimer;
    let expiryCheckInterval;
    let lastHeartbeat = 0;
    let lastActivityAt = 0;
    let redirecting = false;
    let navigatingAway = false;

    function isSameOriginNavigation(url) {
        try {
            return new URL(url, window.location.href).origin === window.location.origin;
        } catch {
            return false;
        }
    }

    document.addEventListener('click', event => {
        if (event.defaultPrevented || !(event.target instanceof Element)) return;

        const link = event.target.closest('a[href]');
        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (link.hasAttribute('download') || (link.target && link.target !== '_self')) return;

        const destination = new URL(link.href, window.location.href);
        if (isSameOriginNavigation(destination) &&
            (destination.pathname !== window.location.pathname || destination.search !== window.location.search)) {
            navigatingAway = true;
        }
    });

    document.addEventListener('submit', event => {
        if (event.defaultPrevented || !(event.target instanceof HTMLFormElement)) return;

        const form = event.target;
        if (form.target && form.target !== '_self') return;
        if (isSameOriginNavigation(form.action || window.location.href)) navigatingAway = true;
    });

    window.addEventListener('pagehide', event => {
        if (navigatingAway || event.persisted) return;

        const body = new URLSearchParams({ release: '1' });
        if (!navigator.sendBeacon || !navigator.sendBeacon('sessionrelease.php', body)) {
            fetch('sessionrelease.php', {
                method: 'POST',
                body,
                credentials: 'same-origin',
                keepalive: true
            }).catch(() => {});
        }
    });

    function expireSession() {
        if (redirecting) return;
        redirecting = true;
        window.clearTimeout(idleTimer);
        window.clearInterval(expiryCheckInterval);
        window.location.replace('sessionactivity.php?timeout=1');
    }

    function hasSessionExpired() {
        return authenticated && Date.now() - lastActivityAt >= idleLimit;
    }

    function armIdleTimer() {
        window.clearTimeout(idleTimer);
        idleTimer = window.setTimeout(() => {
            if (hasSessionExpired()) {
                expireSession();
                return;
            }

            armIdleTimer();
        }, Math.max(0, idleLimit - (Date.now() - lastActivityAt)));
    }

    function sendHeartbeat() {
        lastHeartbeat = Date.now();
        fetch('sessionactivity.php', {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store'
        }).then(async response => {
            if (response.redirected) {
                window.location.replace(response.url);
                return;
            }

            if (!response.ok) return;

            const result = await response.json();
            authenticated = result.active === true;
            if (authenticated) armIdleTimer();
        }).catch(() => {});
    }

    function registerActivity(event) {
        if (!authenticated || !event.isTrusted) return;
        if (hasSessionExpired()) {
            expireSession();
            return;
        }

        lastActivityAt = Date.now();
        armIdleTimer();
        if (Date.now() - lastHeartbeat >= heartbeatInterval) sendHeartbeat();
    }

    function checkSessionExpiry() {
        if (hasSessionExpired()) expireSession();
    }

    fetch('sessionactivity.php', {
        credentials: 'same-origin',
        cache: 'no-store'
    }).then(async response => {
        if (response.redirected) {
            window.location.replace(response.url);
            return;
        }

        if (!response.ok) return;

        const result = await response.json();
        authenticated = result.active === true;
        if (!authenticated) return;

        lastActivityAt = Date.now();
        lastHeartbeat = Date.now();
        armIdleTimer();
        expiryCheckInterval = window.setInterval(checkSessionExpiry, 1000);
        ['click', 'input', 'keydown', 'pointerdown', 'scroll', 'touchstart'].forEach(eventName => {
            document.addEventListener(eventName, registerActivity, { passive: true });
        });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) checkSessionExpiry();
        });
        window.addEventListener('focus', checkSessionExpiry);
        window.addEventListener('pageshow', checkSessionExpiry);
    }).catch(() => {});
})();