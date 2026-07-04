<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Formulaire de contact — envoi via l'API Resend (clé dans .env uniquement).
 */
class ContactController
{
    private string $resendApiKey;
    private string $fromEmail;
    private string $toEmail;
    private string $fromName;

    public function __construct()
    {
        $this->resendApiKey = RESEND_API_KEY;
        $this->fromEmail = CONTACT_FROM_EMAIL;
        $this->toEmail = CONTACT_TO_EMAIL;
        $this->fromName = CONTACT_FROM_NAME;
    }

    public function sendMessage(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Méthode non autorisée']);
        }

        if ($this->resendApiKey === '') {
            error_log('[Contact] RESEND_API_KEY non configurée');
            $this->jsonResponse([
                'success' => false,
                'message' => 'Le formulaire de contact est temporairement indisponible.',
            ]);
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        $errors = $this->validateForm($name, $email, $subject, $message);
        if ($errors !== []) {
            $this->jsonResponse(['success' => false, 'message' => implode(', ', $errors)]);
        }

        if ($this->sendEmailViaResend($name, $email, $subject, $message)) {
            $this->jsonResponse(['success' => true, 'message' => 'Votre message a été envoyé avec succès !']);
        }

        $this->jsonResponse([
            'success' => false,
            'message' => 'Erreur lors de l\'envoi du message. Veuillez réessayer.',
        ]);
    }

    /**
     * @return list<string>
     */
    private function validateForm(string $name, string $email, string $subject, string $message): array
    {
        $errors = [];

        if ($name === '') {
            $errors[] = 'Le nom est requis';
        }

        if ($email === '') {
            $errors[] = 'L\'email est requis';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format d\'email invalide';
        }

        if ($subject === '') {
            $errors[] = 'Le sujet est requis';
        }

        if ($message === '') {
            $errors[] = 'Le message est requis';
        }

        return $errors;
    }

    private function sendEmailViaResend(string $name, string $email, string $subject, string $message): bool
    {
        $data = [
            'from' => $this->fromName . ' <' . $this->fromEmail . '>',
            'to' => [$this->toEmail],
            'subject' => '[Contact Site] ' . $subject,
            'html' => $this->buildEmailTemplate($name, $email, $subject, $message),
            'reply_to' => $email,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->resendApiKey,
            'Content-Type: application/json',
        ]);

        curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('[Contact] Resend HTTP ' . $httpCode);
        }

        return $httpCode === 200;
    }

    private function buildEmailTemplate(string $name, string $email, string $subject, string $message): string
    {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Nouveau message de contact</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f8f9fa; padding: 20px; }
                .field { margin-bottom: 15px; }
                .label { font-weight: bold; color: #2c3e50; }
                .value { margin-top: 5px; padding: 10px; background-color: white; border-left: 3px solid #3498db; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Nouveau message de contact</h1>
                    <p>Reçu depuis le site CSPI10</p>
                </div>
                <div class="content">
                    <div class="field">
                        <div class="label">Nom :</div>
                        <div class="value">' . htmlspecialchars($name) . '</div>
                    </div>
                    <div class="field">
                        <div class="label">Email :</div>
                        <div class="value">' . htmlspecialchars($email) . '</div>
                    </div>
                    <div class="field">
                        <div class="label">Sujet :</div>
                        <div class="value">' . htmlspecialchars($subject) . '</div>
                    </div>
                    <div class="field">
                        <div class="label">Message :</div>
                        <div class="value">' . nl2br(htmlspecialchars($message)) . '</div>
                    </div>
                </div>
                <div class="footer">
                    <p>Ce message a été envoyé depuis le formulaire de contact du site CSPI10</p>
                </div>
            </div>
        </body>
        </html>';
    }

    /**
     * @param array<string, mixed> $data
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
