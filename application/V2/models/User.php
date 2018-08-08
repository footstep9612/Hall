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
class UserModel extends PublicModel {

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
        if (!empty($condition['name_user_no'])) {
            $sql .= ' AND ( employee.name like \'%' . $condition['name_user_no'] . '%\'';
            $sql .= ' or  employee.user_no like \'%' . $condition['name_user_no'] . '%\' )';
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
//        $page=$condition['page'];
//        $offset=($page-1)*10;
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
//        $sql .= ' LIMIT ' . $offset . ',10' ;
        $list =  $this->query($sql);
        return $list;
    }
    public function getcount($condition = [], $order = " employee.id desc") {
        unset($condition['status']);
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
    public function getStatusCount($condition = []) {
        unset($condition['status']);
        $where = $this->getCondition($condition);
        $sql = 'SELECT employee.status,count(DISTINCT `employee`.`id`) as num';
        $sql .= ' FROM ' . $this->g_table;
        $sql .= ' left join  org_member on employee.id = org_member.employee_id ';
        $sql .= ' left join  org on org_member.org_id = org.id ';
        $sql .= ' left join  role_member on employee.id = role_member.employee_id ';
        $sql .= ' left join  role on role_member.role_id = role.id ';
        $sql .= ' left join  country_member on employee.id = country_member.employee_id ';
        $sql .= $where;
        $sql .= ' GROUP BY `employee`.`status`';
        $info=$this->query($sql);
        $arr=[];
        foreach($info as $k => $v){
            if($v['status']=='DISABLED'){   //禁用
                $arr['disabled_num']=$v['num'];
            }
            if($v['status']=='NORMAL'){ //正常
                $arr['normal_num']=$v['num'];
            }
        }
        return $arr;
    }
    public function crmlist($data){
        $lang=$data['lang'];
        $cond=$this->crmCond($data);
        $info=$this->alias('employee')
            ->join("erui_sys.country_member member on employee.id=member.employee_id",'left')
            ->join("erui_dict.country country on member.country_bn=country.bn and country.lang='$lang' and country.deleted_flag='N'",'left')
            ->field('employee.id,employee.name,employee.user_no,member.country_bn,employee.mobile,country.name as country_name')
            ->where($cond)
            ->group('employee.id')
            ->select();
        if(empty($info)){
            $info=[];
        }
        return $info;
    }
    public function crmCond($data){
        $cond="employee.deleted_flag='N'";
        if (!empty($data['username'])) {    //名称
            $userStr='';
            $users=explode(',',$data['username']);
            if(count($users)==1){
                $cond.=" and employee.name like '%".trim($data['username'])."%'";
            }else{
                foreach($users as $k => $v){
                    $userStr.=",'".trim($v)."'";
                }
                $userStr=substr($userStr,1);
                $cond.=" and employee.name in ($userStr)";
            }
        }
        if (!empty($data['user_no'])) { //工号
            $cond.=" and employee.user_no in ($data[user_no])";
        }
        if (!empty($data['bn'])) {  //国家
            $bn = trim($data['bn']);
            $cond.=" and member.country_bn='$bn' ";
        }
        return $cond;
    }

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($id) {
        $where['id'] = $id;
        return $this->where($where)->find();
    }

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function findInfo($where) {
        $sql = 'SELECT * FROM regi.user_main where username = ' . $where;
//        if ( !empty($condition['where']) ){
//            $sql .= ' AND '.$condition['where'];
//        }
//        $sql .= ' Order By '.$order;
//        if ( $condition['page'] ){
//            $sql .= ' LIMIT '.$condition['page'].','.$condition['countPerPage'];
//        }
//return $this->query( $sql );
        $db = db_Db::getInstance($this->db_config);
        return $db->query($sql);
    }

    /**
     * 登录
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function login($data) {
        $where = array();
        if (!empty($data['email'])) {
            $where['email'] = $data['email'];
        }
        if (!empty($data['mobile'])) {
            $where['mobile'] = $data['mobile'];
        }
        if (!empty($data['user_no'])) {
            $where['user_no'] = $data['user_no'];
        }
        if (empty($where['mobile']) && empty($where['email']) && empty($where['user_no'])) {
            echo json_encode(array("code" => "-101", "message" => "帐号不能为空"));
            exit();
        }
        if (!empty($data['password'])) {
            $where['password_hash'] = md5($data['password']);
        }
        $where['status'] = 'NORMAL';
        $row = $this->where($where)
                ->field('id,user_no,name,email,mobile,status,password_status')
                ->find();
        return $row;
    }

    /**
     * 判断用户是否存在
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function Exist($data) {
        $map = [];
        $sql = 'SELECT `id`,`user_no`,`name`,`email`,`mobile`';
        $sql .= ' FROM ' . $this->g_table;
        if (!empty($data['user_no'])) {
            $map['user_no'] = $data['user_no'];
        }
        $row = $this->where($map)->find();
        return empty($row) ? false : (isset($row['id']) ? $row['id'] : true);
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
    public function update_data($create = [], $where) {
        if (isset($create['user_no'])) {
            $data['user_no'] = $create['user_no'];
        }
        if (isset($create['show_name'])) {
            $data['show_name'] = $create['show_name'];
        }
        if (isset($create['name'])) {
            $data['name'] = $create['name'];
        }
        if (isset($create['email'])) {
            $data['email'] = $create['email'];
        }
        if (isset($create['mobile'])) {
            $data['mobile'] = $create['mobile'];
        }
        if (isset($create['password_hash'])) {
            $data['password_hash'] = $create['password_hash'];
            $data['password_status'] = 'N';
        }
        if (isset($create['name_en'])) {
            $data['name_en'] = $create['name_en'];
        }
        if (isset($create['gender'])) {
            $data['gender'] = $create['gender'];
        }
        if (isset($create['mobile2'])) {
            $data['mobile2'] = $create['mobile2'];
        }
        if (isset($create['phone'])) {
            $data['phone'] = $create['phone'];
        }
        if (isset($create['ext'])) {
            $data['ext'] = $create['ext'];
        }
        if (isset($create['remarks'])) {
            $data['remarks'] = $create['remarks'];
        }
        if (isset($create['employee_flag'])) {
            $data['employee_flag'] = $create['employee_flag'];
        }
        if (isset($create['citizenship'])) {
            $data['citizenship'] = $create['citizenship'];
        }
        if (isset($create['deleted_flag'])) {
            $data['deleted_flag'] = $create['deleted_flag'];
        }
        if (isset($create['status'])) {
            $data['status'] = $create['status'];
        }
//        switch ($create['status']) {
//            case self::STATUS_DELETED:
//                $data['status'] = $create['status'];
//                $data['deleted_flag'] = 'Y';
//                break;
//            case self::STATUS_DISABLED:
//                $data['status'] = $create['status'];
//                $data['deleted_flag'] = 'Y';
//                break;
//            case self::STATUS_NORMAL:
//                $data['status'] = $create['status'];
//                $data['deleted_flag'] = 'N';
//                break;
//        }
        if (!$where) {
            return false;
        } else {
            return $this->where($where)->save($data);
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
    public function infoList($where) {
        return $this->where($where)
                        ->field('id,user_no,name,email,mobile,status')
                        ->find();
    }

    /**
     * 新增数据
     * @param  array $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if (isset($create['user_no'])) {
            $data['user_no'] = $create['user_no'];
        }
        if (isset($create['name'])) {
            $data['name'] = $create['name'];
        }
        if (isset($create['email'])) {
            $data['email'] = $create['email'];
        }
        if (isset($create['mobile'])) {
            $data['mobile'] = $create['mobile'];
        }
        if (isset($create['password_hash'])) {
            $data['password_hash'] = $create['password_hash'];
        }
        if (isset($create['name_en'])) {
            $data['name_en'] = $create['name_en'];
        }
        if (isset($create['gender'])) {
            $data['gender'] = $create['gender'];
        }
        if (isset($create['mobile2'])) {
            $data['mobile2'] = $create['mobile2'];
        }
        if (isset($create['show_name'])) {
            $data['show_name'] = $create['show_name'];
        }
        if (isset($create['phone'])) {
            $data['phone'] = $create['phone'];
        }
        if (isset($create['ext'])) {
            $data['ext'] = $create['ext'];
        }
        if (isset($create['remarks'])) {
            $data['remarks'] = $create['remarks'];
        }
        if (isset($data)) {
            $data['created_at'] = date("Y-m-d H:i:s");
        }
        if (isset($create['employee_flag'])) {
            $data['employee_flag'] = $create['employee_flag'];
        }
        if (isset($create['citizenship'])) {
            $data['citizenship'] = $create['citizenship'];
        }
        $datajson = $this->create($data);
        return $this->add($datajson);
    }
    
    /**
     * @desc 获取查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2018-01-22
     */
    public function getWhere($condition = []) {
        $where['deleted_flag'] = 'N';
        if(!empty($condition['user_no'])) {
            $where['user_no'] = ['like', '%' . trim($condition['user_no']) . '%'];
        }
        if(!empty($condition['username'])) {
            $where['name'] = ['like', '%' . trim($condition['username']) . '%'];
        }
        return $where;
    }
    
    /**
     * @desc 获取记录总数
     *
     * @param array $condition
     * @return int $count
     * @author liujf
     * @time 2018-01-22
     */
    public function getCount_($condition = []) {
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
     * @time 2018-01-22
     */
    public function getList_($condition = [], $field = '*') {
        $where = $this->getWhere($condition);
        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
        return $this->field($field)
                            ->where($where)
                            ->page($currentPage, $pageSize)
                            ->order('id DESC')
                            ->select();
    }

}
