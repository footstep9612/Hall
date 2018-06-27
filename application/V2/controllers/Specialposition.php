<?php
/**
 * 专题推荐位
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/6/6
 * Time: 11:50
 */
class SpecialpositionController extends PublicController{
    public function init(){
        parent::init();
    }

    /**
     * 列表
     */
    public function listAction(){
        $input = $this->getPut();
        $model = new SpecialPositionModel();
        $rel = $model->getList($input);
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('', MSG::MSG_FAILED);
        }
    }

    /**
     * 详情
     */
    public function infoAction(){
        $id = $this->getPut('id','');
        $model = new SpecialPositionModel();
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
        $model = new SpecialPositionModel();
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
        $model = new SpecialPositionModel();
        $rel = $model->updateData($this->getPut());
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
        $model = new SpecialPositionModel();
        $rel = $model->deleteData($this->getPut());
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('', MSG::MSG_FAILED);
        }
    }
}