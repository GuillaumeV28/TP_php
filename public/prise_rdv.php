<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialisation des variables pour éviter les warnings
$erreur = '';
$success = '';

// Traitement du formulaire de réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Échec CSRF, action interdite');
    }

    $date = $_POST['date'];
    $heure = $_POST['heure'];

    $stmt = $pdo->prepare('SELECT id_rdv FROM RendezVous WHERE date = ? AND heure = ?');
    $stmt->execute([$date, $heure]);
    if ($stmt->fetch()) {
        $erreur = 'Ce créneau est déjà réservé.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO RendezVous (id_utilisateur, date, heure) VALUES (?, ?, ?)');
        if ($stmt->execute([$_SESSION['id_utilisateur'], $date, $heure])) {
            $success = 'Rendez-vous pris avec succès.';
        } else {
            $erreur = 'Erreur lors de la réservation.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prendre un Rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body class="container mt-5">
    <h1>Prendre un Rendez-vous</h1>

    <?php if (!empty($erreur)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

        <div class="mb-3">
            <label for="date" class="form-label">Date</label>
            <input type="date" class="form-control" id="date" name="date" required>
        </div>

        <div class="mb-3">
            <label for="heure" class="form-label">Créneau horaire</label>
            <select class="form-select" id="heure" name="heure" required>
                <?php
                $creneaux = ['09:00', '10:00', '11:00', '12:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'];
                foreach ($creneaux as $heure): ?>
                    <option value="<?= $heure ?>"><?= $heure ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Réserver</button>
    </form>

    <hr>
    <a href="profil.php" class="btn btn-secondary">Retour au profil</a>
</body>
</html>

