<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of UserController
 *
 * @author zyg
 */
class UserController extends PublicController {

    public function __init() {
        parent::__init();
    }

    /*
     * 用户列表
     * */

    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $data['deleted_flag'] = 'N';
        if (!empty($data['deleted_flag'])) {
            $where['deleted_flag'] = $data['deleted_flag'];
        }
        $where['lang'] = $this->lang;
        if (!empty($data['username'])) {
            $username = trim($data['username']);
            $where['username'] = $username;
        }
        if (!empty($data['group_id'])) {
            $where['group_id'] = trim($data['group_id']);
        }
        if (!empty($data['role_id'])) {
            $where['role_id'] = trim($data['role_id']);
        }
        if (!empty($data['role_no'])) {
            //$where['role_no'] = trim($data['role_no']);
            $role_no = explode(",", $data['role_no']);
            for ($i = 0; $i < count($role_no); $i++) {
                $where['role_no'] = $where['role_no'] . "'" . $role_no[$i] . "',";
            }
            $where['role_no'] = rtrim($where['role_no'], ",");
        }
        if (!empty($data['status'])) {
            $where['status'] = trim($data['status']);
        }
        if (!empty($data['gender'])) {
            $where['gender'] = trim($data['gender']);
        }
        if (!empty($data['employee_flag'])) {
            $where['employee_flag'] = trim($data['employee_flag']);
        }
        if (!empty($data['pageSize'])) {
            $where['num'] = trim($data['pageSize']);
        }
        if (!empty($data['mobile'])) {

            $where['mobile'] = trim($data['mobile']);
        }
        if (!empty($data['user_no'])) {

            $user_no = trim($data['user_no']);
            $where['user_no'] = $user_no;
        }
        if (!empty($data['name_user_no'])) {

            $where['name_user_no'] = trim($data['name_user_no']);
        }
        if (!empty($data['bn'])) {
            $pieces = explode(",", $data['bn']);
            for ($i = 0; $i < count($pieces); $i++) {
                $where['bn'] = $where['bn'] . "'" . $pieces[$i] . "',";
            }
            $where['bn'] = rtrim($where['bn'], ",");
        }
        if (!empty($data['role_name'])) {
            $where['role_name'] = trim($data['role_name']);
        }
        if (!empty($data['currentPage'])) {
            $where['page'] = intval($data['currentPage']) > 1 ? (intval($data['currentPage']) - 1) * $where['num'] : 0;
        }

        $user_modle = new UserModel();
        $data = $user_modle->getlist($where);
        $count = $user_modle->getcount($where);
        $status_count = $user_modle->getStatusCount($where);
        if (!empty($data)) {
            $datajson['code'] = 1;
            if ($count) {
                $datajson['count'] = $count[0]['num'];
                $datajson['disabled_count'] = $status_count['disabled_num'] ? $status_count['disabled_num'] : 0;
                $datajson['normal_count'] = $status_count['normal_num'] ? $status_count['normal_num'] : 0;
            } else {
                $datajson['count'] = 0;
            }
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
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

    public function usercountrybnredislistAction() {
        if (!redisExist(user_country_bn_redis_list)) {
            $user_modle = new UserModel();
            $data = $user_modle->getlist();
            $user_arr = [];
            foreach ($data as $k => $value) {
                $user_arr[$value['id']] = $value['country'];
            }
            redisSet('user_country_bn_redis_list', json_encode($user_arr), 600);
        } else {
            $user_arr = json_decode(redisGet("user_country_bn_redis_list"), true);
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

    /*
     * 用户角色列表
     *
     * */

    public function userroleAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        if ($data['user_id']) {
            $user_id = $data['user_id'];
        } else {
            $user_id = $this->user['id'];
        }
        $role_user_modle = new RoleUserModel();
        $data = $role_user_modle->userRole($user_id);
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function getUserRoleAction() {       //ww
        $data = json_decode(file_get_contents("php://input"), true);
        if ($data['user_id']) {
            $user_id = $data['user_id'];
        } else {
            $user_id = $this->user['id'];
        }
        $role_user_modle = new RoleUserModel();
        $data = $role_user_modle->getUserRole($user_id);
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['message'] = '用户角色列表';
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * 用户国家列表
     *
     * */

    public function usercountryAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        if ($data['user_id']) {
            $user_id = $data['user_id'];
        } else {
            $user_id = $this->user['id'];
        }
        $role_cuntry_modle = new CountryUserModel();
        $data = $role_cuntry_modle->userCountry($user_id);
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * 用户部门列表
     *
     * */

    public function usergroupAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        if ($data['user_id']) {
            $user_id = $data['user_id'];
        } else {
            $user_id = $this->user['id'];
        }
        $role_group_modle = new GroupUserModel();
        $data = $role_group_modle->getlist(['user_id' => $user_id], '');
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * 用户权限列表
     *
     * */

    public function userrolelistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        if (!empty($data['source'])) {
            $where['source'] = trim($data['source']);
        }
        $role_user_modle = new RoleUserModel();
        $data = $role_user_modle->userRoleList($this->user['id'], '', $where);
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * 用户列表
     *
     * */

    public function userrolelisttreeAction() {
        $condition = json_decode(file_get_contents("php://input"), true);
        $roleUserModel = new RoleUserModel();
        if (isset($condition['user_id'])) {
            $userId = $condition['user_id'];
        } else {
            $userId = $this->user['id'];
        }

        $condition['not_pid'] = redisExist('HOME_ID') ? redisGet('HOME_ID') : NULL;
        if (!$condition['not_pid']) {
            $condition['not_pid'] = (new UrlPermModel())->getMenuIdByName('首页');
            redisSet('HOME_ID', $condition['not_pid']);
        }
        if (!empty($condition['type']) && $condition['type'] === 'CHILD' && empty($condition['parent_id'])) {
            $data = (new UrlPermModel())->getDefault();
        } elseif ($condition['parent_id'] == $condition['not_pid']) {
            $data = (new UrlPermModel())->getDefault();
        } else {

            $data = $roleUserModel->getUserMenu($userId, $condition, $this->lang);
            if ($condition['only_one_level'] == 'Y') {
                $home = (new UrlPermModel())->getHome();
                $data = array_merge([$home], $data);
            }
        }
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * 用户列表
     *
     * */

    public function updatepasswordAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $arr['id'] = $this->user['id'];
        $arr['password_hash'] = md5($data['old_password']);
        $pwd = md5($data['password']);
        $user_modle = new UserModel();
        $data = $user_modle->infoList($arr);
        if ($data) {
//            $res = $user_modle->update_data($new_passwoer, $arr);
            $user_modle->where(array('id' => $data['id']))->save(array('password_hash' => $pwd));
            $datajson['code'] = 1;
            $datajson['message'] = '修改成功';
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '原密码错误!';
        }
        $this->jsonReturn($datajson);
    }

    /*
     * 用户详情
     * */

    public function infoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new UserModel();
        $res = $model->info($data['id']);
        if (!empty($res)) {
            unset($res['password_hash']);
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function createAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['mobile'])) {
            $arr['mobile'] = $data['mobile'];
            if (!isMobile($arr['mobile'])) {
                $this->jsonReturn(array("code" => "-101", "message" => "手机格式不正确"));
            }
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "手机不可以都为空"));
        }
        if (!empty($data['email'])) {
            $arr['email'] = $data['email'];
            if (!isEmail($arr['email'])) {
                $this->jsonReturn(array("code" => "-101", "message" => "邮箱格式不正确"));
            }
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "邮箱不可以都为空"));
        }
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "用户名不能为空"));
        }
        if (!empty($data['name_en'])) {
            $arr['name_en'] = $data['name_en'];
        }
        if (!empty($data['gender'])) {
            $arr['gender'] = $data['gender'];
        }
        if (!empty($data['mobile2'])) {
            $arr['mobile2'] = $data['mobile2'];
        }
        if (!empty($data['show_name'])) {
            $arr['show_name'] = $data['show_name'];
        }
        if (!empty($data['phone'])) {
            $arr['phone'] = $data['phone'];
        }
        if (!empty($data['ext'])) {
            $arr['ext'] = $data['ext'];
        }
        if (!empty($data['remarks'])) {
            $arr['remarks'] = $data['remarks'];
        }
        if (!empty($data['employee_flag'])) {
            $arr['employee_flag'] = $data['employee_flag'];
        } else {
            $arr['employee_flag'] = "I";
        }
        if (!empty($data['citizenship'])) {
            $arr['citizenship'] = $data['citizenship'];
        } else {
            $arr['citizenship'] = 'china';
        }
        $password = randStr(6);
        $arr['password_hash'] = md5($password);
        $model = new UserModel();
        if ($arr['employee_flag'] == "O") {
            $condition['page'] = 0;
            $condition['countPerPage'] = 1;
            $condition['employee_flag'] = 'O';
            $data_t = $model->getlist($condition); //($this->put_data);
            if ($data_t) {
                $no = substr($data_t[0]['user_no'], -1, 9);
                $no++;
            } else {
                $no = 1;
            }
            $temp_num = 1000000000;
            $new_num = $no + $temp_num;
            $real_num = date("Ymd") . substr($new_num, 1, 9); //即截取掉最前面的“1”
            $arr['user_no'] = $real_num;
        } else {
            if (!empty($data['user_no'])) {
                $arr['user_no'] = $data['user_no'];
            } else {
                $this->jsonReturn(array("code" => "-101", "message" => "用户编号不能为空"));
            }
        }
        $arr['created_by'] = $this->user['id'];
        $arr['user_no'] = $data['user_no'];
        $check = $model->Exist($arr);
        if ($check) {
            $this->jsonReturn(array("code" => "-101", "message" => "用户已存在"));
        }
        $res = $model->create_data($arr);
        if ($res) {
            if ($data['role_ids']) {
                $model_role_user = new RoleUserModel();
                $role_user_arr['user_id'] = $res;
                $role_user_arr['role_ids'] = $data['role_ids'];
                $model_role_user->update_role_datas($role_user_arr);
            }
            if ($data['group_ids']) {
                $model_group_user = new GroupUserModel();
                $group_user_arr['user_id'] = $res;
                $group_user_arr['group_ids'] = $data['group_ids'];
                $model_group_user->addGroup($group_user_arr);
            }
            if ($data['country_bns']) {
                $model_country_user = new CountryUserModel();
                $country_user_arr['user_id'] = $res;
                $country_user_arr['country_bns'] = $data['country_bns'];
                $model_country_user->addCountry($country_user_arr);
            }
            // $body = $this->getView()->render('login/email.html', $email_arr);
            send_Mail($arr['email'], '帐号创建成功', "密码：" . $password, $arr['name']);
        }
        if ($res) {
            $datajson['code'] = 1;
            $datajson['data'] = ['id' => $res];
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function resetpasswordAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['id'])) {
            $where['id'] = $data['id'];
            $user_modle = new UserModel();
            $info = $user_modle->info($data['id']);
            if (!$info) {
                $this->jsonReturn(array("code" => "-101", "message" => "用户id不正确"));
            }
            $password = randStr(6);
            $arr['password_hash'] = md5($password);
            $res = $user_modle->update_data($arr, $where);
            if (!empty($res)) {
                send_Mail($info['email'], '密码重置成功', "新密码：" . $password, $info['name']);
                $datajson['code'] = 1;
                $datajson['message'] = '成功';
            } else {
                $datajson['code'] = -104;
                $datajson['data'] = "";
                $datajson['message'] = '修改失败!';
            }
            $this->jsonReturn($datajson);
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "用户id不能为空"));
        }
    }

    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['password'])) {
            $arr['password_hash'] = md5($data['password']);
        }
        if (!empty($data['id'])) {
            $where['id'] = $data['id'];
        } else {
            $this->jsonReturn(array("code" => "-101", "message" => "用户id不能为空"));
        }
        if (!empty($data['email'])) {
            $arr['email'] = $data['email'];
            if (!isEmail($arr['email'])) {
                $this->jsonReturn(array("code" => "-101", "message" => "邮箱格式不正确"));
            }
        }
        if (!empty($data['mobile'])) {
            $arr['mobile'] = $data['mobile'];
            /* 去掉手机格式验证 修改于2018-2-24 19:55 张玉良
             * if (!isMobile($arr['mobile'])) {
              $this->jsonReturn(array("code" => "-101", "message" => "手机格式不正确"));
              } */
        }
        if (!empty($data['show_name'])) {
            $arr['show_name'] = $data['show_name'];
        }
        if (!empty($data['name'])) {
            $arr['name'] = $data['name'];
        }
        if (!empty($data['name_en'])) {
            $arr['name_en'] = $data['name_en'];
        }
        if (!empty($data['gender'])) {
            $arr['gender'] = $data['gender'];
        }
        if (!empty($data['mobile2'])) {
            $arr['mobile2'] = $data['mobile2'];
        }
        if (!empty($data['phone'])) {
            $arr['phone'] = $data['phone'];
        }
        if (!empty($data['ext'])) {
            $arr['ext'] = $data['ext'];
        }
        if (!empty($data['remarks'])) {
            $arr['remarks'] = $data['remarks'];
        }
        if (!empty($data['user_no'])) {
            $arr['user_no'] = $data['user_no'];
        }
        if (!empty($data['status'])) {
            $arr['status'] = $data['status'];
        }
        if (!empty($data['employee_flag'])) {
            $arr['employee_flag'] = $data['employee_flag'];
        }
        if (!empty($data['citizenship'])) {
            $arr['citizenship'] = $data['citizenship'];
        }
        if (!empty($data['deleted_flag'])) {
            $arr['deleted_flag'] = $data['deleted_flag'];
        }

        $model = new UserModel();
        $res = $model->update_data($arr, $where);


        if ($res !== false) {
            if (isset($data['role_ids'])) {
                $model_role_user = new RoleUserModel();
                $role_user_arr['user_id'] = $where['id'];
                $role_user_arr['role_ids'] = $data['role_ids'];
                $model_role_user->update_role_datas($role_user_arr);
            }
            if ($data['group_ids']) {
                $model_group_user = new GroupUserModel();
                $group_user_arr['user_id'] = $where['id'];
                $group_user_arr['group_ids'] = $data['group_ids'];
                $model_group_user->addGroup($group_user_arr);
            }
            if (isset($data['country_bns'])) {
                $model_country_user = new CountryUserModel();
                $country_user_arr['user_id'] = $where['id'];
                $country_user_arr['country_bns'] = $data['country_bns'];
                $model_country_user->addCountry($country_user_arr);
            }
            // $body = $this->getView()->render('login/email.html', $email_arr);
        }
        if ($res !== false) {
            redisExist('user_fastentrance_' . $where['id']) ? redisDel('user_fastentrance_' . $where['id']) : '';
            $this->delcache();
        }
        if ($res !== false) {
            $datajson['code'] = 1;
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }

    private function delcache() {
        $redis = new phpredis();
        $user_id = $GLOBALS['SSO_USER']['id'];
        $keys = $redis->getKeys('user.' . $user_id . '.*');

        $redis->delete($keys);
    }

    public function getRoleAction() {
        if ($this->user['id']) {
            $role_user = new RoleUserModel();
            $where['user_id'] = $this->user['id'];
            $data = $role_user->getRoleslist($where);
            $datajson = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $data
            );
            jsonReturn($datajson);
        } else {
            $datajson = array(
                'code' => -104,
                'message' => '用户验证失败',
            );
        }
    }

    /*
     * 根据部门和角色获取用户列表
     * 张玉良
     * 2017-11-02
     */

    public function getOrgUserListAction() {
        $data = json_decode(file_get_contents("php://input"), true);

        $org_modle = new OrgMemberModel();
        $results = $org_modle->getOrgUserlist($data);

        $this->jsonReturn($results);
    }

    /**
     * 根据地理区域获取国家
     * @return mixed
     *
     * @author 买买提
     * @time 2018-01-12 11:42:46
     */
    public function areaCountryAction() {

        $this->validateRequestParams();

        $where = ['deleted_flag' => 'N', 'status' => 'VALID', 'lang' => 'zh'];
        $field = 'bn,name';
        $region = (new MarketAreaModel)->where($where)->field($field)->select();

        foreach ($region as & $item) {
            $item['country_list'] = (new MarketAreaCountryModel)->alias('a')
                    ->join('erui_dict.country b ON a.country_bn=b.bn')
                    ->where(['market_area_bn' => $item['bn'], 'b.lang' => 'zh', 'b.deleted_flag' => 'N'])
                    ->field('b.name,b.bn')
                    ->select();
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $region
        ]);
    }

    /**
     * 根据国家简称获取国家名称
     * @return array
     *
     * @author 买买提
     * @time 2018-01-12 11:42:46
     */
    public function countryNameAction() {

        $condition = $this->validateRequestParams();

        if (!empty($condition['country_bns'])) {
            $countryNames = [];
            foreach (explode(',', $condition['country_bns']) as $country_bn) {
                $countryNames[] = (new CountryModel)->where(['bn' => $country_bn, 'lang' => 'zh'])->getField('name');
            }

            $this->jsonReturn([
                'code' => 1,
                'message' => '成功',
                'data' => $countryNames
            ]);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => ''
        ]);
    }

    /**
     * @desc 项目获取用户列表
     *
     * @author liujf
     * @time 2018-01-22
     */
    public function getObtainUserListAction() {
        $condition = json_decode(file_get_contents("php://input"), true);
        $userModel = new UserModel();
        $countryUserModel = new CountryUserModel();
        $countryModel = new CountryModel();
        $orgMemberModel = new OrgMemberModel();
        $orgModel = new OrgModel();
        $originChina = L('ORIGIN_CHINA');
        $originForeign = L('ORIGIN_FOREIGN');
        $field = 'id, user_no, name AS username, IF(citizenship = \'china\', \'' . $originChina . '\', \'' . $originForeign . '\') AS citizenship';
        $userList = $userModel->getList_($condition, $field);
        foreach ($userList as & $user) {
            $countryBnList = $countryUserModel->getUserCountry(['employee_id' => $user['id']]);
            $countryList = [];
            foreach ($countryBnList as $countryBn) {
                $countryName = $countryModel->where(['bn' => $countryBn, 'lang' => $this->lang, 'deleted_flag' => 'N'])->getField('name');
                if ($countryName)
                    $countryList[] = $countryName;
            }
            $user['country_name'] = implode(',', $countryList);
            $orgIdList = $orgMemberModel->where(['employee_id' => $user['id']])->getField('org_id', true);
            $orgList = [];
            foreach ($orgIdList as $orgId) {
                $org = $orgModel->field('name, name_en, name_es, name_ru')->where(['id' => $orgId, 'deleted_flag' => 'N'])->find();
                if ($org) {
                    switch ($this->lang) {
                        case 'zh' :
                            $orgList[] = $org['name'];
                            break;
                        case 'en' :
                            $orgList[] = $org['name_en'];
                            break;
                        case 'es' :
                            $orgList[] = $org['name_es'];
                            break;
                        case 'ru' :
                            $orgList[] = $org['name_ru'];
                            break;
                        default :
                            $orgList[] = $org['name'];
                    }
                }
            }
            $user['group_name'] = implode(',', $orgList);
        }
        if ($userList) {
            $res['code'] = 1;
            $res['message'] = L('SUCCESS');
            $res['data'] = $userList;
            $res['count'] = $userModel->getCount_($condition);
            $this->jsonReturn($res);
        } else {
            $this->setCode('-101');
            $this->setMessage(L('FAIL'));
            $this->jsonReturn();
        }
    }

    /**
     * @desc 首页快捷入口
     *
     * @author liujf
     * @time 2018-06-19
     */
    public function fastEntranceAction() {

        $redis_key = 'user_fastentrance_' . $this->user['id'];
        $data = null;
        if (!redisExist($redis_key)) {

            $roleUserModel = new RoleUserModel();
            $menu = $roleUserModel->getUserMenu($this->user['id']);
            $mapping = [
                'show_create_inquiry' => '询单管理',
                'show_create_order' => '订单列表',
                'show_create_buyer' => '客户信息管理',
                'show_create_visit' => '客户信息管理',
                'show_demand_feedback' => '客户需求反馈',
                'show_request_permission' => '授信管理',
                'show_supplier_check' => '供应商审核',
                'show_goods_check' => 'SPU审核'
            ];
            foreach ($mapping as $k => $v) {
                $data[$k]['show'] = 'N';
            }
            $this->_scanMenu($menu, $mapping, $data);
            redisSet($redis_key, json_encode($data), 360);
        } else {

            $data = json_decode(redisGet($redis_key), true);
        }
        $this->jsonReturn([
            'code' => 1,
            'message' => L('SUCCESS'),
            'data' => $data
        ]);
    }

    /**
     * @desc 扫描菜单
     *
     * @param array $menu 菜单数据
     * @param array $mapping 菜单映射
     * @param array $data 需处理的数据
     * @author liujf
     * @time 2018-06-19
     */
    private function _scanMenu($menu, $mapping, &$data) {
        $urlPermModel = new UrlPermModel();
        foreach ($menu as $item) {
            foreach ($mapping as $k => $v) {
                if ($item['fn'] == $v) {
                    $data[$k]['show'] = 'Y';
                    $data[$k]['parent_id'] = $urlPermModel->getOneLevelMenuId($item['func_perm_id']);
                }
            }
            if (isset($item['children'])) {
                $this->_scanMenu($item['children'], $mapping, $data);
            }
        }
    }

    public function quickStartMenuAction() {
        $request = $this->validateRequestParams();

        $user_id = isset($request['user_id']) ? $request['user_id'] : $this->user['id'];
        $data = (new RoleUserModel)->userRoleList($user_id, 0, $where);
//p($data);
        $response = $this->restoreUserRoleSource($data);
        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => [
                'flag' => $response
            ]
        ]);
    }

    /**
     * 获取用户菜单种类数
     * @param array $data
     * @return int
     * @author 买买提
     */
    private function restoreUserRoleSource(array $data) {

        $restoredData = [];
        foreach ($data as $item) {
            $restoredData[] = $item['source'];
        }

        sort($restoredData);

//BOSS DATA_REPORT ORDER
        $string = implode(',', array_unique($restoredData));

        switch ($string) {
            case 'BOSS' : return [1];
                break;
            case 'DATA_REPORT' : return [2];
                break;
            case 'ORDER' : return [3];
                break;
            case 'BOSS,DATA_REPORT' : return [1, 2];
                break;
            case 'BOSS,ORDER' : return [1, 3];
                break;
            case 'DATA_REPORT,ORDER' : return [2, 3];
                break;
            case 'BOSS,DATA_REPORT,ORDER' : return [1, 2, 3];
                break;
            default : return [];
                break;
        }
    }

}
