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

        $scModel = new ShoppingCarModel();
        $result = $scModel->myShoppingCar();
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