<?php

class ImportCommand extends AbstractCommand {
	public static $header = [];

	//bulk insert snowman/stock from csv
	public function index($params)
	{
		$type = isset($params["type"])?$params["type"]:"";
		$filname = isset($params["file"])?$params["file"]:"";
		if($filname){
			$header = $this->getHeader($type);
			$params = ['index'=> 'snowman_test','type'=>'stock'];
			$data = Helper::importCsv($filname.".csv",$header);
			foreach($data as $k=>&$v){
				$v['name'] = iconv('GB2312','UTF-8', $v["name"]);
			}
			$response = Helper::getEs()->bulk($data,$params);
		}

	}

	//bulk insert data
	public function test()
	{
		$params = ['index'=> 'snowman_test','type'=>'stock'];
		$index = [
			["h"=>"hello","w"=>"world"],
			["h"=>"hello2","w"=>"world2"],
			["h"=>"hello3","w"=>"world3"],
			["h"=>"hello4","w"=>"world4"],
		];
		$response = Helper::getEs()->bulk($index,$params);
	}

	public function getHeader($type){
		$header = [];
		if(!empty(self::$header)){
			$header = self::$header;
		}elseif(isset($this->config["csv"][$type])){
			$header =  $this->config["csv"][$type];
		}
		return $header;
	}


	//filter data already in elasticsearch
	public static function addFilter()
	{
		$rs = Helper::getEs()->sort("code desc")->group(["code"])->limit(3000)->search(["type"=>"influence"]);
		$stocks = [];
		foreach($rs["aggregations"]["code"]["buckets"] as $k=>$v){
			$stocks[] = (strpos($v["key"], '60') === 0)?"sh".$v["key"] :"sz".$v["key"];
		}
		return $stocks;
	}


	//do bulk insert
	public function indexInfluence($filename,$header)
	{
		$params = ['index'=> 'snowman_test','type'=>'stock'];
		$data = Helper::importCsv($filename.".csv",$header);
		foreach($data as $k=>&$v){
			$v["code"] = ltrim($v["code"],"'");
			$v["name"] = iconv('GB2312','UTF-8', $v["name"]);
			$v["avgTradePrice"] = $v["tradeTotal"]?$v["tradeMoney"]/$v["tradeTotal"]:0;
		}
		echo $filename." count :".count($data).PHP_EOL;
		return Helper::getEs()->bulk($data,['index'=> 'snowman','type'=>'influence']);
		
	}

	//execute php command.php import/loadFromCsv to load all data from csv
	public function loadFromCsv()
	{
		$filters = self::addFilter();
		$stocks = Helper::getEs()->terms(["code"=>$filters],1)->limit(0,3000)->getArrayResult(['type'=>'stock']);
		$header = ["date","code","name","closePrice","highPrice","lowPrice","openPrice","preClosePrice","incMoney","increase","transaction","tradeTotal","tradeMoney","totalMoney","influenceMoney","avgTradePrice"];
		//Helper::log(ElasticHelper::getInstance()->getCondition(),'condition');
		foreach($stocks as $key=>$stock){
			$filename = "export/Export_daily_data_".$stock["code"];
			$this->indexInfluence($filename,$header);
		}
	}
}
