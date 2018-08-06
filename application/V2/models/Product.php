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

    protected $lang_ary = array(
        'zh' => "中文",
        'en' => "英文",
        'ru' => "俄文",
        'es' => "西文"
    );
//定义校验规则
    protected $field = array(
//'lang' => array('method','checkLang','语言'),
        'material_cat_no' => array('required', '', '请输入物料分类'),
        'name' => array('required', '', '请输入名称'),
        'brand' => array('required', '', '请输入品牌'),
            //'description' => array('required', '', '请输入详情介绍'),
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
            $data['name'] = $input['name'];
        } elseif ($type == 'INSERT') {
            $data['name'] = '';
        }

//展示名称
        if (isset($input['show_name'])) {
            $data['show_name'] = $input['show_name'];
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
                            $brand_ary = array(
                                'name' => $r['name'],
                                'style' => isset($r['style']) ? $r['style'] : 'TEXT',
                                'label' => isset($r['label']) ? $r['label'] : $r['name'],
                                'logo' => isset($r['logo']) ? $r['logo'] : '',
                            );
                            ksort($brand_ary);
                            $data['brand'] = json_encode($brand_ary, JSON_UNESCAPED_UNICODE);
                            break;
                        }
                    }
                }
            } else {
                if (is_array($input['brand'])) {
                    ksort($input['brand']);
                    $data['brand'] = json_encode($input['brand'], JSON_UNESCAPED_UNICODE);
                } elseif (!empty($input['brand'])) {
                    $brand_ary = array(
                        'name' => $input['brand'],
                        'style' => 'TEXT',
                        'label' => $input['brand'],
                        'logo' => '',
                    );
                    ksort($brand_ary);
                    $data['brand'] = json_encode($brand_ary, JSON_UNESCAPED_UNICODE);
                } else {
                    $data['brand'] = '';
                }
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
            $data['warranty'] = trim($input['warranty']);
        } elseif ($type == 'INSERT') {
            $data['warranty'] = '';
        }

//供应能力
        if (isset($input['supply_ability'])) {
            $data['supply_ability'] = trim($input['supply_ability']);
        } elseif ($type == 'INSERT') {
            $data['supply_ability'] = '';
        }
        if (isset($input['bizline_id'])) {
            $data['bizline_id'] = intval($input['bizline_id']);
        } elseif ($type == 'INSERT') {
            $data['bizline_id'] = 0;
        }
        if ($type == 'INSERT') {
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
            jsonReturn('', ErrorMsg::ERROR_PARAM);
        }

//检测语言是否规范
        if (isset($input['activename']) && !empty($input['activename']) && !in_array($input['activename'], ['zh', 'en', 'es', 'ru'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM, '语言有误');
        }

        $datas = [];
//检测名称（必填）
        if (!isset($input['activename']) || empty($input['activename'])) {
            if (empty($input['zh']['name']) && empty($input['en']['name']) && empty($input['es']['name']) && empty($input['ru']['name'])) {
                jsonReturn('', ErrorMsg::FAILED, '请输入名称');
            }
            foreach (['zh', 'en', 'es', 'ru'] as $lang) {
                if (isset($input[$lang])) {
                    $datas[$lang] = $input[$lang];
                }
            }
        } else {
            if (empty($input[$input['activename']]['name'])) {
                jsonReturn('', ErrorMsg::FAILED, '请输入名称');
            }
            $datas[$input['activename']] = $input[$input['activename']];
        }

//检测物料分类（必填）
        $material_cat_no = (isset($input['material_cat_no']) && !empty($input['material_cat_no'])) ? $input['material_cat_no'] : ((isset($input['zh']['material_cat_no']) && !empty($input['zh']['material_cat_no'])) ? $input['zh']['material_cat_no'] : ((isset($input['en']['material_cat_no']) && !empty($input['en']['material_cat_no'])) ? $input['en']['material_cat_no'] : ((isset($input['es']['material_cat_no']) && !empty($input['es']['material_cat_no'])) ? $input['es']['material_cat_no'] : ((isset($input['ru']['material_cat_no']) && !empty($input['ru']['material_cat_no'])) ? $input['ru']['material_cat_no'] : ''))));
        if (empty($material_cat_no)) {
            jsonReturn('', ErrorMsg::FAILED, '请输入物料分类');
        } else {
            $mcatModel = new MaterialCatModel();
            $mexist = $mcatModel->info($material_cat_no);
            if (!$mexist) {
                jsonReturn('', ErrorMsg::FAILED, '物料分类编码不存在');
            }
        }

//产品线
        $bizline_id = (isset($input['bizline_id']) && !empty($input['bizline_id'])) ? trim($input['bizline_id']) : null;

        $attachs = $input['attachs'];    //附件
        $fp = fopen(MYPATH . '/public/file/spuedit.lock', 'r');
        if (flock($fp, LOCK_EX | LOCK_NB)) {
            $spu = ( isset($input['spu']) && !empty($input['spu']) ) ? trim($input['spu']) : $this->createSpu($material_cat_no); //不存在生产spu
            if (empty($spu) || $spu === false) {
                flock($fp, LOCK_UN);
                fclose($fp);
                jsonReturn('', ErrorMsg::FAILED, '生成SPU编码失败');
            }

            $this->startTrans();
            try {
                $userInfo = getLoinInfo(); //获取当前用户信息
                foreach ($datas as $lang => $item) {
                    $item['status'] = (isset($item['status']) && !empty($item['status'])) ? $item['status'] : ((isset($input['status']) && !empty($input['status'])) ? $input['status'] : 'DRAFT');
                    $data = $this->getData($item, isset($input['spu']) ? 'UPDATE' : 'INSERT', $lang);
                    if (empty($data) || empty($data['name'])) {
                        continue;
                    }
                    $data['lang'] = $lang;
                    if (empty($data['material_cat_no'])) {
                        $data['material_cat_no'] = $material_cat_no;
                    }
//严重展示名称必须包含产品名称
                    if (!empty($data['show_name'])) {
                        if (!strstr($data['show_name'], $data['name'])) {
                            $this->rollback();
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            jsonReturn('', ErrorMsg::FAILED, '展示名称必须包含产品名称');
                        }
                    }
                    $data['bizline_id'] = $bizline_id;
                    $this->checkParam($data, $this->field);     //字段校验
                    if ($lang == 'en') {
                        if (!empty($data['name']) && haveZh($data['name'])) {
                            $this->rollback();
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            jsonReturn('', ErrorMsg::FAILED, '英文名称中含有中文，请检查');
                        }
                        if (!empty($data['show_name']) && haveZh($data['show_name'])) {
                            $this->rollback();
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            jsonReturn('', ErrorMsg::FAILED, '英文展示名称中含有中文，请检查');
                        }
                        if (!empty($data['exe_standard']) && haveZh($data['exe_standard'])) {
                            $this->rollback();
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            jsonReturn('', ErrorMsg::FAILED, '英文执行标准中含有中文，请检查');
                        }
                        if (!empty($data['description']) && haveZh($data['description'])) {
                            $this->rollback();
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            jsonReturn('', ErrorMsg::FAILED, '英文详情介绍中含有中文，请检查');
                        }
                        if (!empty($data['tech_paras']) && haveZh($data['tech_paras'])) {
                            $this->rollback();
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            jsonReturn('', ErrorMsg::FAILED, '英文技术参数中含有中文，请检查');
                        }
                        if (!empty($data['warranty']) && haveZh($data['warranty'])) {
                            $this->rollback();
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            jsonReturn('', ErrorMsg::FAILED, '英文质保期中含有中文，请检查');
                        }
                    }

//非暂存进行下校验
//if ($item['status'] != 'DRAFT') {
                    $exist_condition = array(//添加时判断同一语言,meterial_cat_no,brand下name是否存在
                        'lang' => $lang,
                        'name' => $data['name'],
                        //'material_cat_no' => $data['material_cat_no'],
//'brand' => $data['brand'],
                        'deleted_flag' => 'N',
                            //'status' => array('neq', 'DRAFT')
                    );
                    if (isset($input['spu'])) {
                        $exist_condition['spu'] = array('neq', $spu);
                    }
                    $exist = $this->field('id,brand')->where($exist_condition)->select();
                    if ($exist) {
                        $brand_ary = json_decode($data['brand'], true);
                        foreach ($exist as $r) {
                            $brand_exist = json_decode($r['brand'], true);
                            if ($brand_ary['name'] == $brand_exist['name']) {
                                $this->rollback();
                                flock($fp, LOCK_UN);
                                fclose($fp);
                                jsonReturn('', ErrorMsg::EXIST);
                            }
                        }
                    }
//}
                    $data['status'] = $item['status'] ? $item['status'] : self::STATUS_DRAFT;
                    $exist_check = $this->field('id')->where(array('spu' => $spu, 'lang' => $lang, 'deleted_flag' => 'N'))->find();
                    if ($exist_check) {    //修改
                        $data['updated_by'] = isset($userInfo['id']) ? $userInfo['id'] : null; //修改人
                        $data['updated_at'] = date('Y-m-d H:i:s', time());
                        $data['deleted_flag'] = 'N';
//$result = $this->where(array('spu' => $spu, 'lang' => $lang))->save($data);
                        $result = $this->where(array('id' => $exist_check['id']))->save($data);
                        if (!$result) {
                            $this->rollback();
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            return false;
                        }
                    } else {    //添加
                        $data['qrcode'] = createQrcode('/product/info/' . $spu);    //生成spu二维码  注意模块    冗余字段这块还要看后期需求是否分语言
                        $data['spu'] = $spu;
                        $data['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : null; //创建人
                        $data['created_at'] = date('Y-m-d H:i:s', time());
                        $result = $this->add($data);
                        if (!$result) {
                            $this->rollback();
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            return false;
                        }
                    }
                }//end foreach
//处理附件
                $pattach = new ProductAttachModel();
                $update_condition = array(
                    'spu' => $spu
                );
                $pattach->where($update_condition)->save(array('status' => $pattach::STATUS_DELETED, 'deleted_flag' => $pattach::DELETED_Y));
                if ($attachs) {
                    foreach ($attachs as $atta) {
                        if (!isset($atta['attach_url']) || empty($atta['attach_url'])) {
                            continue;
                        }
                        $data_atta = array(
                            'spu' => $spu,
                            'attach_type' => isset($atta['attach_type']) ? $atta['attach_type'] : '',
                            'attach_name' => isset($atta['attach_name']) ? $atta['attach_name'] : $atta['attach_url'],
                            'attach_url' => isset($atta['attach_url']) ? $atta['attach_url'] : '',
                            'default_flag' => (isset($atta['default_flag']) && $atta['default_flag']) ? 'Y' : 'N',
                        );

                        if (isset($atta['id']) && $atta['id']) {
                            $data_atta['id'] = $atta['id'];
                        }

                        $attach = $pattach->addAttach($data_atta);
                        if (!$attach) {
                            $this->rollback();
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            return false;
                        }
                    }//end foreach
                } else {
                    if (!isset($input['activename']) || empty($input['activename'])) {
                        if (isset($input['status']) && $input['status'] != 'DRAFT') {
                            $this->rollback();
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            jsonReturn('', '1000', '请上传产品图');
                        }
                    } elseif (isset($datas[$input['activename']]['status']) && !empty($datas[$input['activename']]['status']) && $datas[$input['activename']]['status'] != 'DRAFT') {
                        $this->rollback();
                        flock($fp, LOCK_UN);
                        fclose($fp);
                        jsonReturn('', '1000', '请上传产品图');
                    }
                }
                $this->commit();
                flock($fp, LOCK_UN);
                fclose($fp);
                return $spu;
            } catch (Exception $e) {
                $this->rollback();
                flock($fp, LOCK_UN);
                fclose($fp);
                return false;
            }
            flock($fp, LOCK_UN);
        }
        fclose($fp);
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
//$this->startTrans();
            try {
                $spuary = [];
                $faild_ary = [];    //记录失败的
                $userInfo = getLoinInfo();
                if (is_array($spu)) {
                    foreach ($spu as $r) {
                        $where = array(
                            'spu' => $r,
                        );
                        if (!empty($lang)) {
                            $where['lang'] = $lang;
                        }
                        $updata = [
                            'status' => $status,
                            'updated_at' => date('Y-m-d H:i:s'),
                            'updated_by' => $userInfo['id']
                        ];

                        /** 报审走报审验证 */
                        /*
                          if ($status == self::STATUS_CHECKING || $status == self::STATUS_VALID) {
                          $applyInfo = $this->applyExamine($r, $lang);
                          if ($applyInfo['code'] === false) {
                          $faild_ary[][$r] = $applyInfo['message'];
                          continue;
                          }
                          }
                         */
                        if ($status == self::STATUS_VALID) {
//spu审核 只要有一条SKU审核通过，即可允许审核SPU
                            $validSku = $this->checkValidSku($r, $lang);
                            if (!$validSku) {
                                return [
                                    'code' => -104,
                                    'message' => 'SPU至少要有一个SKU审核通过才能审核'
                                ];
                            }
                        }
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
                            if ($status == self::STATUS_CHECKING) {
                                $where['status'] = self::STATUS_CHECKING;
                                $checkInfo = $this->field('id')->where($where)->select();
                                if ($checkInfo) {
                                    $spuary[] = array('spu' => $r, 'lang' => $lang, 'remarks' => $remark);
                                } else {
                                    $faild_ary[][$r] = '失败';
                                    continue;
                                }
                            } else {
                                $faild_ary[][$r] = '失败';
                                continue;
                            }
                        }
                    }
                } else {
                    $where = array(
                        'spu' => $spu,
                    );
                    if (!empty($lang)) {
                        $where['lang'] = $lang;
                    }

                    $updata = [
                        'status' => $status,
                        'updated_at' => date('Y-m-d H:i:s'),
                        'updated_by' => $userInfo['id']
                    ];

                    /** 报审走报审验证 */
                    /*
                      if ($status == self::STATUS_CHECKING || $status == self::STATUS_VALID) {
                      $applyInfo = $this->applyExamine($spu, $lang);
                      if ($applyInfo['code'] === false) {
                      $faild_ary[][$spu] = $applyInfo['message'];
                      return array(0, $faild_ary);
                      }
                      }
                     */

                    if ($status == self::STATUS_VALID) {
//spu审核 只要有一条SKU审核通过，即可允许审核SPU
                        $validSku = $this->checkValidSku($spu, $lang);
                        if (!$validSku) {
                            return [
                                'code' => -104,
                                'message' => 'SPU至少要有一个SKU审核通过才能审核'
                            ];
                        }
                    }

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
                        if ($status == self::STATUS_CHECKING) {
                            $where['status'] = self::STATUS_CHECKING;
                            $checkInfo = $this->field('id')->where($where)->select();
                            if ($checkInfo) {
                                $spuary[] = array('spu' => $spu, 'lang' => $lang, 'remarks' => $remark);
                            } else {
                                $faild_ary[][$spu] = '失败';
                                return $faild_ary;
                            }
                        } else {
                            $faild_ary[][$spu] = '失败';
                            return $faild_ary;
                        }
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

//$this->commit();
                return array(count($spuary), $faild_ary);
            } catch (Exception $e) {
//$this->rollback();
                return false;
            }
        }
        return false;
    }

    /**
     * 报审验证
     * 验证名称+品牌是否库中存在
     * 验证图片
     * @param string $spu
     * @param string $lang
     */
    public function applyExamine($spu = '', $lang = '') {
        if (empty($spu)) {
            return ['code' => false, 'message' => 'system error: spu is null'];
        }
        $condition = ['spu' => $spu];
        if (!empty($lang)) {
            $condition['lang'] = $lang;
        }
        $result = $this->field('spu,lang,name,brand,material_cat_no')->where($condition)->select();
        if ($result) {
            $attachModel = new ProductAttachModel();
            foreach ($result as $key => $item) {
                if (empty(trim($item['name']))) {    //检测名称
                    return ['code' => false, 'message' => $item['lang'] . '名称不能为空'];
                }

                if (empty(trim($item['brand']))) {    //检测品牌
                    return ['code' => false, 'message' => $item['lang'] . '品牌不能为空'];
                }


                $condition_new = ['lang' => $item['lang'], 'name' => $item['name'], 'deleted_flag' => self::DELETE_N, 'status' => ['neq', self::STATUS_DRAFT], 'spu' => ['neq', $item['spu']]];
                $exist = $this->field('id,brand')->where($condition_new)->select();
                if ($exist) {
                    $brand_ary = json_decode($item['brand'], true);
                    foreach ($exist as $r) {
                        $brand_exist = json_decode($r['brand'], true);
                        if ($brand_ary['name'] == $brand_exist['name']) {
                            return ['code' => false, 'message' => $item['lang'] . '已存在'];
                        }
                    }
                }
            }

//检测图片
            $condition_attach = ['spu' => $spu, 'deleted_flag' => $attachModel::DELETED_N, 'status' => $attachModel::STATUS_VALID];
            $find = $attachModel->field('id')->where($condition_attach)->find();
            if (!$find) {
                return ['code' => false, 'message' => '无图片'];
            }
            return ['code' => true, 'message' => ''];
        } else {
            return ['code' => false, 'message' => 'system error: spu is null'];
        }
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
                            $product_supplier_model = new ProductSupplierModel();
                            $product_supplier_model->deleteSupplierBySpu($r);
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
                        $product_supplier_model = new ProductSupplierModel();
                        $product_supplier_model->deleteSupplierBySpu($spu);
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
    public function etList($condition = [], $field = '', $offset = 0, $length = 20) {
        $field = empty($field) ? 'lang,material_cat_no,spu,name,show_name,brand,keywords,exe_standard,tech_paras,advantages,description,profile,principle,app_scope,properties,warranty,status' : $field;
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
        /* if (redisHashExist('spu', md5(json_encode($condition)))) {
          return json_decode(redisHashGet('spu', md5(json_encode($condition))), true);
          } */
//数据读取
        try {
            $field = 'spu,lang,material_cat_no,qrcode,name,show_name,brand,keywords,exe_standard,'
                    . 'tech_paras,advantages,description,profile,principle,app_scope,properties,warranty,'
                    . 'supply_ability,source,source_detail,sku_count,recommend_flag,status,created_by,'
                    . 'created_at,updated_by,updated_at,checked_by,checked_at,bizline_id';
            $result = $this->field($field)->where($condition)->select();
            $data = array();
            if ($result) {
                $checklogModel = new ProductCheckLogModel();
                $this->_setUserName($result, ['created_by', 'updated_by', 'checked_by']);
                $bizlineModel = new BizlineModel();
                foreach ($result as $item) {
                    $bizline = '';    //产品组
                    if (!empty($item['bizline_id'])) {
                        $bizlineInfo = $bizlineModel->field('name,name_en')->where(array('id' => $item['bizline_id']))->find();
                        $bizline = $bizlineInfo ? ($bizlineInfo['name'] ? $bizlineInfo['name'] : $bizlineInfo['name_en']) : '';
                    }

                    $item['bizline'] = $bizline;
                    if (!is_null(json_decode($item['brand'], true))) {
                        $brand = json_decode($item['brand'], true);
                        $item['brand'] = $brand;
                    }

                    $item['remark'] = $checklogModel->getlastRecord($item['spu'], $item['lang']);
//语言分组
                    $data[$item['lang']] = $item;
                }
//redisHashSet('spu', md5(json_encode($condition)), json_encode($data));
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
     * SPU的编码规则为：6位物料分类编码 + 00 + 4位产品编码 + 0000
     * @return string
     */
    public function createSpu($material_cat_no = '', $spu = '') {
        if (empty($material_cat_no)) {
            return false;
        }

        if (!empty($spu)) {
            $condition = array('spu' => $spu);
            $result2 = $this->field('spu')->where($condition)->find();
            $lockFile = MYPATH . '/public/tmp/' . $spu . '.lock';
            if ($result2 || file_exists($lockFile)) {
                $code = substr($spu, (strlen($material_cat_no) + 2), 4);
                $code = intval($code) + 1;
                $spu = $material_cat_no . '00' . str_pad($code, 4, '0', STR_PAD_LEFT) . '0000';
                return $this->createSpu($material_cat_no, $spu);
            } else {
//目录
                $dirName = MYPATH . '/public/tmp';
                if (!is_dir($dirName)) {
                    if (!mkdir($dirName, 0777, true)) {
                        Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                    }
                }

//上锁
                $handle = fopen($lockFile, "w");
                if (!$handle) {
                    Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Lock Error: Lock file [' . MYPATH . '/public/tmp/' . $spu . '.lock' . '] create faild.', Log::ERR);
                } else {
                    fclose($handle);
                    return $spu;
                }
                return false;
            }
        } else {
            $condition = array(
                'material_cat_no' => $material_cat_no
            );
            $result = $this->field('spu')->where($condition)->order('spu DESC')->find();
            if ($result) {
                $code = substr($result['spu'], (strlen($material_cat_no) + 2), 4);
                $code = intval($code) + 1;
            } else {
                $code = 1;
            }
            $spu = $material_cat_no . '00' . str_pad($code, 4, '0', STR_PAD_LEFT) . '0000';
            return $this->createSpu($material_cat_no, $spu);
        }
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
                        jsonReturn('', '1000', $item[2]);
                    }
                    break;
                case 'method':
                    if (!method_exists($item[1])) {
                        jsonReturn('', '404', '验证方法：' . $item[1] . '未找到!');
                    }
                    if (!call_user_func($item[1], $param[$k])) {
                        jsonReturn('', '1000', $item[2] . '验证失败!');
                    }
                    break;
            }
            $param[$k] = trim($param[$k]);
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
        if (empty($item)) {
            return false;
        }
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
    public function exportTemp($input = []) {
        if (redisHashExist('spu', 'sputemplate')) {
            return json_decode(redisHashGet('spu', 'sputemplate'), true);
        } else {
            $localDir = $_SERVER['DOCUMENT_ROOT'] . "/public/file/spuTemplate.xls";
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
                    $data = array('url' => $fastDFSServer . $fileId['url'] . '?filename=' . $fileId['name'], 'name' => $fileId['name']);
                    redisHashSet('spu', 'sputemplate', json_encode($data));
                    return $data;
                }
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $localDir . ' 上传到FastDFS失败', Log::INFO);
                return false;
            } else {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Excel failed:' . $localDir . ' 模板生成失败', Log::INFO);
                return false;
            }
        }
    }

    /**
     * 导入
     * @param $data   注意这是excel模板数据
     * @param $lang
     */
    public function import($url = '', $lang = '', $process = '', $filename = '') {
        if (empty($url) || empty($lang)) {
            return false;
        }

        /** 返回导入进度start */
        $progress_key = md5(json_encode(array($url, $lang)));
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

        $userInfo = getLoinInfo();
        $es_product_model = new EsProductModel();
        $mcatModel = new MaterialCatModel();
        $brandModel = new BrandModel();
        $localFile = ExcelHelperTrait::download2local($url);    //下载到本地临时文件
//$localFile =  $_SERVER['DOCUMENT_ROOT'] . "/public/tmp/impspu.xls";
        $fileType = PHPExcel_IOFactory::identify($localFile);    //获取文件类型
        $objReader = PHPExcel_IOFactory::createReader($fileType);    //创建PHPExcel读取对象
        $objPHPExcel = $objReader->load($localFile);    //加载文件
        $data = $objPHPExcel->getSheet(0)->toArray();
        if (is_array($data)) {
            $success = $faild = 0;
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('N1', '导入结果');
            $progress_redis['total'] = count($data);
            foreach ($data as $key => $r) {
                $progress_redis['processed'] = $key + 1;    //记录导入进度信息
                redisSet($progress_key, json_encode($progress_redis));
                try {
                    $workText = '';
                    if ($key < 1) {
                        continue;
                    }
                    $fp = fopen(MYPATH . '/public/file/spuedit.lock', 'r');
                    if (flock($fp, LOCK_EX)) {
                        $data_tmp = [];
                        $input_spu = trim($r[2]);    //excel输入的spu
                        if (!empty($input_spu) && strlen($input_spu) != 16) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[产品编码有误]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
                        $data_tmp['lang'] = $lang;
                        $data_tmp['material_cat_no'] = trim($r[3]);    //物料分类
                        if (empty($data_tmp['material_cat_no'])) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[物料分类编码不能为空]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
//检查物料分类
                        $mexist = $mcatModel->info($data_tmp['material_cat_no'], $lang);
                        if (!$mexist) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[物料分类编码不存在]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
                        $data_tmp['name'] = trim($r[4]);    //名称
                        if (empty($data_tmp['name'])) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[产品名称不能为空]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
                        $data_tmp['show_name'] = trim($r[5]);    //展示名称
//严重展示名称必须包含产品名称
                        if (!empty($data_tmp['show_name'])) {
                            if (!strstr($data_tmp['show_name'], $data_tmp['name'])) {
                                $faild++;
                                $objPHPExcel->setActiveSheetIndex(0)
                                        ->setCellValue('N' . ( $key + 1 ), '操作失败[展示名称必须包含产品名称]');
                                flock($fp, LOCK_UN);
                                fclose($fp);
                                continue;
                            }
                        }

                        if (empty($r[6])) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[产品组不能为空]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        } else {
                            $bizline_model = new BizlineModel();
                            $bizline = $bizline_model->field('id')->where(['name' => trim($r[6])])->find();
                            if (!$bizline) {
                                $faild++;
                                $objPHPExcel->setActiveSheetIndex(0)
                                        ->setCellValue('N' . ( $key + 1 ), '操作失败[产品组不存在]');
                                flock($fp, LOCK_UN);
                                fclose($fp);
                                continue;
                            }
                            $data_tmp['bizline_id'] = isset($bizline['id']) ? $bizline['id'] : null;
                        }
//品牌
                        if (empty($r[7])) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[产品品牌不能为空]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
                        $condition_brand = array(
                            'brand' => array('like', '%"name":"' . trim($r[7]) . '"%')
                        );
                        $brand_id = $brandModel->field('id')->where($condition_brand)->find();
                        if (!$brand_id) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[产品品牌不存在]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
                        $brand_ary = array('name' => trim($r[7]), 'style' => 'TEXT', 'label' => trim($r[7]), 'logo' => '');
                        ksort($brand_ary);
                        $data_tmp['brand'] = json_encode($brand_ary, JSON_UNESCAPED_UNICODE);
                        $data_tmp['description'] = trim($r[8]);    //产品介绍
                        if (empty($data_tmp['description'])) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[产品介绍不能为空]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
// $data_tmp['advantages'] = $r[6];
                        $data_tmp['tech_paras'] = trim($r[9]);    //技术参数
                        if (empty($data_tmp['tech_paras'])) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[技术参数不能为空]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
                        $data_tmp['exe_standard'] = trim($r[10]);   //执行标准
                        if (empty($data_tmp['exe_standard'])) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[执行标准不能为空]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
                        $data_tmp['warranty'] = trim($r[11]);    //质保期
                        if (empty($data_tmp['warranty'])) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[质保期不能为空]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
                        $data_tmp['keywords'] = trim($r[12]);    //关键字
                        if (empty($data_tmp['keywords'])) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[关键字不能为空]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
                        $data_tmp['source'] = !empty($r[0]) ? $r[0] : 'ERUI';
                        $data_tmp['source_detail'] = 'Excel批量导入';
                        $data_tmp['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                        $data_tmp['created_at'] = date('Y-m-d H:i:s');

//根据lang,material_cat_no,brand查询name是否存在
                        $condition = array(
                            'material_cat_no' => $data_tmp['material_cat_no'],
                            'name' => $data_tmp['name'],
                            'lang' => $lang,
                            'brand' => $data_tmp['brand'],
                            'deleted_flag' => 'N',
                        );
                        $exist = $this->field('spu')->where($condition)->select();
                        if ($exist) {
                            if (empty($input_spu)) {    //存在且没有传递spu 提示错误
                                $faild++;
                                $objPHPExcel->setActiveSheetIndex(0)
                                        ->setCellValue('N' . ( $key + 1 ), '操作失败[已存在]');
                                flock($fp, LOCK_UN);
                                fclose($fp);
                                continue;
                            } else {    //存在且传递了spu 则按修改操作
                                $newspu = array('spu' => $input_spu);
                                if (in_array($newspu, $exist)) {
                                    $workText = '修改';
                                    $condition_update = array(
                                        'spu' => $input_spu,
                                        'lang' => $lang
                                    );
                                    $result = $this->where($condition_update)->save($data_tmp);
                                } else {
                                    $faild++;
                                    $objPHPExcel->setActiveSheetIndex(0)
                                            ->setCellValue('N' . ( $key + 1 ), '操作失败[已存在]');
                                    flock($fp, LOCK_UN);
                                    fclose($fp);
                                    continue;
                                }
                            }
                        } else {
                            $data_tmp['status'] = $this::STATUS_DRAFT;
                            $workText = '新增';

                            $input_spu = $input_spu ? $input_spu : $this->createSpu($r[3]);    //生成spu
                            if ($input_spu === false) {
                                $faild++;
                                $objPHPExcel->setActiveSheetIndex(0)
                                        ->setCellValue('N' . ( $key + 1 ), '操作失败[生成spu编码失败]');
                                flock($fp, LOCK_UN);
                                fclose($fp);
                                continue;
                            }

                            $data_tmp['spu'] = $input_spu;
                            $result = $this->add($this->create($data_tmp));
                        }

                        if ($result) {
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('C' . ( $key + 1 ), ' ' . $input_spu);
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), $workText . '操作成功');
                            $success++;

//更新es
                            $es_product_model->create_data($input_spu, $lang);
                        } else {
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), $workText . '操作失败');
                            $faild++;
                        }
                        $input_spu = null;
                        unset($input_spu);
                        flock($fp, LOCK_UN);
                    }
                    fclose($fp);
                } catch (Exception $e) {
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('N' . ( $key + 1 ), '操作失败-请检查数据');
                    $faild++;
                    flock($fp, LOCK_UN);
                    fclose($fp);
                    Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . $e->getMessage(), Log::ERR);
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
            $data_fastDFS['name'] = !empty($filename) ? $filename : pathinfo($localFile, PATHINFO_BASENAME);
            $fileId = postfile($data_fastDFS, $url);
            if ($fileId) {
                unlink($localFile);
                return array('success' => $success, 'faild' => $faild, 'url' => $fastDFSServer . $fileId['url'] . '?filename=' . $fileId['name'], 'name' => $fileId['name']);
            }
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $localFile . ' 上传到FastDFS失败', Log::INFO);
            return false;
        }
        return false;
    }

    /**
     * 导入
     * @param $data   注意这是excel模板数据
     * @param $lang
     */
    public function import2($url = '', $lang = '', $process = '', $filename = '') {
        if (empty($url) || empty($lang)) {
            return false;
        }

        /** 返回导入进度start */
        $progress_key = md5(json_encode(array($url, $lang)));
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

        $userInfo = getLoinInfo();
        $es_product_model = new EsProductModel();
        $mcatModel = new MaterialCatModel();
        $brandModel = new BrandModel();
        $localFile = ExcelHelperTrait::download2local($url);    //下载到本地临时文件
//$localFile =  $_SERVER['DOCUMENT_ROOT'] . "/public/file/33.xls";
        $fileType = PHPExcel_IOFactory::identify($localFile);    //获取文件类型
        $objReader = PHPExcel_IOFactory::createReader($fileType);    //创建PHPExcel读取对象
        $objPHPExcel = $objReader->load($localFile);    //加载文件
        $data = $objPHPExcel->getSheet(0)->toArray();
        if (is_array($data)) {
            $success = $faild = 0;
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('N1', '导入结果');
            $progress_redis['total'] = count($data);
            foreach ($data as $key => $r) {
                $progress_redis['processed'] = $key + 1;    //记录导入进度信息
                redisSet($progress_key, json_encode($progress_redis));
                try {
                    $workText = '';
                    if ($key < 1) {
                        continue;
                    }
                    $fp = fopen(MYPATH . '/public/file/spuedit.lock', 'r');
                    if (flock($fp, LOCK_EX)) {
                        $data_tmp = [];
                        $input_spu = trim($r[2]);    //excel输入的spu
                        if (!empty($input_spu) && strlen($input_spu) != 16) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[产品编码有误]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
                        $data_tmp['lang'] = $lang;
                        $data_tmp['material_cat_no'] = trim($r[3]);    //物料分类
                        if (empty($data_tmp['material_cat_no'])) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[物料分类编码不能为空]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
//检查物料分类
                        $mexist = $mcatModel->info($data_tmp['material_cat_no'], $lang);
                        if (!$mexist) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[物料分类编码不存在]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
                        $data_tmp['name'] = trim($r[4]);    //名称
                        if (empty($data_tmp['name'])) {
                            $faild++;
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), '操作失败[产品名称不能为空]');
                            flock($fp, LOCK_UN);
                            fclose($fp);
                            continue;
                        }
                        $data_tmp['show_name'] = trim($r[5]);    //展示名称
//产品组
                        if (!empty($r[6])) {
                            $bizline_model = new BizlineModel();
                            $bizline = $bizline_model->field('id')->where(['name' => trim($r[6])])->find();
                            if ($bizline) {
                                $data_tmp['bizline_id'] = isset($bizline['id']) ? $bizline['id'] : null;
                            }
                        }
//品牌
                        if (!empty($r[7])) {
                            $condition_brand = array(
                                'brand' => array('like', '%"name":"' . trim($r[7]) . '"%')
                            );
                            $brand_id = $brandModel->field('id')->where($condition_brand)->find();
                            if (!$brand_id) {
                                $faild++;
                                $objPHPExcel->setActiveSheetIndex(0)
                                        ->setCellValue('N' . ( $key + 1 ), '操作失败[产品品牌不存在]');
                                flock($fp, LOCK_UN);
                                fclose($fp);
                                continue;
                            }
                            $brand_ary = array('name' => trim($r[7]), 'style' => 'TEXT', 'label' => trim($r[7]), 'logo' => '');
                            ksort($brand_ary);
                            $data_tmp['brand'] = json_encode($brand_ary, JSON_UNESCAPED_UNICODE);
                        } else {
                            $data_tmp['brand'] = '{}';
                        }
                        $data_tmp['description'] = trim($r[8]);    //产品介绍
                        $data_tmp['tech_paras'] = trim($r[9]);    //技术参数
                        $data_tmp['exe_standard'] = trim($r[10]);   //执行标准
                        $data_tmp['warranty'] = trim($r[11]);    //质保期
                        $data_tmp['keywords'] = trim($r[12]);    //关键字
                        $data_tmp['source'] = !empty($r[0]) ? $r[0] : 'ERUI';
                        $data_tmp['source_detail'] = 'Excel临时导入';

//根据lang,material_cat_no,brand查询name是否存在
                        /* $condition = array(
                          // 'material_cat_no' => $data_tmp['material_cat_no'],
                          'name' => $data_tmp['name'],
                          'lang' => $lang,
                          'brand' => $data_tmp['brand'],
                          'deleted_flag' => 'N',
                          );
                          $exist = $this->field('spu')->where($condition)->select();
                          if ($exist) {
                          if (empty($input_spu)) {    //存在且没有传递spu 提示错误
                          $faild++;
                          $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('N' . ( $key + 1 ), '操作失败[已存在同名SPU]');
                          flock($fp, LOCK_UN);
                          fclose($fp);
                          continue;
                          } else {    //存在且传递了spu 则按修改操作
                          $newspu = array('spu' => $input_spu);
                          if (in_array($newspu, $exist)) {
                          $workText = '修改';
                          $condition_update = array(
                          'spu' => $input_spu,
                          'lang' => $lang,
                          'deleted_flag' => 'N'
                          );
                          $result = $this->where($condition_update)->save($data_tmp);
                          } else {
                          $faild++;
                          $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('N' . ( $key + 1 ), '操作失败[已存在同名SPU]');
                          flock($fp, LOCK_UN);
                          fclose($fp);
                          continue;
                          }
                          }
                          } else {
                          $data_tmp['status'] = $this::STATUS_DRAFT;
                          $workText = '新增';

                          $input_spu = $input_spu ? $input_spu : $this->createSpu($r[3]);    //生成spu
                          if ($input_spu === false) {
                          $faild++;
                          $objPHPExcel->setActiveSheetIndex(0)
                          ->setCellValue('N' . ( $key + 1 ), '操作失败[生成spu编码失败]');
                          flock($fp, LOCK_UN);
                          fclose($fp);
                          continue;
                          }

                          $data_tmp['spu'] = $input_spu;
                          $result = $this->add($this->create($data_tmp));
                          } */
                        if (empty($input_spu)) {
                            $data_tmp['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                            $data_tmp['created_at'] = date('Y-m-d H:i:s');
                            $data_tmp['status'] = $this::STATUS_DRAFT;
                            $workText = '新增';

                            $input_spu = $this->createSpu($r[3]);    //生成spu
                            if ($input_spu === false) {
                                $faild++;
                                $objPHPExcel->setActiveSheetIndex(0)
                                        ->setCellValue('N' . ( $key + 1 ), '操作失败[生成spu编码失败]');
                                flock($fp, LOCK_UN);
                                fclose($fp);
                                continue;
                            }

                            $data_tmp['spu'] = $input_spu;
                            $result = $this->add($this->create($data_tmp));
                        } else {
                            $exist = $this->field('spu')->where(['spu' => trim($input_spu), 'lang' => $lang, 'deleted_flag' => 'N'])->find();
                            if ($exist) {
                                $data_tmp['updated_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                                $data_tmp['updated_at'] = date('Y-m-d H:i:s');
                                $result = $this->where(['spu' => trim($input_spu), 'lang' => $lang, 'deleted_flag' => 'N'])->save($data_tmp);
                            } else {
                                $data_tmp['created_by'] = isset($userInfo['id']) ? $userInfo['id'] : null;
                                $data_tmp['created_at'] = date('Y-m-d H:i:s');
                                $data_tmp['status'] = $this::STATUS_DRAFT;
                                $workText = '新增';
                                $data_tmp['spu'] = $input_spu;
                                $result = $this->add($this->create($data_tmp));
                            }
                        }

                        if ($result) {
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('C' . ( $key + 1 ), ' ' . $input_spu);
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), $workText . '操作成功');
                            $success++;

//更新es
                            $es_product_model->create_data($input_spu, $lang);
                        } else {
                            $objPHPExcel->setActiveSheetIndex(0)
                                    ->setCellValue('N' . ( $key + 1 ), $workText . '数据库操作失败');
                            $faild++;
                        }
                        $input_spu = null;
                        unset($input_spu);
                        flock($fp, LOCK_UN);
                    }
                    fclose($fp);
                } catch (Exception $e) {
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValue('N' . ( $key + 1 ), '操作失败-请检查数据');
                    $faild++;
                    flock($fp, LOCK_UN);
                    fclose($fp);
                    Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . $e->getMessage(), Log::ERR);
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
            $data_fastDFS['name'] = !empty($filename) ? $filename : pathinfo($localFile, PATHINFO_BASENAME);
            $fileId = postfile($data_fastDFS, $url);
            if ($fileId) {
                unlink($localFile);
                return array('success' => $success, 'faild' => $faild, 'url' => $fastDFSServer . $fileId['url'] . '?filename=' . $fileId['name'], 'name' => $fileId['name']);
            }
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $localFile . ' 上传到FastDFS失败', Log::INFO);
            return false;
        }
        return false;
    }

    /**
     * 产品导出
     * @return string
     */
    public function export($input = [], $process = '') {
        /** 返回导出进度start */
        $progress_key = md5(json_encode($input));
        if (!empty($process)) {
            if (redisExist($progress_key)) {
                $progress_redis = json_decode(redisGet($progress_key), true);
                return $progress_redis['processed'] < $progress_redis['total'] ? ceil($progress_redis['processed'] / $progress_redis['total'] * 100) : 100;
            } else {
                return 100;
            }
        }
        $progress_redis = array('start_time' => time());    //用来记录导入进度信息
        /** 导出进度end */
        set_time_limit(0);  # 设置执行时间最大值
        $condition = array('lang' => $input['lang']);
        if (isset($input['type']) && $input['type'] == 'CHECKING') {    //类型：CHECKING->审核spu下不去草稿状态。
            $condition['status'] = array('neq', 'DRAFT');
        }
        if (isset($input['spu'])) {    //spu编码
            $condition['spu'] = $input['spu'];
        }
        if (isset($input['name'])) {    //名称
            $condition['name'] = $input['name'];
        }
        if (isset($input['material_cat_no'])) {    //物料分类
            $condition['material_cat_no'] = $input['material_cat_no'];
        }
        if (isset($input['status'])) {    //状态
            $condition['status'] = $input['status'];
        }
        if (isset($input['created_by'])) {    //创建人
            $condition['created_by'] = $input['created_by'];
        }
        if (isset($input['created_at'])) {    //创建时间段，注意格式：2017-09-08 00:00:00 - 2017-09-08 00:00:00
            $time_ary = explode(' - ', $input['created_at']);
            $condition['created_at'] = array('between', $time_ary);
        }
        $pModel = new ProductModel();
        $count = $pModel->field('id')->where($condition)->count();    //总数
        $progress_redis['total'] = $count;
        if ($count <= 0) {
            jsonReturn('', ErrorMsg::FAILED, '无数据可导出');
        }
//存储目录
        $tmpDir = MYPATH . '/public/tmp/';
        rmdir($tmpDir);
        $dirName = $tmpDir . date('YmdH', time());
        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                jsonReturn('', ErrorMsg::FAILED, '操作失败，请联系管理员');
            }
        }
        $xlsSize = 2000;    //单excel显示条数
        $pageSize = 1000;    //分页查询，每页多少条
        $current = 0;    //当前页
        $xlsNum = $i = $p = 0;
        $j = 4;    //excel输出的起始行
        $localFile = $_SERVER['DOCUMENT_ROOT'] . "/public/file/spuTemplate.xls";    //模板
        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));
        $fileType = PHPExcel_IOFactory::identify($localFile);    //获取文件类型
        $objReader = PHPExcel_IOFactory::createReader($fileType);    //创建PHPExcel读取对象
        $objPHPExcel = $objReader->load($localFile);    //加载文件
        $objPHPExcel->setActiveSheetIndex(0)->getStyle("N3")->getFont()->setBold(true);    //粗体
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue("N3", '审核状态');
        try {
            do {
                $result = $pModel->getList($condition, 'spu,material_cat_no,name,show_name,brand,keywords,exe_standard,tech_paras,description,warranty,status', $current * $pageSize, $pageSize);
                foreach ($result as $item) {
                    $i++;
                    $p++;
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("C" . $j, ' ' . $item['spu']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("D" . $j, ' ' . $item['material_cat_no']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("E" . $j, ' ' . $item['name']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("F" . $j, ' ' . $item['show_name']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("G" . $j, ' ' . $item['']);    //产品组
                    $brand_ary = json_decode($item['brand'], true);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("H" . $j, ' ' . $brand_ary['name']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("I" . $j, ' ' . $item['description']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("J" . $j, ' ' . $item['tech_paras']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("K" . $j, ' ' . $item['exe_standard']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("L" . $j, ' ' . $item['warranty']);
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("M" . $j, ' ' . $item['keywords']);
                    $status = '';
                    switch ($item['status']) {
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
                            $status = $item['status'];
                            break;
                    }
                    $objPHPExcel->setActiveSheetIndex(0)->setCellValue("N" . $j, ' ' . $status);

                    if ($i >= $xlsSize) {    //保存文件
                        $xlsNum = $xlsNum + 1;
                        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
                        $objWriter->save($dirName . '/' . $xlsNum . '.xls');
                        $i = 0;
                        unset($objPHPExcel);

                        if (($xlsNum * $xlsSize + $i) < $count) {    //判断如果还有数据则重开个excel
                            PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));
                            $fileType = PHPExcel_IOFactory::identify($localFile);    //获取文件类型
                            $objReader = PHPExcel_IOFactory::createReader($fileType);    //创建PHPExcel读取对象
                            $objPHPExcel = $objReader->load($localFile);    //加载文件
                            $objPHPExcel->setActiveSheetIndex(0)->getStyle("N3")->getFont()->setBold(true);    //粗体
                            $objPHPExcel->setActiveSheetIndex(0)->setCellValue("N3", '审核状态');
                            $j = 3;
                        }
                    } elseif (($xlsNum * $xlsSize + $i) >= $count) {
                        $xlsNum = $xlsNum + 1;
                        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
                        $objWriter->save($dirName . '/' . $xlsNum . '.xls');
                        $i = 0;
                        unset($objPHPExcel);
                    }
                    $j++;
                    $progress_redis['processed'] = $p;    //记录导入进度信息
                    redisSet($progress_key, json_encode($progress_redis));
                }
                $current++;
            } while (count($result) >= $pageSize);
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Export failed:' . $e, Log::ERR);
            return false;
        }
        ZipHelper::zipDir($dirName, $dirName . '.zip');
        ZipHelper::removeDir($dirName);    //清除目录
        if (file_exists($dirName . '.zip')) {
//把导出的文件上传到文件服务器上
            $server = Yaf_Application::app()->getConfig()->myhost;
            $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
            $url = $server . '/V2/Uploadfile/upload';
            $data['tmp_name'] = $dirName . '.zip';
            $data['type'] = 'application/zip';
            $data['name'] = pathinfo($dirName . '.zip', PATHINFO_BASENAME);
            $fileId = postfile($data, $url);
            if ($fileId) {
                unlink($dirName . '.zip');
                return array('url' => $fastDFSServer . $fileId['url'], 'name' => $fileId['name']);
            }
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $dirName . '.zip 上传到FastDFS失败', Log::ERR);
            return false;
        }
    }

    /**
     * 导出上下架
     */
    public function exportShelf($input = []) {
        ini_set("memory_limit", "512M"); // 设置php可使用内存
        set_time_limit(0);  # 设置执行时间最大值

        $lang_ary = isset($input['lang']) ? array($input['lang']) : array('zh', 'en', 'es', 'ru');
        $userInfo = getLoinInfo();
        $showCatProduct = new ShowCatProductModel();
        $tableSCP = $showCatProduct->getTableName();
        $supplierModel = new SupplierModel();
        $spModel = new ProductSupplierModel();
        $tablePS = $spModel->getTableName();
        $userModel = new UserModel();

//目录
        $tmpDir = MYPATH . '/public/tmp/';
        $dirName = $tmpDir . time();
        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
        }

        foreach ($lang_ary as $key => $lang) {
            $num = 1;    //控制文件名
            $i = 0;    //用来控制分页查询
            $j = 2;    //excel控制输出
            $l = 0;
            $length = 100;
//$objPHPExcel = null;

            $condition = array('product.lang' => $lang);
            if (isset($input['spus']) && is_array($input['spus']) && !empty($input['spus'])) {
                $condition['product.spu'] = array('in', $input['spus']);
            } else {
                if (isset($input['spu']) && !empty($input['spu'])) {    //spu编码
                    $condition['product.spu'] = $input['spu'];
                }
                if (isset($input['name']) && !empty($input['name'])) {    //名称
                    $condition['product.name'] = array('like', '%' . $input['spu'] . '%');
                }
                if (isset($input['mcat_no3']) && !empty($input['mcat_no3'])) {    //物料分类
                    $condition['product.material_cat_no'] = $input['mcat_no3'];
                }
                if (isset($input['supplier_name']) && !empty($input['supplier_name'])) {
                    $supplierInfo = $supplierModel->field('id')->where(['name' => $input['supplier_name'], 'deleted_flag' => 'N'])->find();
                    if ($supplierInfo) {
                        $condition['product_supplier.supplier_id'] = $supplierInfo['id'];
                    } else {
                        jsonReturn('', ErrorMsg::FAILED, '无数据可导');
                    }
                }
                if (isset($input['onshelf_flag']) && !empty($input['onshelf_flag'])) {    //上架状态
                    if ($input['onshelf_flag'] == 'Y') {
                        $condition[$tableSCP . '.onshelf_flag'] = 'Y';
                        $condition[$tableSCP . '.status'] = 'VALID';
                    } elseif ($input['onshelf_flag'] == 'N') {
                        $condition[$tableSCP . '.onshelf_flag'] = 'N';
                    }
// $condition['status'] = $input['status'];
                }
                if (isset($input['user_name']) && !empty($input['user_name'])) {    //创建人
                    $userInfo = $userModel->field('id,user_no')->where(['name' => $input['user_name']])->find();
                    if ($userInfo) {
                        if (isset($input['user_type']) && $input['user_type'] == 'create') {
                            $condition['product.created_by'] = $userInfo['id'];
                        } else {
                            $condition['product.updated_by'] = $userInfo['id'];
                        }
                    } else {
                        jsonReturn('', ErrorMsg::FAILED, '无数据可导');
                    }
                }
                if (isset($input['date_start']) && !empty($input['date_start'])) {
                    if (isset($input['date_type']) && $input['date_type'] == 'create') {
                        $condition['product.created_at'] = array('EGT', $input['date_start']);
                    } else {
                        $condition['product.updated_at'] = array('EGT', $input['date_start']);
                    }
                }
                if (isset($input['date_end']) && !empty($input['date_end'])) {
                    if (isset($input['date_type']) && $input['date_type'] == 'create') {
                        $condition['product.created_at'] = array('ELT', $input['date_end']);
                    } else {
                        $condition['product.updated_at'] = array('ELT', $input['date_end']);
                    }
                }
            }
            do {
                unset($result);
                $field = 'product.spu,product.name,product.show_name,product.material_cat_no,product.brand,product.advantages,product.tech_paras,product.exe_standard,product.warranty,product.keywords,product.status,show_cat_product.cat_no,show_cat_product.onshelf_flag,show_cat_product.status as showcat_status,show_cat_product.checked_at';
                $result = $this->field($field)
                        ->join($tableSCP . ' ON product.spu = ' . $tableSCP . '.spu AND product.lang = ' . $tableSCP . '.lang', 'LEFT')
                        ->join($tablePS . ' ON product.spu = ' . $tablePS . '.spu', 'LEFT')
                        ->where($condition)
                        ->group('product.spu')
                        ->limit($i * $length, $length)
                        ->select();
//p($result);
                if ($result) {
                    foreach ($result as $r) {
                        $r['show_cat_name'] = $this->setShowCatName($r['cat_no'], $lang);
                        if (!isset($objPHPExcel) || !$objPHPExcel) {
                            PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));
                            $objPHPExcel = new PHPExcel();
                            $objPHPExcel->getProperties()->setCreator($userInfo['name']);
                            $objPHPExcel->getProperties()->setTitle("Product List");
                            $objPHPExcel->getProperties()->setLastModifiedBy($userInfo['name']);

//$objPHPExcel->createSheet();    //创建工作表
//$objPHPExcel->setActiveSheetIndex($key);    //设置工作表
//$objSheet = $objPHPExcel->getActiveSheet(0);    //当前sheet
                            $objPHPExcel->getActiveSheet(0)->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
                            $objPHPExcel->getActiveSheet(0)->getStyle("A1:M1")
                                    ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            $objPHPExcel->getActiveSheet()->getStyle("A1:M1")->getFont()->setSize(11)->setBold(true);    //粗体
                            $column_width_25 = ["B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "N"];
                            foreach ($column_width_25 as $column) {
                                $objPHPExcel->getActiveSheet(0)->getColumnDimension($column)->setWidth(25);
                            }
                            $objPHPExcel->getActiveSheet(0)->getStyle('B')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

                            $objPHPExcel->getActiveSheet(0)->setTitle($lang);
                            $objPHPExcel->getActiveSheet(0)->setCellValue("A1", "序号");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("B1", "产品编码");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("C1", "产品名称");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("D1", "展示名称");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("E1", "产品组");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("F1", "产品品牌");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("G1", "产品介绍");    //对应产品优势（李志确认）
                            $objPHPExcel->getActiveSheet(0)->setCellValue("H1", "技术参数");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("I1", "执行标准");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("J1", "质保期");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("K1", "关键字");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("L1", "审核状态");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("M1", "上架状态");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("N1", "展示分类");
                            $objPHPExcel->getActiveSheet(0)->setCellValue("O1", "上/下架时间");
                        }

                        $objPHPExcel->getActiveSheet(0)->setCellValue("A" . $j, $j - 1, PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("B" . $j, ' ' . $r['spu'], PHPExcel_Cell_DataType::TYPE_STRING);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("C" . $j, $r['name']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("D" . $j, $r['show_name']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("E" . $j, $r['material_cat_no']);
                        $brand_ary = json_decode($r['brand'], true);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("F" . $j, (is_array($brand_ary) && isset($brand_ary['name'])) ? $brand_ary['name'] : $r['brand']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("G" . $j, $r['advantages']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("H" . $j, $r['tech_paras']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("I" . $j, $r['exe_standard']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("J" . $j, $r['warranty']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("K" . $j, $r['keywords']);
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
                        $objPHPExcel->getActiveSheet(0)->setCellValue("L" . $j, $status);

                        $onshelf = '';
                        if ($r['onshelf_flag'] == 'Y' && $r['showcat_status'] == 'VALID') {
                            $onshelf = '已上架';
                        } else {
                            $onshelf = '下架';
                        }
                        $objPHPExcel->getActiveSheet(0)->setCellValue("M" . $j, $onshelf);

                        $objPHPExcel->getActiveSheet(0)->setCellValue("N" . $j, $r['show_cat_name']);
                        $objPHPExcel->getActiveSheet(0)->setCellValue("O" . $j, $r['checked_at']);
                        $j++;
                        if ($j > 2001) {    //2000条
//保存文件
                            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
                            $objWriter->save($dirName . '/' . $lang . '_' . $num . '.xls');
                            unset($objWriter);
                            unset($objPHPExcel);
                            $j = 2;
                            $num ++;
                        } else {
                            if (count($result) < $length) {
                                $l++;
                            }
                            if ($l == count($result)) {
                                $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
                                $objWriter->save($dirName . '/' . $lang . '_' . $num . '.xls');
                                unset($objWriter);
                                unset($objPHPExcel);
                            }
                        }
                    }
                }
                $i++;
            } while (count($result) >= $length);
        }

        ZipHelper::zipDir($dirName, $dirName . '.zip');
        ZipHelper::removeDir($dirName);    //清除目录
        if (file_exists($dirName . '.zip')) {
//把导出的文件上传到文件服务器上
            $server = Yaf_Application::app()->getConfig()->myhost;
            $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
            $url = $server . '/V2/Uploadfile/upload';
            $data['tmp_name'] = $dirName . '.zip';
            $data['type'] = 'application/zip';
            $data['name'] = pathinfo($dirName . '.zip', PATHINFO_BASENAME);
            $fileId = postfile($data, $url);
            if ($fileId) {
                unlink($dirName . '.zip');
                return array('url' => $fastDFSServer . $fileId['url'] . '?filename=' . $fileId['name'], 'name' => $fileId['name']);
            }
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $dirName . '.zip 上传到FastDFS失败', Log::INFO);
            return false;
        } else {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Zip failed:' . $dirName . '.zip 打包失败', Log::INFO);
            return false;
        }
    }

    /**
     * 根据展示分类ID获取三级展示分类名称
     * @param $show_cat_no 展示分类id
     * @param $lang 语言
     *
     * @return string 三级分类拼接名称
     * @author 买买提
     */
    private function setShowCatName($show_cat_no, $lang) {

        $show_cat_3 = (new ShowCatModel)->where(['cat_no' => $show_cat_no, 'lang' => $lang])->field('parent_cat_no,name')->find();
        $show_cat_2 = (new ShowCatModel)->where(['cat_no' => $show_cat_3['parent_cat_no'], 'lang' => $lang])->field('parent_cat_no,name')->find();
        $show_cat_1 = (new ShowCatModel)->where(['cat_no' => $show_cat_2['parent_cat_no'], 'lang' => $lang])->field('name')->find();

        $show_cat_name = $show_cat_1['name'] . "/" . $show_cat_2['name'] . "/" . $show_cat_3['name'] . "-" . $show_cat_no;
        return $show_cat_name;
    }

    private function checkValidSku($spu, $lang) {
        return (new GoodsModel)->where(['spu' => $spu,
                    'lang' => $lang,
                    'deleted_flag' => 'N',
                    'status' => 'VALID'])->count();
    }

    public function getProductNames($spus, $lang = 'en') {
        if ($spus && is_array($spus) && $lang) {
            $list = $this->where(['spu' => ['in', $spus], 'lang' => $lang, 'deleted_flag' => 'N'])
                    ->field('spu,name,show_name')
                    ->select();
            $ret = [];
            foreach ($list as $item) {
                $ret[$item['spu']] = $item;
            }

            return $ret;
        } else {
            return [];
        }
    }

}
