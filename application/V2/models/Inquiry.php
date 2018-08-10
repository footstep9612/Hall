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
        if (!empty($condition['buyer_inquiry_no'])) {
            $where['buyer_inquiry_no'] = $condition['buyer_inquiry_no'];    //客户询单号
        }
        if (!empty($condition['agent_id'])) {
            $where['agent_id'] = array('in', $condition['agent_id']); //市场经办人
        }
        if (!empty($condition['pm_id'])) {
            $where['pm_id'] = $condition['pm_id'];  //项目经理
        }
        if (!empty($condition['status'])) {
            $where['status'] = $condition['status'];  //项目经理
        }
        if (!empty($condition['start_time']) && !empty($condition['end_time'])) {   //询价时间
            $where['created_at'] = array(
                array('gt', date('Y-m-d H:i:s', $condition['start_time'])),
                array('lt', date('Y-m-d H:i:s', $condition['end_time']))
            );
        }
        $where['deleted_flag'] = !empty($condition['deleted_flag']) ? $condition['deleted_flag'] : 'N'; //删除状态

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

        $where['a.deleted_flag'] = 'N';
        $quotingStatusList = ['REJECT_QUOTING', 'BIZ_QUOTING'];
        if ($condition['list_type'] != 'inquiry') {
            $where['a.status'] = ['neq', 'DRAFT'];
            if (!empty($condition['status']) && $condition['status'] != 'DRAFT') {
                if ($condition['status'] == 'BIZ_QUOTING') {
                    $where['a.status'] = ['in', $quotingStatusList];
                } else {
                    $where['a.status'] = $condition['status'];    //项目状态
                }
            }
        } else {
            if (!empty($condition['status'])) {
                if ($condition['status'] == 'BIZ_QUOTING') {
                    $where['a.status'] = ['in', $quotingStatusList];
                } else {
                    $where['a.status'] = $condition['status'];
                }
            }
            // 询单管理操作状态的筛选
            $inquiryStatusList = ['DRAFT', 'REJECT_MARKET', 'MARKET_APPROVING', 'MARKET_CONFIRMING'];
            $inStatus = ['in', $inquiryStatusList];
            $notInStatus = ['not in', $inquiryStatusList];
            $transmitStatus = is_array($where['a.status']) ? $where['a.status'] : ['eq', $condition['status']];
            $inResult = empty($condition['status']) ? $inStatus : [$inStatus, $transmitStatus];
            $notInResult = empty($condition['status']) ? $notInStatus : [$notInStatus, $transmitStatus];
            switch ($condition['operating_state']) {
                case 'VIEW' :
                    $where['a.status'] = $notInResult;
                    break;
                case 'HANDLE' :
                    $where['a.status'] = $inResult;
                    break;
                case 'URGE' :
                    $where['a.status'] = $inResult;
                    $where['b.id'] = ['gt', 0];
            }
        }

        if (!empty($condition['buyer_code'])) {
            $where['a.buyer_code'] = ['like', '%' . $condition['buyer_code'] . '%'];  //客户编码
        }

        if (!empty($condition['country_bn'])) {
            $where['a.country_bn'] = $condition['country_bn'];    //国家
        }

        /* if (!empty($condition['serial_no'])) {
          $where['a.serial_no'] = ['like', '%' . $condition['serial_no'] . '%'];  //流程编码
          } */

        if (!empty($condition['buyer_name'])) {
            $where['a.buyer_name'] = ['like', '%' . $condition['buyer_name'] . '%'];  //客户名称
        }

        /* if (!empty($condition['buyer_code'])) {
          $where['a.buyer_code'] = ['like', '%' . $condition['buyer_code'] . '%'];  //客户编码
          } */

        if (!empty($condition['input_code'])) {
            // 用户输入编码匹配
            $match = [
                'a.serial_no' => $condition['input_code'],
                'a.buyer_code' => $condition['input_code'],
                'a.id' => ['in', (new InquiryOrderModel())->getInquiryIdByContractNo($condition['input_code']) ?: ['-1']],
                '_logic' => 'or'
            ];
            $where[] = $match;
        }

        if (!empty($condition['buyer_inquiry_no'])) {
            $where['a.buyer_inquiry_no'] = ['like', '%' . $condition['buyer_inquiry_no'] . '%'];    //客户询单号
        }

        if (isset($condition['agent_id'])) {
            $where['a.agent_id'] = ['in', $condition['agent_id'] ?: ['-1']]; //市场经办人
        }

        if (isset($condition['now_agent_id'])) {
            $where['a.now_agent_id'] = ['in', $condition['now_agent_id'] ?: ['-1']]; //当前办理人
        }

        if (isset($condition['quote_id'])) {
            $where['a.quote_id'] = ['in', $condition['quote_id'] ?: ['-1']]; //报价人
        }

        if (isset($condition['contract_inquiry_id'])) {
            if ($condition['contract_no'] == 'Y') {
                $where['a.id'] = ['in', $condition['contract_inquiry_id'] ?: ['-1']]; //销售合同号存在
            } else {
                $where['a.id'] = ['not in', $condition['contract_inquiry_id'] ?: ['-1']]; //销售合同号不存在
            }
        }

        if (!empty($condition['start_time']) && !empty($condition['end_time'])) {   //询价时间
            $where['a.created_at'] = [
                ['egt', date('Y-m-d H:i:s', $condition['start_time'])],
                ['elt', date('Y-m-d H:i:s', $condition['end_time'] + 24 * 3600 - 1)]
            ];
        }
        if (!empty($condition['submit_start_time']) && !empty($condition['submit_end_time'])) {   //报出日期
            $check_model = new InquiryCheckLogModel();
            $check_table = $check_model->getTableName();
            $start_time = date('Y-m-d H:i:s', $condition['submit_start_time']);
            $end_time = date('Y-m-d H:i:s', $condition['submit_end_time'] + 86399);
            $where[] = ' exists  (select inquiry_id from ' . $check_table
                    . ' where inquiry_id=a.id and out_node=\'MARKET_CONFIRMING\' and out_at between \'' . $start_time . '\' and \'' . $end_time . '\')';
        }
        if (!empty($condition['list_type'])) {
            $map = [];
            $role_nos = $condition['role_no'];
            switch ($condition['list_type']) {
                case 'inquiry' :
                    $map[] = ['a.created_by' => $condition['user_id']];

                    //foreach ($condition['role_no'] as $roleNo) {
                    //if ($roleNo == self::marketAgentRole) {
                    $map[] = ['a.agent_id' => $condition['user_id']];
                    $map[] = ['a.now_agent_id' => $condition['user_id']];
                    //}
                    //}
                    break;
                case 'erui' :
                    $map1 = [];
                    $map1['a.status'] = 'CC_DISPATCHING';
                    $map1['a.now_agent_id'] = $condition['user_id'];
                    $map1['_logic'] = 'and';
                    $map['_complex'] = $map1;

                    if (in_array(self::inquiryIssueRole, $role_nos) || in_array(self::inquiryIssueAuxiliaryRole, $role_nos)) {
                        $orgId = $this->getDeptOrgId($condition['group_id'], 'erui');
                        !empty($orgId) ? $map[] = ['a.erui_id' => ['in', $orgId]] : '';
                    }

                    break;
                case 'issue' :
                    $map1 = [];
                    $map1['a.status'] = 'BIZ_DISPATCHING';
                    $map1['a.now_agent_id'] = $condition['user_id'];
                    $map1['_logic'] = 'and';
                    $map['_complex'] = $map1;

                    if (in_array(self::quoteIssueMainRole, $role_nos) || in_array(self::inquiryIssueRole, $role_nos)) {
                        $orgId = $this->getDeptOrgId($condition['group_id'], ['in', ['ub', 'eub', 'erui']]);
                        !empty($orgId) ? $map[] = ['a.org_id' => ['in', $orgId]] : '';
                    } elseif (in_array(self::inquiryIssueAuxiliaryRole, $role_nos) || in_array(self::quoteIssueAuxiliaryRole, $role_nos)) {
                        $orgId = $this->getDeptOrgId($condition['group_id'], ['in', ['ub', 'eub', 'erui']]);
                        $map2 = [];
                        $map2['a.org_id'] = !empty($orgId) ? ['in', $orgId] : '-1';
                        $map2 ['a.country_bn'] = ['in', isset($condition['user_country']) ? $condition['user_country'] : ['-1']];
                        $map2['_logic'] = 'and';
                        $map['_complex'] = $map2;
                    }

                    break;
                case 'quote' :
                    $map1 = [];
                    $map1['a.status'] = ['in', ['BIZ_QUOTING', 'REJECT_QUOTING', 'BIZ_APPROVING']];
                    $map1['a.now_agent_id'] = $condition['user_id'];
                    $map1['_logic'] = 'and';
                    $map['_complex'] = $map1;
                    if (in_array(self::quoterRole, $role_nos) && in_array(self::quoteCheckRole, $role_nos)) {
                        $map[] = ['a.quote_id' => $condition['user_id']];
                        $map[] = ['a.check_org_id' => $condition['user_id']];
                    } elseif (in_array(self::quoterRole, $role_nos)) {
                        $map[] = ['a.quote_id' => $condition['user_id']];
                    } elseif (in_array(self::quoteCheckRole, $role_nos)) {
                        $map[] = ['a.check_org_id' => $condition['user_id']];
                    }
                    break;
                case 'logi' :
                    $map1 = [];
                    $map1['a.status'] = ['in', ['LOGI_DISPATCHING', 'LOGI_QUOTING', 'LOGI_APPROVING']];
                    $map1['a.now_agent_id'] = $condition['user_id'];
                    $map1['_logic'] = 'and';
                    $map['_complex'] = $map1;
                    if (in_array(self::logiIssueMainRole, $role_nos)) {

                        $orgId = $this->getDeptOrgId($condition['group_id'], ['in', ['lg', 'elg']]);
                        !empty($orgId) ? $map[] = ['a.logi_org_id' => ['in', $orgId]] : '';
                    } elseif (in_array(self::logiIssueAuxiliaryRole, $role_nos)) {

                        $orgId = $this->getDeptOrgId($condition['group_id'], ['in', ['lg', 'elg']]);
                        $map2 = [];
                        $map2['a.logi_org_id'] = !empty($orgId) ? ['in', $orgId] : '-1';
                        $map2['a.country_bn'] = ['in', isset($condition['user_country']) ? $condition['user_country'] : ['-1']];
                        $map2['_logic'] = 'and';
                        $map['_complex'] = $map2;
                    }
                    if (in_array(self::quoteCheckRole, $role_nos)) {

                        $map[] = ['a.check_org_id' => $condition['user_id']];
                    }
                    if (in_array(self::logiQuoterRole, $role_nos)) {

                        $map[] = ['a.logi_agent_id' => $condition['user_id']];
                    }
                    if (in_array(self::logiCheckRole, $role_nos)) {

                        $map[] = ['a.logi_check_id' => $condition['user_id']];
                    }
            }

            if ($map) {
                $map['_logic'] = 'or';
            } else {
                $map['a.id'] = '-1';
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
    public function getViewWhere($condition = [], $role_nos = [], $user_id = null, $group_id = null) {

        $where['status'] = ['neq', 'DRAFT'];
        $where['deleted_flag'] = 'N';
        $employeeModel = new EmployeeModel();
        $inquiryOrderModel = new InquiryOrderModel();

// 市场经办人
        (!empty($condition['agent_name'])) ? ($condition['agent_id'] = $employeeModel->getUserIdByName($condition['agent_name']) ?: []) : '';
        // 当前办理人
        (!empty($condition['now_agent_name'])) ? ($condition['now_agent_id'] = $employeeModel->getUserIdByName($condition['now_agent_name']) ?: []) : '';
// 报价人

        (!empty($condition['quote_name'])) ? ($condition['quote_id'] = $employeeModel->getUserIdByName($condition['quote_name']) ?: []) : '';
// 销售合同号
        (!empty($condition['contract_no'])) ? $condition['contract_inquiry_id'] = $inquiryOrderModel->getInquiryIdForContractNo() : '';
//区域和国家
        (!empty($condition['market_area_bn']) && empty($condition['country_bn']) ) ? ($condition['country_bn'] = (new MarketAreaCountryModel())->getCountryBn($condition['market_area_bn']) ?: []) : '';

        if (!empty($condition['status']) && $condition['status'] != 'DRAFT') {
            if ($condition['status'] == 'BIZ_QUOTING') {
                $where['status'] = ['in', ['REJECT_QUOTING', 'BIZ_QUOTING']];
            } else {
                $where['status'] = $condition['status'];    //项目状态
            }
        }
        if (!empty($condition['quote_status'])) {
            $where['quote_status'] = $condition['quote_status'];    //报价状态
        }

        if (!empty($condition['buyer_inquiry_no'])) {
            $where['buyer_inquiry_no'] = ['like', '%' . $condition['buyer_inquiry_no'] . '%'];    //客户询单号
        }

        if (!empty($condition['input_code'])) {
            // 用户输入编码匹配
            $match = [
                'serial_no' => $condition['input_code'],
                'buyer_code' => $condition['input_code'],
                'id' => ['in', (new InquiryOrderModel())->getInquiryIdByContractNo($condition['input_code']) ?: ['-1']],
                '_logic' => 'or'
            ];
            $where[] = $match;
        }

        if (!empty($condition['agent_id'])) {
            $where['agent_id'] = ['in', $condition['agent_id']]; //市场经办人
        } elseif (isset($condition['agent_id'])) {
            $where['agent_id'] = '-1';
        }
        if (!empty($condition['now_agent_id'])) {
            $where['now_agent_id'] = ['in', $condition['now_agent_id']]; //市场经办人
        } elseif (isset($condition['now_agent_id'])) {
            $where['now_agent_id'] = '-1';
        }
        if (!empty($condition['quote_id'])) {
            $where['quote_id'] = ['in', $condition['quote_id'] ?: ['-1']]; //报价人
        } elseif (isset($condition['quote_id'])) {
            $where['quote_id'] = '-1';
        }
        if (isset($condition['contract_inquiry_id']) && $condition['contract_inquiry_id']) {
            if ($condition['contract_no'] == 'Y') {
                $where['id'] = ['in', $condition['contract_inquiry_id']]; //销售合同号存在
            } else {
                $where['id'] = ['not in', $condition['contract_inquiry_id']]; //销售合同号不存在
            }
        } elseif (isset($condition['contract_inquiry_id'])) {
            if ($condition['contract_no'] == 'Y') {
                $where['id'] = ['in', ['-1']]; //销售合同号存在
            }
        }
        if (!empty($condition['start_time']) && !empty($condition['end_time'])) {   //询价时间
            $where['created_at'] = [
                ['egt', date('Y-m-d H:i:s', $condition['start_time'])],
                ['elt', date('Y-m-d H:i:s', $condition['end_time'] + 24 * 3600 - 1)]
            ];
        }
        if (!empty($condition['submit_start_time']) && !empty($condition['submit_end_time'])) {   //报出日期
            $check_model = new InquiryCheckLogModel();
            $check_table = $check_model->getTableName();
            $start_time = date('Y-m-d H:i:s', $condition['submit_start_time']);
            $end_time = date('Y-m-d H:i:s', $condition['submit_end_time'] + 86399);
            $where[] = ' exists  (select inquiry_id from ' . $check_table
                    . ' where inquiry_id=inquiry.id and out_node=\'MARKET_CONFIRMING\' and out_at between \'' . $start_time . '\' and \'' . $end_time . '\')';
        }


        $this->_getRolesWhere($where, $condition, $role_nos, $user_id, $group_id);
        return $where;
    }

    private function _getRolesWhere(&$where, $condition = [], $role_nos = [], $user_id = null, $group_id = null) {

        if (!in_array(self::viewAllRole, $role_nos) && !in_array(self::viewBizDeptRole, $role_nos) && !in_array(self::viewCountryRole, $role_nos)) {
            if (!empty($condition['country_bn']) && is_string($condition['country_bn']) && $user_id) {
                $map = [];
                $map['country_bn'] = $condition['country_bn'];    //查看事业部询单角色国家
                $map['now_agent_id'] = $user_id;
                $map['_logic'] = 'or';
                $where[] = $map;
            } elseif (!empty($condition['country_bn']) && is_array($condition['country_bn']) && $user_id) {
                $map = [];
                $map['country_bn'] = ['in', $condition['country_bn']];    //查看事业部询单角色国家
                $map['now_agent_id'] = $user_id;
                $map['_logic'] = 'or';
                $where[] = $map;
            } elseif (!empty($condition['market_area_bn']) && empty($condition['country_bn'])) {

                $where['country_bn'] = '-1';    //查看事业部询单角色国家
            } else {
                $where[] = ['now_agent_id' => $user_id];
            }
            return;
        } elseif (!empty($condition['view_type'])) {
            switch ($condition['view_type']) {
                case 'dept':
                    if (in_array(self::viewAllRole, $role_nos) && in_array(self::viewBizDeptRole, $role_nos)) {
                        $org_ids = $this->getDeptOrgId($group_id, ['in', ['ub', 'eub', 'erui']]);
                        $map = ['1=1'];
                        if ($org_ids) {
                            $map['org_id'] = ['in', $org_ids]; //事业部
                        }
                        $map['now_agent_id'] = $user_id;
                        $map['_logic'] = 'or';
                        $where[] = $map;
                    } elseif (in_array(self::viewAllRole, $role_nos)) {

                    } elseif (in_array(self::viewBizDeptRole, $role_nos)) {
                        $org_ids = $this->getDeptOrgId($group_id, ['in', ['ub', 'eub', 'erui']]);
                        $map = [];
                        if ($org_ids) {
                            $map['org_id'] = ['in', $org_ids]; //事业部
                            $map['now_agent_id'] = $user_id;
                            $map['_logic'] = 'or';
                            $where[] = $map;
                        } else {
                            $where[] = ['now_agent_id' => $user_id];
                        }
                    } else {
                        $where[] = ['now_agent_id' => $user_id];
                    }
                    if (!empty($condition['country_bn']) && is_string($condition['country_bn']) && $user_id) {
                        $where['country_bn'] = $condition['country_bn'];
                    } elseif (!empty($condition['country_bn']) && is_array($condition['country_bn']) && $user_id) {
                        $where['country_bn'] = ['in', $condition['country_bn']];    //查看事业部询单角色国家
                    } elseif (!empty($condition['market_area_bn']) && empty($condition['country_bn'])) {
                        $where['country_bn'] = '-1';    //查看事业部询单角色国家
                    }
                    break;
                case 'country':
                    if (in_array(self::viewCountryRole, $role_nos)) {
                        $user_country = (new CountryUserModel())->getUserCountry(['employee_id' => $user_id]) ?: [];

                        if ($user_country && $user_id) {
                            $map = [];
                            $map['country_bn'] = ['in', $user_country];    //查看事业部询单角色国家
                            $map['now_agent_id'] = $user_id;
                            $map['_logic'] = 'or';
                            $where[] = $map;
                        } elseif (empty($user_country) && $user_id) {
                            $where[] = ['now_agent_id' => $user_id];
                        } else {
                            $where['country_bn'] = '-1';
                        }
                        if (!empty($condition['country_bn']) && is_string($condition['country_bn']) && $user_id) {
                            $where['country_bn'] = $where['country_bn'] == '-1' ? '-1' : $condition['country_bn'];
                        } elseif (!empty($condition['country_bn']) && is_array($condition['country_bn']) && $user_id) {
                            $where['country_bn'] = $where['country_bn'] == '-1' ? '-1' : ['in', $condition['country_bn']];    //查看事业部询单角色国家
                        } elseif (!empty($condition['market_area_bn']) && empty($condition['country_bn'])) {
                            $where['country_bn'] = '-1';    //查看事业部询单角色国家
                        }
                    } else {

                        $where[] = ['now_agent_id' => $user_id];
                        if (!empty($condition['country_bn']) && is_string($condition['country_bn']) && $user_id) {
                            $where['country_bn'] = $condition['country_bn'];
                        } elseif (!empty($condition['country_bn']) && is_array($condition['country_bn']) && $user_id) {
                            $where['country_bn'] = ['in', $condition['country_bn']];    //查看事业部询单角色国家
                        } elseif (!empty($condition['market_area_bn']) && empty($condition['country_bn'])) {
                            $where['country_bn'] = '-1';    //查看事业部询单角色国家
                        }
                    }
                    break;
            }
        } else {
            $where[] = ['now_agent_id' => $user_id];
            if (!empty($condition['country_bn']) && is_string($condition['country_bn']) && $user_id) {
                $where['country_bn'] = $condition['country_bn'];
            } elseif (!empty($condition['country_bn']) && is_array($condition['country_bn']) && $user_id) {
                $where['country_bn'] = ['in', $condition['country_bn']];    //查看事业部询单角色国家
            } elseif (!empty($condition['market_area_bn']) && empty($condition['country_bn'])) {
                $where['country_bn'] = '-1';    //查看事业部询单角色国家
            }
        }
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
        $count = $this->getSqlJoint($condition)->count(' DISTINCT a.id');

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
    public function getViewCount($condition = [], $role_nos = [], $user_id = null, $group_id = null) {

        $where = $this->getViewWhere($condition, $role_nos, $user_id, $group_id);

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

        if (!empty($condition['user_id'])) {
            if (!empty($condition['agent_id'])) {
                if (!empty($condition['status'])) {
                    if (!in_array($condition['user_id'], $condition['agent_id'])) {
                        $condition['agent_id'][] = $condition['user_id'];
                    }
                    switch ($condition['status']) {
                        case 'DRAFT':
                            $where2 = '(agent_id=' . $condition['user_id'] . ') or (created_by=' . $condition['user_id'] . ') ';
                            break;
                        default:
                            $where2 = 'agent_id in(' . implode(',', $condition['agent_id']) . ') ';
                            break;
                    }
                } else {
                    $where2 = '(agent_id in(' . implode(',', $condition['agent_id']) . ') and status<>"DRAFT") ';
                    $where2 .= 'or (agent_id=' . $condition['user_id'] . ') ';
                    $where2 .= 'or (created_by=' . $condition['user_id'] . ' and status="DRAFT") ';
                }
                unset($where['agent_id']);
            } else {
                $where2 = '(agent_id=' . $condition['user_id'] . ') or (created_by=' . $condition['user_id'] . ' and status="DRAFT")';
            }
        }

        $page = !empty($condition['currentPage']) ? $condition['currentPage'] : 1;
        $pagesize = !empty($condition['pageSize']) ? $condition['pageSize'] : 10;

        try {
            if (!empty($where2)) {
                $count = $this->where($where)->where($where2)->count('id');
                $list = $this->where($where)
                        ->where($where2)
                        ->page($page, $pagesize)
                        ->order('updated_at desc')
                        ->select();
            } else {
                $count = $this->where($where)->count('id');
                $list = $this->where($where)
                        ->page($page, $pagesize)
                        ->order('updated_at desc')
                        ->select();
            }
            $count = $count > 0 ? $count : 0;

            if ($list) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
                $results['count'] = $count;
                $results['data'] = $list;
            } else {
                $results['code'] = '-101';
                $results['message'] = L('NO_DATA');
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
     * @return array
     * @author liujf
     * @time 2017-10-18
     */
    public function getList_($condition = []) {
        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];
        $data = $this->getSqlJoint($condition)
                ->field('a.*')
                ->page($currentPage, $pageSize)
                ->group('a.id')
                ->order('a.updated_at DESC,a.created_at DESC')
                ->select();

        return $data;
    }

    /**
     * @desc 获取组装sql后的对象
     *
     * @param array $condition
     * @return object
     * @author liujf
     * @time 2018-05-28
     */
    public function getSqlJoint($condition = []) {
        $inquiryRemindModel = new InquiryRemindModel();
        // 获取表名
        $inquiryRemindTableName = $inquiryRemindModel->getTableName();
        $where = $this->getWhere($condition);
        return $this->alias('a')
                        ->join($inquiryRemindTableName . ' b ON a.id = b.inquiry_id', 'LEFT') // 关联催办表
                        ->where($where);
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
    public function getViewList($condition = [], $field = '*', $role_nos = [], $user_id = null, $group_id = null) {

        $where = $this->getViewWhere($condition, $role_nos, $user_id, $group_id);

        $currentPage = empty($condition['currentPage']) ? 1 : $condition['currentPage'];
        $pageSize = empty($condition['pageSize']) ? 10 : $condition['pageSize'];

        return $this->field($field)
                        ->where($where)
                        ->page($currentPage, $pageSize)
                        ->order('updated_at DESC,created_at DESC')
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
    public function getExportList($condition = [], $role_nos = [], $user_id = null, $group_id = null) {

        $where = $this->getViewWhere($condition, $role_nos, $user_id, $group_id);

        return $this->where($where)
                        ->order('updated_at DESC,created_at DESC')
                        ->getField('id', true);
    }

    /**
     * 获取详情信息
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getInfo($condition = []) {
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        } elseif (!empty($condition['serial_no'])) {
            $where['serial_no'] = $condition['serial_no'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }

        try {
            $info = $this->where($where)->find();
            if ($info) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
                $results['data'] = $info;
            } else {
                $results['code'] = '-101';
                $results['message'] = L('NO_DATA');
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

        if (!empty($condition['serial_no'])) {
            $data['serial_no'] = $condition['serial_no'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }

        $time = $this->getTime();

        $data['quote_status'] = 'NOT_QUOTED';
        $data['inflow_time'] = $time;
        $data['created_at'] = $time;

        try {
            $id = $this->add($data);
            $data['id'] = $id;
            if ($id) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
                $results['data'] = $data;
            } else {
                $results['code'] = '-101';
                $results['message'] = L('FAIL');
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
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }

        if (isset($data['org_id']) && empty($data['org_id'])) {
            $data['org_id'] = null;
        }
        $time = $this->getTime();

        if (!empty($condition['status']))
            $data['inflow_time'] = $time;

        $data['updated_at'] = $time;

        try {

            $id = $this->where($where)->save($data);
            if ($id !== false) {
                $results['code'] = 1;
                $results['message'] = L('SUCCESS');
            } else {
                $results['code'] = -101;
                $results['message'] = L('FAIL');
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
        if (!empty($condition['id'])) {
            $where['id'] = array('in', explode(',', $condition['id']));
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }

        $time = $this->getTime();

        if (!empty($condition['status'])) {
            $data['inflow_time'] = $time;
            $data['status'] = $condition['status'];
        }

        if (!empty($condition['now_agent_id'])) {
            $data['now_agent_id'] = $condition['now_agent_id'];
        }

        $data['updated_at'] = $time;

        try {
            $id = $this->where($where)->save($data);
            if ($id) {
                if ($data['status'] != 'DRAFT') {
                    (new TemporaryGoodsModel)->sync();
                }
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
            } else {
                $results['code'] = '-101';
                $results['message'] = L('FAIL');
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
        if (!empty($condition['id'])) {
            $where['id'] = array('in', explode(',', $condition['id']));
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }

        try {
            $id = $this->where($where)->save(['deleted_flag' => 'Y']);
            if ($id) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
            } else {
                $results['code'] = '-101';
                $results['message'] = L('FAIL');
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

    public function checkSerialNo($condition = []) {
        if (!empty($condition['serial_no'])) {
            $where['serial_no'] = $condition['serial_no'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }

        try {
            $id = $this->field('id')->where($where)->find();
            if ($id) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
            } else {
                $results['code'] = '-101';
                $results['message'] = L('NO_DATA');
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
            'id' => ['in', !empty($groupId) ? $groupId : ['-1']],
            'org_node' => $orgNode,
            'deleted_flag' => 'N'
        ];

        $data = $orgModel->where($where)->getField('id', true);

        return $data;
    }

    /**
     * @desc 获取指定角色用户ID
     *
     * @param array $groupId 当前用户的全部组ID
     * @param mixed $roleNo 角色编号
     * @param mixed $orgNode 部门节点
     * @return array
     * @author liujf
     * @time 2017-10-23
     */
    public function getRoleUserId($groupId = [], $roleNo = '', $orgNode = ['in', ['ub', 'eub']]) {
        $orgMemberModel = new OrgMemberModel();
        $roleModel = new RoleModel();
        $roleUserModel = new RoleUserModel();
        $employeeModel = new EmployeeModel();

        $orgId = $this->getDeptOrgId($groupId, $orgNode);

        $roleId = $roleModel->where(['role_no' => $roleNo, 'deleted_flag' => 'N'])->getField('id', true);

        $employeeId = $roleUserModel->where(['role_id' => ['in', $roleId ?: ['-1']]])->getField('employee_id', true);

        $employee_Ids = $employeeModel->where(['id' => ['in', $employeeId ?: ['-1']], 'deleted_flag' => 'N'])->getField('id', true);

        $ret = $orgMemberModel->where(['org_id' => ['in', $orgId ?: ['-1']],
                    'employee_id' => ['in', $employee_Ids ?: ['-1']]])
                ->getField('employee_id', true);


        return $ret;
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

        $roleId = $roleUserModel->where(['employee_id' => $userId ?: '-1'])->getField('role_id', true);

        $roleNoArr = $roleModel->where(['id' => ['in', $roleId ?: ['-1']], 'deleted_flag' => 'N'])->getField('role_no', true);

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

        if (in_array(self::marketAgentRole, $roleNoArr)) {
            $isAgent = 'Y';
        }
        if (in_array(self::inquiryIssueRole, $roleNoArr) ||
                in_array(self::inquiryIssueAuxiliaryRole, $roleNoArr)) {
            $isErui = 'Y';
        }

        if (in_array(self::inquiryIssueRole, $roleNoArr) ||
                in_array(self::inquiryIssueAuxiliaryRole, $roleNoArr) ||
                in_array(self::quoteIssueMainRole, $roleNoArr) ||
                in_array(self::quoteIssueAuxiliaryRole, $roleNoArr) ||
                in_array(self::logiIssueMainRole, $roleNoArr) ||
                in_array(self::logiIssueAuxiliaryRole, $roleNoArr)) {
            $isIssue = 'Y';
        }
        if (in_array(self::quoterRole, $roleNoArr) ||
                in_array(self::logiQuoterRole, $roleNoArr)) {
            $isQuote = 'Y';
        }
        if (in_array(self::quoteCheckRole, $roleNoArr) ||
                in_array(self::logiCheckRole, $roleNoArr)) {
            $isCheck = 'Y';
        }
        if (in_array(self::buyerCountryAgent, $roleNoArr)) {
            $isCountryAgent = 'Y';
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
     * 设置角色名称
     * @param $data
     *
     * @author maimaiti
     * @return string
     */
    public function setRoleName($data) {
        if ($data['is_agent'] == 'Y') {
            return '市场经办人';
        } elseif ($data['is_erui'] == 'Y') {
            return '易瑞事业部';
        } elseif ($data['is_issue'] == 'Y') {
            return '事业部分单员';
        } elseif ($data['is_quote'] == 'Y') {
            return '报价人';
        } elseif ($data['is_check'] == 'Y') {
            return '报价审核人';
        } elseif ($data['is_country_agent'] == 'Y') {
            return '区域负责人或国家负责人';
        } else {
            return '';
        }
    }

    /**
     * @desc 获取指定国家的角色用户ID
     *
     * @param mixed $country 国家简称
     * @param array $groupId 当前用户的全部组ID
     * @param mixed $roleNo 角色编号
     * @param mixed $orgNode 部门节点
     * @return array
     * @author liujf
     * @time 2017-11-27
     */
    public function getCountryRoleUserId($country = '', $groupId = [], $roleNo = '', $orgNode = ['in', ['ub', 'eub']]) {
        $countryUserModel = new CountryUserModel();

        $employeeId = $this->getRoleUserId($groupId, $roleNo, $orgNode);

        return $countryUserModel->where(['employee_id' => ['in', $employeeId ?: ['-1']],
                    'country_bn' => $country])->getField('employee_id', true);
    }

    /**
     * @desc 获取指定国家的辅分单员用户ID，如果没有就获取主分单员用户ID
     *
     * @param mixed $country 国家简称
     * @param array $groupId 当前用户的全部组ID
     * @param mixed $roleNo1 辅分单员角色编号
     * @param mixed $roleNo2 主分单员角色编号
     * @param mixed $orgNode 部门节点
     * @return string
     * @author liujf
     * @time 2017-11-28
     */
    public function getCountryIssueUserId($country = '', $groupId = [], $roleNo1 = '', $roleNo2 = '', $orgNode = ['in', ['ub', 'eub']]) {
        $userId = $this->getCountryRoleUserId($country, $groupId, $roleNo1, $orgNode) ?:
                $this->getRoleUserId($groupId, $roleNo2, $orgNode);

        return $userId[0];
    }

    public function getInquiryIssueUserIds($id = '', $groupId = [], $roleNo1 = '', $roleNo2 = '', $orgNode = ['in', ['ub', 'eub']]) {

        $country = $this->getInquiryCountry($id);

        $countryUserModel = new CountryUserModel();

        $employeeId = $this->getRoleUserId($groupId, $roleNo1, $orgNode);

        $employeeIds = $countryUserModel->where(['employee_id' => ['in', $employeeId ?: ['-1']],
                    'country_bn' => $country])->getField('employee_id', true);

        $orgMemberModel = new OrgMemberModel();
        $roleModel = new RoleModel();
        $roleUserModel = new RoleUserModel();
        $employeeModel = new EmployeeModel();
        $orgId = $this->getDeptOrgId($groupId, $orgNode);
        $roleId = $roleModel->where(['role_no' => $roleNo2, 'deleted_flag' => 'N'])->getField('id', true);
        $employeeIds1 = $roleUserModel->where(['role_id' => ['in', $roleId ?: ['-1']]])->getField('employee_id', true);
        $employee_Ids = $employeeModel->where(['id' => ['in', $employeeIds1 ?: ['-1']], 'deleted_flag' => 'N'])->getField('id', true);
        $employeeIds_1 = $orgMemberModel->where(['org_id' => ['in', $orgId ?: ['-1']],
                    'employee_id' => ['in', $employee_Ids ?: ['-1']]])->getField('employee_id', true);

        $ret = empty($employeeIds) ? $employeeIds_1 : (empty($employeeIds_1) ? $employeeIds : array_merge($employeeIds, $employeeIds_1));

        return $ret;
    }

    public function getPermissions($id = '', $groupId = '', $roleNo1s = [], $roleNo2s = [], $orgNode = [], $user = []) {
        $country = $this->getInquiryCountry($id);
        if (empty($groupId)) {

            return false;
        }
        $orgId = $this->getDeptOrgId([$groupId], $orgNode);

        if (empty($orgId)) {

            return false;
        }

        if (is_array($roleNo1s)) {
            foreach ($roleNo1s as $roleNo1) {
                if (in_array($roleNo1, $user['role_no']) && in_array($country, $user['country_bn']) && in_array($groupId, $user['group_id'])) {

                    return true;
                }
            }
        } else {
            if (in_array($roleNo1s, $user['role_no']) && in_array($country, $user['country_bn']) && in_array($groupId, $user['group_id'])) {

                return true;
            }
        }

        if (is_array($roleNo2s)) {
            foreach ($roleNo2s as $roleNo2) {
                if (in_array($roleNo2, $user['role_no']) && in_array($groupId, $user['group_id'])) {

                    return true;
                }
            }
        } else {
            if (in_array($roleNo2s, $user['role_no']) && in_array($groupId, $user['group_id'])) {

                return true;
            }
        }

        return false;
    }

    /**
     * @desc 获取询单的辅分单员用户ID，如果没有就获取主分单员用户ID
     *
     * @param mixed $id 询单ID
     * @param array $groupId 当前用户的全部组ID
     * @param mixed $roleNo1 辅分单员角色编号
     * @param mixed $roleNo2 主分单员角色编号
     * @param mixed $orgNode 部门节点
     * @return string
     * @author liujf
     * @time 2017-12-14
     */
    public function getInquiryIssueUserId($id = '', $groupId = [], $roleNo1 = '', $roleNo2 = '', $orgNode = ['in', ['ub', 'eub']]) {
        $country = $this->getInquiryCountry($id);

        return $this->getCountryIssueUserId($country, $groupId, $roleNo1, $roleNo2, $orgNode);
    }

    /**
     * @desc 获取询单所在国家简称
     *
     * @param string $id 询单ID
     * @return string
     * @author liujf
     * @time 2017-11-28
     */
    public function getInquiryCountry($id = '') {
        return $this->where(['id' => $id])->getField('country_bn');
    }

    /**
     * @desc 获取某个时间段内的询单列表
     *
     * @param array $condition
     * @return mixed
     * @author liujf
     * @time 2017-12-07
     */
    public function getTimeIntervalList($condition = []) {
        if (!empty($condition['creat_at_start']) && !empty($condition['creat_at_end'])) {
            $where['a.deleted_flag'] = 'N';

            $where['a.status'] = ['neq', 'DRAFT'];

            $where['_complex']['a.created_at'] = [
                ['egt', $condition['creat_at_start']],
                ['elt', $condition['creat_at_end']]
            ];

            if (!empty($condition['update_at_start']) && !empty($condition['update_at_end'])) {
                $where['_complex']['a.updated_at'] = [
                    ['egt', $condition['update_at_start']],
                    ['elt', $condition['update_at_end']]
                ];
            }

            $where['_complex']['_logic'] = 'or';

            $lang = empty($condition['lang']) ? 'zh' : $condition['lang'];

            return $this->alias('a')
                            ->field('a.id, a.serial_no, a.buyer_code, a.country_bn, IF(a.proxy_flag = \'Y\', \'是\', \'否\') AS proxy_flag, a.proxy_no, a.quote_status, a.created_at, b.name AS country_name, c.name AS area_name, d.name AS org_name, e.gross_profit_rate, f.total_quote_price')
                            ->join('erui_dict.country b ON a.country_bn = b.bn AND b.lang = \'' . $lang . '\' AND b.deleted_flag = \'N\'', 'LEFT')
                            ->join('erui_operation.market_area c ON a.area_bn = c.bn AND c.lang = \'' . $lang . '\' AND c.deleted_flag = \'N\'', 'LEFT')
                            ->join('erui_sys.org d ON a.org_id = d.id', 'LEFT')
                            ->join('erui_rfq.quote e ON a.id = e.inquiry_id AND e.deleted_flag = \'N\'', 'LEFT')
                            ->join('erui_rfq.final_quote f ON a.id = f.inquiry_id AND f.deleted_flag = \'N\'', 'LEFT')
                            ->where($where)
                            ->order('a.updated_at DESC')
                            ->select();
        } else {
            return false;
        }
    }

    /**
     * 更新用户信息和询单经办人等信息
     * @param  Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function setBuyerAgentInfo($condition = []) {
        if (!empty($condition['buyer_id'])) {
            $where['buyer_id'] = $condition['buyer_id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }
        $where['status'] = !empty($condition['status']) ? $condition['status'] : 'DRAFT';

        if (!empty($condition['agent_id'])) {
            $data['agent_id'] = $condition['agent_id'];
            $data['now_agent_id'] = $condition['agent_id'];
            $data['created_by'] = $condition['agent_id'];
        }

        try {
            $id = $this->where($where)->save($data);
            if ($id) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
            } else {
                $results['code'] = '-101';
                $results['message'] = L('FAIL');
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 根据询单id获取流程编码
     * @param $id
     * @return mixed
     * @author 买买提
     */
    public function getSerialNoById($id) {
        return $this->where(['id' => $id])->getField('serial_no');
    }

    /* @param $buyer_id======================================================================
     * 获取询单数量
     * wangs
     */

    public function statisInquiry($buyer_id) {
        $arr = $this->field('id')
                ->where(array('buyer_id' => $buyer_id, 'deleted_flag' => 'N'))
                ->select();
        if (empty($arr)) {
            $data = array(
                'inquiry_count' => 0,
                'quote_count' => 0,
                'account' => 0
            );
            return $data;
        }
        $count = count($arr);
        $quote = new QuoteModel();
        $sql = "select quote.id as quote_id,quote.total_purchase as amount,quote.purchase_cur_bn as currency_bn from erui_rfq.inquiry inquiry ";
        $sql .= " left join erui_rfq.quote quote on inquiry.id=quote.inquiry_id and quote.deleted_flag='N'";
        $sql .= " where inquiry.deleted_flag='N' and (inquiry.quote_status='QUOTED' or inquiry.quote_status='COMPLETED') and inquiry.buyer_id=$buyer_id";
        $info = $quote->query($sql);
        $res = $this->sumAccountQuote($info);
        $amount = sprintf("%.4f", array_sum($res['amount']));
        $qCount = count($res['count']);
        if (empty($res['count']) && empty($res['amount'])) {
            $data = array(
                'inquiry_count' => $count,
                'quote_count' => 0,
                'account' => 0
            );
        } else {
            $data = array(
                'inquiry_count' => $count,
                'quote_count' => $qCount,
                'account' => !empty($amount) ? $amount : 0
            );
        }
        return $data;
    }

    //计算询报价王帅
    public function sumAccountQuote($order = []) {
        $count = array();
        $arr = [];
        $val = 0;
        foreach ($order as $k => $v) {
            if ($v['currency_bn'] == 'USD') {   //一次交易50万=高级
                $val = $v['amount'];
            } elseif ($v['currency_bn'] == 'CNY') {
                $val = $v['amount'] * 0.1583;
            } elseif ($v['currency_bn'] == 'EUR') {
                $val = $v['amount'] * 1.2314;
            } elseif ($v['currency_bn'] == 'CAD') {
                $val = $v['amount'] * 0.7918;
            } elseif ($v['currency_bn'] == 'RUB') {
                $val = $v['amount'] * 0.01785;
            } else {
                $val = $v['amount'];
            }
            $arr[] = $val;
            $count[] = $v['quote_id'];
        }
        $data['amount'] = $arr;
        $data['count'] = array_flip(array_flip($count));
        return $data;
    }

    /**
     * 客户管理首页获取询单数量和金额
     * wnags
     */
    public function getInquiryStatis($ids) {
        $arr = [];
        foreach ($ids as $k => $v) {
            $arr[$k] = $this->statisInquiry($v);
        }
        return $arr;
    }

    //会员属性统计
    public function getBuyerInquiry($buyer_id) {
        $order = new OrderModel();
        $orderInfo = $order->statisOrder($buyer_id);
        $orderCount = $orderInfo['count'];    //订单数
        $orderAmount = $orderInfo['account']; //订单总额

        $inquiryInfo = $this->statisInquiry($buyer_id);
        $inquiryCount = $inquiryInfo['inquiry_count']; //询单个数
        $quoteCount = $inquiryInfo['quote_count']; //报价个数
        $quoteAmount = $inquiryInfo['account']; //报价总额


        if ($orderCount == 0 && $inquiryCount == 0) { //成单率=订单个数/报价个数
            $orderRate = '0%';
        } elseif ($orderCount >= $inquiryCount) {
            $orderRate = '100%';
        } else {
            $orderRate = (sprintf("%.4f", $orderCount / $quoteCount) * 100) . '%';
        }

        if ($orderAmount == 0 && $quoteAmount == 0) { //成单金额率=订单金额/报价金额
            $orderAmountRate = '0%';
        } elseif ($orderAmount >= $quoteAmount) {
            $orderAmountRate = '100%';
        } else {
            $orderAmountRate = (sprintf("%.4f", $orderAmount / $quoteAmount) * 100) . '%';
        }
        $arr['inquiry_count'] = $inquiryCount; //询单个数
        $arr['quote_count'] = $quoteCount; //报价个数
        $arr['order_count'] = $orderCount; //订单数
        $arr['order_rate'] = $orderRate; //成单率
        $arr['order_amount_rate'] = $orderAmountRate; //成单金额率
        return $arr;
    }

    //crm 获取地区,国家,会员统计中使用====================================================================
    private function _getCountry($lang, $area_bn = '', $country_bn = '', $admin) {
        $access = $this->statisAdmin($admin);
        if ($access === 1) {
            if (!empty($country_bn)) {
                return [['country_bn' => $country_bn]];
            }
            if (!empty($area_bn)) {
                $country = new MarketAreaCountryModel();
                $countryArr = $country->field('country_bn')
                        ->where("market_area_bn='$area_bn'")
                        ->select();
                return $countryArr;
            }
        } elseif ($access === 0) {
            return false;
        } else {
            if (!empty($country_bn)) {
                if (preg_match("/$country_bn/i", $admin['country'])) {    //国家
                    return [['country_bn' => $country_bn]];
                }
            }
            if (!empty($area_bn)) {
                if (preg_match("/$area_bn/i", $admin['area'])) {    //地区下的国家
                    $country = new MarketAreaCountryModel();
                    $countryArr = $country->field('country_bn')
                            ->where("market_area_bn='$area_bn' and country_bn in ($admin[country])")
                            ->select();
                    return $countryArr;
                }
            }
        }
        return false;
    }

    //获取上周日期时间段
    public function getLastWeek() {
        $beginLastweek = mktime(0, 0, 0, date('m'), date('d') - date('w') + 1 - 9, date('Y'));

        $endLastweek = mktime(23, 59, 59, date('m'), date('d') - date('w') + 7 - 9, date('Y'));
        $arr['start_time'] = date('Y-m-d', $beginLastweek);
        $arr['end_time'] = date('Y-m-d', $endLastweek);
        return $arr;
    }

    //统计权限
    public function statisAdmin($admin) {
        if (in_array('CRM客户管理', $admin['role'])) {    //运营专员,CRM客户管理所有权限
            $access = 1;
        } elseif (in_array('201711242', $admin['role'])) {  //市场区域国家负责人
            $access = $admin['country'];
        } else {
            $access = 0;
        }
        return $access;
    }

    public function countryAdmin($data, $column) { //国家权限
//        $cond=' 1 ';
        $admin = $this->statisAdmin($data['admin']);
        if ($admin === 0) { //无权限
            return false;
        }
        if (!empty($data['area_bn']) || !empty($data['country_bn'])) {   //地区国家
            $countryArr = $this->_getCountry($data['lang'], $data['area_bn'], $data['country_bn'], $data['admin']);
            if (!empty($countryArr)) {
                $str = '';
                foreach ($countryArr as $k => $v) {
                    $str .= ",'" . $v['country_bn'] . "'";
                }
                $str = substr($str, 1);
                if (count($countryArr) == 1) {
                    $cond = ' and ' . $column . '.country_bn=' . $str;
                } else {
                    $cond = ' and ' . $column . '.country_bn in (' . $str . ')';
                }
            } else {
                return false;   //无地区国家权限
            }
        } else {
            if ($admin === 0) {  //无权限
                return false;
            } elseif ($admin === 1) { //所有权限
                $cond = '';
            } else {  //国家负责人
                if (!empty($admin)) {
                    $cond = ' and ' . $column . '.country_bn in (' . $admin . ') ';
                } else {
                    return false;
                }
            }
        }
        return $cond;
    }

    //会员统计系列获取条件-wangs
    public function getStatisInquiryCondCrm($data) {
        $cond = ' inquiry.deleted_flag=\'N\'';  //客户状态
        $admin = $this->countryAdmin($data, 'inquiry'); //获取国家权限
        if ($admin === false) {  //无权限
            return false;
        }
        $cond .= $admin;
        if (empty($data['start_time']) && empty($data['end_time'])) { //默认数据
            $week = $this->getLastWeek();
            $cond .= ' and inquiry.created_at >= \'' . $week['start_time'] . ' 00:00:00\'';
            $cond .= ' and inquiry.created_at <= \'' . $week['end_time'] . ' 23:59:59\'';
        } elseif (!empty($data['start_time']) && !empty($data['end_time'])) {   //时间段搜索
            $cond .= ' and inquiry.created_at >= \'' . $data['start_time'] . ' 00:00:00\'';
            $cond .= ' and inquiry.created_at <= \'' . $data['end_time'] . ' 23:59:59\'';
        }
        return $cond;
    }

    //地区,国家,时间段统计询单量-wangs
    public function statisCondInquiry($data) {
        $cond = $this->getStatisInquiryCondCrm($data);
        if ($cond == false) {   //无权限
            return false;
        }
        if (empty($data['start_time']) && empty($data['end_time'])) {
            $week = $this->getLastWeek();
            $data['start_time'] = $week['start_time'];
            $data['end_time'] = $week['end_time'];
        }
        $sql = 'select ';
        $sql .= ' count(id) as count,DATE_FORMAT(created_at,\'%Y-%m-%d\') as created_at ';
        $sql .= ' from erui_rfq.inquiry ';
        $sql .= ' where ';
        $sql .= $cond;
        $sql .= " and status in ('REJECT_MARKET','REJECT_CLOSE','BIZ_DISPATCHING','CC_DISPATCHING','BIZ_QUOTING','LOGI_DISPATCHING','LOGI_QUOTING','LOGI_APPROVING','BIZ_APPROVING','MARKET_APPROVING','MARKET_CONFIRMING','QUOTE_SENT','INQUIRY_CLOSED')";
        $sql .= ' group by DATE_FORMAT(created_at,\'%Y-%m-%d\') ';
        $sql .= ' order by created_at ';
        $info = $this->query($sql);
        $arr = $this->packDailyData($info, $data['start_time'], $data['end_time']);
        return $arr;
    }

    //地区,国家,时间段统计询单量-wangs
    public function statisCondQuote($data) {
        $cond = $this->getStatisInquiryCondCrm($data);
        if ($cond === false) {
            return false;
        }
        if (empty($data['start_time']) && empty($data['end_time'])) {
            $week = $this->getLastWeek();
            $data['start_time'] = $week['start_time'];
            $data['end_time'] = $week['end_time'];
        }
        $sql = 'select ';
        $sql .= ' count(id) as count,DATE_FORMAT(created_at,\'%Y-%m-%d\') as created_at ';
        $sql .= ' from erui_rfq.inquiry ';
        $sql .= ' where ';
        $sql .= $cond;
        $sql .= " and quote_status in ('QUOTED','COMPLETED')";
        $sql .= ' group by DATE_FORMAT(created_at,\'%Y-%m-%d\') ';
        $sql .= ' order by created_at ';
        $info = $this->query($sql);
        $arr = $this->packDailyData($info, $data['start_time'], $data['end_time']);
        return $arr;
    }

    //整理每天的数据
    public function packDailyData($data, $start_time, $end_time) {
        $days = (strtotime($end_time) - strtotime($start_time)) / 86400 + 1;
        $arr = [];
        $info = [];
        for ($i = 0; $i < $days; $i++) {
            $arr[$i]['created_at'] = date("Y-m-d", strtotime("$start_time +$i day"));
            $arr[$i]['count'] = 0;
        }
        foreach ($arr as $key => &$value) {
            foreach ($data as $k => $v) {
                if ($v['created_at'] == $value['created_at']) {
                    $arr[$key]['created_at'] = $value['created_at'];
                    $arr[$key]['count'] = $v['count'];
                }
            }
        }
        foreach ($arr as $k => $v) {
            $info['day'][] = $v['created_at'];
            $info['count'][] = intval($v['count']);
        }
        return $info;
    }

}
