<?php
// Inclure le fichier de configuration
require_once "config.php";

// Initialisation des variables de réponse
$response = array(
    "success" => false,
    "message" => "",
    "redirect" => false
);

// Traitement du formulaire de connexion
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // Récupération des données du formulaire
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    
    // Validation basique des champs
    if(empty($email) || empty($password)){
        $response["message"] = "Veuillez remplir tous les champs.";
        echo json_encode($response);
        exit();
    }
    
    // Vérifier les identifiants de l'utilisateur
    $sql = "SELECT id, first_name, last_name, email, password FROM users WHERE email = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if(mysqli_stmt_execute($stmt)){
            mysqli_stmt_store_result($stmt);
            
            // Vérifier si l'email existe
            if(mysqli_stmt_num_rows($stmt) == 1){
                mysqli_stmt_bind_result($stmt, $id, $firstName, $lastName, $email, $hashed_password);
                
                if(mysqli_stmt_fetch($stmt)){
                    // Vérifier le mot de passe
                    if(password_verify($password, $hashed_password)){
                        // Mot de passe correct
                        $response["success"] = true;
                        $response["message"] = "Connexion réussie !";
                        $response["redirect"] = true;
                        $response["user"] = array(
                            "id" => $id,
                            "firstName" => $firstName,
                            "lastName" => $lastName,
                            "email" => $email
                        );
                    } else {
                        // Mot de passe incorrect
                        $response["message"] = "Mot de passe incorrect.";
                    }
                }
            } else {
                // Email non trouvé
                $response["message"] = "Aucun compte trouvé avec cet email.";
            }
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