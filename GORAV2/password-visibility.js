(() => {
    const translations = {
        es: { showLabel: 'Mostrar contraseña', hideLabel: 'Ocultar contraseña' },
        en: { showLabel: 'Show password', hideLabel: 'Hide password' },
        pt: { showLabel: 'Mostrar senha', hideLabel: 'Ocultar senha' }
    };
    const eyeIcons = {
        open: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>',
        crossed: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a15 15 0 0 1-3.1 3.8M6.2 6.2C3.5 8.1 2 12 2 12s3.6 7 10 7a10.7 10.7 0 0 0 4-.8"/></svg>'
    };

    const getLanguage = () => {
        const selectedLanguage = document.querySelector('.lb.on');
        const language = selectedLanguage
            ? selectedLanguage.textContent.trim().toLowerCase()
            : document.documentElement.lang.toLowerCase();
        return translations[language.slice(0, 2)] || translations.es;
    };

    const updateToggleLabels = () => {
        const text = getLanguage();
        document.querySelectorAll('.password-visibility-toggle').forEach(button => {
            const isVisible = button.getAttribute('aria-pressed') === 'true';
            button.innerHTML = isVisible ? eyeIcons.crossed : eyeIcons.open;
            button.setAttribute('aria-label', isVisible ? text.hideLabel : text.showLabel);
            button.title = isVisible ? text.hideLabel : text.showLabel;
        });
    };

    const addPasswordToggles = () => {
        if (!document.querySelector('input[type="password"]')) {
            return;
        }

        const style = document.createElement('style');
        style.textContent = `
            .password-visibility-field {
                position: relative;
                display: flex;
                flex: 1 1 0%;
                align-items: center;
                width: 100%;
                min-width: 0;
            }
            .password-visibility-field > .password-visibility-input {
                flex: 1 1 auto;
                width: 100%;
                min-width: 0;
                padding-right: 64px !important;
            }
            .password-visibility-toggle {
                position: absolute;
                top: 50%;
                right: 8px;
                z-index: 1;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                width: auto !important;
                min-width: 40px;
                min-height: 32px;
                padding: 4px 8px;
                transform: translateY(-50%);
                border: 0;
                border-radius: 6px;
                background: transparent;
                color: var(--accent, #4b7cf7);
                font-family: inherit;
                font-size: 0.8rem;
                font-weight: 600;
                line-height: 1.2;
                cursor: pointer;
            }
            .password-visibility-toggle svg {
                display: block;
                width: 20px;
                height: 20px;
                fill: none;
                stroke: currentColor;
                stroke-width: 1.8;
                stroke-linecap: round;
                stroke-linejoin: round;
            }
            .password-visibility-toggle:hover,
            .password-visibility-toggle:focus-visible {
                background: rgba(75, 124, 247, 0.14);
            }
            .password-visibility-toggle:focus-visible {
                outline: 2px solid var(--accent, #4b7cf7);
                outline-offset: 2px;
            }
            .reset-form .password-visibility-field {
                margin-bottom: 14px;
            }
            .reset-form .password-visibility-field > .password-visibility-input {
                margin-bottom: 0;
            }
        `;
        document.head.appendChild(style);

        document.querySelectorAll('input[type="password"]').forEach(input => {
            const field = document.createElement('span');
            field.className = 'password-visibility-field';
            input.parentNode.insertBefore(field, input);
            field.appendChild(input);
            input.classList.add('password-visibility-input');

            const toggle = document.createElement('button');
            toggle.type = 'button';
            toggle.className = 'password-visibility-toggle';
            toggle.setAttribute('aria-pressed', 'false');
            toggle.addEventListener('click', () => {
                const isVisible = input.type === 'text';
                input.type = isVisible ? 'password' : 'text';
                toggle.setAttribute('aria-pressed', String(!isVisible));
                updateToggleLabels();
            });
            field.appendChild(toggle);
        });

        updateToggleLabels();
        document.addEventListener('click', event => {
            if (event.target.closest('.lb')) {
                window.setTimeout(updateToggleLabels, 0);
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', addPasswordToggles, { once: true });
    } else {
        addPasswordToggles();
    }
})();
