<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/5/15
 * Time: 10:48
 */

/**
 * Class PublicController
 * 全局方法
 */
abstract class SupplierpublicController extends Yaf_Controller_Abstract {

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
        $lang = $this->header('lang', 'zh');
        $this->setLang($lang);
        if ($this->token) {
            $this->_token();
        }
        Log::write($this->getRequest()->getControllerName(), Log::INFO);
        Log::write($this->getRequest()->getActionName(), Log::INFO);
        Log::write(json_encode($this->getPut()), Log::INFO);
    }

    protected function _getUser() {
        $token = $this->header('supplier_token');
        if (!$token) {
            $token = $this->getPut('supplier_token');
        }
        if (!$token) {
            $token = $this->getPost('supplier_token');
        }

        if (!empty($token)) {
            $tks = explode('.', $token);
            $tokeninfo = JwtInfo($token); //解析token
            $userinfo = json_decode(redisGet('supplier_user_info_' . $tokeninfo['id']), true);

            if (!empty($userinfo)) {
                $this->user = array(
                    "supplier_id" => $userinfo["supplier_id"],
                    "supplier_email" => $userinfo["supplier_email"],
                    "id" => $userinfo["id"],
                    "supplier_token" => $token,
                );
                $this->_setUid($userinfo);
                redisSet('supplier_user_info_' . $tokeninfo['id'], json_encode($userinfo), 18000);
            }
        }
    }

    protected function _token() {
        $this->put_data = $this->getPut();
        $token = $this->header('supplier_token');
        if (!$token) {
            $token = $this->getPut('supplier_token');
        }
        if (!$token) {
            $token = $this->getPost('supplier_token');
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
                    "supplier_id" => $userinfo["supplier_id"],
                    "supplier_email" => $userinfo["supplier_email"],
                    "id" => $userinfo["id"],
                    "supplier_token" => $token, //token
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
        if (!defined('SUID') && $userinfo) {
            define('SUID', $userinfo["supplier_id"]);
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

    public function setLang($lang = 'zh') {
        $this->lang = $lang;
    }

    public function setvalue($name, $value) {
        $this->send[$name] = $value;
    }

    /*
     * 获取语言
     */

    public function getLang() {
        return $this->lang ? $this->lang : $this->getPut('lang', 'zh');
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