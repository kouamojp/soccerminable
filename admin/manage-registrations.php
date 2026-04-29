<?php
/**
 * MANAGE-REGISTRATIONS.PHP — Gestion des inscriptions
 */
require_once 'auth.php';
require_once 'db.php';
require_admin_login();

// Suppression d'une inscription
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: manage-registrations.php?msg=deleted");
    exit;
}

// Mise à jour du statut de paiement
if (isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE registrations SET payment_status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['id']]);
    header("Location: manage-registrations.php?msg=updated");
    exit;
}

// Récupération des inscriptions
$stmt = $pdo->query("SELECT * FROM registrations ORDER BY created_at DESC");
$registrations = $stmt->fetchAll();

// Récupération de l'email de notification
$stmt = $pdo->prepare("SELECT content_fr FROM site_content WHERE section_key = 'notification_email'");
$stmt->execute();
$notification_email = $stmt->fetchColumn();

// Mise à jour de l'email de notification
if (isset($_POST['update_email'])) {
    $stmt = $pdo->prepare("UPDATE site_content SET content_fr = ?, content_en = ? WHERE section_key = 'notification_email'");
    $stmt->execute([$_POST['email'], $_POST['email']]);
    header("Location: manage-registrations.php?msg=email_updated");
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscriptions | SoccerMidable Admin</title>
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
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; font-size: 0.9rem; }
        th { background: #f8f9fa; font-weight: 700; color: #555; }
        .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: 700; cursor: pointer; border: none; }
        .btn-delete { background: #f44336; color: white; }
        .btn-save { background: var(--purple); color: white; }
        .form-inline { display: flex; gap: 10px; align-items: center; }
        .input-text { padding: 8px; border: 1px solid #ddd; border-radius: 4px; flex: 1; }
        .alert { padding: 1rem; border-radius: 5px; margin-bottom: 1rem; font-weight: 700; }
        .alert-success { background: #d4edda; color: #155724; }
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
            <a href="manage-registrations.php" class="active">Inscriptions</a>
            <a href="manage-content.php">Gestion des Textes</a>
            <a href="manage-programs.php">Gestion des Programmes</a>
            <a href="manage-testimonials.php">Témoignages</a>
        </div>
    </div>

    <div class="main">
        <div class="header">
            <h1>Gestion des Inscriptions</h1>
            <a href="logout.php" style="color:#f44336; text-decoration:none; font-weight:700;">Déconnexion</a>
        </div>

        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <?php
                if ($_GET['msg'] == 'deleted') echo "Inscription supprimée avec succès.";
                if ($_GET['msg'] == 'updated') echo "Statut mis à jour.";
                if ($_GET['msg'] == 'email_updated') echo "Email de notification mis à jour.";
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Configuration des notifications</h2>
            <form method="POST" class="form-inline">
                <label>Email de réception :</label>
                <input type="email" name="email" value="<?= htmlspecialchars($notification_email) ?>" class="input-text" required>
                <button type="submit" name="update_email" class="btn btn-save">Enregistrer</button>
            </form>
            <p style="font-size: 0.8rem; color: #666; margin-top: 0.5rem;">
                C'est à cette adresse que seront envoyés les récapitulatifs d'inscription lors de la soumission du formulaire.
            </p>
        </div>

        <div class="card" style="overflow-x: auto;">
            <h2>Liste des inscriptions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Parent(s)</th>
                        <th>Enfant</th>
                        <th>Programme / Lieu</th>
                        <th>Contact</th>
                        <th>Paiement</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrations as $reg): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($reg['created_at'])) ?></td>
                            <td>
                                <strong><?= htmlspecialchars($reg['parent_name_1']) ?></strong><br>
                                <small><?= htmlspecialchars($reg['parent_name_2']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($reg['child_name']) ?><br>
                                <small>Né(e) le <?= htmlspecialchars($reg['child_dob']) ?></small>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($reg['program']) ?></strong><br>
                                <small><?= htmlspecialchars($reg['location']) ?></small>
                            </td>
                            <td>
                                <?= htmlspecialchars($reg['email']) ?><br>
                                <?= htmlspecialchars($reg['phone']) ?>
                            </td>
                            <td>
                                <form method="POST" style="display:flex; gap:5px;">
                                    <input type="hidden" name="id" value="<?= $reg['id'] ?>">
                                    <select name="status" style="font-size:0.7rem;">
                                        <option value="pending" <?= $reg['payment_status'] == 'pending' ? 'selected' : '' ?>>En attente</option>
                                        <option value="paid" <?= $reg['payment_status'] == 'paid' ? 'selected' : '' ?>>Payé</option>
                                        <option value="cancelled" <?= $reg['payment_status'] == 'cancelled' ? 'selected' : '' ?>>Annulé</option>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-save" style="padding:2px 5px;">ok</button>
                                </form>
                            </td>
                            <td>
                                <a href="?delete=<?= $reg['id'] ?>" class="btn btn-delete" onclick="return confirm('Supprimer cette inscription ?')">Supprimer</a>
                            </td>
                        </tr>
                        <?php if ($reg['message']): ?>
                        <tr>
                            <td colspan="7" style="background:#fcfcfc; font-size:0.8rem; color:#666;">
                                <strong>Message :</strong> <?= nl2br(htmlspecialchars($reg['message'])) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if (empty($registrations)): ?>
                        <tr><td colspan="7" style="text-align:center;">Aucune inscription pour le moment.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
