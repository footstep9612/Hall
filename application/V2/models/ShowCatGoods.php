<?php
/**
 * name: ShowCatGoods
 * desc: 展示分类与商品映射
 * User: zhangyuliang
 * Date: 2017/7/21
 * Time: 16:58
 */
class ShowCatGoodsModel extends PublicModel {
    protected $dbName = 'erui2_goods'; //数据库名称
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
        if(empty($condition['onshelf_flag'])){
            $results['code'] = '-101';
            $results['message'] = '缺少上架状态!';
        }
        if(empty($condition['name'])){
            $results['code'] = '-101';
            $results['message'] = '缺少添加人!';
        }

        $showcat = explode(',',$condition['cat_no']);
        $linecat = [];
        foreach($showcat as $val) {
            foreach ($condition['skus'] as $sku) {
                $test['lang'] = $condition['lang'];
                $test['spu'] = $condition['spu'];
                $test['sku'] = $sku['sku'];
                $test['cat_no'] = $val;
                $test['onshelf_flag'] = strtoupper($condition['onshelf_flag']);
                $test['created_by'] = $condition['created_by'];
                $test['created_at'] = $this->getTime();
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

    /**
     * 产品下架删除数据
     * @param array $condition
     * @return array
     * @author zhangyuliang
     */
    public function deleteData($condition = []){
        if(!empty($condition['lang'])) {
            $where['lang'] = $condition['lang'];
        }else{
            $results['code'] = '-101';
            $results['message'] = '缺少lang!';
        }
        if(empty($condition['spu'])){
            $where['spu'] = $condition['spu'];
        }else{
            $results['code'] = '-101';
            $results['message'] = '缺少SPU!';
        }

        try {
            $id = $this->where($where)->delete();
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

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime(){
        return date('Y-m-d h:i:s',time());
    }
}