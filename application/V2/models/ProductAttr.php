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
    const STATUS_DELETED = 'DELETED'; //删除；

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
        if ($lang != '') {
            $condition['lang'] = $lang;
        }
        $field = 'lang,attr_group,attr_no,attr_name,attr_value_type,attr_value,value_unit,goods_flag,spec_flag,logi_flag,hs_flag';
        $condition = array(
            'spu' => $spu,
            'lang' => $lang,
            'status' => self::STATUS_VALID
        );

        //缓存数据redis查询
        $key_redis = md5(json_encode($condition));
        if (redisExist($key_redis)) {
            $result = redisGet($key_redis);
            return $result ? json_decode($result) : array();
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

    /**
     * 返回处理完的产品属性  -- 公共
     * @author link 2017-06-26
     * @return array
     */
    public function getAttrOrder($condition = []) {
        if (!isset($condition['spu']))
            return array();

        $result = $this->getAttr($condition['spu'], isset($condition['lang']) ? $condition['lang'] : '', isset($condition['attr_type']) ? $condition['attr_type'] : '', isset($condition['status']) ? $condition['status'] : '');
        $data = array();
        if ($result) {
            foreach ($result as $item) {
                $item = (array) $item;
                $group1 = '';
                if ($item['goods_flag'] == 'Y') {
                    $group1 = 'goods_flag';
                    $data[$item['lang']][$group1][] = $item;
                }
                if ($item['logi_flag'] == 'Y') {
                    $group1 = 'logi_flag';
                    $data[$item['lang']][$group1][] = $item;
                }
                if ($item['hs_flag'] == 'Y') {
                    $group1 = 'hs_flag';
                    $data[$item['lang']][$group1][] = $item;
                }
                if ($item['spec_flag'] == 'Y') {
                    $group1 = 'spec_flag';
                    $data[$item['lang']][$group1][] = $item;
                }
                if ($group1 == '') {
                    $group1 = 'others';
                    $data[$item['lang']][$group1][] = $item;
                }
            }
        }
        return $data;
    }

    /**
     * 获取产品属性数组（未处理的数据）　　－　公共
     * @author link  2017-06-26
     * @param string $spu
     * @param string $lang
     * @return array|bool|mixed
     */
    public function getAttr($spu = '', $lang = '', $attr_type = '', $status = '') {
        if ($spu == '')
            return array();

        //组装条件
        $where = array(
            'spu' => $spu,
        );
        if (!empty($lang)) {
            $where['lang'] = $lang;
        }
        if (!empty($status) && in_array(strtoupper($status), array('VALID', 'INVALID', 'DELETED'))) {
            $where['status'] = strtoupper($status);
        }
        if (!empty($attr_type)) {
            switch ($attr_type) {
                case 'goods_flag':
                    $where['goods_flag'] = 'Y';
                    break;
                case 'spec_flag':
                    $where['spec_flag'] = 'Y';
                    break;
                case 'logi_flag':
                    $where['logi_flag'] = 'Y';
                    break;
                case 'hs_flag':
                    $where['hs_flag'] = 'Y';
                    break;
            }
        }

        //redis读取
        if (redisHashExist('Attr', md5(json_encode($where)))) {
            return (array) json_decode(redisHashGet('Attr', md5(json_encode($where))));
        }

        //读取
        try {
            $field = 'lang,attr_no,attr_name,attr_value_type,attr_value,value_unit,attr_group,sort_order,goods_flag,logi_flag,hs_flag,spec_flag,status';
            $result = $this->field($field)->where($where)->order('sort_order')->select();
            if ($result) {
                redisHashSet('Attr', md5(json_encode($where)), json_encode($result));
                return $result;
            }
            return array();
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * spu属性参数处理（门户后台）
     * @author klp
     * @return array
     */
    public function check_data($data = []) {
        $condition['lang'] = $data['lang'] ? $data['lang'] : 'en';
        $condition['spu'] = $data['spu'] ? $data['spu'] : '';
        $condition['attr_value_type'] = $data['attr_value_type'] ? $data['attr_value_type'] : 'String';
        $condition['attr_value'] = $data['attr_value'] ? $data['attr_value'] : '';
        $condition['value_unit'] = $data['value_unit'] ? $data['value_unit'] : 'Empty String';
        $condition['goods_flag'] = $data['goods_flag'] ? $data['goods_flag'] : 'Y';
        $condition['spec_flag'] = $data['spec_flag'] ? $data['spec_flag'] : 'N';
        $condition['logi_flag'] = $data['logi_flag'] ? $data['logi_flag'] : 'N';
        $condition['hs_flag'] = $data['hs_flag'] ? $data['hs_flag'] : 'N';
        $condition['attr_group'] = $data['attr_group'] ? $data['attr_group'] : '';
        $condition['sort_order'] = $data['sort_order'] ? $data['sort_order'] : '1';
        $condition['created_at'] = $data['created_at'] ? $data['created_at'] : date('Y-m-d H:i:s');
        if (isset($data['attr_no'])) {
            $condition['attr_no'] = $data['attr_no'];
        } else {
            JsonReturn('', '-1001', '属性编码不能为空');
        }
        if (isset($data['attr_name'])) {
            $condition['attr_name'] = $data['attr_name'];
        } else {
            JsonReturn('', '-1001', '属性名称不能为空');
        }
        if (isset($data['created_by'])) {
            $condition['created_by'] = $data['created_by'];
        } else {
            JsonReturn('', '-1001', '审核人不能为空');
        }
        if (isset($data['status'])) {
            switch ($data['status']) {
                case self::STATUS_VALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_INVALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_DELETED:
                    $condition['status'] = $data['status'];
                    break;
            }
        } else {
            JsonReturn('', '-1001', '状态不能为空');
        }
        return $condition;
    }

    /**
     * spu属性新增（门户后台）
     * @author klp
     * @return bool
     */
    public function createSpuAttr($data) {
        $arr = [];
        foreach ($data as $value) {
            $arr[] = $this->check_data($value);
        }
        $res = $this->addAll($arr);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * spu属性更新（门户后台）
     * @author klp
     * @return bool
     */
    public function updateSpu($data, $where) {
        $condition = $this->check_data($data);
        if (!empty($where)) {
            return $this->where($where)->save($condition);
        } else {
            JsonReturn('', '-1001', '条件不能为空');
        }
    }

    /**
     * spu属性删除（门户后台）
     * @author klp
     * @return bool
     */
    public function deleteSpu($where) {
        if (!empty($where)) {
            return $this->where($where)->delete();
        } else {
            JsonReturn('', '-1001', '条件不能为空');
        }
    }

    /*
     * 根据SPUS 获取产品属性信息
     * @param mix $spus // 产品SPU数组
     * @param string $lang // 语言 zh en ru es
     * @return mix  属性信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getproduct_attrbyspus($spus, $lang = 'en') {
        if (!$spus || !is_array($spus)) {
            return [];
        }
        try {

            $goods_model = new GoodsAttrModel();

            $product_attrs = $goods_model->field('spu,spec_attrs,ex_goods_attrs,ex_hs_attrs,other_attrs')
                    ->where(['spu' => ['in', $spus],
                        'lang' => $lang,
                        'status' => 'VALID',
                        'deleted_flag' => 'N'
                    ])
                    ->select();

            $ret = [];
            if ($product_attrs) {
                foreach ($product_attrs as $item) {
                    if (isset($ret[$item['spu']]['spec_attrs']) && $ret[$item['spu']]['spec_attrs'] && $item['spec_attrs']) {
                        if (json_decode($item['spec_attrs'], true)) {
                            $ret[$item['spu']]['spec_attrs'] = array_merge($ret[$item['spu']]['spec_attrs'], json_decode($item['spec_attrs'], true));
                        }
                    } elseif (isset($ret[$item['spu']]['spec_attrs']) && $ret[$item['spu']]['spec_attrs']) {

                    } elseif ($item['spec_attrs']) {
                        $ret[$item['spu']]['spec_attrs'] = json_decode($item['spec_attrs'], true);
                    } else {
                        $ret[$item['spu']]['spec_attrs'] = [];
                    }
                    if (isset($ret[$item['spu']]['ex_hs_attrs']) && $ret[$item['spu']]['ex_hs_attrs'] && $item['ex_hs_attrs']) {
                        if (json_decode($item['ex_hs_attrs'], true)) {
                            $ret[$item['spu']]['ex_hs_attrs'] = array_merge($ret[$item['spu']]['ex_hs_attrs'], json_decode($item['ex_hs_attrs'], true));
                        }
                    } elseif (isset($ret[$item['spu']]['ex_hs_attrs']) && $ret[$item['spu']]['ex_hs_attrs']) {

                    } elseif ($item['ex_hs_attrs']) {
                        $ret[$item['spu']]['ex_hs_attrs'] = json_decode($item['ex_hs_attrs'], true);
                    } else {
                        $ret[$item['spu']]['ex_hs_attrs'] = [];
                    }
                    if (isset($ret[$item['spu']]['other_attrs']) && $ret[$item['spu']]['other_attrs'] && $item['other_attrs']) {
                        if (json_decode($item['other_attrs'], true)) {
                            $ret[$item['spu']]['other_attrs'] = array_merge($ret[$item['spu']]['other_attrs'], json_decode($item['other_attrs'], true));
                        }
                    } elseif (isset($ret[$item['spu']]['other_attrs']) && $ret[$item['spu']]['other_attrs']) {

                    } elseif ($item['other_attrs']) {
                        $ret[$item['spu']]['other_attrs'] = json_decode($item['other_attrs'], true);
                    } else {
                        $ret[$item['spu']]['other_attrs'] = [];
                    }

                    if (isset($ret[$item['spu']]['ex_goods_attrs']) && $ret[$item['spu']]['ex_goods_attrs'] && $item['ex_goods_attrs']) {
                        if (json_decode($item['ex_goods_attrs'], true)) {
                            $ret[$item['spu']]['ex_goods_attrs'] = array_merge($ret[$item['spu']]['ex_goods_attrs'], json_decode($item['ex_goods_attrs'], true));
                        }
                    } elseif (isset($ret[$item['spu']]['ex_goods_attrs']) && $ret[$item['spu']]['ex_goods_attrs']) {

                    } elseif ($item['ex_goods_attrs']) {
                        $ret[$item['spu']]['ex_goods_attrs'] = json_decode($item['ex_goods_attrs'], true);
                    } else {
                        $ret[$item['spu']]['ex_goods_attrs'] = [];
                    }
                }
            }

            foreach ($ret as $spu => $attr) {

                if ($attr['spec_attrs']) {
                    $attr['spec_attrs'] = json_encode($attr['spec_attrs'], 256);
                } else {
                    $attr['spec_attrs'] = new stdClass();
                }
                if ($attr['ex_goods_attrs']) {
                    $attr['ex_goods_attrs'] = json_encode($attr['ex_goods_attrs'], 256);
                } else {
                    $attr['ex_goods_attrs'] = new stdClass();
                }
                if ($attr['ex_hs_attrs']) {
                    $attr['ex_hs_attrs'] = json_encode($attr['ex_hs_attrs'], 256);
                } else {
                    $attr['ex_hs_attrs'] = new stdClass();
                }

                if ($attr['other_attrs']) {
                    $attr['other_attrs'] = json_encode($attr['other_attrs'], 256);
                } else {
                    $attr['other_attrs'] = new stdClass();
                }
                $ret[$spu] = $attr;
            }

            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
