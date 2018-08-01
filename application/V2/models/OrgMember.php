<?php

/*
 * @desc 部门成员模型
 *
 * @author liujf
 * @time 2017-08-29
 */

class OrgMemberModel extends PublicModel {

    protected $dbName = 'erui_sys';
    protected $tableName = 'org_member';

    public function __construct() {
        parent::__construct();
    }

    /**
     * @desc 获取查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-08-29
     */
    public function getWhere($condition = []) {

        $where = [];

        if (!empty($condition['org_id'])) {
            $where['org_id'] = ['in', $condition['org_id']];
        }

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
     * @time 2017-08-29
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
     * @time 2017-08-29
     */
    public function getList($condition = [], $field = '*') {

        $where = $this->getWhere($condition);

        //$currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        //$pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        return $this->field($field)
                        ->where($where)
                        //->page($currentPage, $pageSize)
                        ->order('id DESC')
                        ->select();
    }

    /**
     * @desc 获取详情
     *
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf
     * @time 2017-08-29
     */
    public function getDetail($condition = [], $field = '*') {

        $where = $this->getWhere($condition);

        return $this->field($field)->where($where)->find();
    }

    /**
     * @desc 添加记录
     *
     * @param array $condition
     * @return mixed
     * @author liujf
     * @time 2017-08-29
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
     * @time 2017-08-29
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
     * @time 2017-08-29
     */
    public function delRecord($condition = []) {

        if (!empty($condition['r_id'])) {
            $where['id'] = ['in', explode(',', $condition['r_id'])];
        } else {
            return false;
        }

        return $this->where($where)->delete();
    }

    /*
     * @param array $condition
     * @return array
     * @author zhangyuliang
     * @time 2017-11-02
     */

    public function getOrgUserlist($condition = []) {
        if (empty($condition['org_id'])) {
            return ['code' => '-104', 'message' => '部门ID必填'];
        } else {
            $where['a.org_id'] = $condition['org_id'];
        }
        if (empty($condition['role_no'])) {
            return ['code' => '-104', 'message' => '角色编码必填'];
        } else {
            $where['c.role_no'] = $condition['role_no'];
        }
        if (!empty($condition['country_bn'])) {
            $where['e.country_bn'] = $condition['country_bn'];
        }
        if (!empty($condition['user_no'])) {
            $where['d.user_no'] = array('like', $condition['user_no']);
        }
        if (!empty($condition['username'])) {
            $where['d.name'] = array('like', $condition['username']);
        }

        $page = !empty($condition['currentPage']) ? $condition['currentPage'] : 1;
        $pagesize = !empty($condition['pageSize']) ? $condition['pageSize'] : 10;

        $where['d.status'] = 'NORMAL';

        try {
            $fields = 'd.id,d.user_no,d.name,c.name as role_name';
            if (!empty($condition['country_bn'])) {
                $list = $this->alias('a')
                        ->join('erui_sys.role_member b ON b.employee_id = a.employee_id', 'left')
                        ->join('erui_sys.role c ON c.id = b.role_id', 'left')
                        ->join('erui_sys.employee d ON d.id = a.employee_id', 'left')
                        ->join('erui_sys.country_member e ON e.employee_id = a.employee_id', 'left')
                        ->field($fields)
                        ->where($where)
                        ->page($page, $pagesize)
                        ->order('a.id DESC')
                        ->group('d.id')
                        ->select();
                $count = $this->alias('a')
                        ->join('erui_sys.role_member b ON b.employee_id = a.employee_id', 'left')
                        ->join('erui_sys.role c ON c.id = b.role_id', 'left')
                        ->join('erui_sys.employee d ON d.id = a.employee_id', 'left')
                        ->join('erui_sys.country_member e ON e.employee_id = a.employee_id', 'left')
                        ->field($fields)
                        ->where($where)
                        ->group('d.id')
                        ->select();
            } else {
                $list = $this->alias('a')
                        ->join('erui_sys.role_member b ON b.employee_id = a.employee_id', 'left')
                        ->join('erui_sys.role c ON c.id = b.role_id', 'left')
                        ->join('erui_sys.employee d ON d.id = a.employee_id', 'left')
                        ->field($fields)
                        ->where($where)
                        ->page($page, $pagesize)
                        ->order('a.id DESC')
                        ->group('d.id')
                        ->select();
                $count = $this->alias('a')
                        ->join('erui_sys.role_member b ON b.employee_id = a.employee_id', 'left')
                        ->join('erui_sys.role c ON c.id = b.role_id', 'left')
                        ->join('erui_sys.employee d ON  d.id = a.employee_id', 'left')
                        ->field($fields)
                        ->where($where)
                        ->group('d.id')
                        ->select();
            }

            if ($list) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['count'] = count($count);
                $results['data'] = $list;
            } else {
                $results['code'] = '-101';
                $results['message'] = '没有找到相关信息!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /*
     * 根据ID数组获取用户名称
     * @author  zhongyg
     * @param array $obtain_ids // 用户ID
     * @return array //返回用户数组
     * @date    2018-02-011 11:45:09
     * @version V2.0
     * @desc  获取单位信息处理
     */

    public function getOrgNamesByemployeeids($obtain_ids) {
        $ret = [];
        if ($obtain_ids && is_array($obtain_ids)) {
            $org_model = new OrgModel();
            $org_table = $org_model->getTableName();
            $users = $this->alias('om')
                    ->join($org_table . ' org on org.id=om.org_id and org.deleted_flag=\'N\'')
                    ->field('om.employee_id,org.name  as org_name')
                    ->where(['employee_id' => ['in', $obtain_ids], 'org.name is not null and org.name<>\'\''])
                    ->group('employee_id')
                    ->select();

            if ($users) {
                foreach ($users as $user) {
                    $ret[$user['employee_id']] = $user['org_name'];
                }
            }
        }
        return $ret;
    }

    public function getOrgIdsByUserid($user_id) {

        return $this->where(['employee_id' => $user_id])->getField('org_id', true);
    }

    Public function getSmsUserByOrgId($org_id) {
        if (!$org_id) {
            return [];
        }
        $role_member_table = (new RoleMemberModel)->getTableName();
        $role_table = (new RoleModel())->getTableName();
        $emploee_table = (new EmployeeModel())->getTableName();
        $user = $this->alias('om')
                ->field('e.name,e.mobile,e.email')
                ->join($role_member_table . ' rm on rm.employee_id=om.employee_id')
                ->join($role_table . ' r on r.id=rm.role_id')
                ->join($emploee_table . ' e on e.id=om.employee_id')
                ->where(['om.org_id' => $org_id, 'r.deleted_flag' => 'N', 'r.role_no' => 'SMS'])
                ->find();
        if (!$user) {
            $user = $this->alias('om')
                    ->field('e.name,e.mobile,e.email')
                    ->join($role_member_table . ' rm on rm.employee_id=om.employee_id')
                    ->join($role_table . ' r on r.id=rm.role_id')
                    ->join($emploee_table . ' e on e.id=om.employee_id')
                    ->where(['om.org_id' => $org_id, 'r.deleted_flag' => 'N', 'r.role_no' => ['in', ['A011', 'A004', 'A003', 'A002']]])
                    ->find();
        }
        return !empty($user) ? $user : [];
    }

}
