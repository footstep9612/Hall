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
    protected $g_table = 'erui2_goods.goods';

    //状态

    const STATUS_VALID = 'VALID';          //有效
    const STATUS_TEST = 'TEST';            //测试
    const STATUS_INVALID = 'INVALID';      //无效
    const STATUS_DELETED = 'DELETED';      //删除
    const STATUS_CHECKING = 'CHECKING';    //审核中
    const STATUS_DRAFT = 'DRAFT';          //草稿
    const DELETE_Y = 'Y';
    const DELETE_N = 'N';

    //定义校验规则

    protected $field = array(
        'spu' => array('required'),
        'supplier_cost' => array('required'),
//        'name' => array('required'),
//        'model' => array('required'),
    );
    //固定属性映射
    protected $const_attr = array(
        'exw_days' => array('zh' => '出货周期(天)', 'en' => 'EXW(day)', 'es' => 'EXW(Día )', 'ru' => 'Время доставки (дней)'),
        'min_pack_naked_qty' => array('zh' => '最小包装内裸货商品数量', 'en' => 'Minimum packing Naked quantity', 'es' => 'La cantidad mínima de embalaje desnudo', 'ru' => 'Количество голого товара минимальном упаковке'),
        'nude_cargo_unit' => array('zh' => '商品裸货单位', 'en' => 'Goods nude cargo units', 'es' => 'Las unidades de carga de mercancías Nude', 'ru' => 'Единица голого товара'),
        'min_pack_unit' => array('zh' => '最小包装单位', 'en' => 'Minimum packing unit', 'es' => 'Mínimo de unidad de embalaje', 'ru' => 'Минимальная единица упаковки'),
        'min_order_qty' => array('zh' => '最小订货数量', 'en' => 'Minimum order quantity', 'es' => 'Cantidad de orden mínima', 'ru' => 'Минимальное количество заказа'),
        'nude_cargo_l_mm' => array('zh' => '裸货尺寸长(mm)', 'en' => 'Length of nude cargo(mm)', 'es' => 'Longitud de Nude carga (mm)', 'ru' => 'Длина голого товара（mm）'),
        'nude_cargo_w_mm' => array('zh' => '裸货尺寸宽(mm)', 'en' => 'Width of nude cargo(mm)', 'es' => 'Anchura de Nude carga (mm)', 'ru' => 'Ширина  голого товара（mm）'),
        'nude_cargo_h_mm' => array('zh' => '裸货尺寸高(mm)', 'en' => 'Height of nude cargo(mm)', 'es' => 'Altura de Nude carga (mm)', 'ru' => 'Высота голого товара（mm）'),
        'min_pack_l_mm' => array('zh' => '最小包装后尺寸长(mm)', 'en' => 'Minimum packing Length size (mm)', 'es' => 'Longitud mínima de embalaje tama?o (mm)', 'ru' => 'Длина наименьшего размера упаковки（mm）'),
        'min_pack_w_mm' => array('zh' => '最小包装后尺寸宽(mm)', 'en' => 'Minimum packing Width size (mm)', 'es' => 'Ancho mínimo de embalaje tama?o (mm)', 'ru' => 'Ширина наименьшего размера упаковки（mm）'),
        'min_pack_h_mm' => array('zh' => '最小包装后尺寸高(mm)', 'en' => 'Minimum packing Height size (mm)', 'es' => 'Altura mínima de embalaje tama?o(mm)', 'ru' => 'Высота  наименьшего размера упаковки（mm）'),
        'net_weight_kg' => array('zh' => '净重(kg)', 'en' => 'Net Weight(kg)', 'es' => 'Peso neto (kg)', 'ru' => 'Вес (кг)'),
        'gross_weight_kg' => array('zh' => '毛重(kg)', 'en' => 'Gross Weight(kg)', 'es' => 'Peso bruto (kg)', 'ru' => 'Брутто (кг)'),
        'compose_require_pack' => array('zh' => '仓储运输包装及其他要求', 'en' => 'Compose Require', 'es' => ' Las plantillas componer incluyen Require', 'ru' => 'Требования написания'),
        'pack_type' => array('zh' => '包装类型', 'en' => 'Packing type', 'es' => 'Tipo de embalaje', 'ru' => 'Тип упаковки'),
        'name_customs' => array('zh' => '中文品名(报关用)', 'en' => 'Name (customs)', 'es' => 'Nombre (aduanas)', 'ru' => 'Китайское название (сделать заявку в таможню)'),
        'hs_code' => array('zh' => '海关编码', 'en' => 'HS CODE', 'es' => 'HS CODE', 'ru' => 'Таможенный кодекс'),
        'tx_unit' => array('zh' => '成交单位', 'en' => 'Transaction Unit', 'es' => 'Unidad de transacción', 'ru' => 'Единица доставки'),
        'tax_rebates_pct' => array('zh' => '退税率(%)', 'en' => 'Tax rebates(%)', 'es' => 'Rebajas fiscales', 'ru' => 'Ставка налога'),
        'regulatory_conds' => array('zh' => '监管条件', 'en' => 'Regulatory conditions', 'es' => 'Condiciones reglamentarias', 'ru' => 'Условие регулирования'),
        'commodity_ori_place' => array('zh' => '境内货源地', 'en' => 'Domestic supply of goods to', 'es' => 'La oferta nacional de bienes', 'ru' => 'Происхождения товаров в границах'),
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
        $existCode = $this->where(['sku' => $rand])->find();
        if ($existCode) {
            $this->setupSku();
        }
        return $rand;
    }

    /**
     * 生成sku编码 - NEW
     * @time 2017-09-26(经史总,平总确认新规则)
     * 规则:SPU的编码规则为：6位物料分类编码 + 00 + 4位产品编码 + 0000
      SKU的编码规则为: 产品的12位编码 + 4位商品编码
     */
    public function setRealSku($input) {

        foreach ($input as $item) {
            if (!isset($item['spu']) || empty($item['spu'])) {
                continue;
            } else {
                $spus[] = $item['spu'];
            }
        }
        if (empty($spus)) {
            jsonReturn('', ErrorMsg::FAILED, 'spu编码缺少!');
        }
        $temp_num = substr($spus[0], 0, 12);
        $data = $this->getSkus($temp_num);
        if ($data && substr($data[0]['sku'], 0, 12) == $temp_num) {
            $num = substr($data[0]['sku'], 12, 4);
            $num++;
            $num = str_pad($num, 4, "0", STR_PAD_LEFT);
        } else {
            $num = str_pad('1', 4, "0", STR_PAD_LEFT);
        }
        $real_num = $temp_num . $num;

        return $real_num;
    }

    /**
     * 获取sku 获取列表
     * @author
     */
    public function getSkus($sku_suffix, $order = " sku desc") {
        $sql = 'SELECT `sku`';
        $sql .= ' FROM ' . $this->g_table;
        if (!empty($sku_suffix)) {
            $sql .= ' WHERE sku like ' . "'$sku_suffix%'";
        }
        $sql .= ' Order By ' . $order;

        return $this->query($sql);
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
    private function checkParam($param = [], $field = [], $supplier_cost = []) {
        if (empty($param) || empty($field))
            return array();

        if (empty($supplier_cost)) {
            jsonReturn('', '1000', '请选择供应商!');
        }
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
        if (!$spu) {
            return [];
        }
        $where = ['lang' => $lang, 'status' => self::STATUS_VALID];
        if (is_array($spu) && $spu) {
            $where['spu'] = ['in', $spu];
        } else {
            $where['spu'] = $spu;
        }
        return $this->field('sku,name,model,show_name')->where($where)->select();
    }

    /**
     * 根据skus 获取sku 信息
     * @author zyg
     */
    public function getskusbyskus($skus, $lang = 'en') {
        if (!$skus && !is_array($skus)) {
            return [];
        }
        return $this->field('sku,name,model,show_name')->where(['sku' => ['in', $skus], 'lang' => $lang, 'status' => self::STATUS_VALID])->select();
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
        $where['deleted_flag'] = self::DELETE_N;
        //redis
        if (redisHashExist('Sku', md5(json_encode($where)))) {
//            return json_decode(redisHashGet('Sku', md5(json_encode($where))), true);
        }
        $field = 'lang, spu, sku, qrcode, name, show_name_loc, show_name, model, description, status, created_by, created_at, updated_by, updated_at, checked_by, checked_at, source, source_detail, deleted_flag,';
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
                $spuNames = $productModel->field('lang,spu,name,show_name')->where(['spu' => $result[0]['spu']])->select();
                if ($spuNames) {
                    foreach ($spuNames as $spuName) {
                        $pData['spu_name'][$spuName['lang']] = $spuName;
                    }
                }

                /**
                 * 获取扩展属性
                 */
                $goodsAttrModel = new GoodsAttrModel();
                $condition_attr = array(
                    "sku" => $condition['sku'],
                    "status" => $goodsAttrModel::STATUS_VALID
                );
                if (isset($condition['lang']) && in_array($condition['lang'], array('zh', 'en', 'es', 'ru'))) {
                    $condition_attr['lang'] = strtolower($condition['lang']);
                }
                $ex_attrs = $goodsAttrModel->getSkuAttrsInfo($condition_attr);

                $checklogModel = new ProductCheckLogModel();
                $this->_getUserName($result, ['created_by', 'updated_by', 'checked_by']);
                foreach ($result as $item) {
                    //固定商品属性
                    $goodsAttr = ['exw_days', 'min_pack_naked_qty', 'nude_cargo_unit', 'min_pack_unit', 'min_order_qty'];
                    $goods_attrs = [];
                    foreach ($goodsAttr as $gAttr) {
                        $goods_attrs[] = ['attr_name' => $this->const_attr[$gAttr][$item['lang']], 'attr_value' => $item[$gAttr], 'attr_key' => $gAttr, 'flag' => 'Y'];
                    }
                    //扩展商品属性
                    if (isset($ex_attrs[$item['lang']]['ex_goods_attrs']) && !empty($ex_attrs[$item['lang']]['ex_goods_attrs'])) {
                        foreach ($ex_attrs[$item['lang']]['ex_goods_attrs'] as $ex_key => $ex_value) {
                            $goods_attrs[] = ['attr_name' => $ex_key, 'attr_value' => $ex_value, 'attr_key' => '', 'flag' => 'N'];
                        }
                    }
                    $item['goods_attrs'] = $goods_attrs;
                    //固定物流属性
                    $logiAttr = ['nude_cargo_w_mm', 'nude_cargo_h_mm', 'nude_cargo_l_mm', 'min_pack_l_mm', 'min_pack_w_mm', 'min_pack_h_mm', 'net_weight_kg', 'gross_weight_kg', 'compose_require_pack', 'pack_type'];
                    foreach ($logiAttr as $lAttr) {
                        $item['logi_attrs'][] = ['attr_name' => $this->const_attr[$lAttr][$item['lang']], 'attr_value' => $item[$lAttr], 'attr_key' => $lAttr, 'flag' => 'Y'];
                    }

                    //固定申报要素属性
                    $hsAttr = ['name_customs', 'hs_code', 'tx_unit', 'tax_rebates_pct', 'regulatory_conds', 'commodity_ori_place'];
                    $hs_attrs = [];
                    foreach ($hsAttr as $hAttr) {
                        $hs_attrs[] = ['attr_name' => $this->const_attr[$hAttr][$item['lang']], 'attr_value' => $item[$hAttr], 'attr_key' => $hAttr, 'flag' => 'Y'];
                    }
                    //扩展申报要素属性
                    if (isset($ex_attrs[$item['lang']]['ex_hs_attrs']) && !empty($ex_attrs[$item['lang']]['ex_hs_attrs'])) {
                        foreach ($ex_attrs[$item['lang']]['ex_hs_attrs'] as $ex_key => $ex_value) {
                            $hs_attrs[] = ['attr_name' => $ex_key, 'attr_value' => $ex_value, 'attr_key' => '', 'flag' => 'N'];
                        }
                    }
                    $item['hs_attrs'] = $hs_attrs;

                    //规格属性（即前台展示的扩展属性，用作页面显示， 上面的商品扩展与申报要素扩展不做展示用）
                    $item['spec_attrs'] = [];
                    if (isset($ex_attrs[$item['lang']]['spec_attrs']) && !empty($ex_attrs[$item['lang']]['spec_attrs'])) {
                        foreach ($ex_attrs[$item['lang']]['spec_attrs'] as $ex_key => $ex_value) {
                            $item['spec_attrs'][] = ['attr_name' => $ex_key, 'attr_value' => $ex_value, 'attr_key' => '', 'flag' => 'N'];
                        }
                    }
                    //其他属性
                    $item['other_attrs'] = [];
                    if (isset($ex_attrs[$item['lang']]['other_attrs']) && !empty($ex_attrs[$item['lang']]['other_attrs'])) {
                        foreach ($ex_attrs[$item['lang']]['other_attrs'] as $ex_key => $ex_value) {
                            $item['other_attrs'][] = ['attr_name' => $ex_key, 'attr_value' => $ex_value, 'attr_key' => '', 'flag' => 'N'];
                        }
                    }
                    $item['remark'] = '';
                    $remark_checked = $checklogModel->getSkuRecord(['sku' => $item['sku'], 'lang' => $item['lang']]);
                    if ($remark_checked) {
                        $item['remark'] = $remark_checked['remarks'];
                    }
                    //按语言分组
                    $kData[$item['lang']] = $item;
                }
                $data = array_merge($kData, $pData);
//                redisHashSet('Sku', md5(json_encode($where)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 用户IDS获取names
     * @author klp
     * @return names
     */
    private function _getUserName(&$result, $fileds) {
        if ($result) {
            $employee_model = new EmployeeModel();
            $userids = [];
            foreach ($result as $key => $val) {
                foreach ($fileds as $filed) {
                    if (isset($val[$filed]) && $val[$filed]) {
                        $userids[] = $val[$filed];
                    }
                }
            }
            $usernames = $employee_model->getUserNamesByUserids($userids);
            foreach ($result as $key => $val) {
                foreach ($fileds as $filed) {
                    if ($val[$filed] && isset($usernames[$val[$filed]])) {
                        $val[$filed . '_name'] = $usernames[$val[$filed]];
                    } else {
                        $val[$filed . '_name'] = '';
                    }
                }
                $result[$key] = $val;
            }
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
        if (!isset($input['sku']) || empty($input['sku'])) {
            $sku = $this->setRealSku($input);
        } else {
            $sku = trim($input['sku']);
        }
        $spu = '';
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $this->startTrans();
        try {

            foreach ($input as $key => $value) {
                $arr = ['zh', 'en', 'ru', 'es'];
                if (in_array($key, $arr)) {


                    if (empty($value['name'])) {
                        $spuModel = new ProductModel();
                        $spuName = $spuModel->field('name')->where(['spu' => $value['spu'], 'lang' => $key,
                                    'deleted_flag' => 'N'])->find();


                        $value['name'] = $spuName['name'];
                    }
                    if (empty($value) || empty($value['name'])) {    //这里主要以名称为主判断
                        continue;
                    }
                    if (empty($value['show_name'])) {
                        $value['show_name'] = $value['name'];
                    }
//                    if (empty($input[$key]['name']) && empty($input[$key]['model']) && empty($input[$key]['attrs']['spec_attrs'])) {
//                        jsonReturn('', ErrorMsg::EXIST, '名称、型号、扩展属性不能同时为空!');
//                    }
                    //字段校验
                    $checkout = $this->checkParam($value, $this->field, $input['supplier_cost']);

                    //状态校验 增加中文验证  --前端vue无法处理改为后端处理验证
                    $status = $this->checkSkuStatus($input['status']);
                    $input['status'] = $status;
                    $spu = $checkout['spu'];
                    $attr = $this->attrGetInit($checkout['attrs']);    //格式化属性
                    //除暂存外都进行校验     这里存在暂存重复加的问题，此问题暂时预留。
                    //校验sku名称/型号/扩展属性
                    if ($input['status'] != 'DRAFT') {
                        $exist_condition = array(//添加时判断同一语言，name,meterial_cat_no,model是否存在
                            'lang' => $key,
                            'spu' => $checkout['spu'],
                            'name' => $value['name'],
                            'model' => $checkout['model'],
                            'deleted_flag' => 'N',
                            'status' => array('neq', 'DRAFT')
                        );
                        if (!empty($input['sku'])) {
                            $exist_condition['sku'] = array('neq', $input['sku']);
                        }
                        $this->_checkExit($exist_condition, $attr);
                    }

                    $data = [
                        'lang' => $key,
                        'spu' => $checkout['spu'],
                        'name' => $checkout['name'],
                        'show_name' => isset($checkout['show_name']) ? $checkout['show_name'] : '',
                        'model' => !empty($checkout['model']) ? $checkout['model'] : '',
                        'description' => !empty($checkout['description']) ? $checkout['description'] : '',
                        'source' => !empty($checkout['source']) ? $checkout['source'] : '',
                        'source_detail' => !empty($checkout['source_detail']) ? $checkout['source_detail'] : '',
                        //固定商品  属性
                        'exw_days' => isset($attr['const_attr']['exw_days']) ? $attr['const_attr']['exw_days'] : null,
                        'min_pack_naked_qty' => (isset($attr['const_attr']['min_pack_naked_qty']) && !empty($attr['const_attr']['min_pack_naked_qty'])) ? $attr['const_attr']['min_pack_naked_qty'] : null,
                        'nude_cargo_unit' => isset($attr['const_attr']['nude_cargo_unit']) ? $attr['const_attr']['nude_cargo_unit'] : null,
                        'min_pack_unit' => isset($attr['const_attr']['min_pack_unit']) ? $attr['const_attr']['min_pack_unit'] : null,
                        'min_order_qty' => (isset($attr['const_attr']['min_order_qty']) && !empty($attr['const_attr']['min_order_qty'])) ? $attr['const_attr']['min_order_qty'] : null,
                        'purchase_price' => (isset($attr['const_attr']['purchase_price']) && !empty($attr['const_attr']['purchase_price'])) ? $attr['const_attr']['purchase_price'] : null,
                        'purchase_price_cur_bn' => isset($attr['const_attr']['purchase_price_cur_bn']) ? $attr['const_attr']['purchase_price_cur_bn'] : null,
                        'nude_cargo_l_mm' => (isset($attr['const_attr']['nude_cargo_l_mm']) && !empty($attr['const_attr']['nude_cargo_l_mm'])) ? $attr['const_attr']['nude_cargo_l_mm'] : null,
                        //固定物流属性
                        'nude_cargo_w_mm' => (isset($attr['const_attr']['nude_cargo_w_mm']) && !empty($attr['const_attr']['nude_cargo_w_mm'])) ? $attr['const_attr']['nude_cargo_w_mm'] : null,
                        'nude_cargo_h_mm' => (isset($attr['const_attr']['nude_cargo_h_mm']) && !empty($attr['const_attr']['nude_cargo_h_mm'])) ? $attr['const_attr']['nude_cargo_h_mm'] : null,
                        'min_pack_l_mm' => (isset($attr['const_attr']['min_pack_l_mm']) && !empty($attr['const_attr']['min_pack_l_mm'])) ? $attr['const_attr']['min_pack_l_mm'] : null,
                        'min_pack_w_mm' => (isset($attr['const_attr']['min_pack_w_mm']) && !empty($attr['const_attr']['min_pack_w_mm'])) ? $attr['const_attr']['min_pack_w_mm'] : null,
                        'min_pack_h_mm' => (isset($attr['const_attr']['min_pack_h_mm']) && !empty($attr['const_attr']['min_pack_h_mm'])) ? $attr['const_attr']['min_pack_h_mm'] : null,
                        'net_weight_kg' => (isset($attr['const_attr']['net_weight_kg']) && !empty($attr['const_attr']['net_weight_kg'])) ? $attr['const_attr']['net_weight_kg'] : null,
                        'gross_weight_kg' => (isset($attr['const_attr']['gross_weight_kg']) && !empty($attr['const_attr']['gross_weight_kg'])) ? $attr['const_attr']['gross_weight_kg'] : null,
                        'compose_require_pack' => isset($attr['const_attr']['compose_require_pack']) ? $attr['const_attr']['compose_require_pack'] : '',
                        'pack_type' => isset($attr['const_attr']['pack_type']) ? $attr['const_attr']['pack_type'] : '',
                        //固定申报要素属性
                        'name_customs' => isset($attr['const_attr']['name_customs']) ? $attr['const_attr']['name_customs'] : '',
                        'hs_code' => isset($attr['const_attr']['hs_code']) ? $attr['const_attr']['hs_code'] : '',
                        'tx_unit' => isset($attr['const_attr']['tx_unit']) ? $attr['const_attr']['tx_unit'] : '',
                        'tax_rebates_pct' => (isset($attr['const_attr']['tax_rebates_pct']) && !empty($attr['const_attr']['tax_rebates_pct'])) ? $attr['const_attr']['tax_rebates_pct'] : null,
                        'regulatory_conds' => isset($attr['const_attr']['regulatory_conds']) ? $attr['const_attr']['regulatory_conds'] : '',
                        'commodity_ori_place' => isset($attr['const_attr']['commodity_ori_place']) ? $attr['const_attr']['commodity_ori_place'] : '',
                    ];

                    //判断是新增还是编辑,如果有sku就是编辑,反之为新增
                    if (isset($input['sku']) && !empty($input['sku'])) {             //------编辑
                        $where = [
                            'lang' => $key,
                            'sku' => trim($input['sku'])
                        ];
                        /**
                         * 修改时根据sku语言查询下，不存在则添加。
                         */
                        $exist = $this->field('id')->where($where)->find();
                        if ($exist) {
                            $data['updated_by'] = $userInfo['id'];
                            $data['updated_at'] = date('Y-m-d H:i:s', time());
                            $data['status'] = isset($input['status']) ? strtoupper($input['status']) : self::STATUS_DRAFT;
                            $res = $this->where($where)->save($data);
                        } else {
                            $data['sku'] = trim($input['sku']);
                            $data['created_by'] = $userInfo['id'];
                            $data['created_at'] = date('Y-m-d H:i:s', time());
                            $data['status'] = isset($input['status']) ? strtoupper($input['status']) : self::STATUS_DRAFT;
                            if ($key == 'zh') {
                                $data['show_name_loc'] = $input['en']['name'];
                            } else {
                                $data['show_name_loc'] = $input['zh']['name'];
                            }
                            $res = $this->add($data);
                            if ($res) {
                                $pModel = new ProductModel();                                 //sku_count加一
                                $presult = $pModel->where(['spu' => $checkout['spu'], 'lang' => $key])
                                        ->save(array('sku_count' => array('exp', 'sku_count' . '+' . 1)));
                                if (!$presult) {
                                    $this->rollback();
                                    return false;
                                }
                            }
                        }
                        if (!$res) {
                            $this->rollback();
                            return false;
                        }
                    } else {             //------新增
                        $data['sku'] = $sku;
                        //               $data['qrcode'] = setupQrcode();                  //二维码字段
                        $data['created_by'] = $userInfo['id'];
                        $data['created_at'] = date('Y-m-d H:i:s', time());
                        $data['status'] = isset($input['status']) ? strtoupper($input['status']) : self::STATUS_DRAFT;
                        if ($key == 'zh') {
                            $data['show_name_loc'] = $input['en']['name'];
                        } else {
                            $data['show_name_loc'] = $input['zh']['name'];
                        }
                        $res = $this->add($data);
                        if (!$res) {
                            $this->rollback();

                            return false;
                        }
                        $pModel = new ProductModel();                                 //sku_count加一
                        $presult = $pModel->where(['spu' => $checkout['spu'], 'lang' => $key])
                                ->save(array('sku_count' => array('exp', 'sku_count' . '+' . 1)));
                        if ($presult === false) {

                            $this->rollback();
                            return false;
                        }
                    }

                    /**
                     * 扩展属性
                     */
                    $gattr = new GoodsAttrModel();
                    $attr_obj = array(
                        'lang' => $key,
                        'spu' => isset($checkout['spu']) ? $checkout['spu'] : null,
                        'spec_attrs' => !empty($attr['spec_attrs']) ? json_encode($attr['spec_attrs'], JSON_UNESCAPED_UNICODE) : null,
                        'other_attrs' => !empty($attr['other_attrs']) ? json_encode($attr['other_attrs'], JSON_UNESCAPED_UNICODE) : null,
                        'ex_goods_attrs' => !empty($attr['ex_goods_attrs']) ? json_encode($attr['ex_goods_attrs'], JSON_UNESCAPED_UNICODE) : null,
                        'ex_hs_attrs' => !empty($attr['ex_hs_attrs']) ? json_encode($attr['ex_hs_attrs'], JSON_UNESCAPED_UNICODE) : null,
                        'status' => $gattr::STATUS_VALID
                    );
                    if (isset($input['sku']) && !empty($input['sku'])) {
                        $attr_obj['sku'] = trim($input['sku']);
                        $attr_obj['updated_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                    } else {
                        $attr_obj['sku'] = $sku;
                        $attr_obj['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                    }
                    $resAttr = $gattr->editAttr($attr_obj);        //属性新增
                    if (!$resAttr || $resAttr === false) {
                        $this->rollback();
                        return false;
                    }
                } elseif ($key == 'attachs') {
                    if (is_array($value) && !empty($value)) {
                        $input['sku'] = (isset($input['sku']) && !empty($input['sku'])) ? $input['sku'] : $sku;
                        $input['user_id'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                        $gattach = new GoodsAttachModel();
                        $resAttach = $gattach->editSkuAttach($value, $input['sku'], $input['user_id']);  //附件新增
                        if (!$resAttach || $resAttach['code'] != 1) {
                            $this->rollback();
                            return false;
                        }
                    }
                } elseif ($key == 'supplier_cost') {
                    if (is_array($value) && !empty($value)) {
                        $input['sku'] = (isset($input['sku']) && !empty($input['sku'])) ? $input['sku'] : $sku;
                        $input['user_id'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                        $gcostprice = new GoodsCostPriceModel();
                        $resCost = $gcostprice->editCostprice($value, $input['sku'], $input['user_id']);  //供应商/价格策略

                        if (!$resCost || $resCost['code'] != 1) {
                            $this->rollback();
                            return false;
                        }
                        $supplier_model = new GoodsSupplierModel();
                        $res = $supplier_model->editSupplier($value, $input['sku'], $input['user_id'], $spu); //供应商

                        if (!$res || $res['code'] != 1) {
                            $this->rollback();
                            return false;
                        }
                    }
                } else {
                    continue;
                }
            }
            $this->commit();
            return $sku;
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . $ex->getMessage(), Log::ERR);
            $this->rollback();
            return false;
        }
    }

    /**
     * sku-status状态校验
     * @author klp
     */
    private function checkSkuStatus($status) {
        if (empty($status)) {
            return self::STATUS_DRAFT;
        }
        switch ($status) {
            case '通过':
                $statusOut = self::STATUS_VALID;
                break;
            case '待审核':
                $statusOut = self::STATUS_CHECKING;
                break;
        }
        if ($statusOut) {
            return $statusOut;
        } else {
            return $statusOut = (isset($status) && in_array(strtoupper($status), array('DRAFT', 'TEST', 'VALID', 'CHECKING'))) ? strtoupper($status) : self::STATUS_DRAFT;
        }
    }

    /**
     * 校验sku名称,model,属性
     * @author klp
     * @return
     */
    private function _checkExit(&$condition, &$attr, $boolen = false) {

        $exist = $this->where($condition)->find();
        if ($exist) {
            $where = array(
                'lang'         => $condition['lang'],
                'spu'          => $condition['spu'],
                'deleted_flag' => 'N'
            );
            if (!empty($condition['sku'])) {
                $where['sku'] = $condition['sku'];
            }
            $attr_model = new GoodsAttrModel();
            $other_attr = $attr_model->where($where)->select();

            if (empty($attr['spec_attrs']) && !$other_attr['spec_attrs']) {
                if ($boolen) {
                    return false;
                } else {
                    jsonReturn('', ErrorMsg::EXIST, '名称：' . $condition['name'] . '型号：' . $condition['model'] . '已存在');
                }
            } else {
                foreach ($other_attr as $key => $item) {
                    $other = json_decode($item['spec_attrs'], true);
                    $otherAttr = $other ? $other : [];
                    $result1 = array_diff_assoc($otherAttr, $attr['spec_attrs']);
                    $result2 = array_diff_assoc($attr['spec_attrs'], $otherAttr);

                    if (empty($result1) && empty($result2)) {
                        if ($boolen) {
                            return false;
                        } else {
                            jsonReturn('', ErrorMsg::EXIST, '名称：' . $condition['name'] . '型号：' . $condition['model'] . '已存在' . '; 扩展属性重复!');
                        }
                    } else {
                        continue;
                    }
                }
            }
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
        $lang = '';
        if (isset($input['lang']) && !in_array(strtolower($input['lang']), array('zh', 'en', 'es', 'ru'))) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        } else {
            $lang = isset($input['lang']) ? strtolower($input['lang']) : '';
        }

        $remark = isset($input['remark']) ? htmlspecialchars($input['remark']) : '';
        unset($input['status_type']);
        $this->startTrans();
        try {
            $res = $this->modifySku($input['sku'], $lang, $status, $remark);               //sku状态
            if (!$res || $res['code'] != 1) {
                $this->rollback();
                return false;
            }

            $gattach = new GoodsAttachModel();
            $resAttach = $gattach->modifyAttach($input['sku'], $status);  //附件状态

            if (!$resAttach || $resAttach['code'] != 1) {
                $this->rollback();

                return false;
            }
            /*  if ('CHECKING' != $status) {
              $checkLogModel = new ProductCheckLogModel();          //审核记录
              $resLogs = $checkLogModel->takeRecord($input['sku'], $status);
              if (!$resLogs || $resLogs['code'] != 1) {
              $this->rollback();

              return false;
              }
              } */


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
    public function modifySku($skuObj, $lang = '', $status, $remark = '') {
        if (empty($skuObj) || empty($status)) {
            return false;
        }
        $results = array();
        //获取当前用户信息
        $userInfo = getLoinInfo();
        if ($skuObj && is_array($skuObj)) {
            try {
                $skuary = [];
                foreach ($skuObj as $sku) {
                    if (self::STATUS_CHECKING == $status) {
                        $this->checkModify($sku, $lang);

                        $where = [
                            'lang' => $lang,
                            'sku' =>  $sku,
                            'deleted_flag' => 'N'
                        ];
                        $result = $this->where($where)
                                ->save(['status' => $status, 'updated_by' => defined('UID') ? UID : 0, 'updated_at' => date('Y-m-d H:i:s')]);
                        if (!$result) {
                            return false;
                        }
                    } else {
                        $where = [
                            'sku' => $sku,
                        ];
                        if (!empty($lang)) {
                            $where['lang'] = $lang;
                        }
                        $save = [
                            'status' => $status,
                            'checked_by' => $userInfo['id'],
                            'checked_at' => date('Y-m-d H:i:s', time())
                        ];
                        $result = $this->where($where)->save($save);
                        if ($result && $sku) {
                            $skuary[] = array('sku' => $sku, 'lang' => $lang, 'remarks' => $remark);
                            if ('VALID' == $status) {

                                $this->checkModify($sku, $lang);

                                $pModel = new ProductModel();                         //spu审核通过
                                $spuCode = $this->field('spu')->where($where)->find();
                                if ($spuCode) {
                                    $spuWhere = array(
                                        'spu' => $spuCode['spu'],
                                    );
                                    if (!empty($lang)) {
                                        $spuCode['lang'] = $lang;
                                    }
                                    $result_spu = $pModel->where($spuWhere)->save(array('status' => $pModel::STATUS_VALID, 'checked_by' => $userInfo['id'], 'checked_at' => date('Y-m-d H:i:s', time())));
                                    if ($result_spu) {
                                        $skuary[] = array('spu' => $spuCode['spu'], 'lang' => $lang, 'remarks' => $remark);

                                        //同步product es
                                        $es_product_model = new EsProductModel();
                                        $langs = ['en', 'zh', 'es', 'ru'];
                                        if (empty($lang)) {
                                            foreach ($langs as $l) {
                                                $es_product_model->create_data($spuCode['spu'], $l);
                                            }
                                        } else {
                                            $es_product_model->create_data($spuCode['spu'], $lang);
                                        }
                                    }
                                }
                            }
                        } else {
                            return false;
                        }
                    }
                }
                if ($result) {
                    if (!empty($skuary)) {
                        $checkLogModel = new ProductCheckLogModel();          //审核记录
                        $resLogs = $checkLogModel->takeRecord($skuary, $status);
                        if (!$resLogs || $resLogs['code'] != 1) {
                            return false;
                        }
                    }

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

    //批量报审校验
    public function checkModify($sku,$lang) {
        $exit_where = [
            'lang'         => $lang,
            'sku'          => $sku,
            'deleted_flag' => self::DELETE_N
        ];
        $thisSkuInfo = $this->field('lang,spu,name,model')->where($exit_where)->find();
        $thisSkuInfo ? $thisSkuInfo : jsonReturn('',-1001,'['. $sku .']不存在或已经删除!');

        $thisSpecAttr = $this->where($exit_where)->find();
        $thisSpecAttr ? $thisSpecAttr : jsonReturn('',-1001,'['. $sku .']属性不存在或已经删除!');

        $where = [
            'spu'          => $thisSkuInfo['spu'],
            'name'         => $thisSkuInfo['name'],
            'model'        => $thisSkuInfo['model'],
            'lang'         => $lang,
            'sku'          => array('neq', $sku),
            'deleted_flag' => self::DELETE_N,
            'status'       => array('neq', self::STATUS_DRAFT)
        ];

        $this->_checkExit($where, $thisSpecAttr);
    }

    /**
     * sku真实删除-（BOSS后台）
     * @author klp
     */
    public function deleteSkuReal($input) {
        if (empty($input)) {
            return false;
        }
        if (!(isset($input['sku']))) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }
        $lang = '';
        if (isset($input['lang']) && !in_array(strtolower($input['lang']), array('zh', 'en', 'es', 'ru'))) {
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        } else {
            $lang = !empty($input['lang']) ? strtolower($input['lang']) : '';
        }
        $this->startTrans();
        try {
            $showCatGoodsModel = new ShowCatGoodsModel();
            if (is_array($input['sku'])) {
                foreach ($input['sku'] as $sku) {
                    $where = ['sku' => $sku, 'onshelf_flag' => 'Y'];
                    if (!empty($lang)) {
                        $where['lang'] = $lang;
                    }
                    $result = $showCatGoodsModel->field('sku')->where($where)->select();
                    if ($result) {
                        jsonReturn('', -101, '上架商品不能删除!');
                    }
                }
            } else {
                $where = ['sku' => $input['sku'], 'onshelf_flag' => 'Y'];
                if (!empty($lang)) {
                    $where['lang'] = $lang;
                }
                $result = $showCatGoodsModel->field('sku')->where($where)->select();
                if ($result) {
                    jsonReturn('', -101, '上架商品不能删除!');
                }
            }
            $res = $this->deleteSku($input['sku'], $lang);                 //sku删除
            if (!$res || $res['code'] != 1) {
                $this->rollback();
                return false;
            }
            /**
             * 放到删除方法里面
              $pModel = new ProductModel();                               //sku_count减一
              $where_spu = ['sku' => $input['sku'], 'onshelf_flag' => 'Y'];
              if(!empty($lang)) {
              $where_spu['lang'] = $lang;
              }
              $spu = $pModel->field('spu')->where($where_spu)->find();
              if ($spu) {
              $presult = $pModel->where(['spu' => $spu['spu'], 'lang' => $lang])
              ->save(array('sku_count' => array('exp', 'sku_count' . '-' . 1)));
              if (!$presult) {
              $this->rollback();
              return false;
              }
              } */
            $gattr = new GoodsAttrModel();
            $resAttr = $gattr->deleteSkuAttr($input['sku'], $lang);        //属性删除
            if (!$resAttr || $resAttr['code'] != 1) {
                $this->rollback();
                return false;
            }

            /**
             * 这里为什么要删除呢？附件不分语言，如果你删除了一种语言的sku其他语言的不用附件了吗？
              $gattach = new GoodsAttachModel();
              $resAttach = $gattach->deleteSkuAttach($input['sku']);  //附件删除
              if (!$resAttach || $resAttach['code'] != 1) {
              $this->rollback();
              return false;
              } */
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
    public function deleteSku($skus, $lang = '') {
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
                        $where["lang"] = $lang;
                    }
                    $skuInfo = $this->field('spu,deleted_flag')->where($where)->find();
                    if ($skuInfo && $skuInfo['deleted_flag'] != 'Y') {
                        $res = $this->where($where)->save(['deleted_flag' => 'Y']);
                        if ($res) {
                            $pModel = new ProductModel();                               //sku_count减一
                            $where_spu = array(
                                'spu' => $skuInfo['spu'],
                            );
                            if (!empty($lang)) {
                                $where_spu["lang"] = $lang;
                            }
                            $presult = $pModel->where($where_spu)
                                    ->save(array('sku_count' => array('exp', 'sku_count' . '-' . 1)));

                            /* if (!$presult) {
                              return false;
                              } */
                        } else {
                            return false;
                        }
                    }
                }
            } else {
                $where = [
                    "sku" => $skus,
                ];
                if (!empty($lang)) {
                    $where["lang"] = $lang;
                }
                $skuInfo = $this->field('spu,deleted_flag')->where($where)->find();
                if ($skuInfo && $skuInfo['deleted_flag'] != 'Y') {
                    $res = $this->where($where)->save(['deleted_flag' => 'Y']);
                    if ($res) {
                        $pModel = new ProductModel();                               //sku_count减一
                        $where_spu = array(
                            'spu' => $skuInfo['spu'],
                        );
                        if (!empty($lang)) {
                            $where_spu["lang"] = $lang;
                        }
                        $presult = $pModel->where($where_spu)
                                ->save(array('sku_count' => array('exp', 'sku_count' . '-' . 1)));
                        if (!$presult) {
                            return false;
                        }
                    } else {
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

    /**
     * 属性输入格式化
     * 属性包括商品固定属性 物流固定属性 申报要素固定属性 ，都在 goods 表的字段中，不能新增或减少
     * 商品扩展属性 对应 goods_attr 中的 ex_goods_attrs
     * 申报要素扩展属性，对应 goods_attr 中的 ex_hs_attrs
     * @author link 2017-08-15
     * @param array $attrs
     * @return array
     */
    function attrGetInit($attrs = []) {
        $data = array(
            'const_attr' => array(),
            'ex_goods_attrs' => array(),
            'ex_hs_attrs' => array(),
            'spec_attrs' => array(),
            'other_attrs' => array()
        );
        if (empty($attrs)) {
            return $data;
        }

        foreach ($attrs as $key => $value) {
            if (!in_array($key, array('goods_attrs', 'hs_attrs', 'logi_attrs', 'spec_attrs', 'other_attrs'))) {
                continue;
            }
            if (!empty($value)) {
                foreach ($value as $attr) {
                    if (empty(trim($attr['attr_name']))) {
                        continue;
                    }
                    if (isset($attr['flag']) && $attr['flag'] == 'Y' && isset($attr['attr_key']) && !empty($attr['attr_key'])) {    //固定属性
                        $data['const_attr'][$attr['attr_key']] = trim($attr['attr_value']);
                    } else {
                        if (in_array($key, array('goods_attrs', 'hs_attrs'))) {
                            $data['ex_' . $key][$attr['attr_name']] = trim($attr['attr_value']);
                        } else {
                            $data[$key][$attr['attr_name']] = trim($attr['attr_value']);
                        }
                    }
                }
            } else {
                continue;
            }
        }
        return $data;
    }

    /*
     * 根据skus 获取SKU名称
     */

    public function getNamesBySkus($skus, $lang = 'zh') {
        $where = [];
        if (is_array($skus) && $skus) {
            $where['sku'] = ['in', $skus];
        } else {
            return [];
        }
        if (empty($lang)) {
            $where['lang'] = 'zh';
        } else {
            $where['lang'] = $lang;
        }
        $result = $this->where($where)->field('name,sku')->select();
        if ($result) {
            $data = [];
            foreach ($result as $item) {
                $data[$item['sku']] = $item['name'];
            }
            return $data;
        } else {
            return [];
        }
    }

    /**
     * 导出模板
     * @return string
     */
    public function exportTemp() {
        if (redisHashExist('sku', 'skutemplate')) {
            return json_decode(redisHashGet('sku', 'skutemplate'), true);
        } else {
            $localDir = $_SERVER['DOCUMENT_ROOT'] . "/public/file/skuTemplate.xls";
            if (file_exists($localDir)) {
                //把导出的文件上传到文件服务器上
                $server = Yaf_Application::app()->getConfig()->myhost;
                $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
                $url = $server . '/V2/Uploadfile/upload';
                $data['tmp_name'] = $localDir;
                $data['type'] = 'application/excel';
                $data['name'] = pathinfo($localDir, PATHINFO_BASENAME);
                $fileId = postfile($data, $url);
                if ($fileId) {
                    //unlink($localDir);    //清理本地空间
                    $data = array('url' => $fastDFSServer . $fileId['url'], 'name' => $fileId['name']);
                    redisHashSet('sku', 'skutemplate', json_encode($data));
                    return $data;
                }
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $localDir . ' 上传到FastDFS失败', Log::INFO);
                return false;
            }
        }
    }

    /**
     * sku导出
     */
    public function export($input = []) {
        //$input['spu'] = '1303040000100000';
        ini_set("memory_limit", "1024M"); // 设置php可使用内存
        set_time_limit(0);  # 设置执行时间最大值

        $lang_ary = (isset($input['lang']) && !empty($input['lang'])) ? array($input['lang']) : array('zh', 'en', 'es', 'ru');
        $userInfo = getLoinInfo();
        $userModel = new UserModel();

        //目录
        $tmpDir = MYPATH . '/public/tmp/';
        $dirName = $tmpDir . time();
        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
            }
        }

        foreach ($lang_ary as $key => $lang) {
            $num = 1;    //控制文件名
            $i = 0;    //用来控制分页查询
            $j = 3;    //excel控制输出
            $l = 0;    //控制结束
            $length = 100;    //分页取

            $condition = array('lang' => $lang);
            if (isset($input['spu']) && !empty($input['spu'])) {    //spu编码
                $condition['spu'] = $input['spu'];
            }

            if (isset($input['sku']) && !empty($input['sku'])) {    //spu编码
                $condition['sku'] = $input['sku'];
            }

            if (isset($input['name']) && !empty($input['name'])) {    //名称
                $condition['name'] = array('like', '%' . $input['name'] . '%');
            }

            if (isset($input['type']) && $input['type'] == 'CHECKING') {    //类型：CHECKING->审核不取草稿状态。
                $condition['status'] = array('neq', 'DRAFT');
            }

            if (isset($input['status']) && !empty($input['status'])) {    //上架状态
                $condition['status'] = $input['status'];
            }
            if (isset($input['created_by']) && !empty($input['created_by'])) {    //创建人
                $condition['created_by'] = $input['created_by'];
            }
            if (isset($input['created_at']) && !empty($input['created_at'])) {    //创建时间段，注意格式：2017-09-08 00:00:00 - 2017-09-08 00:00:00
                $time_ary = explode(' - ', $input['created_at']);
                $condition['created_at'] = array('between', $time_ary);
                unset($time_ary);
            }
            do {
                $field = 'spu,sku,name,model,show_name,description,exw_days,min_pack_naked_qty,nude_cargo_unit,min_pack_unit,min_order_qty,purchase_price,purchase_price_cur_bn,nude_cargo_l_mm,nude_cargo_w_mm,nude_cargo_h_mm,min_pack_l_mm,min_pack_w_mm,min_pack_h_mm,net_weight_kg,gross_weight_kg,compose_require_pack,pack_type,name_customs,hs_code,tx_unit,tax_rebates_pct,regulatory_conds,commodity_ori_place,source,source_detail,status,created_by,created_at';
                $result = $this->field($field)->where($condition)->limit($i * $length, $length)->select();
                $count = count($result);
                if ($result) {
                    foreach ($result as $r) {
                        if (!isset($objPHPExcel) || !$objPHPExcel) {
                            PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));
                            $objPHPExcel = new PHPExcel();
                            $objPHPExcel->getProperties()->setCreator($userInfo['name']);
                            $objPHPExcel->getProperties()->setTitle("Product List");
                            $objPHPExcel->getProperties()->setLastModifiedBy($userInfo['name']);

                            //$objPHPExcel->createSheet();    //创建工作表
                            //$objPHPExcel->setActiveSheetIndex($key);    //设置工作表
                            //$objSheet = $objPHPExcel->getActiveSheet(0);    //当前sheet
                            $objPHPExcel->getActiveSheet(0)->setCellValue("A1", '商品信息')->mergeCells("A1:N1");
                            $objPHPExcel->getActiveSheet(0)->getStyle('A1:N1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('3D9140');
                            $objPHPExcel->getActiveSheet(0)->setCellValue("O1", '物流信息')->mergeCells("O1:X1");
                            $objPHPExcel->getActiveSheet(0)->getStyle('O1:X1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00C957');
                            $objPHPExcel->getActiveSheet(0)->setCellValue("Y1", '申报要素')->mergeCells("Y1:AD1");
                            $objPHPExcel->getActiveSheet(0)->getStyle('Y1:AD1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('00FF00');
                            $objPHPExcel->getActiveSheet(0)->setCellValue("AE1", '其他信息')->mergeCells("AE1:AI1");
                            $objPHPExcel->getActiveSheet(0)->getStyle('AE1:AI1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('3D9140');
                            $objPHPExcel->getActiveSheet(0)->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
                            $objPHPExcel->getActiveSheet(0)->getStyle("A1:AI2")
                                    ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            $objPHPExcel->getActiveSheet(0)->getStyle("A1:AI2")->getFont()->setSize(11)->setBold(true);    //粗体
                            $objPHPExcel->getActiveSheet(0)->getStyle('A2:AI2')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('4F94CD');
                            $objPHPExcel->getActiveSheet(0)->getRowDimension(1)->setRowHeight(20);
                            $objPHPExcel->getActiveSheet(0)->getRowDimension(2)->setRowHeight(20);
                            $column_width_25 = ["B", "C", "D", "E", "F", "G", "H", "I", "J", "K"];
                            foreach ($column_width_25 as $column) {
                                $objPHPExcel->getActiveSheet(0)->getColumnDimension($column)->setWidth(25);
                            }

                            $objPHPExcel->getActiveSheet(0)->setTitle($lang);
                            $objPHPExcel->getActiveSheet(0)->setCellValue("A2", "序号");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("B2", "SPU");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("C2", "SKU");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("D2", "商品名称");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("E2", "展示名称");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("F2", "型号");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("G2", "描述");    //对应产品优势（李志确认）
                            $objPHPExcel->getActiveSheet(0)->setCellValue("H2", "出货周期（天）");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("I2", "最小包装内裸货商品数量");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("J2", "商品裸货单位");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("K2", "最小包装单位");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("L2", "最小订货数量");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("M2", "进货价格");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("N2", "进货价格币种");

                            $objPHPExcel->getActiveSheet(0)->setCellValue("O2", "裸货尺寸长(mm)");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("P2", "裸货尺寸宽(mm)");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("Q2", "裸货尺寸高(mm)");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("R2", "最小包装后尺寸长(mm)");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("S2", "最小包装后尺寸宽(mm)");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("T2", "最小包装后尺寸高(mm)");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("U2", "净重(kg)");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("V2", "毛重(kg)");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("W2", "仓储运输包装及其他要求");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("X2", "包装类型");

                            $objPHPExcel->getActiveSheet(0)->setCellValue("Y2", "报关名称");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("Z2", "海关编码");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("AA2", "成交单位");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("AB2", "退税率(%)");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("AC2", "监管条件");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("AD2", "境内货源地");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("AE2", "数据来源");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("AF2", "数据来源详情");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("AG2", "状态");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("AH2", "创建人");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("AI2", "创建时间");
                        }

                        $objPHPExcel->getActiveSheet(0)->setCellValue("A" . $j, $j - 2);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("B" . $j, ' ' . $r['spu']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("C" . $j, ' ' . $r['sku']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("D" . $j, $r['name']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("E" . $j, $r['show_name']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("F" . $j, $r['model']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("G" . $j, $r['description']);    //对应产品优势（李志确认）
                        $objPHPExcel->getActiveSheet(0)->setCellValue("H" . $j, $r['exw_days']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("I" . $j, $r['min_pack_naked_qty']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("J" . $j, $r['nude_cargo_unit']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("K" . $j, $r['min_pack_unit']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("L" . $j, $r['min_order_qty']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("M" . $j, $r['purchase_price']);

                        $objPHPExcel->getActiveSheet(0)->setCellValue("N" . $j, $r['purchase_price_cur_bn']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("O" . $j, $r['nude_cargo_l_mm']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("P" . $j, $r['nude_cargo_w_mm']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("Q" . $j, $r['nude_cargo_h_mm']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("R" . $j, $r['min_pack_l_mm']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("S" . $j, $r['min_pack_w_mm']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("T" . $j, $r['min_pack_h_mm']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("U" . $j, $r['net_weight_kg']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("V" . $j, $r['gross_weight_kg']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("W" . $j, $r['compose_require_pack']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("X" . $j, $r['pack_type']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("Y" . $j, $r['name_customs']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("Z" . $j, $r['hs_code']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("AA" . $j, $r['tx_unit']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("AB" . $j, $r['tax_rebates_pct']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("AC" . $j, $r['regulatory_conds']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("AD" . $j, $r['commodity_ori_place']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("AE" . $j, $r['source']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("AF" . $j, $r['source_detail']);
                        $status = '';
                        switch ($r['status']) {
                            case 'VALID':
                                $status = '通过';
                                break;
                            case 'INVALID':
                                $status = '驳回';
                                break;
                            case 'CHECKING':
                                $status = '待审核';
                                break;
                            case 'DRAFT':
                                $status = '草稿';
                                break;
                            default:
                                $status = $r['status'];
                                break;
                        }
                        $objPHPExcel->getActiveSheet(0)->setCellValue("AG" . $j, $status);
                        unset($status);
                        $createbyInfo = $userModel->info($r['created_by']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("AH" . $j, $createbyInfo ? $createbyInfo['name'] : $r['created_by']);
                        unset($createbyInfo);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("AI" . $j, $r['created_at']);
                        $j++;
                        if ($j > 2002) {    //2000条
                            //保存文件
                            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
                            $objWriter->save($dirName . '/' . $lang . '_' . $num . '.xls');
                            unset($objWriter);
                            unset($objPHPExcel);
                            $j = 3;
                            $num ++;
                        } else {
                            if ($count < $length) {
                                $l++;
                            }
                            if ($l == $count) {
                                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
                                $objWriter->save($dirName . '/' . $lang . '_' . $num . '.xls');
                                unset($objWriter);
                                unset($objPHPExcel);
                            }
                        }
                        array_shift($result);
                    }
                }
                $i++;
                unset($result);
            } while ($count >= $length);
        }

        ZipHelper::zipDir($dirName, $dirName . '.zip');
        ZipHelper::removeDir($dirName);    //清除目录
        if (file_exists($dirName . '.zip')) {
            //把导出的文件上传到文件服务器上
            $server = Yaf_Application::app()->getConfig()->myhost;
            $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
            $url = $server . '/V2/Uploadfile/upload';
            $data['tmp_name'] = $dirName . '.zip';
            $data['type'] = 'application/excel';
            $data['name'] = pathinfo($dirName . '.zip', PATHINFO_BASENAME);
            $fileId = postfile($data, $url);
            if ($fileId) {
                return array('url' => $fastDFSServer . $fileId['url'], 'name' => $fileId['name']);
            }
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $dirName . '.zip 上传到FastDFS失败', Log::INFO);
            return false;
        } else {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Zip failed:' . $dirName . '.zip 打包失败', Log::INFO);
            return false;
        }
    }

    /**
     * sku导出csv
     * @param array $input
     * @return bool|string
     */
    public function exportCsv($input = []) {
        set_time_limit(0);  # 设置执行时间最大值
        //目录
        $tmpDir = MYPATH . '/public/tmp/';
        $dirName = $tmpDir . time();
        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
            }
        }

        $lang_ary = (isset($input['lang']) && !empty($input['lang'])) ? array($input['lang']) : array('en', 'es', 'ru');
        $titles = array(//定义标题
            'zh' => array(
                'num' => '序号',
                'spu' => 'SPU',
                'sku' => 'SKU',
                'name' => '商品名称',
                'show_name' => '展示名称',
                'model' => '型号',
                'description' => '描述',
                'exw_days' => '出货周期（天）',
                'min_pack_naked_qty' => '最小包装内裸货商品数量',
                'nude_cargo_unit' => '商品裸货单位',
                'min_pack_unit' => '最小包装单位',
                'min_order_qty' => '最小订货数量',
                'purchase_price' => '进货价格',
                'purchase_price_cur_bn' => '进货价格币种',
                'nude_cargo_l_mm' => '裸货尺寸长(mm)',
                'nude_cargo_w_mm' => '裸货尺寸宽(mm)',
                'nude_cargo_h_mm' => '裸货尺寸高(mm)',
                'min_pack_l_mm' => '最小包装后尺寸长(mm)',
                'min_pack_w_mm' => '最小包装后尺寸宽(mm)',
                'min_pack_h_mm' => '最小包装后尺寸高(mm)',
                'net_weight_kg' => '净重(kg)',
                'gross_weight_kg' => '毛重(kg)',
                'compose_require_pack' => '仓储运输包装及其他要求',
                'pack_type' => '包装类型',
                'name_customs' => '报关名称',
                'hs_code' => '海关编码',
                'tx_unit' => '成交单位',
                'tax_rebates_pct' => '退税率(%)',
                'regulatory_conds' => '监管条件',
                'commodity_ori_place' => '境内货源地',
                'source' => '数据来源',
                'source_detail' => '数据来源详情',
                'status' => '状态',
                'created_by' => '创建人',
                'created_at' => '创建时间'
            ),
            'en' => array(),
            'ru' => array(),
            'es' => array()
        );
        $userModel = new UserModel();
        foreach ($lang_ary as $key => $lang) {
            $num = 1;    //控制文件名
            $i = 0;    //用来控制分页查询
            $j = 1;    //excel控制输出
            $l = 0;    //控制结束
            $length = 100;    //分页取

            $condition = array('lang' => $lang);
            if (isset($input['spu']) && !empty($input['spu'])) {    //spu编码
                $condition['spu'] = $input['spu'];
            }

            if (isset($input['sku']) && !empty($input['sku'])) {    //spu编码
                $condition['sku'] = $input['sku'];
            }

            if (isset($input['name']) && !empty($input['name'])) {    //名称
                $condition['name'] = array('like', '%' . $input['name'] . '%');
            }

            if (isset($input['type']) && $input['type'] == 'CHECKING') {    //类型：CHECKING->审核不取草稿状态。
                $condition['status'] = array('neq', 'DRAFT');
            }

            if (isset($input['status']) && !empty($input['status'])) {    //上架状态
                $condition['status'] = $input['status'];
            }
            if (isset($input['created_by']) && !empty($input['created_by'])) {    //创建人
                $condition['created_by'] = $input['created_by'];
            }
            if (isset($input['created_at']) && !empty($input['created_at'])) {    //创建时间段，注意格式：2017-09-08 00:00:00 - 2017-09-08 00:00:00
                $time_ary = explode(' - ', $input['created_at']);
                $condition['created_at'] = array('between', $time_ary);
                unset($time_ary);
            }
            do {
                $field = 'spu,sku,name,model,show_name,description,exw_days,min_pack_naked_qty,nude_cargo_unit,min_pack_unit,min_order_qty,purchase_price,purchase_price_cur_bn,nude_cargo_l_mm,nude_cargo_w_mm,nude_cargo_h_mm,min_pack_l_mm,min_pack_w_mm,min_pack_h_mm,net_weight_kg,gross_weight_kg,compose_require_pack,pack_type,name_customs,hs_code,tx_unit,tax_rebates_pct,regulatory_conds,commodity_ori_place,source,source_detail,status,created_by,created_at';
                $result = $this->field($field)->where($condition)->limit($i * $length, $length)->select();
                $count = count($result);
                if ($result) {
                    foreach ($result as $r) {
                        if (!isset($fhandle) || !$fhandle) {
                            $fhandle = fopen($dirName . '/' . $lang . '_' . $num . '.csv', 'w');
                            $titles[$lang] = toGbk(empty($titles[$lang]) ? $titles['zh'] : $titles[$lang]);
                            fputcsv($fhandle, $titles[$lang]);
                            unset($title);
                        }

                        $content = [];
                        foreach ($titles[$lang] as $k => $value) {
                            if ($k == 'num') {
                                $r['num'] = $j;
                            }
                            if ($k == 'status') {
                                switch ($r['status']) {
                                    case 'VALID':
                                        $r[$k] = '通过';
                                        break;
                                    case 'INVALID':
                                        $r[$k] = '驳回';
                                        break;
                                    case 'CHECKING':
                                        $r[$k] = '待审核';
                                        break;
                                    case 'DRAFT':
                                        $r[$k] = '草稿';
                                        break;
                                    default:
                                        $r[$k] = $r['status'];
                                        break;
                                }
                            }
                            if ($k == 'created_by') {
                                $createbyInfo = $userModel->info($r['created_by']);
                                $r[$k] = $createbyInfo ? $createbyInfo['name'] : $r['created_by'];
                            }
                            $content[$k] = iconv('UTF-8', 'GBK', "\t" . $r[$k]);
                        }
                        fputcsv($fhandle, $content);
                        unset($r);
                        unset($content);
                        if ($j > 2000) {    //2000条
                            fclose($fhandle);
                            unset($fhandle);
                            $j = 1;
                            $num ++;
                        } else {
                            if ($count < $length) {
                                $l++;
                            }
                            if ($l == $count) {
                                fclose($fhandle);
                                unset($fhandle);
                            }
                        }
                        $j++;
                    }
                    unset($result);
                }
                $i++;
            } while ($count >= $length);
        }

        ZipHelper::zipDir($dirName, $dirName . '.zip');
        ZipHelper::removeDir($dirName);    //清除目录
        if (file_exists($dirName . '.zip')) {
            //把导出的文件上传到文件服务器上
            $server = Yaf_Application::app()->getConfig()->myhost;
            $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
            $url = $server . '/V2/Uploadfile/upload';
            $data['tmp_name'] = $dirName . '.zip';
            $data['type'] = 'application/excel';
            $data['name'] = pathinfo($dirName . '.zip', PATHINFO_BASENAME);
            $fileId = postfile($data, $url);
            if ($fileId) {
                unlink($dirName . '.zip');
                return array('url' => $fastDFSServer . $fileId['url'], 'name' => $fileId['name']);
            }
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $dirName . '.zip 上传到FastDFS失败', Log::INFO);
            return false;
        } else {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Zip failed:' . $dirName . '.zip 打包失败', Log::INFO);
            return false;
        }
    }

    /**
     * 获取用户创建的第一个sku信息
     * @author klp
     */
    public function getSku($userInfo, $spu, $order = 'id asc') {
        if (empty($spu['spu'])) {
            return false;
        }
        $where['spu'] = $spu['spu'];
//        $where['status'] = array('neq', self::STATUS_DELETED);
        $where['status'] = self::STATUS_VALID;
        $where['deleted_flag'] = self::DELETE_N;
        $where['created_by'] = $userInfo['id'];
        return $this->where($where)->order($order)->select();
    }

    public function import($spu = '', $url = '', $lang = '', $process = '') {
        if (empty($spu) || empty($url) || empty($lang)) {
            return false;
        }

        /** 返回导入进度start */
        $progress_key = md5(json_encode(array($spu, $url, $lang)));
        if (!empty($process)) {
            if (redisExist($progress_key)) {
                $progress_redis = json_decode(redisGet($progress_key), true);
                return $progress_redis['processed'] < $progress_redis['total'] ? ceil($progress_redis['processed'] / $progress_redis['total'] * 100) : 100;
            } else {
                return 100;
            }
        }
        /** 导入进度end */
        $progress_redis = array('start_time' => time());    //用来记录导入进度信息
        //$localFile = $_SERVER['DOCUMENT_ROOT'] . "/public/file/skuTemplate1.xls";
        $localFile = ExcelHelperTrait::download2local($url);    //下载到本地临时文件
        if (!file_exists($localFile)) {
            return false;
        }
        $fileType = PHPExcel_IOFactory::identify($localFile);    //获取文件类型
        $objReader = PHPExcel_IOFactory::createReader($fileType);    //创建PHPExcel读取对象
        $objPHPExcel = $objReader->load($localFile);    //加载文件
        //$data = $objPHPExcel->getSheet(0)->toArray();
        $columns = $objPHPExcel->getSheet(0)->getHighestColumn();    //获取总列
        $rows = $objPHPExcel->getSheet(0)->getHighestRow();    //获取总行
        $columnsIndex = PHPExcel_Cell::columnIndexFromString($columns);
        if ($rows <= 4) {
            return false;
        }
        $progress_redis['total'] = $rows;
        $userInfo = getLoinInfo();
        $productModel = new ProductModel();
        $es_goods_model = new EsGoodsModel();
        $supplierModel = new SupplierModel();
        $goodsSupplierModel = new GoodsSupplierModel();
        $goodsCostPriceModel = new GoodsCostPriceModel();
        $goodsAttrModel = new GoodsAttrModel();
        $faild = $success = $ext_goods_start = $ext_goods_end = $ext_hs_start = 0;
        $maxCol = PHPExcel_Cell::stringFromColumnIndex($columnsIndex);
        $itemNo = '';
        for ($i = 1; $i <= $rows; $i++) {
            $progress_redis['processed'] = $i;    //记录导入进度信息
            redisSet($progress_key, json_encode($progress_redis));

            $key = $objPHPExcel->getSheet(0)->toArray(); //转码
            if ($i == 3) {    //获取扩展属性起止值
                for ($index = 0; $index < $columnsIndex; $index++) {
                    $col_name = PHPExcel_Cell::stringFromColumnIndex($index); //由列数反转列名(0->'A')
                    $key = $objPHPExcel->getSheet(0)->getCell($col_name . 3)->getValue(); //转码
                    if ($index == $columnsIndex - 1 && $key == '导入结果') {
                        $maxCol = $col_name;
                    }
                    if ($key == '币种') {    //获取币种下标用以取扩展属性
                        $ext_goods_start = $index + 1;
                    }
                    if ($key == '物流信息') {    //获取物流信息下标用以取扩展属性
                        $ext_goods_end = $index - 1;
                    }
                    if ($key == '境内货源地') {    //获取境内货源地下标用以取申报扩展属性
                        $ext_hs_start = $index + 1;
                    }
                    if ($key == '订货号') {        //获取订货号下标用以存储sku编码
                        $itemNo = $col_name;
                    }
                }
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . '3', '导入结果');
            }

            if ($i <= 4) {
                continue;
            }
            $data = array();
            for ($index = 0; $index < $columnsIndex; $index++) {
                $col_name = PHPExcel_Cell::stringFromColumnIndex($index); //由列数反转列名(0->'A')
                //$value    = mb_convert_encoding($objPHPExcel->getSheet(0)->getCell($col_name . $i)->getValue(), 'gbk', 'utf8');//转码
                $key = trim($objPHPExcel->getSheet(0)->getCell($col_name . 3)->getValue()); //转码
                if (empty($key) || $key == '…' || $key == '导入结果') {
                    continue;
                }
                $value = trim($objPHPExcel->getSheet(0)->getCell($col_name . $i)->getValue()); //转码
                if ($index >= $ext_goods_start && $index <= $ext_goods_end) {    //扩展属性
                    if (!empty($value)) {
                        $data['spec_attrs'][$key] = $value;
                    }
                    continue;
                }
                if ($index >= $ext_hs_start) {    //申报要素扩展属性
                    if (!empty($value)) {
                        $data['ex_hs_attrs'][$key] = $value;
                    }
                    continue;
                }
                $data[$key] = $value;
            }
            if (empty($data)) {
                continue;
            }

            //数据组装与校验开始
            $supplie = $data['供应商名称'];    //先处理供应商 必填
            if (empty($data['供应商名称'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[供应商不能为空]');
                continue;
            }
            $supplierInfo = $supplierModel->field('id,supplier_no,brand')->where(array('deleted_flag' => 'N', 'name' => $supplie))->find();
            if (!$supplierInfo) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[供应商不存在]');
                continue;
            }
            $data_tmp = [];
            $data_tmp['spu'] = $spu;
            $input_sku = $data['订货号'];    //输入的sku  订货号
            $data_tmp['lang'] = $lang;
            $data_tmp['name'] = $data['名称'];    //名称
            $data_tmp['model'] = $data['型号'];    //型号
            $data_tmp['exw_days'] = $data['出货周期(天)'];    //出货周期
            if (empty($data_tmp['exw_days']) || !is_numeric($data_tmp['exw_days'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[出货周期有误]');
                continue;
            }
            $data_tmp['min_pack_naked_qty'] = $data['最小包装内裸货商品数量'];    //最小包装内裸货商品数量
            if (empty($data_tmp['min_pack_naked_qty']) || !is_numeric($data_tmp['min_pack_naked_qty'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[最小包装内裸货商品数量有误]');
                continue;
            }
            $data_tmp['nude_cargo_unit'] = $data['商品裸货单位'];    //商品裸货单位
            if (empty($data_tmp['nude_cargo_unit'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[商品裸货单位必填]');
                continue;
            }
            $data_tmp['min_pack_unit'] = $data['最小包装单位'];    //最小包装单位
            if (empty($data_tmp['min_pack_unit'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[最小包装单位必填]');
                continue;
            }
            $data_tmp['min_order_qty'] = $data['最小订货数量'];    //最小订货数量
            if (empty($data_tmp['min_order_qty']) || !is_numeric($data_tmp['min_order_qty'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[最小订货数量有误]');
                continue;
            }
            $data_tmp['purchase_price'] = $data['供应商供货价'];    //进货价格
            if (!is_numeric($data_tmp['purchase_price'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[供应商供货价有误]');
                continue;
            }
            $data_tmp['purchase_price_cur_bn'] = $data['币种'];    //进货价格币种
            if (!isset($data['spec_attrs']) || empty($data['spec_attrs'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[非固定属性必填]');
                continue;
            }
            $data_tmp['nude_cargo_l_mm'] = $data['裸货尺寸长(mm)'];    //裸货尺寸长(mm)
            if (!is_numeric($data_tmp['nude_cargo_l_mm'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[裸货尺寸长有误]');
                continue;
            }
            $data_tmp['nude_cargo_w_mm'] = $data['裸货尺寸宽(mm)'];    //裸货尺寸宽(mm)
            if (!is_numeric($data_tmp['nude_cargo_w_mm'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[裸货尺寸宽有误]');
                continue;
            }
            $data_tmp['nude_cargo_h_mm'] = $data['裸货尺寸高(mm)'];    //裸货尺寸高(mm)
            if (!is_numeric($data_tmp['nude_cargo_h_mm'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[裸货尺寸高有误]');
                continue;
            }
            $data_tmp['min_pack_l_mm'] = $data['最小包装后尺寸长(mm)'];    //最小包装后尺寸长(mm)
            if (!is_numeric($data_tmp['min_pack_l_mm'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[最小包装后尺寸长有误]');
                continue;
            }
            $data_tmp['min_pack_w_mm'] = $data['最小包装后尺寸宽(mm)'];    //最小包装后尺寸宽(mm)
            if (!is_numeric($data_tmp['min_pack_w_mm'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[最小包装后尺寸宽有误]');
                continue;
            }
            $data_tmp['min_pack_h_mm'] = $data['最小包装后尺寸高(mm)'];    //最小包装后尺寸高(mm)
            if (!is_numeric($data_tmp['min_pack_h_mm'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[最小包装后尺寸高有误]');
                continue;
            }
            $data_tmp['net_weight_kg'] = $data['净重(kg)'];    //净重(kg)
            if (!is_numeric($data_tmp['net_weight_kg'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[净重有误]');
                continue;
            }
            $data_tmp['gross_weight_kg'] = (float) $data['毛重(kg)'];    //毛重(kg)
            if (!is_numeric($data_tmp['gross_weight_kg'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[毛重有误]');
                continue;
            }
            $data_tmp['compose_require_pack'] = $data['仓储运输包装及其他要求'];    //仓储运输包装及其他要求
            $data_tmp['pack_type'] = $data['包装类型'];    //包装类型
            $data_tmp['name_customs'] = $data['中文品名(报关用)'];    //报关名称
            $data_tmp['hs_code'] = $data['海关编码'];    //海关编码
            $data_tmp['tx_unit'] = $data['成交单位'];    //成交单位
            $data_tmp['tax_rebates_pct'] = $data['退税率(%)'];    //退税率(%)
            if (!is_numeric($data_tmp['tax_rebates_pct'])) {
                $faild++;
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[退税率有误]');
                continue;
            }
            $data_tmp['regulatory_conds'] = $data['监管条件'];    //监管条件
            $data_tmp['commodity_ori_place'] = $data['境内货源地'];    //境内货源地
            $data_tmp['source'] = 'ERUI';
            $data_tmp['source_detail'] = 'Excel批量导入';
            $data_tmp['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
            $data_tmp['created_at'] = date('Y-m-d H:i:s');
            // 数据组装与校验结束

            /**
             * 查询是否存在
             */
            $condition = array(
                'name' => $data_tmp['name'],
                'lang' => $lang,
                'spu' => $spu,
                'model' => $data_tmp['model'],
                'deleted_flag' => 'N',
            );
            $spec_attrs_array = array('spec_attrs' => $data['spec_attrs']);
            //数据导入
            $this->startTrans();
            $workType = '';
            $result = 0;
            try {
                if ($this->_checkExit($condition, $spec_attrs_array, true) === false) {
                    if (empty($input_sku)) {    //存在并且未输入sku则报存在
                        $faild++;
                        $this->rollback();
                        $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[已存在]');
                        continue;
                    } else {
                        $workType = '更新';
                        $condition_update = array(
                            'sku' => $input_sku,
                            'lang' => $lang
                        );
                        $result = $this->where($condition_update)->save($data_tmp);
                    }
                } else {
                    $workType = '添加';
                    $data_tmp['status'] = $this::STATUS_CHECKING;
                    $input_sku = $data_tmp['sku'] = !empty($input_sku) ? $input_sku : $this->setRealSku(array(array('spu' => $spu)));    //生成sku
                    $result = $this->add($this->create($data_tmp));
                }
            } catch (Exception $e) {
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[内部错误]');
                $faild++;
                $this->rollback();
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . $e, Log::ERR);
                continue;
            }

            if ($result) {
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作成功');
                $success1 = 1;
                if ($workType == '添加') {
                    //更新sku数
                    try {
                        $productModel->where(['spu' => $spu, 'lang' => $lang])->save(array('sku_count' => array('exp', 'sku_count' . '+' . 1)));
                    } catch (Exception $e) {
                        $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[更新spu的sku统计失败]');
                        $faild++;
                        $this->rollback();
                        Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Import Faild:' . $e, Log::ERR);
                        continue;
                    }
                }

                try {    //商品供应商关系
                    $data_supplier = array(
                        'sku' => $input_sku,
                        'supplier_id' => $supplierInfo['id'],
                        'brand' => $supplierInfo['brand'],
                        'created_by' => isset($userInfo['id']) ? $userInfo['id'] : null,
                        'created_at' => date('Y-m-d H:i:s', time()),
                        'status' => 'VALID'
                    );
                    $where_supplier = array('sku' => $input_sku, 'supplier_id' => $supplierInfo['id']);
                    $select_gs = $goodsSupplierModel->where($where_supplier)->find();
                    if ($select_gs) {
                        $goodsSupplierModel->where($where_supplier)->save($data_supplier);
                    } else {
                        $goodsSupplierModel->where($where_supplier)->add($goodsSupplierModel->create($data_supplier));
                    }
                } catch (Exception $e) {
                    $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[操作商品供应商失败]');
                    $faild++;
                    $this->rollback();
                    Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Import Faild:' . $e, Log::ERR);
                    continue;
                }

                try {    //供应商商品价格
                    $data_goods_cost_price = array(
                        'sku' => $input_sku,
                        'supplier_id' => $supplierInfo['id'],
                        'price' => $data_tmp['purchase_price'],
                        'price_unit' => $data_tmp['min_pack_unit'],
                        'price_cur_bn' => $data_tmp['purchase_price_cur_bn'],
                        'min_purchase_qty' => $data_tmp['min_order_qty'],
                        'pricing_date' => date('Y-m-d H:i:s', time()),
                        'created_by' => isset($userInfo['id']) ? $userInfo['id'] : null,
                        'created_at' => date('Y-m-d H:i:s', time()),
                        'status' => 'VALID'
                    );
                    $select_gsp = $goodsCostPriceModel->where($where_supplier)->find();
                    if ($select_gsp) {
                        $goodsCostPriceModel->where($where_supplier)->save($data_goods_cost_price);
                    } else {
                        $goodsCostPriceModel->where($where_supplier)->add($goodsCostPriceModel->create($data_goods_cost_price));
                    }
                } catch (Exception $e) {
                    $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[操作商品供应商价格失败]');
                    $faild++;
                    $this->rollback();
                    Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Import Faild:' . $e, Log::ERR);
                    continue;
                }

                try {    //商品属性
                    $data_goodsattr = array(
                        'spu' => $spu,
                        'lang' => $lang,
                        'sku' => $input_sku,
                        'spec_attrs' => empty($data['spec_attrs']) ? null : json_encode($data['spec_attrs'], JSON_UNESCAPED_UNICODE),
                        'ex_goods_attrs' => empty($data['ex_goods_attrs']) ? null : json_encode($data['ex_goods_attrs'], JSON_UNESCAPED_UNICODE),
                        'ex_hs_attrs' => empty($data['ex_hs_attrs']) ? null : json_encode($data['ex_hs_attrs'], JSON_UNESCAPED_UNICODE),
                        'created_by' => isset($userInfo['id']) ? $userInfo['id'] : null,
                        'created_at' => date('Y-m-d H:i:s', time())
                    );
                    $where_attr = array('spu' => $spu, 'lang' => $lang, 'sku' => $input_sku);
                    $select_attr = $goodsAttrModel->where($where_attr)->find();
                    if ($select_attr) {
                        $goodsAttrModel->where($where_attr)->save($data_goodsattr);
                    } else {
                        $goodsAttrModel->where($where_attr)->add($goodsAttrModel->create($data_goodsattr));
                    }
                } catch (Exception $e) {
                    $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败[操作商品属性失败]');
                    $faild++;
                    $this->rollback();
                    Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Import Faild:' . $e, Log::ERR);
                    continue;
                }

                $objPHPExcel->getSheet(0)->setCellValue($itemNo . $i, ' ' . $input_sku);
                $success++;
                $this->commit();
            } else {
                $faild++;
                $this->rollback();
                $objPHPExcel->getSheet(0)->setCellValue($maxCol . $i, '操作失败');
                continue;
            }
        }
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($localFile);    //文件保存
        //把导出的文件上传到文件服务器上
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server . '/V2/Uploadfile/upload';
        $data_fastDFS['tmp_name'] = $localFile;
        $data_fastDFS['type'] = 'application/excel';
        $data_fastDFS['name'] = pathinfo($localFile, PATHINFO_BASENAME);
        $fileId = postfile($data_fastDFS, $url);
        if ($fileId) {
            unlink($localFile);
            return array('success' => $success, 'faild' => $faild, 'url' => $fastDFSServer . $fileId['url'], 'name' => $fileId['name']);
        }
        Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $localFile . ' 上传到FastDFS失败', Log::INFO);
        return false;
    }

}
