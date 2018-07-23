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
     * 现货更新
     * @author link
     * @date 2018-07-04
     */
    public function updateAction(){
        $condition = $this->getPut();
        if (!isset($condition['id'])) {
            jsonReturn('', MSG::ERROR_PARAM, '请选择现货');
        }
        $model = new StockModel();
        $rel = $model->updateDate($condition);
        if($rel){
            jsonReturn('', MSG::MSG_SUCCESS, '操作成功');
        }else{
            jsonReturn('', MSG::MSG_FAILED, '操作失败');
        }
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
        if(!isset($condition['special_id'])){
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
        }

        $stock_model = new StockModel();
        $list = $stock_model->getList($condition);
        if ($list) {
            $this->_setCountry($list['data']);
            if(isset($condition['strategy']) && $condition['strategy'] && isset($condition['special_id'])){
                //$this->_setConstPrice($list['data']);
                $this->_setStrategy($list['data'],$condition['special_id']);
            }
            if(isset($condition['show_cats']) && $condition['show_cats']){
                if (empty($condition['country_bn'])) {
                    jsonReturn('', MSG::ERROR_PARAM, '请选择国家!');
                }
                $lang = $this->getPut('lang');
                if (empty($lang)) {
                    jsonReturn('', MSG::ERROR_PARAM, '请选择语言!');
                }
                $this->_setShowcats($list['data'], $lang, $condition['country_bn']);
            }
            jsonReturn($list);
        } elseif (empty($list)) {
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
        $input = $this->getPut();
        if(!isset($input['id']) || empty($input['id'])){
            if (empty($input['country_bn'])) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('请选择国家!');
                $this->jsonReturn();
            }
            if (empty($input['lang'])) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('请选择语言!');
                $this->jsonReturn();
            }
            if (empty($input['lang'])) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('请选择商品!');
                $this->jsonReturn();
            }
        }

        $stock_model = new StockModel();
        $list = $stock_model->getInfo( $input );
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
        $special_id = $this->getPut('special_id');
        if (empty($special_id)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('请选择专区!');
            $this->jsonReturn();
        }
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
        $flag = $stock_model->createData($this->getPut());

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
     * Description of 新加现货
     * @author  zhongyg
     * @date    2017-12-6 9:12:49
     * @version V2.0
     * @desc  现货
     */
    public function UpdateStockAction() {
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

        $stock = $this->getPut('stock');
        if (empty($stock)) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('库存不能为空!');
            $this->jsonReturn();
        }
        if (intval($stock) < 0) {
            $this->setCode(MSG::MSG_EXIST);
            $this->setMessage('库存必须是大于零的整数!');
            $this->jsonReturn();
        }
        $stock_model = new StockModel();
        $flag = $stock_model->UpdateStock($country_bn, $sku, $lang, $stock);


        if ($flag) {
            $this->jsonReturn();
        } elseif ($flag === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('系统错误!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('更新失败!');
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
    public function UpdateSortAction() {
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

        $sort_order = $this->getPut('sort_order', 0);

        $stock_model = new StockModel();
        $flag = $stock_model->UpdateSort($country_bn, $sku, $lang, $sort_order);


        if ($flag) {
            $this->jsonReturn();
        } elseif ($flag === false) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('系统错误!');
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage('更新失败!');
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
    public function deleteAction() {
        $condition = $this->getPut();
        if (!isset($condition['id'])) {
            jsonReturn('', MSG::ERROR_PARAM, '请选择商品!');
        }
        $stock_model = new StockModel();
        $list = $stock_model->deleteData($condition);
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

    /**
     * 更新价格策略
     */
    public function updatePSTypeAction(){
        $input = $this->getPut();
        $stockModel = new StockModel();
        $result = $stockModel->updatePriceStrategyType($input);
        if($result!==false){
            jsonReturn($result);
        }else{
            jsonReturn('',MSG::MSG_FAILED);
        }
    }

    /**
     * 更新原价信息
     */
    public function updatePriceAction(){
        $input = $this->getPut();
        $stockModel = new StockModel();
        $result = $stockModel->updatePrice($input);
        if($result!==false){
            jsonReturn($result);
        }else{
            jsonReturn('',MSG::MSG_FAILED);
        }
    }

    /*
     * Description of 获取价格属性
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setConstPrice(&$arr, $country_bn) {
        if ($arr) {

            $skus = [];
            foreach ($arr as $key => $val) {
                $skus[] = $val['sku'];
            }

            $stock_cost_price_model = new StockCostPriceModel();
            $supplier_model = new SuppliersModel();
            $stockcostprices = $stock_cost_price_model->getCostPriceBySkus($skus, $country_bn);

            $supplier_idsBySku = $stock_cost_price_model->getSupplierIds($skus, $country_bn);
            $supplier_ids = [];
            foreach ($supplier_idsBySku as $supplierids) {
                foreach ($supplierids as $supplier_id) {
                    $supplier_ids[] = $supplier_id;
                }
            }

            $suppliers = $supplier_model->getSupplierNameByIds($supplier_ids);
            foreach ($arr as $key => $val) {

                if ($val['spu'] && isset($stockcostprices[$val['sku']])) {
                    if (isset($stockcostprices[$val['sku']])) {
                        $val['costprices'] = $stockcostprices[$val['sku']];
                    }
                } else {
                    $val['costprices'] = '';
                }
                if ($val['spu'] && isset($supplier_idsBySku[$val['sku']])) {
                    $val['supplier_names'] = $this->_getSuppliernames($supplier_idsBySku[$val['sku']], $suppliers);
                } else {
                    $val['supplier_names'] = '';
                }


                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取价格属性
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _getSuppliernames($supplier_ids, $suppliers) {


        foreach ($supplier_ids as $supplier_id) {
            if (isset($suppliers[$supplier_id])) {
                $supplier_names[$supplier_id] = $suppliers[$supplier_id];
            }
        }
        rsort($supplier_names);
        unset($supplier_ids, $suppliers);
        return $supplier_names;
    }

    /*
     * Description of 获取国家
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setCountry(&$arr) {
        if ($arr) {
            $country_model = new CountryModel();
            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = trim($val['country_bn']);
            }

            $countrynames = $country_model->getNamesBybns($country_bns, 'zh');
            foreach ($arr as $key => $val) {
                if (trim($val['country_bn']) && isset($countrynames[trim($val['country_bn'])])) {
                    $val['country_name'] = $countrynames[trim($val['country_bn'])];
                } else {
                    $val['country_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取国家
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setUser(&$arr) {
        $user_ids = [];

        //  $esgoods = new EsGoodsModel();
        foreach ($arr as $key => $item) {
            if ($item['created_by']) {
                $user_ids[] = $item['created_by'];
            }
            if ($item['updated_by']) {
                $user_ids[] = $item['updated_by'];
            }
        }
        $employee_model = new EmployeeModel();
        $usernames = $employee_model->getUserNamesByUserids($user_ids);
        foreach ($arr as $key => $val) {
            if ($val['created_by'] && isset($usernames[$val['created_by']])) {
                $val['created_by_name'] = $usernames[$val['created_by']];
            } else {
                $val['created_by_name'] = '';
            }
            if ($val['updated_by'] && isset($usernames[$val['updated_by']])) {
                $val['updated_by_name'] = $usernames[$val['updated_by']];
            } else {
                $val['updated_by_name'] = '';
            }
            $arr[$key] = $val;
        }
    }

    /*
     * Description of 获取展示分类
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setShowcats(&$arr, $lang, $country_bn) {
        if ($arr) {

            $show_cat_goods_model = new ShowCatGoodsModel();

            $skus = [];
            foreach ($arr as $key => $val) {
                $skus[] = trim($val['sku']);
            }

            $scats = $show_cat_goods_model->getshow_catsbyskus($skus, $lang, $country_bn);
            foreach ($arr as $key => $val) {
                if (trim($val['sku']) && isset($scats[trim($val['sku'])])) {
                    $val['show_cats'] = $scats[trim($val['sku'])];
                } else {
                    $val['show_cats'] = [];
                }
                rsort($val['show_cats']);
                $arr[$key] = $val;
            }
        }
    }

    /**
     * 初始化价格策略详情
     * @author link
     * @param $arr
     * @param $special_id
     * @date 2018-07-06
     */
    private function _setStrategy(&$arr,$special_id){
        if ($arr) {
            $skus = [];
            foreach ($arr as $key => $val) {
                $skus[] = trim($val['sku']);
            }

            $psdmodel = new PriceStrategyDiscountModel();
            $strategy = $psdmodel->getList(['group'=>'STOCK','group_id'=>$special_id,'sku'=>$skus],'min_purchase_qty ASC');
            $strategyAry = [];
            if($strategy){
                foreach($strategy as $key => $item){
                    $strategyAry[$item['sku']][] = $item;
                }
            }

            foreach ($arr as $key => $val) {
                if (trim($val['sku']) && isset($strategyAry[trim($val['sku'])])) {
                    $val['price_range'] = $strategyAry[trim($val['sku'])];
                } else {
                    $val['price_range'] = [];
                }
                $arr[$key] = $val;
            }
        }
    }

}
