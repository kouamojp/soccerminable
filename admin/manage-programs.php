<?php
/**
 * MANAGE-PROGRAMS.PHP — Gestion des programmes de soccer
 */
require_once 'auth.php';
require_once 'db.php';
require_admin_login();

$message = '';

// Ajouter un programme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name_fr = $_POST['name_fr'];
    $name_en = $_POST['name_en'];
    $price_id = $_POST['price_id'];

    $stmt = $pdo->prepare("INSERT INTO programs (name_fr, name_en, price_id_stripe) VALUES (?, ?, ?)");
    $stmt->execute([$name_fr, $name_en, $price_id]);
    $message = "Programme ajouté !";
}

// Supprimer un programme
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $message = "Programme supprimé !";
}

// Récupération de tous les programmes
$stmt = $pdo->query("SELECT * FROM programs ORDER BY id DESC");
$programs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Programmes | SoccerMidable Admin</title>
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
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 1rem; }
        .btn-add { background: #4CAF50; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 700; }
        .btn-delete { color: #f44336; text-decoration: none; font-size: 0.8rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        .alert { background: #4CAF50; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header"><h2>SoccerMidable</h2><small>Admin</small></div>
        <div class="sidebar-menu">
            <a href="index.php">Tableau de bord</a>
            <a href="manage-content.php">Gestion des Textes</a>
            <a href="manage-programs.php" class="active">Gestion des Programmes</a>
            <a href="manage-testimonials.php">Témoignages</a>
        </div>
    </div>
    <div class="main">
        <h1>Gestion des Programmes</h1>
        <?php if ($message): ?><div class="alert"><?= $message ?></div><?php endif; ?>

        <div class="card">
            <h3>Ajouter un programme</h3>
            <form method="POST">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div><label>Nom (FR)</label><input type="text" name="name_fr" required placeholder="Ex: U2-3 de 9h à 9h30"></div>
                    <div><label>Nom (EN)</label><input type="text" name="name_en" required placeholder="Ex: U2-3 from 9:00 to 9:30"></div>
                </div>
                <label>ID Prix Stripe (price_...)</label>
                <input type="text" name="price_id" required placeholder="price_1Oiam...">
                <button type="submit" name="add" class="btn-add">Ajouter le programme</button>
            </form>
        </div>

        <div class="card">
            <h3>Liste des programmes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nom (FR)</th>
                        <th>ID Stripe</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programs as $prog): ?>
                    <tr>
                        <td><?= htmlspecialchars($prog['name_fr']) ?></td>
                        <td><code><?= htmlspecialchars($prog['price_id_stripe']) ?></code></td>
                        <td><?= $prog['is_active'] ? 'Actif' : 'Inactif' ?></td>
                        <td><a href="?delete=<?= $prog['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer ce programme ?')">Supprimer</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
