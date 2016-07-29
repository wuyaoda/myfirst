<?php

class Http{

	public static function Post($url,$params=[])
	{
	    $ch = curl_init();  
	 
	    curl_setopt_array($ch, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_CUSTOMREQUEST => "POST",
  		  CURLOPT_POSTFIELDS => json_encode($params),
		  CURLOPT_HTTPHEADER => array(
		    "apikey: 824907a6f8dc3e6806c2f35a355f82ff",
		    "cache-control: no-cache",
		    "content-type: application/json",
		  ),
		));
	    $output=curl_exec($ch);
	 
	    curl_close($ch);
	    return $output;	 
	}

	public static function Get($url,$params=[]){
		$suffix = "";
		foreach($params as $k => $param){
			$suffix .= $k ."=".$param."&";	
		}
		$seperator = (strpos($url, "?") !== false) ? "&":"?";
		!empty($suffix) and $url = $url.$seperator.rtrim($suffix,"&");
		return file_get_contents($url);
	}
}