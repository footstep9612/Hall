<?php
/**
 * SKU
 * User: linkai
 * Date: 2017/6/15
 * Time: 21:04
 */
class GoodsModel extends PublicModel
{
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
     * pc-sku商品详情
     * klp
     */
    public function getGoodsInfo($sku, $lang = '')
    {
        $lang = $lang ? strtolower($lang) : (browser_lang() ? browser_lang() : 'en');
        $field = 'sku,lang,spu,qrcode,name,show_name,model,description';
        $condition = array(
            'sku' => $sku
        );
        try {
            $result = $this->field($field)->where($condition)->select();
            if ($result) {
                $data = array(
                    'lang' => $lang
                );
                //语言分组
                foreach($result as $k => $v){
                    $data[$v['lang']] = $v;
                }

                //查询附件
                $skuAchModel = new GoodsAchModel();
                $where['sku'] = $result['sku'];
                $attach = $skuAchModel->getInfoByAch($where);

                $data['attachs'] = $attach ? $attach : array();
                return $data;
            }
        } catch(Exception $e){
            return false;
        }
    }

    /**
     * SKU详情
     */
    public function getInfo($sku, $lang)
    {
        $field = 'sku,spu,lang,show_name,model';
        $condition = array(
            'sku' => $sku,
            'lang' => $lang
        );

        /**
         * 缓存数据的判断读取
         */
        $result = $this->field($field)->where($condition)->find();
        if ($result) {
            //查询型号
            $model = $this->getModelBySku($result['sku'], $lang);
            $result['model'] = $model;

            //查询属性
            $skuAttrModel = new GoodsAttrModel();
            $where['sku'] = $sku;
            $attrs = $skuAttrModel->getAttrBySku($where, $lang);

            $data = array(
                'lang' => $lang
            );
            if ($result) {
                foreach ($result as $key => $val) {
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
     * @param string $spu spu编码
     * @param string $lang 语言
     * @retrun int
     */
    public function getCountBySpu($spu = '', $lang = '')
    {
        /**
         * 统计这  后期也注意通过缓存处理下
         */
        $condition = array(
            'status' => array('neq', self::STATUS_NORMAL)
        );
        if ($spu != '') {
            $condition['spu'] = $spu;
        }
        if ($lang != '') {
            $condition['lang'] = $lang;
        }

        $count = $this->where($condition)->count('id');
        return $count ? $count : 0;
    }

    /**
     * sku 列表 （admin）
     */
    public function getList($condition = [], $current_no = 1, $pagesize = 10)
    {
        //取product表名
        $productModel = new ProductModel();
        $ptable = $productModel->getTableName();

        //获取当前表名
        $thistable = $this->getTableName();

        $field = "$thistable.lang,$thistable.id,$thistable.sku,$thistable.spu,$thistable.status,$thistable.name,$thistable.model,$thistable.created_by,$thistable.created_at";

        $where = array();
        //spu 编码
        if (isset($condition['spu'])) {
            $where["$thistable.spu"] = $condition['spu'];
        }

        //审核状态
        if (isset($condition['status'])) {
            $where["$thistable.status"] = $condition['status'];
        }

        //语言
        $lang = '';
        if (isset($condition['lang'])) {
            $where["$thistable.lang"] = $lang = strtolower($condition['lang']);
        }

        //规格型号
        if (isset($condition['model'])) {
            $where["$thistable.model"] = $condition['model'];
        }

        //来源
        if (isset($condition['source'])) {
            $where["$ptable.source"] = $condition['source'];
        }

        //是否已定价
        if (isset($condition['pricing_flag'])) {
            $where["$thistable.pricing_flag"] = $condition['pricing_flag'];
        }

        //sku_name
        if (isset($condition['name'])) {
            $where["$thistable.name"] = $condition['name'];
        }

        //sku id  这里用sku编号
        if (isset($condition['id'])) {
            $where["$thistable.sku"] = $condition['id'];
        }

        try {
            $count = $this->field($field)->join($ptable . " On $ptable.spu = $thistable.spu", 'LEFT')->where($where)->count();
            $result = $this->field($field)->join($ptable . " On $ptable.spu = $thistable.spu", 'LEFT')->where($where)->page($current_no, $pagesize)->select();
            $data = array(
                'lang' => $lang,
                'count' => 0,
                'current_no' => $current_no,
                'pagesize' => $pagesize,
                'data' => array(),
            );
            if ($result) {
                $data['count'] = $count;
                $data['data'] = $result;
            }
            return $data;
        } catch (Exception $e) {
            return false;
        }
    }

}