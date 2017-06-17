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

    //状态
    const STATUS_NORMAL = 'NORMAL'; //发布
    const STATUS_TEST = 'TEST'; //测试；
    const STATUS_CHECKING = 'CHECKING'; //审核中；
    const STATUS_CLOSED = 'CLOSED';  //关闭
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

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
            //查询型号
            $model = $this->getModelBySku($result['sku'],$lang);
            $result['model'] = $model;

            //查询属性
            $skuAttrModel = new GoodsAttrModel();
            $where['sku'] = $sku;
            $attrs = $skuAttrModel->getAttrBySku($where,$lang);

            $data = array(
                'lang' => $lang
            );
            if($result){
                foreach($result as $key => $val){
                    $val['attrs'] = $attrs[$val[$lang]];
                    $data[$val[$lang]] = $val;
                }
            }
            return $result;
        }
        return false;
    }


    /**
     * 根据spu获取sku数
     * @param string $spu  spu编码
     * @param string $lang 语言
     * @retrun int
     */
    public function getCountBySpu($spu='',$lang=''){
        /**
         * 统计这  后期也注意通过缓存处理下
         */
        $condition = array(
            'status' => array('neq'  ,self::STATUS_NORMAL)
        );
        if($spu != ''){
            $condition['spu'] = $spu;
        }
        if($lang!=''){
            $condition['lang'] = $lang;
        }

        $count = $this->where($condition)->count('id');
        return $count ? $count : 0 ;
    }

    /**
     * sku 列表 （admin）
     */
    public function getList($condition=[],$current_no,$pagesize){
        $productModel = new ProductModel();
        $ptable = $productModel->getTableName();

        $thistable = $this->getTableName();
        $field = "$thistable.lang,$thistable.id,$thistable.sku,$thistable.spu,$thistable.name,$thistable.model,$thistable.created_by,$thistable.created_at";

        $condition = array(
            "$thistable.status" => array('neq',self::STATUS_DELETED)
        );
        //语言 有传递取传递语言，没传递取浏览器，浏览器取不到取en英文
        $condition[$thistable.'lang'] = isset($condition['lang']) ? strtolower($condition['lang']) : (browser_lang() ? browser_lang() : 'en');


        if(isset($condition['source'])){
            $where .= " AND source='".$condition['source']."'";
        }
    }

    /**
     * 根据SPU获取品牌
     * @param string $spu
     * @param $lang
     * @return string
     */
    public function getModelBySku($sku='',$lang){
        if(empty($sku))
            return '';

        $condition =array(
            'sku'=>$sku,
            'status'=>self::STATUS_NORMAL,
            'lang'=>$lang
        );
        $result = $this->field('model')->where($condition)->find();
        if($result){
            return $result['model'];
        }
        return '';
    }

}