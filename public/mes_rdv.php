<?php
session_start();
require_once '../config/database.php';

// Générer un token CSRF s'il n'existe pas
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_utilisateur'])) {
    header('Location: connexion.php');
    exit;
}

// Récupérer les rendez-vous
$stmt = $pdo->prepare('SELECT * FROM RendezVous WHERE id_utilisateur = ? ORDER BY date, heure');
$stmt->execute([$_SESSION['id_utilisateur']]);
$rendezvous = $stmt->fetchAll();

// Vérifier si une annulation est demandée
if (isset($_GET['annuler']) && isset($_GET['csrf_token'])) {
    if ($_GET['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Échec CSRF, action interdite');
    }

    $id_rdv = $_GET['annuler'];

    // Supprimer le rendez-vous
    $stmt = $pdo->prepare('DELETE FROM RendezVous WHERE id_rdv = ? AND id_utilisateur = ?');
    $stmt->execute([$id_rdv, $_SESSION['id_utilisateur']]);

    // Régénérer un nouveau token CSRF après suppression pour éviter les réutilisations
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // Redirection pour éviter une nouvelle soumission en cas de rafraîchissement de la page
    header('Location: mes_rdv.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/style.css">
</head>
<body class="container mt-5">
    <h1>Mes Rendez-vous</h1>

    <?php if (empty($rendezvous)): ?>
        <div class="alert alert-info">Vous n'avez aucun rendez-vous pour l'instant.</div>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rendezvous as $rdv): ?>
                    <tr>
                        <td><?= htmlspecialchars($rdv['date']) ?></td>
                        <td><?= htmlspecialchars($rdv['heure']) ?></td>
                        <td>
                            <a href="?annuler=<?= $rdv['id_rdv'] ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Êtes-vous sûr de vouloir annuler ce rendez-vous ?');">
                               Annuler
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <hr>
    <a href="profil.php" class="btn btn-secondary">Retour au profil</a>
</body>
</html>
