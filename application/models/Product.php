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
     * spu列表
     * @param array $condition = array('lang'=>'',    语言
     *                  'source'=>'',    来源
     *                  'brand' =>'',    品牌
     *                  'meterial_cat_no'=>''    分类
     *                  'keyword'=>''    名称/创建者/spu编码
     *                  'start_time'=>''    创建开始时间
     *                  'end_time' =>''    创建结束时间
     *             )  均为选择型
     * @param int $current_num 当前页
     * @param int $pagesize 每页显示条数
     */
    public function getList($condition=[]){
        $field = "lang,spu,brand,name,created_by,created_at,meterial_cat_no";

        $where = "status <> '".self::STATUS_DELETED."'";
        //语言 有传递取传递语言，没传递取浏览器，浏览器取不到取en英文
        $condition['lang'] = isset($condition['lang']) ? strtolower($condition['lang']) : (browser_lang() ? browser_lang() : 'en');
        $where .= " AND lang='".$condition['lang']."'";

        if(isset($condition['source'])){
            $where .= " AND source='".$condition['source']."'";
        }
        if(isset($condition['brand'])){
            $where .= " AND brand='".$condition['brand']."'";
        }
        if(isset($condition['meterial_cat_no'])){
            $where .= " AND meterial_cat_no='".$condition['meterial_cat_no']."'";
        }
        if(isset($condition['start_time'])){
            $where .= " AND created_at >= '".$condition['start_time']."'";
        }
        if(isset($condition['end_time'])){
            $where .= " AND created_at <= '".$condition['end_time']."'";
        }

        //处理keyword
        if(isset($condition['keyword'])){
            $where .= " AND (name like '%".$condition['keyword']."%'
                            OR show_name like '%".$condition['keyword']."%'
                            OR created_by like '%".$condition['keyword']."%'
                            OR spu = '".$condition['keyword']."'
                          )";
        }

        $current_num = isset($condition['current_no'])?$condition['current_no']:1;
        $pagesize = isset($condition['pagesize'])?$condition['pagesize']:10;
        try{
            $return = array(
                'count' =>0,
                'current_no' => $current_num,
                'pagesize'=>$pagesize
            );
            $result = $this->field($field)->where($where)->order('created_at DESC')->page($current_num,$pagesize)->select();
            $count = $this->field('spu')->where($where)->count();
            if($result){
                //遍历获取分类　　与ｓｋｕ统计
                foreach($result as $k=>$r){
                    //分类
                    $mcatModel = new MaterialcatModel();
                    $mcatInfo = $mcatModel->getMeterialCatByNo($r['meterial_cat_no'], $condition['lang']);
                    $result[$k]['meterial_cat'] = $mcatInfo ? $mcatInfo['name'] : '' ;

                    //sku统计
                    $goodsModel = new GoodsModel();
                    $result[$k]['sku_count'] = $goodsModel->getCountBySpu($r['spu'],$condition['lang']);
                }
                $return['count'] = $count;
                $return['data'] = $result;
                return $return;
            }else{
                $return['count'] = 0;
                $return['data'] = array();
                return $return;
            }
        }catch (Exception $e){
            return false;
        }
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

    /**
     * spu详情
     * @param string $spu    spu编码
     * @param string $lang    语言
     * return array
     */
    public function getInfo($spu='',$lang=''){
        if(empty($spu))
           jsonReturn('','10000','spu不能为空');

        //详情返回四种语言， 这里的lang作当前语言类型返回
        $lang = $lang ? strtolower($lang) : (browser_lang() ? browser_lang() : 'en');
        $condition = array(
            'spu' =>$spu,
            'status' => array( 'neq' , self::STATUS_DELETED),
        );
        $field = 'spu,lang,name,show_name,meterial_cat_no,brand,keywords,description,exe_standard,profile';
        try{
            $result = $this ->field($field)->where($condition)->select();

            //查询品牌
            $brand = $this->getBrandBySpu($result['spu'],$lang);
            $result['brand'] = $brand;

            //查询属性
            $pattrModel = new ProductAttrModel();
            $attrs = $pattrModel->getAttrBySpu($spu);

            $data = array(
                'lang' => $lang
            );
            if($result){
                foreach($result as $k => $r){
                    $r['attrs'] = $attrs[$r['lang']];
                    $data[$r['lang']] = $r;
                }
            }
            return $data;
        }catch (Exception $e){
            return false;
        }
    }

}