<?php
/* ==========================================================================
   1. INITIALISATION DE LA SESSION & CONNEXION BDD
   ========================================================================== */
session_start();
require_once 'connexion.php';

// Redirection immédiate si l'administrateur est déjà connecté
if (isset($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}

$erreur = "";

/* ==========================================================================
   2. TRAITEMENT DU FORMULAIRE DE CONNEXION
   ========================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant  = trim($_POST['identifiant'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    if (empty($identifiant) || empty($mot_de_passe)) {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        // Requête préparée PDO (protection contre les injections SQL)
        $stmt = $pdo->prepare("SELECT id, identifiant, mot_de_passe FROM utilisateurs WHERE identifiant = :identifiant");
        $stmt->execute([':identifiant' => $identifiant]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification du mot de passe haché (algorithme Bcrypt)
        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            // Régénération de l'ID de session (protection contre la fixation de session)
            session_regenerate_id(true);
            
            $_SESSION['admin_id']   = $user['id'];
            $_SESSION['admin_nom']  = $user['identifiant'];
            // Création d'un jeton CSRF unique pour sécuriser les futures actions du CRUD
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            header('Location: admin.php');
            exit;
        } else {
            // Message volontairement générique (ne révèle pas si l'identifiant existe)
            $erreur = "Identifiant ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<!-- ==========================================================================
     3. EN-TÊTE HTML & FEUILLE DE STYLE
     ========================================================================== -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - SARL Corentin Bazoge</title>
    <link rel="stylesheet" href="asset/css/styles.css">
</head>

<body>

    <!-- ======================================================================
         4. FORMULAIRE DE CONNEXION ADMINISTRATEUR
         ====================================================================== -->
    <main class="container" style="max-width: 450px; margin: 5rem auto;">
        <form method="post" class="contact-form">
            <h1 style="text-align: center; color: var(--primary-color); margin-bottom: 1.5rem;">🔒 Espace Admin</h1>
            
            <!-- Affichage des erreurs d'authentification (protégé contre XSS) -->
            <?php if ($erreur): ?>
                <p style="color: #dc2626; font-weight: 600; margin-bottom: 1rem; text-align: center;">
                    <?= htmlspecialchars($erreur) ?>
                </p>
            <?php endif; ?>

            <div class="form-group">
                <label for="identifiant">Identifiant :</label>
                <input type="text" id="identifiant" name="identifiant" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Mot de passe :</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" required autocomplete="current-password">
            </div>

            <button type="submit" class="btn-primary btn-submit">Se connecter</button>
            
            <p style="text-align: center; margin-top: 1rem;">
                <a href="index.php" style="color: var(--gray-color); font-size: 0.9rem;">← Retour au site</a>
            </p>
        </form>
    </main>

</body>
</html>