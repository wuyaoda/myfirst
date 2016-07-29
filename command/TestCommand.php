<?php
class TestCommand extends AbstractCommand{
    public function index($params)
    {
        $default = ["index"=>"snowman_test","type"=>"stock","action"=>"index"];
        $params = array_merge($default,$params);
        $filename = isset($params["file"])?$params["file"]:"";
        if($filename){
            $header = $this->getHeader($params['type']);
            //$params = ['index'=> 'snowman_test','type'=>'stock'];
            $data = Helper::importCsv($filename.".csv",$header);
            foreach($data as $k=>&$v){
                $v['name'] = iconv('GB2312','UTF-8', $v["name"]);
            }
            Helper::getEs()->bulk($data,$params);
        }
    }

    public function delete($params)
    {
        $default = ["index"=>"snowman","type"=>"influence","action"=>"delete"];
        $params = array_merge($default,$params);
        $code = ["300260","002609","002607","002606","002604","002602","002601","002260","002160","002060","000860","000609","000608","000601","000060"];
        $data  = Helper::getEs()->select("_id")->terms(["code"=>$code])->limit(0,9999)->getId(["index"=>"snowman","type"=>"influence"]);
        //Helper::log(ElasticHelper::getInstance()->getCondition(),"delete");die();
        Helper::getEs()->bulk($data,$params);
    }

}
