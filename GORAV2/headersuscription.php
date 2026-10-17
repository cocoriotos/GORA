<header>
    <nav class="navbar navbar-dark bg-dark d-flex justify-content-center" id="welcome">
        <center>
            <a id="welcome" class="navbar-brand">
                <span class="username-style"><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></span>,
                <span data-subscription-i18n="header_title">Biblioteca de Contenidos Útiles | Suscripción</span>
            </a>
            <div class="subscription-language-switcher" role="group" aria-label="Idioma">
                <button type="button" class="subscription-language-button" data-subscription-lang="es" aria-label="Español" aria-pressed="false">ES</button>
                <button type="button" class="subscription-language-button" data-subscription-lang="en" aria-label="English" aria-pressed="false">EN</button>
                <button type="button" class="subscription-language-button" data-subscription-lang="pt" aria-label="Português" aria-pressed="false">PT</button>
            </div>
        </center>
    </nav>
    <?php include __DIR__ . "/social_links.php"; ?>
</header>
