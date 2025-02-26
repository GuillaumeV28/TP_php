<?php
require_once '../includes/header.php';

session_start();
session_unset(); // Supprime toutes les variables de session
session_destroy(); // Détruit la session

header('Location: connexion.php');
exit;

