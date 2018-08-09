<?php

/**
 * name: Inquiry.php
 * desc: 询价单控制器
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:45
 */
class InquiryController extends PublicController {

    public function init() {
        parent::init();
        $this->put_data = dataTrim($this->put_data);
    }

    /*
     * 返回询价单流程编码
     * Author:张玉良
     */

    public function getSerialNoAction() {
        $serial_no = InquirySerialNo::getInquirySerialNo();
        return $serial_no;
    }

    /*
     * 查询询单流程编码是否存在
     * Author:张玉良
     */

    public function checkSerialNoAction() {
        $inquiry = new InquiryModel();
        $where = $this->put_data;

        $results = $inquiry->checkSerialNo($where);

        $this->jsonReturn($results);
    }

    /*
     * 返回询价单流程编码
     * Author:张玉良
     */

    public function getInquiryIdAction() {
        $inquiry = new InquiryModel();
        $data['serial_no'] = $this->getSerialNoAction();
        $data['created_by'] = $this->user['id'];
        $data['agent_id'] = $this->user['id'];

        $results = $inquiry->addData($data);

        $this->jsonReturn($results);
    }

    /**
     * @desc 询价单列表
     *
     * @author liujf
     * @time 2017-10-18
     */
    public function getListAction() {
        $condition = $this->put_data;

        $inquiryModel = new InquiryModel();

        $countryModel = new CountryModel();
        $employeeModel = new EmployeeModel();

        $countryUserModel = new CountryUserModel();

        $marketAreaCountryModel = new MarketAreaCountryModel();
        $marketAreaModel = new MarketAreaModel();

        $inquiryOrderModel = new InquiryOrderModel();

// 市场经办人
        if ($condition['agent_name'] != '') {
            $condition['agent_id'] = $employeeModel->getUserIdByName($condition['agent_name']) ?: [];
        }
// 当前办理人
        if ($condition['now_agent_name'] != '') {
            $condition['now_agent_id'] = $employeeModel->getUserIdByName($condition['now_agent_name']) ?: [];
        }
// 报价人

        $condition['quote_name'] != '' ? $condition['quote_id'] = ($employeeModel->getUserIdByName($condition['quote_name']) ?: []) : null;


// 销售合同号

        $condition['contract_no'] != '' ? $condition['contract_inquiry_id'] = $inquiryOrderModel->getInquiryIdForContractNo() : null;


// 当前用户的所有角色编号
        $condition['role_no'] = $this->user['role_no'];

// 当前用户的所有组织ID
        $condition['group_id'] = $this->user['group_id'];

        $condition['user_id'] = $this->user['id'];

        $condition['user_country'] = $countryUserModel->getUserCountry(['employee_id' => $this->user['id']]) ?: [];

        $inquiryList = $inquiryModel->getList_($condition);
        $countryModel->setCountry($inquiryList, $this->lang);
        $marketAreaCountryModel->setAreaBn($inquiryList);
        $marketAreaModel->setArea($inquiryList);
        (new EmployeeModel)->setUserNames($inquiryList, ['agent_name' => 'agent_id', 'quote_name' => 'quote_id', 'now_agent_name' => 'now_agent_id', 'created_name' => 'created_by', 'obtain_name' => 'obtain_id']);
        $this->_setBuyerNo($inquiryList);
        $this->_setLogiQuoteFlag($inquiryList);
        $this->_setOrgName($inquiryList);
        $this->_setTransModeName($inquiryList);
        $this->_setContractNo($inquiryList);


        if ($inquiryList) {
            $res['code'] = 1;
            $res['message'] = L('SUCCESS');
            $res['data'] = $inquiryList;
            $res['count'] = $inquiryModel->getCount_($condition);
            $this->jsonReturn($res);
        } else {
            $this->setCode('-101');
            $this->setMessage(L('FAIL'));
            $this->jsonReturn();
        }
    }

    /**
     * @desc 查看询价单列表
     *
     * @author liujf
     * @time 2017-11-02
     */
    public function getViewListAction() {
        $condition = $this->put_data;
        $inquiryModel = new InquiryModel();
        $countryModel = new CountryModel();
        $marketAreaCountryModel = new MarketAreaCountryModel();
        $marketAreaModel = new MarketAreaModel();

        $inquiryList = $inquiryModel->getViewList($condition, '*', $this->user['role_no'], $this->user['id'], $this->user['group_id']);

        $countryModel->setCountry($inquiryList, $this->lang);
        $marketAreaCountryModel->setAreaBn($inquiryList);
        $marketAreaModel->setArea($inquiryList);
        (new EmployeeModel)->setUserNames($inquiryList, ['agent_name' => 'agent_id', 'quote_name' => 'quote_id',
            'now_agent_name' => 'now_agent_id', 'created_name' => 'created_by', 'obtain_name' => 'obtain_id']);
        $this->_setBuyerNo($inquiryList);
        $this->_setLogiQuoteFlag($inquiryList);
        $this->_setOrgName($inquiryList);
        $this->_setTransModeName($inquiryList);
        $this->_setContractNo($inquiryList);

        if ($inquiryList) {
            $res['code'] = 1;
            $res['message'] = L('SUCCESS');
            $res['data'] = $inquiryList;
            $res['count'] = $inquiryModel->getViewCount($condition, $this->user['role_no'], $this->user['id'], $this->user['group_id']);
            $this->jsonReturn($res);
        } else {
            $this->setCode('-101');
            $this->setMessage(L('FAIL'));
            $this->jsonReturn();
        }
    }

    /**
     * @desc 分配事业部
     *
     * @author liujf
     * @time 2017-10-24
     */
    public function assignBizUnitAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id']) && !empty($condition['org_id'])) {
            $inquiryModel = new InquiryModel();

            $data = [
                'id' => $condition['inquiry_id'],
                'org_id' => $condition['org_id'],
                'now_agent_id' => $inquiryModel->getInquiryIssueUserId($condition['inquiry_id'], [$condition['org_id']], ['in', [$inquiryModel::inquiryIssueAuxiliaryRole, $inquiryModel::quoteIssueAuxiliaryRole]], ['in', [$inquiryModel::inquiryIssueRole, $inquiryModel::quoteIssueMainRole]], ['in', ['ub', 'eub', 'erui']]),
                'quote_id' => NULL,
                'status' => 'BIZ_DISPATCHING',
                'updated_by' => $this->user['id']
            ];

            $inquiryModel->startTrans();
            $res = $inquiryModel->updateData($data);
            $this->rollback($inquiryModel, null, $res);

            $this->rollback($inquiryModel, Rfq_CheckLogModel::addCheckLog($data['id'], $data['status'], $this->user), null, Rfq_CheckLogModel::$mError);
            $inquiryModel->commit();


            $this->jsonReturn($res);
        } else {
            $this->setCode('-103');
            $this->setMessage(L('MISSING_PARAMETER'));
            $this->jsonReturn();
        }
    }

    /**
     * @desc 获取当前用户询报价角色接口
     *
     * @author liujf
     * @time 2017-10-23
     */
    public function getInquiryUserRoleAction() {
        $inquiryModel = new InquiryModel();
        $org_model = new OrgModel();

        $data = $inquiryModel->getUserRoleByNo($this->user['role_no']);

        if ($data['is_agent'] == 'Y') {
//
//
            $org = $org_model->field('id, name, name_en, name_es, name_ru')
                            ->where(['id' => ['in', $this->user['group_id'] ?: ['-1']],
                                'org_node' => ['in', ['ub', 'eub', 'erui']],
                                'deleted_flag' => 'N'])->order('id DESC')->find();
// 事业部id和名称
            $data['ub_id'] = $org['id'];
            switch ($this->lang) {
                case 'zh' :
                    $data['ub_name'] = $org['name'];
                    break;
                case 'en' :
                    $data['ub_name'] = $org['name_en'];
                    break;
                case 'es' :
                    $data['ub_name'] = $org['name_es'];
                    break;
                case 'ru' :
                    $data['ub_name'] = $org['name_ru'];
                    break;
                default :
                    $data['ub_name'] = $org['name'];
            }
        }

        $res['code'] = 1;
        $res['message'] = L('SUCCESS');
        $res['data'] = $data;

        $this->jsonReturn($res);
    }

    /**
     * @desc 关闭询单
     *
     * @author liujf
     * @time 2017-10-25
     */
    public function closeInquiryAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $inquiryModel = new InquiryModel();

            $agentId = $inquiryModel->where(['id' => $condition['inquiry_id']])->getField('agent_id');

            $data = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $agentId,
                'status' => 'INQUIRY_CLOSED',
                'quote_status' => 'COMPLETED',
                'updated_by' => $this->user['id']
            ];

            $inquiryModel->startTrans();
            $res = $inquiryModel->updateData($data);
            $this->rollback($inquiryModel, null, $res);

            $this->rollback($inquiryModel, Rfq_CheckLogModel::addCheckLog($data['id'], $data['status'], $this->user), null, Rfq_CheckLogModel::$mError);
            $inquiryModel->commit();

            $this->jsonReturn($res);
        } else {
            $this->setCode('-103');
            $this->setMessage(L('MISSING_PARAMETER'));
            $this->jsonReturn();
        }
    }

    /**
     * @desc 退回市场关闭
     *
     * @author liujf
     * @time 2018-03-14
     */
    public function rejectCloseAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $inquiryModel = new InquiryModel();

            $agentId = $inquiryModel->where(['id' => $condition['inquiry_id']])->getField('agent_id');

            $data = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $agentId,
                'status' => 'REJECT_CLOSE',
                'quote_status' => 'NOT_QUOTED',
                'updated_by' => $this->user['id']
            ];

            $inquiryModel->startTrans();
            $res = $inquiryModel->updateData($data);
            $this->rollback($inquiryModel, null, $res);
            $op_note = !empty($condition['op_note']) ? $condition['op_note'] : '';
            $in_node = !empty($condition['in_node']) ? $condition['in_node'] : null;
            $this->rollback($this->inquiryModel, Rfq_CheckLogModel::addCheckLog($condition['inquiry_id'], 'REJECT_CLOSE', $this->user, $in_node, 'REJECT', $op_note), null, Rfq_CheckLogModel::$mError);
            $inquiryModel->commit();

            $this->jsonReturn($res);
        } else {
            $this->setCode('-103');
            $this->setMessage(L('MISSING_PARAMETER'));
            $this->jsonReturn();
        }
    }

    /**
     * @desc 退回重新报价
     *
     * @author liujf
     * @time 2017-10-27
     */
    public function returnRequoteAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $inquiryModel = new InquiryModel();
            $quoteModel = new QuoteModel();
            $finalQuoteModel = new FinalQuoteModel();

            $inquiryModel->startTrans();

            $quoteId = $inquiryModel->where(['id' => $condition['inquiry_id']])->getField('quote_id');

            $data = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $quoteId,
                'status' => 'BIZ_QUOTING',
                'quote_status' => 'ONGOING',
                'updated_by' => $this->user['id']
            ];

            $res1 = $inquiryModel->updateData($data);
            $this->rollback($inquiryModel, null, $res1);
// 更改报价单状态
            $quoteData = [
                'status' => 'BIZ_QUOTING',
                'updated_by' => $this->user['id'],
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $res2 = $quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->save($quoteData);

            $this->rollback($inquiryModel, $res2);

// 更改市场报价单状态
            $res3 = $finalQuoteModel->updateFinal(['inquiry_id' => $condition['inquiry_id'], 'status' => 'BIZ_QUOTING', 'updated_by' => $this->user['id']]);
            $this->rollback($inquiryModel, null, $res3);
            $op_note = !empty($condition['op_note']) ? $condition['op_note'] : '';
            $in_node = !empty($condition['in_node']) ? $condition['in_node'] : null;
            $flag = Rfq_CheckLogModel::addCheckLog($condition['inquiry_id'], 'BIZ_QUOTING', $this->user, $in_node, 'REJECT', $op_note);
            $this->rollback($this->inquiryModel, $flag);
            $inquiryModel->commit();
            $this->setCode('1');
            $this->setMessage(L('SUCCESS'));
            $this->jsonReturn($res1);
        } else {
            $this->setCode('-103');
            $this->setMessage(L('MISSING_PARAMETER'));
            $this->jsonReturn();
        }
    }

    /**
     * @desc 项目澄清
     *
     * @author liujf
     * @time 2018-01-15
     */
    public function projectClarifyAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $inquiryModel = new InquiryModel();
            $op_note = !empty($condition['op_note']) ? $condition['op_note'] : '';
            $in_node = !empty($condition['in_node']) ? $condition['in_node'] : null;
            $inquiry = $inquiryModel->where(['id' => $condition['inquiry_id']])->field('agent_id,status')->find();

            if (!empty($inquiry) && $inquiry['status'] == 'CLARIFY') {
                jsonReturn('', '-101', L('INQUIRY_NODE_ERROR'));
            } elseif (empty($inquiry)) {
                jsonReturn('', '-101', L('INQUIRY_NO_DATA'));
            }

            $data = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $inquiry['agent_id'],
                'status' => 'CLARIFY',
                'updated_by' => $this->user['id']
            ];
            $inquiryModel->startTrans();
            $res = $inquiryModel->updateData($data);
            $this->rollback($inquiryModel, null, $res);
            $this->rollback($inquiryModel, Rfq_CheckLogModel::addCheckLog($condition['inquiry_id'], 'CLARIFY', $this->user, $in_node, 'CLARIFY', $op_note), null, Rfq_CheckLogModel::$mError);
            $inquiryModel->commit();

            $this->setCode('1');
            $this->setMessage(L('SUCCESS'));
            $this->jsonReturn($res);
        } else {
            $this->setCode('-103');
            $this->setMessage(L('MISSING_PARAMETER'));
            $this->jsonReturn();
        }
    }

    /**
     * @desc 完成（回复）项目澄清
     *
     * @author liujf
     * @time 2018-01-15
     */
    public function completeClarifyAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $inquiryModel = new InquiryModel();
            $inquiryCheckLogModel = new InquiryCheckLogModel();

            $inquiry = $inquiryModel->field('inflow_time, org_id, erui_id, quote_id, '
                                    . 'check_org_id, logi_org_id, logi_agent_id, logi_check_id')
                            ->where(['id' => $condition['inquiry_id']])->find();
            $inquiryCheckLog = $inquiryCheckLogModel->getDetail(['inquiry_id' => $condition['inquiry_id']], 'in_node, out_node');


            $error = false;
            if ($inquiryCheckLog['out_node'] == 'CLARIFY') {
                // 根据流入环节获取当前办理人
                switch ($inquiryCheckLog['in_node']) {
                    case 'BIZ_DISPATCHING' :
                        $nowAgentId = $inquiryModel->getInquiryIssueUserId($condition['inquiry_id'], [$inquiry['org_id']], ['in', [$inquiryModel::inquiryIssueAuxiliaryRole, $inquiryModel::quoteIssueAuxiliaryRole]], ['in', [$inquiryModel::inquiryIssueRole, $inquiryModel::quoteIssueMainRole]], ['in', ['ub', 'eub', 'erui']]);
                        break;
                    case 'CC_DISPATCHING' :
                        $nowAgentId = $inquiryModel->getInquiryIssueUserId($condition['inquiry_id'], [$inquiry['erui_id']], $inquiryModel::inquiryIssueAuxiliaryRole, $inquiryModel::inquiryIssueRole, 'erui');
                        break;
                    case 'BIZ_QUOTING' :
                        $nowAgentId = $inquiry['quote_id'];
                        break;
                    case 'LOGI_DISPATCHING' :
                        $nowAgentId = $inquiryModel->getInquiryIssueUserId($condition['inquiry_id'], [$inquiry['logi_org_id']], $inquiryModel::logiIssueAuxiliaryRole, $inquiryModel::logiIssueMainRole, ['in', ['lg', 'elg']]);
                        break;
                    case 'LOGI_QUOTING' :
                        $nowAgentId = $inquiry['logi_agent_id'];
                        break;
                    case 'REJECT_QUOTING' :
                        $nowAgentId = $inquiry['quote_id'];
                        break;
                    case 'DRAFT' :
                        $nowAgentId = $inquiry['agent_id'];
                        break;
                    case 'REJECT_MARKET' :
                        $nowAgentId = $inquiry['agent_id'];
                        break;
                    case 'MARKET_CONFIRMING' :
                        $nowAgentId = $inquiry['agent_id'];
                        break;
                    case 'QUOTE_SENT' :
                        $nowAgentId = $inquiry['agent_id'];
                        break;
                    case 'INQUIRY_CLOSE' :
                        $nowAgentId = $inquiry['agent_id'];
                        break;
                    case 'LOGI_APPROVING' :
                        $nowAgentId = $inquiry['logi_check_id'];
                        break;
                    case 'BIZ_APPROVING' :
                        $nowAgentId = $inquiry['quote_id'];
                        break;
                    case 'MARKET_APPROVING' :
                        $nowAgentId = $inquiry['check_org_id'];
                        break;
                    default :
                        $error = true;
                }
            } else {
                $error = true;
            }

            if ($error) {
                jsonReturn('', '-101', L('INQUIRY_NODE_ERROR'));
            }

            $inquiryModel->startTrans();

            $data = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $nowAgentId,
                'status' => $inquiryCheckLog['in_node'],
                'updated_by' => $this->user['id']
            ];

            $log = [
                'inquiry_id' => $condition['inquiry_id'],
                'action' => 'CLARIFY',
                'in_node' => 'CLARIFY',
                'out_node' => $inquiryCheckLog['in_node'],
                'into_at' => $inquiry['inflow_time'],
                'op_note' => $condition['op_note']
            ];

            $res1 = $inquiryModel->updateData($data);

            $res2 = $this->_addInquiryCheckLog($log);

            if ($res1['code'] == 1 && $res2['code'] == 1) {
                $inquiryModel->commit();
                $res = true;
            } else {
                $inquiryModel->rollback();
                $res = false;
            }

            if ($res) {
                $this->setCode('1');
                $this->setMessage(L('SUCCESS'));
                $this->jsonReturn($res);
            } else {
                $this->setCode('-101');
                $this->setMessage(L('FAIL'));
                $this->jsonReturn();
            }
        } else {
            $this->setCode('-103');
            $this->setMessage(L('MISSING_PARAMETER'));
            $this->jsonReturn();
        }
    }

    /**
     * @desc 项目澄清列表
     *
     * @author liujf
     * @time 2018-01-15
     */
    public function getClarifyListAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $inquiryCheckLogModel = new InquiryCheckLogModel();
            $employeeModel = new EmployeeModel();
            $inquiryModel = new InquiryModel();

            $where['inquiry_id'] = $condition['inquiry_id'];
            $where['_complex']['in_node'] = $where['_complex']['out_node'] = 'CLARIFY';
            $where['_complex']['_logic'] = 'or';

            $field = 'in_node, out_node, op_note, created_by, created_at';
            $clarifyList = $inquiryCheckLogModel->field($field)->where($where)->order('id ASC')->select();

            foreach ($clarifyList as &$clarify) {
                $clarify['created_name'] = $employeeModel->getUserNameById($clarify['created_by']);
                $clarify['now_agent_id'] = $inquiryModel->where(['id' => $condition['inquiry_id']])->getField('now_agent_id');
                $clarify['now_agent_name'] = $employeeModel->getUserNameById($clarify['now_agent_id']);
            }

            if ($clarifyList) {
                $res['code'] = 1;
                $res['message'] = L('SUCCESS');
                $res['data'] = $clarifyList;
                $this->jsonReturn($res);
            } else {
                $this->setCode('-101');
                $this->setMessage(L('FAIL'));
                $this->jsonReturn();
            }
        } else {
            $this->setCode('-103');
            $this->setMessage(L('MISSING_PARAMETER'));
            $this->jsonReturn();
        }
    }

    /*
     * 询价单详情
     * Author:张玉良、刘俊飞
     */

    public function getInfoAction() {
        $inquiry = new InquiryModel();
        $employee = new EmployeeModel();
        $countryModel = new CountryModel();
        $marketAreaModel = new MarketAreaModel();
        $buyerModel = new BuyerModel();
        $org = new OrgModel();
        $inquiryCheckLogModel = new InquiryCheckLogModel();
        $transModeModel = new TransModeModel();
        $portModel = new PortModel();

        $where = $this->put_data;

        $inquiryStatus = $inquiry->getInquiryStatus();

        $results = $inquiry->getInfo($where);
        $org_id = $results['data']['org_id'];
        $results['data']['org_parent_id'] = '';
        if ($org_id) {
            $results['data']['org_parent_id'] = $org->getParentid($org_id);
        }
//BOSS编码
        if (!empty($results['data']['buyer_id'])) {
            $results['data']['buyer_no'] = $buyerModel->where(['id' => $results['data']['buyer_id']])->getField('buyer_no');
        }

        $employee->setUserName($results['data'], [
            'agent_name' => 'agent_id',
            'quote_name' => 'quote_id',
            'current_name' => 'now_agent_id',
            'created_name' => 'created_by',
            'logi_agent_name' => 'logi_agent_id',
            'check_org_name' => 'check_org_id',
            'logi_check_name' => 'logi_check_id',
            'obtain_name' => 'obtain_id']);

//事业部
        if (!empty($results['data']['org_id'])) {
            $results['data']['org_name'] = $org->where(['id' => $results['data']['org_id'], 'deleted_flag' => 'N'])->getField('name');
        }
//询单所在国家
        if (!empty($results['data']['country_bn'])) {
            $results['data']['country_name'] = $countryModel->getCountryNameByBn($results['data']['country_bn'], $this->lang);
        }
//询单所在区域
        if (!empty($results['data']['area_bn'])) {
            $results['data']['area_name'] = $marketAreaModel->getAreaNameByBn($results['data']['area_bn'], $this->lang);
        }
//项目获取人
        $employee->setCitizenship($results['data']);

//起运国
        if (!empty($results['data']['from_country'])) {
            $results['data']['from_country_name'] = $countryModel->getCountryNameByBn($results['data']['from_country'], $this->lang);
        }
//目的国
        if (!empty($results['data']['to_country'])) {
            $results['data']['to_country_name'] = $countryModel->getCountryNameByBn($results['data']['to_country'], $this->lang);
        }
//起运港
        if (!empty($results['data']['from_port'])) {
            $results['data']['from_port_name'] = $portModel->getPortNameByBn($results['data']['from_country'], $results['data']['from_port'], $this->lang);
        }
//目的港
        if (!empty($results['data']['to_port'])) {
            $results['data']['to_port_name'] = $portModel->getPortNameByBn($results['data']['to_country'], $results['data']['to_port'], $this->lang);
        }
//运输方式
        if (!empty($results['data']['trans_mode_bn'])) {
            $results['data']['trans_mode_name'] = $transModeModel->getTransModeByBn($results['data']['trans_mode_bn'], $this->lang);
        }

        if (!empty($results['data'])) {
            $results['data']['status_name'] = $inquiryStatus[$results['data']['status']];
            $results['data']['agent_name'] = $results['data']['agent_name'] ?: L('NOTHING');
            $results['data']['obtain_name'] = $results['data']['obtain_name'] ?: L('NOTHING');
            $results['data']['current_name'] = $results['data']['current_name'] ?: L('NOTHING');
            $results['data']['quote_name'] = $results['data']['quote_name'] ?: L('NOTHING');
            $results['data']['logi_agent_name'] = $results['data']['logi_agent_name'] ?: L('NOTHING');
            $results['data']['dispatch_place'] = $results['data']['dispatch_place'] ?: L('NOTHING');
            $results['data']['inquiry_no'] = $results['data']['inquiry_no'] ?: L('NOTHING');
//$results['data']['project_name'] = $results['data']['project_name'] ? : L('NOTHING');

            if ($results['data']['status'] == 'MARKET_APPROVING') {
                $approvingTime = $inquiryCheckLogModel->where(['inquiry_id' => $results['data']['id'], 'out_node' => 'MARKET_APPROVING'])->order('id DESC')->getField('out_at');
            }
            $results['data']['delay_48'] = isset($approvingTime) && (time() - strtotime($approvingTime)) / 3600 > 48 ? 'Y' : 'N';
        }

        $this->jsonReturn($results);
    }

    /*
     * 添加询价单
     * Author:张玉良
     */

    /* public function addAction() {
      $inquiry = new InquiryModel();
      $data = $this->put_data;
      $data['agent_id'] = $this->user['id'];
      $data['created_by'] = $this->user['id'];

      $results = $inquiry->addData($data);
      $this->jsonReturn($results);
      } */

    /*
     * 修改询价单
     * Author:张玉良
     */

    public function updateAction() {
        $inquiry = new InquiryModel();
        $data = $this->put_data;

        $data['inquiry_no'] = $data['inquiry_no'] == L('NOTHING') ? null : $data['inquiry_no'];
        $data['dispatch_place'] = $data['dispatch_place'] == L('NOTHING') ? null : $data['dispatch_place'];
        if (!empty($data['org_id']) && $data['org_id'] == 'ERUI') {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage($this->lang == 'en' ? 'Please choose the business department By Erui!' : '请选择易瑞下的事业部!');
            $this->jsonReturn();
        }
        $data['updated_by'] = $this->user['id'];
        unset($data['agent_id']);

        $results = $inquiry->updateData($data);
        $this->jsonReturn($results);
    }

    /*
     * 批量修改询价单状态
     * Author:张玉良
     */

    public function updateStatusAction() {
        $inquiry = new InquiryModel();
        $data = $this->put_data;
        $data['updated_by'] = $this->user['id'];
        if (empty($data['org_id'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage($this->lang == 'en' ? 'Please choose the business department!' : '请选择事业部!');
            $this->jsonReturn();
        } elseif (!empty($data['org_id']) && $data['org_id'] == 'ERUI') {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage($this->lang == 'en' ? 'Please choose the business department By Erui!' : '请选择易瑞下的事业部!');
            $this->jsonReturn();
        } elseif (!empty($data['org_id']) && $data['org_id'] != 'ERUI') {
            $childs_id = (new OrgModel())->getChilds($data['org_id']);
            if ($childs_id) {
                $this->setCode(MSG::ERROR_PARAM);
                $this->setMessage('请选择二级事业部!');
                $this->jsonReturn();
            }
        }
        if ($data['status'] == 'BIZ_DISPATCHING') {
            $data['now_agent_id'] = $inquiry->getInquiryIssueUserId($data['id'], [$data['org_id']], ['in', [$inquiry::inquiryIssueAuxiliaryRole, $inquiry::quoteIssueAuxiliaryRole]], ['in', [$inquiry::inquiryIssueRole, $inquiry::quoteIssueMainRole]], ['in', ['ub', 'eub', 'erui']]);
        }
        $inquiry->startTrans();
        $results = $inquiry->updateStatus($data);
        $this->rollback($inquiry, null, $results);
        $this->rollback($inquiry, Rfq_CheckLogModel::addCheckLog($data['id'], $data['status'], $this->user), null, Rfq_CheckLogModel::$mError);
        $inquiry->commit();
        $this->jsonReturn($results);
    }

    /*
     * 删除询价单
     * Author:张玉良
     */

    public function deleteAction() {
        $inquiry = new InquiryModel();
        $where = $this->put_data;

//验证删除的数据是否全部是草稿状态
        if (empty($where['id'])) {
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            $this->jsonReturn($results);
        }
        $data = $inquiry->field('id,serial_no,status')->where('status!="DRAFT" and id in(' . $where['id'] . ')')->select();
        if (count($data) > 0) {
            $results['code'] = '-104';
            $results['message'] = L('INQUIRY_NOT_ALLOWED_DELETE');
            $this->jsonReturn($results);
        }

        $results = $inquiry->deleteData($where);
        $this->jsonReturn($results);
    }

    /*
     * 附件列表
     * Author:张玉良
     */

    public function getAttachListAction() {
        $attach = new InquiryAttachModel();
        $employee = new EmployeeModel();
        $roleuser = new RoleUserModel();
        $buyer = new BuyerModel();
        $where = $this->put_data;

        $results = $attach->getList($where);

        if ($results['code'] == 1) {
            foreach ($results['data'] as $key => $val) {
//修改URL地址，增加下载文件改名
                if (!empty($val['attach_name'])) {
                    $results['data'][$key]['attach_url'] = $val['attach_url'] . '?filename=' . $val['attach_name'];
                }
                if ($val['attach_group'] == 'BUYER') {
                    $buyerdata = $buyer->field('id,name')->where('id=' . $val['created_by'])->find();
                    $results['data'][$key]['createhgd_name'] = $buyerdata['name'];
                } else {
                    $employeedata = $employee->field('id,name')->where('id=' . $val['created_by'])->find();
                    $results['data'][$key]['created_name'] = $employeedata['name'];

                    $roledata = $roleuser->alias('a')
                            ->join('erui_sys.role b ON a.role_id = b.id', 'LEFT')
                            ->where('a.employee_id=' . $val['created_by'])
                            ->field('b.name,b.name_en,b.remarks')
                            ->find();
                    $results['data'][$key]['created_role'] = $roledata['name'];
                }
            }
        }

        $this->jsonReturn($results);
    }

    /*
     * 添加附件
     * Author:张玉良
     */

    public function addAttachAction() {
        $attach = new InquiryAttachModel();
        $data = $this->put_data;
        $data['created_by'] = $this->user['id'];

        $results = $attach->addData($data);

        $this->jsonReturn($results);
    }

    /*
     * 删除附件
     * Author:张玉良
     */

    public function deleteAttachAction() {
        $attach = new InquiryAttachModel();
        $data = $this->put_data;
        $data['updated_by'] = $this->user['id'];

        $results = $attach->deleteData($data);
        $this->jsonReturn($results);
    }

    /*
     * 询单sku列表
     * Author:张玉良
     */

    public function getItemListAction() {
        $Item = new InquiryItemModel();

        $where = $this->put_data;

        $results = $Item->getJoinList_($where);
        $this->jsonReturn($results);
    }

    /*
     * 询单sku详情
     * Author:张玉良
     */

    public function getItemInfoAction() {
        $Item = new InquiryItemModel();

        $where = $this->put_data;

        $results = $Item->getInfo($where);
        $this->jsonReturn($results);
    }

    /*
     * 添加询单sku
     * Author:张玉良
     */

    public function addItemAction() {
        $Item = new InquiryItemModel();
        $data = $this->put_data;
        $data['created_by'] = $this->user['id'];

        $results = $Item->addData($data);
        $this->jsonReturn($results);
    }

    /*
     * 批量添加询单sku
     * Author:张玉良
     */

    public function addItemBatchAction() {
        $Item = new InquiryItemModel();
        $data = $this->put_data;
        $data['created_by'] = $this->user['id'];

        $results = $Item->addDataBatch($data);
        $this->jsonReturn($results);
    }

    /*
     * 修改询单sku
     * Author:张玉良
     */

    public function updateItemAction() {
        $Item = new InquiryItemModel();
        $data = $this->put_data;
        $data['updated_by'] = $this->user['id'];

        $results = $Item->updateData($data);
        if (!empty($results['code']) && $results['code'] == 1) {
            (new TemporaryGoodsModel)->sync();
        }
        $this->jsonReturn($results);
    }

    /*
     * 批量修改询单sku
     * Author:张玉良、刘俊飞
     */

    public function updateItemBatchAction() {
        $Item = new InquiryItemModel();
        $data = $this->put_data;

        if (isset($data['sku'])) {
            $Item->startTrans();
// 记录插入的询单SKU主键ID
            $insertItemIds = [];
            foreach ($data['sku'] as $val) {
                $condition = $val;
                if ($condition['name'] == '' || $condition['name_zh'] == '' || $condition['qty'] == '' || $condition['unit'] == '') {
                    if ($condition['id'] == '') {
                        continue;
                    } else {
                        $results = $Item->deleteData($condition);
                    }
                } else {
                    if (!isDecimal($condition['qty'])) {
                        $condition['qty'] = null;
                    }
                    if ($condition['id'] == '') {
                        $condition['inquiry_id'] = $data['id'];
                        $condition['created_by'] = $this->user['id'];
                        $results = $Item->addData($condition);
                    } else {
                        $condition['updated_by'] = $this->user['id'];
                        $results = $Item->updateData($condition);
                    }
                }
                if ($results['code'] != 1) {
                    $Item->rollback();
                    $this->jsonReturn($results);
                } else {
                    if ($results['insert_id']) {
                        $insertItemIds[] = $results['insert_id'];
                    }
                }
            }
            $Item->commit();
        } else {
            $results['code'] = '-101';
            $results['message'] = L('FAIL');
        }
        if ($insertItemIds) {
            $results['insert_item_ids'] = $insertItemIds;
            (new TemporaryGoodsModel)->sync();
        }
        $this->jsonReturn($results);
    }

    /*
     * 删除询单sku
     * Author:张玉良
     */

    public function deleteItemAction() {
        $Item = new InquiryItemModel();
        $data = $this->put_data;

        $results = $Item->deleteData($data);
        $this->jsonReturn($results);
    }

    /**
     * @desc 删除指定询单的所有SKU
     *
     * @author liujf
     * @time 2018-04-09
     */
    public function delInquiryItemAction() {
        $condition = $this->put_data;
        if (!empty($condition['inquiry_id'])) {
            $inquiryItemModel = new InquiryItemModel();
            $res = $inquiryItemModel->delByInquiryId($condition['inquiry_id']);
            if ($res) {
                $this->setCode('1');
                $this->setMessage(L('SUCCESS'));
                $this->jsonReturn($res);
            } else {
                $this->setCode('-101');
                $this->setMessage(L('FAIL'));
                $this->jsonReturn();
            }
        } else {
            $this->setCode('-103');
            $this->setMessage(L('MISSING_PARAMETER'));
            $this->jsonReturn();
        }
    }

    /**
     * @desc 获取SKU历史报价列表
     *
     * @author liujf
     * @time 2018-04-11
     */
    public function getHistoricalSkuQuoteListAction() {
        $condition = $this->put_data;
        $historicalSkuQuoteModel = new HistoricalSkuQuoteModel();
        $historicalSkuQuoteList = $historicalSkuQuoteModel->getList($condition);
        foreach ($historicalSkuQuoteList as &$historicalSkuQuote) {
            $historicalSkuQuote['matching_percent'] .= '%';
        }
        if ($historicalSkuQuoteList) {
            $res['code'] = 1;
            $res['message'] = L('SUCCESS');
            $res['data'] = $historicalSkuQuoteList;
            $res['count'] = $historicalSkuQuoteModel->getCount($condition);
// 采购价格区间
            $res['price_range'] = $historicalSkuQuoteModel->getPriceRange($condition);
// 匹配的品名数
            $res['matching_name_count'] = $historicalSkuQuoteModel->getMatchingNameCount($condition);
            $this->jsonReturn($res);
        } else {
            $this->setCode('-101');
            $this->setMessage(L('FAIL'));
            $this->jsonReturn();
        }
    }

    /*
     * 询单sku附件列表
     * Author:张玉良
     */

    public function getItemAttachListAction() {
        $ItemAttach = new InquiryItemAttachModel();

        $where = $this->put_data;

        $results = $ItemAttach->getlist($where);
        $this->jsonReturn($results);
    }

    /*
     * 添加询单sku附件
     * Author:张玉良
     */

    public function addItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();
        $data = $this->put_data;
        $data['created_by'] = $this->user['id'];

        $results = $ItemAttach->addData($data);
        $this->jsonReturn($results);
    }

    /*
     * 删除询单sku附件
     * Author:张玉良
     */

    public function deleteItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();
        $data = $this->put_data;

        $results = $ItemAttach->deleteData($data);
        $this->jsonReturn($results);
    }

    /**
     * @desc 获取日志列表
     *
     * @author liujf
     * @time 2017-10-26
     */
    public function getCheckLogListAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $inquiryModel = new InquiryModel();
            $inquiryCheckLogModel = new InquiryCheckLogModel();
            $employeeModel = new EmployeeModel();

            $inquiryCheckLogList = $inquiryCheckLogModel->getList($condition);

            $inquiryStatus = $inquiryModel->getInquiryStatus();

            $action = [
                'CREATE' => L('INQUIRY_LOG_CREATE'),
                'REJECT' => L('INQUIRY_LOG_REJECT'),
                'APPROVE' => L('INQUIRY_LOG_APPROVE'),
                'REMIND' => L('INQUIRY_LOG_REMIND')
            ];

            foreach ($inquiryCheckLogList as &$inquiryCheckLog) {
                $inquiryCheckLog['action_name'] = $action[$inquiryCheckLog['action']];
                $inquiryCheckLog['in_node_name'] = $inquiryStatus[$inquiryCheckLog['in_node']];
                $inquiryCheckLog['out_node_name'] = $inquiryStatus[$inquiryCheckLog['out_node']];
                $inquiryCheckLog['created_name'] = $employeeModel->getUserNameById($inquiryCheckLog['created_by']);
            }

            if ($inquiryCheckLogList) {
                $res['code'] = 1;
                $res['message'] = L('SUCCESS');
                $res['data'] = $inquiryCheckLogList;
                $res['count'] = $inquiryCheckLogModel->getCount($condition);
                $this->jsonReturn($res);
            } else {
                $this->setCode('-101');
                $this->setMessage(L('FAIL'));
                $this->jsonReturn();
            }
        } else {
            $this->setCode('-103');
            $this->setMessage(L('MISSING_PARAMETER'));
            $this->jsonReturn();
        }
    }

    /*
     * 添加审核日志
     * Author:张玉良
     */

    public function addCheckLogAction() {
        $data = $this->put_data;

        $result = $this->_addInquiryCheckLog($data);

        $this->jsonReturn($result);
    }

    public function cleanInquiryRemind($inquiry_id) {

        $inquiryModel = new InquiryModel();
        $inquiryModel->where(['id' => $inquiry_id])->save(['remind' => 0]);

//提醒详情标为已读
        $inquiryRemind = new InquiryRemindModel();
        $inquiryRemind->where(['inquiry_id' => $inquiry_id])->save([
            'isread_flag' => 'Y',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return;
    }

    /**
     * @desc 获取日志详情
     *
     * @author liujf
     * @time 2017-10-24
     */
    public function getCheckLogDetailAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $inquiryCheckLogModel = new InquiryCheckLogModel();
            $employeeModel = new EmployeeModel();


            $res = $inquiryCheckLogModel->getDetail($condition);

            if (empty($res)) {
                $res = [];
            } elseif (!empty($res) && $res['agent_id'] != UID) {
                $inquiry = (new InquiryModel())->where(['id' => $condition['inquiry_id']])->find();
                if (empty($inquiry)) {
                    $res = [];
                } elseif (!empty($inquiry) && $inquiry['now_agent_id'] == UID) {

                } else {

                    switch ($res['out_node']) {
                        case 'DRAFT'://新建询单
                            $inquiry['obtain_id'] != UID ? $res = [] : '';
                            break;
                        case 'REJECT_MARKET'://驳回市场
                            $inquiry['agent_id'] != UID ? $res = [] : '';
                            break;
                        case 'REJECT_CLOSE'://驳回市场关闭

                            $inquiry['agent_id'] != UID ?
                                            ( $inquiry['obtain_id'] != UID ? $res = [] : '') :
                                            '';
                            break;
                        case 'BIZ_DISPATCHING'://事业部分单员
                            $permissions = (new InquiryModel())
                                    ->getPermissions($condition['inquiry_id'], $inquiry['org_id'], [InquiryModel::inquiryIssueAuxiliaryRole,
                                InquiryModel::quoteIssueAuxiliaryRole], [InquiryModel::inquiryIssueRole,
                                InquiryModel::quoteIssueMainRole], ['in', ['ub', 'erui', 'eub']], $this->user);
                            $permissions === false ? $res = [] : null;
                            break;
                        case 'CC_DISPATCHING'://易瑞客户中心
                            $permissions = (new InquiryModel())
                                    ->getPermissions($condition['inquiry_id'], $inquiry['erui_id'], InquiryModel::inquiryIssueAuxiliaryRole, InquiryModel::inquiryIssueRole, 'erui', $this->user);
                            $permissions === false ? $res = [] : null;

                            break;
                        case 'BIZ_QUOTING'://事业部报价
                            $inquiry['quote_id'] != UID ? $res = [] : '';
                            break;
                        case 'REJECT_QUOTING'://事业部审核退回事业部报价
                            $inquiry['quote_id'] != UID ? $res = [] : '';
                            break;
                        case 'LOGI_DISPATCHING'://物流分单员
                            $permissions = (new InquiryModel())
                                    ->getPermissions($condition['inquiry_id'], $inquiry['logi_org_id'], InquiryModel::logiIssueAuxiliaryRole, InquiryModel::logiIssueMainRole, ['in', ['lg', 'elg']], $this->user);
                            $permissions === false ? $res = [] : null;
                            break;
                        case 'LOGI_QUOTING'://物流报价
                            $inquiry['logi_agent_id'] != UID ? $res = [] : '';
                            break;
                        case 'LOGI_APPROVING'://物流审核
                            $inquiry['logi_check_id'] != UID ? $res = [] : '';
                            break;
                        case 'BIZ_APPROVING'://事业部核算
                            $inquiry['quote_id'] != UID ? $res = [] : '';
                            break;
                        case 'MARKET_APPROVING'://事业部审核
                            !in_array(UID, [$inquiry['agent_id'], $inquiry['check_org_id']]) ? $res = [] : '';
                            break;
                    }
                }
            }
            if (!empty($res['created_by'])) {
                $res['created_name'] = $employeeModel->where(['id' => $res['created_by']])->getField('name');
            }

            if ($res) {
                $this->setCode('1');
                $this->setMessage(L('SUCCESS'));
                $this->jsonReturn($res);
            } else {
                $this->setCode('-101');
                $this->setMessage(L('FAIL'));
                $this->jsonReturn();
            }
        } else {
            $this->setCode('-103');
            $this->setMessage(L('MISSING_PARAMETER'));
            $this->jsonReturn();
        }
    }

    /*
     * 根据条件返回所有组ID
     * Condition 1.市场组; 2.方案中心组; 3.产品线报价组; 4.物流报价组
     * Author:张玉良
     */

    public function getGroupListAction() {
        $bizlinegroup = new BizlineGroupModel();
        $marketareateam = new MarketAreaTeamModel();

        $where = $this->put_data;

        if (!empty($where['type'])) {
            $type = explode(',', $where['type']);
            $data = [];
            foreach ($type as $val) {
                if ($val == 1) {//所有市场群组
                    $market_org_ids = $marketareateam->field('market_org_id')->group('market_org_id')->getField('market_org_id', true);
                    !empty($market_org_ids) ? $data['market_org'] = implode(',', $market_org_ids) : null;
                }
                if ($val == 2) {//所有方案中心群组
                    $biz_tech_org_ids = $marketareateam->group('biz_tech_org_id')->getField('biz_tech_org_id', true);
                    !empty($biz_tech_org_ids) ? $data['biz_tech_org'] = implode(',', $biz_tech_org_ids) : null;
                }
                if ($val == 3) {//所有产品线群组
                    $group_ids = $bizlinegroup->group('group_id')->getField('group_id', true);
                    !empty($group_ids) ? $data['biz_group_org'] = implode(',', $group_ids) : null;
                }
                if ($val == 4) {//所有物流报价群组
                    $logi_quote_org_ids = $marketareateam->field('logi_quote_org_id')->group('logi_quote_org_id')->getField('logi_quote_org_id', true);
                    !empty($logi_quote_org_ids) ? $data['biz_group_org'] = implode(',', $logi_quote_org_ids) : null;
                }
            }

            $results['code'] = '1';
            $results['message'] = L('SUCCESS');
            $results['data'] = $data;
        } else {
            $results['code'] = '-101';
            $results['message'] = L('FAIL');
        }
        $this->jsonReturn($results);
    }

    /**
     * 询单导出
     */
    public function exportAction() {
        $inquiry_model = new InquiryModel();

        set_time_limit(0);
        $localDir = $inquiry_model->export();

        if ($localDir) {
            jsonReturn($localDir);
        } else {
            jsonReturn('', L('FAIL'));
        }
    }

    /**
     * @desc 记录询单日志
     *
     * @param array $data
     * @return mixed
     * @author liujf
     * @time 2018-01-15
     */
    private function _addInquiryCheckLog($data) {
        $checklog = new CheckLogModel();

        $data['agent_id'] = $this->user['id'];
        $data['created_by'] = $this->user['id'];

        $result = $checklog->addData($data);

//发送短信
        $inquiryModel = new InquiryModel();
        $inquiryInfo = $inquiryModel->where(['id' => $data['inquiry_id']])->field('now_agent_id,serial_no,org_id')->find();

        $employeeModel = new EmployeeModel();
        $receiverInfo = $employeeModel
                        ->where(['id' => $inquiryInfo['now_agent_id']])
                        ->field('name,mobile,email')->find();

//QUOTE_SENT-报价单已发出 INQUIRY_CLOSED-报价关闭 状态下不发送短信
        if (!in_array($data['out_node'], ['QUOTE_SENT', 'INQUIRY_CLOSED'])) {

//
            if ($data['out_node'] == 'BIZ_DISPATCHING' && !empty($inquiryInfo['org_id'])) {

                $user = (new OrgMemberModel())->getSmsUserByOrgId($inquiryInfo['org_id']);

                if (!empty($user)) {
                    $this->sendSms($user['mobile'], $data['action'], $user['name'], $inquiryInfo['serial_no'], $this->user['name'], $data['in_node'], $data['out_node']);
                } else {
                    $this->sendSms($receiverInfo['mobile'], $data['action'], $receiverInfo['name'], $inquiryInfo['serial_no'], $this->user['name'], $data['in_node'], $data['out_node']);
                }
            } else {
                $this->sendSms($receiverInfo['mobile'], $data['action'], $receiverInfo['name'], $inquiryInfo['serial_no'], $this->user['name'], $data['in_node'], $data['out_node']);
            }
//发送邮件通知
//            $role_name = $inquiryModel->setRoleName($inquiryModel->getUserRoleById($this->user['id']));
//
//            if ($data['action'] =='CREATE'){
//
//                $title = '【询报价】办理通知';
//
//                $body = <<< Stilly
//                    <h2>【{$role_name}】{$this->user['name']}</h2>
//                    <p>您好！由【{$role_name}】{$this->user['name']}，提交的【询单流水号：{$inquiryInfo['serial_no']}】，需要您的办理，请登录BOSS系统 (<a href="http://boss.erui.com">boss.erui.com</a>) 及时进行处理。</p>
//Stilly;
//            }else{
//
//                $title = '【询报价】退回通知';
//                $body = <<< Stilly
//                    <h2>【{$role_name}】{$this->user['name']}</h2>
//                    <p>您好！由【{$role_name}】{$this->user['name']}，提交的【询单流水号：{$inquiryInfo['serial_no']}】，需要您的办理，请登录BOSS系统 (<a href="http://boss.erui.com">boss.erui.com</a>) 及时进行处理。</p>
//Stilly;
//
//            }
//
//            send_Mail($receiverInfo['email'], $title, $body, $receiverInfo['name']);
        }


//催办测试清零
        $this->cleanInquiryRemind($data['inquiry_id']);

        return $result;
    }

    /*
     * 添加询单转订单
     * Author:jhw
     */

    public function addInquiryOrderAction() {
        $attach = new InquiryOrderModel();
        $data = $this->put_data;
        $results = $attach->addData($data);
        $this->jsonReturn($results);
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setBuyerNo(&$arr) {
        if ($arr) {
            $buyer_model = new BuyerModel();
            $buyer_ids = [];
            foreach ($arr as $key => $val) {
                if (isset($val['buyer_id']) && $val['buyer_id']) {
                    $buyer_ids[] = $val['buyer_id'];
                }
            }
            $buyer_nos = [];
            if ($buyer_ids) {
                $buyers = $buyer_model->field('id,buyer_no')->where(['id' => ['in', $buyer_ids]])->select();
                foreach ($buyers as $buyer) {
                    $buyer_nos[$buyer['id']] = $buyer['buyer_no'];
                }
            }
            foreach ($arr as $key => $val) {

                if ($val['buyer_id'] && isset($buyer_nos[$val['buyer_id']])) {
                    $val['buyer_no'] = $buyer_nos[$val['buyer_id']];
                } else {
                    $val['buyer_no'] = '';
                }

                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setLogiQuoteFlag(&$arr) {
        if ($arr) {
            $quote_model = new QuoteModel();
            $inquiry_ids = [];
            foreach ($arr as $key => $val) {
                if (isset($val['id']) && $val['id']) {
                    $inquiry_ids[] = $val['id'];
                }
            }
            $logi_quote_flags = [];
            $quotes = $quote_model->where(['inquiry_id' => ['in', $inquiry_ids]])
                            ->field('inquiry_id,logi_quote_flag')->select();
            foreach ($quotes as $quote) {
                $logi_quote_flags[$quote['inquiry_id']] = $quote['logi_quote_flag'];
            }
            foreach ($arr as $key => $val) {
                if ($val['id'] && isset($logi_quote_flags[$val['id']])) {
                    $val['logi_quote_flag'] = $logi_quote_flags[$val['id']];
                } else {
                    $val['logi_quote_flag'] = '';
                }

                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setOrgName(&$arr) {
        if ($arr) {
            $org_model = new OrgModel();
            $org_ids = [];
            foreach ($arr as $key => $val) {
                if (isset($val['org_id']) && $val['org_id']) {
                    $org_ids[] = $val['org_id'];
                }
            }
            $orgnames = [];
            if ($org_ids) {
                $orgs = $org_model->where(['id' => ['in', $org_ids], 'deleted_flag' => 'N'])
                                ->field('id,name')->select();
                foreach ($orgs as $org) {
                    $orgnames[$org['id']] = $org['name'];
                }
            }
            foreach ($arr as $key => $val) {
                if ($val['org_id'] && isset($orgnames[$val['org_id']])) {
                    $val['org_name'] = $orgnames[$val['org_id']];
                } else {
                    $val['org_name'] = '';
                }

                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setContractNo(&$arr) {
        if ($arr) {
            $inquiry_order_model = new InquiryOrderModel();
            $inquiry_ids = [];
            foreach ($arr as $key => $val) {
                if (isset($val['id']) && $val['id']) {
                    $inquiry_ids[] = $val['id'];
                }
            }
            $inquiry_orders = $inquiry_order_model->where(['inquiry_id' => ['in', $inquiry_ids]])
                            ->field('inquiry_id,contract_no')->select();


            $contract_nos = [];
            foreach ($inquiry_orders as $inquiry_order) {
                $contract_nos[$inquiry_order['inquiry_id']] = $inquiry_order['contract_no'];
            }
            foreach ($arr as $key => $val) {
                if ($val['id'] && isset($contract_nos[$val['id']])) {
                    $val['contract_no'] = $contract_nos[$val['id']];
                } else {
                    $val['contract_no'] = '';
                }

                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setTransModeName(&$arr) {
        if ($arr) {
            $trans_mode_model = new TransModeModel();
            $trans_mode_bns = [];
            foreach ($arr as $key => $val) {
                if (isset($val['trans_mode_bn']) && $val['trans_mode_bn']) {
                    $trans_mode_bns[] = $val['trans_mode_bn'];
                }
            }
            $trans_mode_names = [];
            if ($trans_mode_bns) {
                $trans_modes = $trans_mode_model->where(['bn' => ['in', $trans_mode_bns], 'lang' => $this->lang, 'deleted_flag' => 'N'])
                                ->field('bn,trans_mode')->select();
                foreach ($trans_modes as $trans_mode) {
                    $trans_mode_names[$trans_mode['bn']] = $trans_mode['trans_mode'];
                }
            }
            foreach ($arr as $key => $val) {
                if ($val['trans_mode_bn'] && isset($trans_mode_names[$val['trans_mode_bn']])) {
                    $val['trans_mode_name'] = $trans_mode_names[$val['trans_mode_bn']];
                } else {
                    $val['trans_mode_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /**
     * @desc 删除指定报价单的所有SKU
     *
     * @author liujf
     * @time 2018-04-19
     */
    public function DelItemAction() {
        $inquiryItemModel = new InquiryItemModel();
        $inquiryId = $this->validateRequests('inquiryId');

        $res1 = $inquiryItemModel->delByInquiryId($inquiryId);

        if ($res1 !== false) {

            $res = true;
        } else {

            $res = false;
        }
        if ($res) {
            $this->setCode('1');
            $this->setMessage(L('SUCCESS'));
            $this->jsonReturn($res);
        } else {
            $this->setCode('-101');
            $this->setMessage(L('FAIL'));
            $this->jsonReturn();
        }
    }

    /*
     * 回滚判断
     */

    private function rollback(&$inquiry, $flag, $results = null, $error = null) {
        if (!empty($results) && isset($results['code']) && $results['code'] != 1) {
            $inquiry->rollback();
            $this->jsonReturn($results);
        } elseif ($results === false) {
            $inquiry->rollback();
            $this->setCode('-101');
            $this->setMessage(L('FAIL') . $error);
            $this->jsonReturn();
        } elseif ($flag === false) {
            $inquiry->rollback();
            $this->setCode('-101');
            $this->setMessage(L('FAIL') . $error);
            $this->jsonReturn();
        }
    }

}
