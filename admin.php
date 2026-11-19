<?php
/* ==========================================================================
   1. CONTRÔLE D'ACCÈS & CONNEXION BDD
   ========================================================================== */
session_start();
require_once 'connexion.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$message_flash = "";

/* ==========================================================================
   2. TRAITEMENT DES ACTIONS CRUD (UPDATE & DELETE)
   ========================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_recu = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrf_recu)) {
        die("Erreur de sécurité : jeton CSRF invalide.");
    }

    $id     = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';

    if ($id) {
        if ($action === 'basculer_statut') {
            $nouveau_statut = ($_POST['statut_actuel'] === 'nouveau') ? 'traite' : 'nouveau';
            $stmt = $pdo->prepare("UPDATE demandes_contact SET statut = :statut WHERE id = :id");
            $stmt->execute([':statut' => $nouveau_statut, ':id' => $id]);
            $message_flash = "✅ Statut de la demande mis à jour.";
        } elseif ($action === 'supprimer') {
            $stmt = $pdo->prepare("DELETE FROM demandes_contact WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $message_flash = "🗑️ La demande a bien été supprimée.";
        }
    }
}

/* ==========================================================================
   3. OPÉRATION READ (LECTURE DES DEMANDES)
   ========================================================================== */
$stmt = $pdo->query("SELECT * FROM demandes_contact ORDER BY date_envoi DESC");
$demandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Admin - SARL Corentin Bazoge</title>
    <link rel="stylesheet" href="asset/css/styles.css">
    <style>
        .admin-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem 0; flex-wrap: wrap; gap: 1rem; }
        .table-wrapper { overflow-x: auto; background: var(--card-bg); border-radius: 12px; box-shadow: 0 6px 18px rgba(44, 82, 38, 0.08); border: 1px solid #e2d9c5; }
        table { width: 100%; border-collapse: collapse; min-width: 750px; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #e2d9c5; }
        th { background-color: var(--primary-color); color: #ffffff; }
        .badge-nouveau { background-color: #fef3c7; color: #92400e; padding: 4px 10px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; }
        .badge-traite { background-color: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 20px; font-weight: bold; font-size: 0.85rem; }
        .btn-sm { padding: 6px 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.85rem; }
        .btn-status { background-color: var(--accent-color); color: var(--dark-color); }
        .btn-delete { background-color: #dc2626; color: #ffffff; }
        .actions-cell { display: flex; gap: 8px; }
    </style>
</head>
<body></body>
 <main class="container">

        <!-- ==================================================================
             4. BARRE SUPÉRIEURE (BIENVENUE & DÉCONNEXION)
             ================================================================== -->
        <div class="admin-header">
            <div>
                <h1 style="color: var(--primary-color);">🚜 Administration - Demandes de contact</h1>
                <p>Connecté en tant que <strong><?= htmlspecialchars($_SESSION['admin_nom']) ?></strong></p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="index.php" class="btn-secondary">Voir le site</a>
                <a href="logout.php" class="btn-primary" style="background-color: #dc2626;">Déconnexion</a>
            </div>
        </div>

        <!-- ==================================================================
             5. NOTIFICATION DE CONFIRMATION (MESSAGE FLASH)
             ================================================================== -->
        <?php if ($message_flash): ?>
            <p style="background: #e3ebde; color: var(--primary-color); padding: 1rem; border-radius: 8px; font-weight: bold; margin-bottom: 1.5rem;">
                <?= htmlspecialchars($message_flash) ?>
            </p>
        <?php endif; ?>

        <!-- ==================================================================
             6. TABLEAU D'AFFICHAGE DES DEMANDES (PROTECTION XSS)
             ================================================================== -->
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Contact</th>
                        <th>Message</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demandes)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">Aucune demande de contact pour le moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($demandes as $d): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($d['date_envoi'])) ?></td>
                                <td><strong><?= htmlspecialchars($d['nom']) ?></strong></td>
                                <td>
                                    📞 <a href="tel:<?= htmlspecialchars($d['telephone']) ?>"><?= htmlspecialchars($d['telephone'] ?: 'Non renseigné') ?></a><br>
                                    ✉️ <a href="mailto:<?= htmlspecialchars($d['email']) ?>"><?= htmlspecialchars($d['email']) ?></a>
                                </td>
                                <td style="max-width: 300px;"><?= nl2br(htmlspecialchars($d['message'])) ?></td>
                                <td>
                                    <?php if ($d['statut'] === 'nouveau'): ?>
                                        <span class="badge-nouveau">🟠 À traiter</span>
                                    <?php else: ?>
                                        <span class="badge-traite">🟢 Traité</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions-cell">
                                    <!-- Formulaire UPDATE -->
                                    <form method="post">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                                        <input type="hidden" name="statut_actuel" value="<?= htmlspecialchars($d['statut']) ?>">
                                        <input type="hidden" name="action" value="basculer_statut">
                                        <button type="submit" class="btn-sm btn-status">
                                            <?= $d['statut'] === 'nouveau' ? 'Marquer traité' : 'Remettre à traiter' ?>
                                        </button>
                                    </form>

                                    <!-- Formulaire DELETE -->
                                    <form method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer cette demande ?');">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
                                        <input type="hidden" name="action" value="supprimer">
                                        <button type="submit" class="btn-sm btn-delete">Supprimer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>
