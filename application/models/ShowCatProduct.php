<?php
/**
 * 展示分类与产品映射
 * User: linkai
 * Date: 2017/6/15
 * Time: 19:24
 */
class ShowCatProductModel extends PublicModel{
    //状态
    const STATUS_DRAFT = 'DRAFT';    //草稿
    const STATUS_APPROVING = 'APPROVING';    //审核
    const STATUS_VALID = 'VALID';    //生效
    const STATUS_DELETED = 'DELETED';    //删除

    public function __construct()
    {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj=Yaf_Registry::get("config");
        $config_db=$config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'show_cat_product';

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
        $gtable = $goods->getTableName();
        $field = "$gtable.spu,$gtable.show_name,$gtable.sku,$gtable.model";
        $condition = array(
            $this->getTableName().'.status'=>self::STATUS_VALID,
            $this->getTableName().'.cat_no'=>$show_cat_no,
            $gtable.'.status'=>$goods::STATUS_VALID
        );
        $condition["$gtable.lang"] = $lang;
        try {
            $return = array(
                'count' => 0,
                'current_no' => $current_no,
                'pagesize' => $pagesize
            );
            $count = $this->field($field)->join($goods->getTableName() . ' ON ' . $this->getTableName() . ".spu=$gtable.spu", 'LEFT')->where($condition)->count();
            $result =$this->field($field)->join($goods->getTableName() . ' ON ' . $this->getTableName() . ".spu=$gtable.spu", 'LEFT')->where($condition)->page($current_no, $pagesize)->select();
            if ($result) {
                $return['count'] = $count;
                $gach = new GoodsAchModel();
                $gattr = new GoodsAttrModel();
                foreach($result as $k =>$item){
                    //查询附件图
                    $attach = $gach->getInfoByAch( array('sku'=>$item['sku']));
                    $result[$k]['attachs'] = $attach;

                    //查询规格
                    $attr = $gattr->getSpecBySku($item['sku'],$lang);
                    $result[$k]['spec'] = $attr;
                }
                $return['data'] = $result;
            }
            return $return;
        }catch (Exception $e){
            return false;
        }

    }
}