<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Services;

class Email extends BaseController
{
    public function sendEmail()
    {
        // Vérification si le formulaire a été soumis
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

            // Charger le service Email de CodeIgniter
            $email = Services::email();

            // Configuration de l'e-mail
            $to = ""; // L'adresse e-mail du destinataire
            $subject = "Test d'envoi de mail";
            $message = "Ceci est un e-mail de test envoyé depuis un script PHP avec CodeIgniter.";
            $fromEmail = ""; // L'adresse de l'expéditeur

            // Paramétrage des informations de l'e-mail
            $email->setFrom($fromEmail, 'Nom de expéditeur'); // Remplacez par le nom et l'email de l'expéditeur
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($message);

            // Envoi de l'e-mail
            if ($email->send()) {
                // Message de succès après l'envoi
                $messageStatus = "<p style='color: green;'>E-mail envoyé avec succès !</p>";
            } else {
                // Message d'erreur si l'envoi échoue
                $messageStatus = "<p style='color: red;'>Échec de l'envoi de l'e-mail. " . $email->printDebugger() . "</p>";
            }

            return view('/login', ['messageStatus' => $messageStatus]);
        }
    }
}

