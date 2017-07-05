<?php

/**
 * Class PublicController
 * 全局方法
 */
abstract class PublicController extends Yaf_Controller_Abstract {

    protected $user;
    protected $put_data = [];
    protected $code = "1";
    protected $message = '';
    protected $lang = '';

    /*
     * 初始化
     */

    public function init() {
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);
        $this->put_data = $jsondata = $data = json_decode(file_get_contents("php://input"), true);
        $lang = $this->getPut('lang', 'en');
        $this->setLang($lang);
        if ($this->getRequest()->getModuleName() == 'V1' &&
                $this->getRequest()->getControllerName() == 'User' &&
                in_array($this->getRequest()->getActionName(), ['login', 'register', 'es', 'kafka', 'excel'])) {
            
        } else {

            if (!empty($jsondata["token"])) {
                $token = $jsondata["token"];
            }
            $data = $this->getRequest()->getPost();

            if (!empty($data["token"])) {
                $token = $data["token"];
            }
            $model = new UserModel();
            if (!empty($token)) {
                try {
                    $tks = explode('.', $token);
                    $tokeninfo = JwtInfo($token); //解析token
                    $userinfo = json_decode(redisGet('user_info_' . $tokeninfo['id']), true);

                    if (empty($userinfo)) {
                        echo json_encode(array("code" => "-104", "message" => "用户不存在"));
                        exit;
                    } else {
                        $this->user = array(
                            "id" => $userinfo["id"],
                            "name" => $tokeninfo["name"],
                            "token" => $token, //token
                        );
                    }
                } catch (Exception $e) {
                    LOG::write($e->getMessage());
                    $this->jsonReturn($model->getMessage(UserModel::MSG_TOKEN_ERR));
                    exit;
                }
            } else {
                $this->jsonReturn($model->getMessage(UserModel::MSG_TOKEN_ERR));
                exit;
            }
        }
    }

    public function __call($method, $args) {
        $data['code'] = -1;
        $data['message'] = 'Action :There is no method list4Action ';
        $this->jsonReturn($data);
    }

    /*
     * 设置语言
     */

    public function setLang($lang = 'en') {
        $this->lang = $lang;
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
                $send['data'] = $data;
            }

            $send['code'] = $this->getCode();

            if ($send['code'] == "1" && !$this->getMessage()) {
                $send['message'] = '成功!';
            } elseif (!$this->getMessage()) {
                $send['message'] = '未知错误!';
            } else {
                $send['message'] = $this->getMessage();
            }

            exit(json_encode($send, JSON_UNESCAPED_UNICODE));
        }
    }

    /*
     *  获取put过来的数据 待过滤优化
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */

    public function getPut($name, $default = null) {
        $data = isset($this->put_data [$name]) ? $this->put_data [$name] : $default;
        // return array_walk_recursive($data, 'think_filter');
        return $data;
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
    public function get($name, $default = null) {

        return $this->getRequest()->get($name, $default);
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
	 * @author liujf 2017-07-01
	 * @param array $condition 插入数据
	 * @return array
	 */
	public function addApproveLog($condition) {
		$approveLogModel = new ApproveLogModel();
		$user = $this->getUserInfo();
		$condition['approver_id'] = $user['id'];
		$condition['approver'] = $user['name'];
		$condition['created_at'] = date('Y-m-d H:i:s');
		
		return $approveLogModel->addData($condition);
	}

}
