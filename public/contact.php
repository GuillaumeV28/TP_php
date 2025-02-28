<?php
session_start();

$erreur = '';
$success = '';

// Générer un token CSRF s'il n'existe pas encore
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Échec CSRF, action interdite');
    }

    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));

    // Validation rapide
    if (empty($nom) || empty($email) || empty($message)) {
        $erreur = 'Tous les champs sont obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'Email invalide.';
    } else {
        // Envoi de l'email (à adapter si besoin)
        $sujet = 'Nouveau message depuis le formulaire de contact';
        $contenu = "Nom : $nom\nEmail : $email\n\nMessage :\n$message";
        $headers = 'From: ' . $email;

        // On simule l'envoi réel pour le projet
        if (mail('contact@reservation-system.com', $sujet, $contenu, $headers)) {
            $success = 'Votre message a bien été envoyé.';
        } else {
            $erreur = 'Erreur lors de l\'envoi du message.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contact</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.css">

</head>
<body class="container mt-5">
    <h1>Contactez-nous</h1>

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
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>

    <hr>
    <a href="profil.php" class="btn btn-secondary">Retour au profil</a>
</body>
</html>
