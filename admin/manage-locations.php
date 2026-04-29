<?php
/**
 * MANAGE-LOCATIONS.PHP — Gestion des lieux de pratique
 */
require_once 'auth.php';
require_once 'db.php';
require_admin_login();

$msg = '';

// Ajouter un lieu
if (isset($_POST['add_location'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO locations (name) VALUES (?)");
        $stmt->execute([$name]);
        $msg = "Lieu ajouté avec succès.";
    }
}

// Supprimer un lieu
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM locations WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: manage-locations.php?msg=deleted");
    exit;
}

// Toggle status
if (isset($_GET['toggle'])) {
    $stmt = $pdo->prepare("UPDATE locations SET is_active = NOT is_active WHERE id = ?");
    $stmt->execute([$_GET['toggle']]);
    header("Location: manage-locations.php");
    exit;
}

// Récupérer tous les lieux
$stmt = $pdo->query("SELECT * FROM locations ORDER BY name ASC");
$locations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Lieux | SoccerMidable Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root { --purple: #6A1B9A; --gold: #FFD700; --bg: #f4f7f6; --sidebar: #1a237e; }
        body { font-family: 'Nunito', sans-serif; margin: 0; background: var(--bg); display: flex; }
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar); color: white; position: fixed; }
        .sidebar-header { padding: 2rem; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-menu { padding: 1rem 0; }
        .sidebar-menu a { display: block; padding: 12px 2rem; color: rgba(255,255,255,0.7); text-decoration: none; transition: 0.3s; }
        .sidebar-menu a:hover, .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: white; border-left: 4px solid var(--gold); }
        .main { margin-left: 260px; flex: 1; padding: 2rem; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .card { background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 2rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; }
        .btn { padding: 8px 16px; border-radius: 5px; text-decoration: none; font-weight: 700; border: none; cursor: pointer; }
        .btn-purple { background: var(--purple); color: white; }
        .btn-red { background: #f44336; color: white; }
        .status-pill { padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; }
        .status-active { background: #e8f5e9; color: #2e7d32; }
        .status-inactive { background: #ffebee; color: #c62828; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 700; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2 style="margin:0; font-size:1.2rem; color:var(--gold);">SoccerMidable</h2>
            <small>Gestionnaire de contenu</small>
        </div>
        <div class="sidebar-menu">
            <a href="index.php">Tableau de bord</a>
            <a href="manage-registrations.php">Inscriptions</a>
            <a href="manage-locations.php" class="active">Gestion des Lieux</a>
            <a href="manage-content.php">Gestion des Textes</a>
            <a href="manage-programs.php">Gestion des Programmes</a>
            <a href="manage-testimonials.php">Témoignages</a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>Gestion des Lieux</h1>
            <a href="logout.php" style="color:#f44336; text-decoration:none; font-weight:700;">Déconnexion</a>
        </div>

        <?php if ($msg || isset($_GET['msg'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem;">
                <?= $msg ?: ($_GET['msg'] == 'deleted' ? 'Lieu supprimé avec succès.' : '') ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Ajouter un nouveau lieu</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nom du lieu (ex: Kanata, Orleans...)</label>
                    <input type="text" name="name" class="form-control" placeholder="Nom du lieu" required>
                </div>
                <button type="submit" name="add_location" class="btn btn-purple">Ajouter le lieu</button>
            </form>
        </div>

        <div class="card">
            <h2>Lieux existants</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($locations as $loc): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($loc['name']) ?></strong></td>
                            <td>
                                <span class="status-pill <?= $loc['is_active'] ? 'status-active' : 'status-inactive' ?>">
                                    <?= $loc['is_active'] ? 'Actif' : 'Inactif' ?>
                                </span>
                            </td>
                            <td>
                                <a href="?toggle=<?= $loc['id'] ?>" class="btn" style="background:#eee; color:#333; font-size:0.8rem;">
                                    <?= $loc['is_active'] ? 'Désactiver' : 'Activer' ?>
                                </a>
                                <a href="?delete=<?= $loc['id'] ?>" class="btn btn-red" style="font-size:0.8rem;" onclick="return confirm('Supprimer ce lieu ?')">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
