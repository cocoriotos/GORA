(() => {
    const form = document.getElementById('login');
    if (!form) {
        return;
    }

    const messages = {
        es: {
            confirm: 'Este usuario ya tiene una sesión activa. ¿Deseas cerrarla y abrir una nueva sesión desde este dispositivo? Si eliges Cancelar, la sesión actual seguirá activa hasta cerrarse por inactividad.',
            expired: 'La confirmación venció. Intenta ingresar nuevamente.',
            failed: 'No fue posible completar el inicio de sesión. Intenta nuevamente.'
        },
        en: {
            confirm: 'This user already has an active session. Do you want to close it and start a new session on this device? If you choose Cancel, the current session will remain active until it expires due to inactivity.',
            expired: 'The confirmation expired. Please try logging in again.',
            failed: 'Unable to complete sign-in. Please try again.'
        },
        pt: {
            confirm: 'Este usuário já tem uma sessão ativa. Deseja encerrá-la e iniciar uma nova sessão neste dispositivo? Se escolher Cancelar, a sessão atual continuará ativa até expirar por inatividade.',
            expired: 'A confirmação expirou. Tente entrar novamente.',
            failed: 'Não foi possível concluir o login. Tente novamente.'
        }
    };

    const getMessages = () => {
        const language = (localStorage.getItem('smartshelfLang') || document.documentElement.lang || 'es')
            .slice(0, 2)
            .toLowerCase();
        return messages[language] || messages.es;
    };

    const showError = message => {
        const messageBox = document.getElementById('session-timeout-message');
        if (messageBox) {
            messageBox.textContent = message;
            messageBox.hidden = false;
        } else {
            window.alert(message);
        }
    };

    const sendLogin = async (formData, replacementAttempted = false) => {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'X-GORA-Login-Request': '1' }
        });

        if (response.status === 409) {
            const result = await response.json();
            if (result.error !== 'active_session' || typeof result.challenge !== 'string') {
                throw new Error('Unexpected active-session response.');
            }
            if (replacementAttempted) {
                showError(getMessages().failed);
                return;
            }
            if (!window.confirm(getMessages().confirm)) {
                return;
            }

            formData.set('replace_active_session', result.challenge);
            await sendLogin(formData, true);
            return;
        }

        if (response.status === 403) {
            const result = await response.json();
            if (result.error === 'replacement_expired') {
                showError(getMessages().expired);
                return;
            }
        }

        if (!response.ok) {
            throw new Error(`Login request failed with status ${response.status}.`);
        }

        try {
            const email = String(formData.get('email') || '').trim().toLowerCase();
            localStorage.setItem('gora-auth-session-changed', JSON.stringify({
                email,
                nonce: `${Date.now()}-${Math.random()}`
            }));
        } catch (error) {
            console.warn('Could not notify other tabs about the new session.', error);
        }
        window.location.assign(response.url);
    };

    form.addEventListener('submit', event => {
        event.preventDefault();
        if (form.dataset.submitting === 'true') {
            return;
        }

        form.dataset.submitting = 'true';
        const submitButton = form.querySelector('[type="submit"]');
        const messageBox = document.getElementById('session-timeout-message');
        if (submitButton) {
            submitButton.disabled = true;
        }
        if (messageBox) {
            messageBox.hidden = true;
        }

        sendLogin(new FormData(form))
            .catch(error => {
                console.error('Login request failed.', error);
                showError(getMessages().failed);
            })
            .finally(() => {
                delete form.dataset.submitting;
                if (submitButton) {
                    submitButton.disabled = false;
                }
            });
    });
})();
