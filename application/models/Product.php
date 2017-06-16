<?php
/**
 * 产品.
 * User: linkai
 * Date: 2017/6/15
 * Time: 18:52
 */
class ProductModel extends PublicModel{
    //数据库 表映射
    protected $dbName = 'erui_db_ddl_goods';
    protected $tableName = 'product';

    //状态
    const STATUS_NORMAL = 'NORMAL'; //发布
    const STATUS_TEST = 'TEST'; //测试；
    const STATUS_CHECKING = 'CHECKING'; //审核中；
    const STATUS_CLOSED = 'CLOSED';  //关闭
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    //推荐状态
    const RECOMMEND_Y = 'Y';
    const RECOMMEND_N = 'N';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 此方法暂时没用到  测试用的
     * @param array $condition
     */
    public function getList($condition=[]){
        $showCatProduct = new ShowCatProductModel();
        $field  =   $showCatProduct->getTableName().'.spu,'.
                    $showCatProduct->getTableName().'.show_cat_no';
        $where = array(
            $showCatProduct->getTableName().'.show_cat_no' => 'C0001'
        );
        //STATUS_DRAFT
        $result = $this->field($field)->join($showCatProduct->getTableName().' ON '.$showCatProduct->getTableName().'.spu='.$this->dbName.'.'.$this->tablePrefix.$this->tableName.'.spu','RIGHT')->where($where)->select();
        var_dump($result);
        die;
       // $field = empty($field) ? 'lang,spu,show_name,description' : $field ;

       // $this->field($field)->join(' ',)->where()->select();
    }

    /**
     * 根据SPU获取品牌
     * @param string $spu
     * @param $lang
     * @return string
     */
    public function getBrandBySpu($spu='',$lang){
        if(empty($spu))
            return '';

        $condition =array(
            'spu'=>$spu,
            'status'=>self::STATUS_NORMAL,
            'lang'=>$lang
        );
        $result = $this->field('brand')->where($condition)->find();
        if($result){
            return $result['brand'];
        }
        return '';
    }

}