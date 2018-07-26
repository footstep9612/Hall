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
    const inquiryIssueRole = 'A002'; //易瑞辅分单员角色编号
    const quoteIssueMainRole = 'A003'; //报价辅分单员角色编号
    const quoteIssueAuxiliaryRole = 'A004'; //报价主分单员角色编号
    const quoterRole = 'A005'; //报价人角色编号
    const quoteCheckRole = 'A006'; //报价审核人角色编号
    const logiIssueMainRole = 'A007'; //物流报价主分单员角色编号
    const logiIssueAuxiliaryRole = 'A008'; //物流报价辅分单员角色编号
    const logiQuoterRole = 'A009'; //物流报价人角色编号
    const logiCheckRole = 'A010'; //物流报价审核人角色编号
    const inquiryIssueAuxiliaryRole = 'A011'; //易瑞主分单员角色编号
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

    public function info($where) {
        $inquirywhere['id'] = $where['id'];
        $inquiryinfo = $this
                        ->field('serial_no,agent_id,inflow_time,status as inquiry_status')
                        ->where($inquirywhere)->find();
        return $inquiryinfo;
    }

}
