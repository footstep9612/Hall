<?php

/**
 * 产品
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/8
 * Time: 11:45
 */
class ProductModel extends PublicModel {

    protected $tableName = 'product';
    protected $dbName = 'erui_goods'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    /**
     * 产品详情
     * @param string $spu
     * @param string $lang
     * @return bool|mixed
     */
    public function getInfoBySpu($spu = '', $lang = '', $stock = false, $country_bn = '') {
        if (empty($spu) || empty($lang)) {
            return false;
        }

        $condition = ['spu' => $spu, 'lang' => $lang, 'status' => 'VALID', 'deleted_flag' => 'N'];
        try {
            $spuInfo = $this->field('spu,name,show_name,customization_flag,brand,exe_standard,warranty,resp_time,resp_rate,description,exe_standard,tech_paras,advantages,principle,app_scope,properties')->where($condition)->find();
            if ($spuInfo) {
                //附件
                $attachModel = new ProductAttachModel();
                $attachs = $attachModel->getAttachBySpu($spu);
                $spuInfo['attach'] = $attachs ? $attachs : [];

                //最小订货数量
                $condition_order = ['spu' => $spu, 'lang' => $lang, 'status' => 'VALID', 'deleted_flag' => 'N'];
                if ($stock) {
                    $stockModel = new StockModel();
                    $condition_order['country_bn'] = $country_bn;
                    $stockAry = $stockModel->field('sku,stock')->where($condition_order)->select();
                    $skus = [];
                    $stocks = 0;
                    foreach ($stockAry as $r) {
                        $skus[] = $r['sku'];
                        $stocks = $stocks + $r['stock'];
                    }
                    $spuInfo['stock'] = $stocks;    //库存
                    //现货价格
                    $scpModel = new StockCostPriceModel();
                    $condition_price = ['country_bn' => $country_bn, 'sku' => ['in', $skus], 'status' => 'VALID', 'deleted_flag' => 'N', 'price_validity_start' => ['elt', date('Y-m-d', time())]];
                    $priceInfo = $scpModel->field('min_price,price_symbol')->where($condition_price)->order('min_price')->find();
                    $spuInfo['priceAry'] = $priceInfo ? $priceInfo : [];

                    $condition_order = ['sku' => ['in', $skus], 'lang' => $lang];    //现货初始化最小订货量查询条件
                }
                $goodsModel = new GoodsModel();
                $min_order_qty = $goodsModel->field('min_order_qty,min_pack_unit,exw_days')->where($condition_order)->order('min_order_qty')->find();
                $spuInfo['min_order_qty'] = $min_order_qty ? $min_order_qty['min_order_qty'] : 1;
                $spuInfo['min_pack_unit'] = $min_order_qty ? $min_order_qty['min_pack_unit'] : '';
                $spuInfo['exw_days'] = $min_order_qty ? $min_order_qty['exw_days'] : '';
            }
            return $spuInfo ? $spuInfo : false;
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getInfoBySpu:' . $e, Log::ERR);
            return false;
        }
    }

    /**
     * 获取sku列表
     * @param $input
     * @return array|bool
     */
    public function getSkuList($input) {
        if (!isset($input['spu']) || empty($input['spu'])) {
            jsonReturn('', ErrorMsg::NOTNULL_SPU);
        }

        if (!isset($input['lang']) || empty($input['lang'])) {
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        try {
            $goodsModel = new GoodsModel();
            $gtable = $goodsModel->getTableName();
            $gattrModel = new GoodsAttrModel();
            $gatable = $gattrModel->getTableName();
            $condition = ["$gtable.spu" => $input['spu'], "$gtable.lang" => $input['lang']];
            //现货处理
            $stock = false;
            $skus = [];
            if (isset($input['type']) && isset($input['country_bn']) && $input['type'] && $input['country_bn']) {
                $stock = true;
                $condition_stock = ['spu' => $input['spu'], 'lang' => $input['lang'], 'country_bn' => $input['country_bn'], 'status' => 'VALID', 'deleted_flag' => 'N'];
                $stockModel = new StockModel();
                $stockSku = $stockModel->field('sku,stock')->where($condition_stock)->select();
                foreach ($stockSku as $item) {
                    $skus[] = $item['sku'];
                    $skuStock[$item['sku']] = $item['stock'];
                }
                $condition["$gtable.sku"] = ['in', $skus];
            }

            //订货号
            if (isset($input['sku']) && !empty($input['sku'])) {
                $condition["$gtable.sku"] = $input['sku'];
            }

            //型号
            if (isset($input['model']) && !empty($input['model'])) {
                //$condition["$gtable.model"] = $input['model'];
                $input['model'] = trim($input['model']);
                $find = array('(',')'," ",'\\','/');
                $replace = array('.{1}','.{1}'," *",'.{1,2}','.{1,2}');
                $input['model'] = str_replace($find, $replace, $input['model']);
                $condition["$gtable.model"] = ['exp', 'regexp \''.$input['model'].'\''];
            }

            //包装数量
            if (isset($input['min_pack_naked_qty']) && !empty($input['min_pack_naked_qty'])) {
                $condition["$gtable.min_pack_naked_qty"] = $input['min_pack_naked_qty'];
            }

            //出货周期
            if (isset($input['exw_days']) && !empty($input['exw_days'])) {
                $condition["$gtable.exw_days"] = $input['exw_days'];
            }

            $condition["$gtable.status"] = 'VALID';
            $condition["$gtable.deleted_flag"] = 'N';

            $current_no = (isset($input['current_no']) && is_numeric($input['current_no'])) ? $input['current_no'] : 1;
            $pageSize = (isset($input['pageSize']) && is_numeric($input['pageSize'])) ? $input['pageSize'] : 10;
            if(isset($input['spec']) && !empty($input['spec'])){
                $spec = [];
                foreach($input['spec'] as $key => $value){
                    //$spec[] = ['exp', 'regexp \'"'.$key.'":"' . $value . '"\''];    //精确查
                    $find = array('(',')'," ",'\\','/');
                    $replace = array('.{1}','.{1}'," *",'.{1,2}','.{1,2}');
                    $key = str_replace($find, $replace, $key);
                    $key = str_replace("'", "\\'",$key);
                    $key = str_replace('"', '.{1,2}',$key);

                    $value = str_replace($find, $replace, $value);
                    $value = str_replace("'", "\\'",$value);
                    $value = str_replace('"', '.{1,2}',$value);
                    $spec[] = ['exp', 'regexp \'"'.$key.'":"[^"]*' . $value . '[^"]*"\''];    //模糊查
                }
                $condition["$gatable.spec_attrs"] = $spec;
            }

            $idAry = $goodsModel->field("$gtable.id")->join($gatable." ON $gtable.sku=$gatable.sku AND $gtable.lang=$gatable.lang",'LEFT')->where($condition)->select();
            //Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getSkuList:' . $goodsModel->getLastSql(), Log::ERR);
            $ids = [];
            if($idAry){
                foreach($idAry as $id){
                    $ids[] = $id['id'];
                }
            }else{
                return [];
            }

            $result = $goodsModel->field('sku,model,min_pack_naked_qty,nude_cargo_unit,min_pack_unit,min_order_qty,exw_days')->where(['id'=>['in', $ids]])->limit(($current_no - 1) * $pageSize, $pageSize)->select();
            if ($result) {
                $skuAry = [];
                foreach($result as $r){
                    $skuAry[] = $r['sku'];
                }
                //扩展属性
                $condition_attr = ['spu' => $input['spu'], 'sku'=>['in',$skuAry], 'lang' => $input['lang'], 'deleted_flag' => 'N'];
                $attrs = $gattrModel->field('sku,spec_attrs')->where($condition_attr)->select();

                $attr_key = $attr_value = [];
                foreach ($attrs as $index => $attr) {
                    $attrInfo = json_decode($attr['spec_attrs'], true);
                    foreach ($attrInfo as $key => $value) {
                        if (!isset($attr_key[$key])) {
                            $attr_key[$key] = $key;
                        }
                        $attr_value[$attr['sku']][$key] = $value;
                    }
                }

                if ($stock) {
                    //现货价格
                    foreach ($result as $index => $item) {
                        $priceInfo = self::getSkuPriceByCount($item['sku'], $input['country_bn'], $item['min_order_qty']);
                        $result[$index]['price'] = $priceInfo['price'];
                        $result[$index]['price_cur_bn'] = $priceInfo['price_cur_bn'];
                        $result[$index]['price_symbol'] = $priceInfo['price_symbol'];
                    }
                }
            }
            return $result ? ['skuAry' => $result, 'stockAry' => $skuStock ? $skuStock : [], 'attr_key' => $attr_key, 'attr_value' => $attr_value] : [];
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getSkuList:' . $e, Log::ERR);
            return false;
        }
    }

    /**
     * 根据sku 获取商品基本信息
     * @return array|bool
     */
    public function getSkusList($input) {
        if (!isset($input['skus']) || empty($input['skus'])) {
            jsonReturn('', ErrorMsg::NOTNULL_SKU);
        }

        if (!isset($input['lang']) || empty($input['lang'])) {
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        if (isset($input['type']) && $input['type']) {
            if (!isset($input['buyNumber']) || empty($input['buyNumber'])) {
                jsonReturn('', ErrorMsg::ERROR_PARAM, 'buyNumber not null');
            }
            $productModel = new ProductModel();
        }

        try {
            $gmodel = new GoodsModel();
            $gtable = $gmodel->getTableName();
            $thisTable = $this->getTableName();
            $condition = ["$gtable.sku" => ['in', $input['skus']], "$gtable.lang" => $input['lang'], "$gtable.deleted_flag" => 'N', "$gtable.status" => 'VALID'];
            $result = $gmodel->field("$gtable.sku,$gtable.name,$gtable.spu,$gtable.show_name,$gtable.model,$gtable.min_pack_naked_qty,$gtable.nude_cargo_unit,$gtable.min_pack_unit,$gtable.min_order_qty,$thisTable.name as spu_name,$thisTable.show_name as spu_show_name")->join("$thisTable ON $gtable.spu=$thisTable.spu AND $gtable.lang=$thisTable.lang")->where($condition)->select();
            $attachs = [];
            $attrs = [];
            if ($result) {

                $skuAry = [];
                foreach ($result as $index => $r) {
                    $skuAry[] = $r['sku'];
                    if ($input['type']) {
                        $r['priceAry'] = $productModel->getSkuPriceByCount($r['sku'], $input['country_bn'], $input['buyNumber'][$r['sku']]);
                    }
                    $result[$r['sku']] = $r;
                    $result[$r['sku']]['name'] = empty($r['show_name']) ? (empty($r['name']) ? (empty($r['spu_show_name']) ? (empty($r['spu_name']) ? '' : $r['spu_name']) : $r['spu_show_name']) : $r['name']) : $r['show_name'];
                    unset($result[$index]);
                }

                //库存
                $stockAry = [];
                if ($input['type']) {
                    $stockAry = $productModel->getSkuStockBySku($skuAry, $input['country_bn'], $input['lang']);
                }

                $gattrModel = new GoodsAttrModel();
                $condition_attr = ['sku' => ['in', $skuAry], 'lang' => $input['lang'], 'deleted_flag' => 'N'];
                $attrs = $gattrModel->field('sku,spec_attrs')->where($condition_attr)->select();
                foreach ($attrs as $index => $attr) {
                    $attrs[$attr['sku']] = json_decode($attr['spec_attrs'], true);
                    unset($index);
                }


                /*$gaModel = new GoodsAttachModel();
                $attachInfo = $gaModel->field('sku,attach_url,attach_name')->where(['sku' => ['in', $skuAry], 'deleted_flag' => 'N', 'status' => 'VALID'])->order('default_flag DESC')->select();
                if ($attachInfo) {
                    foreach ($attachInfo as $item) {
                        if (isset($attachs[$item['sku']])) {
                            continue;
                        }
                        $attachs[$item['sku']] = $item;
                    }
                }*/
                $paModel = new ProductAttachModel();
                foreach($skuAry as $sku){
                    if(!isset($attachs[$sku])){
                        $attachInfo = $paModel->field('spu,attach_url,attach_name')->where(['spu' => $result[$sku]['spu'], 'deleted_flag' => 'N', 'status' => 'VALID'])->order('default_flag DESC')->find();
                        if ($attachInfo) {
                            $attachs[$sku] = $attachInfo;
                        }
                    }
                }
            }
            return $result ? ['skuAry' => $result, 'attachAry' => $attachs, 'attrAry' => $attrs, 'stockAry' => $stockAry] : [];
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getSkusList:' . $e, Log::ERR);
            return false;
        }
    }

    /**
     * 关联产品
     */
    public function getRelationSpu($input) {
        if (!isset($input['spu']) || empty($input['spu'])) {
            jsonReturn('', ErrorMsg::NOTNULL_SPU);
        }

        if (!isset($input['lang']) || empty($input['lang'])) {
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        $condition = ['spu' => $input['spu'], 'lang' => $input['lang']];
        try {
            $RelationModel = new ProductRelationModel();
            $relationSpu = $RelationModel->field('relation_spu')->where($condition)->select();
            $data = [];
            if ($relationSpu) {
                $spus = [];
                foreach ($relationSpu as $index => $item) {
                    $spus[] = $item['relation_spu'];
                }
                $data['spu'] = $this->field('show_name,name,spu')
                                ->where(['spu' => ['in', $spus], 'lang' => $input['lang']])->select();

                //附件图
                $attachModel = new ProductAttachModel();
                $attachs = $attachModel->getAttachBySpu($spus);

                $dataAttach = [];
                foreach ($attachs as $r) {
                    if (isset($dataAttach[$r['spu']])) {
                        continue;
                    }
                    $dataAttach[$r['spu']] = $r['attach_url'];
                }
                $data['thumbs'] = $dataAttach;
            }
            return $data;
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getRelationSpu:' . $e, Log::ERR);
            return false;
        }
    }

    /**
     * 根据相应数量返回相应价格
     */
    public function getSkuPriceByCount($sku = '', $country_bn = '', $count = '') {
        if (!isset($sku) || empty($sku) || !isset($country_bn) || empty($country_bn) || !isset($count) || !is_numeric($count)) {
            return '';
        }

        $condition = ['sku' => $sku, 'country_bn' => $country_bn, 'price_validity_start' => ['elt', date('Y-m-d', time())], 'min_purchase_qty' => ['elt', $count]];
        try {
            $scpModel = new StockCostPriceModel();
            $priceInfo = $scpModel->field('min_price as price,min_purchase_qty,max_purchase_qty,price_validity_end,price_cur_bn,price_symbol')->where($condition)->order('min_purchase_qty DESC')->select();
            if ($priceInfo) {
                foreach ($priceInfo as $item) {
                    if (($item['price_validity_end'] >= date('Y-m-d', time()) || empty($item['price_validity_end'])) && (empty($item['max_purchase_qty']) || $item['max_purchase_qty'] >= $count)) {
                        return $item;
                        break;
                    }
                }
            }
            return '';
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getSkuPriceByCount:' . $e, Log::ERR);
            return false;
        }
    }

    /**
     * 根据sku跟国家获取库存
     * @param string $sku
     * @param string $country_bn
     * @return array
     */
    public function getSkuStockBySku($sku, $country_bn = '', $lang = '') {
        if (!isset($sku) || empty($sku) || !isset($country_bn) || empty($country_bn) || !isset($lang) || empty($lang)) {
            return [];
        }

        if (is_array($sku)) {
            $condition['sku'] = ['in', $sku];
        } else {
            $condition['sku'] = $sku;
        }
        $condition['country_bn'] = $country_bn;
        $condition['lang'] = $lang;
        $condition['deleted_flag'] = 'N';
        $condition['status'] = 'VALID';
        try {
            $sModel = new StockModel();
            $stockInfo = $sModel->field('stock,sku,spu,country_bn,lang')->where($condition)->order('stock DESC')->select();
            $data = [];
            if ($stockInfo) {
                foreach ($stockInfo as $item) {
                    if (isset($data[$item['sku']])) {
                        continue;
                    }
                    $data[$item['sku']] = $item;
                }
            }
            return $data;
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getSkuStockBySku:' . $e, Log::ERR);
            return false;
        }
    }

    /**
     * 我的购物车
     */
    public function myShoppingCar($input=[]){
        if(!isset($input) || empty($input) || !isset($input['skus']) || !isset($input['lang'])){
            return false;
        }
        if(isset($input['type']) && $input['type']!==0 && !isset($input['country_bn'])){
            return false;
        }
        try{
            $goodsModel= new GoodsModel();
            $goodsTable = $goodsModel->getTableName();
            $result = $input['skus'];
            if($result){
                $skus = [];
                $spus = [];
                foreach($result as $index =>$item){
                    $skus[] = $item['sku'];
                    $spus[] = $item['spu'];
                }

                $goodsModel= new GoodsModel();
                $goodsTable = $goodsModel->getTableName();
                $productModel = new ProductModel();
                $productTable =$productModel->getTableName();
                $goods = $goodsModel->field("$goodsTable.spu,$goodsTable.sku,$goodsTable.name,$goodsTable.show_name,$goodsTable.min_order_qty,$goodsTable.min_pack_naked_qty,$goodsTable.nude_cargo_unit,$goodsTable.min_pack_unit,$productTable.name as spu_name,$productTable.show_name as spu_show_name,$goodsTable.lang,$goodsTable.model,$goodsTable.status,$goodsTable.deleted_flag")
                    ->join("$productTable ON $productTable.spu=$goodsTable.spu AND $productTable.lang=$goodsTable.lang")->where(["$goodsTable.sku"=>['in',$skus], "$goodsTable.lang"=>$input['lang'], "$goodsTable.deleted_flag"=>'N'])->select();
                $goodsAry = [];
                foreach($goods as $r){
                    $r['name'] = empty($r['show_name']) ? (empty($r['name']) ? (empty($r['spu_show_name']) ? $r['spu_name'] : $r['spu_show_name']) : $r['name']): $r['show_name'];
                    if($input['type']){
                        $r['priceAry'] = $productModel->getSkuPriceByCount($r['sku'], $input['country_bn'], $result[$r['sku']]['buy_number']);
                    }
                    $goodsAry[$r['sku']] = $r;
                }

                //库存
                $stockAry = [];
                if($input['type']) {
                    $stockAry = $productModel->getSkuStockBySku( $skus , $input['country_bn'] , $input[ 'lang' ] );
                }

                //扩展属性
                $gattrModel = new GoodsAttrModel();
                $condition_attr = ['sku'=>['in', $skus], 'lang'=>$input['lang'], 'deleted_flag'=>'N'];
                $attrs = $gattrModel->field('sku,spec_attrs')->where($condition_attr)->select();
                $attrAry = [];
                foreach($attrs as $attr){
                    $attrAry[$attr['sku']] = json_decode($attr['spec_attrs'],true);
                }

                //图
                $attachModel = new ProductAttachModel();
                $attachs = $attachModel->getAttachBySpu( $spus );
                $dataAttach = [''];
                foreach ( $attachs as $r ) {
                    if ( isset( $dataAttach[ $r[ 'spu' ] ] ) ) {
                        if ( $r[ 'default_flag' ] == 'Y' ) {
                            $dataAttach[ $r[ 'spu' ] ] = $r[ 'attach_url' ];
                        }
                        continue;
                    }
                    $dataAttach[ $r[ 'spu' ] ] = $r[ 'attach_url' ];
                }
            }
            return $result ? ['skuAry'=>$result, 'infoAry' =>$goodsAry, 'thumbs'=>$dataAttach, 'attrAry'=>$attrAry, 'stockAry'=>$stockAry] : [];
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【ShoppingCar】 myShoppingCar:' . $e , Log::ERR);
            return false;
        }
    }

}
