<?php
//class GoodsController extends PublicController
class GoodsController extends Yaf_Controller_Abstract
{
    private $input;

    public function init()
    {
        $this->input = json_decode(file_get_contents("php://input"), true);
        $this->lang = isset($this->input['lang']) ? strtolower($this->input['lang']) : (browser_lang() ? browser_lang() : 'en');
        if(!in_array($this->lang,array('en','ru','es','zh'))){
            $this->lang = 'en';
        }

    }

    /**
     * 商品（sku）基本信息  --- 公共接口
     * @author link 2017-06-26
     */
    public function infoBaseAction(){
        if(empty($this->input['sku'])){
            jsonReturn('','1000');
        }

        $goods = new GoodsModel();
        $result = $goods->getInfoBase($this->input);
        if($result){
            jsonReturn(array('data'=>$result));
        }else{
            jsonReturn('',400,'');
        }
    }

    /**
     * sku仅属性-详情-app
     */
    public function attrInfoAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if(!empty($data['sku'])){
            $sku = $data['sku'];
        } else{
            jsonReturn('','-1001','sku不可以为空');
        }
        if(!empty($data['lang'])){
            $lang = $data['lang'];
        } else{
            jsonReturn('','-1001','lang不可以为空');
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
            jsonReturn('','-1002','获取失败');
        }
        exit;
    }

    /**
     * sku基本信息编辑p
     */
    public function infoAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if(!empty($data['sku'])){
            $sku = $data['sku'];
        } else{
            jsonReturn('','-1001','sku不可以为空');
        }
        $lang = isset($data['lang']) ? $data['lang'] : '';
        //获取商品属性
        $goods = new GoodsModel();
        $result = $goods->getInfo($sku,$lang);
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
            jsonReturn('','-1001','sku不可以为空');
        }
        $lang = isset($data['lang']) ? $data['lang'] : '';
        $goods = new GoodsModel();
        $result = $goods->getGoodsInfo($sku,$lang);

        if($result){
            $data = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','获取失败');
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
            jsonReturn('','-1002','失败');
        }
        exit;
    }
    /**
     * 获取公共模板表
     * @author  klp  2017/7/6
     */
    public function getCommonTplAction()
    {
        $lang = !empty($this->input['lang'])? $this->input['lang'] : 'en';
        $goodsTplModel = new GoodsAttrTplModel();
        $result = $goodsTplModel->getCommonAttrTpl($lang);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','数据获取失败');
        }
    }

    /**
     * 获取sku模板表
     * @author  klp  2017/7/6
     */
    public function getGoodsAttrTplAction()
    {
        //$this->input['sku'] = 3303060000010001;//测试
        if(!empty($this->input['sku'])){
            $sku = $this->input['sku'];
        } else{
            jsonReturn('','-1001','sku不可以为空');
        }
        $lang = !empty($this->input['lang'])? $this->input['lang'] : 'en';
        $goodsTplModel = new GoodsAttrTplModel();
        $result = $goodsTplModel->getGoodsAttrTpl($sku,$lang);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','数据获取失败');
        }
    }
    /**
     * sku新增  -- 门户
     * @author  klp  2017/7-5
     */
    public function addSkuAction()
    {

        $goodsModel = new GoodsModel();
        $result = $goodsModel->createSku($this->input);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
        }
        exit;
    }

    /**
     * sku属性新增  -- 门户
     * @author  klp  2017/7-5
     */
    public function addSkuAttrAction()
    {

        $goodsAttrModel = new GoodsAttrModel();
        $result = $goodsAttrModel->createAttrSku($this->input);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
        }
        exit;
    }

    /**
     * sku附件新增  -- 门户
     * @author  klp  2017/7-5
     */
    public function addSkuAttachAction()
    {

        $goodsAttachModel = new GoodsAttachModel();
        $result = $goodsAttachModel->createAttachSku($this->input);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
        }
        exit;
    }

    /**
     * sku更新  -- 门户
     * @author  klp  2017/7-5
     * sku lang
     */
    public function updateSkuAction()
    {

        $goodsModel = new GoodsModel();
        $result = $goodsModel->updateSku($this->input);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
        }
        exit;
    }

    /**
     * sku属性更新  -- 门户
     * @author  klp  2017/7-5
     */
    public function updateSkuAttrAction()
    {
        //$this->input = $this->test();//测试
        $goodsAttrModel = new GoodsAttrModel();
        $result = $goodsAttrModel->updateAttrSku($this->input);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
        }
        exit;
    }

    /**
     * sku附件更新  -- 门户
     * @author  klp  2017/7-5
     */
    public function updateSkuAttachAction()
    {
        //$this->input = $this->test();//测试
        $goodsAttachModel = new GoodsAttachModel();
        $result = $goodsAttachModel->updateAttachSku($this->input);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
        }
        exit;
    }

    /**
     * sku状态更改及删除  -- 门户
     * @author  klp  2017/7-5
     * sku lang
     */
    public function modifySkuAction()
    {
        //$this->input = $this->test();//测试
        $goodsModel = new GoodsModel();
       if(isset($this->input['status']) && !empty($this->input['status'])){
           $result = $goodsModel->modifySku($this->input);//状态更改
       } else{
           $result = $goodsModel->deleteRealSku($this->input);//真实删除
       }
        if($result){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
        }
        exit;
    }

    /**
     * sku属性状态更改及删除   -- 门户
     * @author  klp  2017/7-5
     */
    public function modifySkuAttrAction()
    {
        //$this->input = $this->test();//测试
        $goodsAttrModel = new GoodsAttrModel();
        if(isset($this->input['status']) && !empty($this->input['status'])){
            $result = $goodsAttrModel->modifySkuAttr($this->input);//状态更改
        } else{
            $result = $goodsAttrModel->deleteRealAttr($this->input);//真实删除
        }
        if($result){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
        }
        exit;
    }

    /**
     * sku附件状态更改及删除   -- 门户
     * @author  klp  2017/7-5
     */
    public function modifySkuAttachAction()
    {
        //$this->input = $this->test();//测试
        $goodsAttachModel = new GoodsAttachModel();
        if(isset($this->input['status']) && !empty($this->input['status'])){
            $result = $goodsAttachModel->modifySkuAttach($this->input);//状态更改
        } else{
            $result = $goodsAttachModel->deleteRealAttach($this->input);//真实删除
        }
        if($result){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
        }
        exit;
    }

    /**
     * sku供应商信息  -- 门户
     * @author  klp  2017/7-6
     */
    public function getSupplierInfoAction()
    {
        $SupplierAccountModel = new SupplierAccountModel();
        $result = $SupplierAccountModel->getInfo($this->input);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','数据获取失败');
        }
        exit;
    }

    public function test()
    {
        $data = [
            "sku" => "sku001",
            "spu" => "spu001",
            "lang" => "en",
            'model'=> 'model',
            'sku_name'=> 'sku_name2',
            'show_name'=> 'sku00002',
                'goods_flag'=> [
                    'attr1'=> 'attr11',
                    'attr2'=> 'attr22',
                ],
                'spec_flag'=> [
                    'attr4'=> 'attr44',
                    'attr5'=> 'attr55',
                ],
                'logi_flag'=> [
                    'attr6'=> 'attr66',
                    'attr7'=> 'attr77',
                ],
                'hs_flag'=> [
                    'attr8'=> 'attr88',
                    'attr9'=> 'attr99',
                ],
                'BIG_IMAGE'=>[
                    'name1'=> '/2016/12/12ad567b-6243-434f-ab12-334a4b54edb6.jpg',
                    'name2'=> '/2016/12/12ad567b-6243-434f-ab12-334a4b54edb7.jpg',
                    'name3'=> '/2016/12/12ad567b-6243-434f-ab12-334a4b54edb8.jpg',
                ],
        ];

        $up = [
            "sku" => "sku001",
            "spu" => "spu001",
            "lang" => "en",
            'model'=> 'model',
            'sku_name'=> 'sku_name2',
            'show_name'=> 'sku00002',
            'goods_flag'=> [
                0=>[
                    'id'=> 1,
                    'attr_name'=> 'attr1111',
                    'attr_value'=> 'attr1111',
                ],
                1=>[
                    'id'=> 2,
                    'attr_name'=> 'attr1222',
                    'attr_value'=> 'attr1222',
                ],

            ],
            'spec_flag'=> [
                0=>[
                    'id'=> 1,
                    'attr_name'=> 'attr22',
                    'attr_value'=> 'attr222',
                ],
                1=>[
                    'id'=> 2,
                    'attr_name'=> 'attr33',
                    'attr_value'=> 'attr333',
                ],
            ],
            'logi_flag'=> [
                0=>[
                    'id'=> 1,
                    'attr_name'=> 'attr444',
                    'attr_value'=> 'attr4441',
                ],
                1=>[
                    'id'=> 2,
                    'attr_name'=> 'attr555',
                    'attr_value'=> 'attr6666',
                ],
            ],
            'hs_flag'=> [
                0=>[
                    'id'=> 1,
                    'attr_name'=> 'attr63',
                    'attr_value'=> 'attr767',
                ],
                1=>[
                    'id'=> 2,
                    'attr_name'=> 'attr454',
                    'attr_value'=> 'attr7879',
                ],
            ],
            'BIG_IMAGE'=>[
                0=>[
                    'id'=> 1,
                    'attach_name'=> 'attr767',
                    'attach_url'=> '/2016/12/12ad567b-6243-434f-ab12-334a4b54edb6.jpg',
                ],
                1=>[
                    'id'=> 2,
                    'attach_name'=> 'attr7',
                    'attach_url'=> '/2016/12/12ad567b-6243-434f-ab12-334a4b54edb7.jpg',
                ],
                2=>[
                    'id'=> 3,
                    'attach_name'=> 'attr9',
                    'attach_url'=> '/2016/12/12ad567b-6243-434f-ab12-334a4b54edb8.jpg',
                ],
            ],
        ];
        $del = [
            "sku" => "sku001",
            "spu" => "spu001",
            "status" => "INVALID",
        ];
        //return $data;
        //return $up;
        return $del;
    }

}