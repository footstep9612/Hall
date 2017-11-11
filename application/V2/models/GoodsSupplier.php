<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GoodsSupplier
 * @author  zhongyg
 * @date    2017-8-4 11:37:17
 * @version V2.0
 * @desc
 */
class GoodsSupplierModel extends PublicModel {

    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'goods_supplier'; //数据表表名

    //put your code here

    public function __construct() {
        parent::__construct();
    }

    /* 通过SKU获取供应商信息
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getsuppliersbyskus($skus, $lang = 'en') {
        try {
            if (!$skus) {
                return [];
            }
            $product_attrs = $this->field('sku,supplier_id,brand,supply_ability,'
                            . '(select name from  erui_supplier.supplier where id=supplier_id ) as supplier_name')
                    ->where(['sku' => ['in', $skus],
                        'status' => 'VALID',
                        'deleted_flag' => 'N'
                    ])
                    ->group('supplier_id,sku')
                    ->select();
            if (!$product_attrs) {
                return [];
            }
            $ret = [];
            foreach ($product_attrs as $item) {
                $sku = $item['sku'];
                unset($item['sku']);
                $ret[$sku][] = $item;
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 通过SKU获取供应商信息
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getsuppliersbyspus($spus, $lang = 'en') {
        try {
            if (!$spus) {
                return [];
            }
            $goods_model = new GoodsModel();
            $goods_table = $goods_model->getTableName();
            $product_attrs = $this->alias('gs')
                    ->join($goods_table . ' as g on g.sku=gs.sku and g.lang=\'' . $lang . '\'', 'left')
                    ->field('g.spu,gs.supplier_id,'
                            . '(select name from  erui_supplier.supplier where id=gs.supplier_id ) as supplier_name')
                    ->where(['g.spu' => ['in', $spus],
                        'gs.status' => 'VALID',
                        'gs.deleted_flag' => 'N'
                    ])
                    ->group('gs.supplier_id,g.spu')
                    ->select();


            if (!$product_attrs) {
                return [];
            }
            $ret = [];
            foreach ($product_attrs as $item) {
                $spu = $item['spu'];
                unset($item['spu']);
                $ret[$spu][] = $item;
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * sku价格策略新增/编辑 -- 公共
     * @author klp
     * @return array
     */
    public function editSupplier($input, $sku = '', $admin = '', $spu = '') {
        if (empty($input) || empty($sku) || empty($spu)) {
            return false;
        }

        $this->where(['sku' => $sku])->save(['deleted_flag' => 'Y', 'status' => 'DELETED']);
        $results = array();
        try {
            $product_supplier_model = new ProductSupplierModel();
            $product_supplier_model->editSupplier($input, $spu, $admin);
            foreach ($input as $value) {
                $data = $this->checkParam($value, $sku);
                $data['deleted_flag'] = 'N';
                $data['sku'] = $sku;
                $data['spu'] = $spu;
                $data['status'] = 'VALID';
                $data['supplier_id'] = $data['supplier_id'];
                if (isset($data['supplier_id']) && $data['supplier_id']) {

                    $goods_supplier = $this->field('id')->where(['supplier_id' => $data['supplier_id'], 'sku' => $sku])->find();
                }
                $product_model = new ProductModel();
                $product = $product_model->where(['spu' => $spu, 'lang' => 'zh'])->find();
                if (empty($product)) {
                    $product = $product_model->where(['spu' => $spu, 'lang' => 'en'])->find();
                }
                //存在sku编辑,反之新增,后续扩展性
                $data['brand'] = isset($product['brand']) ? $product['brand'] : '{"lang": "zh", "name": "", "logo": "", "manufacturer": ""}';
                if ($goods_supplier) {
                    $data['updated_by'] = $admin;
                    $data['updated_at'] = date('Y-m-d H:i:s');

                    $where = [
                        'id' => $goods_supplier['id'],
                    ];
                    $res = $this->where($where)->save($data);


                    if ($res) {
                        $results['code'] = '1';
                        $results['message'] = '成功！';
                    } else {
                        $results['code'] = '-101';
                        $results['message'] = '失败!';
                    }
                } else {

                    $data['sku'] = $sku;
                    $data['spu'] = $spu;

                    $data['created_by'] = $admin;
                    $data['created_at'] = date('Y-m-d H:i:s');
                    $res = $this->add($data);
                    echo $this->_sql();
                    if ($res) {
                        $results['code'] = '1';
                        $results['message'] = '成功！';
                    } else {
                        $results['code'] = '-101';
                        $results['message'] = '失败!';
                    }
                }
            }

            return $results;
        } catch (Exception $e) {
            Log::write(__CLASS__);
            Log::write($e->getMessage());
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
        unset($checkout['id']);
        if (empty($checkout['supplier_id'])) {
            $results['code'] = '-1001';
            $results['message'] = '[supplier_id]缺失!';
        }
        if (!empty($checkout['supplier_id'])) {
            $data['supplier_id'] = $checkout['supplier_id'];
        }

        if ($results) {
            return $results;
        }
        return $data;
    }

}
