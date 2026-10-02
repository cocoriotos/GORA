(function () {
    if (window.goraSessionTimeoutStarted) return;
    window.goraSessionTimeoutStarted = true;

    const idleLimit = 180000;
    const heartbeatInterval = 60000;
    let authenticated = false;
    let idleTimer;
    let expiryCheckInterval;
    let lastHeartbeat = 0;
    let lastActivityAt = 0;
    let redirecting = false;

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