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
class GroupController extends PublicController {

    public function __init() {
        //   parent::__init();
    }

    //递归获取子记录
    function get_group_children($a, $pid = null, $employee = null) {
        if (!$pid) {
            $pid = $a[0]['parent_id'];
        }
        $tree = array();
        $limit = [];
        if ($employee) {
            $user_modle = new UserModel();
        }
        $i = 0;
        foreach ($a as $v) {
            $model_group = new GroupModel();
            if ($v['parent_id'] == $pid) {
                $v['children'] = $this->get_group_children($model_group->getlist(['parent_id' => $v['id']], $limit), $v['id'], $employee); //递归获取子记录
                if ($v['children'] == null) {
                    if ($employee) {
                        $where_user['group_id'] = $v['id'];
                        $v['children'] = $user_modle->getlist($where_user);
                    }
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    //递归获取子记录
    function get_group_tree($a, $pid = null) {
        if (!$pid) {
            $pid = $a[0]['parent_id'];
        }
        $tree = array();
        $limit = [];
        foreach ($a as $v) {
            $model_group = new GroupModel();
            if ($v['parent_id'] == $pid) {
                $tree[] = $v;
                $list = $this->get_group_tree($model_group->getlist(['parent_id' => $v['id']], $limit), $v['id']); //递归获取子记录
                if ($list != null) {
                    for ($i = 0; $i < count($list); $i++) {
                        $list[$i]['name'] = '|-' . $list[$i]['name'];
                        $tree[] = $list[$i];
                    }
                }
            }
        }
        return $tree;
    }

    public function grouptreelistAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if (!empty($data['name'])) {
            $where['org.name'] = array('like', "%" . $data['name'] . "%");
        }
        if (!empty($data['parent_id'])) {
            $where['org.parent_id'] = $data['parent_id'];
        } else {
            if (empty($data['name'])) {
                $where['org.parent_id'] = 0;
            }
        }
        $model_group = new GroupModel();
        $data = $model_group->getlist($where, $limit); //($this->put_data);
        $arr = $this->get_group_tree($data);
        if (!empty($arr)) {
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = $arr;
            $datajson['message'] = '数据为空!';
        }

        $this->jsonReturn($datajson);
    }

    public function listAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if (!empty($data['name'])) {
            $where['org.name'] = array('like', "%" . $data['name'] . "%");
        }
        if (!empty($data['org_node'])) {
            $pieces = explode(",", $data['org_node']);
            for ($i = 0; $i < count($pieces); $i++) {
                $where['org.org_node'] = $where['org.org_node'] . "'" . $pieces[$i] . "',";
            }
            $where['org.org_node'] = rtrim($where['org.org_node'], ",");
            $where['org.org_node'] = ['exp', 'IN (' . $where['org.org_node'] . ') '];
        }
        if (!empty($data['parent_id'])) {
            $where['org.parent_id'] = $data['parent_id'];
        } else {
            if (empty($data['name']) && empty($data['org_node'])) {
                $where['org.parent_id'] = 0;
            }
        }
        $model_group = new GroupModel();
        $data = $model_group->getlist($where, $limit); //($this->put_data);
        $arr = $this->get_group_children($data);
        if (!empty($arr)) {
            $datajson['code'] = 1;
            $datajson['data'] = $arr;
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = $arr;
            $datajson['message'] = '数据为空!';
        }

        $this->jsonReturn($datajson);
    }

    public function homelistAction() {
        if (redisExist('homelist')) {
            $arr = json_decode(redisGet('homelist'), true);
            if (!empty($arr)) {
                $datajson['code'] = 0;
                $datajson['data'] = $arr;
            } else {
                $datajson['code'] = -104;
                $datajson['data'] = $arr;
                $datajson['message'] = '数据为空!';
            }
            $this->jsonReturn($datajson);
        } else {
            $data = json_decode(file_get_contents("php://input"), true);
            $limit = [];
            $where = [];
            if (!empty($data['name'])) {
                $where['org.name'] = array('like', "%" . $data['name'] . "%");
            }
            if (!empty($data['parent_id'])) {
                $where['org.parent_id'] = $data['parent_id'];
                $where_user['group_id'] = $data['parent_id'];
            } else {
                if (empty($data['name'])) {
                    $where['org.parent_id'] = 0;
                }
            }
            $user_modle = new UserModel();
            $model_group = new GroupModel();
            $data = $model_group->getlist($where, $limit); //($this->put_data);
            if (!isset($where_user['group_id'])) {
                $where_user['group_id'] = $data[0]['id'];
            }
            $arr = $this->get_group_children($data, '', 1);
            if (!empty($arr)) {
                redisSet('homelist', json_encode($arr), 3000);
                $datajson['code'] = 0;
                $datajson['data'] = $arr;
            } else {
                $datajson['code'] = -104;
                $datajson['data'] = $arr;
                $datajson['message'] = '数据为空!';
            }
            $this->jsonReturn($datajson);
        }
    }

    public function listallAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $limit = [];
        $where = [];
        if (!empty($data['lang'])) {    //语言
            $where['lang'] = $data['lang'];
        }
        if (!empty($data['parent_id'])) {
            $where['org.parent_id'] = $data['parent_id'];
        }
        if (!empty($data['name'])) {
            $where['org.name'] = array('like', "%" . $data['name'] . "%");
        }

        if (!empty($data['org_node'])) {
            $pieces = explode(",", $data['org_node']);
            if (in_array('erui', $pieces)) {
                $pieces[] = 'eub';
                $pieces[] = 'elg';
            } elseif (in_array('ub', $pieces) && !in_array('eub', $pieces)) {
                $pieces[] = 'eub';
            }
            if (in_array('lg', $pieces) && !in_array('elg', $pieces)) {
                $pieces[] = 'elg';
            }

            for ($i = 0; $i < count($pieces); $i++) {
                $where['org.org_node'] = $where['org.org_node'] . "'" . $pieces[$i] . "',";
            }
            $where['org.org_node'] = rtrim($where['org.org_node'], ",");
            $where['org.org_node'] = ['exp', 'IN (' . $where['org.org_node'] . ') '];
        }
        $model_group = new GroupModel();
        $data = $model_group->getlist($where, $limit); //($this->put_data);
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

    public function adduserAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $where = [];
        if (!empty($data['group_id'])) {
            $where['group_id'] = $data['group_id'];
        } else {
            $datajson['code'] = -101;
            $datajson['message'] = '组织id不能为空!';
        }
        if (!empty($data['user_ids'])) {
            $where['user_ids'] = $data['user_ids'];
        }
        $model_group = new GroupUserModel();
        $data = $model_group->addusers($where); //($this->put_data);
        if (!empty($data)) {
            $datajson['code'] = 1;
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '操作失败!';
        }

        $this->jsonReturn($datajson);
    }

    public function deleteuserAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $where = [];
        if (!empty($data['group_id'])) {
            $where['group_id'] = $data['group_id'];
        } else {
            $datajson['code'] = -101;
            $datajson['message'] = '组织id不能为空!';
        }
        if (!empty($data['user_id'])) {
            $where['user_id'] = $data['user_id'];
        } else {
            $datajson['code'] = -101;
            $datajson['message'] = '用户id不能为空!';
        }
        $model_group = new GroupUserModel();
        $data = $model_group->deleteuser($where); //($this->put_data);
        if (!empty($data)) {
            $datajson['code'] = 1;
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '操作失败!';
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
        $model_group = new GroupModel();
        $data = $model_group->detail($id);
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
        $data['created_by'] = $this->user['id'];
        $model_group = new GroupModel();
        if ($data['org']) {
            $check = $model_group->where("org='" . $data['org'] . "'")->find();
            if ($check) {
                jsonReturn('', -103, '公司编号已经存在');
            }
        }
        if ($data['show_name']) {
            $check = $model_group->where("show_name='" . $data['show_name'] . "'")->find();
            if ($check) {
                jsonReturn('', -103, '公司显示名称已经存在');
            }
        }
        $id = $model_group->create_data($data);
        if (!empty($id)) {
            $datajson['code'] = 1;
            $datajson['data']['id'] = $id;
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
        $model_group = new GroupModel();
        if ($data['org']) {
            $check = $model_group->where("org='" . $data['org'] . "'")->find();
            if ($check && $check['id'] != $data['id']) {
                jsonReturn('', -103, '公司编号已经存在');
            }
        }
        if ($data['show_name']) {
            $check = $model_group->where("show_name='" . $data['show_name'] . "'")->find();
            if ($check && $check['id'] != $data['id']) {
                jsonReturn('', -103, '公司显示名称已经存在');
            }
        }
        if ($data['id']) {
            $where['id'] = $data['id'];
            $id = $model_group->update_data($data, $where);
            if (!empty($id)) {
                $datajson['code'] = 1;
            } else {
                $datajson['code'] = -104;
                $datajson['data'] = $data;
                $datajson['message'] = '添加失败!';
            }
            $this->jsonReturn($datajson);
        }
    }

    public function deleteAction() {
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
        }
        $arr['deleted_flag'] = 'Y';
        $model_group = new GroupModel();
        $id = $model_group->update_data($arr, $where);
        if ($id > 0) {
            $datajson['code'] = 1;
        } else {
            $datajson['code'] = -104;
            $datajson['message'] = '修改失败!';
        }
        $this->jsonReturn($datajson);
    }

}
