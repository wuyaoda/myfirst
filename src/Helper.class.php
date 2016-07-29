<?php

class Helper {

	public static function getEs()
	{
		return ElasticHelper::getInstance()->start();
	}

	public static function getStocks($data)
	{
		$search = "/(\d{4}-\d{2}-\d{2}),'";
		for($i = 0;$i < 14;$i++){
			$search .= "([^,]+),";
		}
		$search .= "([^,]+)/";
		$flag = preg_match_all($search, $data, $matches);
		$keys = ["code"=>2,"name"=>3,"date"=>1];//+1
		$results = [];
		if($flag){
			foreach($matches[0] as $key=>$result){
				$results[$key]["code"] = $matches[$keys["code"]][$key];
				$results[$key]["name"] = iconv('GB2312','UTF-8', $matches[$keys["name"]][$key]);
				$results[$key]["date"] = $matches[$keys["date"]][$key];
			}
		}
		return $results;
	}

	public static function getDailyData($code,$start,$end,$params=[]){
		$get = [
			"code"=>$code,
			"start"=>$start,
			"end"=>$end,
		];
		$data = Http::Get("http://quotes.money.163.com/service/chddata.html",$get);
		$search = "/(\d{4}-\d{2}-\d{2}),'";
		for($i = 0;$i < 14;$i++){
			$search .= "([^,]+),";
		}
		$search .= "([^,]+)/";
		$flag = preg_match_all($search, $data, $matches);
		$keys = ["date","code","name","closePrice","highPrice","lowPrice","openPrice","preClosePrice","incMoney","increase","transaction","tradeTotal","tradeMoney","totalMoney","influenceMoney"];
		$results = [];
        if($flag){
            foreach($matches[0] as $key=>$result){
                foreach($keys as $k=>$v){
                    $results[$key][$v] = $matches[$k+1][$key];
                }
                $results[$key]["code"] = ltrim($results[$key]["code"],"'");
                $results[$key]["avgTradePrice"] = $results[$key]["tradeTotal"]?$results[$key]["tradeMoney"]/$results[$key]["tradeTotal"]:0;
                $params['body'][] = ['index'=> ['_id'=>$results[$key]["code"].$results[$key]["date"]]];
                $params['body'][] = $results[$key];

            }
        }
        return $params;
	}

	public static function importCsv($filename,$header=[]){
        $basedir = dirname(".")."/data/";
        $file = $basedir.$filename;
        if(file_exists($file) && $header){
            $csv = array_map("str_getcsv",file($file));
            array_shift($csv);
            foreach($csv as $k=>&$v){
                $v = array_combine($header,$v);
            }
            return $csv;
        }
        return '';
    }



    // w write
    // a append
    public static function exportCsv($filename,$data,$setHeader=false){
        $fp = fopen($filename,"a");
        if($setHeader){
	        $header = array_keys($data[0]);
	        fputcsv($fp,$header);
        }
        foreach($data as $key=>$value){
            foreach($value as $k=>&$v){
               if(is_array($v)){$v = json_encode($v);}
               $v = @iconv('UTF-8','GB2312',$v);
            }
            fputcsv($fp,$value);
        }
        fclose($fp);
    }

    public static function log($data,$type){
    	//$file = dirname(".")."/log/".date("Y-m-d").pathinfo($type,PATHINFO_FILENAME)."txt";
    	$file = realpath(dirname("."))."/runtime/log/".date("Y-m-d").$type.".txt";
    	file_put_contents($file,print_r($data,true),FILE_APPEND);
    }
}

