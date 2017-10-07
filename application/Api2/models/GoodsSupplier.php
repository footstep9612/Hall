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

    protected $dbName = 'erui2_goods'; //数据库名称
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
                            . '(select name from  erui2_supplier.supplier where id=supplier_id ) as supplier_name')
                    ->where(['sku' => ['in', $skus],
                        'status' => 'VALID',
                        'deleted_flag' => 'N'
                    ])->group('supplier_id,sku')
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
                    ->field('g.spu,gs.supplier_id,gs.brand,gs.supply_ability,'
                            . '(select name from  erui2_supplier.supplier where id=gs.supplier_id ) as supplier_name')
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

}
