<?php
class GoodsController extends PublicController
//class GoodsController extends Yaf_Controller_Abstract
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
        $this->returnInfo($result);
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
        $this->returnInfo($result);
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
        $this->returnInfo($result);
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

        $this->returnInfo($result);
    }

    /**
     * sku管理列表
     * @author
     */
    public function listAction()
    {
        $goodsModel = new GoodsModel();
        $result = $goodsModel->getList($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku新增/编辑  -- 总接口
     * @author  klp  2017/7-13
     */
    public function editSkuAction()
    {
        $this->input = $this->test();//测试
        $goodsModel = new GoodsModel();
        $result = $goodsModel->editSkuInfo($this->input);
        $this->returnInfo($result);
    }
    /**
     * sku状态更改(审核)/删除  -- 总接口
     * @author  klp  2017/7-13
     */
    public function modifySkuAction()
    {
        if(empty($this->input)){
            return false;
        }
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $this->input['update_by'] = $userInfo['name'];
        $goodsModel = new GoodsModel();
        if(isset($this->input['status_type']) && !empty($this->input['status_type'])){
            $result = $goodsModel->modify($this->input);    //状态更改(暂为报审)
        } else{
            $result = $goodsModel->deleteReal($this->input);//真实删除
        }
        $this->returnInfo($result);
    }

    /**
     * sku新增  -- 门户
     * @author  klp  2017/7-5
     */
    public function addSkuAction()
    {

        $goodsModel = new GoodsModel();
        $result = $goodsModel->createSku($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku属性新增  -- 门户
     * @author  klp  2017/7-5
     */
    public function addSkuAttrAction()
    {

        $goodsAttrModel = new GoodsAttrModel();
        $result = $goodsAttrModel->createAttrSku($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku附件新增  -- 门户
     * @author  klp  2017/7-5
     */
    public function addSkuAttachAction()
    {

        $goodsAttachModel = new GoodsAttachModel();
        $result = $goodsAttachModel->createAttachSku($this->input);
        $this->returnInfo($result);
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
        $this->returnInfo($result);
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
        $this->returnInfo($result);
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
        $this->returnInfo($result);
    }

    /**
     * sku状态更改及删除  -- 门户
     * @author  klp  2017/7-5
     * sku lang
     */
    public function changSkuAction()
    {
        //$this->input = $this->test();//测试
        $goodsModel = new GoodsModel();
       if(isset($this->input['status']) && !empty($this->input['status'])){
           $result = $goodsModel->modifySku($this->input);//状态更改
       } else{
           $result = $goodsModel->deleteRealSku($this->input);//真实删除
       }
        $this->returnInfo($result);
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
        $this->returnInfo($result);
    }

    /**
     * sku附件状态更改及删除   -- 门户
     * @author  klp  2017/7-5
     */
    public function deleteSkuAttachAction()
    {

        $goodsAttachModel = new GoodsAttachModel();
        if(isset($this->input['status']) && !empty($this->input['status'])){
            $result = $goodsAttachModel->modifySkuAttach($this->input);//状态更改
        } else{
            $result = $goodsAttachModel->deleteRealAttach($this->input);//真实删除
        }
        $this->returnInfo($result);
    }

    /**
     * sku供应商信息  -- 门户      待完善
     * @author  klp  2017/7-6
     */
    public function getSupplierInfoAction()
    {
        $SupplierAccountModel = new SupplierAccountModel();
        $result = $SupplierAccountModel->getInfo($this->input);
        $this->returnInfo($result);
    }
    //统一回复调用方法
    function returnInfo($result){
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

    public function test()
    {
        $data = [
            'code' => MSG::MSG_SUCCESS,
            'message' => MSG::getMessage(MSG::MSG_SUCCESS),

            'en'=>[
                "spu" => "spu007",
                "lang" => "en",
                'model'=> 'model7',
                'name'=> 'sku_name7',
                'show_name'=> 'sku00007',
                'attrs'=> [
                    0=>[
                        'attr_name'=> 'attr1111',
                        'attr_value'=> 'attr1111',
                        'goods_flag'=> 'Y',
                    ],
                   /* 1=>[
                        'attr_name'=> 'attr1222',
                        'attr_value'=> 'attr1222',
                        'spec_flag'=> 'Y',
                    ],
                    2=>[
                        'attr_name'=> 'attr1222',
                        'attr_value'=> 'attr1222',
                        'logi_flag'=> 'Y',
                    ],
                    3=>[
                        'attr_name'=> 'attr1222',
                        'attr_value'=> 'attr1222',
                        'hs_flag'=> 'Y',
                    ],*/

                ],
            ],
             'attachs'=>[
                0=>[
                    'attach_name'=> 'attr767',
                    'attach_url'=> '/2016/12/12ad567b-6243-434f-ab12-334a4b54edb6.jpg',
                    'attach_type'=> 'BIG_IMAGE',
                ],
                /*  1=>[
                      'attach_name'=> 'attr7',
                      'attach_url'=> '/2016/12/12ad567b-6243-434f-ab12-334a4b54edb7.jpg',
                      'attach_type'=> 'BIG_IMAGE',
                  ],
                  2=>[
                      'attach_name'=> 'attr9',
                      'attach_url'=> '/2016/12/12ad567b-6243-434f-ab12-334a4b54edb8.jpg',
                      'attach_type'=> 'SMALL_IMAGE',
                  ],*/
            ],
        ];

        $up = [
            "sku" => "sku003",
            'en'=>[
                "spu" => "spu004",
                "lang" => "en",
                'model'=> 'model2',
                'name'=> 'sku_name4',
                'show_name'=> 'sku00004',
                'attrs'=> [
                    0=>[
                        'id'=> 16,
                        'attr_name'=> 'attr1122',
                        'attr_value'=> 'attr1122',
                        'goods_flag'=> 'Y',
                    ],
                    1=>[
                        'id'=> 17,
                        'attr_name'=> 'attr3344',
                        'attr_value'=> 'attr3344',
                        'spec_flag'=> 'Y',
                    ],
                    2=>[
                        'id'=> 18,
                        'attr_name'=> 'attr5522',
                        'attr_value'=> 'attr4422',
                        'logi_flag'=> 'Y',
                    ],
                    3=>[
                        'id'=> 2,
                        'attr_name'=> 'attr6622',
                        'attr_value'=> 'attr7722',
                        'hs_flag'=> 'Y',
                    ],

                ],
            ],
            'attachs'=>[
                0=>[
                    'attach_name'=> 'attr767',
                    'attach_url'=> '/2016/12/12ad567b-6243-434f-ab12-334a4b54edb6.jpg',
                    'attach_type'=> 'BIG_IMAGE',
                ],
                1=>[
                    'attach_name'=> 'attr7',
                    'attach_url'=> '/2016/12/12ad567b-6243-434f-ab12-334a4b54edb7.jpg',
                    'attach_type'=> 'BIG_IMAGE',
                ],
                2=>[
                    'attach_name'=> 'attr9',
                    'attach_url'=> '/2016/12/12ad567b-6243-434f-ab12-334a4b54edb8.jpg',
                    'attach_type'=> 'SMALL_IMAGE',
                ],
            ],
        ];
        $del = [
            "sku" => "sku001",
            "spu" => "spu001",
            "status" => "INVALID",
        ];
        return $data;
        //return $up;
        //return $del;
    }

}