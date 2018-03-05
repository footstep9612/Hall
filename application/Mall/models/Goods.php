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
            $gaModel = new GoodsAttrModel();
            $gaTable = $gaModel->getTableName();
            $result = $this->field("$thisTable.spu,$thisTable.sku,$thisTable.name,$thisTable.show_name,$thisTable.show_name_loc,$thisTable.model,$thisTable.lang,$thisTable.min_pack_unit,$thisTable.min_pack_naked_qty,$thisTable.nude_cargo_unit,$productTable.brand,$productTable.name as spu_name,$productTable.show_name as spu_show_name,$gaTable.spec_attrs")
                ->join($productTable." ON $thisTable.spu=$productTable.spu AND $thisTable.lang=$productTable.lang")->join($gaTable." ON $thisTable.sku=$gaTable.sku AND $thisTable.lang=$gaTable.lang")->where($condition)->select();
            if($result){
                foreach($result as $index =>$item){
                    $item['name'] = empty($item['show_name']) ? (empty($item['name']) ? (empty($item['spu_show_name']) ? $item['spu_name'] : $item['spu_show_name']) : $item['name']) : $item['show_name'];
                    $result[$item['sku']] = $item;
                    unset($result[$index]);
                }
            }
            return $result ? $result : [];
        }catch (Exception $e){
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【Goods】getInfoBySku:' . $e , Log::ERR);
            return false;
        }
    }

}