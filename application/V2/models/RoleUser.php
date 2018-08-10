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
class RoleUserModel extends PublicModel {

    //put your code here
    protected $tableName = 'role_member';
    Protected $autoCheckFields = true;
    protected $table_name = 'role_member';

    public function __construct() {
        parent::__construct();
    }

    public function userRoleList($user_id, $pid = '', $where = null) {
        if ($user_id) {
            $sql = 'SELECT  `func_perm`.`id` as func_perm_id,`func_perm`.`logo_name`,`func_perm`.`logo_url`,`func_perm`.`url`,`func_perm`.`sort`,`func_perm`.`fn`,`func_perm`.`fn_en`,`func_perm`.`fn_es`,`func_perm`.`fn_ru`,`func_perm`.`show_name`,`func_perm`.`show_name_en`,`func_perm`.`show_name_es`,`func_perm`.`show_name_ru`,`func_perm`.`parent_id` ,`func_perm`.`source` ';
            $sql .= ' FROM employee';
            $sql .= ' LEFT JOIN  `role_member` ON `employee`.`id` =`role_member`.`employee_id`';
            $sql .= ' LEFT JOIN  `role` ON `role`.`id` =`role_member`.`role_id` and  `role`.`deleted_flag` = "N"';
            $sql .= ' LEFT JOIN  `role_access_perm` ON `role_access_perm`.`role_id` =`role_member`.`role_id`';
            $sql .= ' LEFT JOIN  `func_perm` ON `func_perm`.`id` =`role_access_perm`.`func_perm_id`';
            $sql .= "WHERE `func_perm`.`id` is not null ";
            if (!empty($user_id)) {
                $sql .= ' and `role_member`.`employee_id` =' . $user_id;
            }
            if ($pid !== '') {
                $sql .= ' and `func_perm`.`parent_id` = ' . $pid;
            }
            if ($where['source']) {
                $sql .= " and `func_perm`.`source` = '" . $where['source'] . "'";
            }
            $sql .= ' group by func_perm_id';
            $sql .= ' order by `func_perm`.`sort`';
            return $this->query($sql);
        }
    }

    /*
     * 获取用户角色
     */

    public function userRole($user_id, $pid = '') {
        if ($user_id) {
            $sql = 'SELECT  role.`id`,role.`name`,role.`name_en`,role.`role_no`,role.`admin_show`,role.`role_group`,role.`remarks`,role.`status`,role.`deleted_flag`  ';
            $sql .= ' FROM role';
            $sql .= ' LEFT JOIN  `role_member` ON `role`.`id` =`role_member`.`role_id`';
            $sql .= " WHERE role.deleted_flag = 'N'  ";
            if (!empty($user_id)) {
                $sql .= ' and `role_member`.`employee_id` =' . $user_id;
            }
            $sql .= ' group by role.`id`';
            $sql .= ' order by role.`id` desc';
            return $this->query($sql);
        }
    }

    public function getUserRole($user_id) {
        $role = new RoleModel();
        $roleInfo = $role->field('id,name')->where(array('attr_id' => 0, 'deleted_flag' => 'N'))->select();
        $mem = $this->field('role_id')->where(array('employee_id' => $user_id))->select();
        $str = '';
        foreach ($mem as $k => $v) {
            $str .= ',' . $v['role_id'];
        }
        $str = substr($str, 1);
        foreach ($roleInfo as $k => $v) {
            $res = $role->field('id,name')
                    ->where("id in ($str) and attr_id=$v[id] and deleted_flag='N'")
                    ->select();
            if (empty($res)) {
                $res = [];
            }
            $roleInfo[$k]['child'] = $res;
        }
        return $roleInfo;
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getRolesUserlist($id, $order = 'id desc') {

        $sql = 'SELECT  `role_member`.`id` as role_member_id,`role_member`.`employee_id`,`employee`.`name`, `employee`.`email` , `employee`.`mobile`  , `employee`.`user_no` ';
        $sql .= ' FROM employee';
        $sql .= ' LEFT JOIN  `role_member` ON `employee`.`id` =`role_member`.`employee_id`';
        // $sql_where = '';
        if (!empty($id)) {
            $sql .= ' WHERE `role_member`.`role_id` =' . $id;
            // $sql .=$sql_where;
        }
//        if ( $where ){
//            $sql .= $sql_where;
//        }
        return $this->query($sql);
    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            return $this->where($where)
                            ->delete();
        } else {
            return false;
        }
    }

    public function update_datas($data) {
        if ($data['role_id']) {
            $this->where(['role_id' => $data['role_id']])->delete();
            if ($data['role_user_ids']) {
                $user_arr = explode(',', $data['role_user_ids']);
                $count = count($user_arr);
                for ($i = 0; $i < $count; $i++) {
                    $info = $this->where(['role_id' => $data['role_id'], 'employee_id' => $user_arr[$i]])->select();
                    if (!$info) {
                        $this->create_data(['role_id' => $data['role_id'], 'employee_id' => $user_arr[$i]]);
                    }
                }
            }
        }
        return true;
    }

    public function update_role_datas($data) {
        if ($data['user_id']) {
            $this->where(['employee_id' => $data['user_id']])->delete();
            if ($data['role_ids']) {
                $role_arr = explode(',', $data['role_ids']);
                $role_arr = array_merge(array_unique($role_arr));
                $count = count($role_arr);
                for ($i = 0; $i < $count; $i++) {
                    if ($role_arr[$i]) {
                        $this->create_data(['role_id' => $role_arr[$i], 'employee_id' => $data['user_id']]);
                    }
                }
            }
        }
        return true;
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if (isset($create['role_id'])) {
            $arr['role_id'] = $create['role_id'];
        }
        if (isset($create['employee_id'])) {
            $arr['employee_id'] = $create['employee_id'];
        }
        $arr['created_at'] = date("Y-m-d H:i:s");
        $data = $this->create($arr);
        return $this->add($data);
    }

    //crm 获取登录用户的角色-wangs
    public function crmGetUserRole($user_id) {
        $role = $this->alias('role_member')
                ->join('erui_sys.role as role on role_member.role_id=role.id', 'left')
                ->field('role.role_no')
                ->where(array('employee_id' => $user_id))
                ->select();
        $arr = array();
        foreach ($role as $k => $v) {
            $arr[] = $v['role_no'];
        }
        return $arr;
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

        $parentId = isDecimal($condition['parent_id']) ? $condition['parent_id'] : 0;
        $data = $this->_TopuserRoleList($userId, $parentId, $condition['source'], $condition['not_pid']);

        return $this->_funcChildren($userId, $data, $condition['source'], $lang, $condition['only_one_level']);
    }

    private function _funcChildren($userId, $data, $source, $lang, $only_one_level = null) {
        $pids = [];
        foreach ($data as $key => $val) {
            $data[$key]['check'] = false;
            $data[$key]['lang'] = $lang;
            $pids[] = $val['func_perm_id'];
        }

        if ($only_one_level == 'Y') {
            return $data;
        } else {
            $list = $this->_userRoleList($userId, $pids, $source);

            if (!empty($list)) {
                $pids = [];
                foreach ($list as $childrens) {
                    foreach ($childrens as $children) {
                        $pids[] = $children['func_perm_id'];
                    }
                }
                unset($childrens, $children);
                $children_childrens = $this->_userRoleList($userId, $pids, $source);
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

    public function _userRoleList($user_id, $pid = [], $source = null) {
        if ($user_id) {
            $fields = '`fp`.`id` as func_perm_id,`fp`.`fn`,`fp`.`parent_id`,`fp`.`url`,fp.top_parent_id,fp.source,`fp`.`fn_en`,`fp`.`fn_es`,`fp`.`fn_ru`';
            $employee_model = new EmployeeModel();
            $where = [];
            if (!empty($user_id)) {
                $where['rm.employee_id'] = $user_id;
            }
            if (!empty($pid) && is_string($pid)) {
                $where['fp.parent_id'] = $pid;
            } elseif (!empty($pid) && is_array($pid)) {
                $where['fp.parent_id'] = ['in', $pid];
            } elseif (empty($pid) && $pid === 0) {
                $where['fp.parent_id'] = 0;
            }
            if ($source) {
                $where['fp.source'] = $source;
            }
            $where[] = '`fp`.`id` is not null';
            $data = $employee_model
                    ->alias('u')
                    ->field($fields)
                    ->where($where)
                    ->join($this->getTableName() . ' rm on rm.employee_id=u.id')
                    ->join((new RoleModel())->getTableName() . ' r on r.id=rm.role_id')
                    ->join((new RoleAccessPermModel())->getTableName() . ' rap on rap.role_id=rm.role_id')
                    ->join('erui_sys.func_perm fp on fp.id=rap.func_perm_id')
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

    private function _TopuserRoleList($user_id, $pid = [], $source = null, $not_pid = null) {
        if ($user_id) {
            $fields = '`fp`.`id` as func_perm_id,`fp`.`fn`,`fp`.`parent_id`,`fp`.`url`,fp.top_parent_id,fp.source,`fp`.`fn_en`,`fp`.`fn_es`,`fp`.`fn_ru`';
            $employee_model = new EmployeeModel();
            $where = [];
            if (!empty($user_id)) {
                $where['rm.employee_id'] = $user_id;
            }
            if (!empty($pid) && is_string($pid)) {
                $where['fp.parent_id'] = $pid;
            } elseif (!empty($pid) && is_array($pid)) {
                $where['fp.parent_id'] = ['in', $pid];
            } elseif (empty($pid) && $pid === 0) {
                $where['fp.parent_id'] = 0;
            }
            if (!empty($not_pid)) {
                $where[] = 'fp.id <>' . $not_pid;
            }
            if ($source) {
                $where['fp.source'] = $source;
            }
            $where[] = '`fp`.`id` is not null';
            $data = $employee_model
                    ->alias('u')
                    ->field($fields)
                    ->where($where)
                    ->join($this->getTableName() . ' rm on rm.employee_id=u.id')
                    ->join((new RoleModel())->getTableName() . ' r on r.id=rm.role_id and r.deleted_flag=\'N\'')
                    ->join((new RoleAccessPermModel())->getTableName() . ' rap on rap.role_id=rm.role_id')
                    ->join('erui_sys.func_perm fp on fp.id=rap.func_perm_id')
                    ->group('fp.id')
                    ->order('`fp`.`sort` asc')
                    ->select();

            return $data;
        }
    }

}
