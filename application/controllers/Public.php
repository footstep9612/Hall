<?php

/**
 * Class PublicController
 * 全局方法
 */
abstract class PublicController extends Yaf_Controller_Abstract {

    protected $user;
    protected $put_data = [];

    /*
     * 初始化
     */

    public function init() {
        ini_set("display_errors", "On");
        error_reporting(E_ALL | E_STRICT);
        $this->put_data = $jsondata = json_decode(file_get_contents("php://input"), true);
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


                    $userinfo = $model->Userinfo("*", array("name" => $tokeninfo["account"]));
                    if (empty($userinfo)) {
                        echo json_encode(array("code" => "-104", "message" => "用户不存在"));
                        exit;
                    } else {
                        $this->user = array(
                            "user_main_id" => md5($userinfo["id"]),
                            "username" => $tokeninfo["account"],
                            "token" => $token, //token
                        );
                    }
                } catch (Exception $e) {
                    // LOG::write($e->getMessage());
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

    protected function jsonReturn($data, $type = 'JSON') {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /*
     *  获取put过来的数据
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
	 * 获取生成的询价单流水号
	 * @author liujf 2017-06-19
	 * @return string $inquirySerialNo 询价单流水号
	 */
	public function getInquirySerialNo() {
		$time = date('Ymd');
		$duration = 3600 * 48;
		$inquirySerialNoCreateTime = redisGet('inquirySerialNoCreateTime') ? : '19700101';
		if ($time > $inquirySerialNoCreateTime) {
			redisSet('inquirySerialNoStep', 0, $duration);
			redisSet('inquirySerialNoCreateTime', $time, $duration);
		}
		$inquirySerialNoStep = redisGet('inquirySerialNoStep') ? : 0;
		$inquirySerialNoStep ++;
		redisSet('inquirySerialNoStep', $inquirySerialNoStep, $duration);
		$inquirySerialNo = $this->createSerialNo('INQ_', $inquirySerialNoStep);
		
		return $inquirySerialNo;
	}
	
	/**
	 * 获取生成的报价单流水号
	 * @author liujf 2017-06-19
	 * @return string $quoteSerialNo 报价单流水号
	 */
	public function getQuoteSerialNo() {
		$time = date('Ymd');
		$duration = 3600 * 48;
		$quoteSerialNoCreateTime = redisGet('quoteSerialNoCreateTime') ? : '19700101';
		if ($time > $quoteSerialNoCreateTime) {
			redisSet('quoteSerialNoStep', 0, $duration);
			redisSet('quoteSerialNoCreateTime', $time, $duration);
		}
		$quoteSerialNoStep = redisGet('quoteSerialNoStep') ? : 0;
		$quoteSerialNoStep ++;
		redisSet('inquirySerialNoStep', $quoteSerialNoStep, $duration);
		$quoteSerialNo = $this->createSerialNo('QUO_', $quoteSerialNoStep);
		
		return $quoteSerialNo;
	}
	
	/**
	 * 生成流水号
	 * @param string $prefix 前缀
	 * @param string $step 需要补零的字符
	 * @author liujf 2017-06-19
	 * @return string $code
	 */
	private function createSerialNo($prefix = '', $step = 1) {
		$time = date('Ymd');
		$pad  = str_pad($step, 5, '0', STR_PAD_LEFT);
		$code = $prefix . $time . '_' . $pad;
		return $code;
	}
}
