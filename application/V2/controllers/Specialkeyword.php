<?php
/**
 * 专题关键词
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/6/6
 * Time: 11:50
 */
class SpecialkeywordController extends PublicController{
    public function init(){
        parent::init();
    }

    /**
     * 列表
     */
    public function listAction(){
        $input = $this->getPut();
        $model = new SpecialKeywordModel();
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
        $model = new SpecialKeywordModel();
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
        $model = new SpecialKeywordModel();
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
        $model = new SpecialKeywordModel();
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
        $model = new SpecialKeywordModel();
        $rel = $model->deleteData($this->getPut());
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('', MSG::MSG_FAILED);
        }
    }
}