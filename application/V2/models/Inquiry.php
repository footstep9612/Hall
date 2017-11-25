<?php
/**
 * name: Inquiry
 * desc: 询价单表
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:11
 */
class InquiryModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry'; //数据表表名
    
    const  marketAgentRole = 'A001'; //市场经办人角色编号
    const  inquiryIssueRole = 'A002'; //易瑞主分单员角色编号
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
        'REJECT_MARKET' => '驳回市场',
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

    /**
     * 根据条件获取查询条件
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    protected function getCondition($condition = []) {
        $where = [];
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
            $where['buyer_name'] = $condition['buyer_name'];  //客户名称
        }
        if (!empty($condition['agent_id'])) {
            $where['agent_id'] = array('in',$condition['agent_id']);//市场经办人
        }
        if (!empty($condition['pm_id'])) {
            $where['pm_id'] = $condition['pm_id'];  //项目经理
        }
        if (!empty($condition['status'])) {
            $where['status'] = $condition['status'];  //项目经理
        }
        if (!empty($condition['start_time']) && !empty($condition['end_time'])) {   //询价时间
            $where['created_at'] = array(
                array('gt',date('Y-m-d H:i:s',$condition['start_time'])),
                array('lt',date('Y-m-d H:i:s',$condition['end_time']))
            );
        }
        $where['deleted_flag'] = !empty($condition['deleted_flag'])?$condition['deleted_flag']:'N'; //删除状态

        return $where;
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
        
        if (!empty($condition['status'])) {
            $where['status'] = $condition['status'];    //项目状态
        }else if($condition['list_type'] == 'quote'){
            $where['status'] = array('neq','DRAFT');
        }
        
        if (!empty($condition['country_bn'])) {
            $where['country_bn'] = $condition['country_bn'];    //国家
        }
        
        if (!empty($condition['serial_no'])) {
            $where['serial_no'] = $condition['serial_no'];  //流程编码
        }
        
        if (!empty($condition['buyer_name'])) {
            $where['buyer_name'] = $condition['buyer_name'];  //客户名称
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
                            
                            if ($orgId) $map[] = ['erui_id' => ['in', $orgId]];
                        }
                        if ($roleNo == self::quoteIssueMainRole || $roleNo == self::quoteIssueAuxiliaryRole) {
                            $orgId = $this->getDeptOrgId($condition['group_id'], ['in', ['ub','erui']]);
                            
                            if ($orgId) $map[] = ['org_id' => ['in', $orgId]];
                        }
                        if ($roleNo == self::inquiryIssueAuxiliaryRole || $roleNo == self::quoteIssueAuxiliaryRole) {
                            $map[] = ['country_bn' => $condition['user_country']];
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
                            $orgId = $this->getDeptOrgId($condition['group_id'], 'lg');
                            
                            if ($orgId) $map[] = ['logi_org_id' => ['in', $orgId]];
                        }
                        if ($roleNo == self::logiIssueAuxiliaryRole) {
                            $map[] = ['country_bn' => $condition['user_country']];
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
    
    /**
     * @desc 获取查询条件
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-11-02
     */
    public function getViewWhere($condition = []) {
        
        $where['status'] = ['neq', 'DRAFT'];
        $where['deleted_flag'] = 'N';
    
        if (!empty($condition['status']) && $condition['status'] != 'DRAFT') {
            $where['status'] = $condition['status'];    //项目状态
        }
        if (!empty($condition['quote_status'])) {
            $where['quote_status'] = $condition['quote_status'];    //报价状态
        }
    
        if (!empty($condition['country_bn'])) {
            $where['country_bn'] = $condition['country_bn'];    //国家
        }
    
        if (!empty($condition['serial_no'])) {
            $where['serial_no'] = $condition['serial_no'];  //流程编码
        }
    
        if (!empty($condition['buyer_name'])) {
            $where['buyer_name'] = $condition['buyer_name'];  //客户名称
        }
    
        if (!empty($condition['agent_id'])) {
            $where['agent_id'] = ['in', $condition['agent_id']]; //市场经办人
        }
        
        if (!empty($condition['org_id'])) {
            $where['org_id'] = ['in', $condition['org_id']]; //事业部
        }
    
        if (!empty($condition['start_time']) && !empty($condition['end_time'])) {   //询价时间
            $where['created_at'] = [
                ['egt', date('Y-m-d H:i:s', $condition['start_time'])],
                ['elt', date('Y-m-d H:i:s', $condition['end_time'] + 24 * 3600 - 1)]
            ];
        }
         
        return $where;
    }

    /**
     * 获取数据条数
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getCount($condition = []) {
        $where = $this->getCondition($condition);

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
    public function getCount_($condition = []) {
         
        $where = $this->getWhere($condition);
         
        $count = $this->where($where)->count('id');
         
        return $count > 0 ? $count : 0;
    }
    
    /**
     * @desc 获取记录总数
     *
     * @param array $condition
     * @return int $count
     * @author liujf
     * @time 2017-11-02
     */
    public function getViewCount($condition = []) {
         
        $where = $this->getViewWhere($condition);
         
        $count = $this->where($where)->count('id');
         
        return $count > 0 ? $count : 0;
    }

    /**
     * 获取列表
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getList($condition = []) {
        $where = $this->getCondition($condition);

        if(!empty($condition['user_id'])){
            if(!empty($condition['agent_id'])){
                if(!empty($condition['status'])){
                    if(!in_array($condition['user_id'],$condition['agent_id'])){
                        $condition['agent_id'][] = $condition['user_id'];
                    }
                    switch($condition['status']) {
                        case 'DRAFT':
                            $where2 ='(agent_id='.$condition['user_id'].') or (created_by='.$condition['user_id'].') ';
                            break;
                        default:
                            $where2 ='agent_id in('.implode(',',$condition['agent_id']).') ';
                            break;
                    }
                }else{
                    $where2 = '(agent_id in('.implode(',',$condition['agent_id']).') and status<>"DRAFT") ';
                    $where2 .='or (agent_id='.$condition['user_id'].') ';
                    $where2 .='or (created_by='.$condition['user_id'].' and status="DRAFT") ';
                }
                unset($where['agent_id']);
            }else{
                $where2 = '(agent_id='.$condition['user_id'].') or (created_by='.$condition['user_id'].' and status="DRAFT")';
            }
        }

        $page = !empty($condition['currentPage'])?$condition['currentPage']:1;
        $pagesize = !empty($condition['pageSize'])?$condition['pageSize']:10;

        try {
            if(!empty($where2)){
                $count = $this->where($where)->where($where2)->count('id');
                $list = $this->where($where)
                        ->where($where2)
                        ->page($page, $pagesize)
                        ->order('updated_at desc')
                        ->select();
            }else{
                $count = $this->where($where)->count('id');
                $list = $this->where($where)
                        ->page($page, $pagesize)
                        ->order('updated_at desc')
                        ->select();
            }
            $count = $count > 0 ? $count : 0;

            if($list){
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['count'] = $count;
                $results['data'] = $list;
            }else{
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
     * @desc 获取列表
     *
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf
     * @time 2017-10-18
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
    
    /**
     * @desc 获取列表
     *
     * @param array $condition
     * @param string $field
     * @return array
     * @author liujf
     * @time 2017-11-02
     */
    public function getViewList($condition = [], $field = '*') {
    
        $where = $this->getViewWhere($condition);
         
        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize =  empty($condition['pageSize']) ? 10 : $condition['pageSize'];
         
        return $this->field($field)
                            ->where($where)
                            ->page($currentPage, $pageSize)
                            ->order('id DESC')
                            ->select();
    }

    /**
     * 获取详情信息
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getInfo($condition = []) {
        if(!empty($condition['id'])){
            $where['id'] = $condition['id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有id!';
            return $results;
        }

        try {
            $info = $this->where($where)->find();

            if($info){
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $info;
            }else{
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
     * 添加数据
     * @return mix
     * @author zhangyuliang
     */
    public function addData($condition = []) {
        $data = $this->create($condition);

        if(!empty($condition['serial_no'])) {
            $data['serial_no'] = $condition['serial_no'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有流程编码!';
            return $results;
        }

        $time = $this->getTime();
        
        $data['quote_status'] = 'NOT_QUOTED';
        $data['inflow_time'] = $time;
        $data['created_at'] = $time;

        try {
            $id = $this->add($data);
            $data['id'] = $id;
            if($id){
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $data;
            }else{
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

    /**
     * 更新数据
     * @param  Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function updateData($condition = []) {
        $data = $this->create($condition);
        if(!empty($condition['id'])){
            $where['id'] = $condition['id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有ID!';
            return $results;
        }
        
        $time = $this->getTime();
        
        if (!empty($condition['status'])) $data['inflow_time'] = $time;
        
        $data['updated_at'] = $time;

        try {
            $id = $this->where($where)->save($data);
            if($id){
                $results['code'] = '1';
                $results['message'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['message'] = '修改失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /*
     * 批量更新状态
     * @param  mix $condition
     * @param  int $serial_no 询单号
     * @return bool
     */
    public function updateStatus($condition = []) {
        if(!empty($condition['id'])){
            $where['id'] = array('in',explode(',',$condition['id']));
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有ID!';
            return $results;
        }
        
        $time = $this->getTime();
        
        if(!empty($condition['status'])){
            $data['inflow_time'] = $time;
            $data['status'] = $condition['status'];
        }

        if(!empty($condition['now_agent_id'])){
            $data['now_agent_id'] = $condition['now_agent_id'];
        }
        
        $data['updated_at'] = $time;

        try {
            $id = $this->where($where)->save($data);
            if($id){
                $results['code'] = '1';
                $results['message'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['message'] = '修改失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 删除数据
     * @param  Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function deleteData($condition = []) {
        if(!empty($condition['id'])){
            $where['id'] = array('in',explode(',',$condition['id']));
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有ID!';
            return $results;
        }

        try {
            $id = $this->where($where)->save(['deleted_flag' => 'Y']);
            if($id){
                $results['code'] = '1';
                $results['message'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['message'] = '删除失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /*
     * 检查流程编码是否存在
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function checkSerialNo($condition = []){
        if(!empty($condition['serial_no'])){
            $where['serial_no'] = $condition['serial_no'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有流程编码!';
            return $results;
        }

        try {
            $id = $this->field('id')->where($where)->find();
            if($id){
                $results['code'] = '1';
                $results['message'] = '成功！';
            }else{
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
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d H:i:s',time());
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
    public function getDeptOrgId($groupId = [], $orgNode = 'ub') {
        $orgModel = new OrgModel();
        
        $where = [
             'id' => ['in', $groupId ? : ['-1']],
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
    public function getRoleUserId($groupId = [], $roleNo = '', $orgNode = 'ub') {
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
        
        $orgMember = $orgMemberModel->field('employee_id')->where(['org_id' => ['in', $orgId ? : ['-1']], 'employee_id' => ['in', $employeeId ? : ['-1']]])->find();
    
        return $orgMember['employee_id'];
    }
    
    /**
     * @desc 根据用户ID获取用户角色
     *
     * @param string $userId 用户ID
     * @return array
     * @author liujf
     * @time 2017-11-24
     */
    public function getUserRoleById($userId = '') {
        $roleUserModel = new RoleUserModel();
        $roleModel = new RoleModel();
    
        $roleUserList = $roleUserModel->field('role_id')->where(['employee_id' => $userId ? : '-1'])->select();
    
        $roleId = [];
    
        foreach ($roleUserList as $roleUser) {
            $roleId[] = $roleUser['role_id'];
        }
    
        $roleList = $roleModel->field('role_no')->where(['id' => ['in', $roleId ? : ['-1']]])->select();
    
        $roleNoArr = [];
    
        foreach ($roleList as $role) {
            $roleNoArr[] = $role['role_no'];
        }
    
        return $this->getUserRoleByNo($roleNoArr);
    }
    
    /**
     * @desc 根据角色编号判断用户角色
     *
     * @param array $roleNoArr 用户的全部角色编号
     * @return array
     * @author liujf
     * @time 2017-11-24
     */
    public function getUserRoleByNo($roleNoArr = []) {
        // 是否市场经办人的标识
        $isAgent = 'N';
    
        // 是否易瑞分单员的标识
        $isErui = 'N';
    
        // 是否分单员的标识
        $isIssue = 'N';
    
        // 是否报价人的标识
        $isQuote = 'N';
    
        // 是否审核人的标识
        $isCheck = 'N';
    
        // 会员管理国家负责人
        $isCountryAgent = 'N';
    
        foreach ($roleNoArr as $roleNo) {
            if ($roleNo == self::marketAgentRole) {
                $isAgent = 'Y';
            }
            if ($roleNo == self::inquiryIssueRole || $roleNo == self::inquiryIssueAuxiliaryRole) {
                $isErui = 'Y';
            }
            if ($roleNo == self::inquiryIssueRole || $roleNo == self::quoteIssueMainRole || $roleNo == self::quoteIssueAuxiliaryRole || $roleNo == self::logiIssueMainRole || $roleNo == self::logiIssueAuxiliaryRole) {
                $isIssue = 'Y';
            }
            if ($roleNo == self::quoterRole || $roleNo == self::logiQuoterRole) {
                $isQuote = 'Y';
            }
            if ($roleNo == self::quoteCheckRole || $roleNo == self::logiCheckRole) {
                $isCheck = 'Y';
            }
            if ($roleNo == self::buyerCountryAgent) {
                $isCountryAgent = 'Y';
            }
        }
    
        $data['is_agent'] = $isAgent;
        $data['is_erui'] = $isErui;
        $data['is_issue'] = $isIssue;
        $data['is_quote'] = $isQuote;
        $data['is_check'] = $isCheck;
        $data['is_country_agent'] = $isCountryAgent;
    
        return $data;
    }

    /**
     * 更新用户信息和询单经办人等信息
     * @param  Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function setBuyerAgentInfo($condition = []) {
        if(!empty($condition['buyer_id'])){
            $where['buyer_id'] = $condition['buyer_id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = '没有客户ID!';
            return $results;
        }
        $where['status'] = !empty($condition['status'])?$condition['status']:'DRAFT';

        if(!empty($condition['agent_id'])){
            $data['agent_id'] = $condition['agent_id'];
            $data['now_agent_id'] = $condition['agent_id'];
        }

        try {
            $id = $this->where($where)->save($data);
            if($id){
                $results['code'] = '1';
                $results['message'] = '成功！';
            }else{
                $results['code'] = '-101';
                $results['message'] = '修改失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }
}
