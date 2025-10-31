<?php
require_once 'utils/helpers.php';
class Mailer
{
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;

    public function __construct()
    {
        loadEnv('.env');
        $this->smtp_host = getenv('SMTP_HOST');
        $this->smtp_port = getenv('SMTP_PORT');
        $this->smtp_username = getenv('SMTP_USERNAME');
        $this->smtp_password = getenv('SMTP_PASSWORD');
    }


    public function sendActivationEmail($to, $activation_link)
    {
        $subject = "Aktivasi Akun Admin Gudang";
        $message = $this->createActivationEmailTemplate($activation_link);
        return $this->sendEmail($to, $subject, $message);
    }

    public function sendPasswordResetEmail($to, $reset_link)
    {
        $subject = "Reset Password Admin Gudang";
        $message = $this->createPasswordResetEmailTemplate($reset_link);
        return $this->sendEmail($to, $subject, $message);
    }

    private function sendEmail($to, $subject, $message)
    {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . $this->smtp_username . "\r\n";
        return mail($to, $subject, $message, $headers);
    }

    private function createActivationEmailTemplate($link)
    {
        return "
            <h2>Selamat Datang di Sistem Admin Gudang</h2>
            <p>Silakan klik tautan berikut untuk mengaktifkan akun Anda:</p>
            <a href='$link'>$link</a>
            <p>Tautan akan kadaluarsa dalam 24 jam.</p>
        ";
    }

    private function createPasswordResetEmailTemplate($link)
    {
        return "
            <h2>Permintaan Reset Password</h2>
            <p>Silakan klik tautan berikut untuk mereset password Anda:</p>
            <a href='$link'>$link</a>
            <p>Tautan akan kadaluarsa dalam 1 jam.</p>
        ";
    }
}
