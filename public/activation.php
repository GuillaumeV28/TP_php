<?php
require_once '../config/database.php';

$erreur = '';
$success = '';

// Vérifier si on a bien un token dans l'URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Vérifier si le token existe en base et que le compte n'est pas déjà activé
    $stmt = $pdo->prepare('SELECT id_utilisateur FROM Utilisateur WHERE token_activation = ? AND actif = 0');
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // Activer le compte
        $stmt = $pdo->prepare('UPDATE Utilisateur SET actif = 1, token_activation = NULL WHERE id_utilisateur = ?');
        $stmt->execute([$user['id_utilisateur']]);
        $success = 'Votre compte a été activé avec succès ! Vous pouvez maintenant vous connecter.';
    } else {
        $erreur = 'Token invalide ou compte déjà activé.';
    }
} else {
    $erreur = 'Token manquant.';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Activation de compte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h1>Activation de votre compte</h1>

    <?php if ($erreur): ?>
        <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <a href="connexion.php" class="btn btn-primary">Se connecter</a>
</body>
</html>
