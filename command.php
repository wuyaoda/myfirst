<?php
require "src/Autoload.php";
//php command.php Controller/Action p1=xx p2=xx
parse_str(implode("&",array_slice($argv, 1)),$_GET);

$params = explode("/",str_replace("\\","/",key($_GET)));
$controller = ucfirst($params[0]."Command");
if(count($params) > 1){
	$action = $params[1];
}else{
	$action = "index";
}

if(file_exists(dirname('.')."/command/".$controller.".php")){
	$runCommand = new $controller();
	array_shift($_GET);
	$runCommand->$action($_GET);
}else{
	echo "command ".$controller." not found";
}
