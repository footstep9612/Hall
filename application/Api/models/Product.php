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
    const STATUS_CLOSED = 'CLOSED';  //关闭
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_TEST = 'TEST'; //测试  暂存；
    const STATUS_CHECKING = 'CHECKING'; //审核中；
    const STATUS_INVALID = 'INVALID';  //无效
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
        $field = "id,lang,spu,brand,name,status,created_by,created_at,updated_by,updated_at,meterial_cat_no";

        $where = "status <> '" . self::STATUS_DELETED . "'";
        //语言 有传递取传递语言，没传递取浏览器，浏览器取不到取en英文
        //$condition['lang'] = isset($condition['lang']) ? strtolower($condition['lang']) : (browser_lang() ? browser_lang() : 'en');
        //$where .= " AND lang='" . $condition['lang'] . "'";
        if(isset($condition['lang'])){
            $where .= " AND lang='" . $condition['lang'] . "'";
        }

        //来源
        if (isset($condition['source'])) {
            $where .= " AND source='" . $condition['source'] . "'";
        }

        //品牌
        if (isset($condition['brand'])) {
            $where .= " AND brand='" . $condition['brand'] . "'";
        }

        //物料分类
        if (isset($condition['meterial_cat_no'])) {
            $where .= " AND meterial_cat_no='" . $condition['meterial_cat_no'] . "'";
        }

        //创建时间
        if (isset($condition['start_time'])) {
            $where .= " AND created_at >= '" . $condition['start_time'] . "'";
        }
        if (isset($condition['end_time'])) {
            $where .= " AND created_at <= '" . $condition['end_time'] . "'";
        }

        //spu
        if(isset($condition['spu'])){
            $where .= " AND spu ='" .$condition['spu'] . "'";
        }

        //status
        if(isset($condition['status'])){
            $where .= " AND status ='" .strtoupper($condition['status']) . "'";
        }

        //创建人
        if(isset($condition['created_by'])){
            $where .= " AND created_by = '".$condition['created_by']."'";
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
                //总sku数
                $sku_total = 0;
                //遍历获取分类　　与ｓｋｕ统计
                foreach ($result as $k => $r) {
                    //分类
                    $mcatModel = new MaterialcatModel();
                    $mcatInfo = $mcatModel->getMeterialCatByNo($r['meterial_cat_no'], $r['lang']);
                    $result[$k]['meterial_cat'] = $mcatInfo ? $mcatInfo['name'] : '';

                    //sku统计
                    $goodsModel = new GoodsModel();
                    $result[$k]['sku_count'] = $goodsModel->getCountBySpu($r['spu'], $r['lang']);
                    $sku_total += $result[$k]['sku_count'];
                }
                $return['count'] = $count;
                $return['total_sku'] = $sku_total;
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
     * 根据SPU获取品牌,供应商,分类
     * @param string $spu
     * @param $lang
     * @return string
     */
    public function getBrandBySpu($spu, $lang) {
        if (empty($spu))
            return '';
        $condition = array(
            'spu' => $spu,
            'lang' => $lang,
            'status' => self::STATUS_VALID
        );
        $result = $this->field('lang,brand,meterial_cat_no,supplier_name')->where($condition)->select();

        if ($result) {
            return $result;
        }
        return '';
    }

    /**
     * 根据SPU获取品牌,供应商,分类
     * @param string $spu
     * @param $lang
     * @return string
     */
    public function getNameBySpu($spu, $lang = '') {
        if (empty($spu))
            return '';
        $condition = array(
            'spu' => $spu,
            'lang' => $lang,
            'status' => self::STATUS_VALID
        );
        $result = $this->field('name')->where($condition)->select();

        if ($result) {
            return $result;
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
     * spu 详情    --公共
     * @param string $spu    spu编码
     * @param string $lang    语言
     * return array
     */
    public function getInfo($spu = '', $lang = '',$status = '') {
        if (empty($spu))
            return array();

        $condition = array(
            'spu' => $spu,
        );
        if(!empty($lang)){
            $condition['lang'] = $lang;
        }
        if(!empty($status)){
            $condition['status'] = $status;
        }

        //读取redis缓存
        if(redisHashExist('Spu',md5(json_encode($condition)))){
            //return json_decode(redisHashGet('Spu',md5(json_encode($condition))),true);
        }

        //数据读取
        try {
            $field = 'spu,lang,qrcode,name,status,show_name,meterial_cat_no,brand,keywords,description,exe_standard,app_scope,tech_paras,advantages,profile,supplier_id,supplier_name,recommend_flag,source,source_detail,created_by,created_at,updated_by,updated_at,checked_by,checked_at,customization_flag,customizability,availability,availability_ratings,resp_time,resp_rate,delivery_cycle,target_market,warranty';
            $result = $this->field($field)->where($condition)->select();
            $data = array();
            if ($result) {
                foreach ($result as $item) {
                    //语言分组
                    $data[$item['lang']] = $item;
                }
                redisHashSet('Spu',md5(json_encode($condition)),json_encode($data));
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

        //获取当前用户信息
        $userInfo = getLoinInfo();

        $spu = isset($input['spu']) ? trim($input['spu']) : '';
        $this->startTrans();
        try {
            foreach ($input as $key => $item) {
                if (in_array($key, array('zh', 'en', 'ru', 'es'))) {
                    if(empty($item)) continue;
                    $data = array(
                        'lang' => $key,
                        'name' => isset($item['name']) ? $item['name'] : '',
                        'show_name' => isset($item['show_name']) ? $item['show_name'] : '',
                        'meterial_cat_no' => isset($item['meterial_cat_no']) ? $item['meterial_cat_no'] : '',
                        'brand' => isset($item['brand']) ? $item['brand'] : '',
                        'advantages' => isset($item['advantages']) ? $item['advantages'] : '',   //产品优势
                        'tech_paras' => isset($item['tech_paras']) ? $item['tech_paras'] : '',    //技术参数
                        'exe_standard' => isset($item['exe_standard']) ? $item['exe_standard'] : '', //执行标准=
                        'keywords' => isset($item['keywords']) ? $item['profile'] : '', //关键词
                        'description' => isset($item['description']) ? $item['description'] : '', //详情描述
                    );

                    //除暂存外都进行校验     这里存在暂存重复加的问题，此问题暂时预留。
                    $input['status'] = (isset($input['status']) && in_array(strtoupper($input['status']),array('TEST,CHECKING,VALID,DELETED,INVALID,CLOSED,NORMAL'))) ? strtoupper($input['status']) : 'TEST';
                    if($input['status'] !='TEST'){
                        //字段校验
                        $item = $this->checkParam($data, $this->field);

                        //添加时判断同一语言，name,meterial_cat_no是否存在
                        $exist_condition = array(
                            'lang' => $key,
                            'name' => $item['name'],
                            'meterial_cat_no' => $item['meterial_cat_no'],
                        );
                        $exist = $this->find($exist_condition);
                        if($exist) {
                            jsonReturn('', '400', '已存在');
                        }
                    }
                    $data['status'] = $input['status'];

                    //不存在添加，存在则为修改
                    if (!isset($input['spu'])) {
                        $spu_tmp = $this->createSpu();    //不存在生产spu
                        $data['spu'] = $spu_tmp;
                        $data['qrcode'] = createQrcode('/product/info/' . $spu);    //生成spu二维码  注意模块    冗余字段这块还要看后期需求是否分语言
                        $data['created_by'] = isset($userInfo['name']) ? $userInfo['name'] : '';    //创建人
                        $data['created_at'] = date('Y-m-d H:i:s', time());
                        $data['updated_at'] = date('Y-m-d H:i:s', time());    //修改时间
                        if($this->add($data)){
                            $spu = $spu_tmp;
                        }
                    } else {
                        $data['updated_by'] = isset($userInfo['name']) ? $userInfo['name'] : '';    //修改人
                        $data['updated_at'] = date('Y-m-d H:i:s', time());    //修改时间
                        $this->where(array('spu' => trim($input['spu']), 'lang' => $key))->save($data);
                    }
                } elseif ($key == 'attachs') {
                    if ($item && $spu) {
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
                            if(empty($data['attach_url']))
                                continue;

                            $pattach = new ProductAttachModel();
                            $pattach->addAttach($data);
                        }
                    }
                } else {
                    continue;
                }
            }
            $spu ? $this->commit() : $this->rollback();
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
     */
    public function upStatus($spu = '',$lang='',$status=''){
        if(empty($spu) || empty($lang) || empty($status))
            return false;

        if($spu && is_array($spu)){
            $this->startTrans();
            try{
                $model = new EsproductModel();
                foreach($spu as $r){
                    $where = array(
                        'spu' => $r,
                        'lang'=> $lang
                    );
                    $result = $this->where($where)->save(array('status'=>$status));
                    if($result){    //更新ES
                        @$model->changestatus($r,$status,$lang);
                    }
                }
                $this->commit();
                return true;
            }catch (Exception $e){
                $this->rollback();
                return false;
            }
        }
        return false;
    }
    /**
     * 通过spu查询四种语言name
     * @param ispu
     * @param array
     */
    public function getName($spu){
        if(empty($spu))
            return false;
        $where = array();
        if(isset($spu)){
            $where['spu'] = $spu;
        }
        $result = $this->field('name,show_name')->where($where)->select();
        return $result ? $result : false;
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

    /**
     * 生成ｓｐｕ编码
     * @return string
     */
    public function createSpu() {
        $spu = randNumber(6);
        $condition = array(
            'spu' => $spu
        );
        $exit = $this->find($condition);
        if($exit) {
            $this->createSpu();
        }
        return $spu;
    }
}