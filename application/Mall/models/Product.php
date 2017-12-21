<?php
/**
 * 产品
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/8
 * Time: 11:45
 */
class ProductModel extends PublicModel{
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
    public function getInfoBySpu($spu = '',$lang = '', $stock = false, $country_bn = ''){
        if(empty($spu) || empty($lang)){
            return false;
        }

        $condition = ['spu'=>$spu,'lang'=>$lang, 'status'=>'VALID', 'deleted_flag'=>'N'];
        try{
            $spuInfo = $this->field('spu,name,show_name,brand,exe_standard,warranty,resp_time,resp_rate,description,exe_standard,tech_paras,advantages,principle,app_scope,properties')->where($condition)->find();
            if($spuInfo){
                //附件
                $attachModel = new ProductAttachModel();
                $attachs = $attachModel->getAttachBySpu($spu);
                $spuInfo['attach'] = $attachs ? $attachs : [];

                //最小订货数量
                $condition_order = ['spu'=>$spu, 'lang'=>$lang, 'status'=>'VALID', 'deleted_flag'=>'N'];
                if($stock){
                    $stockModel = new StockModel();
                    $condition_order['country_bn'] = $country_bn;
                    $stockAry = $stockModel->field('sku,stock')->where($condition_order)->select();
                    $skus = [];
                    $stocks = 0;
                    foreach($stockAry as $r){
                        $skus[] = $r['sku'];
                        $stocks = $stocks + $r['stock'];
                    }
                    $spuInfo['stock'] = $stocks;    //库存

                    //现货价格
                    $scpModel = new StockCostPriceModel();
                    $condition_price = ['country_bn'=>$country_bn, 'sku'=>['in',$skus], 'status'=>'VALID', 'deleted_flag'=>'N', 'price_validity_start'=>['elt',date('Y-m-d',time())]];
                    $priceInfo = $scpModel->field('min_price')->where($condition_price)->order('min_price')->find();
                    $spuInfo['price'] = $priceInfo ? $priceInfo['min_price'] : '';

                    $condition_order = ['sku'=>['in',$skus],'lang'=>$lang];    //现货初始化最小订货量查询条件
                }
                $goodsModel = new GoodsModel();
                $min_order_qty = $goodsModel->field('min_order_qty')->where($condition_order)->order('min_order_qty')->find();
                $spuInfo['min_order_qty'] = $min_order_qty ? $min_order_qty['min_order_qty'] : 1;
            }
            return $spuInfo ? $spuInfo : false;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getInfoBySpu:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 获取sku列表
     * @param $input
     * @return array|bool
     */
    public function getSkuList($input){
        if(!isset($input['spu']) || empty($input['spu'])){
            jsonReturn('', ErrorMsg::NOTNULL_SPU);
        }

        if(!isset($input['lang']) || empty($input['lang'])){
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        $condition = ['spu'=>$input['spu'], 'lang'=>$input['lang']];

        try{
            //现货处理
            $stock = false;
            $skus = [];
            if(isset($input['stock']) && isset($input['country_bn']) && $input['stock'] && $input['country_bn']){
                $stock = true;
                $condition_stock = ['spu'=>$input['spu'], 'lang'=>$input['lang'], 'country_bn'=>$input['country_bn'], 'status'=>'VALID', 'deleted_flag'=>'N'];
                $stockModel = new StockModel();
                $stockSku = $stockModel->field('sku,stock')->where($condition_stock)->select();
                foreach($stockSku as $item){
                    $skus[] = $item['sku'];
                    $skuStock[$item['sku']] =  $item['stock'];
                }
                $condition['sku'] = ['in',$skus];
            }

            //订货号
            if(isset($input['sku']) && !empty($input['sku'])){
                $condition['sku'] = $input['sku'];
            }

            //型号
            if(isset($input['model']) && !empty($input['model'])){
                $condition['model'] = $input['model'];
            }

            //包装数量
            if(isset($input['min_pack_naked_qty']) && !empty($input['min_pack_naked_qty'])){
                $condition['min_pack_naked_qty'] = $input['min_pack_naked_qty'];
            }

            //出货周期
            if(isset($input['exw_days']) && !empty($input['exw_days'])){
                $condition['exw_days'] = $input['exw_days'];
            }
            $condition['status'] = 'VALID';
            $condition['deleted_flag'] = 'N';

            $current_no = (isset($input['current_no']) && is_numeric($input['current_no'])) ? $input['current_no'] : 1;
            $pageSize = (isset($input['pageSize']) && is_numeric($input['pageSize'])) ? $input['pageSize'] : 10;

            $goodsModel = new GoodsModel();
            $result = $goodsModel->field('sku,model,min_pack_naked_qty,nude_cargo_unit,min_pack_unit,min_order_qty,exw_days')->where($condition)->limit(($current_no-1)*$pageSize,$pageSize)->select();
            if($result){
                //扩展属性
                $gattrModel = new GoodsAttrModel();
                $condition_attr = ['spu'=>$input['spu'], 'lang'=>$input['lang'], 'deleted_flag'=>'N'];
                $attrs = $gattrModel->field('sku,spec_attrs')->where($condition_attr)->select();

                $attr_key = $attr_value = [];
                foreach($attrs as $index => $attr){
                    $attrInfo = json_decode($attr['spec_attrs'],true);
                    foreach($attrInfo as $key => $value){
                        if(!isset($attr_key[$key])){
                            $attr_key[$key] = $key;
                        }
                        $attr_value[$attr['sku']][$key] = $value;
                    }
                }

                if($stock){
                    //现货价格
                    foreach($result as $index =>$item){
                        $priceInfo = self::getSkuPriceByCount($item['sku'],$input['country_bn'],$item['min_order_qty']);
                        $result[$index]['price'] = $priceInfo['price'];
                        $result[$index]['price_cur_bn'] = $priceInfo['price_cur_bn'];
                        $result[$index]['price_symbol'] = $priceInfo['price_symbol'];
                    }
                }
            }
            return $result ? ['skuAry'=>$result, 'stockAry'=>$skuStock ? $skuStock : [], 'attr_key'=>$attr_key, 'attr_value'=>$attr_value] : [];
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getSkuList:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 关联产品
     */
    public function getRelationSpu($input){
        if(!isset($input['spu']) || empty($input['spu'])){
            jsonReturn('', ErrorMsg::NOTNULL_SPU);
        }

        if(!isset($input['lang']) || empty($input['lang'])){
            jsonReturn('', ErrorMsg::NOTNULL_LANG);
        }

        $condition = ['spu'=>$input['spu'], 'lang'=>$input['lang']];
        try {
            $RelationModel = new ProductRelationModel();
            $relationSpu = $RelationModel->field( 'relation_spu' )->where( $condition )->select();
            $data = [ ];
            if ( $relationSpu ) {
                $spus = [ ];
                foreach ( $relationSpu as $index => $item ) {
                    $spus[] = $item[ 'relation_spu' ];
                }
                $data[ 'spu' ] = $this->field( 'show_name,name,spu' )->where( [ 'spu' => [ 'in' , $spus ] , 'lang' => $input[ 'lang' ] ] )->select();

                //附件图
                $attachModel = new ProductAttachModel();
                $attachs = $attachModel->getAttachBySpu( $spus );
                $dataAttach = [ ];
                foreach ( $attachs as $r ) {
                    if ( isset( $dataAttach[ $r[ 'spu' ] ] ) ) {
                        if ( $r[ 'default_flag' ] == 'Y' ) {
                            $dataAttach[ $r[ 'spu' ] ] = $r[ 'attach_url' ];
                        }
                        continue;
                    }
                    $dataAttach[ $r[ 'spu' ] ] = $r[ 'attach_url' ];
                }
                $data[ 'thumbs' ] = $dataAttach;
            }
            return $data;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getRelationSpu:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 根据相应数量返回相应价格
     */
    public function getSkuPriceByCount($sku='', $country_bn='', $count=''){
        if(!isset($sku) || empty($sku) || !isset($country_bn) || empty($country_bn) || !isset($count) || !is_numeric($count)){
            return '';
        }

        $condition = ['sku'=>$sku, 'country_bn'=>$country_bn, 'price_validity_start'=>['elt',date('Y-m-d',time())], 'min_purchase_qty'=>['elt',$count]];
        try{
            $scpModel = new StockCostPriceModel();
            $priceInfo = $scpModel->field('min_price as price,min_purchase_qty,max_purchase_qty,price_validity_end,price_cur_bn,price_symbol')->where($condition)->order('min_purchase_qty DESC')->select();
            if($priceInfo){
                foreach($priceInfo as $item){
                    if(($item['price_validity_end'] >= date('Y-m-d',time()) || empty($item['price_validity_end'])) && (empty($item['max_purchase_qty']) || $item['max_purchase_qty']>= $count)){
                        return $item;
                        break;
                    }
                }
            }
            return '';
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getSkuPriceByCount:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 根据sku跟国家获取库存
     * @param string $sku
     * @param string $country_bn
     * @return array
     */
    public function getSkuStockBySku($sku,$country_bn='',$lang=''){
        if(!isset($sku) || empty($sku) || !isset($country_bn) || empty($country_bn) || !isset($lang) || empty($lang)){
            return [];
        }

        if(is_array($sku)){
            $condition['sku'] = ['in',$sku];
        }else{
            $condition['sku'] = $sku;
        }
        $condition['country_bn'] = $country_bn;
        $condition['lang'] = $lang;
        $condition['deleted_flag'] = 'N';
        $condition['status'] = 'VALID';
        try{
            $sModel = new StockModel();
            $stockInfo = $sModel->field('stock,sku,spu,country_bn,lang')->where($condition)->order('stock DESC')->select();
            $data = [];
            if($stockInfo){
                foreach($stockInfo as $item){
                    if(isset($data[$item['sku']])){
                        continue;
                    }
                    $data[$item['sku']] = $item;
                }
            }
            return $data;
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Product】getSkuStockBySku:' . $e , Log::ERR);
            return false;
        }
    }

}