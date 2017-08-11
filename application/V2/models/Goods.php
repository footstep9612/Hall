<?php

/**
 * SKU
 * User: linkai
 * Date: 2017/6/15
 * Time: 21:04
 */
class GoodsModel extends PublicModel {

    protected $tableName = 'goods';
    protected $dbName = 'erui2_goods'; //数据库名称

    //状态

    const STATUS_VALID = 'VALID';          //有效
    const STATUS_TEST = 'TEST';            //测试
    const STATUS_INVALID = 'INVALID';      //无效
    const STATUS_DELETED = 'DELETED';      //删除
    const STATUS_CHECKING = 'CHECKING';    //审核中
    const STATUS_DRAFT = 'DRAFT';          //草稿

    //定义校验规则

    protected $field = array(
        'spu' => array('required'),
        'name' => array('required'),
        'show_name' => array('required'),
    );

      public function __construct() {
        //动态读取配置中的数据库配置   便于后期维护
//        $config_obj = Yaf_Registry::get("config");
//        $config_db = $config_obj->database->config->goods->toArray();
//        $this->dbName = $config_db['name'];
//        $this->tablePrefix = $config_db['tablePrefix'];
//        $this->tableName = 'goods';

        parent::__construct();
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

        $field = "$ptable.meterial_cat_no,$ptable.source,$ptable.sku_count,$ptable.supplier_name,$ptable.brand,$ptable.name as spu_name,$thistable.lang,$thistable.id,$thistable.sku,$thistable.spu,$thistable.status,$thistable.shelves_status,$thistable.name as sku_name,$thistable.show_name,$thistable.model,$thistable.created_by,$thistable.created_at,$thistable.updated_at,$thistable.updated_by";

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
//        if (isset($condition['supplier_name']) && !empty($condition['supplier_name'])) {
//          $where["$ptable.supplier_name"] = array('like', $condition['supplier_name']);
//        }
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
//          //上架状态
//          if (isset($condition['shelves_status']) && !empty($condition['shelves_status']) ) {
//              $where["$thistable.shelves_status"] = $condition['shelves_status'];
//          }
//          //上架人
//          if (isset($condition['shelves_by']) && !empty($condition['shelves_by'])) {
//              $where["$thistable.shelves_by"] = array('like', $condition['shelves_by']);
//          }
//          //上架时间
//          if (isset($condition['shelves_start']) && isset($condition['shelves_start']) && !empty($condition['shelves_end']) && !empty($condition['shelves_end'])) {
//              $where["$thistable.shelves_at"] = array('egt', $condition['shelves_start']);
//              $where["$thistable.shelves_at"] = array('elt', $condition['shelves_end']);
//          }
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
     * 生成sku编码-（BOSS后台）
     * SKU编码：8位数字组成（如12345678）
     * @author klp
     */
    public function setupSku() {
        $rand = rand(10000000, 99999999);
//        $code = str_pad($rand, 8, "0", STR_PAD_LEFT);
        $existCode = $this->where(['sku' => $rand])->find();
        if ($existCode) {
            $this->setupSku();
        }
        return $rand;
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
        return $this->field('sku,name,model,show_name')->where(['spu' => $spu, 'lang' => $lang, 'satus' => self::STATUS_VALID])->select();
    }

    /**
     * 根据skus 获取sku 信息
     * @author zyg
     */
    public function getskusbyskus($skus, $lang = 'en') {
        return $this->field('sku,name,model,show_name')->where(['sku' => ['in', $skus], 'lang' => $lang, 'satus' => self::STATUS_VALID])->select();
    }

    //--------------------------------BOSS.V2--------------------------------------------------------//
    /**
     * sku基本信息查询 == 公共
     * @author klp
     * @return array
     */
    public function getSkuInfo($condition) {
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
        //redis
        if (redisHashExist('Sku', md5(json_encode($where)))) {
//            return json_decode(redisHashGet('Sku', md5(json_encode($where))), true);
        }
        $field = 'lang, spu, sku, qrcode, name, show_name, model, description, status, created_by, created_at, updated_by, updated_at, checked_by, checked_at, source, source_detail, deleted_flag,';
        //固定商品属性
        $field .= 'exw_days, min_pack_naked_qty, nude_cargo_unit, min_pack_unit, min_order_qty, purchase_price, purchase_price_cur_bn, nude_cargo_l_mm,';
        //固定物流属性
        $field .= 'nude_cargo_w_mm, nude_cargo_h_mm, min_pack_l_mm, min_pack_w_mm, min_pack_h_mm, net_weight_kg, gross_weight_kg, compose_require_pack, pack_type,';
        //固定申报要素属性
        $field .= 'name_customs, hs_code, tx_unit, tax_rebates_pct, regulatory_conds, commodity_ori_place';
        try {
            $result = $this->field($field)->where($where)->select();
            $data = $pData = $kData = array();
            if ($result) {
                //查询对应spu-name
                $productModel = new ProductModel();
                $spuNames = $productModel->field('lang,spu,name,show_name')->where(['spu'=>$result[0]['spu']])->select();
                if($spuNames){
                    foreach($spuNames as $spuName){
                        $pData['spu_name'][$spuName['lang']] = $spuName;
                    }
                }
                //根据created_by，updated_by，checked_by获取名称   个人认为：为了名称查询多次库欠妥
                $employee = new EmployeeModel();
                foreach ($result as $item) {
                    $createder = $employee->getInfoByCondition(array('id' => $item['created_by']), 'id,name,name_en');
                    if ($createder && isset($createder[0])) {
                        $item['created_by'] = $createder[0];
                    }
                    $updateder = $employee->getInfoByCondition(array('id' => $item['updated_by']), 'id,name,name_en');
                    if ($updateder && isset($updateder[0])) {
                        $item['updated_by'] = $updateder[0];
                    }
                    $checkeder = $employee->getInfoByCondition(array('id' => $item['checked_by']), 'id,name,name_en');
                    if ($checkeder && isset($checkeder[0])) {
                        $item['checked_by'] = $checkeder[0];
                    }
                    //固定商品属性
                    $goodsAttr = ['exw_days', 'min_pack_naked_qty', 'nude_cargo_unit', 'min_pack_unit', 'min_order_qty', 'purchase_price', 'purchase_price_cur_bn', 'nude_cargo_l_mm'];
                    foreach($goodsAttr as $gAttr){
                        $item['goods_attrs'][] = ['attr_name'=>$gAttr,'attr_value'=>$item[$gAttr],'value_unit'=>'','goods_flag'=>true];
                    }
                    //固定物流属性
                    $logiAttr = ['nude_cargo_w_mm', 'nude_cargo_h_mm', 'min_pack_l_mm', 'min_pack_w_mm', 'min_pack_h_mm',' net_weight_kg', 'gross_weight_kg', 'compose_require_pack', 'pack_type'];
                    foreach($logiAttr as $lAttr){
                        $item['logi_attrs'][] = ['attr_name'=>$lAttr,'attr_value'=>$item[$lAttr],'value_unit'=>'','logi_flag'=>true];
                    }
                    //固定申报要素属性
                    $hsAttr = ['name_customs', 'hs_code', 'tx_unit', 'tax_rebates_pct', 'regulatory_conds', 'commodity_ori_place'];
                    foreach($hsAttr as $hAttr){
                        $item['hs_attrs'][] = ['attr_name'=>$hAttr,'attr_value'=>$item[$hAttr],'value_unit'=>'','hs_flag'=>true];
                    }
                    //按语言分组
                    $kData[$item['lang']] = $item;
                }
                $data = array_merge($kData,$pData);
                redisHashSet('Sku', md5(json_encode($where)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * sku新增/编辑
     * @author klp
     * @return sku
     */
    public function editSku($input) {
        if (!isset($input)) {
            return false;
        }
        //不存在生成sku
        $sku = !empty($input['sku']) ? trim($input['sku']) : $this->setupSku();
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
                        'model' => !empty($checkout['model']) ? $checkout['model'] : '',
                        'description' => !empty($checkout['description']) ? $checkout['description'] : '',
                        'source' => !empty($checkout['source']) ? $checkout['source'] : '',
                        'source_detail' => !empty($checkout['source_detail']) ? $checkout['source_detail'] : '',
                        //固定商品属性
                        'exw_days' => !empty($checkout['exw_days']) ? $checkout['exw_days'] : null,
                        'min_pack_naked_qty' => !empty($checkout['min_pack_naked_qty']) ? $checkout['min_pack_naked_qty'] : null,
                        'nude_cargo_unit' => !empty($checkout['nude_cargo_unit']) ? $checkout['nude_cargo_unit'] : null,
                        'min_pack_unit' => !empty($checkout['min_pack_unit']) ? $checkout['min_pack_unit'] : null,
                        'min_order_qty' => !empty($checkout['min_order_qty']) ? $checkout['min_order_qty'] : null,
                        'purchase_price' => !empty($checkout['purchase_price']) ? $checkout['purchase_price'] : null,
                        'purchase_price_cur_bn' => !empty($checkout['purchase_price_cur_bn']) ? $checkout['purchase_price_cur_bn'] : null,
                        'nude_cargo_l_mm' => !empty($checkout['nude_cargo_l_mm']) ? $checkout['nude_cargo_l_mm'] : null,
                        //固定物流属性
                        'nude_cargo_w_mm' => !empty($checkout['nude_cargo_w_mm']) ? $checkout['nude_cargo_w_mm'] : null,
                        'nude_cargo_h_mm' => !empty($checkout['nude_cargo_h_mm']) ? $checkout['nude_cargo_h_mm'] : null,
                        'min_pack_l_mm' => !empty($checkout['min_pack_l_mm']) ? $checkout['min_pack_l_mm'] : null,
                        'min_pack_w_mm' => !empty($checkout['min_pack_w_mm']) ? $checkout['min_pack_w_mm'] : null,
                        'min_pack_h_mm' => !empty($checkout['min_pack_h_mm']) ? $checkout['min_pack_h_mm'] : null,
                        'net_weight_kg' => !empty($checkout['net_weight_kg']) ? $checkout['net_weight_kg'] : null,
                        'gross_weight_kg' => !empty($checkout['gross_weight_kg']) ? $checkout['gross_weight_kg'] : null,
                        'compose_require_pack' => !empty($checkout['compose_require_pack']) ? $checkout['compose_require_pack'] : '',
                        'pack_type' => !empty($checkout['pack_type']) ? $checkout['pack_type'] : '',
                        //固定申报要素属性
                        'name_customs' => !empty($checkout['name_customs']) ? $checkout['name_customs'] : '',
                        'hs_code' => !empty($checkout['hs_code']) ? $checkout['hs_code'] : '',
                        'tx_unit' => !empty($checkout['tx_unit']) ? $checkout['tx_unit'] : '',
                        'tax_rebates_pct' => !empty($checkout['tax_rebates_pct']) ? $checkout['tax_rebates_pct'] : null,
                        'regulatory_conds' => !empty($checkout['regulatory_conds']) ? $checkout['regulatory_conds'] : '',
                        'commodity_ori_place' => !empty($checkout['commodity_ori_place']) ? $checkout['commodity_ori_place'] : '',
                    ];

                    //判断是新增还是编辑,如果有sku就是编辑,反之为新增
                    if (!empty($input['sku'])) {             //------编辑
                        $data['updated_by'] = $userInfo['id'];
                        $data['updated_at'] = date('Y-m-d H:i:s', time());
                        $where = [
                            'lang' => $key,
                            'sku' => trim($input['sku'])
                        ];
                        $res = $this->where($where)->save($data);
                        if (!$res) {
                            $this->rollback();
                            return false;
                        }

                        $checkout['sku'] = trim($input['sku']);
                        $checkout['lang'] = $key;
                        $checkout['updated_by'] = $userInfo['id'];

                        $gattr = new GoodsAttrModel();
                        $resAttr = $gattr->editSkuAttr($checkout);        //属性更新
                        if (!$resAttr || $resAttr['code'] != 1) {
                            $this->rollback();
                            return false;
                        }
                    } else {             //------新增
                        $data['sku'] = $sku;
                        //                    $data['qrcode'] = setupQrcode();                  //二维码字段
                        $data['created_by'] = $userInfo['id'];
                        $data['created_at'] = date('Y-m-d H:i:s', time());
                        $data['status'] = self::STATUS_DRAFT;
                        $res = $this->add($data);
                        if (!$res) {
                            $this->rollback();
                            return false;
                        }
                        $pModel = new ProductModel();                                 //sku_count加一
                        $presult = $pModel->where(['spu'=>$checkout['spu'],'lang'=>$key])
                            ->save(array('sku_count'=>array('exp', 'sku_count' . '+' . 1)));
                        if(!$presult){
                            $this->rollback();
                            return false;
                        }

                        $checkout['sku'] = $sku;
                        $checkout['lang'] = $key;
                        $checkout['created_by'] = $userInfo['id'];

                        $gattr = new GoodsAttrModel();
                        $resAttr = $gattr->editSkuAttr($checkout);        //属性新增
                        if (!$resAttr || $resAttr['code'] != 1) {
                            $this->rollback();
                            return false;
                        }
                    }
                }
            }
             if (isset($input['attachs'])) {
                if (is_array($input['attachs']) && !empty($input['attachs'])) {
                    $input['sku'] = !empty($input['sku'])?$input['sku']:$sku;
                    $input['user_id'] = $userInfo['id'];
                    $gattach = new GoodsAttachModel();
                    $resAttach = $gattach->editSkuAttach($input);  //附件新增
                    if (!$resAttach || $resAttach['code'] != 1) {
                        $this->rollback();
                        return false;
                    }
                }
            }
            if (isset($input['supplier_cost'])) {
                if (is_array($input['supplier_cost']) && !empty($input['supplier_cost'])) {
                    $input['sku'] =!empty($input['sku'])?$input['sku']:$sku;
                    $input['user_id'] = $userInfo['id'];
                    $gcostprice = new GoodsCostPriceModel();
                    $resCost = $gcostprice->editCostprice($input);  //供应商/价格策略
                    if (!$resCost || $resCost['code'] != 1) {
                        $this->rollback();
                        return false;
                    }
                }
            }
//            if ($sku) {
//                $langs = ['en', 'zh', 'es', 'ru'];
//                $es_goods_model = new EsGoodsModel();
//                foreach ($langs as $lang) {
//                    $es_goods_model->create_data($sku, $lang);
//                }
//            }
            $this->commit();
            return $sku;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * sku状态变更 -- 公共
     * @author klp
     * @params
     * @return bool
     */
    public function modifySkuStatus($input) {
        if (empty($input)) {
            return false;
        }
        $status = $this->checkStatus($input['status_type']);
        if (!$status) {
            jsonReturn('', MSG::MSG_FAILED, MSG::ERROR_PARAM);
        }
        unset($input['status_type']);
        $this->startTrans();
        try {
            $res = $this->modifySku($input['skus'], $status);               //sku状态
            if (!$res || $res['code'] != 1) {
                $this->rollback();
                return false;
            }

            $gattr = new GoodsAttrModel();
            $resAttr = $gattr->modifyAttr($input['skus'], $status);        //属性状态
            if (!$resAttr || $resAttr['code'] != 1) {
                $this->rollback();
                return false;
            }

            $gattach = new GoodsAttachModel();
            $resAttach = $gattach->modifyAttach($input['skus'], $status);  //附件状态
            if (!$resAttach || $resAttach['code'] != 1) {
                $this->rollback();
                return false;
            }
            if ('CHECKING' != $status) {
                $checkLogModel = new ProductCheckLogModel();          //审核记录
                $resLogs = $checkLogModel->takeRecord($input['skus'], $status);
                if (!$resLogs || $resLogs['code'] != 1) {
                    $this->rollback();
                    return false;
                }
            }
//            if ($sku) {
//                $langs = ['en', 'zh', 'es', 'ru'];
//                foreach ($langs as $lang) {
//                    $es_goods_model = new EsGoodsModel();
//                    $es_goods_model->create_data($sku, $lang);
//                }
//            }
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            return false;
        }
    }

    /**
     * sku[状态更改]（BOSS后台）
     * @author klp
     * @return bool
     */
    public function modifySku($data, $status) {
        if (empty($data) || empty($status)) {
            return false;
        }
        $results = array();
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $es_goods_model = new EsGoodsModel();
        $es_product_model = new EsProductModel();
        if ($data && is_array($data)) {
            try {
                foreach ($data as $item) {
                    if (self::STATUS_CHECKING == $status) {
                        $where = [
                            'sku' => $item['sku'],
                            'lang' => $item['lang']
                        ];
                        $result = $this->where($where)->save(['status' => $status]);
                        if (!$result) {
                            return false;
                        }
                    } else {
                        $where = [
                            'sku' => $item['sku'],
                            'lang' => $item['lang']
                        ];
                        $save = [
                            'status' => $status,
                            'checked_by' => $userInfo['id'],
                            'checked_at' => date('Y-m-d H:i:s', time())
                        ];
                        $result = $this->where($where)->save($save);
                        if ($result && $item['sku']) {
                            $es_goods_model->create_data($item['sku'], $item['lang']);
                        }
                        if ($result) {

                            if ('VALID' == $status) {
                                $pModel = new ProductModel();                         //spu审核通过
                                $check = $pModel->field('status')->where(['spu' => $item['spu'], 'lang' => $item['lang']])->find();
                                if($check){
                                    $resp = ('VALID' == $check['status']) ? true : $pModel->updateStatus($item['spu'], $item['lang'], $status);
                                    if (!$resp) {
                                        return false;
                                    }
                                }
                                $es_product_model->create_data($item['spu'], $item['lang']);
                            }
                        } else {
                            return false;
                        }
                    }
                }

                if ($result) {

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
     * sku真实删除-（BOSS后台）
     * @author klp
     */
    public function deleteSkuReal($input) {
        if (empty($input)) {
            return false;
        }
        if (!isset($input['sku'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }
        $lang = '';
        if(isset($input['lang']) && !in_array(strtolower($input['lang']),array('zh','en','es','ru'))) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        } else {
            $lang = !empty($input['lang']) ? strtolower($input['lang']) : '';
        }
        $this->startTrans();
        try {
            $showCatGoodsModel = new ShowCatGoodsModel();
            if(is_array($input['sku'])){
                foreach($input['sku'] as $sku){
                    $result = $showCatGoodsModel->field('sku')->where(['sku'=>$sku,'lang'=>$lang,'onshelf_flag'=>'Y'])->select();
                    if($result){
                        jsonReturn('',-101,'上架商品不能删除!');
                    }
                }
            } else {
                $result = $showCatGoodsModel->field('sku')->where(['sku'=>$input['sku'],'lang'=>$lang,'onshelf_flag'=>'Y'])->select();
                if($result){
                    jsonReturn('',-101,'上架商品不能删除!');
                }
            }

            $res = $this->deleteSku($input['sku'],$lang);                 //sku删除
            if (!$res || $res['code'] != 1) {
                $this->rollback();
                return false;
            }
            $pModel = new ProductModel();                               //sku_count减一
            $spu = $pModel->field('spu')->where(['sku'=>$input['sku'],'lang'=>$lang,'onshelf_flag'=>'Y'])->find();
            if($spu){
                $presult = $pModel->where(['spu'=>$spu['spu'],'lang'=>$lang])
                    ->save(array('sku_count'=>array('exp', 'sku_count' . '-' . 1)));
                if(!$presult){
                    $this->rollback();
                    return false;
                }
            }

            $gattr = new GoodsAttrModel();
            $resAttr = $gattr->deleteSkuAttr($input['sku'],$lang);        //属性删除
            if (!$resAttr || $resAttr['code'] != 1) {
                $this->rollback();
                return false;
            }

            $gattach = new GoodsAttachModel();
            $resAttach = $gattach->deleteSkuAttach($input['sku']);  //附件删除
            if (!$resAttach || $resAttach['code'] != 1) {
                $this->rollback();
                return false;
            }


            $this->commit();
//            if ($input['sku']) {
//                $es_goods_model = new EsGoodsModel();
//                $es_goods_model->delete_data($input['sku'], $lang);
//            }

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
    public function deleteSku($skus, $lang) {
        if (empty($skus)) {
            return false;
        }
        $results = array();
        try {
            if ($skus && is_array($skus)) {
                foreach ($skus as $del) {
                    $where = [
                        "sku" => $del,
                        "lang" => $lang
                    ];
                    $res = $this->where($where)->save(['status' => self::STATUS_DELETED, 'deleted_flag' => 'Y']);
                    if (!$res) {
                        return false;
                    }
                }
            } else {
                $where = [
                    "sku" => $skus,
                    "lang" => $lang
                ];
                $res = $this->where($where)->save(['status' => self::STATUS_DELETED, 'deleted_flag' => 'Y']);
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
//            if ($skus) {
//                $es_goods_model = new EsGoodsModel();
//                $es_goods_model->batchdelete($skus, $lang);
//            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * sku状态验证
     * @author klp
     * @return status
     */
    public function checkStatus($input) {
        if (empty($input)) {
            return false;
        }
        //新状态可以补充
        switch ($input) {
            case 'check':    //报审
                return self::STATUS_CHECKING;
                break;
            case 'valid':    //审核通过
                return self::STATUS_VALID;
                break;
            case 'invalid':    //驳回
                return self::STATUS_INVALID;
                break;
        }
    }

}
