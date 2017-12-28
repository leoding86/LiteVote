<?php
namespace Vendor\Util;

class Session
{
    /**
     * 设置session
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set($key, $value)
    {
        session('[start]');
        session($key, $value);
        session('[pause]');
    }

    /**
     * 读取session
     *
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        if (session_status() == PHP_SESSION_NONE) {
            session('[start]');
            session('[pause]');
        }
        return session($key);
    }
}