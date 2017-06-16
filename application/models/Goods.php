<?php
/**
 * SKU
 * User: linkai
 * Date: 2017/6/15
 * Time: 21:04
 */
class GoodsModel extends PublicModel{
    //数据库 表映射
    protected $dbName = 'erui_db_ddl_goods';
    protected $tableName = 'goods';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * SKU详情
     */
    public function getInfo($sku,$lang){
        $field = 'sku,spu,lang,show_name,model';
        $condition = array(
            'sku' => $sku,
            'lang'=>$lang
        );

        /**
         * 缓存数据的判断读取
         */
        $result = $this->field($field)->where($condition)->find();
        if($result){
            //查询品牌
            $productModel = new ProductModel();
            $brand = $productModel->getBrandBySpu($result['spu'],$lang);
            $result['brand'] = $brand;

            //查询属性
            return $result;
        }
        return false;
    }
}