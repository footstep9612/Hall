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
     * 仓库物流时效详情
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货仓库
     */
    public function infoAction(){
        $id = $this->getPut('id');
        if (empty($id)) {
            jsonReturn('',MSG::ERROR_PARAM,'id不能为空！');
        }
        $storageModel = new StorageCycleModel();
        $flag = $storageModel->getInfo($this->getPut());

        if ($flag) {
            jsonReturn($flag);
        } elseif ($flag === false) {
            jsonReturn('',MSG::MSG_FAILED);
        }
    }

    /**
     * Description of 新加仓库物流时效
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货仓库
     */
    public function createAction() {
        $storage_id = $this->getPut('storage_id');
        if (empty($storage_id)) {
            jsonReturn('',MSG::ERROR_PARAM,'请选择仓库！');
        }
        $data = $this->getPut('data');
        if (empty($data)) {
            jsonReturn('',MSG::ERROR_PARAM,'请输入时效信息');
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
        $storage_id = $this->getPut('storage_id');
        if (empty($storage_id)) {
            jsonReturn('',MSG::ERROR_PARAM,'请选择仓库！');
        }
        $data = $this->getPut('data');
        if (empty($data)) {
            jsonReturn('',MSG::ERROR_PARAM,'请输入时效信息');
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
     * 列表
     */
    public function listAction(){
        $storageModel = new StorageCycleModel();
        $flag = $storageModel->getList($this->getPut());

        if ($flag) {
            jsonReturn($flag);
        } elseif ($flag === false) {
            jsonReturn('',MSG::MSG_FAILED);
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
            jsonReturn($flag);
        } elseif ($flag === false) {
            jsonReturn('',MSG::MSG_FAILED);
        }
    }

}