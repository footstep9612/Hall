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

    /*
     * 初始化
     */

    public function init() {
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);
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
    public function getParam($name = null, $default = null) {

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
//GMV累计及区域占比 http://172.18.20.29/v3/hall/getGmv
//GMV趋势 http://172.18.20.29/v3/hall/getGmvTrend
//全球区域买家数量统计 http://172.18.20.29/v3/hall/getBuyer
//SKU数量及占比 http://172.18.20.29/v3/hall/getGoods
//平台流量趋势 http://172.18.20.29/v3/hall/getPageViewTrend
//海外仓分布点趋势 http://172.18.20.29/v3/hall/getWarehouse
//订单量统计 http://172.18.20.29/v3/hall/getOrders
//海外发运单趋势 http://172.18.20.29/v3/hall/getShipmentrend
//订单交付及时率对比趋势 http://172.18.20.29/v3/hall/getOrdersRratetrend


}
