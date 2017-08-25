<?php

/**
 * SKU
 * User: linkai
 * Date: 2017/6/15
 * Time: 21:04
 */
class GoodsModel extends PublicModel {

    protected $tableName = 'goods';
    protected $dbName = 'erui_goods'; //数据库名称

    //状态

    const STATUS_VALID = 'VALID'; //有效
    const STATUS_TEST = 'TEST'; //测试；
    const STATUS_INVALID = 'INVALID';  //无效
    const STATUS_DELETED = 'DELETED'; //DELETED-删除
    const STATUS_CHECKING = 'CHECKING'; //审核中；

    //  const STATUS_PENDING = 'PENDING'; //待提交；
    //  const STATUS_CHECKING = 'CHECKING'; //待审核；
    //  const STATUS_CHECKING = 'CHECKING'; //已通过；
    //  const STATUS_CHECKING = 'CHECKING'; //已驳回；
    //定义校验规则

    protected $field = array(
        'spu' => array('required'),
        'name' => array('required'),
        'show_name' => array('required'),
    );

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
     * 商品基本信息    -- 公共方法
     * @author link 2017-06-26
     * @param array $condition
     * @return array
     */
    public function getInfoBase($condition = []) {
        if (!isset($condition['sku'])) {
            return array();
        }
        $where = array(
            'sku' => trim($condition['sku'])
        );
        if (isset($condition['lang']) && in_array($condition['lang'], array('zh', 'en', 'es', 'ru'))) {
            $where['lang'] = strtolower($condition['lang']);
        }
        if (!empty($condition['status']) && in_array(strtoupper($condition['status']), array('VALID', 'INVALID', 'DELETED'))) {
            $where['status'] = strtoupper($condition['status']);
        }
        if (redisHashExist('Sku', md5(json_encode($where)))) {
            return json_decode(redisHashGet('Sku', md5(json_encode($where))), true);
        }
        $field = 'sku,spu,lang,name,show_name,qrcode,model,description,warranty,package_quantity,exw_day,purchase_price1,purchase_price2,purchase_price_cur,purchase_unit,pricing_flag,status,created_by,created_at,checked_by,checked_at,update_by,update_at,shelves_status';
        try {
            $result = $this->field($field)->where($where)->select();
            $data = array();
            if ($result) {
                foreach ($result as $item) {
                    //获取供应商与品牌
                    $product = new ProductModel();
                    $productInfo = $product->getInfo($item['spu'], $item['lang'], $product::STATUS_VALID);
                    $item['brand'] = $item['spec'] = $item['supplier_id'] = $item['supplier_name'] = $item['meterial_cat_no'] = '';
                    if ($productInfo) {
                        if (isset($productInfo[$item['lang']])) {
                            $productInfo = (array) $productInfo[$item['lang']];
                            $item['brand'] = $productInfo['brand'];
                            $item['supplier_id'] = $productInfo['supplier_id'];
                            $item['supplier_name'] = $productInfo['supplier_name'];
                            $item['meterial_cat_no'] = $productInfo['meterial_cat_no'];
                        }
                    }

                    //获取商品规格
                    $gattr = new GoodsAttrModel();
                    $spec = $gattr->getSpecBySku($item['sku'], $item['lang']);
                    $spec_str = '';
                    if ($spec) {
                        foreach ($spec as $r) {
                            $spec_str .= $r['attr_name'] . ':' . $r['attr_value'] . $r['value_unit'] . ';';
                        }
                    }
                    $item['spec'] = $spec_str;

                    //按语言分组
                    $data[$item['lang']] = $item;
                }
                redisHashSet('Sku', md5(json_encode($where)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * pc-sku商品详情
     * klp
     */
    public function getGoodsInfo($sku, $lang = '') {
        $lang = $lang ? strtolower($lang) : (browser_lang() ? browser_lang() : 'en');
        $field = 'id,sku,lang,spu,qrcode,name,show_name,model,description';
        $condition = array(
            'sku' => $sku,
            'status' => self::STATUS_VALID
        );

        try {
            //缓存数据redis
            $key_redis = md5(json_encode($condition));
            if (redisExist($key_redis)) {
                $result = redisGet($key_redis);
                return $result ? json_decode($result) : array();
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
                    //查询商品附件(未分语言)
                    $skuAchModel = new GoodsAchModel();
                    $attach = $skuAchModel->getInfoByAch($sku);
                    $data['attachs'] = $attach ? $attach : array();

                    redisSet($key_redis, json_encode($data));
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
    public function getInfo($sku, $lang = '') {
        $ProductModel = new ProductModel();
        $proTable = $ProductModel->getTableName();
        $thisTable = $this->getTableName();
        if ($lang != '') {
            $condition["$thisTable.lang"] = $lang;
            $condition["$proTable.lang"] = $lang;
        }
        $field = "$thisTable.id,$thisTable.lang,$thisTable.sku,$thisTable.spu,$thisTable.name,$thisTable.show_name,$thisTable.model,$proTable.brand,$proTable.meterial_cat_no,$proTable.supplier_name";
        $condition = array(
            "$thisTable.sku" => $sku,
            "$thisTable.status" => self::STATUS_VALID,
            "$proTable.status" => $ProductModel::STATUS_VALID
        );
        try {
            //缓存数据的判断读取
            $redis_key = md5(json_encode($condition));
            if (redisExist($redis_key)) {
                $result = redisGet($redis_key);
                return $result ? json_decode($result) : false;
            } else {
                $result = $this->field($field)->where($condition)->find();
                if ($result) {
                    $data = array(
                        'lang' => $lang
                    );

                    //查找所属分类
                    $cat_no = $result[0]['meterial_cat_no'];
                    $material = new MaterialcatModel();
                    $nameAll = $material->getNameByCat($cat_no);
                    //查找spu英文名称
                    $spu = $result[0]['spu'];
                    $nameEn = $ProductModel->getNameBySpu($spu, 'en');

                    //语言分组
                    foreach ($result as $k => $v) {
                        $data[$v['lang']] = $v;
                        $data[$v['lang']]['cat_name'] = $nameAll;
                        $data[$v['lang']]['en_name'] = $nameEn[0]['name'];
                    }
                    //查询属性
                    $skuAttrModel = new GoodsAttrModel();
                    $attrs = $skuAttrModel->getAttrBySku($sku, $lang);
                    $data['attrs'] = $attrs;
                    $result['attrs'] = $attrs;
                    redisSet($redis_key, $result);
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
     * @param string $spus spu编码
     * @param string $lang 语言
     * @retrun int
     */
    public function getCountBySpus($spus = '', $lang = '') {
        $condition = array(
            'status' => array('neq', self::STATUS_DELETED)
        );
        if (!empty($spus)) {
            $condition['spu'] = ['in', $spus];
        }
        if ($lang != '') {
            $condition['lang'] = $lang;
        }


        try {
            //redis 操作
            $redis_key = md5(json_encode($condition));

            if (redisExist($redis_key)) {
                return json_decode(redisGet($redis_key), true);
            } else {
                $count = $this->field('count(id) as skunum,spu')->where($condition)
                                ->group('spu')->select();
                redisSet($redis_key, json_encode($count), 1800);
                return $count ? $count : [];
            }
        } catch (Exception $e) {

            return [];
        }
    }

    /**
     * 根据spu获取sku数   (这里不包括删除的)
     * @author link
     * @param string $spu spu编码
     * @param string $lang 语言
     * @retrun int
     */
    public function getCountBySpu($spu = '', $lang = '') {
        $condition = array(
            'status' => array('neq', self::STATUS_DELETED)
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

        $field = "$thistable.pricing_flag,$ptable.meterial_cat_no,$ptable.source,$ptable.supplier_name,$ptable.brand,$ptable.name as spu_name,$thistable.lang,$thistable.id,$thistable.sku,$thistable.spu,$thistable.status,$thistable.shelves_status,$thistable.name,$thistable.show_name,$thistable.model,$thistable.created_by,$thistable.created_at,$thistable.updated_at,$thistable.updated_by,$thistable.checked_at,$thistable.checked_by";

        $where = array();
        $current_no = isset($condition['current_no']) ? $condition['current_no'] : 1;
        $pagesize = isset($condition['pagesize']) ? $condition['pagesize'] : 10;     //默认每页10条记录
        //spu 编码
        if (isset($condition['spu']) && !empty($condition['spu'])) {
            $where["$thistable.spu"] = $condition['spu'];
        }
        //spu_name
        if (isset($condition['name']) && !empty($condition['name'])) {
            $where["$ptable.name"] = array('like', $condition['name']);
        }
        //sku_name
        if (isset($condition['name']) && !empty($condition['name'])) {
            $where["$thistable.name"] = array('like', $condition['name']);
        }

        //sku编码  这里用sku编号
        if (isset($condition['sku']) && !empty($condition['sku'])) {
            $where["$thistable.sku"] = $condition['sku'];
        }

        //审核状态
        if (isset($condition['status']) && !empty($condition['status'])) {
            $where["$thistable.status"] = $condition['status'];
        }

        //语言
        $lang = '';
        if (isset($condition['lang']) && !empty($condition['status'])) {
            $where["$thistable.lang"] = $lang = strtolower($condition['lang']);
            $where["$ptable.lang"] = strtolower($condition['lang']);
        }

        //型号
        if (isset($condition['model']) && !empty($condition['model'])) {
            $where["$thistable.model"] = $condition['model'];
        }

        //来源
        if (isset($condition['source']) && !empty($condition['source'])) {
            $where["$ptable.source"] = $condition['source'];
        }

        //按供应商名称
        if (isset($condition['supplier_name']) && !empty($condition['supplier_name'])) {
            $where["$ptable.supplier_name"] = array('like', $condition['supplier_name']);
        }
        //按品牌
        if (isset($condition['brand']) && !empty($condition['brand'])) {
            $where["$ptable.brand"] = $condition['brand'];
        }

        //是否已定价
        if (isset($condition['pricing_flag']) && !empty($condition['pricing_flag'])) {
            $where["$thistable.pricing_flag"] = $condition['pricing_flag'];
        }

        //status状态 (审核,通过) $where = "status <> '" . self::STATUS_DELETED . "'";
        $where["$thistable.status"] = array('<>', self::STATUS_DELETED);
        if (isset($condition['status']) && !empty($condition['status']) && self::STATUS_DELETED != $condition['status']) {
            $where["$thistable.status"] = $condition['status'];
        }
        //上架状态
        if (isset($condition['shelves_status']) && !empty($condition['shelves_status'])) {
            $where["$thistable.shelves_status"] = $condition['shelves_status'];
        }
        //上架人
        if (isset($condition['shelves_by']) && !empty($condition['shelves_by'])) {
            $where["$thistable.shelves_by"] = array('like', $condition['shelves_by']);
        }
        //上架时间
        if (isset($condition['shelves_start']) && isset($condition['shelves_start']) && !empty($condition['shelves_end']) && !empty($condition['shelves_end'])) {
            $where["$thistable.shelves_at"] = array('egt', $condition['shelves_start']);
            $where["$thistable.shelves_at"] = array('elt', $condition['shelves_end']);
        }
        //created_by 创建人
        if (isset($condition['created_by']) && !empty($condition['created_by'])) {
            $where["$thistable.created_by"] = array('like', $condition['created_by']);
        }
        //created_at 创建时间
        if (isset($condition['created_start']) && isset($condition['created_end']) && !empty($condition['created_start']) && !empty($condition['created_end'])) {
            $where["$thistable.created_at"] = array('egt', $condition['created_start']);
            $where["$thistable.created_at"] = array('elt', $condition['created_end']);
        }
        //按分类名称
        if (isset($condition['cat_name']) && !empty($condition['cat_name'])) {
            $material = new MaterialcatModel();
            $m_cats = $material->getCatNoByName($condition['cat_name']);
            if ($m_cats) {
                $mstr = '';
                foreach ($m_cats as $item) {
                    $item = (array) $item;
                    $mstr .= ',' . $item['cat_no'];
                }
                $mstr = strlen($mstr) > 1 ? substr($mstr, 1) : '';
                $where["$ptable.meterial_cat_no"] = array('in', $mstr);
            }
        }

        try {
            //$count_up = $this->field("$thistable.id")->join($ptable . " On $thistable.spu = $ptable.spu AND $thistable.status = ''", 'LEFT')->where($where)->count();
            //$count_down = $this->field("$thistable.id")->join($ptable . " On $thistable.spu = $ptable.spu  AND $thistable.status = ''", 'LEFT')->where($where)->count();
            $result = $this->field($field)->join($ptable . " On $thistable.spu = $ptable.spu AND $thistable.lang =$ptable.lang", 'LEFT')->where($where)->order("$thistable.sku")->page($current_no, $pagesize)->select();
            $data = array(
                'lang' => $lang,
                'count' => 0,
                'current_no' => $current_no,
                'pagesize' => $pagesize,
                'data' => array(),
            );
            if ($result) {
                $res = [];
                foreach ($result as $k => $item) {
                    $res[] = $result['shelves_status'];
                }
                $count_no = array_count_values($res);
                $data['count_up'] = $count_no['VALID']; //上架状态数量
                $data['count_down'] = $count_no['INVALID']; //下架状态数量
                $data['count'] = count($result); //$count;
                $data['data'] = $result;
            }
            return $data;
        } catch (Exception $e) {
            return false;
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

    /**
     * 获取spu下的规格商品（用于门户产品详情页）
     * @param string $spu
     * @param string $lang
     * @return array
     */
    public function getSpecGoodsBySpu($spu = '', $lang = '', $spec_type = 0) {
        if (empty($spu))
            return array();

        if (redisHashExist('Sku', $spu . '_' . $lang)) {
            //return json_decode(redisHashGet('Sku', $spu . '_' . $lang), true);
        }
        try {
            $field = "sku,lang,qrcode,name,show_name,model,package_quantity,warranty,exw_day,status,purchase_price1,purchase_price2,purchase_price_cur,purchase_unit";
            $condition = array(
                "spu" => $spu,
                "lang" => $lang,
                "status" => self::STATUS_VALID
            );
            $result = $this->field($field)->where($condition)->select();
            if ($result) {
                foreach ($result as $k => $item) {
                    //获取商品规格
                    $gattr = new GoodsAttrModel();
                    $spec = $gattr->getSpecBySku($item['sku'], $item['lang']);
                    //增加最小
                    $goods = $gattr->field('attr_value')->where(array('sku' => $item['sku'], 'lang' => $item['lang'], 'attr_name' => 'Package Quantity'))->find();
                    if (isset($goods)) {
                        $result[$k]['goods'] = $goods['attr_value'];
                    }
                    if ($spec_type) {
                        $result[$k]['spec'] = $spec;
                    } else {
                        $spec_str = '';
                        if ($spec) {
                            foreach ($spec as $r) {
                                $spec_str .= $r['attr_name'] . ' : ' . $r['attr_value'] . $r['value_unit'] . ' ;';
                            }
                        }
                        $result[$k]['spec'] = $spec_str;
                    }
                }
                redisHashSet('Sku', $spu . '_' . $lang, json_encode($result));
                return $result;
            }
        } catch (Exception $e) {
            return array();
        }
        return array();
    }

    /**
     * sku新增（BOSS后台）
     * @author klp
     * @return bool
     */
    public function createSku($input) {
        if (!isset($input)) {
            return false;
        }
        //不存在生成sku
        $sku = isset($input['sku']) ? trim($input['sku']) : $this->setupSku();
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $this->startTrans();
        try {
            foreach ($input as $key => $value) {
                $arr = ['zh', 'en', 'ru', 'es'];
                if (in_array($key, $arr)) {

                    $checkout = $this->checkParam($value, $this->field);
                    $data = [
                        'lang' => $key,
                        'spu' => $checkout['spu'],
                        'name' => $checkout['name'],
                        'show_name' => $checkout['show_name'],
                        'model' => isset($checkout['model']) ? $checkout['model'] : '',
                        'description' => isset($checkout['description']) ? $checkout['description'] : '',
                        'package_quantity' => isset($checkout['package_quantity']) ? $checkout['package_quantity'] : '',
                        'exw_day' => isset($checkout['exw_day']) ? $checkout['exw_day'] : '',
                        'purchase_price1' => isset($checkout['purchase_price1']) ? $checkout['purchase_price1'] : 0,
                        'purchase_price2' => isset($checkout['purchase_price2']) ? $checkout['purchase_price2'] : 0,
                        'purchase_price_cur' => isset($checkout['purchase_price_cur']) ? $checkout['purchase_price_cur'] : 0,
                        'purchase_unit' => isset($checkout['purchase_unit']) ? $checkout['purchase_unit'] : '',
                        'pricing_flag' => isset($checkout['pricing_flag']) ? $checkout['pricing_flag'] : 'N',
                    ];

                    $data['sku'] = $sku;
                    //                    $data['qrcode'] = setupQrcode();                  //二维码字段
                    $data['created_by'] = $userInfo['name'];
                    $data['created_at'] = date('Y-m-d H:i:s', time());
                    $this->add($data);
                }
            }
            $this->commit();
            return $sku;
        } catch (\Kafka\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * sku更新（BOSS后台）
     * @author klp
     * @return bool
     */
    public function updateSku($data) {
        if (isset($data['sku'])) {
            $where['sku'] = $data['sku'];
        } else {
            JsonReturn('', '-1001', 'sku编号不能为空');
        }
        if (isset($data['lang'])) {
            $where['lang'] = $data['lang'];
        } else {
            JsonReturn('', '-1002', 'lang不能为空');
        }
        $condition = $this->check_up($data);
        if (!empty($where)) {
            return $this->where($where)->save($condition);
        } else {
            JsonReturn('', '-1001', '条件不能为空');
        }
    }

    /**
     * sku[状态更改](报审/审核)（BOSS后台）
     * @author klp
     * @return bool
     */
    public function modifySku($delData) {
        if (empty($delData)) {
            return false;
        }
        $status = $delData['status'];
        unset($delData['status']);
        $this->startTrans();
        try {
            foreach ($delData as $del) {
                if (isset($del['checked_desc'])) {
                    $where = [
                        "sku" => $del['sku'],
                        "lang" => $del['lang'],
                        "checked_by" => $delData['checked_by'],
                        "checked_at" => date('Y-m-d H:i:s', time()),
                        "checked_desc" => $del['checked_desc']
                    ];
                    $this->where($where)->save(['status' => $status]);
                } else {
                    $where = [
                        "sku" => $del['sku'],
                        "lang" => $del['lang']
                    ];
                    $this->where($where)->save(['status' => $status]);
                }
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * sku真实删除（BOSS后台）
     * @author klp
     * @return bool
     */
    public function deleteRealSku($delData) {
        if (empty($delData))
            return false;
        try {
            foreach ($delData as $del) {
                $where = [
                    "sku" => $del['sku'],
                    "lang" => $del['lang']
                ];
                $this->where($where)->save(['status' => self::STATUS_DELETED]);
            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * sku参数处理（BOSS后台）
     * @author klp
     * @return array
     */
    public function check_data($data = []) {
        if (empty($data)) {
            return false;
        }
        $condition['sku'] = isset($input['sku']) ? trim($input['sku']) : $this->setupSku();
        $condition['lang'] = isset($data['lang']) ? $data['lang'] : 'en';

        $condition['qrcode'] = isset($data['qrcode']) ? $data['qrcode'] : '';
        $condition['model'] = isset($data['model']) ? $data['model'] : '';
        $condition['description'] = isset($data['description']) ? $data['description'] : '';
        $condition['package_quantity'] = isset($data['package_quantity']) ? $data['package_quantity'] : '';
        $condition['exw_day'] = isset($data['exw_day']) ? $data['exw_day'] : '';
        $condition['purchase_price1'] = isset($data['purchase_price1']) ? $data['purchase_price1'] : 0;
        $condition['purchase_price2'] = isset($data['purchase_price2']) ? $data['purchase_price2'] : 0;
        $condition['purchase_price_cur'] = isset($data['purchase_price_cur']) ? $data['purchase_price_cur'] : 0;
        $condition['purchase_unit'] = isset($data['purchase_unit']) ? $data['purchase_unit'] : '';
        $condition['pricing_flag'] = isset($data['pricing_flag']) ? $data['pricing_flag'] : 'N';
        $condition['created_by'] = isset($data['created_by']) ? $data['created_by'] : '';
        $condition['created_at'] = isset($data['created_at']) ? $data['created_at'] : date('Y-m-d H:i:s', time());
        if (isset($data['spu'])) {
            $condition['spu'] = $data['spu'];
        } else {
            JsonReturn('', '-1001', 'spu编号不能为空');
        }
        if (isset($data['name'])) {
            $condition['name'] = $data['name'];
        } else {
            JsonReturn('', '-1003', '商品名称不能为空');
        }
        if (isset($data['show_name'])) {
            $condition['show_name'] = $data['show_name'];
        } else {
            JsonReturn('', '-1004', '商品展示名称不能为空');
        }
        if (isset($data['status'])) {
            switch (strtoupper($data['status'])) {
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
        return $condition;
    }

    /**
     * sku更新参数处理（BOSS后台）
     * @author klp
     * @return bool
     */
    public function check_up($data) {
        if (empty($data)) {
            return false;
        }
        $condition = array();
        if (isset($data['model'])) {
            $condition['model'] = $data['model'];
        }
        if (isset($data['description'])) {
            $condition['description'] = $data['description'];
        }
        if (isset($data['package_quantity'])) {
            $condition['package_quantity'] = $data['package_quantity'];
        }
        if (isset($data['exw_day'])) {
            $condition['exw_day'] = $data['exw_day'];
        }
        if (isset($data['purchase_price1'])) {
            $condition['purchase_price1'] = $data['purchase_price1'];
        }
        if (isset($data['purchase_price2'])) {
            $condition['purchase_price2'] = $data['purchase_price2'];
        }
        if (isset($data['purchase_price_cur'])) {
            $condition['purchase_price_cur'] = $data['purchase_price_cur'];
        }
        if (isset($data['purchase_unit'])) {
            $condition['purchase_unit'] = $data['purchase_unit'];
        }
        if (isset($data['pricing_flag'])) {
            $condition['pricing_flag'] = $data['pricing_flag'];
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
        }
        return $condition;
    }

    /**
     * sku新增/编辑-（BOSS后台）
     * @author klp
     */
    public function editSkuInfo($input) {
        if (!isset($input)) {
            return false;
        }
        //不存在需要生成sku
        $sku = isset($input['sku']) ? trim($input['sku']) : $this->setupSku();
        //获取当前用户信息
        $userInfo = getLoinInfo();
        //$userInfo['name'] = '李四';   //测试
        $this->startTrans();
        try {
            foreach ($input as $key => $value) {
                $arr = ['zh', 'en', 'ru', 'es'];
                if (in_array($key, $arr)) {

                    $checkout = $this->checkParam($value, $this->field);
                    $data = [
                        'lang' => $key,
                        'spu' => $checkout['spu'],
                        'name' => $checkout['name'],
                        'show_name' => $checkout['show_name'],
                        'model' => isset($checkout['model']) ? $checkout['model'] : '',
                        'description' => isset($checkout['description']) ? $checkout['description'] : '',
                        'package_quantity' => isset($checkout['package_quantity']) ? $checkout['package_quantity'] : '',
                        'exw_day' => isset($checkout['exw_day']) ? $checkout['exw_day'] : '',
                        'purchase_price1' => isset($checkout['purchase_price1']) ? $checkout['purchase_price1'] : 0,
                        'purchase_price2' => isset($checkout['purchase_price2']) ? $checkout['purchase_price2'] : 0,
                        'purchase_price_cur' => isset($checkout['purchase_price_cur']) ? $checkout['purchase_price_cur'] : 0,
                        'purchase_unit' => isset($checkout['purchase_unit']) ? $checkout['purchase_unit'] : '',
                        'pricing_flag' => isset($checkout['pricing_flag']) ? $checkout['pricing_flag'] : 'N',
                    ];

                    //判断是新增还是编辑,如果有sku就是编辑,反之为新增
                    if (isset($input['sku'])) {     //------编辑
                        $result = $this->field('sku')->where(['sku' => $input['sku'], 'lang' => $key])->find();
                        if ($result) {
                            JsonReturn('', '-1009', '[sku]已经存在');
                        }
                        $data['updated_by'] = $userInfo['name'];
                        $data['updated_at'] = date('Y-m-d H:i:s', time());
                        $where = [
                            'lang' => $key,
                            'sku' => trim($input['sku'])
                        ];
                        $this->where($where)->save($data);

                        $checkout['sku'] = trim($input['sku']);
                        $checkout['lang'] = $key;

                        $gattr = new GoodsAttrModel();
                        $resAttr = $gattr->updateAttrSku($checkout);        //属性更新
                        if (!$resAttr) {
                            return false;
                        }
                    } else {                       //------新增
                        $data['sku'] = $sku;
                        //                    $data['qrcode'] = setupQrcode();                  //二维码字段
                        $data['created_by'] = $userInfo['name'];
                        $data['created_at'] = date('Y-m-d H:i:s', time());
                        $this->add($data);

                        $checkout['sku'] = $sku;
                        $checkout['lang'] = $key;
                        $checkout['created_by'] = $userInfo['name'];

                        $gattr = new GoodsAttrModel();
                        $resAttr = $gattr->createAttrSku($checkout);        //属性新增
                        if (!$resAttr) {
                            return false;
                        }
                    }
                } elseif ('attachs' == $key) {
                    if (is_array($input['attachs']) && !empty($input['attachs'])) {
                        $input['sku'] = $sku;
                        $gattach = new GoodsAttachModel();
                        $resAttach = $gattach->createAttachSku($input);  //附件新增
                        if (!$resAttach) {
                            return false;
                        }
                    }
                }
            }
            $this->commit();
            return $sku;
        } catch (\Kafka\Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * sku状态变更-（BOSS后台）
     * @author klp
     */
    public function modify($input) {
        if (empty($input)) {
            return false;
        }
        //新状态可以补充
        switch ($input['status_type']) {
            case 'declare':    //报审
                $input['status'] = self::STATUS_CHECKING;
                break;
            case 'valid':    //审核
                $input['status'] = self::STATUS_VALID;
                break;
            case 'invalid':    //驳回
                $input['status'] = self::STATUS_INVALID;
                break;
        }
        unset($input['status_type']);
        $this->startTrans();
        try {
            $res = $this->modifySku($input);                //sku状态
            if (!$res) {
                return false;
            }
//          $pModel = new ProductModel();                  //spu状态(报审)
//          $resp = $pModel->upStatus($input['spu'],$input['lang'], $input['status']);
//          if (!$resp) {
//              return false;
//          }

            $gattr = new GoodsAttrModel();
            $resAttr = $gattr->modifySkuAttr($input);        //属性状态
            if (!$resAttr) {
                return false;
            }

            $gattach = new GoodsAttachModel();
            $resAttach = $gattach->modifySkuAttach($input);  //附件状态
            if (!$resAttach) {
                return false;
            }

            $this->commit();
            return true;
        } catch (\Kafka\Exception $e) {
            $this->rollback();
            //            $results['message'] = $e->getMessage();
            return false;
        }
    }

    /**
     * sku真实删除-（BOSS后台）
     * @author klp
     */
    public function deleteReal($input) {
        if (empty($input)) {
            return false;
        }
        $this->startTrans();
        try {
            $res = $this->deleteRealSku($input);                 //sku删除
            if (!$res) {
                return false;
            }

            $gattr = new GoodsAttrModel();
            $resAttr = $gattr->deleteRealAttr($input);        //属性删除
            if (!$resAttr) {
                return false;
            }

            $gattach = new GoodsAttachModel();
            $resAttach = $gattach->deleteRealAttach($input);  //附件删除
            if (!$resAttach) {
                return false;
            }

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            //            $results['message'] = $e->getMessage();
            return false;
        }
    }

    /**
     * 生成sku编码-（BOSS后台）
     * SKU编码：8位数字组成（如12345678）
     * @author klp
     */
    public function setupSku() {
        $rand = rand(1, 99999999);
        return str_pad($rand, 8, "0", STR_PAD_LEFT);
    }

    /**
     * 生成sku二维码-（BOSS后台） --待以后添加
     * @author klp
     */
    public function setupQrcode() {

        return $this->qrcode;
    }

    /**
     * 参数校验    注：没有参数或没有规则，默认返回true（即不做验证）
     * @param array $param  参数
     * @param array $field  校验规则
     * @return bool
     *
     * Example
     * checkParam(
     *      array('name'=>'','key'=>''),
     *      array(
     *          'name'=>array('required'),
     *          'key'=>array('method','fun')
     *      )
     * )
     */
    private function checkParam($param = [], $field = []) {
        if (empty($param) || empty($field))
            return array();
        foreach ($param as $k => $v) {
            if (isset($field[$k])) {
                $item = $field[$k];
                switch ($item[0]) {
                    case 'required':
                        if ($v == '' || empty($v)) {
                            jsonReturn('', '1000', 'Param ' . $k . ' Not null !');
                        }
                        break;
                    case 'method':
                        if (!method_exists($item[1])) {
                            jsonReturn('', '404', 'Method ' . $item[1] . ' nont find !');
                        }
                        if (!call_user_func($item[1], $v)) {
                            jsonReturn('', '1001', 'Param ' . $k . ' Validate failed !');
                        }
                        break;
                }
            }
            continue;
        }
        return $param;
    }

    /**
     * 根据spu 获取sku 信息
     * @author zyg
     */
    public function getskubyspu($spu, $lang = 'en') {
        return $this->field('sku,name,model,show_name')->where(['spu' => $spu, 'lang' => $lang, 'status' => self::STATUS_VALID])->select();
    }

    /**
     * 根据skus 获取sku 信息
     * @author zyg
     */
    public function getskusbyskus($skus, $lang = 'en') {
        return $this->field('sku,name,model,show_name')->where(['sku' => ['in', $skus], 'lang' => $lang, 'status' => self::STATUS_VALID])->select();
    }

}
