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
//        ini_set("display_errors", "On");
//        error_reporting(E_ALL | E_STRICT);
//        $this->put_data = $jsondata = json_decode(file_get_contents("php://input"), true);
//        if ($this->getRequest()->getModuleName() == 'V1' &&
//                $this->getRequest()->getControllerName() == 'User' &&
//                in_array($this->getRequest()->getActionName(), ['login', 'register', 'es', 'kafka','excel'])) {
//
//        } else {
//
//            if (!empty($jsondata["token"])) {
//                $token = $jsondata["token"];
//            }
//            $data = $this->getRequest()->getPost();
//
//            if (!empty($data["token"])) {
//                $token = $data["token"];
//            }
//            $model = new UserModel();
//            if (!empty($token)) {
//                try {
//
//                    $tks = explode('.', $token);
//
//                    $tokeninfo = JwtInfo($token); //解析token
//
//
//                    $userinfo = $model->Userinfo("*", array("name" => $tokeninfo["account"]));
//                    if (empty($userinfo)) {
//                        echo json_encode(array("code" => "-104", "message" => "用户不存在"));
//                        exit;
//
//                    } else {
//                        $this->user = array(
//                            "user_main_id" => md5($userinfo["id"]) ,
//                            "username" => $tokeninfo["account"],
//                            "token" => $token, //token
//                        );
//                    }
//                } catch (Exception $e) {
//                    // LOG::write($e->getMessage());
//                    $this->jsonReturn($model->getMessage(UserModel::MSG_TOKEN_ERR));
//                    exit;
//                }
//            } else {
//                $this->jsonReturn($model->getMessage(UserModel::MSG_TOKEN_ERR));
//                exit;
//            }
//        }
    }

    protected function jsonReturn($data,$code=0,$message='', $type = 'JSON') {
        header('Content-Type:application/json; charset=utf-8');
        if($code !=0){
            exit(json_encode(array('code'=>$code,'message'=>$message)));
        }
        if(is_array($data) && !isset($data['code'])){
            $data['code']=0;
            $data['message'] = '成功';
        }
        exit(json_encode($data));
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
		$inquirySerialNo = $this->createSerialNo($inquirySerialNoStep, 'INQ_');
		
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
		$quoteSerialNo = $this->createSerialNo($quoteSerialNoStep, 'QUO_');
		
		return $quoteSerialNo;
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
		$pad  = str_pad($step, 5, '0', STR_PAD_LEFT);
		$code = $prefix . $time . '_' . $pad;
		return $code;
	}
}
