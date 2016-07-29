<?php 

class ExportCommand extends AbstractCommand {

	//php command.php export type=stock
	public function index($params=[]) 
	{
		//type is needed
		$config = $this->config["export"];
		$limit = isset($params["size"])?$params["size"]:$config["size"];
		$type = isset($params["type"])?$params["type"]:$config["type"];

		$search = [
			"scroll"=>$config["scroll"],
			"size"=>$limit,
			"type"=>$type,
			//"body"=>,
		];
		$rs = Helper::getEs()->setScrollId($search);
		$filename = str_replace("{{filename}}",$type,$config["filename"]);
		Helper::exportCsv(dirname(".")."/data/".$filename.".csv",$rs["data"],true);
		$time = ceil($rs["count"]/$limit);
		for($i = 0;$i< $time-1;$i++){
			$result = Helper::getEs()->scroll();
			Helper::log($result,$filename);
			Helper::exportCsv(dirname(".")."/data/".$filename.".csv",$result);
		}
		Helper::getEs()->clearScroll();
	}
	//获取远程api的数据到本地csv
	public function saveToCsv()
	{
		$stocks = Helper::getEs()->limit(0,3000)->getArrayResult(["type"=>"stock"]);
		$start = "20140101";
		$end = "20160722";
		foreach($stocks as $k=>$stock){
			$code = str_replace(["sh","sz"],["0","1"],$stock["code"]);
			$params = [
				"code"=>$code,
				"start"=>$start,
				"end"=>$end,
			];
			$result = Http::Get("http://quotes.money.163.com/service/chddata.html",$params);
			$filename = dirname(".")."/data/export/Export_daily_data_".$stock["code"].".csv";
			file_put_contents($filename,$result);
			echo $stock["code"]." saved success!".PHP_EOL;
		}

	}
}