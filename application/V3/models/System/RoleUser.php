<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author zyg
 */
class System_RoleUserModel extends PublicModel {

    //put your code here
    protected $tableName = 'role_member';
    Protected $autoCheckFields = true;
    protected $table_name = 'role_member';
    protected $dbName = 'erui_sys';

    public function __construct() {
        parent::__construct();
    }

    public function userRoleList($user_id, $pid = '', $where = []) {

        if ($user_id) {
            $fields = ' `fp`.`id` as func_perm_id,`fp`.`fn`,`fp`.`parent_id`,`fp`.`url`,fp.top_parent_id,fp.source,`fp`.`fn_en`,`fp`.`fn_es`,`fp`.`fn_ru`';
            $employee_model = new System_EmployeeModel();

            if (!empty($user_id)) {
                $where['rm.employee_id'] = $user_id;
            }
            if (!empty($pid) && is_string($pid)) {
                $where['fp.parent_id'] = $pid;
            } elseif (!empty($pid) && is_array($pid)) {
                $where['fp.parent_id'] = ['in', $pid];
            }

            $where[] = '`fp`.`id` is not null';
            $data = $employee_model
                    ->alias('u')
                    ->field($fields)
                    ->where($where)
                    ->join($this->getTableName() . ' rm on rm.employee_id=u.id', 'LEFT')
                    ->join((new System_RoleModel())->getTableName() . ' r on r.id=rm.role_id', 'LEFT')
                    ->join((new System_RoleAccessPermModel())->getTableName() . ' rap on rap.role_id=rm.role_id', 'LEFT')
                    ->join((new System_FuncPermModel())->getTableName() . '  fp on fp.id=rap.func_perm_id', 'LEFT')
                    ->group('fp.id')
                    ->order('`fp`.`sort` asc')
                    ->select();
            $ret = [];

            if (!empty($data)) {
                foreach ($data as $val) {
                    $ret[] = $val;
                }
            }
            return $ret;
        }
    }

    /*
     * 获取用户角色
     */

    public function userRole($user_id, $pid = '') {
        if ($user_id) {


            $where = ['role.deleted_flag' => 'N'];
            if (!empty($user_id)) {
                $where['role_member.employee_id'] = $user_id;
            }
            $data = $this->alias('role')
                    ->filed('role.`id`,role.`name`,role.`name_en`,role.`role_no`,role.`admin_show`,role.`role_group`,role.`remarks`,role.`status`,role.`deleted_flag` ')
                    ->join((new System_RoleMemberModel())->getTableName() . ' as role_member ON `role`.`id` =`role_member`.`role_id`', 'LEFT')
                    ->where($where)
                    ->group('role.`id`')
                    ->order('role.id desc')
                    ->select();


            return $data;
        }
    }

    public function getUserRole($user_id) {
        $role_model = new System_RoleModel();
        $roles = $role_model->field('id,name')->where(array('attr_id' => 0, 'deleted_flag' => 'N'))->select();
        $mem = $this->field('role_id')->where(array('employee_id' => $user_id))->select();
        $role_attr_ids = $role_ids = [];
        foreach ($mem as $k => $v) {
            $role_ids[] = $v['role_id'];
        }
        foreach ($roles as $k => $v) {
            $role_attr_ids[] = $v['id'];
        }
        $role_childs_select = $role_model->field('id,name,attr_id')
                ->where(['id' => ['in', $role_ids], 'deleted_flag' => 'N', 'attr_id' => ['in', $role_attr_ids]])
                ->select();
        $role_childs = [];
        foreach ($role_childs_select as $item) {
            $role_childs[$item['attr_id']][] = $item;
        }
        foreach ($roles as $key => $item) {
            if (!empty($role_childs[$item['id']])) {
                $item['child'] = $item;
            } else {
                $item['child'] = [];
            }

            $roles[$key] = $item;
        }
        return $roles;
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getRolesUserlist($id, $order = 'rm.id desc') {
        $role_member_model = new System_RoleMemberModel();
        $employee_table = (new System_EmployeeModel())->getTableName();
        $where = [];
        if (!empty($id)) {
            $where['rm.role_id'] = $id;
        }
        $data = $role_member_model->alais('rm')
                ->join($employee_table . ' as e on e.id=rm.employee_id')
                ->where($where)
                ->order($order)
                ->select();


        return $data;
    }

    /**
     * @desc 获取用户菜单
     *
     * @param array $userId
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2018-06-19
     */
    public function getUserMenu($userId, $condition = [], $lang = 'zh') {
        isset($condition['source']) ? $where['source'] = trim($condition['source']) : '';
        $fn = null;
        if (!empty($condition['fn'])) {
            $fn = $condition['fn'];
            $where['fp.fn'] = ['in', $fn];
        }
        $parentId = isDecimal($condition['parent_id']) ? $condition['parent_id'] : 0;
        $data = $this->userRoleList($userId, $parentId, $where);

        return $this->_funcChildren($userId, $data, isset($condition['source']) ? trim($condition['source']) : '', $fn, $lang, $condition['only_one_level']);
    }

    /**
     * @desc 获取用户菜单
     *
     * @param array $userId
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2018-06-19
     */
    public function getShortcutsUserMenu($userId, $condition = [], $lang = 'zh') {
        isset($condition['source']) ? $where['source'] = trim($condition['source']) : '';
        $fn = null;
        if (!empty($condition['fn'])) {
            $fn = $condition['fn'];
            $where['fp.fn'] = ['in', $fn];
        }
        $parentId = isDecimal($condition['parent_id']) ? $condition['parent_id'] : 0;
        $data = $this->userRoleList($userId, $parentId, $where);
        return $this->_funcChildren($userId, $data, isset($condition['source']) ? trim($condition['source']) : '', $fn, $lang, $condition['only_one_level']);
    }

    private function _funcChildren($userId, $data, $source, $fn, $lang, $only_one_level = null) {
        $pids = [];
        foreach ($data as $key => $val) {
            $data[$key]['check'] = false;
            $data[$key]['lang'] = $lang;
            $pids[] = $val['func_perm_id'];
        }

        if ($only_one_level == 'Y') {
            return $data;
        } else {
            $list = $this->_userRoleList($userId, $pids, $source, $fn);

            if (!empty($list)) {
                $pids = [];
                foreach ($list as $childrens) {
                    foreach ($childrens as $children) {
                        $pids[] = $children['func_perm_id'];
                    }
                }
                unset($childrens, $children);
                $children_childrens = $this->_userRoleList($userId, $pids, $source, $fn);
            }
            foreach ($data as $key => $val) {
                if (!empty($list[$val['func_perm_id']])) {
                    foreach ($list[$val['func_perm_id']] as $k => $children) {
                        $children['lang'] = $lang;
                        $children['check'] = false;
                        if (!empty($children_childrens[$children['func_perm_id']])) {
                            $children['children'] = $children_childrens[$children['func_perm_id']];
                        }
                        $list[$val['func_perm_id']][$k] = $children;
                    }
                    $val['children'] = $list[$val['func_perm_id']];
                }
                $data[$key] = $val;
            }
            return $data;
        }
    }

    public function _userRoleList($user_id, $pid = [], $source = null, $fn = null) {
        if ($user_id) {
            $fields = '`fp`.`id` as func_perm_id,`fp`.`fn`,`fp`.`parent_id`,`fp`.`url`,fp.top_parent_id,fp.source,`fp`.`fn_en`,`fp`.`fn_es`,`fp`.`fn_ru`';
            $employee_model = new System_EmployeeModel();
            $where = [];
            if (!empty($user_id)) {
                $where['rm.employee_id'] = $user_id;
            }
            if (!empty($pid) && is_string($pid)) {
                $where['fp.parent_id'] = $pid;
            } elseif (!empty($pid) && is_array($pid)) {
                $where['fp.parent_id'] = ['in', $pid];
            }
            if ($source) {
                $where['fp.source'] = $source;
            }
            if ($fn) {
                $where['fp.fn'] = ['in', $fn];
            }
            $where[] = '`fp`.`id` is not null';
            $data = $employee_model
                    ->alias('u')
                    ->field($fields)
                    ->where($where)
                    ->join($this->getTableName() . ' rm on rm.employee_id=u.id', 'LEFT')
                    ->join((new System_RoleModel())->getTableName() . ' r on r.id=rm.role_id', 'LEFT')
                    ->join((new System_RoleAccessPermModel())->getTableName() . ' rap on rap.role_id=rm.role_id', 'LEFT')
                    ->join((new System_FuncPermModel())->getTableName() . ' fp on fp.id=rap.func_perm_id', 'LEFT')
                    ->group('fp.id')
                    ->order('`fp`.`sort` asc')
                    ->select();
            $ret = [];

            if (!empty($data)) {
                foreach ($data as $val) {
                    $ret[$val['parent_id']][] = $val;
                }
            }
            return $ret;
        }
    }

}
