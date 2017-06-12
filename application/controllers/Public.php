<?php

/**
 * Class PublicController
 * 全局方法
 */
abstract class PublicController extends Yaf_Controller_Abstract {

    protected $user;

    /*
     * 初始化
     */

    public function init() {
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);
        $jsondata = json_decode(file_get_contents("php://input"), true);
        if (!empty($jsondata["token"])) {
            $token = $jsondata["token"];
        }
        $data = $this->getRequest()->getPost();
        if (!empty($data["token"])) {
            $token = $data["token"];
        }
        if (!empty($token)) {
            try {

                $tks = explode('.', $token);

                $tokeninfo = JwtInfo($token); //解析token

                $model = new UsermainModel();
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
                echo json_encode(array("code" => "-101", "message" => $e->getMessage()));
                exit;
            }
        } else {
            echo json_encode(array("code" => "-101", "message" => "缺少token"));
            exit;
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
    public function getlist($condition = []);

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($code = '', $id = '', $lang = '');

    /**
     * 删除数据
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return bool
     * @author zyg
     */
    public function delete($code = '', $id = '', $lang = '');

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return bool
     * @author zyg
     */
    public function update($upcondition = []);

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create($createcondition = []);
}
