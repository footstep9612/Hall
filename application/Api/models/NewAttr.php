<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NewAttr
 * @author  zhongyg
 * @date    2017-8-30 16:41:10
 * @version V2.0
 * @desc
 */
class NewAttrModel extends PublicModel {

    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 't_goods_attr_new_ex'; //数据表表名

    //状态

    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETED = 'DELETED'; //删除；

    protected $tablePrefix = '';

    /* 通过SKU获取数据商品属性列表
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix
     */

    public function getgoods_attrbyskus($skus, $lang = 'en') {

        try {
            $product_attrs = $this->table('erui_goods.t_goods_attr_new_ex')
                    ->field('*')
                    ->where(['sku' => ['in', $skus],
                        'hs_flag' => 'Y',
                        'lang' => $lang,
                        'status' => 'VALID',
                            //  'created_at' => '2017-08-15 00:00:00'
                    ])
                    ->select();
            Log::append(MYPATH . '/logs/sql.sql', $this->_sql());
            $ret = [];
            foreach ($product_attrs as $item) {
                $sku = $item['sku'];
                unset($item['sku']);

                $ret[$sku][$item['attr_name']] = $item['attr_value'];
            }

            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /* 通过SKU获取数据商品规格列表
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix
     */

    public function getgoods_specsbyskus($skus, $lang = 'en') {
        try {
            $product_attrs = $this->table('erui_goods.t_goods_attr_new_ex')
                    ->field('sku,attr_name,attr_value,attr_no')
                    ->where([
                        'sku' => ['in', $skus],
                        'lang' => $lang,
                        'spec_flag' => 'Y',
                        'status' => 'VALID',
                            // 'created_at' => '2017-08-15 00:00:00'
                    ])
                    ->select();
            Log::append(MYPATH . '/logs/sql.sql', $this->_sql());
            $ret = [];
            foreach ($product_attrs as $item) {
                $sku = $item['sku'];
                unset($item['sku']);
                $ret[$sku][$item['attr_name']] = $item['attr_value'];
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
