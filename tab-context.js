(() => {
    const match = window.location.pathname.match(/\/__tab\/([a-f0-9]{32})\//);
    if (!match) {
        return;
    }

    const tabId = match[1];
    const leaseKey = `gora-tab-owner:${tabId}`;
    const owner = Array.from(crypto.getRandomValues(new Uint8Array(16)))
        .map(value => value.toString(16).padStart(2, '0'))
        .join('');
    let redirected = false;

    const readLease = () => {
        try {
            const lease = JSON.parse(localStorage.getItem(leaseKey) || 'null');
            return lease && typeof lease.owner === 'string' && Number.isFinite(lease.updatedAt)
                ? lease
                : null;
        } catch (error) {
            console.warn('Could not read the tab session lease.', error);
            return null;
        }
    };

    const writeLease = () => {
        try {
            localStorage.setItem(leaseKey, JSON.stringify({ owner, updatedAt: Date.now() }));
        } catch (error) {
            console.warn('Could not update the tab session lease.', error);
        }
    };

    const releaseLease = () => {
        const lease = readLease();
        if (lease && lease.owner === owner) {
            localStorage.removeItem(leaseKey);
        }
    };

    const moveToFreshTab = () => {
        if (redirected) {
            return;
        }
        redirected = true;
        const newTabId = Array.from(crypto.getRandomValues(new Uint8Array(16)))
            .map(value => value.toString(16).padStart(2, '0'))
            .join('');
        const newPath = window.location.pathname.replace(
            `/__tab/${tabId}/`,
            `/__tab/${newTabId}/`
        );
        window.location.replace(`${newPath}${window.location.search}${window.location.hash}`);
    };

    const existingLease = readLease();
    if (existingLease && Date.now() - existingLease.updatedAt < 10000) {
        moveToFreshTab();
        return;
    }

    let heartbeat;
    const startHeartbeat = () => {
        window.clearInterval(heartbeat);
        heartbeat = window.setInterval(() => {
            const lease = readLease();
            if (lease && lease.owner !== owner && Date.now() - lease.updatedAt < 10000) {
                if (owner < lease.owner) {
                    writeLease();
                } else {
                    moveToFreshTab();
                }
                return;
            }
            writeLease();
        }, 3000);
    };

    writeLease();
    startHeartbeat();

    window.addEventListener('storage', event => {
        if (event.key !== leaseKey || !event.newValue || redirected) {
            return;
        }
        const lease = readLease();
        if (!lease || lease.owner === owner) {
            return;
        }
        if (owner < lease.owner) {
            writeLease();
        } else {
            moveToFreshTab();
        }
    });

    window.addEventListener('pagehide', () => {
        window.clearInterval(heartbeat);
        releaseLease();
    });

    window.addEventListener('pageshow', event => {
        if (!event.persisted || redirected) {
            return;
        }
        const lease = readLease();
        if (lease && lease.owner !== owner && Date.now() - lease.updatedAt < 10000) {
            moveToFreshTab();
            return;
        }
        writeLease();
        startHeartbeat();
    });
})();
