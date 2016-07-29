<?php

class CaptureCommand extends AbstractCommand {

	public function index ()
	{
		foreach(['600','601','603'] as $k=>$v){
			self::_getStockList($v,0);
		}
		foreach(['000','002','300'] as $k=>$v){
		 	self::_getStockList($v,1);
		}
	}


	public function getDailyData($params=[])
	{
		$start = $end = date("Ymd");
		$stocks = Helper::getEs()->limit(0,3000)->getArrayResult(["index"=>"snowman_test","type"=>"stock"]);
		$newResults = [];
		$params = ["index"=>"snowman_test","type"=>"influence"];
		foreach($stocks as $key=>$stock){
			$code = str_replace(["sh","sz"],["0","1"],$stock["code"]);
			$params = Helper::getDailyData($code,$start,$end,$params);
			$response = Helper::getEs()->bulk($params);
			$params['body'] = [];
		}
		//$response = Helper::getEs()->bulk($params);
	}

	public static function _getStockList($start,$type)
	{
		$params = [
			"start"=>20150721,
			"end"=>20150721,
		];
		$symbol = $type ? "sz":"sh";
		$x = 0;
		for($i = 0 ;$i < 10; $i++){
			for($j = 0 ;$j < 10; $j++){
				for($k = 0 ;$k < 10; $k++){
					$params['code'] = $type.$start.$i.$j.$k;
					echo $params['code'] .'<br/>';
					$data = Http::Get("http://quotes.money.163.com/service/chddata.html",$params);
					if(strlen($data) > 200){
						$result = Helper::getStocks($data);
						foreach($result as $key=>$v){
							$v["code"] = $symbol.$v["code"];
							$index = ["body"=>$v,"type"=>"stock"];
							$response = Helper::getEs()->index($index);
						}
					}
				}
			}
		}
	}

}
