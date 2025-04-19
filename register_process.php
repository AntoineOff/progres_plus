<?php
// Inclure le fichier de configuration
require_once "config.php";

// Initialisation des variables de réponse
$response = array(
    "success" => false,
    "message" => "",
    "redirect" => false
);

// Traitement du formulaire d'inscription
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Récupération des données du formulaire
    $firstName = trim($_POST["firstName"]);
    $lastName = trim($_POST["lastName"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirmPassword = trim($_POST["confirmPassword"]);
    
    // Validation basique des champs
    if(empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)){
        $response["message"] = "Veuillez remplir tous les champs.";
        echo json_encode($response);
        exit();
    }
    
    // Vérification de la correspondance des mots de passe
    if($password !== $confirmPassword){
        $response["message"] = "Les mots de passe ne correspondent pas.";
        echo json_encode($response);
        exit();
    }
    
    // Vérification de l'email (format et unicité)
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $response["message"] = "Format d'email invalide.";
        echo json_encode($response);
        exit();
    }
    
    // Vérifier si l'email existe déjà
    $check_email = "SELECT * FROM users WHERE email = ?";
    
    if($stmt = mysqli_prepare($conn, $check_email)){
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) > 0){
                $response["message"] = "Cet email est déjà utilisé.";
                echo json_encode($response);
                exit();
            }
        } else {
            $response["message"] = "Oups! Une erreur est survenue. Veuillez réessayer plus tard.";
            echo json_encode($response);
            exit();
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Hasher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Préparer la requête d'insertion
    $sql = "INSERT INTO users (first_name, last_name, email, password) VALUES (?, ?, ?, ?)";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "ssss", $firstName, $lastName, $email, $hashed_password);
        
        if(mysqli_stmt_execute($stmt)){
            // Utilisateur enregistré avec succès
            $response["success"] = true;
            $response["message"] = "Inscription réussie !";
            $response["redirect"] = true;
            $response["user"] = array(
                "firstName" => $firstName,
                "lastName" => $lastName,
                "email" => $email
            );
        } else {
            $response["message"] = "Oups! Une erreur est survenue. Veuillez réessayer plus tard.";
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Renvoyer la réponse au format JSON
    echo json_encode($response);
    exit();
}
?>