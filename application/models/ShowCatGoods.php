<?php
/**
 * name: ProductLineCat
 * desc: 产品线和物料分类关联表
 * User: zhangyuliang
 * Date: 2017/7/21
 * Time: 16:58
 */
class ShowCatGoodsModel extends PublicModel {
    protected $dbName = 'erui_config'; //数据库名称
    protected $tableName = 'show_cat_goods'; //数据表表名

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 商品上架添加数据
     * @param array $condition
     * @return array
     * @author zhangyuliang
     */
    public function addData($condition = []){
        if(empty($condition['lang'])){
            $results['code'] = '-101';
            $results['message'] = '缺少lang!';
        }
        if(empty($condition['spu'])){
            $results['code'] = '-101';
            $results['message'] = '缺少SPU!';
        }
        if(empty($condition['skus'])){
            $results['code'] = '-101';
            $results['message'] = '缺少SKU!';
        }
        if(empty($condition['show_cat'])){
            $results['code'] = '-101';
            $results['message'] = '缺少显示分类!';
        }
        if(empty($condition['show_name'])){
            $results['code'] = '-101';
            $results['message'] = '缺少展示名称!';
        }
        if(empty($condition['created_by'])){
            $results['code'] = '-101';
            $results['message'] = '缺少添加人!';
        }

        $showcat = explode(',',$condition['show_cat']);
        $linecat = [];
        foreach($showcat as $val) {
            foreach ($condition['skus'] as $sku) {
                $test['lang'] = $condition['lang'];
                $test['spu'] = $condition['spu'];
                $test['sku'] = $sku['sku'];
                $test['cat_no'] = $val;
                $test['show_name'] = $condition['show_name'];
                $test['created_by'] = $condition['created_by'];
                $test['create_at'] = $this->getTime();
                $linecat[] = $test;
            }
        }
        try {
            $id = $this->addAll($linecat);
            if(isset($id)){
                $results['code'] = '1';
                $results['message'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['message'] = '添加失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }
}