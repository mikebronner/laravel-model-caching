<?php namespace GeneaLabs\LaravelModelCaching;

class CacheGlobal
{
    protected static $enabled = true;

    public static function disableCache()
    {
        static::$enabled = false;
    }

    public static function enableCache()
    {
        static::$enabled = true;
    }

    public static function isDisabled()
    {
        return !static::$enabled;
    }

    public static function isEnabled()
    {
        return static::$enabled;
    }
}
