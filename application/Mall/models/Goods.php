<?php
/**
 * 产品
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/8
 * Time: 11:45
 */
class GoodsModel extends PublicModel{
    protected $tableName = 'goods';
    protected $dbName = 'erui_goods'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据sku获取信息
     * @param $sku
     * @param $lang
     * @return array|bool|mixed
     */
    public function getInfoBySku($sku,$lang){
        if(empty($sku) || empty($lang)){
            return false;
        }

        try{
            $thisTable = $this->getTableName();
            if(is_array($sku)){
                $condition = ["$thisTable.sku" => ['in',$sku]];
            }else{
                $condition = ["$thisTable.sku" => $sku];
            }
            $condition["$thisTable.lang"] = $lang;
            $condition["$thisTable.status"] = 'VALID';
            $condition["$thisTable.deleted_flag"] = 'N';

            $productModel = new ProductModel();
            $productTable = $productModel->getTableName();
            $result = $this->field("$thisTable.spu,$thisTable.sku,$thisTable.name,$thisTable.show_name_loc,$thisTable.model,$thisTable.lang,$thisTable.min_pack_unit,$productTable.brand")
                ->join($productTable." ON $thisTable.spu=$productTable.spu AND $thisTable.lang=$productTable.lang")->where($condition)->select();

            return $result ? $result : [];
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Goods】getInfoBySku:' . $e , Log::ERR);
            return false;
        }
    }

}