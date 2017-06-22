<?php
/**
* Description of GoodsAttrTplModel
*
 * @author  klp
*/
class GoodsAttrTplModel extends PublicModel
{
    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'goods_attr_tpl'; //数据表表名

    public function __construct()
    {
        parent::__construct();
    }

    public function ini()
    {
        $this->catModel = new MaterialcatModel();
        $this->spuModel = new ProductAttrModel();
    }

    /**
     * 根据条件获取商品模板属性
     * @param null $where string 条件
     * @return
     */
    public function getlist($type='',$cat_no,$spu,$sku)
    {
        $where = array(
            'attr_type' => ''
        );
        $field = "lang,attr_group,attr_no,attr_name,goods_flag,spec_flag,logi_flag,hs_flag";
        $common = $this->field($field)->where($where)->select();
       if($type == 'CATEGORY'){
           $category = $this->catModel->field('name')->where(array('cat_no' => $cat_no))->select();
           if($category){
               $cate = array();
               foreach($category as $k => $v){
                   $groups = $this->spuModel->field($field)->where(array('spu' => $v['spu']))->select();
                   $cate[] = $groups? $groups : array();
               }
           }
       } elseif($type == 'PRODUCT'){
           $products = $this->spuModel->field($field)->where(array('spu' => $spu))->select();
           $product = $products ? $products : array();
       } elseif($type == 'GOODS'){
           $skuModel = new GoodsAttrModel();
            $goods = $skuModel->field($field)->where(array('sku' => $sku))->select();
           $good = $goods ? $goods : array();
       }
        $result = array_merge($common,$cate,$product,$good);

    }


}