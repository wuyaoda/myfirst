<?php
class IndexController extends Controller{

	public function index()
	{
		
		$model = [1,2,3];
		//$client = new Predis\Client([
		 //   'scheme' => 'tcp',
		  //  'host'   => '127.0.0.1',
		  //  'port'   => 6379,
		//]);
		  // $client->set("keys:hello", str_pad(7, 4, '0', 0));
		$this->render("index",["model"=>$model]);
	}

	public function test()
	{

	}

}