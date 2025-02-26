<?php
session_start();
require_once '../config/database.php';

$erreur = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $mot_de_passe = $_POST['mot_de_passe'];

    // Vérifier si l'utilisateur existe et est activé
    $stmt = $pdo->prepare('SELECT id_utilisateur, nom, prénom, mot_de_passe FROM Utilisateur WHERE email = ? AND actif = 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
        // Connexion réussie
        $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prénom'];
        header('Location: profil.php');
        exit;
    } else {
        $erreur = 'Email ou mot de passe incorrect, ou compte non activé.';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/style.css">

</head>
<body class="container mt-5">
    <h1>Connexion</h1>

    <?php if ($erreur): ?>
        <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="mot_de_passe" class="form-label">Mot de passe</label>
            <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" required>
        </div>

        <button type="submit" class="btn btn-primary">Se connecter</button>
    </form>
</body>
</html>
