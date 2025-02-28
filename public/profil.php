<?php
session_start();
require_once '../includes/header.php';
require_once '../config/database.php';

$prenom = $_SESSION['prenom'] ?? 'Utilisateur';
$nom = $_SESSION['nom'] ?? '';


// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.php');
    exit;
}

// Générer un token CSRF s'il n'existe pas
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Récupérer les informations actuelles de l'utilisateur
$stmt = $pdo->prepare('SELECT * FROM Utilisateur WHERE id_utilisateur = ?');
$stmt->execute([$_SESSION['id_utilisateur']]);
$user = $stmt->fetch();

// Si l'utilisateur n'existe plus en base
if (!$user) {
    session_destroy();
    header('Location: connexion.php');
    exit;
}

$erreur = '';
$success = '';

// Mise à jour des informations personnelles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['supprimer_compte'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Échec CSRF, action interdite');
    }

    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $date_naissance = $_POST['date_naissance'];
    $adresse = htmlspecialchars(trim($_POST['adresse']));
    $telephone = htmlspecialchars(trim($_POST['telephone']));
    $email = htmlspecialchars(trim($_POST['email']));

    // Vérifier si l'email est unique (sauf si c'est le même)
    if ($email !== $user['email']) {
        $stmt = $pdo->prepare('SELECT id_utilisateur FROM Utilisateur WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erreur = 'Cet email est déjà utilisé.';
        }
    }

    if (!$erreur) {
        $stmt = $pdo->prepare('UPDATE Utilisateur SET nom = ?, prénom = ?, date_naissance = ?, adresse = ?, téléphone = ?, email = ? WHERE id_utilisateur = ?');
        if ($stmt->execute([$nom, $prenom, $date_naissance, $adresse, $telephone, $email, $_SESSION['id_utilisateur']])) {
            $success = 'Informations mises à jour avec succès.';
            $_SESSION['nom'] = $nom;
            $_SESSION['prenom'] = $prenom;
        } else {
            $erreur = 'Erreur lors de la mise à jour.';
        }
    }
}

// Suppression du compte
if (isset($_POST['supprimer_compte'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Échec CSRF, action interdite');
    }

    $stmt = $pdo->prepare('DELETE FROM Utilisateur WHERE id_utilisateur = ?');
    $stmt->execute([$_SESSION['id_utilisateur']]);
    session_destroy();
    header('Location: connexion.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.css">

</head>
<body class="container mt-5">
    <h1>Mon Profil</h1>

    <p>Bienvenue, <?= htmlspecialchars($prenom . ' ' . $nom) ?> !</p>


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
            <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($user['nom']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="prenom" class="form-label">Prénom</label>
            <input type="text" class="form-control" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prénom']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="date_naissance" class="form-label">Date de naissance</label>
            <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?= $user['date_naissance'] ?>" required>
        </div>
        <div class="mb-3">
            <label for="adresse" class="form-label">Adresse</label>
            <input type="text" class="form-control" id="adresse" name="adresse" value="<?= htmlspecialchars($user['adresse']) ?>">
        </div>
        <div class="mb-3">
            <label for="telephone" class="form-label">Téléphone</label>
            <input type="text" class="form-control" id="telephone" name="telephone" value="<?= htmlspecialchars($user['téléphone']) ?>">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Mettre à jour</button>
    </form>

    <hr>
    <form method="post" action="" onsubmit="return confirm('Es-tu sûr(e) de vouloir supprimer ton compte ? Cette action est irréversible.');">
        <input type="hidden" name="supprimer_compte" value="1">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <button type="submit" class="btn btn-outline-danger">Supprimer mon compte</button>
    </form>

    <hr>
    <a href="contact.php" class="btn btn-warning">Contactez-nous</a>

    </body>
</html>
