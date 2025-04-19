<?php
// Inclure le fichier de configuration
require_once "config.php";
require_once "email_service.php";

// Initialisation des variables de réponse
$response = array(
    "success" => false,
    "message" => ""
);

// Traitement de la demande de réinitialisation
if($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupération de l'email
    $email = trim($_POST["email"]);
    
    // Validation basique
    if(empty($email)) {
        $response["message"] = "Veuillez saisir votre adresse email.";
        echo json_encode($response);
        exit();
    }
    
    // Vérifier si l'email existe dans la base de données
    $sql = "SELECT id, first_name FROM users WHERE email = ?";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        if(mysqli_stmt_execute($stmt)) {
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $user_id, $first_name);
                mysqli_stmt_fetch($stmt);
                
                // Générer un token unique
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Enregistrer le token dans la base de données
                $reset_sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)";
                
                if($reset_stmt = mysqli_prepare($conn, $reset_sql)) {
                    mysqli_stmt_bind_param($reset_stmt, "iss", $user_id, $token, $expires);
                    
                    if(mysqli_stmt_execute($reset_stmt)) {
                        // Envoi de l'email de réinitialisation
                        $reset_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/auth.html?reset_token=$token";
                        
                        $subject = "Réinitialisation de votre mot de passe - Progrès+";
                        $message = "Bonjour $first_name,\n\n";
                        $message .= "Vous avez demandé à réinitialiser votre mot de passe sur Progrès+.\n\n";
                        $message .= "Pour définir un nouveau mot de passe, veuillez cliquer sur le lien suivant :\n";
                        $message .= $reset_url . "\n\n";
                        $message .= "Ce lien expirera dans 1 heure.\n\n";
                        $message .= "Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email.\n\n";
                        $message .= "Cordialement,\n";
                        $message .= "L'équipe Progrès+";
                        
                        // Appeler la fonction d'envoi d'email
                        if(send_email($email, $subject, $message)) {
                            $response["success"] = true;
                            $response["message"] = "Un email de réinitialisation a été envoyé à votre adresse.";
                        } else {
                            $response["message"] = "Impossible d'envoyer l'email de réinitialisation. Veuillez réessayer.";
                        }
                    } else {
                        $response["message"] = "Une erreur est survenue. Veuillez réessayer plus tard.";
                    }
                    
                    mysqli_stmt_close($reset_stmt);
                }
            } else {
                // Pour des raisons de sécurité, on ne révèle pas que l'email n'existe pas
                $response["success"] = true;
                $response["message"] = "Si cet email est associé à un compte, un message de réinitialisation a été envoyé.";
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