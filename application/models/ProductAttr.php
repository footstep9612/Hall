<?php

/**
 * Sｐu属性
 * User: linkai
 * Date: 2017/6/17
 * Time: 15:58
 */
class ProductAttrModel extends PublicModel {

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    public function __construct() {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'product_attr';

        parent::__construct();
    }

    /**
     * 根据spu获取属性
     * @param string $spu
     * @param string $lang
     * @return array|bool|mixed
     */
    public function getAttrBySpu($spu = '', $lang = '') {
        if ($spu == '')
            return false;
        if($lang != ''){
            $condition['lang'] = $lang;
        }
        $field = 'lang,attr_group,attr_no,attr_name,attr_value_type,attr_value,value_unit,goods_flag,spec_flag,logi_flag,hs_flag';
        $condition = array(
            'spu' => $spu,
            'lang'=> $lang,
            'status' => self::STATUS_VALID
        );

        //缓存数据redis查询

        $key_redis = md5(json_encode($condition));
        if(redisExist($key_redis)){
            $result = redisGet($key_redis);
            return $result ? json_decode($result) : array();
        $key_redis = md5(json_encode(array('spu' => $spu, 'status' => self::STATUS_VALID) . time()));
        if (redisExist($key_redis)) {
            $result = redisHashGet('pattrs', $key_redis);
            //判断语言,返回对应语言集
            $data = array();
            if ('' != $lang) {
                foreach ($result as $val) {
                    if ($val['lang'] == $lang) {
                        $data[$val['lang']] = $val;
                    }
                }
                return $data ? $data : array();
            } else {
                return $result ? $result : array();
            }
        } else {
            $result = $this->field($field)->where($condition)->select();
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
                $attrs = array();
                foreach ($result as $item) {
                    $group1 = '';
                    if ($item['goods_flag'] == 'Y') {
                        $group1 = 'goods_flag';
                        $attrs[$item['lang']][$group1][] = $item;
                    }
                    if ($item['logi_flag'] == 'Y') {
                        $group1 = 'logi_flag';
                        $attrs[$item['lang']][$group1][] = $item;
                    }
                    if ($item['hs_flag'] == 'Y') {
                        $group1 = 'hs_flag';
                        $attrs[$item['lang']][$group1][] = $item;
                    }
                    if ($item['spec_flag'] == 'Y') {
                        $group1 = 'spec_flag';
                        $attrs[$item['lang']][$group1][] = $item;
                    }
                    if ($group1 == '') {
                        $group1 = 'others';
                        $attrs[$item['lang']][$group1][] = $item;
                    }
                }
                redisSet($key_redis, json_encode($attrs));
                return $attrs;
            } else {
                return array();
            }
        }
    }

}
