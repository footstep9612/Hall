<?php

/**
 * Class PublicController
 * 全局方法
 */
abstract class PublicController extends Yaf_Controller_Abstract {

    protected $user;
    protected $put_data = [];
    protected $params = [];
    protected $headers = [];
    protected $code = "1";
    protected $send = [];
    protected $message = '';
    protected $lang = '';

    /*
     * 初始化
     */

    public function init() {
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);

        $this->headers = getHeaders();
        $this->put_data = $this->getPut();

        $user_id = $GLOBALS['SSO_USER']['id'];
        $erui_token = $GLOBALS['SSO_TOKEN'];
        if (!redisExist('user.' . $user_id . '.' . $erui_token) && $user_id) {
            $this->user = $this->getUserInfo($user_id);
            redisSet('user.' . $user_id . '.' . $erui_token, json_encode($this->user), 18000);
        } elseif ($user_id) {
            $user = redisGet('user.' . $user_id . '.' . $erui_token);
            $this->user = json_decode($user, true);
            unset($user);
        }
        $this->_setUid($this->user);
        if (isset($this->user['id']) && $this->user['id'] > 0) {
            // 加载php公共配置文件
            $this->loadCommonConfig();
            // 语言检查
            $this->checkLanguage();
            // 设置语言
            $this->setLang(LANG_SET);
        } else {
            header("Content-Type: application/json");
            exit(json_encode(['code' => 403, 'message' => 'Token Expired.']));
        }
    }

    /**
     * 获取SSO Token
     * 默认由Cookie读取，其次从Http header读取，token变量名称为eruitoken
     * @author: zhengkq
     * @return string $sso_token 返回sso token
     * */
    protected function getUserInfo($user_id) {
        $user_model = new System_EmployeeModel();
        $user['id'] = $user_id;
        $user['token'] = $GLOBALS['SSO_TOKEN'];

        $user['name'] = $user_model->getUserNameById($user_id);
        $user['group_org'] = $user['group_id'] = (new System_OrgMemberModel())->getOrgIdsByUserid($user_id);

        $user['role_id'] = (new System_RoleMemberModel())->getRoleIdsByUserid($user_id);
        $user['role_no'] = (new System_RoleModel())->getRoleNossByRoleIds($user['role_id']);
        $user['country_bn'] = (new System_CountryMemberModel())->getCountryBnsByUserid($user_id);
        return $user;
    }

    /*
     * 设置UID
     * @param array $userinfo 用户信息
     * return void;
     */

    protected function _setUid($userinfo) {
        if (!defined('UID') && $userinfo) {
            define('UID', $userinfo["id"]);
        }
    }

    public function __call($method, $args) {
        $data['code'] = -1;
        $data['message'] = 'Action :There is no method  ' . $method;
        $this->jsonReturn($data);
    }

    /*
     * 设置语言
     */

    public function setLang($lang = 'en') {
        $this->lang = $lang;
    }

    public function setvalue($name, $value) {
        $this->send[$name] = $value;
    }

    /*
     * 获取语言
     */

    public function getLang() {
        return $this->lang;
    }

    /*
     * 设置信息编码
     */

    public function setCode($code) {
        $this->code = $code;
    }

    /*
     * 设置提示信息
     * 以后会和错误码同一起来
     */

    public function setMessage($message) {
        $this->message = $message;
    }

    /*
     * 获取信息编码
     */

    public function getCode() {
        return $this->code;
    }

    /*
     * 获取提示信息
     */

    public function getMessage() {

        if (!$this->message) {
            $message = MSG::getMessage($this->getCode(), $this->getLang());
            $this->message = $message;
            return $message;
        }
        return $this->message;
    }

    /*     * *******************------公共输出JSON函数------*************************
     * @param mix $data // 发送到客户端的数据 如果$data 中含有code 则直接输出
     * 否则 与$this->code $this->message 组合输出
     * $this ->message 有待完善 如果错误码都有对应的message
     * 可以和错误码表经过对应 输出错误信息
     * @return json
     */

    public function jsonReturn($data = [], $type = 'JSON') {
        header('Content-Type:application/json; charset=utf-8');
        if (isset($data['code'])) {
            exit(json_encode($data, JSON_UNESCAPED_UNICODE));
        } else {
            if ($data) {
                $this->send['data'] = $data;
            } elseif ($data === null) {
                $this->send['data'] = null;
            } elseif ($data === 0) {
                $this->send['data'] = 0;
            }
            $this->send['code'] = $this->getCode();
            if ($this->send['code'] == "1" && !$this->getMessage()) {
                $this->send['message'] = L('SUCCESS', null, '成功！');
            } elseif (!$this->getMessage()) {
                $this->send['message'] = L('ERROR', null, '未知错误！');
            } else {
                $this->send['message'] = $this->getMessage();
            }
            exit(json_encode($this->send, JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     *  获取put过来的数据 待过滤优化
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */

    public function getPut($name = null, $default = null) {

        if (!$this->put_data) {
            $data = $this->put_data = json_decode(file_get_contents("php://input"), true);
        }
        if ($name === 'lang') {

            if (!empty($this->put_data [$name])) {
                $lang = trim($this->put_data [$name]);
            } else {
                if (!$this->headers) {
                    $this->headers = getHeaders();
                }
                $lang = !empty($this->headers [$name]) ? trim($this->headers [$name]) : trim($default);
            }
            return $lang;
        } elseif ($name) {
            if (isset($this->put_data [$name]) && is_string($this->put_data [$name])) {
                $data = !empty($this->put_data [$name]) ? trim($this->put_data [$name]) : trim($default);
            } else {
                $data = !empty($this->put_data [$name]) ? $this->put_data [$name] : $default;
            }
            return $data;
        } else {
            $data = $this->put_data;
            return $data;
        }
    }

    /**
     *
     * @link http://www.php.net/manual/en/yaf-request-abstract.getparam.php
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function getParam($name = null, $default = null) {

        if (!$this->params) {
            $data = $this->getRequest()->getParams();
            $gets = $this->getRequest()->getQuery();
            $this->params = array_merge($data, $gets);
        }
        if ($name === 'lang') {

            if (!empty($this->params [$name])) {
                $lang = trim($this->params [$name]);
            } else {
                if (!$this->headers) {
                    $this->headers = getHeaders();
                }
                $lang = !empty($this->headers [$name]) ? trim($this->headers [$name]) : trim($default);
            }
            return $lang;
        } elseif ($name) {
            if (isset($this->params [$name]) && is_string($this->params [$name])) {
                $data = !empty($this->params [$name]) ? trim($this->params [$name]) : trim($default);
            } else {
                $data = !empty($this->params [$name]) ? $this->params [$name] : $default;
            }
            return $data;
        } else {
            $data = $this->params;
            return $data;
        }
    }

    /**
     * Retrieve variable from client, this method will search the name in $_REQUEST params, if the name is not found, then will search in $_POST, $_GET, $_COOKIE, $_SERVER
     *
     * @link http://www.php.net/manual/en/yaf-request-http.get.php
     *
     * @param string $name the variable name
     * @param string $default if this parameter is provide, this will be returned if the variable can not be found
     *
     * @return mixed
     */
    public function get($name = null, $default = null) {
        if ($name) {
            return isset($_GET[$name]) && $_GET[$name] ? $_GET[$name] : $default;
        } else {
            return $_GET;
        }
    }

    /**
     * Retrieve $_GET variable
     *
     * @link http://www.php.net/manual/en/yaf-request-http.getquery.php
     *
     * @param string $name the variable name, if not provided returns all
     * @param mixed $default if this parameter is provide, this will be returned if the variable can not be found
     *
     * @return mixed
     */
    public function getQuery($name = null, $default = null) {

        return $this->getRequest()->getQuery($name, $default);
    }

    /**
     * Retrieve $_POST variable
     *
     * @link http://www.php.net/manual/en/yaf-request-http.getpost.php
     *
     * @param string $name the variable name, if not provided returns all
     * @param mixed $default if this parameter is provide, this will be returned if the variable can not be found
     *
     * @return mixed
     */
    public function getPost($name = null, $default = null) {

        if ($name) {
            return isset($_POST[$name]) && $_POST[$name] ? $_POST[$name] : $default;
        } else {
            return $_POST;
        }
    }

    /**
     * @link http://www.php.net/manual/en/yaf-request-abstract.getmethod.php
     *
     * @return string
     */
    public function getMethod() {
        return $this->getRequest()->getMethod();
    }

    /**
     * Retrieve $_SERVER variable
     *
     * @link http://www.php.net/manual/en/yaf-request-abstract.getserver.php
     *
     * @param string $name the variable name, if not provided returns all
     * @param mixed $default if this parameter is provide, this will be returned if the variable can not be found
     *
     * @return mixed
     */
    public function getServer($name, $default = null) {

        return $this->getRequest()->getServer($name, $default);
    }

    /**
     * Retrieve $_COOKIE variable
     *
     * @link http://www.php.net/manual/en/yaf-request-http.getcookie.php
     *
     * @param string $name the variable name, if not provided returns all
     * @param mixed $default if this parameter is provide, this will be returned if the variable can not be found
     *
     * @return mixed
     */
    public function getCookie($name, $default = null) {

        return $this->getRequest()->getCookie($name, $default);
    }

    /**
     * Retrieve $_FILES variable
     *
     * @link http://www.php.net/manual/en/yaf-request-http.getfiles.php
     *
     * @param string $name the variable name, if not provided returns all
     * @param mixed $default if this parameter is provide, this will be returned if the variable can not be found
     *
     * @return mixed
     */
    public function getFiles($name, $default = null) {

        return $this->getRequest()->getCookie($name, $default);
    }

    function think_filter(&$value) {
// TODO 其他安全过滤
// 过滤查询特殊字符
        if (preg_match('/^(EXP|NEQ|GT|EGT|LT|ELT|OR|XOR|LIKE|NOTLIKE|NOT BETWEEN|NOTBETWEEN|BETWEEN|NOTIN|NOT IN|IN)$/i', $value)) {
            $value .= ' ';
        }
    }

    function array_map_recursive($filter, $data) {
        $result = array();
        foreach ($data as $key => $val) {
            $result[$key] = is_array($val) ? array_map_recursive($filter, $val) : call_user_func($filter, $val);
        }
        return $result;
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
//    public function getlist($condition = []) {
//
//    }

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
//    public function info($code = '', $id = '', $lang = '') {
//
//    }

    /**
     * 删除数据
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return bool
     * @author zyg
     */
//    public function delete($code = '', $id = '', $lang = '') {
//
//    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return bool
     * @author zyg
     */
//    public function update($upcondition = []) {
//
//    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
//    public function create($createcondition = []) {
//
//    }

    /**
     * 获取采购商流水号
     * @author liujf 2017-06-20
     * @return string $buyerSerialNo 采购商流水号
     */
    public function getBuyerSerialNo() {

        $buyerSerialNo = $this->getSerialNo('buyerSerialNo', 'E-B-S-');

        return $buyerSerialNo;
    }

    /**
     * 获取供应商流水号
     * @author liujf 2017-06-20
     * @return string $supplierSerialNo 供应商流水号
     */
    public function getSupplierSerialNo() {

        $supplierSerialNo = $this->getSerialNo('supplierSerialNo', 'E-S-');

        return $supplierSerialNo;
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public function getInquirySerialNo() {

        $inquirySerialNo = $this->getSerialNo('inquirySerialNo', 'INQ_');

        return $inquirySerialNo;
    }

    /**
     * 获取生成的报价单流水号
     * @author liujf 2017-06-20
     * @return string $quoteSerialNo 报价单流水号
     */
    public function getQuoteSerialNo() {

        $quoteSerialNo = $this->getSerialNo('quoteSerialNo', 'QUO_');

        return $quoteSerialNo;
    }

    /**
     * 获取生成的报价单号
     * @author liujf 2017-06-24
     * @return string $quoteNo 报价单号
     */
    public function getQuoteNo() {

        $quoteNo = $this->getSerialNo('quoteNo', 'Q_');

        return $quoteNo;
    }

    /**
     * 根据流水号名称获取流水号
     * @param string $name 流水号名称
     * @param string $prefix 前缀
     * @author liujf 2017-06-20
     * @return string $code
     */
    private function getSerialNo($name, $prefix = '') {
        $time = date('Ymd');
        $duration = 3600 * 48;
        $createTimeName = $name . 'CreateTime';
        $stepName = $name . 'Step';
        $createTime = redisGet($createTimeName) ?: '19700101';
        if ($time > $createTime) {
            redisSet($stepName, 0, $duration);
            redisSet($createTimeName, $time, $duration);
        }
        $step = redisGet($stepName) ?: 0;
        $step ++;
        redisSet($stepName, $step, $duration);
        $code = $this->createSerialNo($step, $prefix);

        return $code;
    }

    /**
     * 生成流水号
     * @param string $step 需要补零的字符
     * @param string $prefix 前缀
     * @author liujf 2017-06-19
     * @return string $code
     */
    private function createSerialNo($step = 1, $prefix = '') {
        $time = date('Ymd');
        $pad = str_pad($step, 5, '0', STR_PAD_LEFT);
        $code = $prefix . $time . '_' . $pad;
        return $code;
    }

    /*
     * @desc 加载php公共配置文件
     *
     * @author liujf
     * @time 2018-01-25
     */

    public function loadCommonConfig() {
        $files = $commonConfig = [];
        searchDir(COMMON_CONF_PATH, $files);
        foreach ($files as $file) {
            if (preg_match('/.*\.php$/i', $file)) {
                $commonConfig = include $file;
                if (is_array($commonConfig)) {
                    C($commonConfig);
                }
            }
        }
    }

    /**
     * @desc 语言检查(检查浏览器支持语言，并自动加载语言包)
     *
     * @author liujf
     * @time 2018-01-25
     */
    public function checkLanguage() {
// 不开启语言包功能，仅仅加载框架语言文件直接返回
        if (!C('LANG_SWITCH_ON', null, false)) {
            return;
        }
        $langSet = C('DEFAULT_LANG');
        $varLang = C('VAR_LANGUAGE', null, 'l');
        $langList = C('LANG_LIST', null, 'zh');
// 启用了语言包功能
// 根据是否启用自动侦测设置获取语言选择
        if (C('LANG_AUTO_DETECT', null, true)) {
            $langParam = $this->getPut($varLang);
            $langHeader = getHeaders()[$varLang];
            $langCookie = $this->getCookie($varLang);
            $langCache = $this->_getLanguage();
            if (!empty($langParam)) {
                $langSet = $langParam; // 请求参数中设置了语言变量
                $this->_cacheLanguage($langSet);
            } else if (!empty($langHeader)) {
                $langSet = $langHeader; // 请求头信息中设置了语言变量
                $this->_cacheLanguage($langSet);
            } else if (!empty($langCookie)) {
                $langSet = $langCookie; // cookie中设置了语言变量
                $this->_cacheLanguage($langSet);
            } else if (isset($_GET[$varLang])) {
                $langSet = $_GET[$varLang]; // url中设置了语言变量
                $this->_cacheLanguage($langSet);
            } else if ($langCache) { // 获取上次用户的选择
                $langSet = $langCache;
            } else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) { // 自动侦测浏览器语言
                preg_match('/^([a-z\d\-]+)/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $matches);
                $langSet = explode('-', $matches[1])[0];
                $this->_cacheLanguage($langSet);
            }
            if (false === stripos($langList, $langSet)) { // 非法语言参数
                $langSet = C('DEFAULT_LANG');
            }
        }
// 定义当前语言
        define('LANG_SET', strtolower($langSet));
// 读取公共语言包
        $file = COMMON_PATH . DS . 'lang' . DS . LANG_SET . '.php';

        if (is_file($file)) {
            L(include $file);
        }
// 读取模块语言包


        $file = APPLICATION_PATH . DS . 'lang' . DS . LANG_SET . '.php';
        if (is_file($file)) {
            L(include $file);
        }

// 读取当前控制器语言包
        $file = APPLICATION_PATH . DS . 'lang' . DS . LANG_SET . DS . $this->getModuleName() . '.php';

        if (is_file($file)) {
            L(include $file);
        }
    }

    /**
     * @desc 语言缓存
     *
     * @param string $lang 语言
     * @return bool
     * @author liujf
     * @time 2018-01-28
     */
    private function _cacheLanguage($lang = 'zh') {
        return redisSet('erui_boss_language_' . $this->user['id'], $lang, 3600 * 24);
    }

    /**
     * @desc 获取语言缓存
     *
     * @return mixed
     * @author liujf
     * @time 2018-01-28
     */
    private function _getLanguage() {
        return redisGet('erui_boss_language_' . $this->user['id']);
    }

    /**
     * @param        $to            收信人手机号
     * @param        $action        操作说明 SUBMIT(询报价提交) REJECT(询报价退回)
     * @param        $receiver      收信人名称 如:买买提
     * @param        $serial_no     询单流程编码
     * @param        $from          发信人名称
     * @param string $areaCode      手机所属区号 默认86
     * @param int    $subType       短信发送方式  0普通文本 1模板
     * @param int    $groupSending  类型：0为单独发送，1为批量发送
     * @param string $useType       发送用途： 例如：Order、Customer、System等
     * @author 买买提
     * @return string
     */
    public function sendSms($to, $action, $receiver, $serial_no, $from, $in_node, $out_node, $areaCode = "86", $subType = 1, $groupSending = 0, $useType = "询报价系统") {

        if (empty($receiver)) {
            $this->jsonReturn(['code' => -104, 'message' => '收信人名字不能为空']);
        }

        if (empty($serial_no)) {
            $this->jsonReturn(['code' => -104, 'message' => '询单流程编码不能为空']);
        }

        $data = [
            'useType' => $useType,
            'to' => '["' . $to . '"]',
            'areaCode' => $areaCode,
            'subType' => $subType,
            'groupSending' => $groupSending,
        ];

        if ($action == "CREATE") {
            $data['tplId'] = '55047';
            $data['tplParas'] = '["' . $receiver . '","' . $from . '","' . $serial_no . '"]';
        } elseif ($action == "REJECT") {
            $data['tplId'] = '55048';
            $data['tplParas'] = '["' . $receiver . '","' . $serial_no . '","' . $from . '"]';
        }


        $response = json_decode(MailHelper::sendSms($data), true);

//记录短信
        if ($response['code'] == 200) {

            $smsLog = new SmsLogModel();
            $smsLog->add($smsLog->create([
                        'serial_no' => $serial_no,
                        'sms_id' => $response['message'],
                        'mobile' => $to,
                        'receiver' => $receiver,
                        'from' => $from,
                        'action' => $action,
                        'in_node' => $in_node,
                        'out_node' => $out_node,
                        'send_at' => date('Y-m-d H:i:s')
            ]));
        }

        return;
    }

    /**
     * 验证指定参数是否存在
     * @param string $params 初始的请求字段
     * @return array 验证后的请求字段
     */
    public function validateRequestParams($params = '') {
        $request = $this->getPut();
        unset($request['token']);

        if ($params) {
            $params = explode(',', $params);
            foreach ($params as $param) {
                if (empty($request[$param])) {
                    $this->jsonReturn(['code' => '-104', 'message' => '缺少[' . $param . ']参数']);
                }
            }
        }
        return $request;
    }

}
