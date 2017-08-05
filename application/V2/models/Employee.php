<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Employee
 * @author  zhongyg
 * @date    2017-8-5 15:39:16
 * @version V2.0
 * @desc   
 */
class EmployeeModel extends PublicModel {

    //put your code here
    protected $tableName = 'employee';
    protected $dbName = 'erui2_sys'; //数据库名称

    public function __construct() {
        parent::__construct();
    }

    /*
     * 根据用户ID 获取用户姓名
     * @param array $user_ids // 用户ID
     * @return mix 
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品 
     */

    public function getUserNamesByUserids($user_ids) {

        try {
            $where = [];
            if (is_string($user_ids)) {
                $where['id'] = $user_ids;
            } elseif (is_array($user_ids)) {
                $where['id'] = ['in' => $user_ids];
            } else {
                return false;
            }
            $users = $this->where($where)->field('id,name')->select();
            $user_names = [];
            foreach ($users as $user) {
                $user_names[$user['id']] = $user['name'];
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

}
