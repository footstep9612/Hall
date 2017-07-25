<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/7/20
 * Time: 9:34
 */
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
        //$this->input = $this->test();//测试
        $goodsModel = new GoodsModel();
        $result = $goodsModel->editSkuInfo($this->input);
        $this->returnInfo($result);
    }
    /**
     * sku状态更改(审核)/删除  -- 总接口
     * @param    status_type  sku_id  spu_id
     * @author  klp  2017/7-13
     */
    public function modifySkuAction()
    {
        if(empty($this->input)){
            return false;
        }
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $this->input['checked_by'] = $userInfo['name'];
        $goodsModel = new GoodsModel();
        if(isset($this->input['status_type']) && !empty($this->input['status_type'])){
            $result = $goodsModel->modify($this->input);    //状态更改(暂为报审)
        } else{
            $result = $goodsModel->deleteReal($this->input);//真实删除
        }
        $this->returnInfo($result);
    }


    /**
     * sku新增 (单独) -- 门户
     * @author  klp  2017/7-5
     */
    public function addSkuAction()
    {

        $goodsModel = new GoodsModel();
        $result = $goodsModel->createSku($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku属性新增 (单独) -- 门户
     * @author  klp  2017/7-5
     */
    public function addSkuAttrAction()
    {

        $goodsAttrModel = new GoodsAttrModel();
        $result = $goodsAttrModel->createAttrSku($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku附件新增 (单独) -- 门户
     * @author  klp  2017/7-5
     */
    public function addSkuAttachAction()
    {

        $goodsAttachModel = new GoodsAttachModel();
        $result = $goodsAttachModel->createAttachSku($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku更新  (单独)-- 门户
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
     * sku属性更新 (单独) -- 门户
     * @author  klp  2017/7-5
     */
    public function updateSkuAttrAction()
    {
        $goodsAttrModel = new GoodsAttrModel();
        $result = $goodsAttrModel->updateAttrSku($this->input);
        $this->returnInfo($result);
    }

    /**
     * sku状态更改及删除 (单独) -- 门户
     * @author  klp  2017/7-5
     * sku lang
     */
    public function changSkuAction()
    {
        $goodsModel = new GoodsModel();
       if(isset($this->input['status']) && !empty($this->input['status'])){
           $result = $goodsModel->modifySku($this->input);//状态更改
       } else{
           $result = $goodsModel->deleteRealSku($this->input);//真实删除
       }
        $this->returnInfo($result);
    }

    /**
     * sku属性状态更改及删除 (单独)  -- 门户
     * @author  klp  2017/7-5
     */
    public function modifySkuAttrAction()
    {
        $goodsAttrModel = new GoodsAttrModel();
        if(isset($this->input['status']) && !empty($this->input['status'])){
            $result = $goodsAttrModel->modifySkuAttr($this->input);//状态更改
        } else{
            $result = $goodsAttrModel->deleteRealAttr($this->input);//真实删除
        }
        $this->returnInfo($result);
    }

    /**
     * sku附件状态更改及删除 (单独)  -- 门户
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

    /**
     *   通过spu查询四种语言name
     * @author  klp  2017/7-22
     */
    public function getNameAction()
    {
        $productModel = new ProductModel();
        $result = $productModel->getName($this->put_data);
        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
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

}