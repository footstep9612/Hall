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

    /**
     * 验证用户权限
     * Author:张玉良
     * @return string
     */
    /* public function checkAuthAction() {
      $groupid = $this->user['group_id'];
      if (isset($groupid)) {
      $maketareateam = new MarketAreaTeamModel();
      $users = [];

      if(is_array($groupid)){
      //查询是否方案中心，下面有多少市场人员
      $users = $maketareateam->alias('a')
      ->field('b.employee_id')
      ->join('`erui_sys`.`org_member` b on a.market_org_id = b.org_id')
      ->where('a.biz_tech_org_id in('.implode(',',$groupid).')')
      ->select();

      //查询是否是市场人员
      $agent = $maketareateam->where('market_org_id in('.implode(',',$groupid).')')->count('id');
      }else{
      //查询是否方案中心，下面有多少市场人员
      $users = $maketareateam->alias('a')
      ->field('b.employee_id')
      ->join('`erui_sys`.`org_member` b on a.market_org_id = b.org_id')
      ->where('a.biz_tech_org_id='.$groupid)
      ->select();

      //查询是否是市场人员
      $agent = $maketareateam->where('market_org_id='.$groupid)->count('id');
      }

      if (!empty($users)) {
      array_unique($users);
      $results['code'] = '1';
      $results['message'] = '方案中心！';
      $results['data'] = $users;
      } else if ($agent>0) {
      $results['code'] = '2';
      $results['message'] = '市场人员！';
      } else {
      $results['code'] = '3';
      $results['message'] = '其他人员！';
      }
      } else {
      $results['code'] = '-101';
      $results['message'] = '用户没有权限此操作！';
      }

      return $results;
      } */

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

    /*
     * 询价单列表
     * Author:张玉良
     */

    /* public function getListAction() {
      $auth = $this->checkAuthAction();
      //判断是否有权限访问
      if ($auth['code'] == '-101') {
      $this->jsonReturn($auth);
      }

      $inquiry = new InquiryModel();
      $employee = new EmployeeModel();
      $country = new CountryModel();
      $where = $this->put_data;

      $where['agent_id'] = [];   //经办人为自己
      //如果有方案中心权限
      if ($auth['code'] == 1) {
      foreach ($auth['data'] as $epl) {
      $where['agent_id'][] = $epl['employee_id'];
      }
      }

      //如果搜索条件有经办人，转换成id
      if (!empty($where['agent_name'])) {
      $agent = $employee->field('id')->where('name="' . $where['agent_name'] . '"')->find();

      if (in_array($agent['id'], $where['agent_id']) || $agent['id'] == $this->user['id']) {
      $where['agent_id'] = [];
      $where['agent_id'][] = $agent['id'];
      } else {
      $results['code'] = '-101';
      $results['message'] = '没有找到相关信息！';
      $this->jsonReturn($results);
      }
      }else{
      $where['user_id'] = $this->user['id'];
      }
      //如果搜索条件有项目经理，转换成id
      if (!empty($where['pm_name'])) {
      $pm = $employee->field('id')->where('name="' . $where['pm_name'] . '"')->find();
      if ($pm) {
      $where['pm_id'] = $pm['id'];
      } else {
      $results['code'] = '-101';
      $results['message'] = '没有找到相关信息！';
      $this->jsonReturn($results);
      }
      }

      $results = $inquiry->getList($where);

      //把经办人和项目经理转换成名称显示
      if ($results['code'] == '1') {
      $buyer = new BuyerModel();
      foreach ($results['data'] as $key => $val) {
      //经办人
      if (!empty($val['agent_id'])) {
      $rs1 = $employee->field('name')->where('id=' . $val['agent_id'])->find();
      $results['data'][$key]['agent_name'] = $rs1['name'];
      }
      //项目经理
      if (!empty($val['pm_id'])) {
      $rs2 = $employee->field('name')->where('id=' . $val['pm_id'])->find();
      $results['data'][$key]['pm_name'] = $rs2['name'];
      }
      //国家
      if (!empty($val['country_bn'])) {
      $rs3 = $country->field('name')->where("lang='zh' and bn='" . $val['country_bn'] . "'")->find();
      $results['data'][$key]['country_name'] = $rs3['name'];
      }
      //区域
      if(!empty($val['buyer_id'])){
      $rs4 = $buyer->field('area_bn')->where("id=" . $val['buyer_id'])->find();
      $results['data'][$key]['area_bn'] = $rs3['area_bn'];
      }
      }

      //权限
      $results['auth'] = $auth['code'];
      }

      $this->jsonReturn($results);
      } */

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
        if ($condition['quote_name'] != '') {
            $condition['quote_id'] = $employeeModel->getUserIdByName($condition['quote_name']) ?: [];
        }

// 销售合同号
        if ($condition['contract_no'] != '') {
            $condition['contract_inquiry_id'] = $inquiryOrderModel->getInquiryIdForContractNo();
        }

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
        $this->_setUserName($inquiryList, ['agent_name' => 'agent_id', 'quote_name' => 'quote_id',
            'now_agent_name' => 'now_agent_id', 'created_name' => 'created_by', 'obtain_name' => 'obtain_id']);
        $this->_setBuyerNo($inquiryList);
        $this->_setLogiQuoteFlag($inquiryList);
        $this->_setOrgName($inquiryList);
        $this->_setTransModeName($inquiryList);
        $this->_setContractNo($inquiryList);
//        foreach ($inquiryList as &$inquiry) {
//            $inquiry['buyer_no'] = $buyerModel->where(['id' => $inquiry['buyer_id']])->getField('buyer_no');
//            $inquiry['logi_quote_flag'] = $quoteModel->where(['inquiry_id' => $inquiry['id']])->getField('logi_quote_flag');
//            $inquiry['org_name'] = $org->where(['id' => $inquiry['org_id'], 'deleted_flag' => 'N'])->getField('name');
//            $inquiry['trans_mode_name'] = $transModeModel->getTransModeByBn($inquiry['trans_mode_bn'], $this->lang);
//            $inquiry['contract_no'] = $inquiryOrderModel->where(['inquiry_id' => $inquiry['id']])->getField('contract_no');
//        }

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
        $this->_setUserName($inquiryList, ['agent_name' => 'agent_id', 'quote_name' => 'quote_id',
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

            $res = $inquiryModel->updateData($data);

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
//        if ($data['is_erui'] == 'N' && !empty($this->user['group_id'])) {
//            $data['is_erui'] = $org_model->getIsEruiById(['in', $this->user['group_id']]);
//        }
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

            $res = $inquiryModel->updateData($data);

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

            $res = $inquiryModel->updateData($data);

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

// 更改报价单状态
            $quoteData = [
                'status' => 'BIZ_QUOTING',
                'updated_by' => $this->user['id'],
                'updated_at' => $this->time
            ];
            $res2 = $quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->save($quoteData);

// 更改市场报价单状态
            $res3 = $finalQuoteModel->updateFinal(['inquiry_id' => $condition['inquiry_id'], 'status' => 'BIZ_QUOTING', 'updated_by' => $this->user['id']]);

            if ($res1['code'] == 1 && $res2 && $res3['code'] == 1) {
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
     * @desc 项目澄清
     *
     * @author liujf
     * @time 2018-01-15
     */
    public function projectClarifyAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $inquiryModel = new InquiryModel();

            $agentId = $inquiryModel->where(['id' => $condition['inquiry_id']])->getField('agent_id');

            $data = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $agentId,
                'status' => 'CLARIFY',
                'updated_by' => $this->user['id']
            ];

            $res = $inquiryModel->updateData($data);

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
            } else
                $error = true;

            if ($error)
                jsonReturn('', '-101', L('INQUIRY_NODE_ERROR'));

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
//经办人
        if (!empty($results['data']['agent_id'])) {
            $results['data']['agent_name'] = $employee->getUserNameById($results['data']['agent_id']);
        }
//客户中心分单员
        if (!empty($results['data']['erui_id'])) {
            $results['data']['erui_name'] = $employee->getUserNameById($results['data']['erui_id']);
        }
//询单创建人
        if (!empty($results['data']['created_by'])) {
            $results['data']['created_name'] = $employee->getUserNameById($results['data']['created_by']);
        }
//事业部报价人
        if (!empty($results['data']['quote_id'])) {
            $results['data']['quote_name'] = $employee->getUserNameById($results['data']['quote_id']);
        }
//事业部
        if (!empty($results['data']['org_id'])) {
            $results['data']['org_name'] = $org->where(['id' => $results['data']['org_id'], 'deleted_flag' => 'N'])->getField('name');
        }
//事业部审核人
        if (!empty($results['data']['check_org_id'])) {
            $results['data']['check_org_name'] = $employee->getUserNameById($results['data']['check_org_id']);
        }
//物流报价人
        if (!empty($results['data']['logi_agent_id'])) {
            $results['data']['logi_agent_name'] = $employee->getUserNameById($results['data']['logi_agent_id']);
        }
//物流审核人
        if (!empty($results['data']['logi_check_id'])) {
            $results['data']['logi_check_name'] = $employee->getUserNameById($results['data']['logi_check_id']);
        }
//当前办理人
        if (!empty($results['data']['now_agent_id'])) {
            $results['data']['current_name'] = $employee->getUserNameById($results['data']['now_agent_id']);
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
        if (!empty($results['data']['obtain_id'])) {
            $results['data']['obtain_name'] = $employee->getUserNameById($results['data']['obtain_id']);
        }
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
        }
        if ($data['status'] == 'BIZ_DISPATCHING') {
            $data['now_agent_id'] = $inquiry->getInquiryIssueUserId($data['id'], [$data['org_id']], ['in', [$inquiry::inquiryIssueAuxiliaryRole, $inquiry::quoteIssueAuxiliaryRole]], ['in', [$inquiry::inquiryIssueRole, $inquiry::quoteIssueMainRole]], ['in', ['ub', 'eub', 'erui']]);
        }

        $results = $inquiry->updateStatus($data);
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
            $actions = isset($condition['action']) ? explode(',', $condition['action']) : '';
            unset($condition['action']);
            $res = $inquiryCheckLogModel->getDetail($condition);


            if ($actions && !empty($res['action']) && !in_array($res['action'], $actions)) {
                $res = [];
            } elseif (empty($res)) {
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
                            $nowAgentIds = (new InquiryModel())
                                    ->getInquiryIssueUserIds($condition['inquiry_id'], [$inquiry['org_id']], ['in', [InquiryModel::inquiryIssueAuxiliaryRole,
                                    InquiryModel::quoteIssueAuxiliaryRole]], ['in', [InquiryModel::inquiryIssueRole,
                                    InquiryModel::quoteIssueMainRole]], ['in', ['ub', 'erui', 'eub']]);

                            !in_array(UID, $nowAgentIds) ? $res = [] : '';
                            break;
                        case 'CC_DISPATCHING'://易瑞客户中心
                            $inquiry_model = new InquiryModel();
                            $nowAgentIds = $inquiry_model
                                    ->getInquiryIssueUserIds($condition['inquiry_id'], [$inquiry['erui_id']], InquiryModel::inquiryIssueAuxiliaryRole, InquiryModel::inquiryIssueRole, 'erui');


                            !in_array(UID, $nowAgentIds) ? $res = [] : '';
                            break;
                        case 'BIZ_QUOTING'://事业部报价
                            $inquiry['quote_id'] != UID ? $res = [] : '';
                            break;
                        case 'REJECT_QUOTING'://事业部审核退回事业部报价
                            $inquiry['quote_id'] != UID ? $res = [] : '';
                            break;
                        case 'LOGI_DISPATCHING'://物流分单员
                            $nowAgentIds = (new InquiryModel())
                                    ->getInquiryIssueUserIds($condition['inquiry_id'], [$inquiry['logi_org_id']], InquiryModel::logiIssueAuxiliaryRole, InquiryMode::logiIssueMainRole, ['in', ['lg', 'elg']]);

                            !in_array(UID, $nowAgentIds) ? $res = [] : '';
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
                    $list = $marketareateam->field('market_org_id')->group('market_org_id')->select();
                    if ($list) {
                        foreach ($list as $lt) {
                            if (!empty($lt['market_org_id'])) {
                                $test1[] = $lt['market_org_id'];
                            }
                        }
                        $data['market_org'] = implode(',', $test1);
                    }
                }
                if ($val == 2) {//所有方案中心群组
                    $list = $marketareateam->field('biz_tech_org_id')->group('biz_tech_org_id')->select();
                    if ($list) {
                        foreach ($list as $lt) {
                            if (!empty($lt['biz_tech_org_id'])) {
                                $test2[] = $lt['biz_tech_org_id'];
                            }
                        }
                        $data['biz_tech_org'] = implode(',', $test2);
                    }
                }
                if ($val == 3) {//所有产品线群组
                    $list = $bizlinegroup->field('group_id')->group('group_id')->select();
                    if ($list) {
                        foreach ($list as $lt) {
                            if (!empty($lt['group_id'])) {
                                $test3[] = $lt['group_id'];
                            }
                        }
                        $data['biz_group_org'] = implode(',', $test3);
                    }
                }
                if ($val == 4) {//所有物流报价群组
                    $list = $marketareateam->field('logi_quote_org_id')->group('logi_quote_org_id')->select();
                    if ($list) {
                        foreach ($list as $lt) {
                            if (!empty($lt['logi_quote_org_id'])) {
                                $test4[] = $lt['logi_quote_org_id'];
                            }
                        }
                        $data['logi_quote_org'] = implode(',', $test4);
                    }
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
        $inquiryInfo = $inquiryModel->where(['id' => $data['inquiry_id']])->field('now_agent_id,serial_no')->find();

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
                    $this->sendSms($user['mobile'], $data['action'], $user['name'], $inquiryInfo['serial_no'], $user['name'], $data['in_node'], $data['out_node']);
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

    private function _setUserName(&$arr, $fileds) {
        if ($arr) {
            $employee_model = new EmployeeModel();
            $userids = [];
            foreach ($arr as $key => $val) {
                foreach ($fileds as $filed) {
                    if (isset($val[$filed]) && $val[$filed]) {
                        $userids[] = $val[$filed];
                    }
                }
            }
            $usernames = $employee_model->getUserNamesByUserids($userids);
            foreach ($arr as $key => $val) {
                foreach ($fileds as $filed_key => $filed) {
                    if ($val[$filed] && isset($usernames[$val[$filed]])) {
                        $val[$filed_key] = $usernames[$val[$filed]];
                    } else {
                        $val[$filed_key] = '';
                    }
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
                $contract_nos[$inquiry_order['inquiry_id']] = $inquiry_order['logi_quote_flag'];
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

    /* 询单关闭后2个月，通过待办对市场人员进行提醒，同时在询单管理页面置顶。成单、失单分析填写完成后该询单报价完成
     *
     */

    public function SucOrFailReasonAction() {
        $data = $this->getPut();

        if (empty($data['inquiry_id']) || empty($data['loss_rfq_flag']) || empty($data['loss_rfq_reason'])) {
            $this->setCode('-103');
            $this->setMessage(L('MISSING_PARAMETER'));
            $this->jsonReturn();
        } else {
            $data['loss_rfq_flag'] = $data['loss_rfq_flag'] == 'Y' ? 'Y' : 'N';
            $inquiry_model = new InquiryModel();
            $create_data = $inquiry_model->create(['loss_rfq_flag' => $data['loss_rfq_flag'] == 'Y' ?
                'Y' : ($data['loss_rfq_flag'] == 'N' ? 'N' : null),
                'loss_rfq_reason' => $data['loss_rfq_reason'],
                'loss_rfq_reason_analysis' => $data['loss_rfq_reason_analysis'],
            ]);
            $ret = false;
            if ($create_data) {
                $ret = $inquiry_model->where(['id' => $data['inquiry_id']])
                        ->save($create_data);
            }
            if ($ret !== false) {
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
            } else {
                $results['code'] = '-101';
                $results['message'] = L('FAIL');
            }
            $this->jsonReturn($results);
        }
    }

}
