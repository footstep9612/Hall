<?php

/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/2
 * Time: 14:04
 */
class GoodsCostPriceModel extends PublicModel {

    protected $tableName = 'goods_cost_price'; //数据表名称
    protected $dbName = 'erui_goods';         //数据库名称

    public function __construct($str = '') {

        parent::__construct();
    }

    //状态--INVALID,CHECKING,VALID,DELETED
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETED = 'DELETED'; //删除；
    const STATUS_CHECKING = 'CHECKING'; //审核；
    const STATUS_DRAFT = 'DRAFT';       //草稿

    /**
     * 通过商品sku编码获取价格策略信息
     * @param array $input
     * @return array
     * @author klp
     */

    public function getInfo($input) {
        if (empty($input['sku'])) {
            return false;
        }
        $fields = 'id, sku, supplier_id, price,max_price, price_unit, price_cur_bn, min_purchase_qty,max_purchase_qty, pricing_date, price_validity, status, created_by, created_at';
        try {
            $result = $this->field($fields)->where(['sku' => $input['sku'], 'deleted_flag' => 'N', 'status' => 'VALID'])->select();
            $data = array();
            if ($result) {
                //通过supplier_id查询供应商名称
                $SupplierModel = new SupplierModel();
                foreach ($result as $item) {
                    $item['supplier_name'] = '';
                    $info = $SupplierModel->field('name')->where(['id' => $item['supplier_id']])->find();
                    if ($info) {
                        $item['supplier_name'] = $info['name'];
                    }
                    $data[] = $item;
                }
            }
            return $data;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * sku价格策略新增/编辑 -- 公共
     * @author klp
     * @return array
     */
    public function editCostprice($input, $sku = '', $admin = '') {
        if (empty($input) || empty($sku)) {
            return false;
        }
        $results = array();
        try {
            $this->where(['sku' => $sku])->save(['deleted_flag' => 'Y', 'status' => 'DELETED']);
            foreach ($input as $key => $value) {
                $data = $this->checkParam($value, $sku);
                //存在sku编辑,反之新增,后续扩展性

                if (isset($value['id']) && !empty($value['id'])) {
                    $data['updated_by'] = $admin;
                    $data['deleted_flag'] = 'N';
                    $data['status'] = self::STATUS_VALID;
                    $data['updated_at'] = date('Y-m-d H:i:s', time());
                    $where = [
                        'sku' => $sku,
                        'id' => $data['id']
                    ];
                    $res = $this->where($where)->save($data);
                    if (!$res) {
                        return false;
                    }
                } else {
                    $data['status'] = self::STATUS_VALID;
                    $data['sku'] = $sku;
                    $data['created_by'] = $admin;
                    $data['created_at'] = date('Y-m-d H:i:s', time());
                    $res = $this->add($data);
                    if (!$res) {
                        return false;
                    }
                }
            }
            if ($res) {
                $results['code'] = '1';
                $results['message'] = '成功！';
            } else {
                $results['code'] = '-101';
                $results['message'] = '失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 参数校验,目前只测必须项
     * @author klp
     * @return array
     */
    public function checkParam($checkout, $sku) {
        if (empty($checkout)) {
            return false;
        }
        $results = $data = array();
        if (empty($sku)) {
            $results['code'] = '-1001';
            $results['message'] = '[sku]缺失!';
        }
        /* if (empty($checkout['min_purchase_qty'])) {
          $results['code'] = '-1001';
          $results['message'] = '[最小购买量]缺失!';
          }
          if (empty($checkout['price']) || !is_numeric($checkout['price'])) {
          $results['code'] = '-1001';
          $results['message'] = '[价格]缺失或非数字!';
          } */

        if (empty($checkout['supplier_id'])) {
            $results['code'] = '-1001';
            $results['message'] = '[供应商]缺失!';
        }

        if (!empty($checkout['supplier_id'])) {
            $data['supplier_id'] = $checkout['supplier_id'];
        }
        if (!empty($checkout['contact_first_name'])) {
            $data['contact_first_name'] = $checkout['contact_first_name'];
        }
        if (!empty($checkout['contact_last_name'])) {
            $data['contact_last_name'] = $checkout['contact_last_name'];
        }
        if (!empty($checkout['price']) && is_numeric($checkout['price'])) {
            $data['price'] = $checkout['price'];
        }else{
            $data['price'] = 0;
        }
        if (!empty($checkout['max_price']) && ($checkout['max_price'] >= $data['price'])) {
            $data['max_price'] = $checkout['max_price'];
        }
        if (!empty($checkout['price_unit'])) {
            $data['price_unit'] = $checkout['price_unit'];
        }
        if (!empty($checkout['price_cur_bn'])) {
            $currencyModel = new CurrencyModel();
            $currencyList = $currencyModel->field('bn')->where(['deleted_flag'=>'N'])->select();
            if(!in_array(array('bn'=>$data['price_cur_bn']),$currencyList)){
                $results['code'] = '-1001';
                $results['message'] = '[币种有误]';
            }
            $data['price_cur_bn'] = $checkout['price_cur_bn'];
        }
        if (!empty($checkout['min_purchase_qty'])) {
            $data['min_purchase_qty'] = $checkout['min_purchase_qty'];
        }
        if (!empty($checkout['max_purchase_qty'])) {
            $data['max_purchase_qty'] = $checkout['max_purchase_qty'];
        }
        if (!empty($checkout['pricing_date'])) {
            $data['pricing_date'] = $checkout['pricing_date'];
        }
        if (!empty($checkout['price_validity'])) {
            $data['price_validity'] = $checkout['price_validity'];
        }
        if (!empty($checkout['id'])) {
            $data['id'] = $checkout['id'];
        }

        if ($results) {
            jsonReturn($results);
        }
        return $data;
    }

    protected function _checkCostPrice($data, $field = 'min_purchase_qty,max_purchase_qty') {
        if (isset($data['price']) && isset($data['max_price'])) {
            if ($data['price'] >= $data['max_price']) {
                jsonReturn('', -1006, '价格区间错误!');
            }
        }

        $where = array(
            'supplier_id' => $data['supplier_id'],
            'deleted_flag' => 'N'
        );
        $result = $this->field($field)->where($where)->select();
        if ($result) {
            foreach ($result as $item) {
                if (!empty($item['min_purchase_qty']) && !empty($item['max_purchase_qty'])) {
                    $arrNumO = range($item['min_purchase_qty'], $item['max_purchase_qty']);
                } else {
                    $numO = $item['min_purchase_qty'];
                }

                if (!empty($data['min_purchase_qty']) && !empty($data['max_purchase_qty'])) {
                    $arrNumT = range($data['min_purchase_qty'], $data['max_purchase_qty']);
                } else {
                    $numT = $data['min_purchase_qty'];
                }

                if ($arrNumO) {
                    if ($arrNumT) {
                        $res = array_diff($arrNumO, $arrNumT);
                        if (!empty($res)) {
                            $code = -1006;
                        }
                    } else {
                        if ($numT <= $item['max_purchase_qty']) {
                            $code = -1006;
                        }
                    }
                } else {
                    if ($arrNumT) {
                        if (in_array($numO, $arrNumT)) {
                            $code = -1006;
                        }
                    }
                }
                if ($code) {
                    jsonReturn('', $code, '数量区间错误或冲突!');
                }
            }
        }
    }

    /* 根据SKUS数组获取SKU价格信息
     * @author zyg 2017-10-12
     * @param mix $skus // SPU
     * @return mix
     * @author  zhongyg
     * @version V2.0
     * @desc   ES 产品
     */

    public function getCostPricesBySkus($skus) {

        try {
            if (!$skus) {
                return [];
            }
            $product_costprices = $this->field('sku,supplier_id,contact_first_name,contact_last_name,price,max_price,'
                            . 'price_unit,price_cur_bn,min_purchase_qty,max_purchase_qty,pricing_date,price_validity')
                    ->where(['sku' => ['in', $skus],
                        'status' => 'VALID',
                        'deleted_flag' => 'N'
                    ])
                    ->select();
            if (!$product_costprices) {
                return [];
            }
            $ret = [];
            foreach ($product_costprices as $item) {
                $sku = $item['sku'];
                unset($item['sku']);
                $ret[$sku][] = $item;
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS:' . __CLASS__ . PHP_EOL . 'FUNCTION:' . __FUNCTION__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 根据SKU数组获取SKU价格信息
     * @author zyg 2017-10-12
     * @param mix $sku // SPU
     * @return mix
     * @author  zhongyg
     * @version V2.0
     * @desc   ES 产品
     */

    public function getCostPricesBySku($sku) {

        try {
            if (!$sku) {
                return [];
            }
            $product_costprices = $this->field('supplier_id,contact_first_name,contact_last_name,price,max_price,'
                            . 'price_unit,price_cur_bn,min_purchase_qty,max_purchase_qty,pricing_date,price_validity')
                    ->where(['sku' => $sku,
                        'status' => 'VALID',
                        'deleted_flag' => 'N'
                    ])
                    ->select();
            if (!$product_costprices) {
                return [];
            }

            return $product_costprices;
        } catch (Exception $ex) {
            LOG::write('CLASS:' . __CLASS__ . PHP_EOL . 'FUNCTION:' . __FUNCTION__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
