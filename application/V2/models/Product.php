<?php

/**
 * 产品.
 * User: linkai
 * Date: 2017/6/15
 * Time: 18:52
 */
class ProductModel extends PublicModel {

    const STATUS_NORMAL = 'NORMAL'; //发布
    const STATUS_DRAFT = 'DRAFT';          //草稿
    const STATUS_CLOSED = 'CLOSED'; //关闭
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_TEST = 'TEST'; //测试  暂存；
    const STATUS_CHECKING = 'CHECKING'; //审核中；
    const STATUS_INVALID = 'INVALID'; //无效
    const STATUS_DELETED = 'DELETED'; //DELETED-删除
    const RECOMMEND_Y = 'Y'; //推荐
    const RECOMMEND_N = 'N'; //未推荐
    const DELETE_Y = 'Y';
    const DELETE_N = 'N';

    //定义校验规则
    protected $field = array(
        //'lang' => array('method','checkLang'),
        'material_cat_no' => array('required'),
        'name' => array('required'),
            //  'brand' => array('required'),//暂时先去掉品牌的必填验证
    );

    /**
     * 构造方法
     * 初始化数据库跟表
     */
    public function __construct() {
        //动态读取配置中的数据库配置   便于后期维护
        $config_obj = Yaf_Registry::get("config");
        $config_db = $config_obj->database->config->goods->toArray();
        $this->dbName = $config_db['name'];
        $this->tablePrefix = $config_db['tablePrefix'];
        $this->tableName = 'product';

        parent::__construct();
    }

    /**
     * 获取操作数据
     * @param array $input 请求数据
     * @param string $type 操作类型（INSERT/UPDATE）
     */
    public function getData($input = [], $type = 'INSERT', $lang = '') {
        $data = array();
        //展示分类
        if (isset($input['material_cat_no'])) {
            $data['material_cat_no'] = trim($input['material_cat_no']);
        } elseif ($type == 'INSERT') {
            $data['material_cat_no'] = '';
        }

        //name名称
        if (isset($input['name'])) {
            $data['name'] = htmlspecialchars($input['name']);
        } elseif ($type == 'INSERT') {
            $data['name'] = '';
        }

        //展示名称
        if (isset($input['show_name'])) {
            $data['show_name'] = htmlspecialchars($input['show_name']);
        } else {
            $data['show_name'] = '';
        }

        //品牌  看前台传什么如果传id则需要查询brand表，否则直至存json
        if (isset($input['brand'])) {
            if (is_numeric($input['brand'])) {
                $data['brand'] = '';
                $brand = new BrandModel();
                $brandInfo = $brand->info($input['brand']);
                if ($brandInfo) {
                    $brandAry = json_decode($brandInfo['brand'], true);
                    foreach ($brandAry as $r) {
                        if ($r['lang'] == $lang) {
                            unset($r['lang']);
                            unset($r['manufacturer']);
                            $r['id'] = $brandInfo['id'];
                            $data['brand'] = json_encode($r, JSON_UNESCAPED_UNICODE);
                            break;
                        }
                    }
                }
            } else {
                $data['brand'] = is_array($input['brand']) ? json_encode($input['brand'], JSON_UNESCAPED_UNICODE) : json_encode(array('lang' => $lang, 'name' => $input['brand']),JSON_UNESCAPED_UNICODE);
            }
        } elseif ($type == 'INSERT') {
            $data['brand'] = '';
        }

        //关键字
        if (isset($input['keywords'])) {
            $data['keywords'] = removeXSS($input['keywords']);
        } elseif ($type == 'INSERT') {
            $data['keywords'] = '';
        }

        //执行标准
        if (isset($input['exe_standard'])) {
            $data['exe_standard'] = removeXSS($input['exe_standard']);
        } elseif ($type == 'INSERT') {
            $data['exe_standard'] = '';
        }

        //技术参数
        if (isset($input['tech_paras'])) {
            $data['tech_paras'] = removeXSS($input['tech_paras']);
        } elseif ($type == 'INSERT') {
            $data['tech_paras'] = '';
        }

        //产品优势
        if (isset($input['advantages'])) {
            $data['advantages'] = removeXSS($input['advantages']);
        } elseif ($type == 'INSERT') {
            $data['advantages'] = '';
        }

        //详情
        if (isset($input['description'])) {
            $data['description'] = removeXSS($input['description']);
        } elseif ($type == 'INSERT') {
            $data['description'] = '';
        }

        //简介
        if (isset($input['profile'])) {
            $data['profile'] = removeXSS($input['profile']);
        } elseif ($type == 'INSERT') {
            $data['profile'] = '';
        }

        //工作原理
        if (isset($input['principle'])) {
            $data['principle'] = removeXSS($input['principle']);
        } elseif ($type == 'INSERT') {
            $data['principle'] = '';
        }

        //适用范围
        if (isset($input['app_scope'])) {
            $data['app_scope'] = removeXSS($input['app_scope']);
        } elseif ($type == 'INSERT') {
            $data['app_scope'] = '';
        }

        //使用特点
        if (isset($input['properties'])) {
            $data['properties'] = removeXSS($input['properties']);
        } elseif ($type == 'INSERT') {
            $data['properties'] = '';
        }

        //质保期
        if (isset($input['warranty'])) {
            $data['warranty'] = $input['warranty'];
        } elseif ($type == 'INSERT') {
            $data['warranty'] = '';
        }

        //供应能力
        if (isset($input['supply_ability'])) {
            $data['supply_ability'] = $input['supply_ability'];
        } elseif ($type == 'INSERT') {
            $data['supply_ability'] = '';
        }

        if( $type == 'INSERT' ){
            $data['status'] = 'DRAFT';
        }

        return $data;
    }

    /**
     * 添加/编辑
     * @param object $input 操作集
     */
    public function editInfo($input = []) {
        if (empty($input)) {
            return false;
        }

        $spu = isset($input['spu']) ? trim($input['spu']) : $this->createSpu(); //不存在生产spu
        $this->startTrans();
        try {
            $userInfo = getLoinInfo(); //获取当前用户信息
            foreach ($input as $key => $item) {
                if (in_array($key, array('zh', 'en', 'ru', 'es'))) {
                    $data = $this->getData($item, isset($input['spu']) ? 'UPDATE' : 'INSERT', $key);
                    $data['lang'] = $key;
                    if (empty($data) || empty($data['name'])) {
                        continue;
                    }

                    if (empty($data['show_name'])) {
                        $data['show_name'] = $data['name'];
                    }
                    //除暂存外都进行校验     这里存在暂存重复加的问题，此问题暂时预留。
                    //$input['status'] = (isset($input['status']) && in_array(strtoupper($input['status']), array('DRAFT', 'TEST', 'VALID', 'CHECKING'))) ? strtoupper($input['status']) : 'DRAFT';
                    //if ($input['status'] != 'DRAFT') {
                        //字段校验
                        $this->checkParam($data, $this->field);

                        $exist_condition = array(//添加时判断同一语言，name,meterial_cat_no是否存在
                            'lang' => $key,
                            'name' => $data['name'],
                            'material_cat_no' => $data['material_cat_no']
                            //'status' => array('neq', 'DRAFT')
                        );
                        if (isset($input['spu'])) {
                            $exist_condition['spu'] = array('neq', $spu);
                        }
                        $exist = $this->where($exist_condition)->find();
                        if ($exist) {
                            jsonReturn('', ErrorMsg::EXIST);
                        }
                    //}
                    //$data['status'] = $input['status'];

                    $exist_check = $this->field('id')->where(array('spu' => $spu, 'lang' => $key))->find();
                    if (isset($input['spu'])) {
                        $data['updated_by'] = isset($userInfo['id']) ? $userInfo['id'] : null; //修改人
                        $data['updated_at'] = date('Y-m-d H:i:s', time());
                    }
                    if ($exist_check) {    //修改
                        $data['updated_by'] = isset($userInfo['id']) ? $userInfo['id'] : null; //修改人
                        $data['updated_at'] = date('Y-m-d H:i:s', time());
                        $result = $this->where(array('spu' => $spu, 'lang' => $key))->save($data);
                        if (!$result) {
                            $this->rollback();
                            return false;
                        }
                    } else {    //添加
                        $data['qrcode'] = createQrcode('/product/info/' . $data['spu']);    //生成spu二维码  注意模块    冗余字段这块还要看后期需求是否分语言
                        $data['spu'] = $spu;
                        $data['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : null; //创建人
                        $data['created_at'] = date('Y-m-d H:i:s', time());
                        $result = $this->add($data);
                        if (!$result) {
                            $this->rollback();
                            return false;
                        }
                    }
                } elseif ($key == 'attachs') {
                    if ($item) {
                        //if (!isset($input['spu'])) {
                            if (!$this->checkAttachImage($item)) {
                                jsonReturn('', '1000', '产品图不能为空');
                            }
                        //}

                        $pattach = new ProductAttachModel();

                        $update_condition = array(
                            'spu' => $spu
                        );
                        $pattach ->where($update_condition)->save(array('status'=>$pattach::STATUS_DELETED,'deleted_flag'=>$pattach::DELETED_Y));

                        //$ids = [];

                        foreach ($item as $atta) {
                            $data = array(
                                'spu' => $spu,
                                'attach_type' => isset($atta['attach_type']) ? $atta['attach_type'] : '',
                                'attach_name' => isset($atta['attach_name']) ? $atta['attach_name'] : $atta['attach_url'],
                                'attach_url' => isset($atta['attach_url']) ? $atta['attach_url'] : '',
                                'default_flag' => (isset($atta['default_flag']) && $atta['default_flag']) ? 'Y' : 'N',
                            );
                            if (isset($input['spu'])) {    //修改
                                $data['id'] = isset($atta['id']) ? $atta['id'] : '';
                            }
                            if (empty($data['attach_url'])) {
                                continue;
                            }
                            $attach = $pattach->addAttach($data);
                            if (!$attach) {
                                $this->rollback();
                                return false;
                            }/*else{
                                $ids[] = $attach;
                            }
                            //删除其他附件
                            $update_condition = array(
                                'spu' => $spu,
                                'id' => array('notin',$ids)
                            );
                            $pattach ->where($update_condition)->save(array('status'=>$pattach::STATUS_DELETED,'deleted_flag'=>$pattach::DELETED_Y));
                            */
                        }
                    }
                } else {
                    continue;
                }
            }
            $this->commit();
            return $spu;
        } catch (Exception $e) {
            $this->rollback();
        }
    }

    /**
     * 修改状态
     * @param array $spu    spu编码数组['spu1','spu2']
     * @param string $lang  语言（zh/en/ru/es）
     * @param string $status 状态
     * @param string $remark 评语
     * @example: updateStatus(array('111','222'),'','CHECKING')    #不分语言处理
     */
    public function updateStatus($spu = '', $lang = '', $status = '', $remark = '') {
        if (empty($spu) || empty($status))
            return false;

        if ($spu) {
            $this->startTrans();
            try {

                $spuary = [];
                $userInfo = getLoinInfo();
                if (is_array($spu)) {
                    foreach ($spu as $r) {
                        $where = array(
                            'spu' => $r,
                        );
                        if (!empty($lang)) {
                            $where['lang'] = $lang;
                        }
                        $updata = array('status' => $status);
                        /**
                         * 审核人跟时间
                         */
                        if ($status == self::STATUS_VALID || $status == self::STATUS_INVALID) {
                            $updata['checked_at'] = date('Y-m-d H:i:s', time());
                            $updata['checked_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                        }
                        $result = $this->where($where)->save($updata);
                        if ($result) {
                            $spuary[] = array('spu' => $r, 'lang' => $lang, 'remarks' => $remark);
                        } else {
                            $this->rollback();
                            return false;
                        }
                    }
                } else {
                    $where = array(
                        'spu' => $spu,
                    );
                    if (!empty($lang)) {
                        $where['lang'] = $lang;
                    }
                    $updata = array('status' => $status);
                    /**
                     * 审核人跟时间
                     */
                    if ($status == self::STATUS_VALID || $status == self::STATUS_INVALID) {
                        $updata['checked_at'] = date('Y-m-d H:i:s', time());
                        $updata['checked_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                    }
                    $result = $this->where($where)->save($updata);
                    if ($result) {
                        $spuary[] = array('spu' => $spu, 'lang' => $lang, 'remarks' => $remark);
                    } else {
                        $this->rollback();
                        return false;
                    }
                }
                switch ($status) {
                    case self::STATUS_VALID:
                        $pclog = new ProductCheckLogModel();
                        $pclog->takeRecord($spuary, $pclog::STATUS_PASS);
                        break;
                    case self::STATUS_INVALID:
                        $pclog = new ProductCheckLogModel();
                        $pclog->takeRecord($spuary, $pclog::STATUS_REJECTED);
                        break;
                }

                $this->commit();
                return true;
            } catch (Exception $e) {
                $this->rollback();
                return false;
            }
        }
        return false;
    }

    /*
     * 删除
     * @param string $spu
     * @param string $lang
     */

    public function deleteInfo($spu = '', $lang = '') {
        if (empty($spu)) {
            return false;
        }

        if ($spu) {
            $this->startTrans();
            try {

                $goodsModel = new GoodsModel();
                if (is_array($spu)) {
                    foreach ($spu as $r) {
                        $where = array(
                            'spu' => $r,
                        );
                        if (!empty($lang)) {
                            $where['lang'] = $lang;
                        }
                        $result = $this->where($where)->save(array('deleted_flag' => self::DELETE_Y, 'sku_count' => 0));
                        if ($result) {
                            /**
                             * 删除ｓｋｕ
                             * 优化意见：这块最好放入队列，以确保成功删除掉。
                             */
                            $res = $goodsModel->field('spu')->where($where)->select();

                            if ($res) {
                                $goodsModel->where($where)->save(array('deleted_flag' => self::DELETE_Y));
                            }
                        } else {
                            $this->rollback();
                            return false;
                        }
                    }
                } else {
                    $where = array(
                        'spu' => $spu,
                    );
                    if (!empty($lang)) {
                        $where['lang'] = $lang;
                    }

                    $result = $this->where($where)->save(array('deleted_flag' => self::DELETE_Y, 'sku_count' => 0));
                    if ($result) {
                        /**
                         * 删除ｓｋｕ
                         * 优化意见：这块最好放入队列，以确保成功删除掉。
                         */
                        $res = $goodsModel->field('spu')->where($where)->select();

                        if ($res) {
                            $goodsModel->where($where)->save(array('deleted_flag' => self::DELETE_Y));
                        }
                    } else {
                        $this->rollback();
                        return false;
                    }
                }

                $this->commit();
                return true;
            } catch (Exception $e) {
                $this->rollback();
                return false;
            }
        }
        return false;
    }

    /**
     * 列表查询
     */
    public function getList($condition = [], $field = '', $offset = 0, $length = 20) {
        $field = empty($field) ? 'lang,material_cat_no,spu,name,show_name,brand,keywords,exe_standard,tech_paras,advantages,description,profile,principle,app_scope,properties,warranty' : $field;
        try {
            $result = $this->field($field)->where($condition)->limit($offset, $length)->select();
            return $result ? $result : array();
        } catch (Exception $e) {
            return array();
        }
    }

    /**
     * spu详情
     * @param string $spu    spu编码
     * @param string $lang    语言
     * @param string $status    状态
     * return array
     */
    public function getInfo($spu = '', $lang = '', $status = '') {
        if (empty($spu)) {
            return array();
        }

        $condition = array(
            'spu' => $spu,
            'deleted_flag' => self::DELETE_N,
        );
        if (!empty($lang)) {
            $condition['lang'] = $lang;
        }
        if (!empty($status)) {
            $condition['status'] = $status;
        }

        //读取redis缓存
        if (redisHashExist('spu', md5(json_encode($condition)))) {
//            return json_decode(redisHashGet('spu', md5(json_encode($condition))), true);
        }

        //数据读取
        try {
            $field = 'spu,lang,material_cat_no,qrcode,name,show_name,brand,keywords,exe_standard,'
                    . 'tech_paras,advantages,description,profile,principle,app_scope,properties,warranty,'
                    . 'supply_ability,source,source_detail,sku_count,recommend_flag,status,created_by,'
                    . 'created_at,updated_by,updated_at,checked_by,checked_at';
            $result = $this->field($field)->where($condition)->select();
            $data = array();
            if ($result) {
                $employee = new EmployeeModel();
                $checklogModel = new ProductCheckLogModel();
                $this->_setUserName($result, ['created_by', 'updated_by', 'checked_by']);
                foreach ($result as $item) {
                    //根据created_by，updated_by，checked_by获取名称   个人认为：为了名称查询多次库欠妥
                    // $createder = $employee->getInfoByCondition(array('id' => $item['created_by']), 'id,name,name_en');
//                    if ($createder && isset($createder[0])) {
//                        $item['created_by'] = $createder[0]['name'];
//                    }
//
//                    $updateder = $employee->getInfoByCondition(array('id' => $item['updated_by']), 'id,name,name_en');
//                    if ($updateder && isset($updateder[0])) {
//                        $item['updated_by'] = $updateder[0]['name'];
//                    }
//
//                    $checkeder = $employee->getInfoByCondition(array('id' => $item['checked_by']), 'id,name,name_en');
//                    if ($checkeder && isset($checkeder[0])) {
//                        $item['checked_by'] = $checkeder[0]['name'];
//                    }
                    if (!is_null(json_decode($item['brand'], true))) {
                        $brand = json_decode($item['brand'], true);
                        $item['brand'] = $brand;
                    }


                    $item['remark'] = $checklogModel->getlastRecord($item['spu'], $item['lang']);
                    //语言分组
                    $data[$item['lang']] = $item;
                }
//                redisHashSet('spu', md5(json_encode($condition)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return false;
        }
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setUserName(&$arr, $fileds) {
        if ($arr) {
            $employee_model = new EmployeeModel();
            $userids = [];
            $update_time = '';
            $update_by = '';
            $update_by_name = '';
            foreach ($arr as $key => $val) {
                foreach ($fileds as $filed) {
                    if (isset($val[$filed]) && $val[$filed]) {
                        $userids[] = $val[$filed];
                        if ($filed == 'updated_by' && empty($update_time)) {
                            $update_time = $val['updated_at'];
                            $update_by = $val['updated_by'];
                        } elseif ($filed == 'updated_by' && !empty($val['updated_at']) && $update_time < $val['updated_at']) {
                            $update_time = $val['updated_at'];
                            $update_by = $val['updated_by'];
                        }
                    }
                }
            }
            $usernames = $employee_model->getUserNamesByUserids($userids);
            foreach ($arr as $key => $val) {
                foreach ($fileds as $filed) {
                    if ($val[$filed] && isset($usernames[$val[$filed]])) {
                        $val[$filed . '_name'] = $usernames[$val[$filed]];
                    } else {
                        $val[$filed . '_name'] = '';
                    }
                    if ($filed == 'updated_by') {
                        $val['updated_at'] = $update_time;
                        $val['updated_by'] = $update_by;
                        $val['updated_by_name'] = isset($usernames[$update_by]) ? $usernames[$update_by] : '';
                    }
                }
                $arr[$key] = $val;
            }
        }
    }

    /**
     * 根据条件查询
     * @param array $condition
     * @param string $field
     * @return bool
     * @example: findByCondition(array('spu'=>'111','lang'=>'zh'),array('spu','status'));
     */
    public function findByCondition($condition = [], $field = '') {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }

        if (is_array($field)) {
            $field = implode(',', $field);
        } elseif (empty($field)) {
            $field = 'spu,lang,material_cat_no,qrcode,name,show_name,brand,keywords,exe_standard,tech_paras,advantages,description,profile,principle,app_scope,properties,warranty,supply_ability,source,source_detail,sku_count,recommend_flag,status,created_by,created_at,updated_by,updated_at,checked_by,checked_at';
        }
        try {
            $result = $this->field($field)->where($condition)->select();
            if ($result) {
                return $result;
            }
        } catch (Exception $e) {
            return false;
        }
        return array();
    }

    /**
     * 生成ｓｐｕ编码
     * @return string
     */
    public function createSpu() {
        $spu = randNumber(6);
        $condition = array(
            'spu' => $spu
        );
        $exit = $this->where($condition)->find();
        if ($exit) {
            $this->createSpu();
        }
        return $spu;
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
        if (empty($param) || empty($field)) {
            return array();
        }
        foreach ($field as $k => $item) {
            switch ($item[0]) {
                case 'required':
                    if ($param[$k] == '' || empty($param[$k])) {
                        jsonReturn('', '1000', 'Param ' . $k . ' Not null !');
                    }
                    break;
                case 'method':
                    if (!method_exists($item[1])) {
                        jsonReturn('', '404', 'Method ' . $item[1] . ' nont find !');
                    }
                    if (!call_user_func($item[1], $param[$k])) {
                        jsonReturn('', '1000', 'Param ' . $k . ' Validate failed !');
                    }
                    break;
            }
            $param[$k] = htmlspecialchars(trim($param[$k]));
            continue;
        }
        return $param;
    }

    /**
     * 验证语言
     * @param string $lang
     * @return bool
     */
    private function checkLang($lang = '') {
        if (!empty($lang) && in_array(strtolower($lang), array('zh', 'en', 'ru', 'es'))) {
            return true;
        }
        return false;
    }

    /**
     * 验证附件
     * 这里只验证图片附件是否为空
     * @param array $item 附件集
     * @param 空返回false,否则返回true
     */
    public function checkAttachImage($item) {
        if (empty($item))
            return false;
        foreach ($item as $r) {
            if (in_array($r['attach_type'], array('SMALL_IMAGE', 'MIDDLE_IMAGE', 'BIG_IMAGE'))) {
                return true;
            }
            continue;
        }
        return false;
    }

    /*
     * 根据spus 获取SPU名称
     */

    public function getNamesBySpus($spus, $lang = 'zh') {
        $where = [];
        if (is_array($spus) && $spus) {
            $where['spu'] = ['in', $spus];
        } else {
            return [];
        }
        if (empty($lang)) {
            $where['lang'] = 'zh';
        } else {
            $where['lang'] = $lang;
        }
        $result = $this->where($where)->field('name,spu')->select();
        if ($result) {
            $data = [];
            foreach ($result as $item) {
                $data[$item['spu']] = $item['name'];
            }
            return $data;
        } else {
            return [];
        }
    }

    /**
     * 导出模板
     */
    public function exportTemp() {
        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();    //当前sheet
        $objSheet->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
        //$objSheet->getStyle("A1:K1")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('ccffff');
        $objSheet->getStyle("A1:K1")
                ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objSheet->getStyle("A1:K1")->getFont()->setSize(11)->setBold(true);    //粗体
        //$objSheet->getStyle("A1:K1")->getFill()->getStartColor()->setARGB('FF808080');
        //$objSheet->getRowDimension("1")->setRowHeight(25);    //设置行高
        $column_width_25 = ["B", "C", "D", "E", "F", "G", "H", "I", "J", "K"];
        foreach ($column_width_25 as $column) {
            $objSheet->getColumnDimension($column)->setWidth(25);
        }
        $objSheet->setTitle('产品模板'); //设置报价单标题
        $objSheet->setCellValue("A1", "序号");
        $objSheet->setCellValue("B1", "产品编码");
        $objSheet->setCellValue("C1", "产品名称");
        $objSheet->setCellValue("D1", "展示名称");
        $objSheet->setCellValue("E1", "产品组");
        $objSheet->setCellValue("F1", "产品品牌");
        $objSheet->setCellValue("G1", "产品介绍");    //对应产品优势（李志确认）
        $objSheet->setCellValue("H1", "技术参数");
        $objSheet->setCellValue("I1", "执行标准");
        $objSheet->setCellValue("J1", "质保期");
        $objSheet->setCellValue("K1", "关键字");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        $localDir = ExcelHelperTrait::createExcelToLocalDir($objWriter, 'spu template_' . '.xls');
        return $localDir ? $localDir : '';
    }

    /**
     * 产品导出
     * @return string
     */
    public function export() {
        $lang_ary = array('zh','en','es','ru');
        $userInfo = getLoinInfo();
        $pModel = new ProductModel();

        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($userInfo['name']);
        $objPHPExcel->getProperties()->setTitle("Product List");
        $objPHPExcel->getProperties()->setLastModifiedBy($userInfo['name']);
        foreach($lang_ary as $key => $lang){
            $objPHPExcel->createSheet();    //创建工作表
            $objPHPExcel->setActiveSheetIndex($key);    //设置工作表
            $objSheet = $objPHPExcel->getActiveSheet();    //当前sheet
            $objSheet->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
            $objSheet->getStyle("A1:K1")
                ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objSheet->getStyle("A1:K1")->getFont()->setSize(11)->setBold(true);    //粗体
            $column_width_25 = ["B", "C", "D", "E", "F", "G", "H", "I", "J", "K"];
            foreach ($column_width_25 as $column) {
                $objSheet->getColumnDimension($column)->setWidth(25);
            }

            $objSheet->setTitle($lang);
            $objSheet->setCellValue("A1", "序号");
            $objSheet->setCellValue("B1", "产品编码");
            $objSheet->setCellValue("C1", "产品名称");
            $objSheet->setCellValue("D1", "展示名称");
            $objSheet->setCellValue("E1", "产品组");
            $objSheet->setCellValue("F1", "产品品牌");
            $objSheet->setCellValue("G1", "产品介绍");    //对应产品优势（李志确认）
            $objSheet->setCellValue("H1", "技术参数");
            $objSheet->setCellValue("I1", "执行标准");
            $objSheet->setCellValue("J1", "质保期");
            $objSheet->setCellValue("K1", "关键字");
            $objSheet->setCellValue("L1", "审核状态");

            $i = 0;    //用来控制分页查询
            $j = 2;    //excel控制输出
            $length = 20;
            $condition = array('lang' => $lang);
            do {
                $result = $pModel->getList($condition, '', $i * $length, $length);
                if ($result) {
                    foreach ($result as $r) {
                        $objSheet->setCellValue("A" . $j, $j - 1, PHPExcel_Cell_DataType::TYPE_STRING);
                        $objSheet->setCellValue("B" . $j, $r['spu'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $objSheet->setCellValue("C" . $j, $r['name']);
                        $objSheet->setCellValue("D" . $j, $r['show_name']);
                        $objSheet->setCellValue("E" . $j, $r['material_cat_no']);
                        $brand_ary = json_decode($r['brand'], true);
                        $objSheet->setCellValue("F" . $j, (is_array($brand_ary) && isset($brand_ary['name'])) ? $brand_ary['name'] : $r['brand']);
                        $objSheet->setCellValue("G" . $j, $r['advantages']);
                        $objSheet->setCellValue("H" . $j, $r['tech_paras']);
                        $objSheet->setCellValue("I" . $j, $r['exe_standard']);
                        $objSheet->setCellValue("J" . $j, $r['warranty']);
                        $objSheet->setCellValue("K" . $j, $r['keywords']);
                        $status = '';
                        switch($r['status']){
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
                        }
                        $objSheet->setCellValue("L" . $j, $status == '' ? $r['status'] : $status);
                        $j++;
                    }
                }
                $i++;
            } while (count($result) >= $length);
        }

        //保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        $localDir = ExcelHelperTrait::createExcelToLocalDir($objWriter, 'Product_'.time() . '.xls');

        return $localDir ? $localDir : '';
    }

    /**
     * 导入
     * @param $data   注意这是excel模板数据
     * @param $lang
     */
    public function import($data = []) {
        if (empty($data)) {
            return false;
        }

        $userInfo = getLoinInfo();
        $brandModel = new BrandModel();

        $this->startTrans();
        try {
            $spu = $this->createSpu();    //生成spu
            foreach ($data as $xls) {
                if (empty($xls['url']) || empty($xls['lang'])) {
                    continue;
                }

                //下载到本地临时文件
                //$localFile = ExcelHelperTrait::download2local($xls['url']);
                $localFile = MYPATH . '/public/tmp/1504073110.xls';
                $data = ExcelHelperTrait::ready2import($localFile);
                array_shift($data);

                if (empty($data) || empty($r = $data[0])) {
                    jsonReturn('', ErrorMsg::FAILED, '语言：' . $xls['lang'] . ' 无可导入数据');
                }

                $data_tmp = [];
                $data_tmp['spu'] = $spu;    //生成spu
                $data_tmp['lang'] = $xls['lang'];
                $data_tmp['name'] = $r[2];    //名称
                $data_tmp['show_name'] = $r[3];    //展示名称
                //$catNo = $mcatModel->getCatNoByName($r[5] , 'eq');
                //$data_tmp['material_cat_no'] = ($catNo && $catNo[0]['level_no']==3) ? $catNo[0]['cat_no'] : null;    //物料分类
                $data_tmp['material_cat_no'] = $r[4];    //物料分类
                //品牌
                $condition_brand = array(
                    'brand' => array('like', '%' . $r[5] . '%')
                );
                $brand_id = $brandModel->Exist($condition_brand);
                $data_tmp['brand'] = $brand_id ? json_encode(array('id' => $brand_id, 'name' => $r[5]), JSON_UNESCAPED_UNICODE) : null;    //品牌

                /**
                 * 根据lang 品牌查询name是否存在
                 */
                $condition = array(
                    'name' => $data_tmp['name'],
                    'lang' => $xls['lang'],
                    'brand' => array('like', '%' . $r[5] . '%'),
                );
                $exist = $this->field('id')->where($condition)->find();
                if ($exist) {
                    $this->rollback();
                    jsonReturn('语言：' . $xls['lang'] . ' 品牌：' . $r[5] . '下已存在[' . $data_tmp['name'] . ']', ErrorMsg::FAILED, '语言：' . $xls['lang'] . ' 品牌：' . $r[5] . '下已存在[' . $data_tmp['name'] . ']');
                }

                $data_tmp['advantages'] = $r[6];
                $data_tmp['tech_paras'] = $r[7];
                $data_tmp['exe_standard'] = $r[8];
                $data_tmp['warranty'] = $r[9];
                $data_tmp['keywords'] = $r[10];
                $data_tmp['source'] = 'ERUI';
                $data_tmp['source_detail'] = 'Excel批量导入';
                $data_tmp['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                $data_tmp['created_at'] = date('Y-m-d H:i:s');
                $data_tmp['status'] = $this::STATUS_VALID;
                $insert = $this->add($this->create($data_tmp));
                if (!$insert) {
                    $this->rollback();
                    jsonReturn($xls['lang'] . '导入有误，请稍后重试', ErrorMsg::FAILED, $xls['lang'] . '导入有误，请稍后重试');
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
     * 压缩导入
     * @param string $url
     * @return array|bool
     */
    public function zipImport($url = '') {
        if (empty($url)) {
            return false;
        }

        //下载到本地临时文件
        //$localFile = ExcelHelperTrait::download2local($url);
        $localFile = MYPATH . '/public/tmp/tmp.zip';

        $pathInfo = ( pathinfo($localFile) );
        if (strtolower($pathInfo['extension']) != 'zip') {
            jsonReturn('只支持zip格式', ErrorMsg::FAILED);
        };

        $sucess = $sucess_lang = 0;    //记录成功数
        $failds = [];   //记录失败项与错误
        try {
            $zip = new ZipArchive();
            $res = $zip->open($localFile);
            if ($res === true) {
                $tmpDir = MYPATH . '/public/tmp/' . $pathInfo['filename'];
                if ($zip->extractTo($tmpDir)) {    //解压缩到目录
                    $userInfo = getLoinInfo();
                    $productModel = new ProductModel();
                    $brandModel = new BrandModel();

                    $handle = opendir($tmpDir);
                    while ($f = readdir($handle)) {    //遍历spu目录层
                        if ($f != "." && $f != "..") {
                            $dir_spu = $tmpDir . '/' . $f;
                            if (is_dir($dir_spu)) {
                                $spu = $productModel->createSpu();    //生成spu
                                $handle2 = opendir($dir_spu);

                                $bool_spu = false;
                                $this->startTrans();
                                $sucess_lang_tmp = 0;
                                while ($xls = readdir($handle2)) {    //遍历excel
                                    if ($xls == '.' || $xls == '..') {
                                        continue;
                                    }
                                    if (is_file($dir_spu . '/' . $xls)) {
                                        $xlsFile = $dir_spu . '/' . $xls;
                                        $lang = strtolower(pathinfo($xls, PATHINFO_FILENAME));
                                        if (!in_array($lang, array('zh', 'en', 'es', 'ru'))) {
                                            $failds[] = array('item' => $f, 'hint' => 'excel文件请以语言（zh,en,es,ru）加.xls命名');
                                            $this->rollback();
                                            $bool_spu = false;
                                            $sucess_lang_tmp = 0;
                                            Log::write($f . '下的excel文件请以语言（zh,en,es,ru）加.xls命名', Log::INFO);
                                            break;
                                        }

                                        $data = ExcelHelperTrait::ready2import($xlsFile);    //读取excel信息
                                        array_shift($data);
                                        if (empty($data) || empty($r = $data[0])) {
                                            continue;
                                        }

                                        $data_tmp = [];
                                        $data_tmp['spu'] = $spu;    //生成spu
                                        $data_tmp['lang'] = $lang;
                                        $data_tmp['name'] = $r[2];    //名称
                                        $data_tmp['show_name'] = $r[3];    //展示名称
                                        //$catNo = $mcatModel->getCatNoByName($r[5] , 'eq');
                                        //$data_tmp['material_cat_no'] = ($catNo && $catNo[0]['level_no']==3) ? $catNo[0]['cat_no'] : null;    //物料分类
                                        $data_tmp['material_cat_no'] = $r[4];    //物料分类
                                        //品牌
                                        $condition_brand = array(
                                            'brand' => array('like', '%' . $r[5] . '%')
                                        );
                                        $brand_id = $brandModel->Exist($condition_brand);
                                        $data_tmp['brand'] = $brand_id ? json_encode(array('id' => $brand_id, 'name' => $r[5]), JSON_UNESCAPED_UNICODE) : null;    //品牌

                                        /**
                                         * 根据lang 品牌查询name是否存在
                                         */
                                        $condition = array(
                                            'name' => $data_tmp['name'],
                                            'lang' => $lang,
                                            'brand' => array('like', '%' . $r[5] . '%'),
                                        );
                                        $exist = $this->field('id')->where($condition)->find();
                                        if ($exist) {
                                            $failds[] = array('item' => $f, 'hint' => '语言：' . $lang . ' 品牌：' . $r[5] . '下已存在' . $data_tmp['name']);
                                            $this->rollback();
                                            $bool_spu = false;
                                            $sucess_lang_tmp = 0;
                                            Log::write($f . '下，语言：' . $lang . ' 品牌：' . $r[5] . '下已存在' . $data_tmp['name'], Log::INFO);
                                            break;
                                        }

                                        $data_tmp['advantages'] = $r[6];
                                        $data_tmp['tech_paras'] = $r[7];
                                        $data_tmp['exe_standard'] = $r[8];
                                        $data_tmp['warranty'] = $r[9];
                                        $data_tmp['keywords'] = $r[10];
                                        $data_tmp['source'] = 'ERUI';
                                        $data_tmp['source_detail'] = 'Excel批量导入';
                                        $data_tmp['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                                        $data_tmp['created_at'] = date('Y-m-d H:i:s');
                                        $data_tmp['status'] = $this::STATUS_VALID;
                                        $insert = $this->add($this->create($data_tmp));
                                        if (!$insert) {
                                            $failds[] = array('item' => $f, 'hint' => $lang . '导入失败，请检查信息后重试');
                                            $this->rollback();
                                            $bool_spu = false;
                                            $sucess_lang_tmp = 0;
                                            Log::write($f . '下，' . $lang . '导入失败，请检查信息后重试', Log::INFO);
                                            break;
                                        } else {
                                            $bool_spu = true;
                                            $sucess_lang_tmp++;
                                        }
                                    } else {
                                        continue;
                                    }
                                }
                                if ($bool_spu) {
                                    $sucess++;
                                    $sucess_lang = $sucess_lang + $sucess_lang_tmp;
                                    $this->commit();
                                }
                            } else {
                                continue;
                            }
                        }
                    }
                    return array(
                        'sucess' => $sucess,
                        'succes_lang' => $sucess_lang,
                        'failds' => $failds
                    );
                } else {
                    Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'failed, code:解压失败', Log::ERR);
                }
                $zip->close();
            } else {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'failed, code:' . $res, Log::ERR);
                return false;
            }
        } catch (Exception $e) {
            $this->rollback();
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . $e->getMessage(), Log::ERR);
            return array(
                'sucess' => $sucess,
                'succes_lang' => $sucess_lang,
                'failds' => $failds
            );
        }
    }

}
