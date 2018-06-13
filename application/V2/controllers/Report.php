<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Esgoods
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   ES 产品
 */
class ReportController extends PublicController {

    //put your code here
    public function init() {
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);
    }

    public function getPut($name = null, $default = null) {

//        return parent::getPut($name, $default);

        if (!$this->put_data) {
            $key = '9b2a37b7b606c14d43db538487a148c7';
            $input = json_decode(file_get_contents("php://input"), true);


            $sign = md5($key . $input['input']);
//            echo $sign;
            if ($input['sign'] != $sign) {
                $this->setCode(MSG::MSG_FAILED);
                $this->setMessage('验证失败!');
                $this->jsonReturn();
            }
            $data = $this->put_data = json_decode($input['input'], true);
        }
        if ($name) {
            $data = isset($this->put_data [$name]) && !empty($this->put_data [$name]) ? $this->put_data [$name] : $default;
            return $data;
        } else {
            $data = $this->put_data;
            return $data;
        }
    }

    /**
     * 已开发SPU数量
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getProductCountAction() {
        $condition = $this->getPut();




        $lang = $this->getPut('lang', 'zh');
        $condition['status'] = 'ALL';
        $condition['onshelf_flag'] = 'A';
        $esproduct_model = new EsProductModel();

        $total = $esproduct_model->getCount($condition, $lang); //已开发SPU数量
        $this->setvalue('count', $total); //已开发供应商数量

        $condition['status'] = 'DRAFT';
        $DraftCount = $esproduct_model->getCount($condition, $lang); //已驳回供应商数量
        $this->setvalue('draft_count', $DraftCount); //$InvalidCount
        // $this->setvalue('draft_rate', $this->_number_format($DraftCount, $total));

        $this->setvalue('checking_count', $this->_getSpuCount($condition, $lang, 'CHECKING', 'TO_BE_CHANGED')); //待审核供应商数量
        // $this->setvalue('checking_rate', $this->_number_format($CheckingCount, $total));
        //        $condition['status'] = 'VALID';
        //        $ValidCount = $esproduct_model->getCount($condition, $lang); //已通过供应商数量
        //        $this->setvalue('valid_count', $ValidCount); //待审核供应商数量
        // $this->setvalue('valid_rate', $this->_number_format($ValidCount, $total));


        $this->setvalue('invalid_count', $this->_getSpuCount($condition, $lang, 'INVALID', 'REJECTED')); //$InvalidCount
        //   $this->setvalue('invalid_rate', $this->_number_format($InvalidCount, $total));



        $this->setvalue('valid_count', $this->_getSpuCount($condition, $lang, 'VALID', 'PASS'));

        $onshelfcondition['status'] = 'VALID';
        $onshelfcondition['onshelf_flag'] = 'Y';
        $onshelfcondition['lang'] = $lang;

        if (!empty($condition['created_at_start'])) {
            $onshelfcondition['onshelf_at_start'] = $condition['created_at_start'];
        }
        if (!empty($condition['created_at_end'])) {
            $onshelfcondition['onshelf_at_end'] = $condition['created_at_end'];
        }
        $onshelfCount = $esproduct_model->getCount($onshelfcondition, $lang); //已通过供应商数量
        $this->setvalue('onshelf_count', $onshelfCount);
        $this->setCode(MSG::MSG_SUCCESS);
        $this->setMessage('获取成功!');
        $this->jsonReturn();
    }

    private function _getSpuCount($condition, $lang, $spustatus, $logstatus) {
        $validcondition['pcl.status'] = $logstatus;
        $validcondition['p.deleted_flag'] = 'N';
        $validcondition['p.status'] = $spustatus;
        if (isset($condition['created_at_start']) && isset($condition['created_at_end']) && $condition['created_at_end'] && $condition['created_at_start']) {
            $created_at_start = trim($condition['created_at_start']);
            $created_at_end = trim($condition['created_at_end']);
            $validcondition['pcl.approved_at'] = ['between', $created_at_start . ', ' . $created_at_end,];
        } elseif (isset($condition['created_at_start']) && $condition['created_at_start']) {
            $created_at_start = trim($condition['created_at_start']);

            $validcondition['pcl.approved_at'] = ['egt', $created_at_start];
        } elseif (isset($condition['created_at_end']) && $condition['created_at_end']) {
            $created_at_end = trim($condition['created_at_end']);
            $validcondition['pcl.approved_at'] = ['elt', $created_at_end,];
        }
        $validcondition['p.lang'] = $lang;


        $produtc_check_log_table = (new ProductCheckLogModel())->getTableName();
        $product_model = new ProductModel();
        $valid_count = $product_model
                ->alias('p')
                ->join($produtc_check_log_table . ' pcl on pcl.lang = p.lang and pcl.spu = p.spu')
                ->where($validcondition)
                ->count('DISTINCT p.spu');

        return intval($valid_count);
    }

    private function _getSkuCount($condition, $lang, $spustatus, $logstatus) {
        $validcondition['pcl.status'] = $logstatus;
        $validcondition['p.deleted_flag'] = 'N';
        $validcondition['p.status'] = $spustatus;
        if (isset($condition['created_at_start']) && isset($condition['created_at_end']) && $condition['created_at_end'] && $condition['created_at_start']) {
            $created_at_start = trim($condition['created_at_start']);
            $created_at_end = trim($condition['created_at_end']);
            $validcondition['pcl.approved_at'] = ['between', $created_at_start . ', ' . $created_at_end,];
        } elseif (isset($condition['created_at_start']) && $condition['created_at_start']) {
            $created_at_start = trim($condition['created_at_start']);

            $validcondition['pcl.approved_at'] = ['egt', $created_at_start];
        } elseif (isset($condition['created_at_end']) && $condition['created_at_end']) {
            $created_at_end = trim($condition['created_at_end']);
            $validcondition['pcl.approved_at'] = ['elt', $created_at_end,];
        }
        $validcondition['p.lang'] = $lang;


        $produtc_check_log_table = (new ProductCheckLogModel())->getTableName();
        $goods_model = new GoodsModel();
        $valid_count = $goods_model
                ->alias('p')
                ->join($produtc_check_log_table . ' pcl on pcl.lang = p.lang and pcl.sku = p.spu')
                ->where($validcondition)
                ->count('DISTINCT p.spu');

        return intval($valid_count);
    }

    /**
     * 已开发SKU数量
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getGoodsCountAction() {
        $condition = $this->getPut();

        $lang = $this->getPut('lang', 'zh');
        $condition['status'] = 'ALL';
        $condition['onshelf_flag'] = 'A';

        $esproduct_model = new EsGoodsModel();
        $total = $esproduct_model->getCount($condition, $lang); //已开发SPU数量
        $this->setvalue('count', $total); //已开发供应商数量
        $condition['status'] = 'DRAFT';
        $DraftCount = $esproduct_model->getCount($condition, $lang); //已驳回供应商数量
        $this->setvalue('draft_count', $DraftCount); //$InvalidCount


        $CheckingCount = $this->_getSkuCount($condition, $lang, 'CHECKING', 'TO_BE_CHANGED'); //待审核供应商数量

        $this->setvalue('checking_count', $CheckingCount); //待审核供应商数量
        $this->setvalue('checking_rate', $this->_number_format($CheckingCount, $total));




        $ValidCount = $this->_getSkuCount($condition, $lang, 'VALID', 'PASS'); //已通过供应商数量
        $this->setvalue('valid_count', $ValidCount); //待审核供应商数量

        $InvalidCount = $this->_getSkuCount($condition, $lang, 'INVALID', 'REJECTED'); //已驳回供应商数量
        $this->setvalue('invalid_count', $InvalidCount); //$InvalidCount

        $onshelfcondition['status'] = 'VALID';
        $onshelfcondition['onshelf_flag'] = 'Y';
        $onshelfcondition['lang'] = $lang;

        if (!empty($condition['created_at_start'])) {
            $onshelfcondition['onshelf_at_start'] = $condition['created_at_start'];
        }
        if (!empty($condition['created_at_end'])) {
            $onshelfcondition['onshelf_at_end'] = $condition['created_at_end'];
        }
        $onshelfCount = $esproduct_model->getCount($onshelfcondition, $lang); //已通过供应商数量
        $this->setvalue('onshelf_count', $onshelfCount);

        $this->setCode(MSG::MSG_SUCCESS);
        $this->setMessage('获取成功!');
        $this->jsonReturn();
    }

    /**
     * 根据分类获取SPU SKU 供应商数量
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getCatProductCountAction() {
        $condition = $this->getPut();
        $lang = $this->getPut('lang', 'zh');
        $condition['status'] = 'ALL';
        $condition['onshelf_flag'] = 'A';

        $material_cat_model = new MaterialCatModel();

        $catlist = $material_cat_model->get_list('', $lang);
        $esproduct_model = new EsProductModel();
        $esgoods_model = new EsGoodsModel();

        foreach ($catlist as $key => $cat) {


            $condition['mcat_no1'] = $cat['cat_no'];
            $spu_count = $esproduct_model->getCount($condition, $lang); //已开发SPU数量
            $cat['spu_count'] = $spu_count;


            $sku_count = $esgoods_model->getCount($condition, $lang); //已开发SPU数量
            $cat['sku_count'] = $sku_count;

            $supplier_material_cat_model = new SupplierMaterialCatModel();
            $supplier_count = $supplier_material_cat_model->getCatSupplierCount($cat['cat_no'], $condition); //已开发SPU数量


            $cat['supplier_count'] = $supplier_count;

            $catlist[$key] = $cat;
        }


        $this->setCode(MSG::MSG_SUCCESS);
        $this->setMessage('获取成功!');
        $this->jsonReturn($catlist);
    }

    /**
     * 已开发供应商数量
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getSupplierCountAction() {
        $condition = $this->getPut();
        $supplier_model = new SupplierChainModel();
        $total = $supplier_model->getReportCount($condition); //已开发供应商数量
        $this->setvalue('count', $total); //已开发供应商数量
        $condition['status'] = 'APPROVING';
        $CheckingCount = $supplier_model->getReportCount($condition); //待审核供应商数量
        $this->setvalue('checking_count', $CheckingCount); //待审核供应商数量
        // $this->setvalue('checking_rate', $this->_number_format($CheckingCount, $total));
        $condition['status'] = 'APPROVED';
        $ValidCount = $supplier_model->getReportCount($condition); //已通过供应商数量
        $this->setvalue('valid_count', $ValidCount); //待审核供应商数量
        // $this->setvalue('valid_rate', $this->_number_format($ValidCount, $total));


        $condition['status'] = 'INVALID';
        $InvalidCount = $supplier_model->getReportCount($condition); //已驳回供应商数量
        $this->setvalue('invalid_count', $InvalidCount); //$InvalidCount
        //  $this->setvalue('invalid_rate', $this->_number_format($InvalidCount, $total));


        unset($condition['status']);
        $supplier_brand_model = new SupplierBrandModel();
        $brandcount = $supplier_brand_model->getBrandsCount($condition); //供应商品牌数量

        $this->setvalue('brand_count', $brandcount); //$InvalidCount
        $this->setCode(MSG::MSG_SUCCESS);
        $this->setMessage('获取成功!');
        $this->jsonReturn();
    }

    /**
     * 已开发供应商数量
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getSupplierInfoListAction() {
        $condition = $this->getPut();
        $esproduct_model = new EsProductModel();
        $esgoods_model = new EsGoodsModel();
        $onshelfcondition['status'] = 'VALID';
        $onshelfcondition['onshelf_flag'] = 'Y';
        $onshelfcondition['lang'] = !empty($condition['lang']) ? trim($condition['lang']) : 'zh';
        if (!empty($condition['created_at_start'])) {
            $onshelfcondition['onshelf_at_start'] = $condition['created_at_start'];
        }
        if (!empty($condition['created_at_end'])) {
            $onshelfcondition['onshelf_at_end'] = $condition['created_at_end'];
        }

        list( $SupplieridsAndSpuCount, $spu_supplierids) = $esproduct_model->getSupplieridsAndSpuCountByCondition($onshelfcondition, $condition['lang']);
        list( $SupplieridsAndSkuCount, $sku_supplierids) = $esgoods_model->getSupplieridsAndSkuCountByCondition($onshelfcondition, $condition['lang']);
        $supplierids = array_merge($spu_supplierids, $sku_supplierids);
        $suppliers = null;
        if ($supplierids) {
            $supplier_model = new SupplierChainModel();
            $suppliers = $supplier_model
                    ->field('id,name')
                    ->where(['id' => ['in', $supplierids], 'deleted_flag' => 'N'])
                    ->select();
            if ($suppliers) {
                foreach ($suppliers as $key => $supplier) {

                    $supplier['spu_count'] = isset($SupplieridsAndSpuCount[$supplier['id']]) ? $SupplieridsAndSpuCount[$supplier['id']] : 0;
                    $supplier['sku_count'] = isset($SupplieridsAndSkuCount[$supplier['id']]) ? $SupplieridsAndSkuCount[$supplier['id']] : 0;
                    $suppliers[$key] = $supplier;
                }
            }
        }
        $this->setCode(MSG::MSG_SUCCESS);
        $this->setMessage('获取成功!');

        $this->jsonReturn($suppliers);
    }

    /**
     * @desc 获取询单某个时间段内的数据
     *
     * @author liujf
     * @time 2017-12-08
     */
    public function getTimeIntervalDataAction() {
        $condition = $this->getPut();
        if (!empty($condition['creat_at_start']) && !empty($condition['creat_at_end'])) {
            $inquiryModel = new InquiryModel();
            $inquiryCheckLogModel = new InquiryCheckLogModel();
            $inquiryItemModel = new InquiryItemModel();
            $marketAreaModel = new MarketAreaModel();
            $marketAreaCountryModel = new MarketAreaCountryModel();
            $nowTime = time();
            $quoteStatus = [
                'DRAFT' => '草稿',
                'NOT_QUOTED' => '未报价',
                'ONGOING' => '报价中',
                'QUOTED' => '已报价',
                'COMPLETED' => '已完成'
            ];
            $inquiryList = $inquiryModel->getTimeIntervalList($condition);
            foreach ($inquiryList as &$inquiry) {
                $where['inquiry_id'] = $inquiry['id'];
                $inquiry['gross_profit_rate'] = $inquiry['gross_profit_rate'] / 100;
                if (empty($inquiry['area_name'])) {
                    $area = $marketAreaCountryModel->where(['country_bn' => $inquiry['country_bn']])->getField('market_area_bn');
                    $inquiry['area_name'] = $marketAreaModel->where(['bn' => $area, 'lang' => 'zh', 'deleted_flag' => 'N'])->getField('name');
                }
                // 最后一次流入事业部分单员的时间
                $lastBizDispatchingLog = $inquiryCheckLogModel->field('id, into_at')->where(array_merge($where, ['in_node' => 'BIZ_DISPATCHING']))->order('id DESC')->find();
                $lastBizDispatchingTime = strtotime($lastBizDispatchingLog['into_at']);
                // 最后一次流入事业部分单员之后的项目澄清时间
                $clarifyTotalTime = 0;
                $clarifyList = $lastBizDispatchingLog['id'] ? $inquiryCheckLogModel->field('id, out_at')->where(array_merge($where, ['id' => ['gt', $lastBizDispatchingLog['id']], 'out_node' => 'CLARIFY']))->order('id ASC')->select() : [];
                foreach ($clarifyList as $clarify) {
                    $clarifyTime = $inquiryCheckLogModel->where(array_merge($where, ['id' => ['gt', $clarify['id']], 'in_node' => 'CLARIFY']))->order('id ASC')->getField('out_at');
                    if ($clarifyTime) {
                        $clarifyTotalTime += strtotime($clarifyTime) - strtotime($clarify['out_at']);
                    } else {
                        $clarifyTotalTime += $nowTime - strtotime($clarify['out_at']);
                        break;
                    }
                }
                // 询单报价时间
                if ($inquiry['quote_status'] == 'QUOTED' || $inquiry['quote_status'] == 'COMPLETED') {
                    $quoteTime = $inquiryCheckLogModel->where(array_merge($where, ['out_node' => [['eq', 'MARKET_CONFIRMING'], ['eq', 'INQUIRY_CLOSED'], 'or']]))->getField('out_at');
                    $inquiry['quote_time'] = strtotime($quoteTime) - $lastBizDispatchingTime - $clarifyTotalTime;
                } else {
                    $inquiry['quote_time'] = $nowTime - $lastBizDispatchingTime - $clarifyTotalTime;
                }
                $inquiry['quote_time'] = $inquiry['quote_time'] > 0 ? $inquiry['quote_time'] : 0;
                // 报价状态显示成中文
                $inquiry['quote_status'] = $quoteStatus[$inquiry['quote_status']];
                // 询单驳回次数
                $rejectWhere = array_merge($where, ['action' => 'REJECT']);
                $inquiry['reject_count'] = $inquiryCheckLogModel->getCount($rejectWhere);
                // 询单驳回理由
                $rejectReasonList = $inquiryCheckLogModel->where($rejectWhere)->order('id DESC')->getField('op_note', true);
                $inquiry['reject_reason'] = [];
                foreach ($rejectReasonList as $rejectReason) {
                    $tmpArr = explode(', ', $rejectReason);
                    $inquiry['reject_reason'][] = $tmpArr[0];
                }
                $inquiryItemList = $inquiryItemModel->getJoinList($where);
                foreach ($inquiryItemList as &$inquiryItem) {
                    // sku是否油气
                    $inquiryItem['oil_type'] = in_array($inquiryItem['category'], $inquiryItemModel->isOil) ? '油气' : (in_array($inquiryItem['category'], $inquiryItemModel->noOil) ? '非油气' : '');
                    // sku是否平台
                    $inquiryItem['sku_type'] = empty($inquiryItem['sku']) ? '非平台' : '平台';
                }
                // sku记录数
                $inquiry['sku_count'] = $inquiryItemModel->getJoinCount($where);
                $inquiry['other'] = $inquiryItemList;
                unset($inquiry['id']);
            }
            $this->jsonReturn($inquiryList);
        } else {
            $this->setCode('-103');
            $this->setMessage('缺少参数!');
            $this->jsonReturn();
        }
    }

    private function _number_format($value, $total) {
        if ($total) {
            return number_format($value / $total * 100, 2, '.', ', ');
        } else {
            return 100;
        }
    }

}
