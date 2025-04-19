<?php
// Configuration de la base de données
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // À modifier selon votre configuration
define('DB_PASSWORD', ''); // À modifier selon votre configuration
define('DB_NAME', 'progres_plus');

// Établir la connexion
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Vérifier la connexion
if(!$conn){
    die("ERREUR : Impossible de se connecter. " . mysqli_connect_error());
}
?>