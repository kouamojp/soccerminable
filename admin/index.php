<?php
/**
 * INDEX.PHP — Tableau de bord Admin
 */
require_once 'auth.php';
require_once 'db.php';
require_admin_login();

// Statistiques rapides
$stmt = $pdo->query("SELECT COUNT(*) FROM programs");
$count_programs = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM site_content");
$count_content = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM registrations");
$count_registrations = $stmt->fetchColumn();


$locations = $pdo->query("SELECT COUNT(*) FROM locations");
$count_locations = $locations->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SoccerMidable Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --purple: #6A1B9A;
            --gold: #FFD700;
            --bg: #f4f7f6;
            --sidebar: #1a237e;
        }
        body { font-family: 'Nunito', sans-serif; margin: 0; background: var(--bg); display: flex; }
        
        /* Sidebar */
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar); color: white; position: fixed; }
        .sidebar-header { padding: 2rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-menu { padding: 1rem 0; }
        .sidebar-menu a {
            display: block; padding: 12px 2rem; color: rgba(255,255,255,0.7);
            text-decoration: none; transition: 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1); color: white; border-left: 4px solid var(--gold);
        }

        /* Main Content */
        .main { margin-left: 260px; flex: 1; padding: 2rem; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .welcome h1 { margin: 0; font-weight: 900; color: #333; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .stat-card h3 { margin: 0; color: #666; font-size: 0.9rem; text-transform: uppercase; }
        .stat-card .num { font-size: 2.5rem; font-weight: 900; color: var(--purple); margin: 0.5rem 0; }
        
        .btn-action {
            display: inline-block; padding: 10px 20px; background: var(--purple); color: white;
            text-decoration: none; border-radius: 5px; font-weight: 700; margin-top: 1rem;
        }
        .logout { color: #f44336; text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2 style="margin:0; font-size:1.2rem; color:var(--gold);">SoccerMidable</h2>
            <small>Gestionnaire de contenu</small>
        </div>
        <div class="sidebar-menu">
            <a href="index.php" class="active">Tableau de bord</a>
            <a href="manage-registrations.php">Inscriptions</a>
            <a href="manage-content.php">Gestion des Textes</a>
            <a href="manage-programs.php">Gestion des Programmes</a>
            <a href="manage-testimonials.php">Témoignages</a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <div class="welcome">
                <h1>Bienvenue, <?= htmlspecialchars($_SESSION['admin_user']) ?></h1>
                <p>Que souhaitez-vous modifier aujourd'hui ?</p>
            </div>
            <a href="logout.php" class="logout">Déconnexion</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Inscriptions</h3>
                <div class="num"><?= $count_registrations ?></div>
                <a href="manage-registrations.php" class="btn-action">Gérer</a>
            </div>
            <div class="stat-card">
                <h3>Lieux</h3>
                <div class="num"><?= $count_locations ?></div>
                <a href="manage-locations.php" class="btn-action">Gérer</a>
            </div>
            <div class="stat-card">
                <h3>Programmes Actifs</h3>
                <div class="num"><?= $count_programs ?></div>
                <a href="manage-programs.php" class="btn-action">Gérer</a>
            </div>
        </div>

        <div style="margin-top: 3rem; background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <h2 style="margin-top:0;">Dernières activités</h2>
            <p style="color: #666;">Le système est prêt pour vos modifications. Cliquez sur les menus à gauche pour commencer.</p>
        </div>
    </div>
</body>
</html>
