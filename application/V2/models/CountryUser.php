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
class CountryUserModel extends PublicModel {

    //put your code here
    protected $tableName = 'country_member';
    protected $table = 'country_member';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    public function addCountry($data) {
        if ($data['user_id']) {
            $this->where(['employee_id' => $data['user_id']])->delete();
            if (!empty($data['country_bns'])) {
                $country_arr = explode(",", $data['country_bns']);
                for ($i = 0; $i < count($country_arr); $i++) {
                    if ($country_arr[$i]) {
                        $arr['country_bn'] = $country_arr[$i];
                        $arr['employee_id'] = $data['user_id'];
                        $this->create_data($arr);
                    }
                }
            }
        }
        return true;
    }

    /*
     * 获取用户国家
     */

    public function userCountry($user_id, $pid = '') {
        if ($user_id) {
            $sql = 'SELECT country_member.*,country.`id`,`lang`,`region_bn`,`code`,`bn`,`name`,`int_tel_code`,`time_zone`,`status`,`deleted_flag`  ';
            $sql .= ' FROM  `country_member` ';
            $sql .= ' LEFT JOIN  `erui_dict`.`country` ON `erui_dict`.`country`.`bn` =`country_member`.`country_bn` and `erui_dict`.`country`.`lang` ="zh"';
            $sql .= " WHERE 1=1  ";
            if (!empty($user_id)) {
                $sql .= ' and `country_member`.`employee_id` =' . $user_id;
            }
            $sql .= ' group by country_member.`country_bn`';
            $sql .= ' order by country.`id` desc';
            return $this->query($sql);
        }
    }

    /*
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author jhw
     */

    public function create_data($create = []) {
        $data = $this->create($create);
        return $this->add($data);
    }

    /**
     * @desc 获取查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-11-23
     */
    public function getWhere($condition = []) {
        $where = [];

        if (!empty($condition['employee_id'])) {
            $where['employee_id'] = $condition['employee_id'];
        }

        return $where;
    }

    /**
     * @desc 获取记录总数
     *
     * @param array $condition
     * @return int $count
     * @author liujf
     * @time 2017-11-23
     */
    public function getCount($condition = []) {

        $where = $this->getWhere($condition);

        $count = $this->where($where)->count('id');

        return $count > 0 ? $count : 0;
    }

    /**
     * @desc 获取列表
     *
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf
     * @time 2017-11-23
     */
    public function getList($condition = [], $field = '*') {

        $where = $this->getWhere($condition);

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        return $this->field($field)->where($where)->page($currentPage, $pageSize)->order('id DESC')->select();
    }

    /**
     * @desc 获取详情
     *
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf
     * @time 2017-11-23
     */
    public function getDetail($condition = [], $field = '*') {

        $where = $this->getWhere($condition);

        return $this->field($field)->where($where)->order('id DESC')->find();
    }

    /**
     * @desc 添加记录
     *
     * @param array $condition
     * @return mixed
     * @author liujf
     * @time 2017-11-23
     */
    public function addRecord($condition = []) {

        $data = $this->create($condition);

        return $this->add($data);
    }

    /**
     * @desc 修改信息
     *
     * @param array $where , $condition
     * @return bool
     * @author liujf
     * @time 2017-11-23
     */
    public function updateInfo($where = [], $condition = []) {

        $data = $this->create($condition);

        return $this->where($where)->save($data);
    }

    /**
     * @desc 删除记录
     *
     * @param array $condition
     * @return bool
     * @author liujf
     * @time 2017-11-23
     */
    public function delRecord($condition = []) {

        if (!empty($condition['id'])) {
            $where['id'] = ['in', explode(',', $condition['id'])];
        } else {
            return false;
        }

        return $this->where($where)->delete();
    }

    /**
     * @desc 获取用户所在的国家
     *
     * @param array $condition
     * @return bool
     * @author liujf
     * @time 2017-11-23
     */
    public function getUserCountry($condition = []) {

        $where = $this->getWhere($condition);

        return $this->where($where)->getField('country_bn', true);
    }

    public function getCountryBnsByUserid($user_id) {

        return $this->where(['employee_id' => $user_id])->getField('country_bn', true);
    }

}
