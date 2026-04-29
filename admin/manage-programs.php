<?php
/**
 * MANAGE-PROGRAMS.PHP — Gestion des programmes de soccer
 */
require_once 'auth.php';
require_once 'db.php';
require_admin_login();

$message = '';
$edit_prog = null;

// Ajouter un programme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name_fr = $_POST['name_fr'];
    $name_en = $_POST['name_en'];
    $price_id = $_POST['price_id'];
    $location_id = !empty($_POST['location_id']) ? $_POST['location_id'] : null;

    $stmt = $pdo->prepare("INSERT INTO programs (name_fr, name_en, price_id_stripe, location_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name_fr, $name_en, $price_id, $location_id]);
    $message = "Programme ajouté !";
}

// Modifier un programme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name_fr = $_POST['name_fr'];
    $name_en = $_POST['name_en'];
    $price_id = $_POST['price_id'];
    $location_id = !empty($_POST['location_id']) ? $_POST['location_id'] : null;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE programs SET name_fr = ?, name_en = ?, price_id_stripe = ?, location_id = ?, is_active = ? WHERE id = ?");
    $stmt->execute([$name_fr, $name_en, $price_id, $location_id, $is_active, $id]);
    $message = "Programme mis à jour !";
}

// Charger un programme pour édition
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_prog = $stmt->fetch();
}

// Supprimer un programme
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $message = "Programme supprimé !";
}

// Récupération de tous les programmes avec le nom de leur localisation
$stmt = $pdo->query("
    SELECT p.*, l.name as location_name 
    FROM programs p 
    LEFT JOIN locations l ON p.location_id = l.id 
    ORDER BY p.id DESC
");
$programs = $stmt->fetchAll();

// Récupération des localisations pour le formulaire
$locations = get_active_locations();
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
        .btn-update { background: var(--purple); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: 700; }
        .btn-delete { color: #f44336; text-decoration: none; font-size: 0.8rem; }
        .btn-edit { color: var(--purple); text-decoration: none; font-size: 0.8rem; margin-right: 10px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; }
        .alert { background: #4CAF50; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1.5rem; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; }
        .badge-active { background: #e8f5e9; color: #2e7d32; }
        .badge-inactive { background: #ffebee; color: #c62828; }
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
            <a href="manage-locations.php">Gestion des Lieux</a>
            <a href="manage-content.php">Gestion des Textes</a>
            <a href="manage-programs.php" class="active">Gestion des Programmes</a>
            <a href="manage-testimonials.php">Témoignages</a>
        </div>
    </div>
    <div class="main">
        <h1>Gestion des Programmes</h1>
        <?php if ($message): ?><div class="alert"><?= $message ?></div><?php endif; ?>

        <div class="card">
            <h3><?= $edit_prog ? 'Modifier le programme' : 'Ajouter un programme' ?></h3>
            <form method="POST">
                <?php if ($edit_prog): ?>
                    <input type="hidden" name="id" value="<?= $edit_prog['id'] ?>">
                <?php endif; ?>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label>Nom (FR)</label>
                        <input type="text" name="name_fr" required placeholder="Ex: U2-3 de 9h à 9h30" value="<?= $edit_prog ? htmlspecialchars($edit_prog['name_fr']) : '' ?>">
                    </div>
                    <div>
                        <label>Nom (EN)</label>
                        <input type="text" name="name_en" required placeholder="Ex: U2-3 from 9:00 to 9:30" value="<?= $edit_prog ? htmlspecialchars($edit_prog['name_en']) : '' ?>">
                    </div>
                </div>
                
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label>Localisation</label>
                        <select name="location_id" required>
                            <option value="">— Choisir un lieu —</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= $loc['id'] ?>" <?= ($edit_prog && $edit_prog['location_id'] == $loc['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($loc['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>ID Prix Stripe (price_...)</label>
                        <input type="text" name="price_id" required placeholder="price_1Oiam..." value="<?= $edit_prog ? htmlspecialchars($edit_prog['price_id_stripe']) : '' ?>">
                    </div>
                </div>

                <?php if ($edit_prog): ?>
                    <div style="margin-bottom: 1rem;">
                        <label>
                            <input type="checkbox" name="is_active" <?= $edit_prog['is_active'] ? 'checked' : '' ?>> 
                            Programme actif
                        </label>
                    </div>
                    <button type="submit" name="update" class="btn-update">Mettre à jour</button>
                    <a href="manage-programs.php" style="margin-left:10px; text-decoration:none; color:#666;">Annuler</a>
                <?php else: ?>
                    <button type="submit" name="add" class="btn-add">Ajouter le programme</button>
                <?php endif; ?>
            </form>
        </div>

        <div class="card" style="overflow-x: auto;">
            <h3>Liste des programmes</h3>
            <table>
                <thead>
                    <tr>
                        <th>Lieu</th>
                        <th>Nom (FR)</th>
                        <th>ID Stripe</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programs as $prog): ?>
                    <tr>
                        <td><span style="background:#eee; padding:2px 6px; border-radius:4px; font-size:0.8rem;"><?= htmlspecialchars($prog['location_name'] ?? 'N/A') ?></span></td>
                        <td><?= htmlspecialchars($prog['name_fr']) ?></td>
                        <td><code><?= htmlspecialchars($prog['price_id_stripe']) ?></code></td>
                        <td>
                            <span class="badge <?= $prog['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                <?= $prog['is_active'] ? 'Actif' : 'Inactif' ?>
                            </span>
                        </td>
                        <td>
                            <a href="?edit=<?= $prog['id'] ?>" class="btn-edit">Éditer</a>
                            <a href="?delete=<?= $prog['id'] ?>" class="btn-delete" onclick="return confirm('Supprimer ce programme ?')">Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
