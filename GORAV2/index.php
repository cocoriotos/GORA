<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>GORA— Tu Biblioteca Digital Inteligente</title>
<link rel="icon" href="GORA.ico"/>
<link rel="stylesheet" href="smartshelf.css"/>
</head>
<body>

<!-- NAV -->
<nav>
  <a href="#" class="nav-logo">
    <img src="GORA.ico" alt="GORA"/>
  </a>
  <ul class="nav-links">
    <li><a href="#features" data-i18n="nav_features">Características</a></li>
    <li><a href="#how" data-i18n="nav_how">Cómo Funciona</a></li>
    <li><a href="#pricing" data-i18n="nav_pricing">Precios</a></li>
    <li><a href="#testi" data-i18n="nav_reviews">Testimonios</a></li>
    <li><a href="https://wa.me/573117592209" target="_blank" rel="noopener" data-i18n="nav_support" style="display:inline-flex;align-items:center;gap:6px;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="flex-shrink:0"><path d="M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.445h.006c6.585 0 11.946-5.336 11.949-11.896.001-3.176-1.24-6.165-3.48-8.448z" fill="#25D366"/></svg>
      Soporte
    </a></li>
  </ul>
  <div class="nav-right">
    <div class="lang-sw">
      <button class="lb on" onclick="setLang('es')">ES</button>
      <button class="lb" onclick="setLang('en')">EN</button>
      <button class="lb" onclick="setLang('pt')">PT</button>
    </div>
    <a href="https://solicionespro.com/GORAV2/videotrackerauth.php" target="_blank" rel="noopener" class="btn btn-ghost" data-i18n="nav_login">Ingresar</a>
    <a href="https://solicionespro.com/GORAV2/requestaccessfinal.php" target="_blank" rel="noopener" class="btn btn-primary" data-i18n="nav_cta">Prueba Gratis</a>
  </div>
  <button class="ham" onclick="toggleMenu()" aria-label="Menú">
    <span></span><span></span><span></span>
  </button>
</nav>

<div class="mob-nav" id="mobNav">
  <a href="#features" onclick="toggleMenu()" data-i18n="nav_features">Características</a>
  <a href="#how" onclick="toggleMenu()" data-i18n="nav_how">Cómo Funciona</a>
  <a href="#pricing" onclick="toggleMenu()" data-i18n="nav_pricing">Precios</a>
  <a href="#testi" onclick="toggleMenu()" data-i18n="nav_reviews">Testimonios</a>
  <a href="https://wa.me/573117592209" target="_blank" rel="noopener" onclick="toggleMenu()" style="display:flex;align-items:center;gap:7px;" data-i18n="nav_support">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.445h.006c6.585 0 11.946-5.336 11.949-11.896.001-3.176-1.24-6.165-3.48-8.448z" fill="#25D366"/></svg>
    Soporte
  </a>
  <a href="https://solicionespro.com/GORAV2/videotrackerauth.php" target="_blank" rel="noopener" data-i18n="nav_login">Ingresar</a>
  <a href="https://solicionespro.com/GORAV2/requestaccessfinal.php" target="_blank" rel="noopener" class="btn btn-primary" style="text-align:center;" data-i18n="nav_cta_free">Comenzar Gratis — 30 días</a>
  <div class="mob-lang">
    <button class="lb on" onclick="setLang('es');toggleMenu()">ES</button>
    <button class="lb" onclick="setLang('en');toggleMenu()">EN</button>
    <button class="lb" onclick="setLang('pt');toggleMenu()">PT</button>
  </div>
</div>

<!-- HERO -->
<section id="hero">
  <div class="hero-grid-bg"></div>
  <div class="hero-orb"></div>
  <div class="hero-content">
    <div class="hero-badge fu fu1" data-i18n="hero_badge">✦ Tu biblioteca digital personal | De Pereira para el mundo </div>
    <h1 class="hero-title fu fu2">
      <span data-i18n="hero_title_1">Guarda, Organiza</span><br>
      <em data-i18n="hero_title_2">y Encuentra</em>
      <span class="ac" data-i18n="hero_title_3"> cualquier<br>enlace</span>
      <span data-i18n="hero_title_4"> al instante</span>
    </h1>
    <p class="hero-sub fu fu3" data-i18n="hero_sub">¿Cuántos links perdidos tienes hoy? GORA centraliza todos tus contenidos favoritos de internet en un solo lugar, con búsqueda instantánea y acceso desde cualquier dispositivo.</p>
    <div class="hero-ctas fu fu4">
      <a href="https://solicionespro.com/GORA/requestaccessfinal.php" target="_blank" rel="noopener" class="btn btn-primary btn-lg" data-i18n="hero_cta1">Comenzar Gratis — 30 días ›</a>
      <a href="#video" class="btn-outline-lg" data-i18n="hero_cta2">Ver Demo</a>
    </div>
    <div class="hero-stats fu fu4">
      <div class="stat"><span class="stat-num">30</span><span class="stat-lbl" data-i18n="stat1">días gratis</span></div>
      <div class="stat-divider"></div>
      <div class="stat"><span class="stat-num">∞</span><span class="stat-lbl" data-i18n="stat2">links guardados</span></div>
      <div class="stat-divider"></div>
      <div class="stat"><span class="stat-num">3s</span><span class="stat-lbl" data-i18n="stat3">para encontrar algo</span></div>
    </div>
  </div>
  <div class="mockup-wrap fu fu3">
    <div class="mockup-glow"></div>
    <div class="browser">
      <div class="b-bar">
        <div class="b-dots"><div class="b-dot"></div><div class="b-dot"></div><div class="b-dot"></div></div>
        <div class="b-url">GORA.app/biblioteca</div>
      </div>
      <div class="b-body">
        <div class="a-side">
          <div class="a-logo-sm">GORA</div>
          <div class="a-cat-lbl" data-i18n="mock_cats">Categorías</div>
          <div class="a-cat on"><span class="a-dot"></span><span data-i18n="mock_all">Todo</span></div>
          <div class="a-cat"><span class="a-dot"></span>YouTube</div>
          <div class="a-cat"><span class="a-dot"></span>LinkedIn</div>
          <div class="a-cat"><span class="a-dot"></span>Google Drive</div>
          <div class="a-divider"></div>
          <div class="a-cat"><span class="a-dot"></span><span data-i18n="mock_tech">Tecnología</span></div>
          <div class="a-cat"><span class="a-dot"></span><span data-i18n="mock_design">Diseño</span></div>
          <div class="a-cat"><span class="a-dot"></span><span data-i18n="mock_courses">Cursos</span></div>
        </div>
        <div class="a-main">
          <div class="a-search"><span>⌕</span><span data-i18n="mock_search">Buscar en mis links...</span></div>
          <div class="a-card">
            <div class="a-row"><span class="a-tag a-tag-yt">YouTube</span></div>
            <div class="a-ttl" data-i18n="mock_link1">Tutorial completo de React 2024 — Traversy Media</div>
            <div class="a-url">youtube.com/watch?v=w7ejDZ8...</div>
          </div>
          <div class="a-card">
            <div class="a-row"><span class="a-tag a-tag-li">LinkedIn</span></div>
            <div class="a-ttl" data-i18n="mock_link2">10 habilidades más demandadas en 2025</div>
            <div class="a-url">linkedin.com/pulse/habilidades...</div>
          </div>
          <div class="a-card">
            <div class="a-row"><span class="a-tag a-tag-gd">Drive</span></div>
            <div class="a-ttl" data-i18n="mock_link3">Plantilla de plan de proyecto</div>
            <div class="a-url">docs.google.com/spreadsheets/d/...</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PROBLEM -->
<section id="problem">
  <div class="prob-grid">
    <div>
      <div class="s-tag" data-i18n="prob_tag">El problema</div>
      <h2 class="s-title" data-i18n="prob_title">¿Cuántos links tienes perdidos ahora mismo?</h2>
      <p class="s-sub" data-i18n="prob_sub">La información valiosa está en todas partes: favoritos del navegador, chats de WhatsApp, correos, notas... y cuando la necesitas, no la encuentras.</p>
      <ul class="pain-list">
        <li class="pain-item reveal"><span class="pain-ico">📌</span><span data-i18n="pain1">Links guardados en favoritos que nunca vuelves a encontrar</span></li>
        <li class="pain-item reveal"><span class="pain-ico">💬</span><span data-i18n="pain2">Videos enviados por WhatsApp que se pierden entre miles de mensajes</span></li>
        <li class="pain-item reveal"><span class="pain-ico">🕵️</span><span data-i18n="pain3">Horas buscando un artículo que leíste hace meses</span></li>
        <li class="pain-item reveal"><span class="pain-ico">📂</span><span data-i18n="pain4">Carpetas de Drive, notas, emails y pestañas abiertas sin orden</span></li>
      </ul>
    </div>
    <div class="sol-card reveal">
      <h3 class="sol-title" data-i18n="sol_title">GORA lo resuelve todo</h3>
      <p class="sol-desc" data-i18n="sol_desc">Un solo lugar para guardar cualquier enlace de internet. Categorizado, buscable y accesible desde cualquier dispositivo.</p>
      <div class="sol-feature"><span class="sol-check">✓</span><span data-i18n="sol_f1">Guarda links de YouTube, LinkedIn, Drive, o cualquier URL pública</span></div>
      <div class="sol-feature"><span class="sol-check">✓</span><span data-i18n="sol_f2">Categorías y subcategorías personalizadas por ti</span></div>
      <div class="sol-feature"><span class="sol-check">✓</span><span data-i18n="sol_f3">Buscador inteligente con palabras clave incompletas</span></div>
      <div class="sol-feature"><span class="sol-check">✓</span><span data-i18n="sol_f4">Links privados y públicos, todo en un mismo lugar</span></div>
      <div class="sol-feature"><span class="sol-check">✓</span><span data-i18n="sol_f5">Acceso rápido desde cualquier dispositivo</span></div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section id="features">
  <div class="tc">
    <div class="s-tag" data-i18n="feat_tag">Características</div>
    <h2 class="s-title" data-i18n="feat_title">Todo lo que necesitas para organizar tu conocimiento</h2>
    <p class="s-sub" data-i18n="feat_sub">GORA fue diseñada para que guardar y encontrar contenido sea simple, rápido y sin fricción.</p>
  </div>
  <div class="feat-grid">
    <div class="feat-card reveal"><div class="feat-ico">🗂️</div><div class="feat-title" data-i18n="f1_title">Organización Inteligente</div><div class="feat-desc" data-i18n="f1_desc">Crea categorías y subcategorías completamente personalizadas para clasificar tus contenidos exactamente como tú lo necesitas.</div></div>
    <div class="feat-card reveal"><div class="feat-ico">⚡</div><div class="feat-title" data-i18n="f2_title">Búsqueda Instantánea</div><div class="feat-desc" data-i18n="f2_desc">Encuentra cualquier link con solo escribir palabras clave. Funciona incluso si no recuerdas el título exacto o la plataforma.</div></div>
    <div class="feat-card reveal"><div class="feat-ico">🌐</div><div class="feat-title" data-i18n="f3_title">Multiplataforma</div><div class="feat-desc" data-i18n="f3_desc">Compatible con YouTube, LinkedIn, Google Drive, sitios web, blogs, repositorios y cualquier URL pública o privada.</div></div>
    <div class="feat-card reveal"><div class="feat-ico">🔒</div><div class="feat-title" data-i18n="f4_title">Links Privados</div><div class="feat-desc" data-i18n="f4_desc">Guarda también contenido privado o interno de forma segura. Tu biblioteca es tuya y solo tú decides qué guardar.</div></div>
    <div class="feat-card reveal"><div class="feat-ico">📱</div><div class="feat-title" data-i18n="f5_title">Acceso Desde Cualquier Lugar</div><div class="feat-desc" data-i18n="f5_desc">Accede a tus links desde el computador, tablet o celular. Tu biblioteca siempre disponible donde estés.</div></div>
    <div class="feat-card reveal"><div class="feat-ico">🚀</div><div class="feat-title" data-i18n="f6_title">Acceso Directo al Contenido</div><div class="feat-desc" data-i18n="f6_desc">Un clic y llegas directo al contenido. Sin pasos intermedios, sin perder tiempo navegando entre carpetas.</div></div>
  </div>
</section>

<!-- HOW -->
<section id="how">
  <div class="tc">
    <div class="s-tag" data-i18n="how_tag">Cómo Funciona</div>
    <h2 class="s-title" data-i18n="how_title">En 3 pasos simples</h2>
    <p class="s-sub" data-i18n="how_sub">Empezar a organizar tu vida digital es más fácil de lo que crees.</p>
  </div>
  <div class="steps">
    <div class="step reveal"><div class="step-num">1</div><div class="step-title" data-i18n="step1_title">Regístrate Gratis</div><div class="step-desc" data-i18n="step1_desc">Crea tu cuenta en segundos y comienza tu período de prueba gratuito de 30 días. Sin tarjeta de crédito, sin compromisos.</div></div>
    <div class="step reveal"><div class="step-num">2</div><div class="step-title" data-i18n="step2_title">Crea tus Categorías</div><div class="step-desc" data-i18n="step2_desc">Define las categorías que mejor se adapten a tu vida: trabajo, estudio, entretenimiento, recetas... lo que necesites.</div></div>
    <div class="step reveal"><div class="step-num">3</div><div class="step-title" data-i18n="step3_title">Guarda y Encuentra</div><div class="step-desc" data-i18n="step3_desc">Pega tus links, asígnalos a una categoría y úsalos cuando los necesites con el buscador inteligente.</div></div>
  </div>
</section>

<!-- VIDEO -->
<section id="video">
  <div class="tc">
    <div class="s-tag" data-i18n="video_tag">Demo</div>
    <h2 class="s-title" data-i18n="video_title">Míralo en acción</h2>
    <p class="s-sub" data-i18n="video_sub">En menos de 3 minutos verás exactamente cómo GORA puede cambiar la forma en que gestionas tu información.</p>
  </div>

  <!-- Contenedor de Imágenes Lado a Lado -->
  <div class="images-comparison reveal" style="display: flex; gap: 16px; justify-content: center; align-items: center; flex-wrap: wrap; margin-bottom: 24px;">
    <div class="img-box" style="flex: 1; min-width: 280px; max-width: 500px;">
      <img src="GORABefore.png" alt="GORA Antes" style="width: 100%; height: auto; border-radius: 8px; display: block;" />
    </div>
    <div class="img-box" style="flex: 1; min-width: 280px; max-width: 500px;">
      <img src="GORAAfter.png" alt="GORA Después" style="width: 100%; height: auto; border-radius: 8px; display: block;" />
    </div>
  </div>

  <!-- Contenedor de Video -->
  <div class="video-wrap reveal">
    <iframe src="https://www.youtube.com/embed/qEt4bgTXqYc?rel=0&modestbranding=1" title="GORA Demo" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
  </div>

<!-- Reproductor de Audio -->
  <div class="audio-wrap reveal" style="margin-top: 20px; text-align: center;">
    <audio controls style="width: 100%; max-width: 600px;"> 
      <source src="/GORAV2/GoraPodCast.mp3" type="audio/mpeg">
      Tu navegador no soporta el reproductor de audio.
    </audio>
  </div>


  <p class="video-caption" data-i18n="video_caption">¿Tienes preguntas? Escríbenos por <a href="https://wa.me/573117592209" target="_blank" rel="noopener" style="color:var(--accent);text-decoration:none;">WhatsApp</a> y te ayudamos.</p>
</section>

<!-- PRICING -->
<section id="pricing">
  <div class="tc">
    <div class="s-tag" data-i18n="price_tag">Precios</div>
    <h2 class="s-title" data-i18n="price_title">Simple, transparente, accesible</h2>
    <p class="s-sub" data-i18n="price_sub">Comienza gratis por 30 días. Luego elige el plan que más te convenga.</p>
  </div>
  <div class="price-grid">
    <div class="p-card reveal">
      <div class="p-plan" data-i18n="plan1_name">Trimestral</div>
      <div class="p-amount"><span class="p-currency">$</span>20.000</div>
      <div class="p-period" data-i18n="plan1_per">COP · cada 3 meses</div>
      <div class="p-usd" data-i18n="plan1_usd">≈ USD $5 / trimestre</div>
      <ul class="p-feats">
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat1">Links ilimitados</span></li>
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat2">Categorías personalizadas</span></li>
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat3">Buscador inteligente</span></li>
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat4">Acceso desde cualquier dispositivo</span></li>
      </ul>
      <a href="https://solicionespro.com/GORAV2/requestaccessfinal.php" target="_blank" rel="noopener" class="btn btn-ghost" style="width:100%;justify-content:center;" data-i18n="plan_btn">Comenzar ›</a>
    </div>
    <div class="p-card reveal">
      <div class="p-plan" data-i18n="plan2_name">Semestral</div>
      <div class="p-amount"><span class="p-currency">$</span>40.000</div>
      <div class="p-period" data-i18n="plan2_per">COP · cada 6 meses</div>
      <div class="p-usd" data-i18n="plan2_usd">≈ USD $10 / semestre · Ahorra 17%</div>
      <ul class="p-feats">
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat1">Links ilimitados</span></li>
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat2">Categorías personalizadas</span></li>
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat3">Buscador inteligente</span></li>
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat4">Acceso desde cualquier dispositivo</span></li>
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat5">Tutoriales y soporte prioritario</span></li>
      </ul>
      <a href="https://solicionespro.com/GORAV2/requestaccessfinal.php" target="_blank" rel="noopener" class="btn btn-ghost" style="width:100%;justify-content:center;" data-i18n="plan_btn">Comenzar ›</a>
    </div>
    <div class="p-card feat reveal">
      <div class="p-badge" data-i18n="plan3_badge">Más popular</div>
      <div class="p-plan" data-i18n="plan3_name">Anual</div>
      <div class="p-amount"><span class="p-currency">$</span>60.000</div>
      <div class="p-period" data-i18n="plan3_per">COP · por año completo</div>
      <div class="p-usd" data-i18n="plan3_usd">≈ USD $15 / año · El mejor valor</div>
      <ul class="p-feats">
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat1">Links ilimitados</span></li>
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat2">Categorías personalizadas</span></li>
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat3">Buscador inteligente</span></li>
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat4">Acceso desde cualquier dispositivo</span></li>
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat5">Tutoriales y soporte prioritario</span></li>
        <li class="p-feat"><span class="p-chk">✓</span><span data-i18n="pfeat6">Acceso anticipado a nuevas funciones</span></li>
      </ul>
      <a href="https://solicionespro.com/GORAV2/requestaccessfinal.php" target="_blank" rel="noopener" class="btn btn-primary" style="width:100%;justify-content:center;" data-i18n="plan_btn">Comenzar ›</a>
    </div>
  </div>
  <p style="text-align:center;margin-top:28px;font-size:.82rem;color:var(--t3);" data-i18n="price_note">Todos los planes incluyen 30 días de prueba gratuita. Sin tarjeta de crédito.</p>
</section>

<!-- TESTIMONIALS -->
<section id="testi">
  <div class="tc">
    <div class="s-tag" data-i18n="testi_tag">Testimonios</div>
    <h2 class="s-title" data-i18n="testi_title">Lo que dicen quienes ya la usan</h2>
    <p class="s-sub" data-i18n="testi_sub">Personas reales que transformaron cómo gestionan su contenido digital.</p>
  </div>
  <div class="testi-grid">
    <div class="t-card reveal"><div class="t-q">"</div><div class="t-stars">★★★★★</div><p class="t-text" data-i18n="t1_text">Si no quieres enloquecer con tantos links de tu interés guardados por todos lados, esta app te ayuda a organizarlos y encontrarlos cuando los necesites. Su buscador es increíble.</p><div class="t-author"><div class="t-avatar">SO</div><div><div class="t-name">Sandra Ojeda</div><div class="t-handle" data-i18n="t_user">Usuaria GORA</div></div></div></div>
    <div class="t-card reveal"><div class="t-q">"</div><div class="t-stars">★★★★★</div><p class="t-text" data-i18n="t2_text">Permite guardar de forma ordenada y segura los links de acceso de las páginas importantes. Una herramienta que uso todos los días.</p><div class="t-author"><div class="t-avatar">MJ</div><div><div class="t-name">María José Ospino</div><div class="t-handle" data-i18n="t_user">Usuaria GORA</div></div></div></div>
    <div class="t-card reveal"><div class="t-q">"</div><div class="t-stars">★★★★★</div><p class="t-text" data-i18n="t3_text">Una biblioteca digital personal y fácil de usar. La función de búsqueda es imprescindible para cualquier persona que maneje un volumen considerable de contenidos digitales.</p><div class="t-author"><div class="t-avatar">CZ</div><div><div class="t-name">Carol Zambrano</div><div class="t-handle" data-i18n="t_user">Usuaria GORA</div></div></div></div>
    <div class="t-card reveal"><div class="t-q">"</div><div class="t-stars">★★★★★</div><p class="t-text" data-i18n="t4_text">Muy práctica para organizar tantos enlaces de internet, especialmente para buscarlos de una manera ágil y personalizada. La recomiendo ampliamente.</p><div class="t-author"><div class="t-avatar">OA</div><div><div class="t-name">Oscar Ariza</div><div class="t-handle" data-i18n="t_user2">Usuario GORA</div></div></div></div>
  </div>
</section>

<!-- CTA -->
<section id="cta">
  <div class="trial-badge" data-i18n="cta_badge">✦ Sin tarjeta de crédito · Sin compromisos</div>
  <h2 class="cta-title" data-i18n="cta_title">Deja de perder links.<br>Empieza hoy, gratis.</h2>
  <p class="cta-sub" data-i18n="cta_sub">30 días para explorar todo GORA sin restricciones. Tu conocimiento merece un mejor hogar.</p>
  <div class="cta-btns">
    <a href="https://solicionespro.com/GORAV2/requestaccessfinal.php" target="_blank" rel="noopener" class="btn btn-primary btn-lg" data-i18n="cta_btn1">Crear mi cuenta gratis ›</a>
    <a href="https://wa.me/573117592209" target="_blank" rel="noopener" class="btn-outline-lg">
      <span class="wa-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.445h.006c6.585 0 11.946-5.336 11.949-11.896.001-3.176-1.24-6.165-3.48-8.448zM12.045 21.785h-.005c-1.774 0-3.513-.476-5.031-1.378l-.361-.214-3.741.975.997-3.63-.235-.374a9.859 9.859 0 01-1.517-5.267c.003-5.45 4.46-9.883 9.943-9.883 2.654 0 5.145 1.031 7.021 2.902a9.825 9.825 0 012.908 6.998c-.003 5.452-4.462 9.871-9.98 9.871zm5.465-7.398c-.3-.149-1.773-.873-2.048-.972-.274-.099-.474-.149-.674.149-.198.297-.773.972-.947 1.17-.174.199-.349.223-.647.075-.3-.149-1.266-.465-2.411-1.483-.892-.792-1.492-1.77-1.668-2.069-.174-.298-.019-.459.131-.607.134-.133.298-.348.447-.521.15-.174.199-.298.299-.496.099-.198.05-.372-.025-.521-.075-.149-.674-1.62-.923-2.218-.242-.58-.487-.501-.674-.51l-.573-.01c-.198 0-.522.074-.796.372-.273.297-1.045 1.02-1.045 2.487s1.07 2.884 1.219 3.082c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.627.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.29.174-1.413-.074-.124-.273-.198-.573-.348z" fill="#25D366"/></svg></span>
      <span data-i18n="cta_btn2">Hablar con soporte</span>
    </a>
  </div>
</section>

<!-- FOOTER -->
<footer>
  <div class="f-grid">
    <div>
      <div class="f-logo"><img src="GORAarmonyV2.png" alt="GORAarmony"/></div>
      <div class="f-tag" data-i18n="f_tag">Tu biblioteca digital inteligente. Centraliza, organiza y accede a todos tus contenidos en un solo lugar.</div>
      <div class="socials">
        <a href="https://www.instagram.com/goracoloficial" target="_blank" rel="noopener" class="soc" title="Instagram"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><rect x="2" y="2" width="20" height="20" rx="5" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/></svg></a>
        <a href="https://www.facebook.com/people/Gora-App/pfbid032TPGC76as7biVs9jRwgr48jaQZNpCTKdkmrVioJxmvsv4YdGvU8FMmr31NoXzQ85l/" target="_blank" rel="noopener" class="soc" title="Facebook"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <a href="https://www.youtube.com/@GORAColOficial" target="_blank" rel="noopener" class="soc" title="YouTube"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58z" stroke="currentColor" stroke-width="2"/><polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg></a>
        <a href="https://www.tiktok.com/@goracoloficial" target="_blank" rel="noopener" class="soc" title="TikTok"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M9 12a4 4 0 104 4V4a5 5 0 005 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <a href="https://wa.me/573117592209" target="_blank" rel="noopener" class="soc" title="WhatsApp"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.334.101 11.893c0 2.096.549 4.14 1.595 5.945L0 24l6.335-1.652c1.746.943 3.71 1.444 5.71 1.445h.006c6.585 0 11.946-5.336 11.949-11.896.001-3.176-1.24-6.165-3.48-8.448z" fill="currentColor"/></svg></a>
      </div>
    </div>
    <div>
      <div class="f-col-title" data-i18n="f_product">Producto</div>
      <ul class="f-links">
        <li><a href="#features" data-i18n="nav_features">Características</a></li>
        <li><a href="#how" data-i18n="nav_how">Cómo Funciona</a></li>
        <li><a href="#pricing" data-i18n="nav_pricing">Precios</a></li>
        <li><a href="https://solicionespro.com/GORAV2/requestaccessfinal.php" target="_blank" rel="noopener" data-i18n="f_trial">Prueba Gratis</a></li>
      </ul>
    </div>
    <div>
      <div class="f-col-title" data-i18n="f_support">Soporte</div>
      <ul class="f-links">
        <li><a href="https://www.youtube.com/watch?v=qEt4bgTXqYc" target="_blank" rel="noopener" data-i18n="f_tutorials">Tutoriales</a></li>
        <li><a href="https://wa.me/573117592209" target="_blank" rel="noopener" data-i18n="f_whatsapp">WhatsApp</a></li>
        <li><a href="https://wa.me/573117592209" target="_blank" rel="noopener" data-i18n="f_contact">Contáctenos</a></li>
        <li><a href="https://solicionespro.com/GORAV2/TermsConditions.php" target="_blank" rel="noopener" data-i18n="f_terms">Términos y Condiciones</a></li>
      </ul>
    </div>
    <div>
      <div class="f-col-title" data-i18n="f_follow">Síguenos</div>
      <ul class="f-links">
        <li><a href="https://www.instagram.com/goracoloficial" target="_blank" rel="noopener">Instagram</a></li>
        <li><a href="https://www.facebook.com/people/Gora-App/pfbid032TPGC76as7biVs9jRwgr48jaQZNpCTKdkmrVioJxmvsv4YdGvU8FMmr31NoXzQ85l/" target="_blank" rel="noopener">Facebook</a></li>
        <li><a href="https://www.tiktok.com/@goracoloficial" target="_blank" rel="noopener">TikTok</a></li>
        <li><a href="https://www.youtube.com/@GORAColOficial" target="_blank" rel="noopener">YouTube</a></li>
      </ul>
    </div>
  </div>
  <div class="f-bottom">
    <div class="f-copy">© 2025 GORA. <span data-i18n="f_rights">Todos los derechos reservados.</span></div>
    <div class="f-lang-mini">
      <button class="lb on" onclick="setLang('es')">ES</button>
      <button class="lb" onclick="setLang('en')">EN</button>
      <button class="lb" onclick="setLang('pt')">PT</button>
    </div>
  </div>
</footer>

<script>
const T={es:{nav_features:"Características",nav_how:"Cómo Funciona",nav_pricing:"Precios",nav_reviews:"Testimonios",nav_support:"Soporte",nav_login:"Ingresar",nav_cta:"Prueba Gratis",nav_cta_free:"Comenzar Gratis — 30 días",hero_badge:"✦ Tu biblioteca digital personal",hero_title_1:"Guarda, Organiza",hero_title_2:"y Encuentra",hero_title_3:" cualquier enlace",hero_title_4:" al instante",hero_sub:"¿Cuántos links perdidos tienes hoy? GORA centraliza todos tus contenidos favoritos de internet en un solo lugar, con búsqueda instantánea y acceso desde cualquier dispositivo.",hero_cta1:"Comenzar Gratis — 30 días ›",hero_cta2:"Ver Demo",stat1:"días gratis",stat2:"links guardados",stat3:"para encontrar algo",mock_cats:"Categorías",mock_all:"Todo",mock_tech:"Tecnología",mock_design:"Diseño",mock_courses:"Cursos",mock_search:"Buscar en mis links...",mock_link1:"Tutorial completo de React 2024 — Traversy Media",mock_link2:"10 habilidades más demandadas en 2025",mock_link3:"Plantilla de plan de proyecto",prob_tag:"El problema",prob_title:"¿Cuántos links tienes perdidos ahora mismo?",prob_sub:"La información valiosa está en todas partes: favoritos del navegador, chats de WhatsApp, correos, notas... y cuando la necesitas, no la encuentras.",pain1:"Links guardados en favoritos que nunca vuelves a encontrar",pain2:"Videos enviados por WhatsApp que se pierden entre miles de mensajes",pain3:"Horas buscando un artículo que leíste hace meses",pain4:"Carpetas de Drive, notas, emails y pestañas abiertas sin orden",sol_title:"GORA lo resuelve todo",sol_desc:"Un solo lugar para guardar cualquier enlace de internet. Categorizado, buscable y accesible desde cualquier dispositivo.",sol_f1:"Guarda links de YouTube, LinkedIn, Drive, o cualquier URL pública",sol_f2:"Categorías y subcategorías personalizadas por ti",sol_f3:"Buscador inteligente con palabras clave incompletas",sol_f4:"Links privados y públicos, todo en un mismo lugar",sol_f5:"Acceso rápido desde cualquier dispositivo",feat_tag:"Características",feat_title:"Todo lo que necesitas para organizar tu conocimiento",feat_sub:"GORA fue diseñada para que guardar y encontrar contenido sea simple, rápido y sin fricción.",f1_title:"Organización Inteligente",f1_desc:"Crea categorías y subcategorías completamente personalizadas para clasificar tus contenidos exactamente como tú lo necesitas.",f2_title:"Búsqueda Instantánea",f2_desc:"Encuentra cualquier link con solo escribir palabras clave. Funciona incluso si no recuerdas el título exacto o la plataforma.",f3_title:"Multiplataforma",f3_desc:"Compatible con YouTube, LinkedIn, Google Drive, sitios web, blogs, repositorios y cualquier URL pública o privada.",f4_title:"Links Privados",f4_desc:"Guarda también contenido privado o interno de forma segura. Tu biblioteca es tuya y solo tú decides qué guardar.",f5_title:"Acceso Desde Cualquier Lugar",f5_desc:"Accede a tus links desde el computador, tablet o celular. Tu biblioteca siempre disponible donde estés.",f6_title:"Acceso Directo al Contenido",f6_desc:"Un clic y llegas directo al contenido. Sin pasos intermedios, sin perder tiempo navegando entre carpetas.",how_tag:"Cómo Funciona",how_title:"En 3 pasos simples",how_sub:"Empezar a organizar tu vida digital es más fácil de lo que crees.",step1_title:"Regístrate Gratis",step1_desc:"Crea tu cuenta en segundos y comienza tu período de prueba gratuito de 30 días. Sin tarjeta de crédito, sin compromisos.",step2_title:"Crea tus Categorías",step2_desc:"Define las categorías que mejor se adapten a tu vida: trabajo, estudio, entretenimiento, recetas... lo que necesites.",step3_title:"Guarda y Encuentra",step3_desc:"Pega tus links, asígnalos a una categoría y úsalos cuando los necesites con el buscador inteligente.",video_tag:"Demo",video_title:"Míralo en acción",video_sub:"En menos de 3 minutos verás exactamente cómo GORA puede cambiar la forma en que gestionas tu información.",video_caption:"¿Tienes preguntas? Escríbenos por <a href='https://wa.me/573117592209' target='_blank' rel='noopener' style='color:var(--accent);text-decoration:none;'>WhatsApp</a> y te ayudamos.",price_tag:"Precios",price_title:"Simple, transparente, accesible",price_sub:"Comienza gratis por 30 días. Luego elige el plan que más te convenga.",plan1_name:"Trimestral",plan1_per:"COP · cada 3 meses",plan1_usd:"≈ USD $5 / trimestre",plan2_name:"Semestral",plan2_per:"COP · cada 6 meses",plan2_usd:"≈ USD $10 / semestre · Ahorra 17%",plan3_name:"Anual",plan3_per:"COP · por año completo",plan3_usd:"≈ USD $15 / año · El mejor valor",plan3_badge:"Más popular",pfeat1:"Links ilimitados",pfeat2:"Categorías personalizadas",pfeat3:"Buscador inteligente",pfeat4:"Acceso desde cualquier dispositivo",pfeat5:"Tutoriales y soporte prioritario",pfeat6:"Acceso anticipado a nuevas funciones",plan_btn:"Comenzar ›",price_note:"Todos los planes incluyen 30 días de prueba gratuita. Sin tarjeta de crédito.",testi_tag:"Testimonios",testi_title:"Lo que dicen quienes ya la usan",testi_sub:"Personas reales que transformaron cómo gestionan su contenido digital.",t1_text:"Si no quieres enloquecer con tantos links de tu interés guardados por todos lados, esta app te ayuda a organizarlos y encontrarlos cuando los necesites. Su buscador es increíble.",t2_text:"Permite guardar de forma ordenada y segura los links de acceso de las páginas importantes. Una herramienta que uso todos los días.",t3_text:"Una biblioteca digital personal y fácil de usar. La función de búsqueda es imprescindible para cualquier persona que maneje un volumen considerable de contenidos digitales.",t4_text:"Muy práctica para organizar tantos enlaces de internet, especialmente para buscarlos de una manera ágil y personalizada. La recomiendo ampliamente.",t_user:"Usuaria GORA",t_user2:"Usuario GORA",cta_badge:"✦ Sin tarjeta de crédito · Sin compromisos",cta_title:"Deja de perder links.<br>Empieza hoy, gratis.",cta_sub:"30 días para explorar todo GORA sin restricciones. Tu conocimiento merece un mejor hogar.",cta_btn1:"Crear mi cuenta gratis ›",cta_btn2:"Hablar con soporte",f_tag:"Tu biblioteca digital inteligente. Centraliza, organiza y accede a todos tus contenidos en un solo lugar.",f_product:"Producto",f_support:"Soporte",f_follow:"Síguenos",f_trial:"Prueba Gratis",f_tutorials:"Tutoriales",f_whatsapp:"WhatsApp",f_terms:"Términos y Condiciones",f_rights:"Todos los derechos reservados."},
en:{nav_features:"Features",nav_how:"How It Works",nav_pricing:"Pricing",nav_reviews:"Reviews",nav_support:"Support",nav_login:"Log In",nav_cta:"Free Trial",nav_cta_free:"Start Free — 30 days",hero_badge:"✦ Your personal digital library",hero_title_1:"Save, Organize",hero_title_2:"and Find",hero_title_3:" any link",hero_title_4:" instantly",hero_sub:"How many lost links do you have right now? GORA centralizes all your favorite internet content in one place, with instant search and access from any device.",hero_cta1:"Start Free — 30 days ›",hero_cta2:"Watch Demo",stat1:"days free",stat2:"links saved",stat3:"to find anything",mock_cats:"Categories",mock_all:"All",mock_tech:"Technology",mock_design:"Design",mock_courses:"Courses",mock_search:"Search my links...",mock_link1:"Complete React 2024 Tutorial — Traversy Media",mock_link2:"10 most in-demand skills in 2025",mock_link3:"Project plan template",prob_tag:"The problem",prob_title:"How many links have you lost right now?",prob_sub:"Valuable information is everywhere: browser bookmarks, WhatsApp chats, emails, notes... and when you need it, you can't find it.",pain1:"Bookmarks saved in folders you never find again",pain2:"Videos shared on WhatsApp lost among thousands of messages",pain3:"Hours searching for an article you read months ago",pain4:"Drive folders, notes, emails and open tabs — all a mess",sol_title:"GORA solves it all",sol_desc:"One place to save any internet link. Categorized, searchable and accessible from any device.",sol_f1:"Save links from YouTube, LinkedIn, Drive, or any public URL",sol_f2:"Custom categories and subcategories created by you",sol_f3:"GORA search with partial or incomplete keywords",sol_f4:"Private and public links, all in one place",sol_f5:"Quick access from any device",feat_tag:"Features",feat_title:"Everything you need to organize your knowledge",feat_sub:"GORA was designed to make saving and finding content simple, fast and frictionless.",f1_title:"Smart Organization",f1_desc:"Create fully custom categories and subcategories to classify your content exactly the way you need it.",f2_title:"Instant Search",f2_desc:"Find any link by just typing keywords. Works even if you don't remember the exact title or platform.",f3_title:"Multi-platform",f3_desc:"Compatible with YouTube, LinkedIn, Google Drive, websites, blogs, repos and any public or private URL.",f4_title:"Private Links",f4_desc:"Securely save private or internal content too. Your library is yours — only you decide what to store.",f5_title:"Access From Anywhere",f5_desc:"Access your links from your computer, tablet or phone. Your library always available wherever you are.",f6_title:"Direct Content Access",f6_desc:"One click and you go straight to the content. No extra steps, no time wasted navigating folders.",how_tag:"How It Works",how_title:"In 3 simple steps",how_sub:"Starting to organize your digital life is easier than you think.",step1_title:"Sign Up Free",step1_desc:"Create your account in seconds and start your 30-day free trial. No credit card, no commitments.",step2_title:"Create Your Categories",step2_desc:"Define categories that fit your life: work, study, entertainment, recipes... whatever you need.",step3_title:"Save and Find",step3_desc:"Paste your links, assign them a category, and find them instantly with the smart search.",video_tag:"Demo",video_title:"See it in action",video_sub:"In less than 3 minutes you'll see exactly how GORA can change the way you manage your information.",video_caption:"Got questions? <a href='https://wa.me/573117592209' target='_blank' rel='noopener' style='color:var(--accent);text-decoration:none;'>Chat with us on WhatsApp</a> and we'll help.",price_tag:"Pricing",price_title:"Simple, transparent, affordable",price_sub:"Start free for 30 days. Then choose the plan that works best for you.",plan1_name:"Quarterly",plan1_per:"COP · every 3 months",plan1_usd:"≈ USD $5 / quarter",plan2_name:"Biannual",plan2_per:"COP · every 6 months",plan2_usd:"≈ USD $10 / 6 months · Save 17%",plan3_name:"Annual",plan3_per:"COP · full year",plan3_usd:"≈ USD $15 / year · Best value",plan3_badge:"Most popular",pfeat1:"Unlimited links",pfeat2:"Custom categories",pfeat3:"Smart search",pfeat4:"Access from any device",pfeat5:"Tutorials & priority support",pfeat6:"Early access to new features",plan_btn:"Get started ›",price_note:"All plans include a 30-day free trial. No credit card required.",testi_tag:"Testimonials",testi_title:"What our users say",testi_sub:"Real people who transformed how they manage their digital content.",t1_text:"If you don't want to go crazy with so many links saved everywhere, this app helps you organize them and find them when you need them. Its search is incredible.",t2_text:"It lets you save the links to important pages in an orderly and secure way. A tool I use every day.",t3_text:"A personal digital library that's easy to use. The search function is essential for anyone who handles a considerable volume of digital content.",t4_text:"Very practical for organizing so many internet links, especially for finding them in an agile and personalized way. I highly recommend it.",t_user:"GORA User",t_user2:"GORA User",cta_badge:"✦ No credit card · No commitments",cta_title:"Stop losing links.<br>Start today, free.",cta_sub:"30 days to explore all of GORA without restrictions. Your knowledge deserves a better home.",cta_btn1:"Create my free account ›",cta_btn2:"Chat with support",f_tag:"Your intelligent digital library. Centralize, organize and access all your content in one place.",f_product:"Product",f_support:"Support",f_follow:"Follow Us",f_trial:"Free Trial",f_tutorials:"Tutorials",f_whatsapp:"WhatsApp",f_terms:"Terms & Conditions",f_rights:"All rights reserved."},
pt:{nav_features:"Recursos",nav_how:"Como Funciona",nav_pricing:"Preços",nav_reviews:"Depoimentos",nav_support:"Suporte",nav_login:"Entrar",nav_cta:"Teste Grátis",nav_cta_free:"Começar Grátis — 30 dias",hero_badge:"✦ Sua biblioteca digital pessoal",hero_title_1:"Salve, Organize",hero_title_2:"e Encontre",hero_title_3:" qualquer link",hero_title_4:" instantaneamente",hero_sub:"Quantos links perdidos você tem agora? GORA centraliza todos os seus conteúdos favoritos da internet em um só lugar, com busca instantânea e acesso de qualquer dispositivo.",hero_cta1:"Começar Grátis — 30 dias ›",hero_cta2:"Ver Demo",stat1:"dias grátis",stat2:"links salvos",stat3:"para encontrar algo",mock_cats:"Categorias",mock_all:"Todos",mock_tech:"Tecnologia",mock_design:"Design",mock_courses:"Cursos",mock_search:"Buscar nos meus links...",mock_link1:"Tutorial completo de React 2024",mock_link2:"10 habilidades mais demandadas em 2025",mock_link3:"Modelo de plano de projeto",prob_tag:"O problema",prob_title:"Quantos links você perdeu até agora?",prob_sub:"Informações valiosas estão em todo lugar: favoritos do navegador, chats do WhatsApp, e-mails, notas... e quando você precisa, não encontra.",pain1:"Links salvos em favoritos que você nunca mais encontra",pain2:"Vídeos enviados no WhatsApp perdidos entre milhares de mensagens",pain3:"Horas procurando um artigo que você leu há meses",pain4:"Pastas do Drive, notas, e-mails e abas abertas sem ordem",sol_title:"GORA resolve tudo",sol_desc:"Um único lugar para salvar qualquer link da internet. Categorizado, pesquisável e acessível de qualquer dispositivo.",sol_f1:"Salve links do YouTube, LinkedIn, Drive ou qualquer URL pública",sol_f2:"Categorias e subcategorias personalizadas por você",sol_f3:"Busca inteligente com palavras-chave incompletas",sol_f4:"Links privados e públicos, tudo em um só lugar",sol_f5:"Acesso rápido de qualquer dispositivo",feat_tag:"Recursos",feat_title:"Tudo que você precisa para organizar seu conhecimento",feat_sub:"GORA foi desenvolvido para que salvar e encontrar conteúdo seja simples, rápido e sem fricção.",f1_title:"Organização Inteligente",f1_desc:"Crie categorias e subcategorias totalmente personalizadas para classificar seus conteúdos exatamente como você precisa.",f2_title:"Busca Instantânea",f2_desc:"Encontre qualquer link simplesmente digitando palavras-chave. Funciona mesmo que você não lembre o título exato.",f3_title:"Multiplataforma",f3_desc:"Compatível com YouTube, LinkedIn, Google Drive, sites, blogs, repositórios e qualquer URL pública ou privada.",f4_title:"Links Privados",f4_desc:"Salve também conteúdo privado ou interno com segurança. Sua biblioteca é sua.",f5_title:"Acesso De Qualquer Lugar",f5_desc:"Acesse seus links do computador, tablet ou celular. Sua biblioteca sempre disponível onde você estiver.",f6_title:"Acesso Direto ao Conteúdo",f6_desc:"Um clique e você vai direto ao conteúdo. Sem etapas intermediárias.",how_tag:"Como Funciona",how_title:"Em 3 passos simples",how_sub:"Começar a organizar sua vida digital é mais fácil do que você imagina.",step1_title:"Cadastre-se Grátis",step1_desc:"Crie sua conta em segundos e comece seu período de avaliação gratuita de 30 dias. Sem cartão de crédito.",step2_title:"Crie suas Categorias",step2_desc:"Defina as categorias que melhor se adaptam à sua vida: trabalho, estudo, entretenimento, receitas... o que precisar.",step3_title:"Salve e Encontre",step3_desc:"Cole seus links, atribua uma categoria e use-os quando precisar com a busca inteligente.",video_tag:"Demo",video_title:"Veja em ação",video_sub:"Em menos de 3 minutos você verá exatamente como o GORA pode mudar a forma como você gerencia suas informações.",video_caption:"Tem perguntas? <a href='https://wa.me/573117592209' target='_blank' rel='noopener' style='color:var(--accent);text-decoration:none;'>Fale conosco pelo WhatsApp</a> e te ajudamos.",price_tag:"Preços",price_title:"Simples, transparente, acessível",price_sub:"Comece grátis por 30 dias. Depois escolha o plano que mais te convém.",plan1_name:"Trimestral",plan1_per:"COP · a cada 3 meses",plan1_usd:"≈ USD $5 / trimestre",plan2_name:"Semestral",plan2_per:"COP · a cada 6 meses",plan2_usd:"≈ USD $10 / semestre · Economize 17%",plan3_name:"Anual",plan3_per:"COP · por ano completo",plan3_usd:"≈ USD $15 / ano · Melhor custo-benefício",plan3_badge:"Mais popular",pfeat1:"Links ilimitados",pfeat2:"Categorias personalizadas",pfeat3:"Busca inteligente",pfeat4:"Acesso de qualquer dispositivo",pfeat5:"Tutoriais e suporte prioritário",pfeat6:"Acesso antecipado a novos recursos",plan_btn:"Começar ›",price_note:"Todos os planos incluem 30 dias de avaliação gratuita. Sem cartão de crédito.",testi_tag:"Depoimentos",testi_title:"O que nossos usuários dizem",testi_sub:"Pessoas reais que transformaram como gerenciam seu conteúdo digital.",t1_text:"Se você não quer enlouquecer com tantos links salvos por todo lado, este app ajuda a organizá-los e encontrá-los quando precisar. O buscador é incrível.",t2_text:"Permite salvar os links das páginas importantes de forma organizada e segura. Uma ferramenta que uso todos os dias.",t3_text:"Uma biblioteca digital pessoal e fácil de usar. A função de busca é essencial para qualquer pessoa que lida com grande volume de conteúdo digital.",t4_text:"Muito prático para organizar tantos links da internet, especialmente para encontrá-los de forma ágil e personalizada. Recomendo muito.",t_user:"Usuária GORA",t_user2:"Usuário GORA",cta_badge:"✦ Sem cartão de crédito · Sem compromisso",cta_title:"Pare de perder links.<br>Comece hoje, grátis.",cta_sub:"30 dias para explorar todo o GORA sem restrições. Seu conhecimento merece um lar melhor.",cta_btn1:"Criar minha conta grátis ›",cta_btn2:"Falar com suporte",f_tag:"Sua biblioteca digital inteligente. Centralize, organize e acesse todos os seus conteúdos em um só lugar.",f_product:"Produto",f_support:"Suporte",f_follow:"Siga-nos",f_trial:"Teste Grátis",f_tutorials:"Tutoriais",f_whatsapp:"WhatsApp",f_terms:"Termos e Condições",f_rights:"Todos os direitos reservados."}};

T.es.f_contact="Contáctenos";T.en.f_contact="Contact Us";T.pt.f_contact="Contate-nos";
function setLang(lang){const d=T[lang];if(!d)return;localStorage.setItem('smartshelfLang', lang);document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.getAttribute('data-i18n');if(d[k]!==undefined)el.innerHTML=d[k];});document.querySelectorAll('.lb').forEach(b=>{b.classList.toggle('on',b.textContent.trim()===lang.toUpperCase());});document.documentElement.lang=lang;}
(function(){const initialLang = localStorage.getItem('smartshelfLang') || 'es';setLang(initialLang);})();
function toggleMenu(){document.getElementById('mobNav').classList.toggle('open');}
const io=new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting)e.target.classList.add('visible');});},{threshold:0.1});
document.querySelectorAll('.reveal').forEach(el=>io.observe(el));
</script>
<a href="https://wa.me/573117592209" target="_blank" 
    style="position: fixed; bottom: 20px; right: 20px; background-color: #25D366; color: white; padding: 10px 20px; border-radius: 50px; font-size: 16px; text-decoration: none; display: flex; align-items: center;">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" alt="WhatsApp" width="35" height="35" style="margin-right: 10px;"></a>
    <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
</body>
</html>
