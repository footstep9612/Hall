<?php

/**
 * 产品.
 * User: linkai
 * Date: 2017/6/15
 * Time: 18:52
 */
class ProductModel extends PublicModel {

    protected $module = '';

//状态
    const STATUS_NORMAL = 'NORMAL'; //发布
    const STATUS_TEST = 'TEST'; //测试；
    const STATUS_CHECKING = 'CHECKING'; //审核中；
    const STATUS_CLOSED = 'CLOSED';  //关闭
    const STATUS_DELETED = 'DELETED'; //DELETED-删除
//推荐状态
    const RECOMMEND_Y = 'Y';
    const RECOMMEND_N = 'N';

//定义校验规则
    protected $field = array(
        'name' => array('required'),
        'meterial_cat_no' => array('required'),
        'brand' => array('required'),
    );

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
     * spu列表
     * @param array $condition = array('lang'=>'',    语言
     *                  'source'=>'',    来源
     *                  'brand' =>'',    品牌
     *                  'meterial_cat_no'=>''    分类
     *                  'keyword'=>''    名称/创建者/spu编码
     *                  'start_time'=>''    创建开始时间
     *                  'end_time' =>''    创建结束时间
     *             )  均为选择型
     * @param int $current_num 当前页
     * @param int $pagesize 每页显示条数
     */
    public function getList($condition = []) {
        $field = "lang,spu,brand,name,created_by,created_at,meterial_cat_no";

        $where = "status <> '" . self::STATUS_DELETED . "'";
//语言 有传递取传递语言，没传递取浏览器，浏览器取不到取en英文
        $condition['lang'] = isset($condition['lang']) ? strtolower($condition['lang']) : (browser_lang() ? browser_lang() : 'en');
        $where .= " AND lang='" . $condition['lang'] . "'";

        if (isset($condition['source'])) {
            $where .= " AND source='" . $condition['source'] . "'";
        }
        if (isset($condition['brand'])) {
            $where .= " AND brand='" . $condition['brand'] . "'";
        }
        if (isset($condition['meterial_cat_no'])) {
            $where .= " AND meterial_cat_no='" . $condition['meterial_cat_no'] . "'";
        }
        if (isset($condition['start_time'])) {
            $where .= " AND created_at >= '" . $condition['start_time'] . "'";
        }
        if (isset($condition['end_time'])) {
            $where .= " AND created_at <= '" . $condition['end_time'] . "'";
        }

//处理keyword
        if (isset($condition['keyword'])) {
            $where .= " AND (name like '%" . $condition['keyword'] . "%'
                            OR show_name like '%" . $condition['keyword'] . "%'
                            OR created_by like '%" . $condition['keyword'] . "%'
                            OR spu = '" . $condition['keyword'] . "'
                          )";
        }

        $current_num = isset($condition['current_no']) ? $condition['current_no'] : 1;
        $pagesize = isset($condition['pagesize']) ? $condition['pagesize'] : 10;
        try {
            $return = array(
                'count' => 0,
                'current_no' => $current_num,
                'pagesize' => $pagesize
            );
            $result = $this->field($field)->where($where)->order('created_at DESC')->page($current_num, $pagesize)->select();
            $count = $this->field('spu')->where($where)->count();
            if ($result) {
//遍历获取分类　　与ｓｋｕ统计
                foreach ($result as $k => $r) {
//分类
                    $mcatModel = new MaterialcatModel();
                    $mcatInfo = $mcatModel->getMeterialCatByNo($r['meterial_cat_no'], $condition['lang']);
                    $result[$k]['meterial_cat'] = $mcatInfo ? $mcatInfo['name'] : '';

//sku统计
                    $goodsModel = new GoodsModel();
                    $result[$k]['sku_count'] = $goodsModel->getCountBySpu($r['spu'], $condition['lang']);
                }
                $return['count'] = $count;
                $return['data'] = $result;
                return $return;
            } else {
                $return['count'] = 0;
                $return['data'] = array();
                return $return;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 根据SPU获取品牌
     * @param string $spu
     * @param $lang
     * @return string
     */
    public function getBrandBySpu($spu = '', $lang) {
        if (empty($spu))
            return '';

        $condition = array(
            'spu' => $spu,
            'status' => self::STATUS_NORMAL,
            'lang' => $lang
        );
        $result = $this->field('brand')->where($condition)->find();
        if ($result) {
            return $result['brand'];
        }
        return '';
    }

    /**
     * 根据SPU获取物料分类
     * @param string $spu
     * @param $lang
     * @return string
     */
    public function getMcatBySpu($spu = '', $lang) {
        if (empty($spu))
            return false;

        $condition = array(
            'spu' => $spu,
            'status' => self::STATUS_NORMAL,
            'lang' => $lang
        );
        $result = $this->field('meterial_cat_no')->where($condition)->find();
        if ($result) {
            return $result['meterial_cat_no'];
        }
        return false;
    }

    /**
     * spu 详情
     * @param string $spu    spu编码
     * @param string $lang    语言
     * return array
     */
    public function getInfo($spu = '', $lang = '') {
        if (empty($spu))
            jsonReturn('', '1000', 'spu不能为空');

//详情返回四种语言， 这里的lang作当前语言类型返回
        $lang = $lang ? strtolower($lang) : (browser_lang() ? browser_lang() : 'en');
        $condition = array(
            'spu' => $spu,
            'status' => array('neq', self::STATUS_DELETED),
        );
        $field = 'spu,lang,name,show_name,meterial_cat_no,brand,keywords,description,exe_standard,profile';
        try {
            $result = $this->field($field)->where($condition)->select();
            $data = array(
                'lang' => $lang
            );
            if ($result) {
                foreach ($result as $item) {
//查询品牌
                    $brand = $this->getBrandBySpu($spu, $item['lang']);
                    $item['brand'] = $brand;

//语言分组
                    $data[$item['lang']] = $item;
                }

//附件不分语言，暂时放循环外
                $pattach = new ProductAttachModel();
                $data['attachs'] = $pattach->getAttachBySpu($spu);
            }
            return $data;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 编辑spu详情
     * @param array $input 参数
     */
    public function editInfo($input) {
        if (empty($input))
            return false;

//获取当前模块地址
        $config_obj = Yaf_Registry::get("config");
        $this_module = $config_obj->myhost . $this->module;

//获取当前用户信息
        $userInfo = getLoinInfo();


        $spu = isset($input['spu']) ? trim($input['spu']) : createSpu();    //不存在生产spu
        $this->startTrans();
        try {
            foreach ($input as $key => $item) {
                if (in_array($key, array('zh', 'en', 'ru', 'es'))) {
//字段校验
                    $item = $this->checkParam($item, $this->field);
                    $data = array(
                        'lang' => $key,
                        'name' => $item['name'],
                        'show_name' => isset($item['show_name']) ? $item['show_name'] : '',
                        'meterial_cat_no' => $item['meterial_cat_no'],
                        // 'show_cat_no' => isset($item['show_cat_no']) ? $item['show_cat_no'] : '',    //后期实现
                        'brand' => $item['brand'],
                        'exe_standard' => isset($item['exe_standard']) ? $item['exe_standard'] : '', //执行标准
                        'profile' => isset($item['profile']) ? $item['profile'] : '', //产品简介
                        'keywords' => isset($item['keywords']) ? $item['profile'] : '', //简介
                        'description' => isset($item['description']) ? $item['description'] : '', //描述
                        'status' => self::STATUS_CHECKING,
                    );

//不存在添加，存在则为修改

                    if (!isset($input['spu'])) {
                        $data['spu'] = $spu;
                        $data['qrcode'] = createQrcode($this_module . '/product/info/' . $spu);    //生成spu二维码    冗余字段这块还要看后期需求是否分语言
                        $data['created_by'] = $userInfo['name'];    //创建人                 
                        $data['created_at'] = date('Y-m-d H:i:s', time());
                        $data['updated_at'] = date('Y-m-d H:i:s', time());    //修改时间
                        $this->add($data);
                    } else {

                        $data['updated_by'] = $userInfo['name'];    //修改人

                        $data['updated_at'] = date('Y-m-d H:i:s', time());    //修改时间
                        $this->where(array('spu' => trim($input['spu']), 'lang' => $key))->save();
                    }
                } elseif ($key == 'attachs') {
                    if ($item) {

                        //验证附件
                        if (!$this->checkAttachImage($item)) {
                            jsonReturn('', '1000', '产品图不能为空');
                        }

                        foreach ($item as $atta) {
                            $data = array(
                                'spu' => $spu,
                                'attach_type' => isset($atta['attach_type']) ? $atta['attach_type'] : '',
                                'attach_name' => isset($atta['attach_name']) ? $atta['attach_name'] : '',
                                'attach_url' => isset($atta['attach_url']) ? $atta['attach_url'] : '',
                            );
                            $pattach = new ProductAttachModel();
                            $pattach->addAttach($data);
                        }
                    }
                } else {

                    break;
                }
            }
            $this->commit();
            return $spu;
        } catch (Exception $e) {
            $this->rollback();
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
            $param[$k] = htmlspecialchars(trim($v));
            continue;
        }
        return $param;
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

    /**
     * 设置当前module
     * @param $module
     */
    public function setModule($module) {
        $this->module = $module;
    }

}
