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
        ini_set("display_errors", "off");
        error_reporting(E_ALL | E_STRICT);
        $this->put_data = $jsondata = json_decode(file_get_contents("php://input"), true);
        if ($this->getRequest()->getModuleName() == 'V1' &&
                $this->getRequest()->getControllerName() == 'User' &&
                in_array($this->getRequest()->getActionName(), ['login', 'register', 'es', 'kafka','excel'])) {

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
                    $userinfo = json_decode(redisGet('user_info_'.$tokeninfo['id']) ,true);
                    if (empty($userinfo)) {
                        echo json_encode(array("code" => "-104", "message" => "用户不存在"));
                        exit;

                    } else {
                        $this->user = array(
                            "id" =>$userinfo["id"] ,
                            "name" => $tokeninfo["name"],
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
		$createTime = redisGet($createTimeName) ? : '19700101';
		if ($time > $createTime) {
			redisSet($stepName, 0, $duration);
			redisSet($createTimeName, $time, $duration);
		}
		$step = redisGet($stepName) ? : 0;
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
		$pad  = str_pad($step, 5, '0', STR_PAD_LEFT);
		$code = $prefix . $time . '_' . $pad;
		return $code;
	}
}
