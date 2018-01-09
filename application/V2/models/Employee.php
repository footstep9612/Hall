<?php

/**
 * 员工
 * @author  zhongyg
 * @date    2017-8-5 15:39:16
 * @version V2.0
 * @desc
 */
class EmployeeModel extends PublicModel {

    protected $dbName = 'erui_sys'; //数据库名称
    protected $tableName = 'employee'; //数据表表名

    const DELETE_Y = 'Y';   //删除
    const DELETE_N = 'N';   //未删除

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
            } elseif (is_array($user_ids) && !empty($user_ids)) {
                $where['id'] = ['in', $user_ids];
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
                $where['name'] = ['like', '%' . trim($UserName) . '%'];
            } else {
                return false;
            }
            $users = $this->where($where)->field('id')->select();
            $userids = [];
            foreach ($users as $user) {
                $userids[] = $user['id'];
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

        /*if (!isset($condition['deleted_flag'])) {
            $condition['deleted_flag'] = self::DELETE_N;
        }*/

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

    /**
     * 框架协议-商务技术经办人-列表
     * wangs
     */
    public function buyerTechAgent($data){
        $cond = "1=1";
        if(!empty($data['name'])){
            $cond .= " and name like '%$data[name]%'";
        }
        if(!empty($data['user_no'])){
            $cond .= " and user_no like '%$data[user_no]%'";
        }
        $page = 1;
        $pageSize = 10;
        $totalCont = $this -> where($cond) -> count();
        $totalPage = ceil($totalCont/$pageSize);
        if(!empty($data['page']) && is_numeric($data['page']) && $data['page']>0){
            $page = ceil($data['page']);
        }
        if($page > $totalPage && $totalPage > 0){
            $page = $totalPage;
        }
        $offset = ($page-1)*$pageSize;
        $info = $this -> field('id,user_no,name') ->where($cond) -> limit($offset,$pageSize) -> select();
        $arr = array(
            'info'=>$info,
            'page'=>$page,
            'totalCount'=>$totalCont,
            'totalPage'=>$totalPage
        );
        return $arr;
    }

    /**
     * @param $id
     * 根据id获取技术人员名称-王帅
     */
    public function getNameByid($id){
        return $this->field('id,name')->where(array('id'=>$id))->find();
    }
    public function getIdByName($name){
        return $this->field('id,name')->where(array('name'=>$name))->find();
    }
}
