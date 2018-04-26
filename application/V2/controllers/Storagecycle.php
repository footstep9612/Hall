<?php
/**
 * 仓库物流时效管理
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/26
 * Time: 9:29
 */
class StoragecycleController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 新加仓库物流时效
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货仓库
     */
    public function CreateAction() {
        $storage_id = $this->getPut('storage_id');
        if (empty($storage_id)) {
            jsonReturn('',MSG::ERROR_PARAM,'请选择仓库！');
        }
        $spu = $this->getPut('spu');
        if (empty($spu)) {
            jsonReturn('',MSG::ERROR_PARAM,'请输入spu！');
        }
        $sku = $this->getPut('sku');
        if (empty($sku)) {
            jsonReturn('',MSG::ERROR_PARAM,'请输入sku！');
        }
        $to_country_bn = $this->getPut('to_country_bn');
        if (empty($to_country_bn)) {
            jsonReturn('',MSG::ERROR_PARAM,'请输入to_country_bn！');
        }
        $to_city = $this->getPut('to_city');
        if (empty($to_city)) {
            jsonReturn('',MSG::ERROR_PARAM,'请输入to_city！');
        }
        $cycle = $this->getPut('cycle');
        if (empty($cycle)) {
            jsonReturn('',MSG::ERROR_PARAM,'请输入cycle！');
        }

        $storagecycleModel = new StorageCycleModel();
        $flag = $storagecycleModel->createData($this->getPut());

        if ($flag) {
            $this->jsonReturn($flag);
        } elseif ($flag === false) {
            $this->jsonReturn('',MSG::MSG_FAILED);
        }
    }

    /**
     * 更新仓库物流时效
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货仓库
     */
    public function updateAction(){
        $id = $this->getPut('id');
        if (empty($id)) {
            jsonReturn('',MSG::ERROR_PARAM,'id不能为空！');
        }
        $storageModel = new StorageCycleModel();
        $flag = $storageModel->updateData($this->getPut());

        if ($flag) {
            $this->jsonReturn($flag);
        } elseif ($flag === false) {
            $this->jsonReturn('',MSG::MSG_FAILED);
        }
    }

    /**
     * 删除仓库物流时效
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货仓库
     */
    public function deleteAction(){
        $id = $this->getPut('id');
        if (empty($id)) {
            jsonReturn('',MSG::ERROR_PARAM,'id不能为空！');
        }
        $storageModel = new StorageCycleModel();
        $flag = $storageModel->deleteData($this->getPut());

        if ($flag) {
            $this->jsonReturn($flag);
        } elseif ($flag === false) {
            $this->jsonReturn('',MSG::MSG_FAILED);
        }
    }

}