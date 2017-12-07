<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SPU关联
 * @author  zhongyg
 * @date    2017-12-6 9:12:49
 * @version V2.0
 * @desc
 */
class ProductrelationController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 获取SPU关联列表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  SPU关联
     */
    public function ListAction() {
        $spu = $this->getPut('spu');
        if (empty($spu)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择SPU!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }


        $current_no = $this->getPut('current_no', 1);
        $pagesize = $this->getPut('pagesize', 10);

        $product_relation_model = new ProductRelationModel();
        $data = $product_relation_model->getList($spu, $lang, ($current_no - 1) * $pagesize, $pagesize);

        if ($data) {
            $count = $product_relation_model->getCont($spu, $lang);
            $this->setvalue('count', $count);
            $this->jsonReturn($data);
        } elseif ($data === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 新加SPU关联维护表
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  SPU关联
     */
    public function CreateAction() {
        $spu = $this->getPut('spu');
        if (empty($spu)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择SPU!');
            $this->jsonReturn();
        }
        $spus = $this->getPut('spus');
        if (empty($spus)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择关联SPU!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $product_relation_model = new ProductRelationModel();

        $flag = $product_relation_model->createData($spu, $spus, $lang);
        if ($flag) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('添加成功!');
            $this->jsonReturn();
        } elseif ($flag === FALSE) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('添加失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 删除SPU关联
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc  SPU关联
     */
    public function DeleteAction() {
        $id = $this->getPut('id');
        if (empty($id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择关联SPU!');
            $this->jsonReturn();
        }
        $product_relation_model = new ProductRelationModel();

        $flag = $product_relation_model->deletedData($id);
        if ($flag) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('删除成功!');
            $this->jsonReturn();
        } elseif ($flag === FALSE) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('删除失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

}
