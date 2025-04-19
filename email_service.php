<?php
/**
 * Service d'envoi d'emails
 * 
 * Ce fichier contient les fonctions nécessaires pour envoyer des emails
 * depuis l'application Progrès+
 */

/**
 * Envoie un email en utilisant la fonction mail de PHP
 * 
 * @param string $to Adresse email du destinataire
 * @param string $subject Sujet de l'email
 * @param string $message Corps du message
 * @return bool Succès ou échec de l'envoi
 */
function send_email($to, $subject, $message) {
    // Headers pour l'email
    $headers = "From: noreply@progresplus.com\r\n";
    $headers .= "Reply-To: support@progresplus.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // Envoi de l'email
    return mail($to, $subject, $message, $headers);
}

/**
 * Alternative: envoi d'email avec PHPMailer (décommentez si vous utilisez PHPMailer)
 * Nécessite l'installation de PHPMailer via Composer
 */
/*
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_email($to, $subject, $message) {
    // Instancier PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        // Configuration du serveur
        $mail->isSMTP();
        $mail->Host       = 'smtp.example.com';  // Votre serveur SMTP
        $mail->SMTPAuth   = true;
        $mail->Username   = 'user@example.com';  // Votre nom d'utilisateur SMTP
        $mail->Password   = 'password';          // Votre mot de passe SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Destinataires
        $mail->setFrom('noreply@progresplus.com', 'Progrès+');
        $mail->addAddress($to);
        $mail->addReplyTo('support@progresplus.com', 'Support Progrès+');
        
        // Contenu
        $mail->isHTML(false);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Erreur d'envoi d'email: {$mail->ErrorInfo}");
        return false;
    }
}
*/
?>