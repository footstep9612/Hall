<?php

/**
 * @desc   询单模型
 * @Author 买买提
 */
class InquiryModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'inquiry';

    const marketAgentRole = 'A001'; //市场经办人角色编号
    const inquiryIssueRole = 'A002'; //易瑞主分单员角色编号
    const quoteIssueMainRole = 'A003'; //报价主分单员角色编号
    const quoteIssueAuxiliaryRole = 'A004'; //报价辅分单员角色编号
    const quoterRole = 'A005'; //报价人角色编号
    const quoteCheckRole = 'A006'; //报价审核人角色编号
    const logiIssueMainRole = 'A007'; //物流报价主分单员角色编号
    const logiIssueAuxiliaryRole = 'A008'; //物流报价辅分单员角色编号
    const logiQuoterRole = 'A009'; //物流报价人角色编号
    const logiCheckRole = 'A010'; //物流报价审核人角色编号
    const inquiryIssueAuxiliaryRole = 'A011'; //易瑞辅分单员角色编号
    const viewAllRole = 'A012'; //查看全部询单角色编号
    const viewBizDeptRole = 'A013'; //查看事业部询单角色编号
    const buyerCountryAgent = 'B001'; //区域负责人或国家负责人

    public $inquiryStatus = [
        'DRAFT' => '草稿',
        'BIZ_DISPATCHING' => '事业部分单员',
        'CC_DISPATCHING' => '易瑞客户中心分单员',
        'BIZ_QUOTING' => '事业部报价',
        'LOGI_DISPATCHING' => '物流分单员',
        'LOGI_QUOTING' => '物流报价',
        'LOGI_APPROVING' => '物流审核',
        'BIZ_APPROVING' => '事业部核算',
        'MARKET_APPROVING' => '市场主管审核',
        'MARKET_CONFIRMING' => '市场确认',
        'QUOTE_SENT' => '报价单已发出',
        'INQUIRY_CLOSED' => '报价关闭'
    ];

    public function __construct() {
        parent::__construct();
    }

    private $listFields = "id,serial_no,buyer_name,country_bn,now_agent_id,created_at,quote_status,status";

    /**
     * 获取统计数据
     * @param $type 类型
     * @return mixed
     */
    public function getStatisticsByType($type, $auth) {
        switch ($type) {
            case 'TODAY' :
                $where = "DATE_FORMAT(created_at,'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')";
                $data = $this->getListCount($auth, $this->listFields, $where);
                break;
            case 'TOTAL' :
                $data = $this->getListCount($auth, $this->listFields);
                break;
            case 'QUOTED' :
                $data = $data = $this->getListCount($auth, $this->listFields, ['quote_status' => 'QUOTED']);
                break;
        }
        return $data;
    }

    /**
     * @param $auth
     * @param $field
     *
     * @return mixed
     */
    public function getNewItems($auth, $field) {
        $data = array_slice($this->getList($auth, $field), 0, 3);

        $employee = new EmployeeModel();
        foreach ($data as $key => $value) {
            $data[$key]['name'] = $employee->where(['id' => $value['now_agent_id']])->getField('name');
            unset($data[$key]['now_agent_id']);
        }

        return $data;
    }

    /**
     * 创建对象
     * @param array $condition 数据
     * @return array
     */
    public function addData($condition = []) {

        $data = $this->create($condition);
        $time = $this->getTime();
        $data['quote_status'] = 'NOT_QUOTED';
        $data['inquiry_source'] = 'APP';
        $data['inflow_time'] = $time;
        $data['created_at'] = $time;

        try {
            $id = $this->add($data);
            if ($id) {
                $results = ['id' => $id, 'serial_no' => $data['serial_no']];
            } else {
                $results['code'] = '-101';
                $results['message'] = '添加失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    public function updateData($data = []) {

        if (!empty($data['status']))
            $data['inflow_time'] = $this->getTime();
        if (!empty($data['agent_id']))
            $data['now_agent_id'] = $data['agent_id'];
        $data['updated_at'] = $this->getTime();

        try {
            $id = $this->save($this->create($data));
            if ($id) {

                //处理附件
                if (isset($data['attach_list']) && !empty($data['attach_list'])) {

                    $inquiryAttach = new InquiryAttachModel();

                    foreach ($data['attach_list'] as $v) {

                        $flag = $inquiryAttach->where(['inquiry_id' => $data['id'], 'attach_name' => $v['attach_name']])->find();
                        if ($flag) {
                            $inquiryAttach->save($inquiryAttach->create([
                                        'inquiry_id' => $data['id'],
                                        'attach_group' => 'INQUIRY_SKU',
                                        'attach_name' => $v['attach_name'],
                                        'attach_url' => $v['attach_url'],
                                        'created_by' => $data['updated_by'],
                                        'created_at' => $this->getTime()
                            ]));
                        } else {
                            $inquiryAttach->add($inquiryAttach->create([
                                        'inquiry_id' => $data['id'],
                                        'attach_group' => 'INQUIRY_SKU',
                                        'attach_name' => $v['attach_name'],
                                        'attach_url' => $v['attach_url'],
                                        'created_by' => $data['updated_by'],
                                        'created_at' => $this->getTime()
                            ]));
                        }
                    }
                }

                //处理sku信息
                if (isset($data['sku_list']) && !empty($data['sku_list'])) {

                    $inquiryItem = new InquiryItemModel();

                    foreach ($data['sku_list'] as $v) {

                        $flag = $inquiryItem->where(['inquiry_id' => $data['id'], 'sku' => $v['sku']])->find();
                        if ($flag) {
                            $inquiryItem->save($inquiryItem->create([
                                        'sku' => $v['sku'],
                                        'qty' => $v['qty'],
                                        'name' => $v['name'],
                                        'unit' => $v['unit'],
                                        'brand' => $v['brand'],
                                        'model' => $v['model'],
                                        'name_zh' => $v['name_zh'],
                                        'remarks' => $v['remarks'],
                                        'inquiry_id' => $data['id'],
                                        'created_at' => $this->getTime()
                            ]));
                        } else {
                            $inquiryItem->add($inquiryItem->create([
                                        'sku' => $v['sku'],
                                        'qty' => $v['qty'],
                                        'name' => $v['name'],
                                        'unit' => $v['unit'],
                                        'brand' => $v['brand'],
                                        'model' => $v['model'],
                                        'name_zh' => $v['name_zh'],
                                        'remarks' => $v['remarks'],
                                        'inquiry_id' => $data['id'],
                                        'created_at' => $this->getTime()
                            ]));
                        }
                    }
                }

                //处理询单联系人
                if (isset($data['contact'])) {
                    $inquiryContact = new InquiryContactModel();
                    $inquiryContact->add($inquiryContact->create([
                                'inquiry_id' => $data['id'],
                                'name' => $data['contact']['name'],
                                'company' => $data['contact']['company'],
                                'country_bn' => $data['contact']['country_bn'],
                                'phone' => $data['contact']['phone'],
                                'email' => $data['contact']['email'],
                                'created_by' => $data['updated_by'],
                                'created_at' => date('Y-m-d H:i:s')
                    ]));
                }

                $results['code'] = 1;
                $results['message'] = '成功！';
            } else {
                $results['code'] = -1;
                $results['message'] = '修改失败!';
            }
        } catch (Exception $exception) {
            //$results['code'] = $exception->getCode();
            $results['code'] = -1; //兼容APP端
            $results['message'] = $exception->getMessage();
        }

        return $results;
    }

    public function getDetail($condition, $field = "*") {
        return $this->where($condition)->field($field)->find();
    }

    /**
     * @desc 获取列表
     *
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf
     * @time 2017-10-18
     */
    public function getList($condition = [], $field = '*', $where1 = []) {

        $where = $this->getWhere($condition);
        //$where[]="updated_by is NOT NULL";

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        return $this->field($field)
                        ->where($where)
                        ->where($where1)
                        ->page($currentPage, $pageSize)
                        ->order('id DESC')
                        ->select();
    }

    public function getListCount($condition = [], $field = '*', $where1 = []) {

        $where = $this->getWhere($condition);

        $count = $this->where($where)->where($where1)->count('id');

        return $count > 0 ? $count : 0;
    }

    public function getList_($condition = [], $field = '*') {

        $where = $this->getWhere($condition);

        //$where[]="updated_by is NOT NULL";

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        return $this->field($field)
                        ->where($where)
                        ->page($currentPage, $pageSize)
                        ->order('id DESC')
                        ->select();
    }

    public function getCount_($condition = []) {

        $where = $this->getWhere($condition);
        //$where[]="updated_by is NOT NULL";

        $count = $this->where($where)->count('id');

        return $count > 0 ? $count : 0;
    }

    /**
     * @desc 获取记录总数
     *
     * @param array $condition
     * @return int $count
     * @author liujf
     * @time 2017-10-19
     */
    public function getCount($condition = []) {

        $where = $this->getWhere($condition);

        $count = $this->where($where)->count('id');

        return $count > 0 ? $count : 0;
    }

    /**
     * @desc 获取查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-10-18
     */
    public function getWhere($condition = []) {

        $where['deleted_flag'] = 'N';

        if (!empty($condition['quote_status'])) {
            $where['quote_status'] = $condition['quote_status'];    //项目状态
        }

        if (!empty($condition['status'])) {
            $where['status'] = $condition['status'];    //项目状态
        }

        if (!empty($condition['country_bn'])) {
            $where['country_bn'] = $condition['country_bn'];    //国家
        }

        if (!empty($condition['serial_no'])) {
            $where['serial_no'] = $condition['serial_no'];  //流程编码
        }

        if (!empty($condition['buyer_name'])) {
            //$where['buyer_name'] = $condition['buyer_name'];  //客户名称
            $where['buyer_name'] = ['like', '%' . $condition['buyer_name'] . '%'];
        }

        if (!empty($condition['agent_id'])) {
            $where['agent_id'] = ['in', $condition['agent_id']]; //市场经办人
        }

        if (!empty($condition['start_time']) && !empty($condition['end_time'])) {   //询价时间
            $where['created_at'] = [
                ['egt', date('Y-m-d H:i:s', $condition['start_time'])],
                ['elt', date('Y-m-d H:i:s', $condition['end_time'] + 24 * 3600 - 1)]
            ];
        }

        if (!empty($condition['list_type'])) {
            $map = [];

            switch ($condition['list_type']) {
                case 'inquiry' :
                    $map[] = ['created_by' => $condition['user_id']];

                    foreach ($condition['role_no'] as $roleNo) {
                        if ($roleNo == self::marketAgentRole) {
                            $map[] = ['agent_id' => $condition['user_id']];
                        }
                    }
                    break;
                case 'quote' :
                    foreach ($condition['role_no'] as $roleNo) {
                        if ($roleNo == self::inquiryIssueRole || $roleNo == self::inquiryIssueAuxiliaryRole) {
                            $orgId = $this->getDeptOrgId($condition['group_id'], 'erui');

                            if ($orgId)
                                $map[] = ['erui_id' => ['in', $orgId]];
                        }
                        if ($roleNo == self::quoteIssueMainRole || $roleNo == self::quoteIssueAuxiliaryRole) {
                            $orgId = $this->getDeptOrgId($condition['group_id'], ['in', ['ub', 'erui', 'eub']]);

                            if ($orgId)
                                $map[] = ['org_id' => ['in', $orgId]];
                        }
                        if ($roleNo == self::quoterRole) {
                            $map[] = ['quote_id' => $condition['user_id']];
                        }
                        if ($roleNo == self::quoteCheckRole) {
                            $map[] = ['check_org_id' => $condition['user_id']];
                        }
                    }
                    break;
                case 'logi' :
                    foreach ($condition['role_no'] as $roleNo) {
                        if ($roleNo == self::logiIssueMainRole || $roleNo == self::logiIssueAuxiliaryRole) {
                            $orgId = $this->getDeptOrgId($condition['group_id'], ['in', ['lg', 'elg']]);

                            if ($orgId)
                                $map[] = ['logi_org_id' => ['in', $orgId]];
                        }
                        if ($roleNo == self::logiQuoterRole) {
                            $map[] = ['logi_agent_id' => $condition['user_id']];
                        }
                        if ($roleNo == self::logiCheckRole) {
                            $map[] = ['logi_check_id' => $condition['user_id']];
                        }
                    }
            }

            if ($map) {
                $map['_logic'] = 'or';
            } else {
                $map['id'] = '-1';
            }

            $where[] = $map;
        }

        return $where;
    }

    /*
     * 检查流程编码是否存在
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */

    public function checkSerialNo($condition = []) {
        if (!empty($condition['serial_no'])) {
            $where['serial_no'] = $condition['serial_no'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有流程编码!';
            return $results;
        }

        try {
            $id = $this->field('id')->where($where)->find();
            if ($id) {
                $results['code'] = '1';
                $results['message'] = '成功！';
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

    /**
     * 格式化返回当前时间
     * @return false|string
     */
    private function getTime() {
        return date('Y-m-d H:i:s', time());
    }

    /**
     * @desc 获取询单办理部门组ID
     *
     * @param array $groupId 当前用户的全部组ID
     * @param mixed $orgNode 部门节点
     * @return array
     * @author liujf
     * @time 2017-10-20
     */
    public function getDeptOrgId($groupId = [], $orgNode = ['in', ['ub', 'eub']]) {
        $orgModel = new OrgModel();

        $where = [
            'id' => ['in', $groupId ?: ['-1']],
            'org_node' => $orgNode
        ];
        $orgList = $orgModel->field('id')->where($where)->select();

        // 用户所在部门的组ID
        $orgId = [];
        foreach ($orgList as $org) {
            $orgId[] = $org['id'];
        }

        return $orgId;
    }

    /**
     * @desc 获取指定角色用户ID
     *
     * @param array $groupId 当前用户的全部组ID
     * @param string $roleNo 角色编号
     * @param mixed $orgNode 部门节点
     * @return array
     * @author liujf
     * @time 2017-10-23
     */
    public function getRoleUserId($groupId = [], $roleNo = '', $orgNode = ['in', ['ub', 'eub']]) {
        $orgMemberModel = new OrgMemberModel();
        $roleModel = new RoleModel();
        $roleUserModel = new RoleUserModel();

        $orgId = $this->getDeptOrgId($groupId, $orgNode);

        $role = $roleModel->field('id')->where(['role_no' => $roleNo])->find();

        $roleUserList = $roleUserModel->field('employee_id')->where(['role_id' => $role['id']])->select();

        $employeeId = [];

        foreach ($roleUserList as $roleUser) {
            $employeeId[] = $roleUser['employee_id'];
        }

        $orgMember = $orgMemberModel->field('employee_id')->where(['org_id' => ['in', $orgId ?: ['-1']], 'employee_id' => ['in', $employeeId ?: ['-1']]])->find();

        return $orgMember['employee_id'];
    }

}
