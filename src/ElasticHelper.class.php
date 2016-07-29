<?php
use Elasticsearch\ClientBuilder;
/**
 * Class ElasticHelper
 * 使用start来清除之前查询的condtion
 * Helper::getEs()->select('COUNT(mid),sex,mid')->term(["sex"=>1],1)
 * ->terms(["mid"=>[34,35,33]])->wildcard(["language"=>"*zh*"])->sort("id desc ,mid asc")->aggs("mid")->limit(10);
 */
class ElasticHelper {
    private static $_instance = null;
    public $index = "snowman";
    public $type = "stock";
    public $params = [];
    public $db;
    private  $_condition;
    protected $_sortby;
    protected $scroll_id;
    protected $types = array('must','must_not');

    private function __construct()
    {
        $this->db = ClientBuilder::create()->setHosts(["localhost:9200"])->build();
        $this->params = ['index'=>$this->index,'type'=>$this->type];
    }

    //index deleteByQuery bulk search
    public function __call($method,$params)
    {
        $params[0] = $this->beforeSearch($params[0]);
        if (method_exists($this->db,$method)) {
            return call_user_func_array([$this->db,$method], $params);
        }
        throw new Exception("Error Processing Request", 1);
    }

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function bulk($data,$params)
    {
        $params = $this->beforeSearch($params);
        $esParams = [];
        $index = $params["index"];
        $type = $params["type"];
        $action = isset($params["action"])?$params["action"]:"index";
        $n = count($data);
        if($action == "index"){
            $esParams = $this->bulkIndex($esParams,$data,$index,$type);
        }elseif($action == "delete"){
            $esParams = $this->bulkDelete($esParams,$data,$index,$type);
        }
        if($n % 500 != 0){
             $this->db->bulk($esParams);
        }
    }

    protected function bulkIndex($esParams,$data,$index,$type)
    {
        foreach($data as $k=>$v){
            $esParams['body'][] = ['index'=>[
                "_index"=>$index,
                "_type"=>$type,
                "_id"=>null,
            ]];
            $esParams['body'][] = $v;
            if($k % 500 == 0){
                echo $k.PHP_EOL;
                $this->db->bulk($esParams);
                $esParams = [];
            }
        }

        return $esParams;
    }

    protected function bulkDelete($esParams,$data,$index,$type)
    {
        foreach($data as $k=>$v){
            $esParams['body'][] = ['delete'=>[
                "_index"=>$index,
                "_type"=>$type,
                "_id"=>$v["_id"]
            ]];
            if($k % 500 == 0){
                echo $k.PHP_EOL;
                $this->db->bulk($esParams);
                $esParams = [];
            }
        }

        return $esParams;
    }

    public function count($params=[]){
        $params = $this->beforeSearch($params);
        $result = $this->db->count($params);
        return $result["count"];
    }

    protected function beforeSearch($params)
    {
        $params = array_merge($this->params,$params);
        if(!empty($this->_condition)){
            $params["body"] = json_encode($this->_condition);
        }
        return $params;
    }

    public function getScrollId()
    {
        return $this->scroll_id;
    }

    /**
    *$params["scroll"] = "1m";
    *$params["size"] = $limit;
    */
    public function setScrollId($params=[])
    {
        $params = $this->beforeSearch($params);
        $search = $this->db->search($params);
        $this->scroll_id = $search["_scroll_id"];
        $rs = [];
        foreach($search["hits"]["hits"] as $k=>$v){
            $rs[] = $v["_source"];
        };
        return ["count"=>$search["hits"]["total"],"data"=>$rs];
    }

    public function scroll()
    {
        $params = [
            "scroll_id" =>$this->scroll_id,
            "scroll" =>"1m",
        ];
        $result = $this->db->scroll($params);
        $rs = [];
        foreach($result["hits"]["hits"] as $k=>$v){
            $rs[] = $v["_source"];
        };
        return $rs;
    }

    public function clearScroll()
    {
        return $this->db->clearScroll(["scroll_id" =>$this->scroll_id]);
    }

    //aggs未实现
    public function getArrayResult($params=[],$from="_source")
    {
        $result = $this->search($params);
        $rs = [];
        foreach($result["hits"]["hits"] as $k=>$v){
            $rs[] = $v["_source"];
        };

        return $rs;
    }

    //aggs未实现
    public function getId($params=[])
    {
        $result = $this->search($params);
        $rs = [];
        foreach($result["hits"]["hits"] as $k=>$v){
            $rs[] = ["_id"=>$v["_id"]];
        };
        return $rs;
    }


    public function getObjResult($params,$from="_source")
    {
        $data = $this->getArrayResult($params,$from);
        return json_decode(json_encode($data));
    }

    public function start()
    {
        $this->_condition = array();
        return $this;
    }

    /**
     * @param $condition = array("filed" => "value")
     * @param int $type 0,1| must,must_not
     * should currently not allowed
     * @return $this
     */
    public function setCondition($subtype,$conditions,$type = 0)
    {
        $key = $this->types[$type];
        if(isset($conditions[0])){
            foreach($conditions as $k=>$condition){
                 $this->_condition["query"]["bool"][$key][] = array($subtype => $condition);
            }
        }else{
            $this->_condition["query"]["bool"][$key][] = array($subtype => $conditions);
        }
        return $this;
    }

    public function term($condition,$type = 0)
    {
        return $this->setCondition('term',$condition,$type);
    }

    /**
     * @param $condition
     * @param int $type
     * @return mysql 风格查询 使用select where in between group
     */
    public function where($condition,$type = 0)
    {
        return $this->term($condition,$type);
    }

    public function in($condition,$type = 0)
    {
        return $this->terms($condition,$type);
    }

    public function between($condition,$type = 0)
    {
        return $this->range($condition,$type);
    }

    public function like($condition,$type = 0)
    {
        return $this->wildcard($condition,$type);
    }

    public function group($field)
    {
        return $this->aggs($field);
    }

    public function order($condition)
    {
        return $this->sort($condition);
    }



    public function terms($condition,$type = 0)
    {
        return $this->setCondition('terms',$condition,$type);
    }

    //这边没有对 value做处理，需要自己将value两边加 '*'.$like.'*'
    public function wildcard($condition,$type = 0)
    {
        return $this->setCondition('wildcard',$condition,$type);
    }
    /**
     * @param $condition  array("create_time"=>array("gte"=>xxx,"lte"=>xxx))
     * @param int $type
     */
    public function range($condition,$type = 0)
    {
        return $this->setCondition('range',$condition,$type);
    }


    public function limit($limit,$offset= 0)
    {
        $data = func_get_args();
        if(count($data) == 2){
            $limit = $data[1];
            $offset = $data[0];
        }
        if(isset($this->_condition["aggregations"])){
            $key = key($this->_condition["aggregations"]);
            //$this->_condition["aggregations"][$key]["terms"]["from"] = $offset;
            $this->_condition["aggregations"][$key]["terms"]["size"] = $limit;
        }else{
            $this->_condition["from"] = $offset;
            $this->_condition["size"] = $limit;
        }
        return $this;
    }
    /**
     * @param $conditions array("id"=>array("order"=>"desc"))
     * @return $this
     */
    public function sort($conditions)
    {
        if(is_string($conditions)){
            $sortby = explode(',',$conditions);
            foreach($sortby as $k=>$v){
                $data = explode(' ',$v);
                $this->_condition["sort"][$data[0]] = array("order"=>$data[1]);
            }
            $this->_sortby = $sortby;
        }elseif(isset($conditions[0])){
            foreach($conditions as $k=>$condition){
                $this->_condition["sort"] = $condition;
            }
        }elseif(is_array($conditions)){
            $this->_condition["sort"] = $conditions;
        }
        return $this;
    }



    public function select($fields)
    {
        if(is_string($fields)){
            $this->_condition["_source"]["includes"] = explode(',',$fields);
        }
        if(is_array($fields)) {
            $this->_condition["_source"]["includes"] = $fields;
        }
        return $this;
    }

    //id desc,mid asc
    public function aggs($fields,$sortby='')
    {
        if(is_string($fields)){
            $fields = explode(',',$fields);
        }
        $sort = array();
        if(!empty($this->_sortby)){
            foreach($this->_sortby as $k=>$v){
                $data = explode(' ',$v);
                $sort[$data[0]] =  $data[1];
            }
        }
        $result = array();
        if(is_array($fields)){
            $fields = array_reverse($fields);
            foreach($fields as $k=>$field){
                if($k == 0){
                    $result[$k]["aggregations"][$field]["terms"]["field"] = $field;
                    $addSelectAggs = $this->addSelectAggs();
                    if(isset($addSelectAggs["aggregations"])){
                        $result[$k]["aggregations"][$field]["aggregations"] = $addSelectAggs["aggregations"];
                    }
                }else{
                    $result[$k]["aggregations"][$field]["terms"]["field"] =  $field;
                    $result[$k]["aggregations"][$field] = $result[$k-1];
                }
                if(!empty($sort) && isset($sort[$field])){
                    $result[$k]["aggregations"][$field]["terms"]["order"] = array("_term"=>$sort[$field]);
                }
                $condtion["aggregations"] = $result;
            }
        }
        $cnt = count($result)-1;
        $this->_condition["aggregations"] = $result[$cnt]["aggregations"];
        return $this;
    }

    public function addSelectAggs(){
        $result = array();
        if(isset($this->_condition["_source"]["includes"])){
           foreach($this->_condition["_source"]["includes"] as $key=>$field){
                preg_match_all('/(COUNT|SUM|AVG)\({0,1}([^)]*)\){0,1}?/i',$field,$matches);
                if(!empty($matches[1][0])){
                    $lower = strtolower($matches[1][0]);
                    $field = empty($matches[2][0])? '_index' : $matches[2][0];
                    strpos($lower,'count') !== false  and $lower = 'value_count';
                    $result["aggregations"][$matches[1][0]][$lower] = array("field" => $field);
                }
            }
        }
        return $result;
    }




    public function getCondition()
    {
        return json_encode($this->_condition);
    }



}
