<?php
/**
 * 专题分类
 * User: linkai
 * Date: 2018/3/1
 * Time: 9:43
 */
class SpecialcategoryController extends PublicController{
    public function init() {
        $this->token = false;
    }

    /**
     * 分类详情
     */
    public function infoAction(){
        $input = $this->getPut();
        $model = new SpecialCategoryModel();
        $rel = $model->getInfo($input);
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('',MSG::MSG_FAILED);
        }
    }

    /**
     * 分类列表
     */
    public function listAction(){
        $input = $this->getPut();
        $model = new SpecialCategoryModel();
        $rel = $model->getList($input);
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('',MSG::MSG_FAILED);
        }
    }
}