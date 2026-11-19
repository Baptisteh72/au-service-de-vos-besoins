<?php
// Fichier modèle : renommer en connexion.php et renseigner vos identifiants
$host     = 'localhost';
$dbname   = 'ma_base';
$username = 'root';
$password = '';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, $options);
} catch (PDOException $e) {
    error_log("Erreur de connexion BDD : " . $e->getMessage());
    die("Une erreur technique est survenue.");
}
?>