<?php

/**
 * SKU
 * User: linkai
 * Date: 2017/6/15
 * Time: 21:04
 */
class GoodsModel extends PublicModel {

    protected $tableName = 'goods';
    protected $dbName = 'erui2_goods'; //数据库名称

    //状态

    const STATUS_VALID = 'VALID';          //有效
    const STATUS_TEST = 'TEST';            //测试
    const STATUS_INVALID = 'INVALID';      //无效
    const STATUS_DELETED = 'DELETED';      //删除
    const STATUS_CHECKING = 'CHECKING';    //审核中
    const STATUS_DRAFT = 'DRAFT';          //草稿
    const DELETED_Y = 'Y';          //Y
    const DELETED_N = 'N';          //N

    //定义校验规则

    protected $field = array(
        'spu' => array('required'),
        'name' => array('required'),
            //'show_name' => array('required'),
    );

    public function __construct() {
        parent::__construct();
    }

    /**
     * 商品基本信息    -- 公共方法--z暂不使用
     * @author link 2017-06-26
     * @param array $condition
     * @return array
     */
    public function getInfoBase($condition = []) {
        if (!isset($condition['sku']))
            return array();

        $where = array(
            'sku' => trim($condition['sku']),
        );
        if (isset($condition['lang'])) {
            $where['lang'] = strtolower($condition['lang']);
        }
        if (!empty($condition['status']) && in_array(strtoupper($condition['status']), array('VALID', 'INVALID', 'DELETED'))) {
            $where['status'] = strtoupper($condition['status']);
        }


        $field = 'sku,spu,lang,name,show_name,qrcode,model,description,status';
        try {
            $result = $this->field($field)->where($where)->select();
            $data = array();
            if ($result) {
                if ($where['lang']) {
                    $this->getSpecBySku($result, $where['lang']);
                } else {
                    $this->getSpecBySku($result, 'en');
                }
                foreach ($result as $item) {
                    //获取供应商与品牌
                    $item['brand'] = $item['spec'] = $item['supplier_id'] = $item['supplier_name'] = $item['meterial_cat_no'] = '';

                    $data[$item['lang']] = $item;
                }
            }
            return $data;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * sku状态验证
     * @author klp
     * @return status
     */
    public function checkStatus($input) {
        if (empty($input)) {
            return false;
        }
        //新状态可以补充
        switch ($input) {
            case 'check':    //报审
                return self::STATUS_CHECKING;
                break;
            case 'valid':    //审核通过
                return self::STATUS_VALID;
                break;
            case 'invalid':    //驳回
                return self::STATUS_INVALID;
                break;
        }
    }

    /**
     * 获取spu下的规格商品（用于门户产品详情页）
     * @param string $spu
     * @param string $lang
     * @return array
     */
    public function getSpecGoodsBySpu($spu = '', $lang = '', $spec_type = 0) {
        if (empty($spu))
            return array();
        $keyRedis = md5(json_encode($spu.$lang.self::STATUS_VALID));
        if (redisHashExist('specGoods',$keyRedis)) {
            return json_decode(redisHashGet('specGoods', $keyRedis), true);
        }
        try {
            $field = "lang,spu,sku,qrcode,name,show_name_loc,show_name,model,exw_days,min_pack_naked_qty,nude_cargo_unit,min_pack_unit,min_order_qty,purchase_price,purchase_price_cur_bn,nude_cargo_l_mm,nude_cargo_w_mm,nude_cargo_h_mm,min_pack_l_mm,min_pack_w_mm,min_pack_h_mm,net_weight_kg,gross_weight_kg,compose_require_pack,pack_type,name_customs,hs_code,tx_unit,tax_rebates_pct,regulatory_conds,commodity_ori_place,source,source_detail";
            $condition = array(
                "spu" => '1303070000010000',
                "lang" => $lang,
                "status" => self::STATUS_VALID,
                "deleted_flag" => self::DELETED_N
            );
            $result = $this->field($field)->where($condition)->select();

            $this->getSpecBySku($result, $lang, $spec_type, $spu);
            redisHashSet('specGoods', $keyRedis, json_encode($result));
            return $result;
        } catch (Exception $e) {

            return array();
        }
    }

    public function getSpecBySku(&$result, $lang, $spec_type, $spu) {

        if ($result) {
            $skus = [];
            foreach ($result as $k => $item) {
                $skus[] = $item['sku'];
            }
            if ($skus) {
                $gattr = new GoodsAttrModel();
                $specs = $gattr->getgoods_attrbyskus($skus, $lang);
            }
            foreach ($result as $k => $item) {
                $condition = array(
                    "spu" => $spu,
                    "lang" => $lang,
                    "status" => self::STATUS_VALID,
                    "deleted_flag" => self::DELETED_N
                );
                //获取spu的brand
                $result[$k]['brand']='';
                $productModel = new ProductModel();
                $brand = $productModel->field('brand')->where($condition)->find();
                if($brand){
                    if(!is_null(json_decode($brand['brand'],true))){
                        $resBrand = json_decode($brand['brand'],true);
                        $result[$k]['brand'] = $resBrand['name'];
                    }
                    $result[$k]['brand'] = $brand['brand'];
                }
                //获取商品规格
                //增加最小
                $sku = $item['sku'];
                $result[$k]['exw_day'] = $item['exw_days'];
                $result[$k]['purchase_unit'] = $item['tx_unit'];


                $result[$k]['goods'] = $item['min_pack_naked_qty'] . $item['nude_cargo_unit'] . '/' . $item['tx_unit'];
                $spec = [];
                if (isset($specs[$sku])) {
                    $spec = json_decode($specs[$sku][0]['spec_attrs'], true);
                }
                if ($spec) {
                    $result[$k]['spec'] = $spec;
                } else {
                    $result[$k]['spec'] = [];
                }
            }
        }
    }

    /**
     * sku基本信息查询 == 公共
     * @author klp
     * @return array
     */
    public function getSkuInfo($condition) {
        if (!isset($condition)) {
            return false;
        }
        if (isset($condition['sku']) && !empty($condition['sku'])) {
            $where = array('sku' => trim($condition['sku']));
        } else {
            jsonReturn('', MSG::MSG_FAILED, MSG::getMessage(MSG::MSG_FAILED));
        }
        if (isset($condition['lang']) && in_array($condition['lang'], array('zh', 'en', 'es', 'ru'))) {
            $where['lang'] = strtolower($condition['lang']);
        }
        if (!empty($condition['status']) && in_array(strtoupper($condition['status']), array('VALID', 'INVALID', 'DELETED'))) {
            $where['status'] = strtoupper($condition['status']);
        } else {
            $where['status'] = array('neq', self::STATUS_DELETED);
        }
        //redis
        if (redisHashExist('Sku', md5(json_encode($where)))) {
            return json_decode(redisHashGet('Sku', md5(json_encode($where))), true);
        }
        $field = 'lang, spu, sku, qrcode, name, show_name_loc, show_name, model, description, status, created_by, created_at, updated_by, updated_at, checked_by, checked_at, source, source_detail, deleted_flag,';
        //固定商品属性
        $field .= 'exw_days, min_pack_naked_qty, nude_cargo_unit, min_pack_unit, min_order_qty, purchase_price, purchase_price_cur_bn, nude_cargo_l_mm,';
        //固定物流属性
        $field .= 'nude_cargo_w_mm, nude_cargo_h_mm, min_pack_l_mm, min_pack_w_mm, min_pack_h_mm, net_weight_kg, gross_weight_kg, compose_require_pack, pack_type,';
        //固定申报要素属性
        $field .= 'name_customs, hs_code, tx_unit, tax_rebates_pct, regulatory_conds, commodity_ori_place';
        try {
            $result = $this->field($field)->where($where)->select();
            $data = array();
            if ($result) {
                foreach ($result as $k=>$item) {
                    $condition = array(
                        "spu" => $item['spu'],
                        "lang" =>  $where['lang'],
                        "status" => self::STATUS_VALID,
                        "deleted_flag" => self::DELETED_N
                    );
                    //获取spu的brand
                    $item['brand']='';
                    $productModel = new ProductModel();
                    $brand = $productModel->field('brand')->where($condition)->find();
                    if($brand){
                        if(!is_null(json_decode($brand['brand'],true))){
                            $resBrand = json_decode($brand['brand'],true);
                            $item['brand'] = $resBrand['name'];
                        }
                        $item['brand'] = $brand['brand'];
                    }
                    //按语言分组
                    $data[$item['lang']] = $item;
                }
                redisHashSet('Sku', md5(json_encode($where)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }


}
