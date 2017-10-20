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
    
    const  inquiryIssueRole = 'A001'; //客户中心分单员角色编号
    const quoteIssueMainRole = 'A002'; //报价主分单员角色编号
    const quoteIssueAuxiliaryRole = 'A003'; //报价辅分单员角色编号
    const quoteCheckRole = 'A004'; //报价审核人角色编号
    const logiIssueMainRole = 'A005'; //物流报价主分单员角色编号
    const logiIssueAuxiliaryRole = 'A006'; //物流报价辅分单员角色编号
    const logiCheckRole = 'A007'; //物流报价审核人角色编号

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
            $where['agent_id'] = ['in', $condition['agent_id']]; //市场经办人
        }

        if (!empty($condition['start_time']) && !empty($condition['end_time'])) {   //询价时间
            $where['created_at'] = [
                ['egt', $condition['start_time']],
                ['elt', $condition['end_time'] . ' 23:59:59']
            ];
        }
        
        if (!empty($condition['list_type'])) {
            switch ($condition['list_type']) {
                case 'inquiry' :
                    $map[] = ['created_by' => $condition['user_id']];
                    $map[] = ['agent_id' => $condition['user_id']];
                    
                    foreach ($condition['role_no'] as $roleNo) {
                        if ($roleNo == $this->inquiryIssueRole) {
                            $map[] = ['erui_id' => $condition['user_id']];
                        }
                    }
                    break;
                case 'quote' :
                    $map[] = ['quote_id' => $condition['user_id']];
                    
                    foreach ($condition['role_no'] as $roleNo) {
                        if ($roleNo == $this->quoteIssueMainRole || $roleNo == $this->quoteIssueAuxiliaryRole) {
                            $orgId = $this->_getDeptOrgId($condition['group_id']);
                            
                            if ($orgId) $map[] = ['org_id' => ['in', $orgId]];
                        }
                        if ($roleNo == $this->quoteCheckRole) {
                            $orgId = $this->_getDeptOrgId($condition['group_id']);
                            
                            if ($orgId) $map[] = ['check_org_id' => ['in', $orgId]];
                        }
                    }
                    break;
                case 'logi' :
                    $map[] = ['logi_agent_id' => $condition['user_id']];
                    
                    foreach ($condition['role_no'] as $roleNo) {
                        if ($roleNo == $this->logiIssueMainRole || $roleNo == $this->logiIssueAuxiliaryRole) {
                            $orgId = $this->_getDeptOrgId($condition['group_id'], 'lg');
                            
                            if ($orgId) $map[] = ['logi_org_id' => ['in', $orgId]];
                        }
                        if ($roleNo == $this->logiCheckRole) {
                            $orgId = $this->_getDeptOrgId($condition['group_id'], 'lg');
                            
                            if ($orgId) $map[] = ['logi_check_id' => ['in', $orgId]];
                        }
                    }
            }
            
            $map['_logic'] = 'or';
            $where[] = $map;
        }
    
        $where['deleted_flag'] = 'N';
         
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
        $data['status'] = 'DRAFT';
        $data['created_at'] = $this->getTime();

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
        $data['updated_at'] = $this->getTime();

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
        if(!empty($condition['status'])){
            $data['status'] = $condition['status'];
        }
        $data['updated_at'] = $this->getTime();

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
     * @param string $orgNode 部门节点
     * @return array
     * @author liujf
     * @time 2017-10-20
     */
    private function _getDeptOrgId($groupId = [], $orgNode = 'ub') {
        $org = new OrgModel();
        
        $where = [
             'id' => ['in', $groupId ? : ['-1']],
             'org_node' => $orgNode
        ];
        $orgList = $org->field('id')->where($where)->select();
        
        // 用户所在部门的组ID
        $orgId = [];
        foreach ($orgList['id'] as $id) {
            $orgId[] = $id;
        }
        
        return $orgId;
    }
}
