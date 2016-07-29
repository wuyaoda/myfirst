<?php
class AbstractCommand {
	public static $header = [];
	public $config = [];
	public function __construct()
	{
		$this->config = require(dirname(".")."/config/main.php");
	}

    public function getHeader($type){
        $header = [];
        if(!empty(static::$header)){
            $header = static::$header;
        }elseif(isset($this->config["csv"][$type])){
            $header =  $this->config["csv"][$type];
        }
        return $header;
    }
}
