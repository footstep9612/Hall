<?php
/**
 * 品类管理
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/07/18
 * Time: 11:50
 */
class SpecialgroupController extends PublicController{
    public function init(){
        parent::init();
    }

    /**
     * 列表
     */
    public function listAction(){
        $input = $this->getPut();
        $model = new SpecialGroupModel();
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
        $model = new SpecialGroupModel();
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
        $model = new SpecialGroupModel();
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
        $model = new SpecialGroupModel();
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
        $model = new SpecialGroupModel();
        $rel = isset($input['id']) ? $model->updateData($this->getPut()) : $model->createData($this->getPut());
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('', MSG::MSG_FAILED);
        }
    }

    /**
     * 排序
     */
    public function sortorderAction(){
        $model = new SpecialGroupModel();
        $rel = $model->sortOrder($this->getPut());
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
        $model = new SpecialGroupModel();
        $rel = $model->deleteData($this->getPut());
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('', MSG::MSG_FAILED);
        }
    }
}