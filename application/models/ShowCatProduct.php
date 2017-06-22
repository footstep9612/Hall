<?php
/**
 * 展示分类与产品映射
 * User: linkai
 * Date: 2017/6/15
 * Time: 19:24
 */
class ShowCatProductModel extends PublicModel{
    //数据库 表映射
    protected $dbName = 'erui_goods';
    protected $tableName = 'show_cat_product';

    //状态
    const STATUS_DRAFT = 'DRAFT';    //草稿
    const STATUS_APPROVING = 'APPROVING';    //审核
    const STATUS_VALID = 'VALID';    //生效
    const STATUS_DELETED = 'DELETED';    //删除

    public function __construct(){
        parent::__construct();
    }

    /**
     * 根据展示分类编号查询sku
     * @param string $show_cat_no 展示分类编号
     * @param int $current_num 当前页
     * @param int $pagesize 每页显示多少条
     * @return array|bool
     */
    public function getSkuByCat($show_cat_no='',$lang='',$current_no=1,$pagesize=10){
        if(empty($show_cat_no))
            return false;

        $goods = new GoodsModel();
        $field = 'g.spu,g.show_name,g.sku,g.model';
        $condition = array(
            'status'=>self::STATUS_VALID,
            'show_cat_no'=>$show_cat_no,
        );
        $condition['lang'] = $lang;
        try {
            $return = array(
                'count' => 0,
                'current_no' => $current_no,
                'pagesize' => $pagesize
            );
            $obj = $this->field($field)->join($goods->getTableName() . ' g ON ' . $this->getTableName() . '.spu=g.spu', 'LEFT')->where($condition);
            $result = $obj->page($current_no, $pagesize)->select();
            if ($result) {
                $return['count'] = $obj->count();
                $return['data'] = $result;
            }
            return $return;
        }catch (Exception $e){
                return false;
        }

    }
}