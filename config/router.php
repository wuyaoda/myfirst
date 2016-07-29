<?php
//base on localhost/shang/public/index.php

Router::get('/','IndexController@index');
Router::post('/','IndexController@index');
Router::get('/([\/]?)([\w\_]+)([\/]?)([\w\d\_]*)(.*)',function(){
	$params = func_get_args();
	if(!empty($params[1])){
		$controllerName = ucfirst($params[1]).'Controller';
		$controller = new $controllerName();	
	}
	$view = empty($params[3])?'index':$params[3];
	if(isset($controller) && !method_exists($controller, $view)) {
	    throw new Exception("controller and action not found");
	} else {
	    call_user_func_array(array($controller, $view), array_slice($params,4));
	}
});
/*Router::get('/(:any)',function($params){
    echo 'hello world!'.$params;
});*/
Router::$error_callback = function() {
    throw new Exception("路由无匹配项 404 Not Found");
};
Router::dispatch();