<?php
/**
 * DB.PHP — Connexion à la base de données
 */

$config = require __DIR__ . '/../config.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4",
        $config['db_user'],
        $config['db_pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

/**
 * Récupère un contenu dynamique par sa clé
 */
function get_site_content($key) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT content_fr, content_en FROM site_content WHERE section_key = ?");
    $stmt->execute([$key]);
    return $stmt->fetch();
}

/**
 * Récupère les programmes actifs
 */
function get_active_programs() {
    global $pdo;
    $stmt = $pdo->query("SELECT id, name_fr, name_en, location_id FROM programs WHERE is_active = 1");
    return $stmt->fetchAll();
}

/**
 * Récupère les lieux actifs
 */
function get_active_locations() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM locations WHERE is_active = 1");
    return $stmt->fetchAll();
}
?>
