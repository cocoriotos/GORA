<!-- Developed by Julián González Bucheli -->
<html>
<?php 
    include "sessions.php";
    include "sessionvalidation.php";
    $local_username = $_SESSION['email'];
    $savedcategory = $_SESSION['savedcategory'];
    $duplicatedcategory = $_SESSION['duplicatedcategory'];
    $sessiontimeoutreached = $_SESSION['sessiontimeoutreached'];
    $updatedcategory = $_SESSION['updatedcategory'];
    $deletedcategory = $_SESSION['deletedcategory'];
    include "headercategory.php";
    include "db_connection1.php";
?>
<head>
    <script src="head.js" defer></script>
    <script src="categorytoclipboard.js" defer></script>
    <link rel="icon" href="GORA.ico" type="image/x-icon">
    <!--<link rel="icon" href="SSCircleBackgroundWhite.ico" type="image/x-icon">-->
    <link rel="stylesheet" href="style_sheet_ops.css" />
    <script src="Popper/popper.min.js"></script>
    <script src="plugins/sweetalert/sweetalert.min.js"></script>
    <script src="plugins/alertifyjs/alertify.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/js/alertify.min.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/themes/default.min.css" />
    <style>
        /* Copiado de videolinkadminmodule para homogeneidad */
        body.admin-module-page {
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(180deg, #f7fcfb 0%, #ffffff 100%);
            color: #10283d;
            margin: 0;
            min-height: 100vh;
        }

        body.admin-module-page {
            --welcome-bottom: 48px;
        }

        body.admin-module-page .header-action-group {
            display: none;
        }

        .admin-wrapper {
            max-width: none;
            margin: 0;
            padding: 0 20px 64px 0;
        }

        .module-workspace {
            position: relative;
            display: block;
            min-height: calc(100vh - var(--welcome-bottom));
        }

        .module-sidebar {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 1100;
            width: 68px;
            min-height: calc(100vh - var(--welcome-bottom));
            height: 100%;
            overflow-y: auto;
            padding: 14px;
            background: #032642;
            border-radius: 0;
            box-shadow: 0 16px 32px rgba(3, 38, 66, 0.16);
            transition: width 0.2s ease;
        }

        .module-sidebar:not(.collapsed) {
            width: 240px;
        }

        .module-sidebar.collapsed:hover {
            width: 240px;
        }

        .module-sidebar-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 34px;
            margin-bottom: 12px;
            border: 0;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            cursor: pointer;
        }

        .module-sidebar-toggle i,
        .module-sidebar-link i {
            color: #ffffff !important;
        }

        .module-sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .module-sidebar-link {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 12px;
            border: 0;
            border-radius: 10px;
            background: transparent;
            color: #ffffff;
            text-align: left;
            font-size: 1rem;
            text-decoration: none;
            cursor: pointer;
        }

        .module-sidebar-link i {
            flex: 0 0 24px;
            width: 24px;
            font-size: 1.25rem;
            text-align: center;
        }

        .module-sidebar-link:hover,
        .module-sidebar-link.active {
            background: #25d366;
        }

        .module-sidebar.collapsed {
            width: 68px;
        }

        .module-sidebar.collapsed .module-sidebar-link span {
            display: none;
        }

        .module-sidebar.collapsed:hover .module-sidebar-link span {
            display: inline;
        }

        .module-sidebar.collapsed .module-sidebar-link {
            justify-content: center;
            padding-left: 8px;
            padding-right: 8px;
        }

        .module-sidebar.collapsed:hover .module-sidebar-link {
            justify-content: flex-start;
            padding-left: 12px;
            padding-right: 12px;
        }

        .module-main {
            min-width: 0;
            margin-left: 90px;
            transition: margin-left 0.2s ease;
        }

        .module-workspace.sidebar-open .module-main {
            margin-left: 262px;
        }

        .module-view.is-hidden {
            display: none;
        }

        .admin-wrapper {
            max-width: none;
            margin: 0;
            padding: 0 20px 64px 0;
        }

        .section-card {
            background: rgba(255, 255, 255, 0.96);
            border: 1px solid rgba(3, 38, 66, 0.08);
            box-shadow: 0 20px 45px rgba(3, 38, 66, 0.08);
            border-radius: 28px;
            padding: 32px;
            margin-bottom: 28px;
        }

        .hero-card {
            background: rgba(37, 211, 102, 0.08);
            border: 1px solid rgba(37, 211, 102, 0.18);
            border-radius: 28px;
            padding: 32px;
            margin-bottom: 28px;
        }

        .section-heading {
            color: #0f3650;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.35rem;
            text-align: center;
        }

        .section-subtitle {
            color: #4b6475;
            margin-bottom: 1.8rem;
            font-size: 1rem;
            text-align: center;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .form-group label {
            color: #10283d;
            font-weight: 700;
            font-size: 1rem;
        }

        .form-control {
            border: 1px solid #d7e6ea;
            border-radius: 16px;
            padding: 14px 16px;
            font-size: 0.98rem;
            color: #10283d;
            background: #f9fefa;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            text-align: left;
        }

        .form-control:focus {
            outline: none;
            border-color: #25d366;
            box-shadow: 0 0 0 6px rgba(37, 211, 102, 0.12);
            background: #ffffff;
        }

        .btn-success {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 320px;
            margin: 0 auto;
            background-color: #25d366;
            border: none;
            color: #ffffff;
            padding: 14px 24px;
            border-radius: 999px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .lang-switcher {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 24px;
            flex-wrap: wrap;
        }

        .lang-btn {
            border: 1px solid rgba(3, 38, 66, 0.16);
            background: rgba(255, 255, 255, 0.95);
            color: #10283d;
            padding: 10px 18px;
            border-radius: 999px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease, color 0.2s ease;
            min-width: 64px;
        }

        .lang-btn:hover,
        .lang-btn.active {
            background: #032642;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .category-search-row {
            width: calc(100% + 64px);
            margin-left: -32px;
            margin-right: -32px;
            padding: 20px 32px;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid rgba(3, 38, 66, 0.08);
            display: block;
        }

        .category-search-row > * {
            width: 100%;
        }

        .cards-count {
            text-align: center;
            margin: 20px 0 8px 0;
            font-weight: 700;
            color: #405862;
            font-size: 0.95rem;
        }

        .section-divider {
            height: 1px;
            background: linear-gradient(90deg, rgba(37, 211, 102, 0) 0%, rgba(37, 211, 102, 0.28) 50%, rgba(37, 211, 102, 0) 100%);
            margin: 36px 0;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 8px;
        }

        .content-card {
            border-radius: 24px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 330px;
            border: 1px solid rgba(3, 38, 66, 0.08);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(243, 255, 250, 0.96));
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        @media (max-width: 768px) {
            .admin-wrapper {
                padding: 0 16px 48px 0;
            }

            .module-sidebar {
                min-height: calc(100vh - var(--welcome-bottom));
                height: 100%;
            }

            .module-main {
                margin-left: 0;
            }

            .module-workspace.sidebar-open .module-main {
                margin-left: 172px;
            }
        }

        .content-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 22px 48px rgba(3, 38, 66, 0.09);
        }

        .grid-item-content {
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 18px;
            height: 100%;
        }

        .grid-item-title {
            color: #032642;
            font-size: 1.15rem;
            font-weight: 800;
            line-height: 1.2;
        }

        .grid-item-body p {
            margin: 8px 0;
            color: #405862;
        }

        .grid-item-body .p-title {
            font-weight: 700;
            color: #10283d;
            margin-bottom: 4px;
        }

        .grid-item-action-btn {
            background: rgba(3, 38, 66, 0.06);
            border: none;
            border-radius: 999px;
            width: 46px;
            height: 46px;
            font-size: 28px;
            color: #032642;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .grid-item-action-btn:hover {
            background: rgba(3, 38, 66, 0.12);
        }

        .grid-item-actions {
            position: relative;
        }

        .grid-item-action-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 50px;
            width: 220px;
            background-color: #ffffff;
            border: 1px solid rgba(3, 38, 66, 0.08);
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(3, 38, 66, 0.12);
            z-index: 1000;
        }

        .grid-item-action-menu button {
            display: block;
            width: 100%;
            padding: 12px 14px;
            text-align: left;
            background: none;
            border: none;
            border-bottom: 1px solid rgba(3, 38, 66, 0.06);
            color: #10283d;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .grid-item-action-menu button:last-child {
            border-bottom: none;
        }

        .grid-item-action-menu button:hover {
            background: #f6fbf8;
        }

        .action-button {
            background: rgba(3, 38, 66, 0.06);
            border: none;
            border-radius: 999px;
            padding: 8px 12px;
            color: #032642;
            cursor: pointer;
            font-weight: 700;
        }
    </style>
    <script>
        window.moduleTranslations = {
            es: { add_category_title: 'Adicionar Categoría y Subcategoría', add_category_subtitle: 'Administra tus categorías con el diseño de SmartShelf.' , save: 'Adicionar Categoría', label_category: 'Categoría:', label_subcategory: 'Subcategoría:', form_category: 'Categoría', form_subcategory: 'Subcategoría', maincategory_placeholder: 'Digite la Categoría Principal', subcategory_placeholder: 'Digite la SubCategoría', copy_category: 'Copiar Categoría', copy_subcategory: 'Copiar Subcategoría', edit_category: 'Modificar', delete_category: 'Borrar', sidebar_add: 'Adicionar Enlace', sidebar_addCategory: 'Adicionar Categoría', sidebar_search: 'Buscar Categoría', sidebar_exit: 'Salir' },
            en: { add_category_title: 'Add Category and Subcategory', add_category_subtitle: 'Manage your categories with SmartShelf design.' , save: 'Add Category', label_category: 'Category:', label_subcategory: 'Subcategory:', form_category: 'Category', form_subcategory: 'Subcategory', maincategory_placeholder: 'Enter the Main Category', subcategory_placeholder: 'Enter the Subcategory', copy_category: 'Copy Category', copy_subcategory: 'Copy Subcategory', edit_category: 'Edit', delete_category: 'Delete', sidebar_add: 'Add Link',  sidebar_addCategory: 'Add Category', sidebar_search: 'Search Category', sidebar_exit: 'Logout' },
            pt: { add_category_title: 'Adicionar Categoria e Subcategoria', add_category_subtitle: 'Gerencie suas categorias com o design SmartShelf.' , save: 'Adicionar Categoria', label_category: 'Categoria:', label_subcategory: 'Subcategoria:', form_category: 'Categoria', form_subcategory: 'Subcategoria', maincategory_placeholder: 'Digite a Categoria Principal', subcategory_placeholder: 'Digite a Subcategoria', copy_category: 'Copiar Categoria', copy_subcategory: 'Copiar Subcategoria', edit_category: 'Modificar', delete_category: 'Excluir', sidebar_add: 'Adicionar Link',  sidebar_addCategory: 'Adicionar Categoria', sidebar_search: 'Buscar Categoria', sidebar_exit: 'Sair' }
        };

        window.currentLang = localStorage.getItem('smartshelfLang') || localStorage.getItem('moduleLang') || 'es';

        function t(key) {
            const translations = window.moduleTranslations?.[window.currentLang] || window.moduleTranslations?.es || {};
            return translations[key] || key;
        }

        function updateModuleText() {
            const updateText = (selector, key) => {
                const el = document.querySelector(selector);
                if (el) el.textContent = t(key);
            };

            updateText('#heading-add-category', 'add_category_title');
            updateText('#subtitle-add-category', 'add_category_subtitle');
            updateText('#label-maincategory', 'form_category');
            updateText('#label-category', 'form_subcategory');
            const mainCategoryInput = document.getElementById('maincategory');
            if (mainCategoryInput) mainCategoryInput.placeholder = t('maincategory_placeholder');
            const subcategoryInput = document.getElementById('category');
            if (subcategoryInput) subcategoryInput.placeholder = t('subcategory_placeholder');
            document.querySelectorAll('[data-key="label-category"]').forEach(el => el.textContent = t('label_category'));
            document.querySelectorAll('[data-key="label-subcategory"]').forEach(el => el.textContent = t('label_subcategory'));
            document.querySelectorAll('[data-key="copy-category"]').forEach(el => el.textContent = t('copy_category'));
            document.querySelectorAll('[data-key="copy-subcategory"]').forEach(el => el.textContent = t('copy_subcategory'));
            document.querySelectorAll('[data-key="edit-category"]').forEach(el => el.textContent = t('edit_category'));
            document.querySelectorAll('[data-key="delete-category"]').forEach(el => el.textContent = t('delete_category'));
            document.querySelectorAll('[data-i18n="sidebar_add"]').forEach(el => el.textContent = t('sidebar_add'));
            document.querySelectorAll('[data-i18n="sidebar_search"]').forEach(el => el.textContent = t('sidebar_search'));
            document.querySelectorAll('[data-i18n="sidebar_exit"]').forEach(el => el.textContent = t('sidebar_exit'));
            const saveButton = document.getElementById('save_link');
            if (saveButton) saveButton.value = t('save');
        }

        function setModuleLanguage(lang) {
            window.currentLang = lang;
            localStorage.setItem('moduleLang', lang);
            localStorage.setItem('smartshelfLang', lang);
            document.querySelectorAll('.lang-btn').forEach(button => {
                button.classList.toggle('active', button.dataset.lang === lang);
            });
            updateModuleText();
            window.dispatchEvent(new Event('languageChanged'));
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.lang-btn').forEach(button => {
                button.addEventListener('click', function () {
                    setModuleLanguage(this.dataset.lang);
                });
            });
            setModuleLanguage(window.currentLang);
        });
    </script>
</head>
<body id="bodyadminmodule" class="admin-module-page">
    <div class="admin-wrapper">
        <div class="container-fluid p-0">
            <div class="module-workspace">
                <aside class="module-sidebar collapsed" id="module-sidebar">
                    <button type="button" class="module-sidebar-toggle" aria-label="Menu" onclick="toggleModuleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <nav class="module-sidebar-nav">
                        <a class="module-sidebar-link" href="videolinkadminmodule.php">
                            <i class="fas fa-plus-circle"></i><span data-i18n="sidebar_add">Adicionar Enlace</span>
                        </a>
                        <a class="module-sidebar-link" href="addcategory.php">
                            <i class="fas fa-plus-circle"></i><span data-i18n="sidebar_addCategory">Adicionar Categoría</span>
                        </a>
                        <button type="button" class="module-sidebar-link" data-view="search-category" onclick="showCategoryView('search-category', this)">
                            <i class="fas fa-search"></i><span data-i18n="sidebar_search">Buscar Categoría</span>
                        </button>
                        <a class="module-sidebar-link" href="closetaskscon.php">
                            <i class="fas fa-right-from-bracket"></i><span data-i18n="sidebar_exit">Salir</span>
                        </a>
                    </nav>
                </aside>
                <div class="module-main">
            <div class="row justify-content-start" style="width: 100%;">
                <div class="col-md-12 module-view" data-view-panel="add-category">
                    <div class="section-card hero-card">
                        <form action="savecategory.php" method="POST">
                            <div class="lang-switcher">
                                <button type="button" class="lang-btn" data-lang="es">ES</button>
                                <button type="button" class="lang-btn" data-lang="en">EN</button>
                                <button type="button" class="lang-btn" data-lang="pt">PT</button>
                            </div>
                            <div class="section-heading" id="heading-add-category">Adicionar Categoría y Subcategoría</div>
                            <div class="section-subtitle" id="subtitle-add-category">Administra tus categorías con el diseño de SmartShelf.</div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="maincategory" id="label-maincategory"><strong>Categoría</strong></label>
                                    <input class="form-control" id="maincategory" type="text" name="maincategory" placeholder="Digite la Categoría Principal" required>
                                </div>
                                <div class="form-group">
                                    <label for="category" id="label-category"><strong>Subcategoría</strong></label>
                                    <input class="form-control" id="category" type="text" name="category" placeholder="Digite la SubCategoría" required>
                                </div>
                            </div>

                            <div style="text-align:center; margin-top: 16px;">
                                <input id="save_link" type="submit" class="btn btn-success" name="add filter" value="Adicionar Categoría">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-12 module-view is-hidden" data-view-panel="search-category">
                    <div class="category-search-row">
                        <?php include("search.php") ?>
                    </div>
                    <div class="section-card">
                        <div class="content-grid">
                            <?php 
                                $query1 = "select * from videotips_viodetipscategory where username ='$local_username' order by id, maincategory, category asc";
                                $result_categories = mysqli_query($conn, $query1);
                                while($categories = mysqli_fetch_array($result_categories)) { 
                                    $randomColor = getRandomLightColor();          
                            ?>
                            <div class="content-card grid-item" style="background-color: <?php echo $randomColor; ?>;">
                                <div class="grid-item-content">
                                    <button class="grid-item-action-btn" onclick="toggleActions(event, <?php echo $categories['id']; ?>)">...</button>
                                    <div class="grid-item-actions">
                                        <div class="grid-item-action-menu" id="action-menu-<?php echo $categories['id']; ?>">
                                            <button class="action-button" data-key="copy-category" onclick="copyToClipboard('<?php echo $categories['maincategory']; ?>'); toggleActions(event, <?php echo $categories['id']; ?>);">Copiar Categoría</button>
                                            <button class="action-button" data-key="copy-subcategory" onclick="copyToClipboard('<?php echo $categories['category']; ?>'); toggleActions(event, <?php echo $categories['id']; ?>);">Copiar Subcategoría</button>
                                            <button class="action-button" data-key="edit-category" onclick="window.location.href = 'editcategory.php?id=<?php echo $categories['id']; ?>'">Modificar</button>
                                            <button class="action-button" data-key="delete-category" onclick="confirmDelete(<?php echo $categories['id']; ?>)">Borrar</button>
                                        </div>
                                    </div>
                                    <span class="grid-item-title"><?php echo $categories['content']; ?></span>
                                    <div class="grid-item-body">
                                        <p><span class="p-title" data-key="label-category">Categoría:</span><span class="p-content"><?php echo $categories['maincategory']; ?></span></p>
                                        <p><span class="p-title" data-key="label-subcategory">Subcategoría:</span><span class="p-content"><?php echo $categories['category']; ?></span></p>
                                    </div>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</body>
<script>
    function alignCategorySidebar() {
        const welcomeNav = document.querySelector('nav#welcome');
        if (!welcomeNav) return;

        const welcomeBottom = Math.ceil(welcomeNav.getBoundingClientRect().bottom);
        document.documentElement.style.setProperty('--welcome-bottom', `${welcomeBottom}px`);
    }

    function toggleModuleSidebar() {
        const sidebar = document.getElementById('module-sidebar');
        const workspace = document.querySelector('.module-workspace');
        const isCollapsed = sidebar.classList.toggle('collapsed');
        workspace.classList.toggle('sidebar-open', !isCollapsed);
    }

    function showCategoryView(viewName, selectedButton) {
        document.querySelectorAll('[data-view-panel]').forEach(panel => {
            panel.classList.toggle('is-hidden', panel.dataset.viewPanel !== viewName);
        });

        document.querySelectorAll('.module-sidebar-link[data-view]').forEach(button => {
            button.classList.toggle('active', button === selectedButton);
        });

        if (viewName === 'search-category' && window.searchCards) {
            window.searchCards();
        }
    }

    alignCategorySidebar();
    window.addEventListener('load', alignCategorySidebar);
    window.addEventListener('resize', alignCategorySidebar);

    function toggleActions(event, id) {
        event.stopPropagation(); // Evita que el evento de clic se propague al documento
        var actionMenu = document.getElementById("action-menu-" + id);
        if (actionMenu.style.display === "block") {
            actionMenu.style.display = "none";
        } else {
            // Cerrar todos los menús abiertos antes de abrir uno nuevo
            var allMenus = document.querySelectorAll('.grid-item-action-menu');
            allMenus.forEach(function(menu) {
                menu.style.display = "none";
            });
            actionMenu.style.display = "block";
        }
    }

	// Cerrar el menú al hacer clic fuera de él
    document.addEventListener('click', function(event) {
        var allMenus = document.querySelectorAll('.grid-item-action-menu');
        var isClickInside = false;

        allMenus.forEach(function(menu) {
            // Verificar si el clic fue dentro del menú
            if (menu.contains(event.target)) {
                isClickInside = true;
            }
        });

        if (!isClickInside) {
            allMenus.forEach(function(menu) {
                menu.style.display = "none";
            });
        }
    });

	function confirmDelete(id) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "¡No podrás revertir esta acción!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#032642',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, borrar',
            cancelButtonText: 'Cancelar',
            customClass: {
                popup: 'custom-swal-popup',
                title: 'custom-swal-title',
                content: 'custom-swal-content',
                confirmButton: 'custom-swal-confirm-button',
                cancelButton: 'custom-swal-cancel-button'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Si el usuario confirma, redirigir a delete.php con el ID
                window.location.href = "deletecategory.php?id=" + id;
            } else {
                // Si el usuario cancela, no hacer nada
                Swal.fire({
                    title: 'Cancelado',
                    text: 'El elemento no fue borrado.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    customClass: {
                        popup: 'custom-swal-popup',
                        title: 'custom-swal-title',
                        content: 'custom-swal-content',
                        confirmButton: 'custom-swal-confirm-button'
                    }
                });
            }
        });
    }

</script>
<?php
   if ($savedcategory == 1) {

	echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: 'Subcategoría Adicionada Exitosamente',
        icon: 'success',
        confirmButtonText: 'Aceptar',
        customClass: {
          popup: 'custom-swal-popup',
          title: 'custom-swal-title',
          content: 'custom-swal-content',
          confirmButton: 'custom-swal-confirm-button'
        },
        timer: 1000, // 1000 milisegundos = 1 segundo
        timerProgressBar: true, // Muestra una barra de progreso
        didOpen: () => {
          Swal.showLoading(); // Muestra un indicador de carga
        },
        willClose: () => {
        }
      });
    });
 	 </script>"; 
    $_SESSION['savedcategory'] = 0;
}

if ($savedcategory == 2 ) {

	echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: 'Hubo un problema al adicionar la subcategoría, intente nuevamente',
        icon: 'error',
        confirmButtonText: 'Aceptar',
        customClass: {
          popup: 'custom-swal-popup',
          title: 'custom-swal-title',
          content: 'custom-swal-content',
          confirmButton: 'custom-swal-confirm-button'
        },
        timer: 1500, // 1500 milisegundos = 1.5 segundos
        timerProgressBar: true, // Muestra una barra de progreso
        didOpen: () => {
          Swal.showLoading(); // Muestra un indicador de carga
        },
        willClose: () => {
        }
      });
    });
 	 </script>"; 
	$_SESSION['savedcategory'] = 0;
}

if ($duplicatedcategory == 1) {

	echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: 'Subcategoría duplicada, usar otra',
        icon: 'error',
        confirmButtonText: 'Aceptar',
        customClass: {
          popup: 'custom-swal-popup',
          title: 'custom-swal-title',
          content: 'custom-swal-content',
          confirmButton: 'custom-swal-confirm-button'
        },
        timer: 1500, // 1500 milisegundos = 1.5 segundos
        timerProgressBar: true, // Muestra una barra de progreso
        didOpen: () => {
          Swal.showLoading(); // Muestra un indicador de carga
        },
        willClose: () => {
        }
      });
    });
 	 </script>"; 
    $_SESSION['duplicatedcategory'] = 0;
}

if ($FreeSubcateryReached == 1) {

	echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: 'Ha alcanzado el límite de 5 subcategorías gratis. Para continuar subcategorizando puede usar el botón de Pago por Nequi para adquirir las subcategorías, leer muy bien los términos y condiciones',
        icon: 'warning',
        confirmButtonText: 'Aceptar',
        customClass: {
          popup: 'custom-swal-popup',
          title: 'custom-swal-title',
          content: 'custom-swal-content',
          confirmButton: 'custom-swal-confirm-button'
        },
        timer: 1500, // 1500 milisegundos = 1.5 segundos
        timerProgressBar: true, // Muestra una barra de progreso
        didOpen: () => {
          Swal.showLoading(); // Muestra un indicador de carga
        },
        willClose: () => {
        }
      });
    });
 	 </script>"; 
    $_SESSION['FreeSubcateryReached'] = 0;
}

if ($sessiontimeoutreached  == 1){

	echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        title: 'Mensaje',
        text: 'Detectada que la sesion no tiene actividad por más de 15 minutos, debe iniciar sesión nuevamente',
        icon: 'warning',
        confirmButtonText: 'Aceptar',
        customClass: {
          popup: 'custom-swal-popup',
          title: 'custom-swal-title',
          content: 'custom-swal-content',
          confirmButton: 'custom-swal-confirm-button'
        },
        timer: 1500, // 1500 milisegundos = 1.5 segundos
        timerProgressBar: true, // Muestra una barra de progreso
        didOpen: () => {
          Swal.showLoading(); // Muestra un indicador de carga
        },
        willClose: () => {
        }
      });
    });
 	 </script>";    
    }
	
	if ($updatedcategory == 1) {

		echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
		echo "<script>
		document.addEventListener('DOMContentLoaded', function() {
		Swal.fire({
			title: 'Mensaje',
			text: 'Subcategoría Actualizada Exitosamente',
			icon: 'success',
			confirmButtonText: 'Aceptar',
			customClass: {
			popup: 'custom-swal-popup',
			title: 'custom-swal-title',
			content: 'custom-swal-content',
			confirmButton: 'custom-swal-confirm-button'
			},
			timer: 1500, // 1500 milisegundos = 1.5 segundos
			timerProgressBar: true, // Muestra una barra de progreso
			didOpen: () => {
			Swal.showLoading(); // Muestra un indicador de carga
			},
			willClose: () => {
			}
		});
		});
		</script>"; 
		$_SESSION['updatedcategory'] = 0;
	}
	
	if ($updatedcategory == 2){

		echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
		echo "<script>
		document.addEventListener('DOMContentLoaded', function() {
		Swal.fire({
			title: 'Mensaje',
			text: 'Hubo un problema al actualizar la subcategoría, intente nuevamente',
			icon: 'error',
			confirmButtonText: 'Aceptar',
			customClass: {
			popup: 'custom-swal-popup',
			title: 'custom-swal-title',
			content: 'custom-swal-content',
			confirmButton: 'custom-swal-confirm-button'
			},
			timer: 1500, // 1500 milisegundos = 1.5 segundos
			timerProgressBar: true, // Muestra una barra de progreso
			didOpen: () => {
			Swal.showLoading(); // Muestra un indicador de carga
			},
			willClose: () => {
			}
		});
		});
		</script>";  
	$_SESSION['updatedcategory'] = 0;
	}
	
	
	if ($deletedcategory == 1) {

		echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
		echo "<script>
		document.addEventListener('DOMContentLoaded', function() {
		Swal.fire({
			title: 'Mensaje',
			text: 'Categoría Borrada satisfactoriamente',
			icon: 'Success',
			confirmButtonText: 'Aceptar',
			customClass: {
			popup: 'custom-swal-popup',
			title: 'custom-swal-title',
			content: 'custom-swal-content',
			confirmButton: 'custom-swal-confirm-button'
			},
			timer: 1500, // 1500 milisegundos = 1.5 segundos
			timerProgressBar: true, // Muestra una barra de progreso
			didOpen: () => {
			Swal.showLoading(); // Muestra un indicador de carga
			},
			willClose: () => {
			}
		});
		});
		</script>"; 
		$_SESSION['deletedcategory'] = 0;
	}
	
	if ($deletedcategory == 2){
		
		echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
		echo "<script>
		document.addEventListener('DOMContentLoaded', function() {
		Swal.fire({
			title: 'Mensaje',
			text: 'Hubo un problema al borrar subcategoría, intente nuevamente',
			icon: 'error',
			confirmButtonText: 'Aceptar',
			customClass: {
			popup: 'custom-swal-popup',
			title: 'custom-swal-title',
			content: 'custom-swal-content',
			confirmButton: 'custom-swal-confirm-button'
			},
			timer: 1500, // 1500 milisegundos = 1.5 segundos
			timerProgressBar: true, // Muestra una barra de progreso
			didOpen: () => {
			Swal.showLoading(); // Muestra un indicador de carga
			},
			willClose: () => {
			}
		});
		});
		</script>"; 
	$_SESSION['deletedcategory'] = 0;
	}
?>
<?php 
    function getRandomLightColor() {
        $red = rand(200, 255);
        $green = rand(200, 255);
        $blue = rand(200, 255);
        return sprintf("#%02x%02x%02x", $red, $green, $blue);
    }
?>
</html>