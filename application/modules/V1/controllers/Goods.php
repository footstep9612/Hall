<?php
class GoodsController extends PublicController
{
    private $lang;
    private $input;
    public function init()
    {
        $this->input = json_decode(file_get_contents("php://input"), true);

        $lang = $this->getRequest()->getPost("lang");
        $this->lang = empty($lang) ?  'en': strtolower($lang);
        if(!in_array($this->lang,array('en','ru','es','zh'))){
            $this->lang = 'en';
        }

    }

    /**
     * sku属性查询数据编辑p
     */
    public function getInfoAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if(!empty($data['sku'])){
            $where['sku'] = $data['sku'];
        } else{
            echo json_encode(array("code" => "-102", "message" => "sku不可以都为空"));
            exit();
        }
        //获取商品属性
        $goods = new GoodsAttrModel();
        $result = $goods->getAttrBySku($where,$this->lang);

        if($result){
            echo json_encode(array("code" => "0", "message" => "获取数据成功", "data"=>$result));
        }else{
            echo json_encode(array("code" => "-101", "message" => "获取数据失败"));
        }
        exit;
    }
    /**
     * sku查看详情p
     */
    public function showGoodsAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if(!empty($data['sku'])){
            $sku = $data['sku'];
        } else{
            echo json_encode(array("code" => "-101", "message" => "sku不可以都为空"));
            exit();
        }
        $goods = new GoodsModel();
        $result = $goods->getGoodsInfo($sku,$this->lang);

        if($result){
            echo json_encode(array("code" => "0", "message" => "获取数据成功", "data"=>$result));
        }else{
            echo json_encode(array("code" => "-101", "message" => "获取数据失败"));
        }
        exit;
    }


    /**
     * spu列表(pc)
     * @author  link  2017/6/17
     */
    public function listAction()
    {
        $goodsModel = new GoodsModel();
        $result = $goodsModel->getList($this->input);
        if($result){
            jsonReturn($result);
        }else{
            jsonReturn('',400,'失败');
        }
        exit;
    }

}