<?php
use Elasticsearch\ClientBuilder;
class Elastic{
	private static $_instance = null;
	public $index = "wechat_v3_mongo";
	public $type = "wechat_customer";
	public $params = [];
	public $db;

	protected function __construct()
	{
		$this->db = ClientBuilder::create()->setHosts(["localhost:9200"])->build();
		$this->params = ['index'=>$this->index,'type'=>$this->type];
	}
	public static function getInstance()
	{
		if(self::$_instance === null){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function index($params)
	{
		$params = array_merge($this->params,$params);
		$response = $this->db->index($params);
	}

	public function search($params=[])
	{
		$params = array_merge($this->params,$params);
		$response = $this->db->search($params);
	}
}
