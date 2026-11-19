<?php
/* ==========================================================================
   CONFIGURATION & CONNEXION SÉCURISÉE À LA BASE DE DONNÉES (PDO)
   ========================================================================== */
$host     = 'localhost';
$dbname   = 'ma_base';
$username = 'root';
$password = '';

$options = [
    // Active les exceptions en cas d'erreur SQL
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    // Récupère les résultats uniquement sous forme de tableau associatif
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // Utilise de vraies requêtes préparées natives MySQL (sécurité anti-injection SQL)
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch (PDOException $e) {
    // Enregistre l'erreur technique dans les logs du serveur sans l'exposer à l'utilisateur
    error_log("Erreur de connexion BDD : " . $e->getMessage());
    die("Une erreur technique est survenue. Veuillez réessayer plus tard.");
}
?>