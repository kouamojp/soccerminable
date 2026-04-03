<?php
/**
 * MANAGE-CONTENT.PHP — Gestion des textes dynamiques
 */
require_once 'auth.php';
require_once 'db.php';
require_admin_login();

$message = '';

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $content_fr = $_POST['content_fr'];
    $content_en = $_POST['content_en'];

    $stmt = $pdo->prepare("UPDATE site_content SET content_fr = ?, content_en = ? WHERE id = ?");
    $stmt->execute([$content_fr, $content_en, $id]);
    $message = "Contenu mis à jour avec succès !";
}

// Récupération de tous les contenus
$stmt = $pdo->query("SELECT * FROM site_content ORDER BY section_key");
$contents = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Textes | SoccerMidable Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root { --purple: #6A1B9A; --gold: #FFD700; --bg: #f4f7f6; --sidebar: #1a237e; }
        body { font-family: 'Nunito', sans-serif; margin: 0; background: var(--bg); display: flex; }
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar); color: white; position: fixed; }
        .sidebar-header { padding: 2rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-menu a { display: block; padding: 12px 2rem; color: rgba(255,255,255,0.7); text-decoration: none; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: white; border-left: 4px solid var(--gold); }
        .main { margin-left: 260px; flex: 1; padding: 2rem; }
        .card { background: white; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; margin-bottom: 1rem; }
        .btn-update { background: var(--purple); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 700; }
        .alert { background: #4CAF50; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header"><h2>SoccerMidable</h2><small>Admin</small></div>
        <div class="sidebar-menu">
            <a href="index.php">Tableau de bord</a>
            <a href="manage-content.php" class="active">Gestion des Textes</a>
            <a href="manage-programs.php">Gestion des Programmes</a>
            <a href="manage-testimonials.php">Témoignages</a>
        </div>
    </div>
    <div class="main">
        <h1>Gestion des Textes Dynamiques</h1>
        <?php if ($message): ?><div class="alert"><?= $message ?></div><?php endif; ?>

        <?php foreach ($contents as $content): ?>
        <div class="card">
            <h3 style="margin-top:0; color:var(--purple);">Clé : <?= htmlspecialchars($content['section_key']) ?></h3>
            <form method="POST">
                <input type="hidden" name="id" value="<?= $content['id'] ?>">
                <label>Français (FR)</label>
                <textarea name="content_fr" rows="3"><?= htmlspecialchars($content['content_fr']) ?></textarea>
                <label>Anglais (EN)</label>
                <textarea name="content_en" rows="3"><?= htmlspecialchars($content['content_en']) ?></textarea>
                <button type="submit" name="update" class="btn-update">Enregistrer les modifications</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
