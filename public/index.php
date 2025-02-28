<?php
session_start();
if (isset($_SESSION['id_utilisateur'])) {
    header('Location: profil.php');
} else {
    header('Location: connexion.php');
}
exit;
require_once '../config/database.php';
echo 'Connexion réussie !';
?>
