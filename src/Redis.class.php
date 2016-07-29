<?php
use Predis\Client;
/**
 * \Redis
 */
class Redis{
    const CONFIG_FILE = '/config/redis.php';
    protected $db;
    protected static $instance = null;

    protected function __construct()
    {
        $this->db = new Client(require APP_PATH.self::CONFIG_FILE);
    }

    public static function getInstance()
    {   
        if(self::$instance === null){
           self::$instance = new self(); 
        }
        return self::$instance;
    }

    //优先使用redis
    public function get($key,$defauleValue,$expire = null)
    {
        if(!($result = $this->db->get($key))){
            if($expire){
                $this->db->psetex($key,$expire,$defauleValue);
            }else{
                $this->db->set($key,$defauleValue);
            }
            $result = $defauleValue;
        }
        return $result;
    }

    //优先更新redis并返回值
    public function set($key,$defauleValue,$expire = null)
    {
        if($expire){
            $this->db->psetex($key,$expire,$defauleValue);
        }else{
            $this->db->set($key,$defauleValue);
        }
        return $defauleValue;
    }

    public function delete($key)
    {
        return $this->db->del($key);
    }


}