<?php
class Session
{
    private static function ensureStarted()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function start()
    {
        self::ensureStarted();
    }

    public static function set($key, $value)
    {
        self::ensureStarted();
        $_SESSION[$key] = $value;
    }

    public static function get($key)
    {
        self::ensureStarted();
        return $_SESSION[$key] ?? null;
    }

    public static function destroy()
    {
        self::ensureStarted();
        $_SESSION = [];
        session_destroy();
    }

    public static function isLoggedIn()
    {
        self::ensureStarted();
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin()
    {
        if (!self::isLoggedIn()) {
            header("Location: /login.php");
            exit;
        }
    }
}
