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
     * 获取推荐商品
     */
    public function infoAction(){
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
     * 推荐商品
     */
    public function goodsAction(){
        $input = $this->getPut();
        $model = new SpecialPositionModel();
        $rel = $model->getList($input);
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('', MSG::MSG_FAILED);
        }
    }

}