<?php
session_start();
require_once '../config/database.php';

// ✅ Assurer que la session contient un token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Charger PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$erreur = '';
$success = '';

// ✅ Initialisation des champs (évite les erreurs si le formulaire échoue)
$nom = $prenom = $date_naissance = $adresse = $telephone = $email = '';

// 🚀 Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Vérification du token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Échec CSRF, action interdite'); 
    }

    // 🔹 Nettoyage et validation des champs
    $nom = htmlspecialchars(trim($_POST['nom'] ?? ''));
    $prenom = htmlspecialchars(trim($_POST['prenom'] ?? ''));
    $date_naissance = $_POST['date_naissance'] ?? '';
    $adresse = htmlspecialchars(trim($_POST['adresse'] ?? ''));
    $telephone = htmlspecialchars(trim($_POST['telephone'] ?? ''));
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $mot_de_passe = password_hash($_POST['mot_de_passe'] ?? '', PASSWORD_DEFAULT);
    $token_activation = bin2hex(random_bytes(16));
    $actif = 0;

    // ✅ Vérifier si l'email est valide
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Adresse email invalide.';
    } elseif (empty($adresse) || empty($telephone)) {
        $erreur = 'Tous les champs sont obligatoires.';
    } else {
        // ✅ Vérifier si l'email existe déjà
        $stmt = $pdo->prepare('SELECT id_utilisateur FROM Utilisateur WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erreur = 'Cet email est déjà utilisé.';
        } else {
            // ✅ Insérer l'utilisateur
            $stmt = $pdo->prepare('INSERT INTO Utilisateur (nom, prénom, date_naissance, adresse, téléphone, email, mot_de_passe, token_activation, actif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            if ($stmt->execute([$nom, $prenom, $date_naissance, $adresse, $telephone, $email, $mot_de_passe, $token_activation, $actif])) {
                
                // ✅ Envoi de l'email avec PHPMailer
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'voisine.guillaume@gmail.com'; // Remplace par ton adresse Gmail
                    $mail->Password = 'vhva lsyv jqaa vyqf';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Destinataire
                    $mail->setFrom('tonemail@gmail.com', 'Réservation System');
                    $mail->addAddress($email, "$prenom $nom");

                    // Contenu de l'email
                    $mail->isHTML(true);
                    $mail->Subject = 'Activation de votre compte';
                    $mail->Body = "Bonjour $prenom $nom,<br><br>
                    Merci pour votre inscription. Cliquez sur le lien ci-dessous pour activer votre compte :<br>
                    <a href='http://localhost/reservation_system/public/activation.php?token=$token_activation'>Activer mon compte</a><br><br>
                    Cordialement,<br>L'équipe de Réservation";

                    // Envoi de l'email
                    $mail->send();
                    $success = 'Inscription réussie. Consultez votre email pour activer votre compte.';
                } catch (Exception $e) {
                    $erreur = 'Erreur lors de l\'envoi de l\'email : ' . $mail->ErrorInfo;
                }
            } else {
                $erreur = 'Erreur lors de l\'inscription.';
            }
        }
    }

    // ✅ Régénérer le token CSRF après soumission pour éviter une attaque répétée
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.css">

</head>
<body class="container mt-5">
    <h1>Inscription</h1>

    <?php if (!empty($erreur)): ?>
        <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <!-- ✅ CSRF Token caché -->
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($nom) ?>" required>
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($prenom) ?>" required>
        </div>
        <div class="mb-3">
            <label for="date_naissance" class="form-label">Date de naissance</label>
            <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?= $date_naissance ?>" required>
        </div>
        <div class="mb-3">
            <label for="adresse" class="form-label">Adresse postale</label>
            <input type="text" class="form-control" id="adresse" name="adresse" value="<?= htmlspecialchars($adresse) ?>" required>
        </div>
        <div class="mb-3">
            <label for="telephone" class="form-label">Numéro de téléphone</label>
            <input type="text" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($telephone) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="mb-3">
            <label for="mot_de_passe" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
        </div>

        <button type="submit" class="btn btn-primary">S'inscrire</button>
    </form>

    <hr>
    <!-- ✅ Bouton "Déjà un compte? Connecte-toi" -->
    <p class="text-center">Déjà un compte ? <a href="connexion.php" class="btn btn-outline-secondary">Connecte-toi</a></p>

</body>
</html>

