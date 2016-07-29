<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/21
 * Time: 15:29
 */
class View
{
    const VIEW_BASE_PATH = '/application/views/';
    public $viewPath;

    public function __construct($view)
    {
        $this->viewPath = $view;
    }

    public static function make($viewName = null,$controller="")
    {
        if ( ! $viewName ) {
            throw new Exception("视图名称不能为空！");
        } else {
            $viewFilePath = self::getFilePath($viewName,$controller);
            if ( is_file($viewFilePath) ) {
                return new View($viewFilePath);
            } else {
                throw new Exception("视图文件不存在！");
            }
        }
    }

    public static function getFilePath($viewName,$controller="")
    {
        if(strpos($viewName, "/") !== false){
            $params = explode('/', $viewName);
            $controller =  $params[0];    
            $view =  $params[1];    
        }else{
            $controller =  substr($controller, 0,-10);    
            $view =  $viewName;  
        }
        return APP_PATH.self::VIEW_BASE_PATH.$controller.'/'.$view.'.php';
    }

  
}