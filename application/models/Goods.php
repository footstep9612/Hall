<?php

/**
 * SKU
 * User: linkai
 * Date: 2017/6/15
 * Time: 21:04
 */
class GoodsModel extends PublicModel {

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_TEST = 'TEST'; //测试；
    const STATUS_CHECKING = 'CHECKING'; //审核中；
    const STATUS_INVALID = 'INVALID';  //无效
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    public function __construct() {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'goods';

        parent::__construct();
    }

    /**
     * pc-sku商品详情
     * klp
     */
    public function getGoodsInfo($sku, $lang = '') {
        $lang = $lang ? strtolower($lang) : (browser_lang() ? browser_lang() : 'en');
        $field = 'id,sku,lang,spu,qrcode,name,show_name,model,description';
        $condition = array(
            'sku' => $sku
        );

        try {
            //缓存数据redis
<<<<<<< HEAD
            $key_redis = md5(json_encode($condition));
            if(redisHashExist('data',$key_redis)){
                $result = redisHashGet('data',$key_redis);
                return $result ? $result : array();
=======
            $key_redis = md5(json_encode($condition . time()));
            if (redisExist($key_redis)) {
                $result = redisHashGet('data', $key_redis);
                //判断语言,返回对应语言集
                $data = array();
                if ('' != $lang) {
                    foreach ($result as $val) {
                        if ($val['lang'] == $lang) {
                            $data[$val['lang']] = $val;
                            $data['attachs'] = $attach ? $attach : array();
                        }
                    }
                    return $data ? $data : array();
                } else {
                    $result['attachs'] = $attach ? $attach : array();
                    return $result ? $result : array();
                }
>>>>>>> 262dd875e6973a78de322d9480ce4e1b44a791e8
            } else {
                $result = $this->field($field)->where($condition)->select();
                if ($result) {
                    $data = array(
                        'lang' => $lang
                    );
                    //语言分组
                    foreach ($result as $k => $v) {
                        $data[$v['lang']] = $v;
                    }

<<<<<<< HEAD
                    //查询商品附件(未分语言)
                    $skuAchModel = new GoodsAchModel();
                    $where['sku'] = $sku;
                    $attach = $skuAchModel->getInfoByAch($where);
                    $data['attachs'] = $attach ? $attach : array();

                    redisHashSet('data',$key_redis,$data);
=======
                    redisHashSet('data', $key_redis, $data);
>>>>>>> 262dd875e6973a78de322d9480ce4e1b44a791e8
                    return $data;
                } else {
                    return array();
                }
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * SKU基本信息
     */
<<<<<<< HEAD
    public function getInfo($sku, $lang)
    {
        $field = 'id,sku,spu,lang,show_name,model,';
=======
    public function getInfo($sku, $lang) {
        $field = 'sku,spu,lang,show_name,model';
>>>>>>> 262dd875e6973a78de322d9480ce4e1b44a791e8
        $condition = array(
            'sku' => $sku,
            'lang' => $lang
        );
<<<<<<< HEAD
        try{
=======

        try {
>>>>>>> 262dd875e6973a78de322d9480ce4e1b44a791e8
            //缓存数据的判断读取
            $redis_key = md5(json_encode($condition));
            if (redisExist($redis_key)) {
                $result = redisGet($redis_key);
                return $result ? $result : false;
            } else {
                $result = $this->field($field)->where($condition)->find();
                if ($result) {
                    $data = array(
                        'lang' => $lang
                    );
                    //语言分组
                    foreach ($result as $k => $v) {
                        $data[$v['lang']] = $v;
                    }
                    //查询属性
<<<<<<< HEAD
		            $skuAttrModel = new GoodsAttrModel();
		            $attrs = $skuAttrModel->getAttrBySku($sku, $lang);
		            $result['attrs'] = $attrs;
            
                    redisSet($redis_key,$result);
=======
                    $skuAttrModel = new GoodsAttrModel();
                    $where['sku'] = $sku;
                    $attrs = $skuAttrModel->getAttrBySku($where, $lang);
                    $result['attrs'] = $attrs;

                    redisSet($redis_key, $result);
>>>>>>> 262dd875e6973a78de322d9480ce4e1b44a791e8
                    return $result;
                } else {
                    return array();
                }
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 根据spu获取sku数
     * @param string $spu spu编码
     * @param string $lang 语言
     * @retrun int
     */
    public function getCountBySpu($spu = '', $lang = '') {
        $condition = array(
            'status' => array('neq', self::STATUS_NORMAL)
        );
        if ($spu != '') {
            $condition['spu'] = $spu;
        }
        if ($lang != '') {
            $condition['lang'] = $lang;
        }


        try {
            //redis 操作
            $redis_key = md5(json_encode($condition));
            if (redisExist($redis_key)) {
                return redisGet($redis_key);
            } else {
                $count = $this->where($condition)->count('id');
                redisSet($redis_key, $count);
                return $count ? $count : 0;
            }
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * sku 列表 （admin）
     */
    public function getList($condition = []) {
        //取product表名
        $productModel = new ProductModel();
        $ptable = $productModel->getTableName();

        //获取当前表名
        $thistable = $this->getTableName();

        $field = "$ptable.source,$ptable.supplier_name,$ptable.brand,$ptable.name as spu_name,$thistable.lang,$thistable.id,$thistable.sku,$thistable.spu,$thistable.status,$thistable.name,$thistable.model,$thistable.created_by,$thistable.created_at";

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

        //按供应商
        if (isset($condition['supplier_name'])) {
            $where["$ptable.supplier_name"] = array('like', $condition['supplier_name']);
        }
        //按品牌
        if (isset($condition['brand'])) {
            $where["$ptable.brand"] = $condition['brand'];
        }

        //按分类名称
        //是否已定价
        if (isset($condition['pricing_flag'])) {
            $where["$thistable.pricing_flag"] = $condition['pricing_flag'];
        }

        //sku_name
        if (isset($condition['name'])) {
            $where["$thistable.name"] = array('like', $condition['name']);
        }

        //sku id  这里用sku编号
        if (isset($condition['id'])) {
            $where["$thistable.sku"] = $condition['id'];
        }

        $current_no = $condition['current_no'] ? $condition['current_no'] : 1;
        $pagesize = $condition['pagesize'] ? $condition['pagesize'] : 10;

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

    /**

     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author klp
     */
    public function create_data($createcondition)
    {
        $where = [];
        $data = $this->condition($createcondition);
        return $this->where($where)->save($data);
    }


    //公共部分处理
    public function condition($condition, $username = '')
    {
        if ($condition['id']) {
            $where['id'] = $condition['id'];
        }
        if ($condition['lang']) {
            $data['lang'] = $condition['lang'];
        }
        if ($condition['spu']) {
            $data['spu'] = $condition['spu'];
        }
        if ($condition['sku']) {
            $data['sku'] = $condition['sku'];
        }
        if ($condition['cat_no']) {
            $data['cat_no'] = $condition['cat_no'];
        }
        if ($condition['attr_value_type']) {
            $data['attr_value_type'] = $condition['attr_value_type'];
        }
        if ($condition['attr_group']) {
            $data['attr_group'] = $condition['attr_group'];
        }
        if ($condition['sort_order']) {
            $data['sort_order'] = $condition['sort_order'];
        }
        switch ($condition['status']) {
            case self::STATUS_DELETED:
                $data['status'] = $condition['status'];
                break;
            case self::STATUS_VALID:
                $data['status'] = $condition['status'];
                break;
            case self::STATUS_INVALID:
                $data['status'] = $condition['status'];
                break;
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = $username;

        $attrs = array();
        if ($condition['goods_flag']) {
            foreach($condition['goods_flag'] as $v){
                $v['goods_flag'] = 'Y';
                $v['spec_flag'] = 'N';
                $v['logi_flag'] = 'N';
                $v['hs_flag'] = 'N';
                $r = array_merge($data,$v);
                $attrs[] = $r;
            }
        } elseif($condition['spec_flag']){
            foreach($condition['spec_flag'] as $v){
                $v['goods_flag'] = 'N';
                $v['spec_flag'] = 'Y';
                $v['logi_flag'] = 'N';
                $v['hs_flag'] = 'N';
                $r = array_merge($data,$v);
                $attrs[] = $r;
            }
        } elseif($condition['logi_flag']){
            foreach($condition['logi_flag'] as $v){
                $v['goods_flag'] = 'N';
                $v['spec_flag'] = 'N';
                $v['logi_flag'] = 'Y';
                $v['hs_flag'] = 'N';
                $r = array_merge($data,$v);
                $attrs[] = $r;
            }
        } elseif($condition['hs_flag']){
            foreach($condition['hs_flag'] as $v){
                $v['goods_flag'] = 'N';
                $v['spec_flag'] = 'N';
                $v['logi_flag'] = 'N';
                $v['hs_flag'] = 'Y';
                $r = array_merge($data,$v);
                $attrs[] = $r;
            }
        }

    }



    /**
     * 根据sku获取spu
     * @param string $sku sku编码
     * @return bool
     */
    public function getSpubySku($sku = '', $lang = '') {
        if (empty($sku) || empty($lang))
            return false;

        $result = $this->field('spu')->where(array('sku' => $sku, 'lang' => $lang, 'status' => self::STATUS_VALID))->find();
        if ($result) {
            return $result['spu'];
        }
        return false;
    }

}
<<<<<<< HEAD

=======
>>>>>>> 262dd875e6973a78de322d9480ce4e1b44a791e8
