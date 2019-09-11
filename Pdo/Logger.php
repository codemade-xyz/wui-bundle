<?php


namespace CodeMade\WuiBundle\Pdo;


class Logger
{
    protected static $log = [];
    protected static $error = [];
    protected static $timeAll = 0;


    public static function getLog()
    {
        return self::$log;
    }

    public static function getError()
    {
        return self::$error;
    }

    public static function getTimeAll()
    {
        return number_format(self::$timeAll, 2, '.', '');
    }

    public static function addLog(array $log)
    {
        self::$log[] = $log;
    }

    public static function addError($error)
    {
        self::$error[] = $error;
    }

    public static function setError($error)
    {
        self::$error = $error;
    }


    public static function addTimeAll($ms)
    {
        self::$timeAll = self::$timeAll+$ms;
    }

}