<?php

class Mailer
{
    private string $apiUrl;
    private string $apiKey;
    private string $fromEmail;
    private string $fromName;

    public function __construct()
    {
        $this->apiUrl    = getenv('SMTP2GO_API_URL') ?: 'https://api.smtp2go.com/v3/email/send';
        $this->apiKey    = getenv('SMTP2GO_API_KEY') ?: '';
        $this->fromEmail = getenv('SMTP2GO_FROM_EMAIL') ?: 'no-reply@zentra.com';
        $this->fromName  = getenv('SMTP2GO_FROM_NAME') ?: 'Zentra';

        if ($this->apiKey === '') {
            error_log("MAILER ERROR: Missing SMTP2GO_API_KEY");
        }
    }

    private function sendEmail(array $payload, ?int $userId = null, string $type = 'generic'): bool
    {
        $ch = curl_init();   // still allowed, not deprecated
        $handle = curl_init($this->apiUrl);

        curl_setopt_array($handle, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'accept: application/json',
                'X-Smtp2go-Api-Key: ' . $this->apiKey
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => 10
        ]);

        $responseBody = curl_exec($handle);
        $httpCode     = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($handle);

        curl_close($handle); // deprecated but still safe; alternative below
        // $handle = null;   // optional: new recommended cleanup

        if ($curlError) {
            $this->logFailure($userId, $type, 'curl_error', $curlError);
            return false;
        }

        if ($httpCode !== 200) {
            $this->logFailure($userId, $type, 'http_' . $httpCode, $responseBody);
            return false;
        }

        return true;
    }

    private function logFailure(?int $userId, string $type, string $code, string $detail): void
    {
        error_log(json_encode([
            'event'     => 'email_send_failed',
            'type'      => $type,
            'user_id'   => $userId,
            'code'      => $code,
            'detail'    => mb_substr($detail, 0, 300),
            'timestamp' => gmdate('c')
        ]));
    }

    public function sendResetPasswordEmail(string $toEmail, string $toName, string $resetToken, ?int $userId = null): bool
    {
        $baseUrl   = rtrim(getenv('APP_BASE_URL') ?: 'https://app.zentra.com', '/');
        $resetLink = $baseUrl . '/reset-password.php?token=' . urlencode($resetToken);

        $subject = "Zentra - Password Reset Request";

        $html = "
            <h3>Hello " . htmlspecialchars($toName) . ",</h3>
            <p>You requested a password reset. Click the link below:</p>
            <p><a href='{$resetLink}'>Reset Password</a></p>
            <p>If you didn't request this, you can ignore this message.</p>
        ";

        $payload = [
            "sender"    => "{$this->fromName} <{$this->fromEmail}>",
            "to"        => ["{$toName} <{$toEmail}>"],
            "subject"   => $subject,
            "html_body" => $html
        ];

        return $this->sendEmail($payload, $userId, 'password_reset');
    }

    public function sendWelcomeAndVerificationEmail(string $toEmail, string $toName, string $activationCode, ?int $userId = null): bool
    {
        $baseUrl   = rtrim(getenv('APP_BASE_URL') ?: 'https://app.zentra.com', '/');
        $verifyLink = $baseUrl . '/verify-email.php?code=' . urlencode($activationCode);

        $subject = "Welcome to Zentra! Please Confirm Your Email";

        $html = "
            <h2>Welcome to Zentra, " . htmlspecialchars($toName) . "!</h2>
            <p>Please verify your email by clicking the link below:</p>
            <p><a href='{$verifyLink}' style='background:#0066cc;color:#fff;padding:10px 15px;border-radius:5px;text-decoration:none;'>Verify My Email</a></p>
            <p>If you didn't create this account, you can ignore this message.</p>
        ";

        $payload = [
            "sender"    => "{$this->fromName} <{$this->fromEmail}>",
            "to"        => ["{$toName} <{$toEmail}>"],
            "subject"   => $subject,
            "html_body" => $html
        ];

        return $this->sendEmail($payload, $userId, 'welcome_verification');
    }
}
