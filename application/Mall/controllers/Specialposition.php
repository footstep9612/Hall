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
        $this->token = false;
        parent::init();
    }

    /**
     * 推荐商品
     */
    public function goodsAction(){
        $input = $this->getPut();
        $model = new SpecialPositionDataModel();
        $rel = $model->getList($input);
        if($rel){
            jsonReturn($rel);
        }else{
            jsonReturn('', MSG::MSG_FAILED);
        }
    }

}