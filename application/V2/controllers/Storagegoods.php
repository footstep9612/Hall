<?php
/**
 * 仓库商品管理
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/26
 * Time: 9:29
 */
class StoragegoodsController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 新加仓库商品
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货仓库
     */
    public function createAction() {
        $storage_id = $this->getPut('storage_id');
        if (empty($storage_id)) {
            jsonReturn('',MSG::ERROR_PARAM,'请选择仓库！');
        }
        $sku = $this->getPut('sku');
        if (empty($sku)) {
            jsonReturn('',MSG::ERROR_PARAM,'请输入sku！');
        }

        $storagegoodsModel = new StorageGoodsModel();
        $flag = $storagegoodsModel->createData($this->getPut());

        if ($flag) {
            $this->jsonReturn($flag);
        } else{
            $this->jsonReturn('',MSG::MSG_FAILED);
        }
    }

    /**
     * 删除仓库商品
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货仓库
     */
    public function deleteAction(){
        $id = $this->getPut('sku','');
        if(empty($id)){
            $storage_id = $this->getPut('storage_id',0);
            $sku = $this->getPut('sku','');
            if(empty($storage_id) && empty($sku)){
                jsonReturn('',MSG::ERROR_PARAM,'storage_id或sku不能全为空');
            }
        }

        $storagegoodsModel = new StorageGoodsModel();
        $flag = $storagegoodsModel->deleteData($this->getPut());
        if ($flag) {
            $this->jsonReturn($flag);
        } else{
            $this->jsonReturn('',MSG::MSG_FAILED);
        }
    }

    public function listAction(){
        $condition = $this->getPut();
        if(!isset($condition['storage_id'])){
            jsonReturn('',MSG::ERROR_PARAM,'storage_id不能为空');
        }
        $model = new StorageGoodsModel();
        $result = $model->getList($condition);
        if ($result===false) {
            jsonReturn('',MSG::MSG_FAILED);
        } else{
            jsonReturn($result);
        }
    }

}