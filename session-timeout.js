(function () {
    if (window.goraSessionTimeoutStarted) return;
    window.goraSessionTimeoutStarted = true;

    const idleLimit = 600000;
    const heartbeatInterval = 60000;
    const script = document.currentScript;
    let sessionGeneration = script ? script.dataset.sessionGeneration : '';
    const accountEmail = script ? (script.dataset.accountEmail || '').trim().toLowerCase() : '';
    let authenticated = false;
    let idleTimer;
    let expiryCheckInterval;
    let activeSessionCheckInterval;
    let lastHeartbeat = 0;
    let lastActivityAt = 0;
    let redirecting = false;

    function expireSession() {
        if (redirecting) return;
        redirecting = true;
        window.clearTimeout(idleTimer);
        window.clearInterval(expiryCheckInterval);
        window.clearInterval(activeSessionCheckInterval);
        window.location.replace('sessionactivity.php?timeout=1');
    }

    function expireReplacedSession() {
        if (redirecting) return;
        redirecting = true;
        window.clearTimeout(idleTimer);
        window.clearInterval(expiryCheckInterval);
        window.clearInterval(activeSessionCheckInterval);
        window.location.replace('videotrackerauth.php?login=session_replaced');
    }

    function expireInvalidSession() {
        if (redirecting) return;
        redirecting = true;
        window.clearTimeout(idleTimer);
        window.clearInterval(expiryCheckInterval);
        window.clearInterval(activeSessionCheckInterval);
        window.location.replace('videotrackerauth.php?login=session_invalid');
    }

    function checkActiveSession() {
        fetch('sessionactivity.php', {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'X-GORA-Session-Check-Only': '1',
                ...(sessionGeneration ? { 'X-GORA-Session-Generation': sessionGeneration } : {})
            }
        }).then(async response => {
            if (response.redirected) {
                window.location.replace(response.url);
                return;
            }
            if (response.status === 409) {
                const result = await response.json();
                if (result.error === 'session_replaced') expireReplacedSession();
                return;
            }
            if (response.status === 401) expireInvalidSession();
        }).catch(() => {});
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
            cache: 'no-store',
            headers: sessionGeneration
                ? { 'X-GORA-Session-Generation': sessionGeneration }
                : {}
        }).then(async response => {
            if (response.redirected) {
                window.location.replace(response.url);
                return;
            }

            if (response.status === 409) {
                const result = await response.json();
                if (result.error === 'session_replaced') expireReplacedSession();
                return;
            }

            if (response.status === 401) {
                expireInvalidSession();
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
        cache: 'no-store',
        headers: sessionGeneration
            ? { 'X-GORA-Session-Generation': sessionGeneration }
            : {}
    }).then(async response => {
        if (response.redirected) {
            window.location.replace(response.url);
            return;
        }

        if (response.status === 409) {
            const result = await response.json();
            if (result.error === 'session_replaced') expireReplacedSession();
            return;
        }

        if (response.status === 401) {
            expireInvalidSession();
            return;
        }

        if (!response.ok) return;

        const result = await response.json();
        authenticated = result.active === true;
        if (!authenticated) return;

        sessionGeneration = result.generation;
        lastActivityAt = Date.now();
        lastHeartbeat = Date.now();
        armIdleTimer();
        expiryCheckInterval = window.setInterval(checkSessionExpiry, 1000);
        activeSessionCheckInterval = window.setInterval(checkActiveSession, 10000);
        checkActiveSession();
        ['click', 'input', 'keydown', 'pointerdown', 'scroll', 'touchstart'].forEach(eventName => {
            document.addEventListener(eventName, registerActivity, { passive: true });
        });
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                checkSessionExpiry();
                sendHeartbeat();
            }
        });
        window.addEventListener('focus', () => {
            checkSessionExpiry();
            sendHeartbeat();
        });
        window.addEventListener('pageshow', () => {
            checkSessionExpiry();
            sendHeartbeat();
        });
    }).catch(() => {});

    window.addEventListener('storage', event => {
        if (event.key !== 'gora-auth-session-changed' || !event.newValue) {
            return;
        }
        try {
            const change = JSON.parse(event.newValue);
            if (typeof change.email === 'string' && change.email.trim().toLowerCase() === accountEmail) {
                expireReplacedSession();
            }
        } catch (error) {
            console.warn('Could not read the active-session change notification.', error);
        }
    });
})();