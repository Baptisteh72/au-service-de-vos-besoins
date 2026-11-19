<?php
/* ==========================================================================
   1. RÉCUPÉRATION & DESTRUCTION DE LA SESSION ADMINISTRATEUR
   ========================================================================== */
session_start();

// Vide toutes les variables de session en mémoire
$_SESSION = [];

// Détruit la session côté serveur
session_destroy();

/* ==========================================================================
   2. REDIRECTION VERS LA PAGE DE CONNEXION
   ========================================================================== */
header('Location: login.php');
exit;
?>