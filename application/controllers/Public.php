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
        error_reporting(E_ERROR | E_STRICT);
        $this->put_data = $jsondata = json_decode(file_get_contents("php://input"), true);
        if ($this->getRequest()->getModuleName() == 'V1' && 
                $this->getRequest()->getControllerName() == 'User' &&
                in_array($this->getRequest()->getActionName(), ['login', 'register','es'])) {

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


                    $userinfo = $model->Userinfo("*", array("username" => $tokeninfo["account"]));
                    if (empty($userinfo)) {
                        echo json_encode(array("code" => "-104", "message" => "用户不存在"));
                        exit;
                        $data = array(
                            "username" => $tokeninfo["account"]
                        );
                        $user_main_id = $model->UserCreate($data);
                        $this->user = array(
                            "user_main_id" => $user_main_id,
                            "username" => $tokeninfo["account"],
                            "token" => $token, //token
                        );
                    } else {
                        $this->user = array(
                            "user_main_id" => $userinfo["id"],
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

    protected function jsonReturn($data, $type = 'JSON') {


        header('Content-Type:application/json; charset=utf-8');
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
}
