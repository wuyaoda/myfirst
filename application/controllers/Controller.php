<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/5/21
 * Time: 13:32
 */
class Controller
{
    protected $view;
    public $title = "snowman";
    public $layout= "layout/main";
    public $basedir= APP_PATH;
    public $data= [];

    public function __construct()
    {

    }

    public function __set($key,$value)
    {
        $this->$key = $value;
    }

    public function render($viewName = null,$data=[])
    {
        $this->view = View::make($viewName,get_class($this));
        $this->data = $data;
        extract($data);
        $content = $viewName;
        require View::getFilePath($this->layout);
    }

    public function renderPartial($viewName = null,$data=[])
    {
        $this->view = View::make($viewName,get_class($this));
        $data = empty($data)? $this->data : $data;
        extract($data);
        require $this->view->viewPath;
    }

    public function __destruct()
    {

    }

}