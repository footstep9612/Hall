<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/7/21
 * Time: 15:40
 */
class ProductController extends PublicController {
    public function init() {
    }

    /**
     * 产品添加/编辑
     */
    public function editAction() {
        $this->put_data['en'] = ['name'=>''];
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
        if(!isset($this->put_data['id']))
            jsonReturn('',ErrorMsg::ERROR_PARAM);

        $productModel = new ProductModel();
        $result = $productModel->del($this->put_data);
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

        if(!isset($this->put_data['id']))
            jsonReturn('',ErrorMsg::ERROR_PARAM);

        $result = '';
        switch($this->put_data['update_type']){
            case 'declare':    //SPU报审
                $productModel = new ProductModel();
                $result = $productModel->upStatus($this->put_data['id'],$productModel::STATUS_CHECKING);
                break;
        }
        if ($result) {
            jsonReturn($result);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

}
