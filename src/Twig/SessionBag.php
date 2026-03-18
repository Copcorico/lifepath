<?php
namespace App\Twig;

class SessionBag
{
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }
}
