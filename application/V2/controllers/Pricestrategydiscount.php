<?php
/**
 * 商品价格折扣
 * Created by PhpStorm.
 * User: linkai
 * Date: 2018/4/26
 * Time: 9:29
 */
class PricestrategydiscountController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 新增
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货
     */
/*    public function createAction() {
        $sku = $this->getPut('sku');
        if (empty($sku)) {
            jsonReturn('',MSG::ERROR_PARAM,'请输入sku！');
        }
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            jsonReturn('',MSG::ERROR_PARAM,'请输入country_bn！');
        }
        $discount = $this->getPut('discount');
        if (empty($discount)) {
            jsonReturn( '' , MSG::ERROR_PARAM , '输入折扣！' );
        }
        $PriceStrategyDiscountModel = new PriceStrategyDiscountModel();
        $flag = $PriceStrategyDiscountModel->createData($this->getPut());
        if ($flag === false) {
            $this->jsonReturn('',MSG::MSG_FAILED);
        } else {
            $this->jsonReturn($flag);
        }
    }*/

    /**
     * 更新
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货仓库
     */
    /*public function updateAction(){
        $sku = $this->getPut('sku');
        if (empty($sku)) {
            jsonReturn('',MSG::ERROR_PARAM,'sku不能为空！');
        }
        if (empty($this->getPut('country_bn'))) {
            jsonReturn('',MSG::ERROR_PARAM,'country_bn不能为空！');
        }
        $model = new PriceStrategyDiscountModel();
        $flag = $model->updateData($this->getPut());

        if ($flag) {
            $this->jsonReturn($flag);
        } elseif ($flag === false) {
            $this->jsonReturn('',MSG::MSG_FAILED);
        }
    }*/

    /**
     * 删除
     * @author  link
     * @date    2017-12-6 9:12:49
     * @desc   现货仓库
     */
    /*public function deleteAction(){
        if (empty($this->getPut('sku'))) {
            jsonReturn('',MSG::ERROR_PARAM,'sku不能为空！');
        }
        if (empty($this->getPut('country_bn'))) {
            jsonReturn('',MSG::ERROR_PARAM,'country_bn不能为空！');
        }
        $model = new PriceStrategyDiscountModel();
        $flag = $model->deleteData($this->getPut());

        if ($flag) {
            $this->jsonReturn($flag);
        } elseif ($flag === false) {
            $this->jsonReturn('',MSG::MSG_FAILED);
        }
    }*/

}