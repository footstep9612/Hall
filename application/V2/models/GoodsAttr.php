<?php

/**
 * Class GoodsAttrModel
 *  @author  klp
 */
class GoodsAttrModel extends PublicModel {

    protected $dbName = 'erui2_goods'; //数据库名称
    protected $tableName = 'goods_attr'; //数据表表名

    //状态

    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效
    const STATUS_DELETED = 'DELETED'; //删除
    const STATUS_CHECKING = 'CHECKING'; //审核
    const STATUS_DRAFT = 'DRAFT'; //草稿

    public function editAttr($data = []) {
        if (!empty($data)) {
            if (empty($data['lang']) || empty($data['sku'])) {
                return false;
            }
            $condition = array(
                'lang' => $data['lang'],
                'sku' => $data['sku'],
            );
            try {
                $result = $this->field('id')->where($condition)->find();
                if ($result) {
                    $data['updated_at'] = date('Y-m-d H:i:s', time());
                    $rel = $this->where(array('id' => $result['id']))->save($data);
                } else {
                    $data['created_at'] = date('Y-m-d H:i:s', time());
                    $rel = $this->add($data);
                }

                return $rel ? true : false;
            } catch (Exception $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * 商品属性 -- 公共
     * @author link 2017-06-26
     * @param array $condition
     * @return array
     */
    public function getAttr($condition = []) {
        if (!isset($condition['sku'])) {
            return array();
        }
        //组装条件
        $where = array(
            'sku' => trim($condition['sku']),
        );
        if (isset($condition['lang']) && in_array(strtolower($condition['lang']), array('zh', 'en', 'es', 'ru'))) {
            $where['lang'] = strtolower($condition['lang']);
        }
        if (isset($condition['status']) && in_array(strtoupper($condition['status']), array('VALID', 'INVALID', 'DELETED'))) {
            $where['status'] = strtoupper($condition['status']);
        }
        if (isset($condition['attr_type'])) {
            switch ($condition['attr_type']) {
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

        //redis获取
        if (redisHashExist('Attr', md5(json_encode($where)))) {
            return json_decode(redisHashGet('Attr', md5(json_encode($where))), true);
        }
        //查询
        try {
            $field = 'id,lang,attr_no,attr_name,attr_value_type,attr_value,value_unit,attr_group,sort_order,goods_flag,logi_flag,hs_flag,spec_flag,status';
            $result = $this->field($field)->where($where)->order('sort_order')->select();

            //根据sku获取spu
            $gmodel = new GoodsModel();
            $spu = $gmodel->getSpubySku(trim($condition['sku']));

            //获取产品属性
            $product = new ProductAttrModel();
            $pattr = $product->getAttr($spu ? $spu : '', isset($condition['lang']) ? $condition['lang'] : '', isset($condition['attr_type']) ? $condition['attr_type'] : '', isset($condition['status']) ? $condition['status'] : '');

            $data = $attrs = array();
            $attrs = array_merge($result, $pattr);
            if ($attrs) {
                foreach ($attrs as $item) {
                    $group1 = '';
                    $item['flag'] = true;
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
            redisHashSet('Attr', md5(json_encode($where)), json_encode($data));
            return $data;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * sku属性新增（门户后台）
     * @author klp
     * @return bool
     */
    public function createAttrSku($data) {
        $arr = $this->check_data($data);
        $res = $this->addAll($arr);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * sku属性更新（门户后台）
     * @author klp
     * @return bool
     */
    public function updateAttrSku($data) {
        $condition = $this->check_up($data);
        if ($condition) {
            try {
                foreach ($condition as $v) {
                    $this->where(array('sku' => $v['sku'], 'lang' => $v['lang']))->save($v);
                }
                return true;
            } catch (\Kafka\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * sku属性参数处理（门户后台）
     * @author klp
     * @return array
     */
    public function check_data($data = []) {
        if (empty($data))
            return false;
        $condition['lang'] = isset($data['lang']) ? $data['lang'] : 'en';
        $condition['required_flag'] = isset($data['required_flag']) ? $data['required_flag'] : 'N';
        $condition['search_flag'] = isset($data['search_flag']) ? $data['search_flag'] : 'Y';
        $condition['attr_group'] = isset($data['attr_group']) ? $data['attr_group'] : '';
        $condition['sort_order'] = isset($data['sort_order']) ? $data['sort_order'] : 1;
        $condition['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s');
        if (isset($data['created_by'])) {
            $condition['created_by'] = $data['created_by'];
        }
        if (isset($data['sku'])) {
            $condition['sku'] = $data['sku'];
        } else {
            JsonReturn('', '-1002', 'sku编号不能为空');
        }
        if (isset($data['attr_no'])) {
            $condition['attr_no'] = $data['attr_no'];
        } else {
            JsonReturn('', '-1003', '属性编码不能为空');
        }
        if (isset($data['status'])) {
            switch ($data['status']) {
                case self::STATUS_VALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_INVALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_CHECKING:
                    $condition['status'] = $data['status'];
                    break;
            }
        } else {
            $condition['status'] = self::STATUS_VALID;
        }
        //属性组处理
        $attrs = array();
        foreach ($data['attrs'] as $k => $v) {
            if (isset($v['goods_flag']) && 'Y' == $v['goods_flag']) {
                $condition['goods_flag'] = 'Y';
                $condition['spec_flag'] = 'N';
                $condition['logi_flag'] = 'N';
                $condition['hs_flag'] = 'N';
            } elseif (isset($v['spec_flag']) && 'Y' == $v['spec_flag']) {
                $condition['spec_flag'] = 'Y';
                $condition['goods_flag'] = 'N';
                $condition['logi_flag'] = 'N';
                $condition['hs_flag'] = 'N';
            } elseif (isset($v['logi_flag']) && 'Y' == $v['logi_flag']) {
                $condition['logi_flag'] = 'Y';
                $condition['spec_flag'] = 'N';
                $condition['goods_flag'] = 'N';
                $condition['hs_flag'] = 'N';
            } elseif (isset($v['hs_flag']) && 'Y' == $v['hs_flag']) {
                $condition['hs_flag'] = 'Y';
                $condition['spec_flag'] = 'N';
                $condition['logi_flag'] = 'N';
                $condition['goods_flag'] = 'N';
            }
            $condition['attr_name'] = $v['attr_name'];
            $condition['attr_value'] = isset($v['attr_value']) ? $v['attr_value'] : '';
            $condition['attr_value_type'] = isset($v['attr_value_type']) ? $v['attr_value_type'] : 'String';
            $condition['value_unit'] = isset($v['value_unit']) ? $v['value_unit'] : ' ';
            $attrs[] = $condition;
        }
        return $attrs;
    }

    /**
     * sku属性更新参数处理（门户后台）
     * @author klp
     * @return bool
     */
    public function check_up($data) {
        if (empty($data)) {
            return false;
        }
        $condition = [];
        if (isset($data['sku'])) {
            $condition['sku'] = $data['sku'];
        } else {
            JsonReturn('', '-1001', 'sku编号不能为空');
        }
        if (isset($data['lang'])) {
            $condition['lang'] = $data['lang'];
        } else {
            JsonReturn('', '-1002', 'lang不能为空');
        }
        if (isset($data['spu'])) {
            $condition['spu'] = $data['spu'];
        }
        if (isset($data['attr_no'])) {
            $condition['attr_no'] = $data['attr_no'];
        }
        if (isset($data['value_unit'])) {
            $condition['value_unit'] = $data['value_unit'];
        }
        if (isset($data['required_flag'])) {
            $condition['required_flag'] = $data['required_flag'];
        }
        if (isset($data['search_flag'])) {
            $condition['search_flag'] = $data['search_flag'];
        }
        if (isset($data['attr_group'])) {
            $condition['attr_group'] = $data['attr_group'];
        }
        if (isset($data['sort_order'])) {
            $condition['sort_order'] = $data['sort_order'];
        }
//        if (isset($data['updated_by'])) {$condition['updated_by'] = $data['updated_by'];}
        if (isset($data['status'])) {
            switch ($data['status']) {
                case self::STATUS_VALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_INVALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_CHECKING:
                    $condition['status'] = $data['status'];
                    break;
            }
        }
        //属性组处理
        $attrs = array();
        foreach ($data['attrs'] as $k => $v) {
            $condition['id'] = $v['id'];
            if (isset($v['attr_name'])) {
                $condition['attr_name'] = $v['attr_name'];
            }
            if (isset($v['attr_value'])) {
                $condition['attr_value'] = $v['attr_value'];
            }
            $attrs[] = $condition;
        }
        return $attrs;
    }

    //-----------------------------------BOSS.V2------------------------------------------------------//
    /**
     * sku属性查询 -- 公共
     * @author klp
     * @return array
     */
    public function getSkuAttrsInfo($condition) {
        if (!isset($condition)) {
            return false;
        }
        if (isset($condition['sku']) && !empty($condition['sku'])) {
            $where = array('sku' => trim($condition['sku']));
        } else {
            jsonReturn('', MSG::MSG_FAILED, MSG::getMessage(MSG::MSG_FAILED));
        }
        if (isset($condition['lang']) && in_array($condition['lang'], array('zh', 'en', 'es', 'ru'))) {
            $where['lang'] = strtolower($condition['lang']);
        }
        if (!empty($condition['status']) && in_array(strtoupper($condition['status']), array('VALID', 'INVALID', 'DELETED'))) {
            $where['status'] = strtoupper($condition['status']);
        } else {
            $where['status'] = array('neq', self::STATUS_DELETED);
        }
        $where['deleted_flag'] = 'N';
        //redis
        if (redisHashExist('SkuAttrs', md5(json_encode($where)))) {
           // return json_decode(redisHashGet('SkuAttrs', md5(json_encode($where))), true);
        }
        $field = 'lang, spu, sku, spec_attrs, ex_goods_attrs, ex_hs_attrs, other_attrs, status, created_by,  created_at';
        //spec_attrs--规格属性   ex_goods_attrs--其它商品属性  ex_hs_attrs--其它申报要素  other_attrs--其它属性
        try {
            $result = $this->field($field)->where($where)->select();

            $data = array();
            if ($result) {
                //获取产品属性
                $product = new ProductAttrModel();
                $pattr = $product->getAttr($result[0]['spu'] ? $result[0]['spu'] : '', isset($where['lang']) ? $where['lang'] : '', isset($where['status']) ? $where['status'] : '');
                $attrs = array_merge($result, $pattr);

                //按语言分组,类型分组
                foreach ($attrs as $item) {
                    if (isset($item['spec_attrs'])) {    //对应扩展属性
                        $item['spec_attrs'] = json_decode($item['spec_attrs'], true);
                    }
                    if (isset($item['ex_goods_attrs'])) {
                        $item['ex_goods_attrs'] = json_decode($item['ex_goods_attrs'], true);
                    }
                    if (isset($item['ex_hs_attrs'])) {
                        $item['ex_hs_attrs'] = json_decode($item['ex_hs_attrs'], true);
                    }
                    if (isset($item['other_attrs'])) {
                        $item['other_attrs'] = json_decode($item['other_attrs'], true);
                    }
                    $data[$item['lang']] = $item;
                }
                redisHashSet('SkuAttrs', md5(json_encode($where)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * sku属性新增/编辑 -- 公共
     * @author klp
     * @params 条件:sku lang
     * @return bool
     */
    public function editSkuAttr($input) {
        if (empty($input)) {
            return false;
        }
        $results = array();
        if (empty($input['sku']) || empty($input['lang'])) {
            jsonReturn('', MSG::MSG_FAILED, MSG::ERROR_PARAM);
        }
        $data = $this->checkParam($input);

        try {
            //存在sku编辑,反之新增,后续扩展性
            $result = $this->field('sku')->where(['sku' => $input['sku'], 'lang' => $input['lang']])->find();
            if ($result) {
                $where = [
                    'sku' => trim($input['sku']),
                    'lang' => $input['lang']
                ];
                $data['updated_by'] = $input['updated_by'];
                $data['updated_at'] = date('Y-m-d H:i:s', time());
                $res = $this->where($where)->save($data);
                if (!$res) {
                    return false;
                }
            } else {
                $data['status'] = self::STATUS_DRAFT;
                $data['spu'] = $input['spu'];
                $data['sku'] = $input['sku'];
                $data['lang'] = $input['lang'];
                $data['created_by'] = $input['created_by'];
                $data['created_at'] = date('Y-m-d H:i:s', time());
                $res = $this->add($data);
                if (!$res) {
                    return false;
                }
            }
            if ($res) {
                $results['code'] = '1';
                $results['message'] = '成功！';
            } else {
                $results['code'] = '-101';
                $results['message'] = '失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 参数校验    注：没有参数或没有规则，默认返回true（即不做验证）
     * @param array $param  参数
     * @param array $field  校验规则
     * @return
     *
     */
    private function checkParam($param = []) {
        $data = $results = [];
        if (!empty($param['attrs']['spec_attrs'])) {
            $data['spec_attrs'] = json_encode($param['attrs']['spec_attrs']);
        }
        if (!empty($param['attrs']['ex_goods_attrs'])) {
            $data['ex_goods_attrs'] = json_encode($param['attrs']['ex_goods_attrs']);
        }
        if (!empty($param['attrs']['ex_hs_attrs'])) {
            $data['ex_hs_attrs'] = json_encode($param['attrs']['ex_hs_attrs']);
        }
        if (!empty($param['attrs']['other_attrs'])) {
            $data['other_attrs'] = json_encode($param['attrs']['other_attrs']);
        }

        return $data;
    }

    /**
     * sku属性[状态更改]
     * @author klp
     * @return bool
     */
    public function modifyAttr($data, $status) {

        if (empty($data) || empty($status)) {
            return false;
        }
        $results = array();

        if ($data && is_array($data)) {
            try {
                foreach ($data as $sku) {
                    $where = [
                        "sku" => $sku,
                    ];
                    if (isset($item['lang']) && !empty($item['lang'])) {
                        $where["lang"] = $item['lang'];
                    }
                    $resatr = $this->field('sku')->where($where)->find();

                    if ($resatr) {
                        $res = $this->where($where)->save(['status' => $status,
                            'updated_by' => defined('UID') ? UID : 0,
                            'updated_at' => date('Y-m-d H:i:s')]);

                        if (!$res) {
                            return false;
                        }
                    }
                }
                if ($res) {
                    $results['code'] = '1';
                    $results['message'] = '成功！';
                } else {
                    $results['code'] = '-101';
                    $results['message'] = '失败!';
                }
                return $results;
            } catch (Exception $e) {
                $results['code'] = $e->getCode();
                $results['message'] = $e->getMessage();

                return $results;
            }
        }
        return false;
    }

    /**
     * sku属性删除[真实]
     * @author klp
     * @return bool
     */
    public function deleteSkuAttr($skus, $lang) {
        if (empty($skus)) {
            return false;
        }
        $results = array();
        try {
            if ($skus && is_array($skus)) {
                foreach ($skus as $del) {
                    $where = [
                        "sku" => $del,
                    ];
                    if (!empty($lang)) {
                        $where['lang'] = $lang;
                    }
                    $find = $this->where($where)->select();
                    if ($find) {
                        $res = $this->where($where)->save(['deleted_flag' => 'Y']);
                        if (!$res) {
                            return false;
                        }
                    }
                }
            } else {
                $where = [
                    "sku" => $skus,
                ];
                if (!empty($lang)) {
                    $where['lang'] = $lang;
                }
                $find = $this->where($where)->select();
                if ($find) {
                    $res = $this->where($where)->save(['deleted_flag' => 'Y']);
                    if (!$res) {
                        return false;
                    }
                }
            }

            $results['code'] = '1';
            $results['message'] = '成功！';
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /* 通过SKU获取数据商品属性列表
     * @param mix $skus // 商品SKU编码数组
     * @param string $lang // 语言
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 商品
     */

    public function getgoods_attrbyskus($skus, $lang = 'en') {

        try {
            $product_attrs = $this->field('*')
                    ->where(['sku' => ['in', $skus], 'lang' => $lang, 'status' => 'VALID'])
                    ->select();
            $ret = [];
            foreach ($product_attrs as $item) {
                $ret[$item['sku']][] = $item;
            }
            return $ret;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

}
