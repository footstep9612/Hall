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
class UserModel extends PublicModel {

    //put your code here
    protected $tableName = 'user';

    const STATUS_NORMAL = 'NORMAL'; //NORMAL-正常；
    const STATUS_DISABLED = 'DISABLED'; //DISABLED-禁止；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zyg
     *
     */
    protected function getcondition($condition = []) {
        $where = [];
        if ($condition['id']) {
            $where['id'] = $condition['id'];
        }
        if ($condition['user_id']) {
            $where['user_id'] = $condition['user_id'];
        }
        if ($condition['name']) {
            $where['name'] = ['like' => '%' . $condition['name'] . '%'];
        }
        if ($condition['email']) {
            $where['email'] = ['like' => '%' . $condition['email'] . '%'];
        }
        if ($condition['mobile']) {
            $where['mobile'] = ['like' => '%' . $condition['mobile'] . '%'];
        }
        if ($condition['enc_password']) {
            $where['enc_password'] = md5($condition['enc_password']);
        }
        if ($condition['status']) {
            $where['status'] = $condition['status'];
        }
        return $where;
    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getcount($condition = []) {
        $where = $this->getcondition($condition);
        try {
            return $this->where($where)
                            ->field('id,user_id,name,email,mobile,status')
                            ->count('id');
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), $level);
            return false;
        }
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = []) {
        $where = $this->getcondition($condition);

        if (isset($condition['page']) && isset($condition['countPerPage'])) {
            $count = $this->getcount($condition);
            return $this->where($where)
                            ->limit($condition['page'] . ',' . $condition['countPerPage'])
                            ->field('id,user_id,name,email,mobile,status')
                            ->select();
        } else {
            return $this->where($where)
                            ->field('id,user_id,name,email,mobile,status')
                            ->select();
        }
    }

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($id = '') {
        $where['id'] = $id;
        return $this->where($where)
                        ->field('id,user_id,name,email,mobile,status')
                        ->find();
    }

    /**
     * 删除数据
     * @param  int $id id
     * @return bool
     * @author zyg
     */
    public function delete_data($id = '') {

        $where['id'] = $id;
        return $this->where($where)
                        ->save(['status' => 'DELETED']);
    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return bool
     * @author zyg
     */
    public function update_data($upcondition = []) {
        $data = [];
        $where = [];
        if ($condition['id']) {
            $where['id'] = $condition['id'];
        }
        if ($condition['user_id']) {
            $data['user_id'] = $condition['user_id'];
        }
        if ($condition['name']) {
            $data['name'] = $condition['name'];
        }
        if ($condition['email']) {
            $data['email'] = $condition['email'];
        }
        if ($condition['mobile']) {
            $data['mobile'] = $condition['mobile'];
        }
        if ($condition['enc_password']) {
            $data['enc_password'] = md5($condition['enc_password']);
        }
        switch ($condition['status']) {

            case self::STATUS_DELETED:
                $data['status'] = $condition['status'];
                break;
            case self::STATUS_DISABLED:
                $data['status'] = $condition['status'];
                break;
            case self::STATUS_NORMAL:
                $data['status'] = $condition['status'];
                break;
        }


        return $this->where($where)->save($data);
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($createcondition = []) {



        $data['enc_password'] = md5($condition['enc_password']);
        switch ($condition['status']) {

            case self::STATUS_DELETED:
                $data['status'] = $condition['status'];
                break;
            case self::STATUS_DISABLED:
                $data['status'] = $condition['status'];
                break;
            case self::STATUS_NORMAL:
                $data['status'] = $condition['status'];
                break;
        }
        $data = $this->create($data);

        return $this->add($data);
    }

}
