<?php

/**
 * name: Inquiry
 * desc: 询价单表
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:11
 */
class Rfq_InquiryModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry'; //数据表表名

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
    const viewCountryRole = 'A015'; //查看国家角色编号(A014被占用)
    const buyerCountryAgent = 'B001'; //区域负责人或国家负责人

    public function __construct() {
        parent::__construct();
    }

    /**
     * @desc 获取询单状态
     *
     * @return array
     * @author liujf
     * @time 2018-01-28
     */
    public function getInquiryStatus() {
        return [
            'DRAFT' => L('INQUIRY_DRAFT'),
            'CLARIFY' => L('INQUIRY_CLARIFY'),
            'REJECT_MARKET' => L('INQUIRY_REJECT_MARKET'),
            'BIZ_DISPATCHING' => L('INQUIRY_BIZ_DISPATCHING'),
            'CC_DISPATCHING' => L('INQUIRY_CC_DISPATCHING'),
            'BIZ_QUOTING' => L('INQUIRY_BIZ_QUOTING'),
            'REJECT_QUOTING' => L('INQUIRY_REJECT_QUOTING'),
            'LOGI_DISPATCHING' => L('INQUIRY_LOGI_DISPATCHING'),
            'LOGI_QUOTING' => L('INQUIRY_LOGI_QUOTING'),
            'LOGI_APPROVING' => L('INQUIRY_LOGI_APPROVING'),
            'BIZ_APPROVING' => L('INQUIRY_BIZ_APPROVING'),
            'MARKET_APPROVING' => L('INQUIRY_MARKET_APPROVING'),
            'MARKET_CONFIRMING' => L('INQUIRY_MARKET_CONFIRMING'),
            'QUOTE_SENT' => L('INQUIRY_QUOTE_SENT'),
            'INQUIRY_CLOSED' => L('INQUIRY_INQUIRY_CLOSED'),
            'REJECT_CLOSE' => L('INQUIRY_REJECT_CLOSE'),
            'INQUIRY_CONFIRM' => L('INQUIRY_INQUIRY_CONFIRM')
        ];
    }

    /**
     * @desc 获取报价状态
     *
     * @return array
     * @author liujf
     * @time 2018-01-28
     */
    public function getQuoteStatus() {
        return [
            'DRAFT' => L('QUOTE_DRAFT'),
            'NOT_QUOTED' => L('QUOTE_NOT_QUOTED'),
            'ONGOING' => L('QUOTE_ONGOING'),
            'QUOTED' => L('QUOTE_QUOTED'),
            'COMPLETED' => L('QUOTE_COMPLETED')
        ];
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
            $status = explode(',', $condition['status']);
            $where['status'] = ['in', $status];    //项目状态
        }
        if (!empty($condition['country_bn'])) {
            $where['country_bn'] = $condition['country_bn'];    //国家
        }

        if (!empty($condition['start_time']) && !empty($condition['end_time'])) {   //询价时间
            $where['created_at'] = array(
                array('gt', date('Y-m-d H:i:s', $condition['start_time'])),
                array('lt', date('Y-m-d H:i:s', $condition['end_time']))
            );
        }
        $where['deleted_flag'] = !empty($condition['deleted_flag']) ? $condition['deleted_flag'] : 'N'; //删除状态


        if (!empty($condition['buyer_no'])) {
            $buyer_ids = (new Buyer_BuyerModel())->getBuyerIdByBuyerNo($condition['buyer_no']);
            $where['buyer_id'] = ['in', !empty($buyer_ids) ? $buyer_ids : [-1]];
        }

        if (!empty($condition['agent_name'])) {
            $agent_ids = (new System_EmployeeModel())->getUserIdByName($condition['agent_name']);
            $where['agent_id'] = ['in', !empty($agent_ids) ? $agent_ids : [-1]];
        }

        if (!empty($condition['pm_id'])) {
            $where['pm_id'] = $condition['pm_id'];  //项目经理
        }
        if (!empty($condition['serial_no'])) {
            $where['serial_no'] = ['like', '%' . $condition['serial_no'] . '%']; //流程编码
        }
        if (!empty($condition['buyer_code'])) {
            $where['buyer_code'] = ['like', '%' . $condition['buyer_code'] . '%'];  //客户编码
        }
        return $where;
    }

    /**
     * @desc 获取列表
     *
     * @param array $condition
     * @return array
     * @author liujf
     * @time 2017-10-18
     */
    public function getList($condition = []) {
        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];
        $field = 'id,serial_no,country_bn,buyer_id,buyer_inquiry_no,buyer_code,buyer_name,buyer_oil,'
                . 'agent_id,erui_id,org_id,quote_id,check_org_id,created_by,'
                . 'created_at,updated_by,updated_at,checked_by,checked_at';

        $where = $this->getCondition($condition);
        $data = $this
                ->where($where)
                ->field($field)
                ->page($currentPage, $pageSize)
                ->order('updated_at DESC,created_at DESC')
                ->select();
        return $data;
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
        $where = $this->getCondition($condition);
        $count = $this->where($where)->count();

        return $count > 0 ? $count : 0;
    }

}
