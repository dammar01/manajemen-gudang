<?php
require_once 'config/database.php';
require_once 'config/mailer.php';
require_once 'models/User.php';
require_once 'lib/PasswordReset.php';
require_once 'utils/Session.php';
require_once 'utils/Validator.php';

class AuthController
{
    private $db;
    private $user;
    private $mailer;
    private $passwordReset;
    private $validator;

    public function __construct()
    {
        $this->db = (new Database())->connect();
        $this->user = new User($this->db);
        $this->mailer = new Mailer();
        $this->passwordReset = new PasswordReset($this->db);
        $this->validator = new Validator();
    }

    public function register($email, $password)
    {
        $email = strtolower(trim($email));
        $password = trim($password);
        if (!$this->validator->validateEmail($email)) {
            return ['success' => false, 'message' => 'Format email tidak valid'];
        }

        if ($password === '' || trim($password) === '') {
            return ['success' => false, 'message' => 'Password wajib diisi'];
        }

        if (!$this->validator->validatePassword($password)) {
            return ['success' => false, 'message' => 'Password minimal 6 karakter'];
        }

        $this->user->email = $email;
        $this->user->password = $password;

        if ($this->user->emailExists()) {
            return ['success' => false, 'message' => 'Email sudah terdaftar'];
        }

        if ($this->user->create()) {
            $activation_link = $this->buildUrl('activate.php?token=' . urlencode($this->user->activation_token));

            if ($this->mailer->sendActivationEmail($email, $activation_link)) {
                return ['success' => true, 'message' => 'Registrasi berhasil. Silakan cek email untuk aktivasi.'];
            }

            $this->user->deleteByEmail($email);
        }

        return ['success' => false, 'message' => 'Registrasi gagal'];
    }

    public function login($email, $password)
    {
        $email = strtolower(trim($email));
        if (!$this->validator->validateEmail($email)) {
            return ['success' => false, 'message' => 'Format email tidak valid'];
        }

        $userData = $this->user->findByEmail($email);

        if (!$userData) {
            return ['success' => false, 'message' => 'Email tidak terdaftar'];
        }

        if ((int) $userData['is_active'] !== 1) {
            return ['success' => false, 'message' => 'Akun belum aktif. Silakan cek email Anda untuk aktivasi.'];
        }

        if (!password_verify($password, $userData['password'])) {
            return ['success' => false, 'message' => 'Email atau password salah'];
        }

        Session::start();
        Session::set('user_id', $userData['id']);
        Session::set('user_email', $userData['email']);

        $this->user->id = $userData['id'];
        $this->user->email = $userData['email'];

        return ['success' => true];
    }

    public function forgotPassword($email)
    {
        $email = strtolower(trim($email));

        if (!$this->validator->validateEmail($email)) {
            return ['success' => false, 'message' => 'Format email tidak valid'];
        }

        $userData = $this->user->findByEmail($email);

        if (!$userData) {
            return ['success' => false, 'message' => 'Email tidak terdaftar'];
        }

        if ((int) $userData['is_active'] !== 1) {
            return ['success' => false, 'message' => 'Akun belum aktif. Silakan aktivasi terlebih dahulu.'];
        }

        $token = $this->passwordReset->createToken($email);

        if ($token) {
            $reset_link = $this->buildUrl('reset_password.php?token=' . urlencode($token));

            if ($this->mailer->sendPasswordResetEmail($email, $reset_link)) {
                return ['success' => true, 'message' => 'Tautan reset password telah dikirim ke email'];
            }
        }

        return ['success' => false, 'message' => 'Gagal mengirim email reset password'];
    }

    public function resetPassword($token, $new_password)
    {
        $new_password = trim($new_password);
        if (!$this->validator->validatePassword($new_password)) {
            return ['success' => false, 'message' => 'Password minimal 6 karakter'];
        }

        $email = $this->passwordReset->validateToken($token);

        if (!$email) {
            return ['success' => false, 'message' => 'Token tidak valid atau telah kadaluarsa'];
        }

        $userData = $this->user->findByEmail($email);
        if (!$userData) {
            return ['success' => false, 'message' => 'Pengguna tidak ditemukan'];
        }

        $this->user->email = $email;
        $this->user->password = $new_password;
        if ($this->user->updatePassword()) {
            $this->passwordReset->deleteToken($token);
            return ['success' => true, 'message' => 'Password berhasil direset'];
        }

        return ['success' => false, 'message' => 'Gagal reset password'];
    }

    public function activateAccount($token)
    {
        $token = trim($token);
        if ($token === '') {
            return ['success' => false, 'message' => 'Token aktivasi tidak valid'];
        }

        if ($this->user->activateAccount($token)) {
            return ['success' => true, 'message' => 'Akun berhasil diaktifkan'];
        }
        return ['success' => false, 'message' => 'Token aktivasi tidak valid'];
    }

    private function buildUrl($path)
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = '';

        if (!empty($_SERVER['PHP_SELF'])) {
            $basePath = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            if ($basePath === '.' || $basePath === '/') {
                $basePath = '';
            }
        }

        $baseUrl = rtrim($scheme . '://' . $host . $basePath, '/');

        return $baseUrl . '/' . ltrim($path, '/');
    }
}
