<?php
/**
 * AUTH.PHP — Gestion de l'authentification
 */

session_start();

/**
 * Vérifie si l'utilisateur est connecté
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']);
}

/**
 * Redirige vers le login si non connecté
 */
function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
?>
