<?php
session_start();
require_once '../config/database.php';

$erreur = '';
$success = '';

// Générer un token CSRF s'il n'existe pas
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Échec CSRF, action interdite');
    }

    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $date_naissance = $_POST['date_naissance'];
    $adresse = htmlspecialchars(trim($_POST['adresse']));
    $telephone = htmlspecialchars(trim($_POST['telephone']));
    $email = htmlspecialchars(trim($_POST['email']));
    $mot_de_passe = password_hash($_POST['mot_de_passe'], PASSWORD_DEFAULT);
    $token_activation = bin2hex(random_bytes(16)); // Génère un token unique
    $actif = 0;

    // Vérifier l'unicité de l'email
    $stmt = $pdo->prepare('SELECT id_utilisateur FROM Utilisateur WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $erreur = 'Cet email est déjà utilisé.';
    } else {
        // Insérer l'utilisateur
        $stmt = $pdo->prepare('INSERT INTO Utilisateur (nom, prénom, date_naissance, adresse, téléphone, email, mot_de_passe, token_activation, actif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$nom, $prenom, $date_naissance, $adresse, $telephone, $email, $mot_de_passe, $token_activation, $actif])) {
            $success = 'Inscription réussie. Consultez votre email pour activer votre compte.';
            $sujet = 'Activation de votre compte';
            $message = "Bonjour $prenom $nom,\n\nMerci pour votre inscription. Cliquez sur le lien ci-dessous pour activer votre compte :\n\nhttp://localhost/reservation_system/public/activation.php?token=$token_activation\n\nCordialement,\nL'équipe de Réservation";
            $headers = 'From: no-reply@reservation-system.com';

            if (!mail($email, $sujet, $message, $headers)) {
                $erreur = 'Erreur lors de l\'envoi de l\'email.';
                $success = '';
            }
        } else {
            $erreur = 'Erreur lors de l\'inscription.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/style.css">

</head>
<body class="container mt-5">
    <h1>Inscription</h1>

    <?php if ($erreur): ?>
        <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="mb-3">
            <label for="nom" class="form-label">Nom</label>
            <input type="text" class="form-control" id="nom" name="nom" required>
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" required>
        </div>
        <div class="mb-3">
            <label for="date_naissance" class="form-label">Date de naissance</label>
            <input type="date" class="form-control" id="date_naissance" name="date_naissance" required>
        </div>
        <div class="mb-3">
            <label for="adresse" class="form-label">Adresse</label>
            <input type="text" class="form-control" id="adresse" name="adresse">
        </div>
        <div class="mb-3">
            <label for="telephone" class="form-label">Téléphone</label>
            <input type="text" class="form-control" id="telephone" name="telephone">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="mot_de_passe" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
        </div>

        <button type="submit" class="btn btn-primary">S'inscrire</button>
    </form>
</body>
</html>
