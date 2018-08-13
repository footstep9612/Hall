<?php
class UserController extends PublicController {

    public function __init() {
        parent::__init();
    }
    public function crmlistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $data['lang'] = $this->lang;
//        $data['created_by'] = $this->user['id'];

        $user = new UserModel();
        $res = $user->crmlist($data);
        $datajson['code'] = 1;
        $datajson['message'] = '数据信息';
        $datajson['data'] = $res;
        $this->jsonReturn($datajson);
    }
    public function userredislistAction() {
        if (!redisExist(user_redis_list)) {
            $user_modle = new UserModel();
            $data = $user_modle->getlist();
            $user_arr = [];
            foreach ($data as $k => $value) {
                $user_arr[$value['id']] = $value['name'];
            }
            redisSet('user_redis_list', json_encode($user_arr), 600);
        } else {
            $user_arr = json_decode(redisGet("user_redis_list"), true);
        }
        if (!empty($user_arr)) {
            $datajson['code'] = 1;
            $datajson['data'] = $user_arr;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }
}
