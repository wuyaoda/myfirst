<?php

class DeleteCommand {

	public function index()
	{
		$rs = Helper::getEs()->limit(0,2000)->search(["type"=>"influence"]);
		foreach($rs["hits"]["hits"] as $k=>$v){
			Helper::getEs()->db->delete(["index"=>"snowman","type"=>"influence","id"=>$v["_id"]]);
			echo " delete success! cost time ".$v["_id"]." ms".PHP_EOL;
		}
	}

	//获取es记录count大于600的记录
	public function getCount()
	{
		$data = Helper::getEs()->group(["code"])->limit(0,3000)->search(["index"=>"snowman","type"=>"influence"]);
		Helper::log(ElasticHelper::getInstance()->getCondition(),"condition");
		foreach($data["aggregations"]["code"]["buckets"]  as $k=>$v){
			if($v["doc_count"] > 600){
				echo $v["key"].PHP_EOL;	
			}
		}
	}

	public function saveToCsv($params)
	{

	}
}
