<?php
// Configuration de la base de données
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // À modifier selon votre configuration
define('DB_PASSWORD', ''); // À modifier selon votre configuration
define('DB_NAME', 'progres_plus');

// Connexion à la base de données
function connectDB() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        die('Erreur de connexion à la base de données: ' . $e->getMessage());
    }
}

// Fonction utilitaire pour nettoyer les entrées utilisateur
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Fonction pour générer une réponse JSON
function jsonResponse($success, $message, $data = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Vérifier si l'utilisateur est connecté en tant qu'admin
function checkAdminAuth() {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        jsonResponse(false, 'Accès non autorisé. Veuillez vous connecter en tant qu\'administrateur.');
    }
}