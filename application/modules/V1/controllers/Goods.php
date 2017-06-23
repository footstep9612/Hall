<?php
class GoodsController extends PublicController
{
    private $lang;
    private $input;
    public function init()
    {
        $this->input = json_decode(file_get_contents("php://input"), true);
        $lang = $this->input['lang'];
        $this->lang = empty($lang) ?  'en': strtolower($lang);
        if(!in_array($this->lang,array('en','ru','es','zh'))){
            $this->lang = 'en';
        }

    }

    /**
     * sku仅属性-详情-app
     */
    public function attrInfoAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['sku'] = 'sku001';$data['lang'] = 'en';
        if(!empty($data['sku'])){
            $sku = $data['sku'];
        } else{
            jsonReturn('','-1003','sku不可以为空');
        }
        if(!empty($data['lang'])){
            $lang = $data['lang'];
        } else{
            jsonReturn('','-1003','lang不可以为空');
        }
        $goods = new GoodsAttrModel();
        $result = $goods->attrBySku($sku,$lang);

        if(!empty($result)){
            $data = array(
                'code' => '1',
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn(array('code' => -1001, 'message' => '获取失败'));
        }
        exit;
    }
    /**
     * sku基本信息p
     */
    public function infoAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if(!empty($data['sku'])){
            $sku = $data['sku'];
        } else{
            jsonReturn(array("code" => "-1002", "message" => "sku不可以为空"));
        }
        //获取商品属性
        $goods = new GoodsModel();
        $result = $goods->getInfo($sku,$this->lang);
        if(!empty($result)){
            $data = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn(array('code' => -1003, 'message' => '获取失败'));
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
            jsonReturn(array("code" => "-1002", "message" => "sku不可以为空"));
        }
        $goods = new GoodsModel();
        $result = $goods->getGoodsInfo($sku,$this->lang);

        if($result){
            $data = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn(array('code' => -1002, 'message' => '获取失败'));
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

    /**
     * sku新建插入(pc)
     * @author  klp  2017/6/22
     */
    public function createAction()
    {
        $goodsModel = new GoodsModel();
        $result = $goodsModel->create_data($this->create_data,$this->username);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '新增成功'
            );
        } else{
            $data = array(
                'code' => -1008,
                'message' => '新增失败'
            );
        }
        jsonReturn($data);
    }

    /**
     * sku编辑更新(pc)
     * @author  klp  2017/6/22
     */
    public function updateAction()
    {
        $goodsModel = new GoodsModel();
        $result = $goodsModel->create_data($this->create_data,$this->username);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '新增成功'
            );
        } else{
            $data = array(
                'code' => -1008,
                'message' => '新增失败'
            );
        }
        jsonReturn($data);
    }

}