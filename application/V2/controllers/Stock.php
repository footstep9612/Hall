<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of 现货
 * @author  zhongyg
 * @date    2017-12-6 9:07:59
 * @version V2.0
 * @desc
 */
class StockController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }

    /**
     * Description of 获取现货列表
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function ListAction() {
        $condition = $this->getPut();
        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $stock_model = new StockModel();
        $list = $stock_model->getList($condition, $lang);
        if ($list) {
            $this->jsonReturn($list);
        } elseif ($list === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 获取现货详情
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function InfoAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }

        $sku = $this->getPut('sku');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择商品!');
            $this->jsonReturn();
        }
        $stock_model = new StockModel();

        $list = $stock_model->getInfo($country_bn, $lang, $sku);
        if ($list) {
            $this->jsonReturn($list);
        } elseif ($list === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 新加现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function CreateAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $skus = $this->getPut('skus');
        if (empty($skus)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择现货商品!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $stock_model = new StockModel();
        $flag = $stock_model->createData($country_bn, $skus, $lang);

        if ($flag) {
            $this->jsonReturn();
        } elseif ($flag === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('更新失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function deletedAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $skus = $this->getPut('skus');
        if (empty($skus)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择现货商品!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }
        $stock_model = new StockModel();
        $list = $stock_model->deleteData($country_bn, $skus, $lang);
        if ($list) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('删除成功!');
            $this->jsonReturn();
        } elseif ($list === FALSE) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('删除失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function setingPricesAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $sku = $this->getPut('sku');
        if (empty($sku)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择现货商品!');
            $this->jsonReturn();
        }
        $lang = $this->getPut('lang');
        if (empty($lang)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择语言!');
            $this->jsonReturn();
        }

        $cost_prices = $this->getPut('cost_prices');
        if (empty($cost_prices)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请添加价格信息!');
            $this->jsonReturn();
        }
        $stock_cost_price_model = new StockCostPriceModel();


        $list = $stock_cost_price_model->updateDatas($country_bn, $lang, $sku, $cost_prices);
        if ($list) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('更新成功!');
            $this->jsonReturn();
        } elseif ($list === FALSE) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('更新失败!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function getPricesAction() {
        $country_bn = $this->getPut('country_bn');
        if (empty($country_bn)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $sku = $this->getPut('sku');
        if (empty($sku)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择现货商品!');
            $this->jsonReturn();
        }


        $stock_cost_price_model = new StockCostPriceModel();


        $list = $stock_cost_price_model->getList($country_bn, $sku);
        if ($list) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setMessage('获取成功!');
            $this->jsonReturn($list);
        } elseif ($list === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('空数据!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('系统错误!');
            $this->jsonReturn();
        }
    }

}
