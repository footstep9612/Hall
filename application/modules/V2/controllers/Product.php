<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/7/21
 * Time: 15:40
 */
class ProductController extends PublicController{
    public function init() {
        $this->put_data = $this->put_data ? $this->put_data : $_POST;
        parent::init();
    }

    /**
     * 基本详情信息
     */
    public function infoAction(){
        if(!isset($this->put_data['spu']) || empty($this->put_data['spu'])){
            jsonReturn('','1000','参数[spu]有误');
        }
        $lang = !empty($this->put_data['lang']) ? $this->put_data['lang'] : '';
        if($lang != '' && !in_array($lang,array('zh','en','es','ru'))) {
            jsonReturn('','1000','参数[语言]有误');
        }
        $status = isset($this->put_data['status'])?strtoupper($this->put_data['status']):'';
        if($status != '' && !in_array($status,array('NORMAL','CLOSED','VALID','TEST','CHECKING','INVALID','DELETED'))) {
            jsonReturn('','1000','参数[状态]有误');
        }

        $productModel = new ProductModel();
        $result = $productModel->getInfo($this->put_data['spu'], $lang,$status);
        if (!empty($result)) {
            $data = array(
                'data' => $result
            );
            jsonReturn($data);
        } else {
            jsonReturn('',  ErrorMsg::FAILED);
        }
        exit;
    }

    /**
     * 产品添加/编辑
     */
    public function editAction() {
        $productModel = new ProductModel();
        $productModel->setModule(Yaf_Controller_Abstract::getModuleName());

        $result = $productModel->editInfo($this->put_data);
        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
        exit;
    }

    /**
     * SPU删除
     */
    public function deleteAction(){
        if(!isset($this->put_data['spu']))
            jsonReturn('',ErrorMsg::ERROR_PARAM);

        if(!isset($this->put_data['lang']))
            jsonReturn('',ErrorMsg::ERROR_PARAM);

        $productModel = new ProductModel();
        $result = $productModel->upStatus($this->put_data['spu'],$this->put_data['lang'],$productModel::STATUS_DELETED);
        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 修改
     */
    public function updateAction(){
        if(!isset($this->put_data['update_type']))
            jsonReturn('', ErrorMsg::ERROR_PARAM);

        if(!isset($this->put_data['spu']))
            jsonReturn('',ErrorMsg::ERROR_PARAM);

        if(!isset($this->put_data['lang']))
            jsonReturn('',ErrorMsg::ERROR_PARAM);

        $result = '';
        switch($this->put_data['update_type']){
            case 'declare':    //SPU报审
                $productModel = new ProductModel();
                $result = $productModel->upStatus($this->put_data['spu'],$this->put_data['lang'],$productModel::STATUS_CHECKING);
                break;
        }
        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

}
