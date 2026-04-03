<?php
/**
 * LOGOUT.PHP — Déconnexion de l'admin
 */
session_start();
session_unset();
session_destroy();
header('Location: login.php');
exit;
?>
