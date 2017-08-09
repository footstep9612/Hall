<?php

class EditGoodsController extends PublicController
{
    private $lang;
    public function init()
    {
        $lang = $this->getRequest()->getPost("lang");
        $this->lang = empty($lang) ?  'en': strtolower($lang);
        if(!in_array($this->lang,array('en','ru','es','zh'))){
            $this->lang = 'en';
        }
    }

    /**
     * sku查询数据编辑
     */
    public function getInfoAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['sku'])){
            $where['sku'] = $data['sku'];
        } else{
            echo json_encode(array("code" => "-101", "message" => "sku不可以都为空"));
            exit();
        }
        $goods = new GoodsAttrModel();
        $result = $goods->editInfo($where,$this->lang);
        if($result){
            echo json_encode(array("code" => "01", "message" => "获取数据成功", "data"=>$result));
        }else{
            echo json_encode(array("code" => "-101", "message" => "获取数据失败"));
        }
        exit;
    }
    /**
     * sku查看详情
     */
    public function showGoodsAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if(!empty($data['sku'])){
            $where['sku'] = $data['sku'];
        } else{
            echo json_encode(array("code" => "-101", "message" => "sku不可以都为空"));
            exit();
        }
        $goods = new GoodsAttrModel();
        $result = $goods->getInfoBySku($where,$this->lang);
        if($result){
            echo json_encode(array("code" => "01", "message" => "获取数据成功", "data"=>$result));
        }else{
            echo json_encode(array("code" => "-101", "message" => "获取数据失败"));
        }
        exit;
    }

}