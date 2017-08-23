<?php

/**
 * 产品.
 * User: linkai
 * Date: 2017/6/15
 * Time: 18:52
 */
class ProductModel extends PublicModel {

    const STATUS_NORMAL = 'NORMAL'; //发布
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
        'brand' => array('required'),
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
                            $data['brand'] = json_encode($r);
                            break;
                        }
                    }
                }
            } else {
                $data['brand'] = is_array($input['brand']) ? json_encode($input['brand']) : $input['brand'];
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
                    if (empty($data)) {
                        continue;
                    }

                    //除暂存外都进行校验     这里存在暂存重复加的问题，此问题暂时预留。
                    $input['status'] = (isset($input['status']) && in_array(strtoupper($input['status']), array('DRAFT', 'TEST', 'CHECKING'))) ? strtoupper($input['status']) : 'DRAFT';
                    if ($input['status'] != 'DRAFT') {
                        //字段校验
                        $this->checkParam($data, $this->field);

                        $exist_condition = array(//添加时判断同一语言，name,meterial_cat_no是否存在
                            'lang' => $key,
                            'name' => $data['name'],
                            'status' => array('neq', 'DRAFT')
                        );
                        if (isset($input['spu'])) {
                            $exist_condition['spu'] = array('neq', $spu);
                        }
                        $exist = $this->where($exist_condition)->find();
                        if ($exist) {
                            jsonReturn('', ErrorMsg::EXIST);
                        }
                    }
                    $data['status'] = $input['status'];

                    $exist_check = $this->field('id')->where(array('spu' => $spu, 'lang' => $key))->find();
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
                        if (!isset($input['spu'])) {
                            if (!$this->checkAttachImage($item)) {
                                jsonReturn('', '1000', '产品图不能为空');
                            }
                        }
                        foreach ($item as $atta) {
                            $data = array(
                                'spu' => $spu,
                                'attach_type' => isset($atta['attach_type']) ? $atta['attach_type'] : '',
                                'attach_name' => isset($atta['attach_name']) ? $atta['attach_name'] : '',
                                'attach_url' => isset($atta['attach_url']) ? $atta['attach_url'] : '',
                                'default_flag' => (isset($atta['default_flag']) && $atta['default_flag']) ? 'Y' : 'N',
                            );
                            if (isset($input['spu'])) {    //修改
                                $data['id'] = isset($atta['id']) ? $atta['id'] : '';
                            }
                            if (empty($data['attach_url'])) {
                                continue;
                            }
                            $pattach = new ProductAttachModel();
                            $attach = $pattach->addAttach($data);
                            if (!$attach) {
                                $this->rollback();
                                return false;
                            }
                        }
                    }
                } else {
                    continue;
                }
            }
            $this->commit();
            if ($spu) {
                $langs = ['en', 'zh', 'es', 'ru'];
                foreach ($langs as $lang) {
                    $esproductmodel = new EsProductModel();
                    $esproductmodel->create_data($spu, $lang);
                }
            }
            return $spu;
        } catch (Exception $e) {
            p($e);
            die;
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
                $model = new EsProductModel();
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
                            /**
                             * 更新ES
                             */
                            $model->changestatus($r, $status, $lang);
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
                        /**
                         * 更新ES
                         */
                        $model->changestatus($spu, $status, $lang);
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
                $model = new EsProductModel();
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
                            $goodsModel->where($where)->save(array('deleted_flag' => self::DELETE_Y));

                            /**
                             * 更新ES
                             */
                            $model->delete_data($r, $lang);
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
                        $goodsModel->where($where)->save(array('deleted_flag' => self::DELETE_Y));

                        /**
                         * 更新ES
                         */
                        $model->delete_data($spu, $lang);
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
    public function getList() {

    }

    /**
     * spu详情
     * @param string $spu    spu编码
     * @param string $lang    语言
     * return array
     */
    public function getInfo($spu = '', $lang = '', $status = '') {
        if (empty($spu)) {
            return array();
        }

        $condition = array(
            'spu' => $spu,
                //'deleted_flag' => self::DELETE_N,
        );
        if (!empty($lang)) {
            $condition['lang'] = $lang;
        }
        if (!empty($status)) {
            $condition['status'] = $status;
        }

        //读取redis缓存
        if (redisHashExist('spu', md5(json_encode($condition)))) {
            return json_decode(redisHashGet('spu', md5(json_encode($condition))), true);
        }

        //数据读取
        try {
            $field = 'spu,lang,material_cat_no,qrcode,name,show_name,brand,'
                    . 'keywords,exe_standard,tech_paras,advantages,description,'
                    . 'profile,principle,app_scope,properties,warranty,supply_ability,'
                    . 'source,source_detail,sku_count,recommend_flag,status,created_by,'
                    . 'created_at,updated_by,updated_at,checked_by,checked_at,target_market';
            $result = $this->field($field)->where($condition)->select();
            $data = array();
            if ($result) {
                $employee = new EmployeeModel();
                foreach ($result as $item) {
                    //根据created_by，updated_by，checked_by获取名称   个人认为：为了名称查询多次库欠妥
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

                    //语言分组
                    $data[$item['lang']] = $item;
                }
                redisHashSet('spu', md5(json_encode($condition)), json_encode($data));
            }
            return $data;
        } catch (Exception $e) {
            return false;
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

}
