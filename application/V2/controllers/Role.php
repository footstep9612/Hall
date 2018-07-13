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
class RoleController extends PublicController {

    public function __init() {
        //   parent::__init();
    }

    public function roleListAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new RoleModel();
        $res = $model->getRoleList($data);
        $dataJson['code '] = 1;
        $dataJson['message'] = '角色数据';
        $dataJson['data'] = $res;
        $this->jsonReturn($dataJson);
    }

    public function moveRoleAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $model = new RoleModel();
        $res = $model->moveRole($data);
        if ($res) {
            $dataJson['code '] = 1;
            $dataJson['message'] = '成功';
        } else {
            $dataJson['code '] = 0;
            $dataJson['message'] = '失败';
        }
        $this->jsonReturn($dataJson);
    }

    public function roleConfAction() {
//        $data = json_decode(file_get_contents("php://input"), true);
        $model = new RoleModel();
        $res = $model->sortRole();
        $dataJson['code'] = 1;
        $dataJson['message'] = '角色配置列表';
        $dataJson['data'] = $res;
        $this->jsonReturn($dataJson);
    }

    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if (!empty($data['name'])) {
            $where['role.name'] = array('like', '%' . $data['name'] . '%');
        }
        if (!empty($data['role_group'])) {
            if ($data['role_group'] == 'other') {
                $map['role.role_group'] = ['notin', ['sys', 'admin', 'inquiry']];
                $map[] = 'ISNULL(role.role_group)';
                $map['_logic'] = 'or';
                $where['_complex'] = $map;
            } else {
                $where['role.role_group'] = $data['role_group'];
            }
        }
        if (!empty($data['role_no'])) {
            $where['role.role_no'] = $data['role_no'];
        }
        if (!empty($data['pageSize'])) {
            $limit['num'] = $data['pageSize'];
        }
        if (!empty($data['currentPage'])) {
            $limit['page'] = ($data['currentPage'] - 1) * $limit['num'];
        }
        //判断用户可分配权限
        if ($data['is_show'] == 1) {
            if ($this->user['id'] != 1) {
                $where['role.admin_show'] = 'N';
            }
        }
        $where['role.deleted_flag'] = "N";
        $where['role.attr_id'] = ['neq', 0];
        $model_rolo = new RoleModel();
        $data = $model_rolo->getlist($where, $limit);

        if ($limit) {
            $count = $model_rolo->getcount($where);
            $datajson['count'] = $count;
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

    public function roleurllistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $model_url_perm = new UrlPermModel();
        if ($data['id']) {
            $arr['id'] = $data['id'];
        } else {
            $datajson['code'] = -102;
            $datajson['message'] = '角色id不能为空!';
            $this->jsonReturn($datajson);
        }
        $arr['parent_id'] = 0;
        $model_rolo = new RoleModel();
        $arr_data = $model_rolo->getRoleslist($arr['id']);
        $res = $this->get_roleurlperm_children($arr_data, 0);
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['data'] = $res;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = $data;
            $datajson['message'] = '数据为空!';
        }

        $this->jsonReturn($datajson);
    }

    //递归获取子记录
    function get_roleurlperm_children($a, $pid = null, $employee = null) {
        if (!$pid) {
            $pid = $a[0]['parent_id'];
        }
        $tree = array();
        foreach ($a as $v) {
            $v['check'] = false;
            if ($v['parent_id'] == $pid) {
                $v['children'] = $this->get_roleurlperm_children($a, $v['func_perm_id'], $employee); //递归获取子记录
                if ($v['children'] == null) {
                    unset($v['children']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    public function roleuserAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        $id = $data['id'];
        if (empty($id)) {
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model_role_user = new RoleUserModel();
        $data = $model_role_user->getRolesUserlist($id);
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = $data;
            $datajson['message'] = '数据为空!';
        }

        $this->jsonReturn($datajson);
    }

    public function roleuserdeleteAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        if (empty($id)) {
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model_role_user = new RoleUserModel();
        $res = $model_role_user->delete_data($id);
        if ($res) {
            $datajson['code'] = 1;
            $datajson['message'] = '删除成功!';
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '删除失败!';
        }

        $this->jsonReturn($datajson);
    }

    public function infoAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        if (empty($id)) {
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model_rolo = new RoleModel();
        $data = $model_rolo->detail($id);
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = $data;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function permlistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        if (empty($id)) {
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model_rolo = new RoleModel();
        $data = $model_rolo->getRoleslist($id);
        if (!empty($data)) {
            $datajson['code'] = 1;
            $datajson['data'] = $data;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = $data;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

    public function createAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data)) {
            $datajson['code'] = -101;
            $datajson['message'] = '数据不可为空!';
            $this->jsonReturn($datajson);
        }
        if (empty($data['name'])) {
            $datajson['code'] = -101;
            $datajson['message'] = '权限名称不可为空!';
            $this->jsonReturn($datajson);
        }
        if (empty($data['attr_id'])) {  //属性id
            $datajson['code'] = 0;
            $datajson['message'] = '请选择角色的归属分类';
            $this->jsonReturn($datajson);
        } else {
            $role_arr['attr_id'] = $data['attr_id'];
        }
        $model_rolo = new RoleModel();
        if (!empty($data['role_no'])) {
            $roleinfo = $model_rolo->where(['role_no' => $data['role_no'], 'deleted_flag' => 'N'])->find();
            if ($roleinfo) {
                $datajson['code'] = MSG::ERROR_PARAM;
                $datajson['message'] = '角色编号已存在!';
                $this->jsonReturn($datajson);
            }
        }
        $data['created_by'] = $this->user['id'];
        $id = $model_rolo->create_data($data);
        if (!empty($id)) {
            if ($data['url_perm_ids']) {
                $model_role_access_perm = new RoleAccessPermModel();
                $role_arr['url_perm_ids'] = $data['url_perm_ids'];
                $role_arr['role_id'] = $id;
                $model_role_access_perm->update_datas($role_arr);
            }
            if ($data['role_user_ids']) {
                $model_role_user = new RoleUserModel();
                $role_user_arr['role_user_ids'] = $data['role_user_ids'];
                $role_user_arr['role_id'] = $id;
                $model_role_user->update_datas($role_user_arr);
            }
            $datajson['code'] = 1;
            $datajson['data']['id'] = $id;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = $data;
            $datajson['message'] = '添加失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function addroleAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data['user_id'])) {
            $datajson['code'] = -101;
            $datajson['message'] = '用户id不可为空!';
            $this->jsonReturn($datajson);
        }
        $user_id = $data['user_id'];
        if (!empty($user_id)) {
            if ($data['role_ids']) {
                $model_role_user = new RoleUserModel();
                $role_user_arr['user_id'] = $user_id;
                $role_user_arr['role_ids'] = $data['role_ids'];
                $model_role_user->update_role_datas($role_user_arr);
            }
            $this->delcache();
            $datajson['code'] = 1;
            $datajson['message'] = "成功";
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = $data;
            $datajson['message'] = '添加失败!';
        }
        $this->jsonReturn($datajson);
    }

    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        if (empty($data)) {
            $datajson['code'] = -101;
            $datajson['message'] = '数据不可为空!';
            $this->jsonReturn($datajson);
        }
        if (empty($data['id'])) {
            $datajson['code'] = -101;
            $datajson['message'] = '缺少主键!';
            $this->jsonReturn($datajson);
        } else {
            $where['id'] = $data['id'];
            $role_arr['role_id'] = $data['id'];
        }
        if (empty($data['attr_id'])) {  //属性id
            $datajson['code'] = 0;
            $datajson['message'] = '请选择角色的归属分类';
            $this->jsonReturn($datajson);
        } else {
            $role_arr['attr_id'] = $data['attr_id'];
        }
        $model_rolo = new RoleModel();
        if (!empty($data['role_no'])) {
            $roleinfo = $model_rolo->where(['role_no' => $data['role_no'], 'id' => ['neq', $data['id']], 'deleted_flag' => 'N'])->find();
            if ($roleinfo) {
                $datajson['code'] = MSG::ERROR_PARAM;
                $datajson['message'] = '角色编号已存在!';
                $this->jsonReturn($datajson);
            }
        }

        $model_rolo->update_data($data, $where);
        $model_role_access_perm = new RoleAccessPermModel();
        $role_arr['url_perm_ids'] = $data['url_perm_ids'];
        $model_role_access_perm->update_datas($role_arr);
//        if(isset( $data['role_user_ids'])){
//            $model_role_user = new RoleUserModel();
//            $role_user_arr['role_id'] = $data['id'];
//            $role_user_arr['role_user_ids'] = $data['role_user_ids'];
//            $model_role_user->update_datas($role_user_arr);
//        }

        $this->delcache();
        $datajson['code'] = 1;
        $datajson['message'] = '操作完成!';
        $this->jsonReturn($datajson);
    }

    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('user.');
        $redis->delete($keys);
    }

    public function deleteAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $id = $data['id'];
        if (empty($id)) {
            $datajson['code'] = -101;
            $datajson['message'] = 'id不可以都为空!';
            $this->jsonReturn($datajson);
        }
        $model_rolo = new RoleModel();
        $re = $model_rolo->delete_data($id);
        if ($re > 0) {
            $this->delcache();
            $datajson['code'] = 1;
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '数据为空!';
        }
        $this->jsonReturn($datajson);
    }

}
