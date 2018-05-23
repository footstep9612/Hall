<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jhw
 */
class EmployeeModel extends PublicModel {

    //put your code here
    protected $tableName = 'employee';
    protected $g_table = 'employee';

    const STATUS_NORMAL = 'NORMAL'; //NORMAL-正常；
    const STATUS_DISABLED = 'DISABLED'; //DISABLED-禁止；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 根据条件获取查询条件
     * @param Array $condition
     * @return Array
     * @author jhw
     */
    protected function getCondition($condition = []) {
        $sql = ' WHERE 1 = 1 ';
        if (isset($condition['deleted_flag'])) {
            $sql .= ' AND `employee`.`deleted_flag`= \'' . $condition['deleted_flag'] . '\'';
        }
        if (!empty($condition['status'])) {
            $sql .= ' AND `employee`.`status`= \'' . $condition['status'] . '\'';
        }
        if (!empty($condition['group_id'])) {
            $sql .= ' AND org_member.org_id in (' . $condition['group_id'] . ')';
        }
        if (!empty($condition['mobile'])) {
            $sql .= ' AND employee.mobile =\'' . $condition['mobile'] . '\'';
        }
        if (!empty($condition['role_id'])) {
            $sql .= ' AND role_member.role_id =' . $condition['role_id'];
        }
        if (!empty($condition['role_no'])) {
            $sql .= ' AND role.role_no in (' . $condition['role_no'] . ')';
        }
        if (!empty($condition['role_name'])) {
            $sql .= ' AND role.name like \'%' . $condition['role_name'] . '%\'';
        }
        if (!empty($condition['gender'])) {
            $sql .= ' AND employee.gender = \'' . $condition['gender'] . '\'';
        }
        if (!empty($condition['username'])) {
            $sql .= ' AND employee.name like \'%' . $condition['username'] . '%\'';
        }
        if (!empty($condition['employee_flag'])) {
            $sql .= ' AND employee.employee_flag =\'' . $condition['employee_flag'] . '\'';
        }
        if (!empty($condition['user_no'])) {
            $sql .= ' AND employee.user_no like \'%' . $condition['user_no'] . '%\'';
        }
        if (!empty($condition['bn'])) {
            $sql .= ' AND country_member.country_bn in (' . $condition['bn'] . ')';
        }
        return $sql;
    }

    /**
     * 获取列表
     * @param  array $condition;
     * @return array
     * @author jhw
     */
    public function getlist($condition = [], $order = " employee.id desc") {
        $lang = $condition['lang'] ? : 'zh';
        unset($condition['lang']);
        $where = $this->getCondition($condition);
        $sql = 'SELECT `employee`.`id`,`employee`.`status`,`employee`.`deleted_flag`,`employee`.`created_at`,`employee`.`show_name`,`employee`.`gender`,`employee`.`user_no`,`employee`.`name`,`employee`.`email`,`employee`.`mobile` ,group_concat(DISTINCT `org`.`name' . ($lang == 'zh' ? '' : '_' . $lang) .'`) as group_name,group_concat(DISTINCT `role`.`name' . ($lang == 'zh' ? '' : '_' . $lang) .'`) as role_name,group_concat(DISTINCT `country`.`name`) as country_name,group_concat(DISTINCT `country_member`.`country_bn`) as country';
        $sql .= ' FROM ' . $this->g_table;
        $sql .= ' left join  org_member on employee.id = org_member.employee_id ';
        $sql .= ' left join  org on org_member.org_id = org.id AND org.deleted_flag = \'N\'';
        $sql .= ' left join  role_member on employee.id = role_member.employee_id ';
        $sql .= ' left join  role on role_member.role_id = role.id AND role.deleted_flag = \'N\'';
        $sql .= ' left join  country_member on employee.id = country_member.employee_id ';
        $sql .= " left join  `erui_dict`.`country` on country_member.country_bn = country.bn and country.lang='$lang'";
        $sql .= $where;
        $sql .= ' group by `employee`.`id`';
        if ($condition['num']) {
            $sql .= ' LIMIT ' . $condition['page'] . ',' . $condition['num'];
        }
        $list =  $this->query($sql);
        return $list;
    }

    public function getcount($condition = [], $order = " employee.id desc") {
        $where = $this->getCondition($condition);
        $sql = 'SELECT count(DISTINCT `employee`.`id`) as num';
        $sql .= ' FROM ' . $this->g_table;
        $sql .= ' left join  org_member on employee.id = org_member.employee_id ';
        $sql .= ' left join  org on org_member.org_id = org.id ';
        $sql .= ' left join  role_member on employee.id = role_member.employee_id ';
        $sql .= ' left join  role on role_member.role_id = role.id ';
        $sql .= ' left join  country_member on employee.id = country_member.employee_id ';
        $sql .= $where;
        return $this->query($sql);
    }

    public function getUserNamesByUserids($user_ids, $show_type = true) {

        try {
            $where = [];

            if (is_string($user_ids)) {
                $where['id'] = $user_ids;
            } elseif (is_array($user_ids) && !empty($user_ids)) {
                $where['id'] = ['in', $user_ids];
            } else {
                return false;
            }
            $users = $this->where($where)->field('id,name,email')->select();
            $user_names = [];
            foreach ($users as $user) {
                if ($show_type) {
                    $user_names[$user['id']] = $user['name'];
                } else {
                    $user_names[$user['id']]['name'] = $user['name'];
                    $user_names[$user['id']]['email'] = $user['email'];
                }
            }
            return $user_names;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据用户姓 获取用户ID
     * @param array $user_ids // 用户ID
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function getUseridsByUserName($UserName) {

        try {
            $where = [];
            if ($UserName) {
                $where['name'] = ['like', '%' . $UserName . '%'];
            } else {
                return false;
            }
            $users = $this->where($where)->field('id')->select();
            $userids = [];
            foreach ($users as $user) {
                $userids = $user['id'];
            }
            return $userids;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 根据条件查询信息
     * @param array $condition 条件数组
     * @param string|array $field 查询字段
     * @return bool|array
     * @author link 2017-08-05
     */
    public function getInfoByCondition($condition = [], $field = '') {
        if (empty($condition)) {
            return false;
        }

        if (!isset($condition['deleted_flag'])) {
            $condition['deleted_flag'] = self::DELETE_N;
        }

        if (empty($field)) {
            $field = 'id,user_no,email,mobile,password_hash,name,name_en,avatar,gender,mobile2,phone,ext,remarks,status';
        } elseif (is_array($field)) {
            $field = implode(',', $field);
        }

        try {
            $result = $this->field($field)->where($condition)->select();
            return $result ? $result : array();
        } catch (Exception $e) {
            return false;
        }
    }

}
