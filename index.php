<?php
require_once 'admin/db.php';

// --- TRAITEMENT DU RETOUR DE PAIEMENT RÉUSSI ---
if (isset($_GET['payment']) && $_GET['payment'] === 'success' && isset($_GET['reg_id'])) {
    $reg_id = intval($_GET['reg_id']);
    
    // 1. Vérifier si elle n'est pas déjà marquée comme payée
    $stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ? AND payment_status = 'pending'");
    $stmt->execute([$reg_id]);
    $registration = $stmt->fetch();

    if ($registration) {
        // 2. Mettre à jour le statut en base de données
        $update = $pdo->prepare("UPDATE registrations SET payment_status = 'paid' WHERE id = ?");
        $update->execute([$reg_id]);

        // 3. Récupérer l'email de notification configuré
        $to_stmt = $pdo->prepare("SELECT content_fr FROM site_content WHERE section_key = 'notification_email'");
        $to_stmt->execute();
        $to_email = $to_stmt->fetchColumn() ?: 'info@soccermidable.com';

        // 4. Envoyer l'email de confirmation (Paiement Réussi)
        $subject = "✅ PAIEMENT REÇU - Inscription SoccerMidable : " . $registration['child_name'];
        $body = "Bonne nouvelle ! Un paiement a été validé pour une inscription.\n\n";
        $body .= "DÉTAILS DE L'INSCRIPTION :\n";
        $body .= "-----------------------------------\n";
        $body .= "Parent 1 : " . $registration['parent_name_1'] . "\n";
        $body .= "Parent 2 : " . $registration['parent_name_2'] . "\n";
        $body .= "Enfant : " . $registration['child_name'] . " (Né le: " . $registration['child_dob'] . ")\n";
        $body .= "Email : " . $registration['email'] . "\n";
        $body .= "Téléphone : " . $registration['phone'] . "\n";
        $body .= "Programme : " . $registration['program'] . "\n";
        $body .= "Localisation : " . $registration['location'] . "\n";
        $body .= "-----------------------------------\n";
        $body .= "Statut du paiement : CONFIRMÉ (Stripe)\n";
        $body .= "ID Inscription : " . $reg_id . "\n";
        
        $headers = "From: SoccerMidable <no-reply@soccermidable.ca>\r\n";
        $headers .= "Reply-To: " . $registration['email'] . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        mail($to_email, $subject, $body, $headers);
    }
}

// Fonction helper pour récupérer le texte traduit
function t($key) {
    global $pdo;
    static $content_cache = null;
    if ($content_cache === null) {
        $stmt = $pdo->query("SELECT section_key, content_fr, content_en FROM site_content");
        $content_cache = [];
        while ($row = $stmt->fetch()) {
            $content_cache[$row['section_key']] = $row;
        }
    }
    return $content_cache[$key] ?? ['content_fr' => "[$key]", 'content_en' => "[$key]"];
}
?>
<!DOCTYPE html>
<html lang="fr" data-lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SoccerMidable | Champions de la vie · Champions in Life</title>
<meta name="description" content="Programme de soccer pour enfants de 2 à 14 ans à Ottawa–Gatineau. Leadership, confiance et épanouissement à travers le sport. | Soccer program for children aged 2–14 in Ottawa–Gatineau.">
<meta property="og:title" content="SoccerMidable | Champions de la vie">
<meta property="og:description" content="Plus qu'un programme de soccer — une plateforme de leadership pour les enfants de 2 à 14 ans à Ottawa–Gatineau.">
<meta property="og:type" content="website">
<meta property="og:url" content="https://soccermidable.ca/">
<link rel="canonical" href="https://soccermidable.ca/">
<link rel="icon" href="images/logo-purple.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Nunito:wght@400;600;700;800;900&family=Quicksand:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css?v=1.1.7">
</head>
<body data-lang="fr">

<!-- ══════════════════════════════════════════
     NAVBAR — Bright, Visible, Kid-Friendly
══════════════════════════════════════════ -->
<nav class="navbar" id="navbar">
  <a href="#" class="navbar-logo">
    <img src="images/logo-purple.png" alt="SoccerMidable">
    <span class="logo-text brand-name"><span class="soccer">Soccer</span><span class="midable">Midable</span></span>
  </a>
  <ul class="navbar-links">
    <li><a href="#vision"><span class="fr">Vision</span><span class="en">Vision</span></a></li>
    <li><a href="#about"><span class="fr">À propos</span><span class="en">About</span></a></li>
    <li><a href="#programs"><span class="fr">Programmes</span><span class="en">Programs</span></a></li>
    <li><a href="#gallery"><span class="fr">Photos</span><span class="en">Photos</span></a></li>
    <li><a href="#impact">Impact</a></li>
    <li><a href="#partners"><span class="fr">Partenaires</span><span class="en">Partners</span></a></li>
    <li><a href="#story"><span class="fr">Histoire</span><span class="en">Story</span></a></li>
    <li><a href="#team"><span class="fr">Équipe</span><span class="en">Team</span></a></li>
  </ul>
  <div class="navbar-right">
    <div class="lang-switch">
      <button class="lang-btn active" data-lang="fr" onclick="setLang('fr')">FR</button>
      <button class="lang-btn" data-lang="en" onclick="setLang('en')">EN</button>
    </div>
    <a href="#register" class="btn btn-nav-cta">
      <span class="fr">S'inscrire</span><span class="en">Register</span>
    </a>
    <button class="hamburger" id="hamburger" onclick="toggleMenu()" aria-label="Menu">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

<!-- MOBILE MENU -->
<div class="mobile-menu" id="mobileMenu">
  <a href="#vision" onclick="closeMenu()"><span class="fr">Vision</span><span class="en">Vision</span></a>
  <a href="#about" onclick="closeMenu()"><span class="fr">À propos</span><span class="en">About</span></a>
  <a href="#programs" onclick="closeMenu()"><span class="fr">Programmes</span><span class="en">Programs</span></a>
  <a href="#gallery" onclick="closeMenu()"><span class="fr">Photos</span><span class="en">Photos</span></a>
  <a href="#impact" onclick="closeMenu()">Impact</a>
  <a href="#partners" onclick="closeMenu()"><span class="fr">Partenaires</span><span class="en">Partners</span></a>
  <a href="#story" onclick="closeMenu()"><span class="fr">Notre histoire</span><span class="en">Our Story</span></a>
  <a href="#team" onclick="closeMenu()"><span class="fr">Équipe</span><span class="en">Team</span></a>
  <a href="#register" class="mobile-cta" onclick="closeMenu()">
    ⚽ <span class="fr">S'inscrire maintenant</span><span class="en">Register Now</span>
  </a>
</div>


<!-- ══════════════════════════════════════════
     HERO — Warm, Inviting, Parent-Focused
══════════════════════════════════════════ -->
<section id="hero">
  
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-decor">
      <span class="ball">⚽</span>
      <span class="ball">⚽</span>
      <span class="ball">⚽</span>
      <span class="ball">⚽</span>
    </div>
    <div class="container">
      <div class="hero-content">
        <div class="hero-badge">
          ⚽&nbsp;
          <span class="fr">Ottawa–Gatineau · Fondé en 2018 · 2 à 14 ans</span>
          <span class="en">Ottawa–Gatineau · Founded 2018 · Ages 2–14</span>
        </div>
        <h1 class="hero-title brand-name">
          <?php $h = t('hero_title_soccer'); ?>
          <span class="soccer" style="color:white;"><?= $h['content_fr'] ?></span>
          <?php $h2 = t('hero_title_midable'); ?>
          <span class="line-gold"><?= $h2['content_fr'] ?></span>
        </h1>
        <p class="hero-sub">
          <?php $sub = t('hero_sub'); ?>
          <span class="fr"><?= $sub['content_fr'] ?></span>
          <span class="en"><?= $sub['content_en'] ?></span>
        </p>
        <div class="hero-actions">
          <a href="#register" class="btn btn-gold">
            ⚽&nbsp;<span class="fr">S'inscrire maintenant</span><span class="en">Register Now</span>
          </a>
          <a href="#programs" class="btn btn-outline-white">
            <span class="fr">Nos programmes</span><span class="en">Our Programs</span>&nbsp;→
          </a>
        </div>
        <div class="hero-stats">
          <div><div class="hstat-num">2000+</div><div class="hstat-label"><span class="fr">Enfants impactés</span><span class="en">Children impacted</span></div></div>
          <div><div class="hstat-num">13</div><div class="hstat-label"><span class="fr">Pays d'impact</span><span class="en">Countries of impact</span></div></div>
          <div><div class="hstat-num">3×</div><div class="hstat-label"><span class="fr">Meilleure entreprise Canada</span><span class="en">Best Business Canada</span></div></div>
          <div><div class="hstat-num">7+</div><div class="hstat-label"><span class="fr">Ans d'excellence</span><span class="en">Years of excellence</span></div></div>
        </div>
      </div>
    <div class="kids-photo"><img src="images/kids/kids-training-10.jpeg" alt="Young soccer player" loading="lazy"></div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     VALUES TICKER — Gold Band
══════════════════════════════════════════ -->
<div id="ticker">
  <div class="ticker-track">
    <span class="ticker-item"><span class="ticker-dot"></span>Discipline</span>
    <span class="ticker-item purple-text">Excellence</span>
    <span class="ticker-item"><span class="ticker-dot"></span>Respect</span>
    <span class="ticker-item purple-text">Intégrité · Integrity</span>
    <span class="ticker-item"><span class="ticker-dot"></span>Inclusion</span>
    <span class="ticker-item purple-text"><span class="brand-name"><span class="soccer">Soccer</span>Mindset</span></span>
    <span class="ticker-item"><span class="ticker-dot"></span>Leadership</span>
    <span class="ticker-item purple-text">Confiance · Confidence</span>
    <span class="ticker-item"><span class="ticker-dot"></span>Résilience</span>
    <span class="ticker-item purple-text">Champions de la vie · Champions in Life</span>
    <!-- duplicate for seamless scroll -->
    <span class="ticker-item"><span class="ticker-dot"></span>Discipline</span>
    <span class="ticker-item purple-text">Excellence</span>
    <span class="ticker-item"><span class="ticker-dot"></span>Respect</span>
    <span class="ticker-item purple-text">Intégrité · Integrity</span>
    <span class="ticker-item"><span class="ticker-dot"></span>Inclusion</span>
    <span class="ticker-item purple-text"><span class="brand-name"><span class="soccer">Soccer</span>Mindset</span></span>
    <span class="ticker-item"><span class="ticker-dot"></span>Leadership</span>
    <span class="ticker-item purple-text">Confiance · Confidence</span>
    <span class="ticker-item"><span class="ticker-dot"></span>Résilience</span>
    <span class="ticker-item purple-text">Champions de la vie · Champions in Life</span>
  </div>
</div>


<!-- ══════════════════════════════════════════
     KIDS PHOTO STRIP — Aligned Grid
══════════════════════════════════════════ -->
<div id="kids-strip">
  <div class="kids-grid">
    <div class="kids-photo"><img src="images/kids/kids-toddler.jpg" alt="Young soccer player" loading="lazy"></div>
    <div class="kids-photo"><img src="images/kids/kids-tiny.jpg" alt="Tiny player with ball" loading="lazy"></div>
    <div class="kids-photo"><img src="images/kids/kids-jerseys.jpg" alt="Kids in SoccerMidable jerseys" loading="lazy"></div>
    <div class="kids-photo"><img src="images/kids/kids-coach-1.jpg" alt="Coach with young player" loading="lazy"></div>
    <div class="kids-photo"><img src="images/kids/kids-player.jpg" alt="Player dribbling" loading="lazy"></div>
    <div class="kids-photo"><img src="images/kids/kids-training-1.jpg" alt="Group training session" loading="lazy"></div>
    <div class="kids-photo"><img src="images/kids/kids-training-5.jpeg" alt="Group training session" loading="lazy"></div>
    <div class="kids-photo"><img src="images/kids/kids-training-12.jpeg" alt="Group training session" loading="lazy"></div>
    <div class="kids-photo"><img src="images/kids/kids-training-7.jpeg" alt="Group training session" loading="lazy"></div>
    <div class="kids-photo"><img src="images/kids/kids-training-8.jpeg" alt="Group training session" loading="lazy"></div>
    <div class="kids-photo"><img src="images/kids/kids-training-9.jpeg" alt="Group training session" loading="lazy"></div>
    <div class="kids-photo"><img src="images/kids/kids-training-4.jpeg" alt="Group training session" loading="lazy"></div>
  </div>
</div>


<!-- ══════════════════════════════════════════
     VISION
══════════════════════════════════════════ -->
<section id="vision" class="section">
  <div class="container">
    <div class="vision-grid">
      <div class="reveal">
        <div class="vision-card">
          <blockquote class="fr">« Le jeu est un langage universel. Il rassemble, il inspire et il transforme. »</blockquote>
          <blockquote class="en">"Sport is a universal language. It brings people together, inspires, and transforms."</blockquote>
          <cite>— <span class="brand-name" style="color:var(--gold-light);font-size:0.95rem;"><span class="soccer" style="color:white;">Soccer</span><span class="midable">Midable</span></span></cite>
        </div>
        <div class="vision-float">
          <div class="vf-num">2000+</div>
          <div class="vf-label"><span class="fr">Vies transformées</span><span class="en">Lives transformed</span></div>
        </div>
      </div>
      <div class="reveal d2">
        <div class="section-tag"><span class="fr">Notre vision</span><span class="en">Our Vision</span></div>
        <?php $v = t('vision_title'); ?>
        <h2 class="section-title fr"><?= $v['content_fr'] ?></h2>
        <h2 class="section-title en"><?= $v['content_en'] ?></h2>
        <?php $vl = t('vision_lead'); ?>
        <p class="section-lead fr"><?= $vl['content_fr'] ?></p>
        <p class="section-lead en"><?= $vl['content_en'] ?></p>
        <div style="margin-top:2rem;">
          <a href="#register" class="btn btn-purple">
            <span class="fr">Rejoindre <span class="brand-name" style="font-size:inherit;"><span class="soccer" style="color:inherit;">Soccer</span><span class="midable" style="color:var(--gold-light);">Midable</span></span></span>
            <span class="en">Join <span class="brand-name" style="font-size:inherit;"><span class="soccer" style="color:inherit;">Soccer</span><span class="midable" style="color:var(--gold-light);">Midable</span></span></span>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     ABOUT / WHO WE ARE
══════════════════════════════════════════ -->
<section id="about" class="section">
  <div class="container">
    <div class="about-grid">
      <div class="reveal">
        <div class="section-tag"><span class="fr">Qui sommes-nous</span><span class="en">Who We Are</span></div>
        <h2 class="section-title fr">Le <span class="accent brand-name"><span class="soccer">Soccer</span></span>Mindset</h2>
        <h2 class="section-title en">The <span class="accent brand-name"><span class="soccer">Soccer</span></span>Mindset</h2>
        <p class="section-lead fr" style="margin-bottom:1.2rem;">Bien plus qu'un programme de soccer, nous offrons à vos enfants de 2 à 14 ans un environnement sécurisant et bienveillant, où ils apprennent à découvrir leur potentiel, s'exprimer, croire en eux, travailler en équipe et se dépasser — sur le terrain comme dans la vie.</p>
        <p class="section-lead en" style="margin-bottom:1.2rem;">More than just a soccer program, we offer children aged 2 to 14 a safe, supportive environment where they learn to discover their potential, express themselves, believe in themselves, work as a team, and push beyond their limits — on the field and in life.</p>
        <p style="font-family:'Nunito',sans-serif;font-weight:900;font-size:0.7rem;letter-spacing:0.16em;text-transform:uppercase;color:var(--purple);margin-bottom:0.8rem;">
          <span class="fr">Nos 5 valeurs fondamentales</span>
          <span class="en">Our 5 Core Values</span>
        </p>
        <div class="values-row">
          <div class="value-pill">🏆 Discipline</div>
          <div class="value-pill">⭐ Excellence</div>
          <div class="value-pill">🤝 Respect</div>
          <div class="value-pill">💎 <span class="fr">Intégrité</span><span class="en">Integrity</span></div>
          <div class="value-pill">🌍 Inclusion</div>
        </div>
      </div>
      <div class="mindset-list reveal d2">
        <div class="mindset-item">
          <div class="mi-icon">⚽</div>
          <div>
            <div class="mi-title"><span class="fr">Formation sportive de qualité</span><span class="en">High-Quality Sports Training</span></div>
            <div class="mi-text"><span class="fr">Curriculum structuré et éprouvé, adapté à chaque groupe d'âge. Coaches formés et certifiés.</span><span class="en">A structured, proven curriculum adapted to each age group, led by trained and certified coaches.</span></div>
          </div>
        </div>
        <div class="mindset-item">
          <div class="mi-icon">🧠</div>
          <div>
            <div class="mi-title"><span class="fr">Développement du mindset</span><span class="en">Mindset Development</span></div>
            <div class="mi-text"><span class="fr">Compétences de vie : confiance en soi, résilience, leadership et intelligence émotionnelle.</span><span class="en">Life skills: self-confidence, resilience, leadership, and emotional intelligence.</span></div>
          </div>
        </div>
        <div class="mindset-item">
          <div class="mi-icon">📚</div>
          <div>
            <div class="mi-title"><span class="fr">Encadrement éducatif et ludique</span><span class="en">Educational & Playful Guidance</span></div>
            <div class="mi-text"><span class="fr">Un accompagnement qui favorise l'épanouissement global de l'enfant sur le terrain et dans la vie.</span><span class="en">Holistic support that fosters the child's overall growth both on the field and in everyday life.</span></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     PROGRAMS
══════════════════════════════════════════ -->
<section id="programs" class="section">
  <div class="container">
    <div class="programs-intro">
      <div class="reveal">
        <div class="section-tag gold"><span class="fr">Nos programmes</span><span class="en">Our Programs</span></div>
        <h2 class="section-title white fr">Une saison pour <span class="gold">grandir</span></h2>
        <h2 class="section-title white en">A season to <span class="gold">grow</span></h2>
      </div>
      <div class="reveal d2">
        <p class="section-lead white fr">Nous proposons des activités tout au long de l'année afin d'offrir aux enfants une expérience sportive enrichissante, inclusive et accessible.</p>
        <p class="section-lead white en">We offer year-round activities providing children with an enriching, inclusive, and accessible sports experience.</p>
        <div style="margin-top:1.5rem;"><a href="#register" class="btn btn-gold"><span class="fr">S'inscrire</span><span class="en">Register</span>&nbsp;→</a></div>
      </div>
    </div>
    <div class="programs-grid">
      <div class="prog-card reveal">
        <div class="prog-icon">📅</div>
        <div class="prog-title"><span class="fr">Programmes annuels</span><span class="en">Year-Round Programs</span></div>
        <p class="prog-text"><span class="fr">Sessions de 8 semaines adaptées à chaque groupe d'âge. Hiver, printemps, été, automne.</span><span class="en">Eight-week sessions tailored to each age group. Winter, spring, summer, and fall.</span></p>
        <span class="prog-badge">2–14 ans / years</span>
      </div>
      <div class="prog-card reveal d1">
        <div class="prog-icon">☀️</div>
        <div class="prog-title"><span class="fr">Camps d'été</span><span class="en">Summer Camps</span></div>
        <p class="prog-text"><span class="fr">Camps intensifs combinant soccer, jeux et activités éducatives.</span><span class="en">Intensive 1–2 week camps combining soccer, games, and educational activities.</span></p>
        <span class="prog-badge fr">Été · Summer</span><span class="prog-badge en">Summer</span>
      </div>
      <div class="prog-card reveal d2">
        <div class="prog-icon">🏫</div>
        <div class="prog-title"><span class="fr">Programmes scolaires</span><span class="en">School Programs</span></div>
        <p class="prog-text"><span class="fr">Partenariats avec écoles et garderies pour intégrer le soccer pendant les heures scolaires.</span><span class="en">Partnerships with schools and daycares to integrate soccer during school hours.</span></p>
        <span class="prog-badge fr">Parascolaire</span><span class="prog-badge en">After School</span>
      </div>
      <div class="prog-card reveal d3">
        <div class="prog-icon">🤝</div>
        <div class="prog-title"><span class="fr">Événements communautaires</span><span class="en">Community Events</span></div>
        <p class="prog-text"><span class="fr">Tournois, festivals et journées portes ouvertes pour rassembler les familles autour du soccer.</span><span class="en">Tournaments, festivals, and open days bringing families together through soccer.</span></p>
        <span class="prog-badge fr">Impact social</span><span class="prog-badge en">Social Impact</span>
      </div>
      <div class="prog-card reveal d4">
        <div class="prog-icon">👧</div>
        <div class="prog-title"><span class="fr">Programme pour filles</span><span class="en">Girls' Program</span></div>
        <p class="prog-text"><span class="fr">Programme d'autonomisation conçu pour les filles — parce que chaque jeune fille mérite de briller.</span><span class="en">Empowerment program exclusively for girls — because every girl deserves to shine.</span></p>
        <span class="prog-badge fr">Autonomisation</span><span class="prog-badge en">Empowerment</span>
      </div>
      <div class="prog-card reveal d5">
        <div class="prog-icon">🌍</div>
        <div class="prog-title"><span class="fr">Impact international</span><span class="en">International Impact</span></div>
        <p class="prog-text"><span class="fr">Initiatives d'autonomisation utilisant le sport comme outil de développement dans le monde.</span><span class="en">Empowerment initiatives using sport as a development tool worldwide.</span></p>
        <span class="prog-badge">13 pays / countries</span>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     KIDS PHOTO GALLERY — Properly Aligned
══════════════════════════════════════════ -->
<section id="gallery" class="section">
  <div class="container">
    <div style="text-align:center;" class="reveal">
      <div class="section-tag" style="justify-content:center;"><span class="fr">Nos enfants en action</span><span class="en">Our Kids in Action</span></div>
      <h2 class="section-title fr"><span class="accent">Moments</span> inoubliables</h2>
      <h2 class="section-title en">Unforgettable <span class="accent">moments</span></h2>
    </div>
    <div class="gallery-grid">
      <div class="gallery-item wide reveal">
        <img src="images/kids/kids-training-1.jpg" alt="Group training session" loading="lazy">
        <div class="gallery-caption"><div class="gallery-caption-text"><span class="fr">Entraînement collectif</span><span class="en">Group Training</span></div></div>
      </div>
      <div class="gallery-item reveal d1">
        <img src="images/kids/kids-jerseys.jpg" alt="Kids in SoccerMidable jerseys" loading="lazy">
        <div class="gallery-caption"><div class="gallery-caption-text"><span class="fr">Fiers de leur maillot</span><span class="en">Proud in their jerseys</span></div></div>
      </div>
      <div class="gallery-item reveal d1">
        <img src="images/kids/kids-toddler.jpg" alt="Young player with ball" loading="lazy">
        <div class="gallery-caption"><div class="gallery-caption-text"><span class="fr">Dès 2 ans !</span><span class="en">Starting at age 2!</span></div></div>
      </div>
      <div class="gallery-item reveal d2">
        <img src="images/kids/kids-tiny.jpg" alt="Tiny player learning" loading="lazy">
        <div class="gallery-caption"><div class="gallery-caption-text"><span class="fr">Apprentissage par le jeu</span><span class="en">Learning through play</span></div></div>
      </div>
      <div class="gallery-item reveal d2">
        <img src="images/kids/kids-coach-1.jpg" alt="Coach guiding child" loading="lazy">
        <div class="gallery-caption"><div class="gallery-caption-text"><span class="fr">Un encadrement personnalisé</span><span class="en">Personalized coaching</span></div></div>
      </div>
      <div class="gallery-item reveal d3">
        <img src="images/kids/kids-player.jpg" alt="Player dribbling" loading="lazy">
        <div class="gallery-caption"><div class="gallery-caption-text"><span class="fr">Technique et passion</span><span class="en">Skill and passion</span></div></div>
      </div>
      <div class="gallery-item reveal d3">
        <img src="images/kids/kids-training-9.jpeg" alt="Player dribbling" loading="lazy">
        <div class="gallery-caption"><div class="gallery-caption-text"><span class="fr">Technique et passion</span><span class="en">Skill and passion</span></div></div>
      </div>
      <div class="gallery-item reveal d3">
        <img src="images/kids/kids-training-21.jpeg" alt="Player dribbling" loading="lazy">
        <div class="gallery-caption"><div class="gallery-caption-text"><span class="fr">Technique et passion</span><span class="en">Skill and passion</span></div></div>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     PARENT TESTIMONIALS (Video)
══════════════════════════════════════════ -->
<section id="testimonials" class="section">
  <div class="container">
    <div style="text-align:center;margin-bottom:0.5rem;" class="reveal">
      <div class="section-tag" style="justify-content:center;"><span class="fr">Témoignages</span><span class="en">Testimonials</span></div>
      <h2 class="section-title fr">Ce que disent <span class="accent">les parents</span></h2>
      <h2 class="section-title en">What <span class="accent">parents say</span></h2>
      <p class="section-lead" style="max-width:560px;margin:0 auto 1.5rem;">
        <span class="fr">Découvrez les témoignages de familles dont la vie a été transformée par <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span>.</span>
        <span class="en">Hear from families whose lives have been transformed by <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span>.</span>
      </p>
    </div>
    <div class="testi-grid reveal">
      <div class="testi-video-card">
        <div class="testi-video-wrap">
         <video controls class="reveal">
            <source src="videos/testimonial-1.mp4" type="video/mp4">
          </video>
        </div>
        <div class="testi-info">
          <div class="testi-name fr">Témoignage d'un parent</div>
          <div class="testi-name en">Parent Testimonial</div>
          <div class="testi-role fr">Famille <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> · Ottawa–Gatineau</div>
          <div class="testi-role en"><span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> Family · Ottawa–Gatineau</div>
        </div>
      </div>
      <div class="testi-video-card">
        <div class="testi-video-wrap">
          <video controls class="reveal">
            <source src="videos/testimonial-10.mp4" type="video/mp4">
          </video>
        </div>
        <div class="testi-info">
          <div class="testi-name fr">Témoignage d'un parent</div>
          <div class="testi-name en">Parent Testimonial</div>
          <div class="testi-role fr">Famille <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> · Ottawa–Gatineau</div>
          <div class="testi-role en"><span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> Family · Ottawa–Gatineau</div>
        </div>
      </div>
      <!-- <div class="testi-video-card"> -->
        <!-- <div class="testi-video-wrap"> -->
          <!-- <video controls class="reveal"> -->
            <!-- <source src="videos/testimonial-3.mp4" type="video/mp4"> -->
          <!-- </video> -->
        <!-- </div> -->
        <!-- <div class="testi-info"> -->
          <!-- <div class="testi-name fr">Témoignage d'un parent</div> -->
          <!-- <div class="testi-name en">Parent Testimonial</div> -->
          <!-- <div class="testi-role fr">Famille <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> · Ottawa–Gatineau</div> -->
          <!-- <div class="testi-role en"><span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> Family · Ottawa–Gatineau</div> -->
        <!-- </div> -->
      <!-- </div> -->
      <!-- <div class="testi-video-card"> -->
        <!-- <div class="testi-video-wrap"> -->
          <!-- <video controls class="reveal"> -->
            <!-- <source src="videos/testimonial-4.mp4" type="video/mp4"> -->
          <!-- </video> -->
        <!-- </div> -->
        <!-- <div class="testi-info"> -->
          <!-- <div class="testi-name fr">Témoignage d'un parent</div> -->
          <!-- <div class="testi-name en">Parent Testimonial</div> -->
          <!-- <div class="testi-role fr">Famille <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> · Ottawa–Gatineau</div> -->
          <!-- <div class="testi-role en"><span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> Family · Ottawa–Gatineau</div> -->
        <!-- </div> -->
      <!-- </div> -->
      <div class="testi-video-card">
        <div class="testi-video-wrap">
          <video controls class="reveal">
            <source src="videos/testimonial-5.mp4" type="video/mp4">
          </video>
        </div>
        <div class="testi-info">
          <div class="testi-name fr">Témoignage d'un parent</div>
          <div class="testi-name en">Parent Testimonial</div>
          <div class="testi-role fr">Famille <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> · Ottawa–Gatineau</div>
          <div class="testi-role en"><span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> Family · Ottawa–Gatineau</div>
        </div>
      </div>
      <div class="testi-video-card">
        <div class="testi-video-wrap">
          <video controls class="reveal">
            <source src="videos/testimonial-6.mp4" type="video/mp4">
          </video>
        </div>
        <div class="testi-info">
          <div class="testi-name fr">Témoignage d'un parent</div>
          <div class="testi-name en">Parent Testimonial</div>
          <div class="testi-role fr">Famille <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> · Ottawa–Gatineau</div>
          <div class="testi-role en"><span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> Family · Ottawa–Gatineau</div>
        </div>
      </div>
      <div class="testi-video-card">
        <div class="testi-video-wrap">
          <video controls class="reveal">
            <source src="videos/testimonial-7.mp4" type="video/mp4">
          </video>
        </div>
        <div class="testi-info">
          <div class="testi-name fr">Témoignage d'un parent</div>
          <div class="testi-name en">Parent Testimonial</div>
          <div class="testi-role fr">Famille <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> · Ottawa–Gatineau</div>
          <div class="testi-role en"><span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> Family · Ottawa–Gatineau</div>
        </div>
      </div>
      <div class="testi-video-card">
        <div class="testi-video-wrap">
          <video controls class="reveal">
            <source src="videos/testimonial-8.mp4" type="video/mp4">
          </video>
        </div>
        <div class="testi-info">
          <div class="testi-name fr">Témoignage d'un parent</div>
          <div class="testi-name en">Parent Testimonial</div>
          <div class="testi-role fr">Famille <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> · Ottawa–Gatineau</div>
          <div class="testi-role en"><span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> Family · Ottawa–Gatineau</div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     VIDEOS — En Action
══════════════════════════════════════════ -->
<section id="videos" class="section">
  <div class="container">
    <div style="text-align:center;margin-bottom:3rem;" class="reveal">
      <div class="section-tag" style="justify-content:center;"><span class="fr">En action</span><span class="en">In Action</span></div>
      <h2 class="section-title fr">Voyez la <span class="accent">magie</span> opérer</h2>
      <h2 class="section-title en">See the <span class="accent">magic</span> happen</h2>
    </div>
    <div class="videos-grid">
      <div class="video-featured reveal">
        <div class="video-wrap">
          <!-- <iframe src="https://www.youtube.com/embed/tZJ-ROiW2Bs?rel=0&modestbranding=1" -->
            <!-- title="SoccerMidable – Highlights" allowfullscreen -->
            <!-- allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"> -->
          <!-- </iframe> -->
           <video controls autoplay muted loop class="reveal">
            <source src="videos/video-1.mp4" type="video/mp4">
          </video>
        </div>
      </div>
      <!-- <div class="reveal d1"> -->
        <!-- <div class="video-wrap"> -->
          <!-- <iframe src="https://www.youtube.com/embed/irbJ1-V8_1I?rel=0&modestbranding=1" title="SoccerMidable 2" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe> -->
        <!-- </div> -->
         <!-- <video controls autoplay muted loop class="reveal"> -->
            <!-- <source src="videos/video-1.mp4" type="video/mp4"> -->
          <!-- </video> -->
        <!-- </div> -->
      <!-- </div> -->
      <div class="reveal d2">
        <div class="video-wrap">
          <!-- <iframe src="https://www.youtube.com/embed/irbJ1-V8_1I?rel=0&modestbranding=1" title="SoccerMidable 2" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe> -->
        <!-- </div> -->
         <video controls autoplay muted loop class="reveal">
            <source src="videos/video-2.mp4" type="video/mp4">
          </video>
        </div>
      </div>
    <div style="text-align:center;margin-top:2.5rem;" class="reveal">
      <a href="https://www.youtube.com/@soccermidable" target="_blank" rel="noopener" class="btn btn-outline-purple">
        ▶&nbsp;<span class="fr">Voir toutes nos vidéos</span><span class="en">Watch all videos</span>
      </a>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     IMPACT
══════════════════════════════════════════ -->
<section id="impact" class="section">
  <div class="container">
    <div class="impact-top">
      <div class="reveal">
        <div class="section-tag white"><span class="fr">Notre impact</span><span class="en">Our Impact</span></div>
        <h2 class="section-title white fr">Un impact <span class="gold">mondial</span></h2>
        <h2 class="section-title white en">A <span class="gold">worldwide</span> impact</h2>
      </div>
      <div class="reveal d2">
        <p class="section-lead white fr">Nos programmes touchent des jeunes dans 13 pays grâce à nos initiatives d'impact social.</p>
        <p class="section-lead white en">Our programs reach youth in 13 countries through our ongoing social impact initiatives.</p>
      </div>
    </div>
    <div class="impact-kpis reveal">
      <div class="kpi-card"><span class="kpi-num">2000+</span><span class="kpi-label"><span class="fr">Enfants impactés</span><span class="en">Children impacted</span></span></div>
      <div class="kpi-card"><span class="kpi-num">13</span><span class="kpi-label"><span class="fr">Pays touchés</span><span class="en">Countries reached</span></span></div>
      <div class="kpi-card"><span class="kpi-num">3×</span><span class="kpi-label"><span class="fr">Meilleure entreprise</span><span class="en">Best Business Canada</span></span></div>
      <div class="kpi-card"><span class="kpi-num">7+</span><span class="kpi-label"><span class="fr">Années d'impact</span><span class="en">Years of impact</span></span></div>
    </div>
    <div class="reveal d2">
      <div class="countries-label fr">🇨🇦 Programmes actifs &nbsp;·&nbsp; 🌍 Impact social international</div>
      <div class="countries-label en">🇨🇦 Active programs &nbsp;·&nbsp; 🌍 International social impact</div>
      <div class="countries-wrap">
        <span class="country-tag active">🇨🇦 Canada</span>
        <span class="country-tag active">🇨🇩 <span class="fr">RDC</span><span class="en">DRC</span></span>
        <span class="country-tag">🇨🇲 Cameroun</span>
        <span class="country-tag">🇧🇫 Burkina Faso</span>
        <span class="country-tag">🇬🇦 Gabon</span>
        <span class="country-tag">🇸🇳 Sénégal</span>
        <span class="country-tag">🇹🇿 Tanzanie</span>
        <span class="country-tag">🇺🇬 Ouganda</span>
        <span class="country-tag">🇭🇹 Haïti</span>
        <span class="country-tag">🇹🇹 <span class="fr">Trinité-et-Tobago</span><span class="en">Trinidad & Tobago</span></span>
        <span class="country-tag">🇰🇪 Kenya</span>
        <span class="country-tag">🇧🇯 Bénin</span>
        <span class="country-tag">🇱🇷 Libéria</span>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     STORY
══════════════════════════════════════════ -->
<section id="story" class="section">
  <div class="container">
    <div class="story-grid">
      <div class="story-visual reveal-left">
        <div class="story-frame">
          <!-- 🧑‍💼 -->
          <div class="story-frame-overlay"></div>
          <div class="story-frame-label">
            <div class="sf-name">Marc-Cyrille Kamdem</div>
            <div class="sf-role fr">Co-Fondateur · <span class="brand-name" style="font-size:inherit;color:var(--gold-light);"><span class="soccer" style="color:white;">Soccer</span><span class="midable">Midable</span></span></div>
            <div class="sf-role en">Co-Founder · <span class="brand-name" style="font-size:inherit;color:var(--gold-light);"><span class="soccer" style="color:white;">Soccer</span><span class="midable">Midable</span></span></div>
          </div>
        </div>
        <div class="story-badge"><div class="sb-year">2018</div><span class="fr">Fondé au Canada</span><span class="en">Founded in Canada</span></div>
      </div>
      <div class="reveal-right">
        <div class="section-tag"><span class="fr">Notre histoire</span><span class="en">Our Story</span></div>
        <h2 class="section-title fr">L'histoire qui <span class="accent">inspire</span></h2>
        <h2 class="section-title en">The story that <span class="accent">inspires</span></h2>
        <blockquote class="story-quote fr">« L'idée est née à la naissance de ma fille. En tant que nouveau père, je souhaitais lui transmettre des valeurs essentielles comme l'indépendance, la confiance, le courage et la résilience à travers le sport. »</blockquote>
        <blockquote class="story-quote en">"The idea was born when my daughter was born. As a new father, I wanted to pass on essential values through sport — independence, confidence, courage, and resilience."</blockquote>
        <p class="story-body fr">Marc Cyrille Kamdem est un entrepreneur visionnaire, ancien joueur de soccer de haut niveau. Il a fondé SoccerMidable en 2018 au Canada, convaincu que le sport est un puissant levier d'épanouissement, d'inclusion et de développement pour les jeunes.</p>
        <p class="story-body en">Marc Cyrille Kamdem is a visionary entrepreneur and former high-level soccer player. He founded SoccerMidable in Canada in 2018, driven by the belief that sport is a powerful tool for personal growth, inclusion, and youth development.</p>
        <p class="story-body fr">Son initiative a déjà permis à des centaines de jeunes de bénéficier d'un environnement sécurisant, favorisant l'autonomie, la confiance en soi et l'ouverture vers des horizons académiques et professionnels.</p>
        <p class="story-body en">His initiative has already enabled hundreds of young people to thrive in a safe, supportive environment — fostering autonomy, self-confidence, and openness to academic and professional horizons.</p>
        <div class="awards">
          <div class="award-item"><div class="award-icon">🏅</div><div class="award-text fr">Lauréat – Concours RDÉE – 48h Top Chrono 2024</div><div class="award-text en">Award Winner – RDÉE – 48h Top Chrono 2024</div></div>
          <div class="award-item"><div class="award-icon">🌟</div><div class="award-text fr">Reconnaissance leadership & engagement communautaire 2023 – ADN</div><div class="award-text en">Leadership & Community Engagement Recognition 2023 – ADN</div></div>
          <div class="award-item"><div class="award-icon">🇨🇦</div><div class="award-text fr">Meilleure entreprise au Canada – Canadian Business Review 2022–2024</div><div class="award-text en">Best Business in Canada – Canadian Business Review 2022–2024</div></div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     TEAM — With Jay Yogo Yemo added
══════════════════════════════════════════ -->
<section id="team" class="section">
  <div class="container">
    <div style="text-align:center;margin-bottom:0.5rem;" class="reveal">
      <div class="section-tag" style="justify-content:center;"><span class="fr">Notre équipe</span><span class="en">Our Team</span></div>
      <h2 class="section-title fr"><span class="accent">Passionnés</span> à votre service</h2>
      <h2 class="section-title en">A <span class="accent">passionate</span> team for you</h2>
    </div>
    <div class="team-grid">
      <div class="team-card reveal">
        <div class="team-avatar">MC</div>
        <div class="team-name">Marc-Cyrille Kamdem</div>
        <div class="team-role fr">Co-Fondateur</div>
        <div class="team-role en">Co-Founder</div>
        <a href="mailto:marc.cyrille@soccermidable.com" class="team-email">marc.cyrille@soccermidable.com</a>
      </div>
      <div class="team-card reveal d1">
        <div class="team-avatar">JE</div>
        <div class="team-name">Jessica Efole</div>
        <div class="team-role fr">Co-Fondatrice</div>
        <div class="team-role en">Co-Founder</div>
        <a href="mailto:info@soccermidable.com" class="team-email">info@soccermidable.com</a>
      </div>
      <div class="team-card reveal d2">
        <div class="team-avatar">GO</div>
        <div class="team-name">Grace Obiang</div>
        <div class="team-role fr">Dir. des opérations</div>
        <div class="team-role en">Director of Operations</div>
        <a href="mailto:grace.obg@soccermidable.com" class="team-email">grace.obg@soccermidable.com</a>
      </div>
      <div class="team-card reveal d3">
        <div class="team-avatar">DK</div>
        <div class="team-name">Djeinaba Kane</div>
        <div class="team-role fr">Dir. des programmes & logistiques</div>
        <div class="team-role en">Director of Programs & Logistics</div>
        <a href="mailto:djein.kane@soccermidable.com" class="team-email">djein.kane@soccermidable.com</a>
      </div>
      <div class="team-card reveal d4">
        <div class="team-avatar">AK</div>
        <div class="team-name">Alexandrine Kamdem</div>
        <div class="team-role fr">Dir. des finances</div>
        <div class="team-role en">Director of Finance</div>
        <a href="mailto:info@soccermidable.com" class="team-email">info@soccermidable.com</a>
      </div>
      <div class="team-card reveal d5">
        <div class="team-avatar">JY</div>
        <div class="team-name">Jay Yogo Yemo</div>
        <div class="team-role fr">Dir. des partenariats stratégiques</div>
        <div class="team-role en">Dir. of Strategic Partnerships</div>
        <a href="mailto:info@soccermidable.com" class="team-email">info@soccermidable.com</a>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     PARTNERS — Miniature Side by Side
══════════════════════════════════════════ -->
<section id="partners" class="section-sm">
  <div class="container">
    <div style="text-align:center;" class="reveal">
      <div class="section-tag" style="justify-content:center;"><span class="fr">Nos partenaires</span><span class="en">Our Partners</span></div>
      <h2 class="section-title fr">Ensemble, on va <span class="accent">plus loin</span></h2>
      <h2 class="section-title en">Together, we go <span class="accent">further</span></h2>
    </div>
    <div class="partners-strip reveal d1">
      <div class="partner-logo">
        <img src="images/partners/partner-elykia.png" alt="Elykia Foundation" loading="lazy">
      </div>
      <div class="partner-logo">
        <img src="images/partners/partner-canadian-women-sport.png" alt="Canadian Women & Sport" loading="lazy">
      </div>
      <div class="partner-logo">
        <img src="images/partners/partner-wouessi.png" alt="Wouessi" loading="lazy">
      </div>
      <div class="partner-logo">
        <img src="images/partners/partner-mouvassur.png" alt="Mouvassur" loading="lazy">
      </div>
      <div class="partner-logo">
        <img src="images/partners/partner-jumpstart.png" alt="Jumpstart" loading="lazy">
      </div>
      <div class="partner-logo">
        <img src="images/partners/micky-media.png" alt="Micky Media" loading="lazy">
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     REGISTER
══════════════════════════════════════════ -->
<section id="register" class="section">
  <div class="container">
    <div class="register-grid">
      <!-- INFO -->
      <div class="reveal-left">
        <div class="section-tag gold"><span class="fr">Inscription</span><span class="en">Registration</span></div>
        <h2 class="section-title white fr">Prêt à commencer <span class="gold">l'aventure?</span></h2>
        <h2 class="section-title white en">Ready to start the <span class="gold">adventure?</span></h2>
        <p class="section-lead white fr">Nos programmes sont offerts dans la région d'Ottawa–Gatineau. Inscrivez votre enfant dès aujourd'hui!</p>
        <p class="section-lead white en">Our programs are available across the Ottawa–Gatineau region. Register your child today!</p>
        <div class="reg-info-items">
          <div class="reg-info-item">
            <div class="rii-icon">📍</div>
            <div>
              <div class="rii-title">Ottawa–Gatineau</div>
              <div class="rii-sub fr">Plusieurs emplacements dans la région</div>
              <div class="rii-sub en">Multiple locations throughout the region</div>
            </div>
          </div>
          <div class="reg-info-item">
            <div class="rii-icon">👶</div>
            <div>
              <div class="rii-title fr">Enfants de 2 à 14 ans</div>
              <div class="rii-title en">Children Ages 2 to 14</div>
              <div class="rii-sub fr">Groupes adaptés à chaque tranche d'âge</div>
              <div class="rii-sub en">Groups tailored to each age range</div>
            </div>
          </div>
          <div class="reg-info-item">
            <div class="rii-icon">✉️</div>
            <div><div class="rii-title">info@soccermidable.com</div></div>
          </div>
          <div class="reg-info-item">
            <div class="rii-icon">📸</div>
            <div>
              <div class="rii-title">@soccermidablecanada</div>
              <div class="rii-sub">Instagram · Facebook</div>
            </div>
          </div>
        </div>
      </div>
      <!-- FORM -->
      <div class="reveal-right d1">
        <div class="reg-form-box">
          <div id="regFormInner">
            <div class="reg-form-title">📝 <span class="fr">Formulaire d'inscription</span><span class="en">Registration Form</span></div>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label"><span class="fr">Nom du parent 1 *</span><span class="en">Parent's Name 1 *</span></label>
                <input id="parentName_1" type="text" class="form-input" placeholder="Jean / John">
              </div>
              <div class="form-group">
                <label class="form-label"><span class="fr">Nom du parent 2 *</span><span class="en">Parent's Name 2 *</span></label>
                <input id="parentName_2" type="text" class="form-input" placeholder="Marie / Jane">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label"><span class="fr">Adresse</span><span class="en">Address</span></label>
              <input id="parentAddress" type="text" class="form-input" placeholder="123 Rue Principale">
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label"><span class="fr">Courriel *</span><span class="en">Email *</span></label>
                <input id="parentEmail" type="email" class="form-input" placeholder="email@exemple.com">
              </div>
              <div class="form-group">
                <label class="form-label"><span class="fr">Téléphone</span><span class="en">Phone</span></label>
                <input id="parentPhone" type="tel" class="form-input" placeholder="613-xxx-xxxx">
              </div>
            </div>
     
              <div class="form-group">
                <label class="form-label"><span class="fr">Nom et prénom de l'enfant</span><span class="en">Child's Full Name</span></label>
                <input id="childName" type="text" class="form-input" placeholder="Nom complet de l'enfant">
              </div>
              <div class="form-group">
                <label class="form-label"><span class="fr">Date de naissance</span><span class="en">Date of Birth</span></label>
                  <input id="childDOB" type="date" class="form-input">
              </div>
            
            <div class="form-group">
              <label class="form-label"><span class="fr">Localisation</span><span class="en">Location</span></label>
              <select id="location" class="form-input" onchange="filterPrograms()">
                <option value="">—</option>
                <?php
                $locs = get_active_locations();
                foreach ($locs as $l):
                ?>
                <option value="<?= htmlspecialchars($l['id']) ?>" data-name="<?= htmlspecialchars($l['name']) ?>"><?= htmlspecialchars($l['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group">
              <label class="form-label"><span class="fr">Programme d'intérêt</span><span class="en">Program of Interest</span></label>
              <select id="program" class="form-input">
                <option value="">—</option>
              </select>
            </div>

            <script>
              const allPrograms = <?php echo json_encode(get_active_programs()); ?>;
              function filterPrograms() {
                const locSelect = document.getElementById('location');
                const progSelect = document.getElementById('program');
                const locId = locSelect.value;
                const lang = document.body.getAttribute('data-lang') || 'fr';

                progSelect.innerHTML = '<option value="">—</option>';

                if (!locId) {
                  progSelect.innerHTML = lang === 'fr' ? '<option value="">— Choisissez d\'abord un lieu —</option>' : '<option value="">— Choose a location first —</option>';
                  return;
                }

                const filtered = allPrograms.filter(p => p.location_id == locId);
                if (filtered.length === 0) {
                  progSelect.innerHTML = lang === 'fr' ? '<option value="">—</option>' : '<option value="">—</option>';
                } else {
                  filtered.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.name_fr;
                    opt.textContent = lang === 'fr' ? p.name_fr : p.name_en;
                    progSelect.appendChild(opt);
                  });
                }
              }
            </script>
            <div class="form-group">
              <label class="form-label"><span class="fr">Message (optionnel)</span><span class="en">Message (optional)</span></label>
              <textarea id="message" class="form-input" rows="3" style="resize:vertical;" placeholder="Questions ou commentaires…"></textarea>
            </div>

            <div class="form-group consent-group">
              <input type="checkbox" id="consent-1" class="form-checkbox">
              <label class="form-label"><span class="fr">Décharge de responsabilité <br> Je comprends que la participation à des activités sportives comporte certains risques. J’accepte que l’Association sportive SoccerMidable, son personnel, ses entraîneurs(euses) et volontaires ne puissent être tenus responsables des blessures ou accident pouvant survenir durant la participation. Une assistance médicale d’urgence peut être fournie si nécessaire</span>
              <span class="en">Liability waiver <br> I understand that participation in sports involves physical activity and certain risks. I release Association sportive SoccerMidable, its staff, coaches and volunteers from any claims related to injuries or accidents that may occur during participation. Emergency medical attention may be provided if necessary.</span></label>
            </div>
            
            <div class="form-group consent-group">
              <input type="checkbox" id="consent-2" class="form-checkbox">
              <label class="form-label"><span class="fr">Consentement photo/vidéo <br> J’autorise l’Association sportive SoccerMidable à prendre des photos, vidéos ou enregistrements audio de mon enfant dans le cadre des activités du programme, à des fins documentaires ou promotionnelles. Aucun nom ne sera partagé.</span>
              <span class="en">Photo/ Video Consent <br> I authorize Association Sportive SoccerMidable to take photos, videos and audio recordings of my child during program activities for documentation and promotional purposes. No names will be shared.</span></label>
            </div>
            <button class="form-submit" onclick="submitForm()">
              ⚽ <span class="fr">S'inscrire maintenant</span><span class="en">Register Now</span>
            </button>
          </div>
          <div class="form-success" id="formSuccess">
            <div class="form-success-icon">🎉</div>
            <h3><span class="fr">Merci!</span><span class="en">Thank you!</span></h3>
            <p>
              <span class="fr">Notre équipe vous contactera très bientôt. Bienvenue dans la famille <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span>! ⚽</span>
              <span class="en">Our team will be in touch soon. Welcome to the <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span> family! ⚽</span>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     WIDGET
══════════════════════════════════════════ -->
<section id="widget" class="section-sm">
  <div class="container">
    <div style="text-align:center;" class="reveal">
      <div class="section-tag" style="justify-content:center;"><span class="fr">Widget partageable</span><span class="en">Shareable Widget</span></div>
      <h2 class="section-title fr">Partagez <span class="accent brand-name"><span class="soccer">Soccer</span><span class="midable">Midable</span></span></h2>
      <h2 class="section-title en">Share <span class="accent brand-name"><span class="soccer">Soccer</span><span class="midable">Midable</span></span></h2>
      <p class="section-lead" style="max-width:480px;margin:0 auto 1.5rem;">
        <span class="fr">Intégrez ce widget sur votre site ou blog pour inviter d'autres familles.</span>
        <span class="en">Embed this widget on your website or blog to invite other families.</span>
      </p>
    </div>
    <div class="widget-preview reveal d1">
      <img src="images/logo-purple.png" alt="SoccerMidable" class="wp-logo">
      <div class="wp-title fr brand-name"><span class="soccer">Soccer</span><span class="midable">Midable</span></div>
      <div class="wp-title en brand-name"><span class="soccer">Soccer</span><span class="midable">Midable</span></div>
      <p class="wp-text fr">Programme de soccer · Enfants 2–14 ans · Ottawa–Gatineau</p>
      <p class="wp-text en">Soccer program · Children 2–14 yrs · Ottawa–Gatineau</p>
      <a href="#register" class="wp-btn wp-btn-gold fr">⚽ S'inscrire maintenant</a>
      <a href="#register" class="wp-btn wp-btn-gold en">⚽ Register Now</a>
      <a href="https://www.instagram.com/soccermidablecanada/" target="_blank" class="wp-btn wp-btn-purple">📸 @soccermidablecanada</a>
    </div>
    <!-- <div class="code-box reveal d2"> -->
      <!-- <button class="code-copy" onclick="copyWidget()"><span class="fr">Copier</span><span class="en">Copy</span></button> -->
      <!-- <pre id="widgetCode">&lt;!-- SoccerMidable Widget --&gt; -->
        <!-- &lt;div style="border:2px solid #6C3FA0;border-radius:20px;padding:24px;max-width:360px;font-family:sans-serif;text-align:center;"&gt; -->
          <!-- &lt;p style="font-size:1.3rem;font-weight:900;margin:0 0 6px;"&gt;&lt;span style="color:#6C3FA0;"&gt;Soccer&lt;/span&gt;&lt;span style="color:#F5A623;"&gt;Midable&lt;/span&gt;&lt;/p&gt; -->
          <!-- &lt;p style="color:#666;font-size:0.83rem;margin:0 0 14px;"&gt;Soccer · Enfants 2–14 ans · Ottawa–Gatineau&lt;/p&gt; -->
          <!-- &lt;a href="https://soccermidable.ca/#register" -->
            <!-- style="display:block;padding:12px;background:#F5A623;color:#2D1B4E;border-radius:50px;text-decoration:none;font-weight:800;font-size:0.88rem;margin-bottom:8px;"&gt; -->
            <!-- S'inscrire / Register Now -->
          <!-- &lt;/a&gt; -->
          <!-- &lt;a href="https://www.instagram.com/soccermidablecanada/" -->
            <!-- style="display:block;padding:10px;background:#6C3FA0;color:white;border-radius:50px;text-decoration:none;font-weight:700;font-size:0.82rem;"&gt; -->
            <!-- 📸 @soccermidablecanada -->
          <!-- &lt;/a&gt; -->
          <!-- &lt;p style="margin:10px 0 0;font-size:0.72rem;color:#999;"&gt;info@soccermidable.com&lt;/p&gt; -->
        <!-- &lt;/div&gt;</pre> -->
    <!-- </div> -->
  </div>
</section>


<!-- ══════════════════════════════════════════
     SOCIAL
══════════════════════════════════════════ -->
<section id="social-section">
  <div class="container">
    <div class="reveal">
      <div class="section-tag" style="justify-content:center;"><span class="fr">Suivez-nous</span><span class="en">Follow Us</span></div>
      <h2 class="section-title fr" style="text-align:center;">Rejoignez notre <span class="accent">communauté</span></h2>
      <h2 class="section-title en" style="text-align:center;">Join our <span class="accent">community</span></h2>
      <div class="social-btns">
        <a href="https://www.instagram.com/soccermidablecanada/" target="_blank" rel="noopener" class="soc-btn soc-btn-ig">📸 @soccermidablecanada</a>
        <a href="https://www.instagram.com/soccermidablerdc/" target="_blank" rel="noopener" class="soc-btn soc-btn-ig">📸 @soccermidablerdc</a>
        <a href="https://www.facebook.com/profile.php?id=100063766651457" target="_blank" rel="noopener" class="soc-btn soc-btn-fb">👥 SoccerMidable</a>
      </div>
    </div>
  </div>
</section>


<!-- ══════════════════════════════════════════
     FOOTER
══════════════════════════════════════════ -->
<footer>
  <div class="container">
    <div class="footer-grid">
      <div>
        <img src="images/logo-white.png" alt="SoccerMidable" class="footer-brand-logo">
        <p class="footer-brand-desc fr">Plus qu'un programme de soccer — une plateforme de leadership et d'épanouissement pour les champions de demain. Fondé en 2018 à Ottawa, Canada.</p>
        <p class="footer-brand-desc en">More than a soccer program — a leadership and life-skills platform for tomorrow's champions. Founded in 2018 in Ottawa, Canada.</p>
        <div class="footer-socials">
          <a href="https://www.instagram.com/soccermidablecanada/" target="_blank" class="footer-soc-btn" title="Instagram Canada">📸</a>
          <a href="https://www.instagram.com/soccermidablerdc/" target="_blank" class="footer-soc-btn" title="Instagram RDC">🌍</a>
          <a href="https://www.facebook.com/profile.php?id=100063766651457" target="_blank" class="footer-soc-btn" title="Facebook">👥</a>
        </div>
      </div>
      <div>
        <div class="footer-col-title fr">Navigation</div>
        <div class="footer-col-title en">Navigation</div>
        <ul class="footer-links">
          <li><a href="#vision"><span class="fr">Vision</span><span class="en">Vision</span></a></li>
          <li><a href="#about"><span class="fr">À propos</span><span class="en">About</span></a></li>
          <li><a href="#programs"><span class="fr">Programmes</span><span class="en">Programs</span></a></li>
          <li><a href="#gallery"><span class="fr">Photos</span><span class="en">Photos</span></a></li>
          <li><a href="#impact">Impact</a></li>
          <li><a href="#partners"><span class="fr">Partenaires</span><span class="en">Partners</span></a></li>
          <li><a href="#story"><span class="fr">Notre histoire</span><span class="en">Our Story</span></a></li>
          <li><a href="#team"><span class="fr">Équipe</span><span class="en">Team</span></a></li>
        </ul>
      </div>
      <div>
        <div class="footer-col-title fr">Programmes</div>
        <div class="footer-col-title en">Programs</div>
        <ul class="footer-links">
          <li><a href="#programs"><span class="fr">Programmes annuels</span><span class="en">Year-Round</span></a></li>
          <li><a href="#programs"><span class="fr">Camps d'été</span><span class="en">Summer Camps</span></a></li>
          <li><a href="#programs"><span class="fr">Programmes scolaires</span><span class="en">School Programs</span></a></li>
          <li><a href="#programs"><span class="fr">Programme pour filles</span><span class="en">Girls' Program</span></a></li>
          <li><a href="#programs"><span class="fr">Événements communautaires</span><span class="en">Community Events</span></a></li>
        </ul>
      </div>
      <div>
        <div class="footer-col-title">Contact</div>
        <ul class="footer-links">
          <li><a href="mailto:info@soccermidable.com">info@soccermidable.com</a></li>
          <li><a href="https://www.instagram.com/soccermidablecanada/" target="_blank">@soccermidablecanada</a></li>
          <li><a href="https://www.instagram.com/soccermidablerdc/" target="_blank">@soccermidablerdc</a></li>
          <li><a href="#register" style="color:var(--gold);font-weight:700;">→ <span class="fr">S'inscrire</span><span class="en">Register</span></a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="footer-copy">© 2024–2026 <span class="brand-name" style="font-size:inherit;"><span class="soccer">Soccer</span><span class="midable">Midable</span></span>. <span class="fr">Tous droits réservés.</span><span class="en">All rights reserved.</span></div>
      <div class="footer-slogan">CHAMPIONS DE LA VIE · CHAMPIONS IN LIFE</div>
    </div>
  </div>
</footer>

<button id="back-top" aria-label="Back to top">↑</button>
<div id="toast"></div>
<script src="js/main.js?v=1.1.7"></script>
</body>
</html>
