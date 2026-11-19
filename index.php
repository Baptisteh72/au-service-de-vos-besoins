<?php
/* ==========================================================================
   1. IMPORTATION DES BIBLIOTHÈQUES & CONNEXION BDD
   ========================================================================== */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'connexion.php';
require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';

$message_statut = "";

/* ==========================================================================
   2. TRAITEMENT DU FORMULAIRE, INSERTION BDD & NOTIFICATION EMAIL
   ========================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données du formulaire
    $nom       = trim($_POST['nom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $message   = trim($_POST['message'] ?? '');
    $rgpd      = isset($_POST['rgpd']);

    // Validation des champs obligatoires, du format email et du consentement RGPD
    if (empty($nom) || empty($email) || empty($message)) {
        $message_statut = "<p style='color: #d97706; font-weight: 600; margin-bottom: 1rem;'>⚠️ Veuillez remplir tous les champs obligatoires.</p>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message_statut = "<p style='color: #d97706; font-weight: 600; margin-bottom: 1rem;'>⚠️ L'adresse email saisie n'est pas valide.</p>";
    } elseif (!$rgpd) {
        $message_statut = "<p style='color: #d97706; font-weight: 600; margin-bottom: 1rem;'>⚠️ Veuillez accepter la politique de confidentialité (RGPD).</p>";
    } else {
        try {
            // 1. Requête préparée PDO (protection contre les injections SQL)
            $sql = "INSERT INTO demandes_contact (nom, telephone, email, message) 
                    VALUES (:nom, :telephone, :email, :message)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nom'       => $nom,
                ':telephone' => $telephone,
                ':email'     => $email,
                ':message'   => $message
            ]);

            // 2. Envoi de la notification par email à l'entrepreneur (SMTP Gmail)
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = '://gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'bazogecorentin@gmail.com';
                $mail->Password   = 'xxxx xxxx xxxx xxxx'; // <-- REMPLACE PAR TON MOT DE PASSE D'APPLICATION GOOGLE
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;
                $mail->CharSet    = 'UTF-8';

                $mail->setFrom('bazogecorentin@gmail.com', 'Site Web - Au Service de Vos Besoins');
                $mail->addAddress('bazogecorentin@gmail.com');
                $mail->addReplyTo($email, $nom);

                $mail->isHTML(false);
                $mail->Subject = "Nouvelle demande de contact de $nom";
                $mail->Body    = "Nouvelle demande reçue depuis le site :\n\n"
                               . "Nom / Entreprise : $nom\n"
                               . "Téléphone : " . ($telephone ?: 'Non renseigné') . "\n"
                               . "Email : $email\n\n"
                               . "Message :\n$message";

                $mail->send();
            } catch (Exception $e) {
                // Si l'email échoue (ex: hors ligne), on enregistre l'erreur sans bloquer la BDD
                error_log("Erreur PHPMailer : " . $mail->ErrorInfo);
            }

            $message_statut = "<p style='color: #2c5226; font-weight: bold; margin-bottom: 1rem;'>✅ Votre demande a bien été envoyée ! Je vous recontacterai rapidement.</p>";
        } catch (PDOException $e) {
            error_log("Erreur PDO : " . $e->getMessage());
            $message_statut = "<p style='color: #dc2626; font-weight: 600; margin-bottom: 1rem;'>❌ Une erreur technique est survenue lors de l'envoi. Veuillez réessayer.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<!-- ==========================================================================
     2. EN-TÊTE TECHNIQUE & SEO (HEAD)
     ========================================================================== -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SARL Corentin Bazoge - Salarié agricole, nettoyage haute pression de poulaillers et bâtiments agricoles, entretien d'espaces verts en Sarthe (72).">
    <title>Au Service de Vos Besoins - Agriculture, Nettoyage & Entretien (Sarthe 72)</title>
    
    <!-- Feuilles de style -->
    <link rel="stylesheet" href="asset/css/styles.css">
</head>

<body>

    <!-- ======================================================================
         3. EN-TÊTE DU SITE (HEADER) & NAVIGATION RESPONSIVE
         ====================================================================== -->
    <header>
        <div class="container nav-container">
            
            <!-- Logo & Titre de l'entreprise -->
            <a href="#" class="logo">
                <img src="img/IMG_0013.jpeg" alt="Logo SARL Corentin Bazoge" class="logo-img">
                <div class="logo-text-group">
                    <span class="logo-title"><span class="tracteur-anime">🚜</span> Au Service de Vos Besoins</span>
                    <span class="logo-subtitle">SARL Corentin Bazoge • Sarthe (72)</span>
                </div>
            </a>

            <!-- Zone d'actions & Menu -->
            <div class="header-actions">
                
                <!-- Actions visibles uniquement sur ordinateur (>= 900px) -->
                <div class="desktop-only">
                    <span class="header-dispo">🟢 Disponible & Réactif</span>
                    <a href="tel:0786550696" class="btn-call">
                        <svg class="phone-icon" xmlns="http://w3.org" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M6.62 10.79a15.053 15.053 0 0 0 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                        </svg>
                        <span>07 86 55 06 96</span>
                    </a>
                </div>

                <!-- Menu déroulant -->
                <div class="floating-nav">
                    <button class="floating-btn" id="floatingBtn" aria-label="Ouvrir le menu de navigation" aria-expanded="false">
                        <span class="btn-icon">☰</span>
                        <span class="btn-text">Menu</span>
                    </button>

                    <nav class="floating-menu" id="floatingMenu" aria-label="Navigation principale">
                        <a href="#accueil" class="floating-link">🏠 Accueil</a>
                        <a href="#produits" class="floating-link">🚜 Services</a>
                        <a href="#apropos" class="floating-link">👨‍🌾 À Propos</a>
                        <a href="#formulaire-contact" class="floating-link link-contact">✉️ Contact / Devis</a>

                        <!-- Actions basculées dans le menu sur mobile (< 900px) -->
                        <div class="mobile-only">
                            <span class="header-dispo">🟢 Disponible & Réactif</span>
                            <a href="tel:0786550696" class="btn-call">
                                <svg class="phone-icon" xmlns="http://w3.org" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M6.62 10.79a15.053 15.053 0 0 0 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                                </svg>
                                <span>07 86 55 06 96</span>
                            </a>
                        </div>
                    </nav>
                </div>

            </div>
        </div>
    </header>

    <!-- ======================================================================
         CONTENU PRINCIPAL DE LA PAGE (MAIN)
         ====================================================================== -->
    <main>

        <!-- ==================================================================
             4. SECTION HÉRO & CARROUSEL DE PRÉSENTATION
             ================================================================== -->
        <section id="accueil" class="hero-section">
            <div class="container hero-flex">
                <div class="hero-text">
                    <span class="badge-secteur">Sarthe (72) & alentours</span>
                    <h1>Agriculture, Nettoyages, Entretiens</h1>
                    <p>Je réponds à vos besoins avec sérieux, efficacité et réactivité pour tous vos travaux agricoles et d'entretien.</p>
                    <div class="hero-buttons">
                        <a href="#produits" class="btn-primary">Découvrir mes services</a>
                        <a href="#formulaire-contact" class="btn-secondary">Demander un devis</a>
                    </div>
                </div>

                <!-- Carrousel d'images -->
                <div class="header-slider">
                    <div class="slider-track" id="track">
                        <img src="img/4d03091f-1780-4562-8bb7-b3486e60a8fd.jpg" alt="Nettoyage de bâtiment agricole">
                        <img src="img/ff533f53-65a9-4104-80db-9fca01855bd1.jpg" alt="Travaux agricoles et matériel">
                        <img src="img/images (2).jpg" alt="Entretien d'espaces verts">
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================================================================
             5. SECTION NOS SERVICES (GRILLE DE PRESTATIONS)
             ================================================================== -->
        <section id="produits" class="products">
            <div class="container">
                <h2>Nos Services</h2>
                <p class="section-subtitle">Des prestations adaptées aux exploitations agricoles, professionnels et particuliers</p>
                
                <div class="grid">
                    <!-- Carte 1 : Salarié Agricole -->
                    <article class="card">
                        <img src="img/tractor-5414037_1280.jpg" alt="Tracteur dans un champ - Salarié Agricole">
                        <div class="card-body">
                            <h3>Salarié Agricole</h3>
                            <p>Accompagnement, conduite d'engins et renfort polyvalent pour vos travaux agricoles saisonniers ou réguliers.</p>
                        </div>
                    </article>

                    <!-- Carte 2 : Nettoyage de bâtiments -->
                    <article class="card">
                        <img src="img/4d03091f-1780-4562-8bb7-b3486e60a8fd.jpg" alt="Nettoyage haute pression de bâtiment agricole">
                        <div class="card-body">
                            <h3>Nettoyage de vos bâtiments</h3>
                            <p>Nettoyage haute pression, lavage et désinfection rigoureuse de vos poulaillers et bâtiments agricoles.</p>
                        </div>
                        </article>

                    <!-- Carte 3 : Entretien d'espaces verts -->
                    <article class="card">
                        <img src="img/jardinier-taille-haie-1536x927.jpg" alt="Taille de haie - Entretien d'espaces verts">
                        <div class="card-body">
                            <h3>Entretien d'espaces verts</h3>
                            <p>Tonte, débroussaillage, taille de haies et entretien complet de vos abords et extérieurs.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- ==================================================================
             6. SECTION À PROPOS & GALERIE INTERACTIVE (LOUPE + LIGHTBOX)
             ================================================================== -->
        <section id="apropos" class="about">
            <div class="container about-grid">
                <div class="about-text">
                    <h2>Une passion pour l'agriculture et l'entretien de vos poulaillers</h2>
                    <p>Je suis un jeune entrepreneur doté de solides compétences dans le domaine de l'agriculture. Rigoureux et équipé, je saurai être à l'écoute de toutes vos exigences pour vous garantir un travail soigné.</p>
                    <ul class="about-list">
                        <li>✔ Intervention rapide et flexible</li>
                        <li>✔ Travail soigné et respect des normes d'hygiène</li>
                        <li>✔ À l'écoute des besoins de chaque exploitation</li>
                    </ul>
                </div>

                <div class="about-img">
                    <div class="zoom-container" id="zoomContainer">
                        <img src="img/IMG_0012.jpeg" alt="Flyer de présentation SARL Corentin Bazoge" class="about-main-photo" id="zoomImg">
                        <div class="loupe-lens" id="loupeLens"></div>
                        <span class="zoom-hint">🔍 Survolez ou cliquez pour agrandir</span>
                    </div>

                    <img src="img/1744411d-245c-4291-b9b4-d89781ec10d1.jpg" alt="Portrait Corentin Bazoge" class="logo-coin">
                </div>
            </div>
        </section>

        <!-- ==================================================================
             7. SECTION CONTACT & FORMULAIRE (AVEC VALIDATION RGPD)
             ================================================================== -->
        <section id="formulaire-contact" class="contact-section">
            <div class="container">
                <h2>Contactez-moi</h2>
                <p class="section-subtitle">Une question ou un besoin d'intervention ? Envoyez-moi un message ci-dessous.</p>
                
                <form action="#formulaire-contact" method="post" class="contact-form">
                    <!-- Décoration brin de maïs -->
                    <div class="corn-decor" aria-hidden="true">
                        <svg width="65" height="85" viewBox="0 0 100 130" fill="none" xmlns="http://w3.org">
                            <path d="M50 125V75" stroke="#2c5226" stroke-width="5" stroke-linecap="round"/>
                            <path d="M50 105C30 98 15 75 22 50C35 65 45 85 50 105Z" fill="#4f793e"/>
                            <path d="M50 100C70 93 85 70 78 45C65 60 55 80 50 100Z" fill="#2c5226"/>
                            <ellipse cx="50" cy="52" rx="14" ry="30" fill="#d99b38"/>
                            <path d="M43 32V72M50 25V79M57 32V72" stroke="#f3dfb9" stroke-width="2" stroke-linecap="round" stroke-dasharray="3 5"/>
                            <path d="M38 42H62M37 52H63M38 62H62" stroke="#b87d22" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </div>

                    <!-- Ligne 1 : Nom & Téléphone -->
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom / Entreprise :</label>
                            <input type="text" id="nom" name="nom" placeholder="Votre nom" required>
                        </div>
                        <div class="form-group">
                            <label for="telephone">Téléphone :</label>
                            <input type="tel" id="telephone" name="telephone" placeholder="06 XX XX XX XX">
                        </div>
                    </div>

                    <!-- Ligne 2 : Email -->
                    <div class="form-group">
                        <label for="email">Email :</label>
                        <input type="email" id="email" name="email" placeholder="votre.email@exemple.fr" required>
                    </div>

                    <!-- Ligne 3 : Message -->
                    <div class="form-group">
                        <label for="message">Votre besoin / Message :</label>
                        <textarea id="message" name="message" rows="5" placeholder="Décrivez votre besoin (type de bâtiment, surface, dates souhaitées...)" required></textarea>
                    </div>

                    <!-- Case de consentement RGPD obligatoire -->
                    <div class="form-group-rgpd">
                        <input type="checkbox" id="rgpd" name="rgpd" required>
                        <label for="rgpd">
                            J'accepte que mes informations soient utilisées pour me recontacter dans le cadre de ma demande. 
                            <a href="mentions-legales.html" target="_blank" rel="noopener">En savoir plus</a>.
                        </label>
                    </div>

                    <!-- Affichage du message de confirmation ou d'erreur PHP -->
                    <?= $message_statut ?>

                    <button type="submit" class="btn-primary btn-submit">Envoyer ma demande</button>
                </form>
            </div>
        </section>

    </main>

    <!-- ======================================================================
         8. PIED DE PAGE (FOOTER)
         ====================================================================== -->
    <footer id="contact">
        <div class="container footer-content">
            <p class="footer-brand">🚜 Au Service de Vos Besoins</p>
            <p>
                 Adresse : La Sinelière, 72510 Saint-Jean-de-la-Motte | 
                ✉️ Mail : <a href="mailto:bazogecorentin@gmail.com" style="color: inherit;">bazogecorentin@gmail.com</a> | 
                📞 Tél : <a href="tel:0786550696" style="color: inherit;">07 86 55 06 96</a>
            </p>
            <p class="footer-copy">
                &copy; 2026 Au Service de Vos Besoins - Tous droits réservés | 
                <a href="login.php" style="color: inherit; text-decoration: none; opacity: 0.7;" title="Accès administration">🔒</a>
                <a href="mentions-legales.html" class="footer-legal-link">Mentions Légales & Politique de Confidentialité</a>
            </p>
        </div>
    </footer>

    <!-- ======================================================================
         9. FENÊTRE MODALE LIGHTBOX & SCRIPTS JAVASCRIPT
         ====================================================================== -->
    <div class="lightbox-modal" id="lightboxModal" role="dialog" aria-modal="true" aria-label="Aperçu agrandi du flyer">
        <span class="lightbox-close" id="lightboxClose" aria-label="Fermer l'aperçu">&times;</span>
        <img class="lightbox-content" id="lightboxImg" alt="Aperçu agrandi du flyer">
    </div>

     <script src="asset/js/script.js"></script>

</body>
</html>