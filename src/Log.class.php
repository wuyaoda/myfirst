<?php
// extends SeasLog
class Log
{
	protected  $_instance = null;

	private function __construct()
    {
        SeasLog::setBasePath();
    }

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


}