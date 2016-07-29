<?php
define("APP_PATH",dirname("."));
$config = require(APP_PATH."/config/main.php");
require APP_PATH.'/application/Bootstrap.php';
require APP_PATH.'/config/router.php';

//Helper::parseCsv("54.csv",$config["csv"][54]);
//Helper::exportCsv(dirname(".")."/data/test.csv",$response);


