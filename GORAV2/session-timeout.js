(function () {
    if (window.goraSessionTimeoutStarted) return;
    window.goraSessionTimeoutStarted = true;

    const idleLimit = 180000;
    const heartbeatInterval = 60000;
    let authenticated = false;
    let idleTimer;
    let lastHeartbeat = 0;

    function expireSession() {
        window.location.replace('sessionactivity.php?timeout=1');
    }

    function armIdleTimer() {
        window.clearTimeout(idleTimer);
        idleTimer = window.setTimeout(expireSession, idleLimit);
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

        armIdleTimer();
        if (Date.now() - lastHeartbeat >= heartbeatInterval) sendHeartbeat();
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

        lastHeartbeat = Date.now();
        armIdleTimer();
        ['click', 'input', 'keydown', 'pointerdown', 'pointermove', 'scroll', 'touchstart'].forEach(eventName => {
            document.addEventListener(eventName, registerActivity, { passive: true });
        });
    }).catch(() => {});
})();