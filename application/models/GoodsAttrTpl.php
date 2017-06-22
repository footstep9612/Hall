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

    public function ini()
    {

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
           $category = $this->field('attr_no')->where(array('cat_no' => $cat_no, 'attr_type' => 'CATEGORY'))->select();
           $catModel = new AttrModel();
           if($category){
               $groups = $catModel->field($field)->where(array('attr_no' => $category))->select();
               $cate = $groups? $groups : array();
           }
       } elseif($type == 'PRODUCT'){
           $spuModel = new ProductAttrModel();
           $products = $spuModel->field($field)->where(array('spu' => $spu))->select();
           $product = $products ? $products : array();
       } elseif($type == 'GOODS'){
           $skuModel = new GoodsAttrModel();
            $goods = $skuModel->field($field)->where(array('sku' => $sku))->select();
           $good = $goods ? $goods : array();
       }
        $result = array_merge($common,$cate,$product,$good);
        if ($result) {
            //按语言树形结构
            /**
             * 属性分类:一级
             *   goods_flag - 商品属性
             *   spec_flag - 规格型号
             *   logi_flag  - 物流属性
             *   hs_flag  - 申报要素
             *   Others - 其他　
             */
            $ListTpl = array();
            foreach ($result as $item) {
                $group1 = '';
                if ($item['goods_flag'] == 'Y') {
                    $group1 = 'goods_flag';
                    $ListTpl[$item['lang']][$group1][] = $item;
                }
                if ($item['logi_flag'] == 'Y') {
                    $group1 = 'logi_flag';
                    $ListTpl[$item['lang']][$group1][] = $item;
                }
                if ($item['hs_flag'] == 'Y') {
                    $group1 = 'hs_flag';
                    $ListTpl[$item['lang']][$group1][] = $item;
                }
                if ($item['spec_flag'] == 'Y') {
                    $group1 = 'spec_flag';
                    $ListTpl[$item['lang']][$group1][] = $item;
                }
                if ($group1 == '') {
                    $group1 = 'others';
                    $ListTpl[$item['lang']][$group1][] = $item;
                }
            }
            return $ListTpl;
        } else {
            return array();
        }
    }


}