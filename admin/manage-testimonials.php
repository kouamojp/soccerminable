<?php
/**
 * MANAGE-TESTIMONIALS.PHP — Gestion des témoignages vidéo
 */
require_once 'auth.php';
require_once 'db.php';
require_admin_login();

$message = '';

// Ajouter un témoignage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $video_url = $_POST['video_url'];
    $stmt = $pdo->prepare("INSERT INTO testimonials (video_url) VALUES (?)");
    $stmt->execute([$video_url]);
    $message = "Témoignage ajouté !";
}

// Supprimer
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $message = "Témoignage supprimé !";
}

$stmt = $pdo->query("SELECT * FROM testimonials ORDER BY id DESC");
$testimonials = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Témoignages | SoccerMidable Admin</title>
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
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 1rem; }
        .btn-add { background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 700; }
        .btn-delete { color: #f44336; text-decoration: none; font-size: 0.8rem; }
        .alert { background: #4CAF50; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem; }
        .testi-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; }
        .testi-item { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .testi-video { aspect-ratio: 16/9; background: #000; }
        .testi-body { padding: 1rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header"><h2>SoccerMidable</h2><small>Admin</small></div>
        <div class="sidebar-menu">
            <a href="index.php">Tableau de bord</a>
            <a href="manage-content.php">Gestion des Textes</a>
            <a href="manage-programs.php">Gestion des Programmes</a>
            <a href="manage-testimonials.php" class="active">Témoignages</a>
        </div>
    </div>
    <div class="main">
        <h1>Gestion des Témoignages Vidéo</h1>
        <?php if ($message): ?><div class="alert"><?= $message ?></div><?php endif; ?>

        <div class="card">
            <h3>Ajouter une vidéo</h3>
            <form method="POST">
                <label>Chemin de la vidéo (ex: videos/mon-video.mp4)</label>
                <input type="text" name="video_url" required placeholder="videos/...">
                <button type="submit" name="add" class="btn-add">Ajouter le témoignage</button>
            </form>
        </div>

        <div class="testi-list">
            <?php foreach ($testimonials as $t): ?>
            <div class="testi-item">
                <div class="testi-video">
                    <video width="100%" height="100%" controls>
                        <source src="../<?= htmlspecialchars($t['video_url']) ?>" type="video/mp4">
                    </video>
                </div>
                <div class="testi-body">
                    <p style="margin:0; font-weight:700;">Source: <?= htmlspecialchars($t['video_url']) ?></p>
                    <a href="?delete=<?= $t['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer ?')">Supprimer</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
