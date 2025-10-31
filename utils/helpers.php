<?php

/**
 * Helper Functions
 * File ini berisi fungsi-fungsi bantuan yang dapat digunakan di seluruh aplikasi
 */

/**
 * Sanitize string input
 */
function sanitize($data)
{
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Format currency to IDR
 */
function formatRupiah($amount, $decimals = null)
{
    $amount = (float) $amount;
    if ($decimals === null) {
        $decimals = fmod($amount, 1) !== 0.0 ? 2 : 0;
    }
    return 'Rp ' . number_format($amount, $decimals, ',', '.');
}

/**
 * Format date to Indonesian format
 */
function formatDate($date, $format = 'd/m/Y H:i')
{
    return date($format, strtotime($date));
}

/**
 * Redirect to a page
 */
function redirect($url)
{
    header("Location: " . $url);
    exit;
}

/**
 * Check if user is logged in
 */
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Set flash message
 */
function setFlashMessage($message, $type = 'success')
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Get and clear flash message
 */
function getFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'text' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return $message;
    }
    return null;
}

/**
 * Generate random token
 */
function generateToken($length = 32)
{
    return bin2hex(random_bytes($length));
}

/**
 * Validate email format
 */
function isValidEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Check password strength
 */
function isStrongPassword($password)
{
    return strlen($password) >= 6;
}

/**
 * Load .env file
 */
function loadEnv($path)
{
    if (!file_exists($path)) return;

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue; // komentar
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        putenv("$key=$value");
    }
}
