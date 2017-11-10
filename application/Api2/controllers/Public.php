<?php

/**
 * Class PublicController
 * 全局方法
 */
abstract class PublicController extends Yaf_Controller_Abstract {

    protected $user;
    protected $put_data = [];
    protected $headers = [];
    protected $code = "1";
    protected $send = [];
    protected $message = '';
    protected $lang = '';
    protected $token = true;

    /*
     * 初始化
     */

    public function init() {
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);
        $lang = $this->header('lang', 'en');
        $this->setLang($lang);
        if ($this->token) {
            $this->_token();
        }
    }

    protected function _getUser() {
        $token = $this->header('token');
        if (!$token) {
            $token = $this->getPut('token');
        }
        if (!$token) {
            $token = $this->getPost('token');
        }

        if (!empty($token)) {
            $tks = explode('.', $token);
            $tokeninfo = JwtInfo($token); //解析token
            $userinfo = json_decode(redisGet('shopmall_user_info_' . $tokeninfo['id']), true);

            if (!empty($userinfo)) {
                $this->user = array(
                    "buyer_id" => $userinfo["buyer_id"],
                    "user_name" => $tokeninfo["user_name"],
                    "email" => $userinfo["email"],
                    "id" => $userinfo["id"],
                    "token" => $token, //token
                );
                $this->_setUid($userinfo);
                redisSet('shopmall_user_info_' . $tokeninfo['id'], json_encode($userinfo), 18000);
            }
        }
    }

    protected function _token() {
        $this->put_data = $this->getPut();
        $token = $this->header('token');
        if (!$token) {
            $token = $this->getPut('token');
        }
        if (!$token) {
            $token = $this->getPost('token');
        }

        if (!empty($token)) {
            $tks = explode('.', $token);
            $tokeninfo = JwtInfo($token); //解析token

            $userinfo = json_decode(redisGet('shopmall_user_info_' . $tokeninfo['id']), true);

            if (empty($userinfo)) {
                echo json_encode(array("code" => "-104", "message" => "用户不存在"));
                exit;
            } else {
                $this->user = array(
                    "buyer_id" => $userinfo["buyer_id"],
                    "user_name" => $tokeninfo["user_name"],
                    "email" => $userinfo["email"],
                    "id" => $userinfo["id"],
                    "token" => $token, //token
                );
                $this->_setUid($userinfo);
            }
        } else {
            echo json_encode(array("code" => "-104", "message" => "token不存在"));
            exit;
        }
    }

    /**
     * 设置或者获取当前的Header
     * @access public
     * @param string|array  $name header名称
     * @param string        $default 默认值
     * @return string
     */
    public function header($name = '', $default = null) {
        if (empty($this->header)) {
            $header = [];
            if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
                $header = $result;
            } else {
                $server = $this->server ?: $_SERVER;
                foreach ($server as $key => $val) {
                    if (0 === strpos($key, 'HTTP_')) {
                        $key = str_replace('_', '-', strtolower(substr($key, 5)));
                        $header[$key] = $val;
                    }
                }
                if (isset($server['CONTENT_TYPE'])) {
                    $header['content-type'] = $server['CONTENT_TYPE'];
                }
                if (isset($server['CONTENT_LENGTH'])) {
                    $header['content-length'] = $server['CONTENT_LENGTH'];
                }
            }
            $this->header = array_change_key_case($header);
        }
        if (is_array($name)) {
            return $this->header = array_merge($this->header, $name);
        }
        if ('' === $name) {
            return $this->header;
        }
        $name = str_replace('_', '-', strtolower($name));
        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }

    /*
     * 设置UID
     * @param array $userinfo 用户信息
     * return void;
     */

    protected function _setUid($userinfo) {
        if (!defined('UID') && $userinfo) {
            define('UID', $userinfo["buyer_id"]);
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
        return $this->lang ? $this->lang : $this->getPut('lang', 'en');
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
            }
            $this->send['code'] = $this->getCode();
            if ($this->send['code'] == "1" && !$this->getMessage()) {
                $this->send['message'] = '成功!';
            } elseif (!$this->getMessage()) {
                $this->send['message'] = '未知错误!';
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
        if ($name) {
            $data = isset($this->put_data [$name]) ? $this->put_data [$name] : $default;
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
    public function getParam($name, $default = null) {

        return $this->getRequest()->getParam($name, $default);
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
            return $this->getRequest()->get($name, $default);
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
    public function getQuery($name, $default = null) {

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
    public function getPost($name, $default = null) {

        return $this->getRequest()->getPost($name, $default);
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

    /**
     * @desc 获取当前用户信息
     * @author liujf 2017-07-01
     * @return array
     */
    public function getUserInfo() {
        $userModel = new UserModel();
        return $userModel->info($this->user['id']);
    }

    /**
     * @desc 记录审核日志
     *
     * @param array $condition
     * @param object $model
     * @return array
     * @author liujf
     * @time 2017-08-10
     */
    public function addCheckLog($condition, &$model) {
        if (is_object($model)) {
            $inquiryCheckLogModel = &$model;
        } else {
            $inquiryCheckLogModel = new InquiryCheckLogModel();
        }
        $time = date('Y-m-d H:i:s');

        $inquiryIdArr = explode(',', $condition['inquiry_id']);

        $checkLogList = $checkLog = array();

        foreach ($inquiryIdArr as $inquiryId) {
            $data = $condition;
            $data['op_id'] = $this->user['id'];
            $data['inquiry_id'] = $inquiryId;
            $data['created_by'] = $this->user['id'];
            $data['created_at'] = $time;

            $checkLog = $inquiryCheckLogModel->create($data);

            $checkLogList[] = $checkLog;
        }

        return $inquiryCheckLogModel->addAll($checkLogList);
    }

}
