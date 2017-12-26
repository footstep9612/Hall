<?php
/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/9
 * Time: 21:16
 */
class ShoppingcarController extends PublicController{
    public function init(){
        parent::init();
    }

    /**
     * 详情列表
     */
    public function listAction(){
        $input = $this->getPut();
        if(!isset($this->user['buyer_id']) || empty($this->user['buyer_id']) || empty($input['lang'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM , '语言跟用户id不能为空');
        }
        if(!isset($input['type'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM , 'type不能为空');
        }
        $condition = ['buyer_id' =>$this->user['buyer_id'], 'lang' => $input['lang']];
        $condition['type'] = $input['type'];
        if($condition['type']) {
            if ( !isset( $input[ 'country_bn' ] ) || empty( $input[ 'country_bn' ] ) ) {
                jsonReturn( '' , ErrorMsg::ERROR_PARAM , 'country_bn不能为空' );
            }
        }
        $scModel = new ShoppingCarModel();
        $result = $scModel->myShoppingCar($condition,$input[ 'country_bn' ] ? $input[ 'country_bn' ] : '');
        if($result !== false){
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 购物车/询单车的sku列表信息
     */
    public function skusAction(){
        $input = $this->getPut();
        if(empty($input['skus']) || !is_array($input['skus']) || empty($input['lang'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM , '语言跟skus不能为空');
        }
        if(!isset($this->user['buyer_id']) || empty($this->user['buyer_id'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM , '语言跟buyer_id不能为空');
        }
        if(isset($input['type']) && $input['type']) {
            if ( !isset( $input[ 'country_bn' ] ) || empty( $input[ 'country_bn' ] ) ) {
                jsonReturn( '' , ErrorMsg::ERROR_PARAM , 'country_bn不能为空' );
            }
        }

        $condition = ['sku' => ['in', $input['skus']],'buyer_id'=>$this->user['buyer_id'], 'lang' => $input['lang']];
        if(isset($input['type'])){
            $condition['type'] = $input['type'];
        }
        $scModel = new ShoppingCarModel();
        $result = $scModel->myShoppingCar($condition,$input[ 'country_bn' ] ? $input[ 'country_bn' ] : '');
        if($result !== false){
            jsonReturn($result);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 创建
     * @param string spu
     * @param array skus     ['sku' => 数量]
     * @param string lang
     * @param string buyer_id
     */
    public function createAction(){
        $input = $this->getPut();
        if(!$this->user || !isset($this->user['buyer_id'])){
            jsonReturn('',ErrorMsg::NOLOGIN);
        }
        if(!isset($input['spu']) || empty($input['spu'])){
            jsonReturn('',ErrorMsg::NOTNULL_SPU);
        }

        if(!isset($input['skus']) || empty($input['skus']) || !is_array($input['skus'])){
            jsonReturn('',ErrorMsg::NOTNULL_SKU);
        }

        if(!isset($input['lang']) || empty($input['lang'])){
            jsonReturn('',ErrorMsg::NOTNULL_LANG);
        }

        $scModel = new ShoppingCarModel();
        $result = $scModel->edit($input,$this->user);
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