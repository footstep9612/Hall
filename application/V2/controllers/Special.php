<?php
/**
 * 专题
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/6/6
 * Time: 11:50
 */
class SpecialController extends PublicController{
    public function init(){
        parent::init();
    }

    /**
     * 列表
     */
    public function listAction(){
        $input = $this->getPut();
        $model = new SpecialModel();
        $rel = $model->getList($input);
        if($rel===false){
            jsonReturn('', MSG::MSG_FAILED);
        }else{
            jsonReturn($rel);
        }
    }

    /**
     * 详情
     */
    public function infoAction(){
        $id = $this->getPut('id','');
        $model = new SpecialModel();
        $rel = $model->getInfo($id);
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('', MSG::MSG_FAILED);
        }
    }

    /**
     * 增
     */
    public function createAction(){
        $model = new SpecialModel();
        $rel = $model->createData($this->getPut());
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('', MSG::MSG_FAILED);
        }
    }

    /**
     * 改
     */
    public function updateAction(){
        $model = new SpecialModel();
        $rel = $model->updateData($this->getPut());
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('', MSG::MSG_FAILED);
        }
    }

    /**
     * 新增/修改
     */
    public function editAction(){
        $input = $this->getPut();
        $model = new SpecialModel();
        $rel = isset($input['id']) ? $model->updateData($this->getPut()) : $model->createData($this->getPut());
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('', MSG::MSG_FAILED);
        }
    }

    /**
     * 删
     */
    public function deleteAction(){
        $model = new SpecialModel();
        $rel = $model->deleteData($this->getPut());
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('', MSG::MSG_FAILED);
        }
    }
}