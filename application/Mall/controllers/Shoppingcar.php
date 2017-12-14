<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/9
 * Time: 21:16
 */
class ShoppingcarController extends PublicController{
    public function init(){
        //parent::init();
    }

    /**
     * 详情列表
     */
    public function listAction(){
        $input = $this->getPut();
        if(empty($input['buyer_id']) || empty($input['lang'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM , '语言跟用户id不能为空');
        }

        $condition = ['buyer_id' => $input['buyer_id'], 'lang' => $input['lang']];

        $scModel = new ShoppingCarModel();
        $result = $scModel->myShoppingCar($condition);
        if($result !== false){
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * sku列表信息
     */
    public function skusAction(){
        $input = $this->getPut();
        if(empty($input['skus']) || !is_array($input['skus']) || empty($input['lang'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM , '语言跟skus不能为空');
        }

        $condition = ['sku' => ['in', $input['skus']], 'lang' => $input['lang']];

        $scModel = new ShoppingCarModel();
        $result = $scModel->myShoppingCar($condition);
        if($result !== false){
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 创建
     */
    public function createAction(){
        $input = $this->getPut();
        $scModel = new ShoppingCarModel();
        $result = $scModel->edit($input);
        if($result !== false){
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 删除
     */
    public function deleteAction(){
		$input = $this->getPut();
        $scModel = new ShoppingCarModel();
        $result = $scModel->del($input);
        if($result !== false){
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }
}