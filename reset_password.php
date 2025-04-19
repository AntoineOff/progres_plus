<?php
// Inclure le fichier de configuration
require_once "config.php";

// Initialisation des variables de réponse
$response = array(
    "success" => false,
    "message" => ""
);

// Traitement de la réinitialisation du mot de passe
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupération des données du formulaire
    $token = trim($_POST["token"]);
    $password = trim($_POST["password"]);
    $confirmPassword = trim($_POST["confirmPassword"]);
    
    // Validation basique des champs
    if(empty($token) || empty($password) || empty($confirmPassword)) {
        $response["message"] = "Veuillez remplir tous les champs.";
        echo json_encode($response);
        exit();
    }
    
    // Vérification de la correspondance des mots de passe
    if($password !== $confirmPassword) {
        $response["message"] = "Les mots de passe ne correspondent pas.";
        echo json_encode($response);
        exit();
    }
    
    // Vérifier que le token existe et est valide
    $now = date('Y-m-d H:i:s');
    $sql = "SELECT user_id FROM password_resets WHERE token = ? AND expires_at > ? LIMIT 1";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $token, $now);
        
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $user_id);
                mysqli_stmt_fetch($stmt);
                
                // Hasher le nouveau mot de passe
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Mettre à jour le mot de passe de l'utilisateur
                $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                
                if($update_stmt = mysqli_prepare($conn, $update_sql)) {
                    mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $user_id);
                    
                    if(mysqli_stmt_execute($update_stmt)) {
                        // Supprimer le token utilisé
                        $delete_sql = "DELETE FROM password_resets WHERE token = ?";
                        if($delete_stmt = mysqli_prepare($conn, $delete_sql)) {
                            mysqli_stmt_bind_param($delete_stmt, "s", $token);
                            mysqli_stmt_execute($delete_stmt);
                            mysqli_stmt_close($delete_stmt);
                        }
                        
                        $response["success"] = true;
                        $response["message"] = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.";
                    } else {
                        $response["message"] = "Une erreur est survenue lors de la mise à jour du mot de passe.";
                    }
                    
                    mysqli_stmt_close($update_stmt);
                }
            } else {
                $response["message"] = "Le lien de réinitialisation est invalide ou a expiré.";
            }
        } else {
            $response["message"] = "Une erreur est survenue. Veuillez réessayer plus tard.";
        }
        
        mysqli_stmt_close($stmt);
    }
    
    // Renvoyer la réponse au format JSON
    echo json_encode($response);
    exit();
}
?>