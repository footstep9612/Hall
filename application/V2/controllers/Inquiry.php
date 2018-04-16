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
        $quoteModel = new QuoteModel();
        $countryModel = new CountryModel();
        $employeeModel = new EmployeeModel();
        $buyerModel = new BuyerModel();
        $countryUserModel = new CountryUserModel();
        $org = new OrgModel();
        $marketAreaCountryModel = new MarketAreaCountryModel();
        $marketAreaModel = new MarketAreaModel();
        $transModeModel = new TransModeModel();
        $inquiryOrderModel = new InquiryOrderModel();

        // 市场经办人
        if ($condition['agent_name'] != '') {
            $condition['agent_id'] = $employeeModel->getUserIdByName($condition['agent_name']) ? : [];
        }
        
        // 报价人
        if ($condition['quote_name'] != '') {
            $condition['quote_id'] = $employeeModel->getUserIdByName($condition['quote_name']) ? : [];
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
        
        $condition['user_country'] = $countryUserModel->getUserCountry(['employee_id' => $this->user['id']]) ? : [];

        $inquiryList = $inquiryModel->getList_($condition);

        foreach ($inquiryList as &$inquiry) {
            $inquiry['country_name'] = $countryModel->getCountryNameByBn($inquiry['country_bn'], $this->lang);
            $inquiry['agent_name'] = $employeeModel->getUserNameById($inquiry['agent_id']);
            $inquiry['quote_name'] = $employeeModel->getUserNameById($inquiry['quote_id']);
            $inquiry['buyer_no'] = $buyerModel->where(['id' => $inquiry['buyer_id']])->getField('buyer_no');
            $inquiry['now_agent_name'] = $employeeModel->getUserNameById($inquiry['now_agent_id']);
            $inquiry['logi_quote_flag'] = $quoteModel->where(['inquiry_id' => $inquiry['id']])->getField('logi_quote_flag');
            $inquiry['created_name'] = $employeeModel->getUserNameById($inquiry['created_by']);
            $inquiry['obtain_name'] = $employeeModel->getUserNameById($inquiry['obtain_id']);
            $inquiry['org_name'] = $org->where(['id' => $inquiry['org_id'], 'deleted_flag' => 'N'])->getField('name');
            $inquiry['area_bn'] = $marketAreaCountryModel->where(['country_bn' => $inquiry['country_bn']])->getField('market_area_bn');
            $inquiry['area_name'] = $marketAreaModel->getAreaNameByBn($inquiry['area_bn'], $this->lang);
            $transMode = $transModeModel->field('bn, trans_mode')->where(['id' => $inquiry['trans_mode_bn'], 'deleted_flag' => 'N'])->find();
            $inquiry['trans_mode_bn'] = $transMode['bn'];
            $inquiry['trans_mode_name'] = $transMode['trans_mode'];
            $inquiry['contract_no'] = $inquiryOrderModel->where(['inquiry_id' => $inquiry['id']])->getField('contract_no');
        }

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
        $quoteModel = new QuoteModel();
        $countryModel = new CountryModel();
        $employeeModel = new EmployeeModel();
        $buyerModel = new BuyerModel();
        $countryUserModel = new CountryUserModel();
        $org = new OrgModel();
        $marketAreaCountryModel = new MarketAreaCountryModel();
        $marketAreaModel = new MarketAreaModel();
        $transModeModel = new TransModeModel();
        $inquiryOrderModel = new InquiryOrderModel();
        
        // 市场经办人
        if ($condition['agent_name'] != '') {
            $condition['agent_id'] = $employeeModel->getUserIdByName($condition['agent_name']) ? : [];
        }
        
        // 报价人
        if ($condition['quote_name'] != '') {
            $condition['quote_id'] = $employeeModel->getUserIdByName($condition['quote_name']) ? : [];
        }
        
        // 销售合同号
        if ($condition['contract_no'] != '') {
            $condition['contract_inquiry_id'] = $inquiryOrderModel->getInquiryIdForContractNo();
        }

        //区域和国家
        if ($condition['market_area_bn'] != '' && $condition['country_bn'] == '') {
            $condition['country_bn'] = $marketAreaCountryModel->getCountryBn($condition['market_area_bn']) ? : [];
        }

        // 是否显示列表
        $isShow = false;

        foreach ($this->user['role_no'] as $roleNo) {
            if ($condition['view_type'] == 'dept') {
                if ($roleNo == $inquiryModel::viewAllRole) {
                    $isShow = true;
                    break;
                }
                if ($roleNo == $inquiryModel::viewBizDeptRole) {
                    $isShow = true;
                    $condition['org_id'] = $inquiryModel->getDeptOrgId($this->user['group_id'], ['in', ['ub','erui']]);
                    break;
                }
            }
            
            if ($condition['view_type'] == 'country' && $roleNo == $inquiryModel::viewCountryRole) {
                $isShow = true;
                $condition['user_country'] = $countryUserModel->getUserCountry(['employee_id' => $this->user['id']]) ? : [];
                break;
            }
        }

        $inquiryList = [];

        if ($isShow) {
            $inquiryList = $inquiryModel->getViewList($condition);

            foreach ($inquiryList as &$inquiry) {
                $inquiry['country_name'] = $countryModel->getCountryNameByBn($inquiry['country_bn'], $this->lang);
                $inquiry['agent_name'] = $employeeModel->getUserNameById($inquiry['agent_id']);
                $inquiry['quote_name'] = $employeeModel->getUserNameById($inquiry['quote_id']);
                $inquiry['buyer_no'] = $buyerModel->where(['id' => $inquiry['buyer_id']])->getField('buyer_no');
                $inquiry['now_agent_name'] = $employeeModel->getUserNameById($inquiry['now_agent_id']);
                $inquiry['logi_quote_flag'] = $quoteModel->where(['inquiry_id' => $inquiry['id']])->getField('logi_quote_flag');
                $inquiry['created_name'] = $employeeModel->getUserNameById($inquiry['created_by']);
                $inquiry['obtain_name'] = $employeeModel->getUserNameById($inquiry['obtain_id']);
                $inquiry['org_name'] = $org->where(['id' => $inquiry['org_id'], 'deleted_flag' => 'N'])->getField('name');
                $inquiry['area_bn'] = $marketAreaCountryModel->where(['country_bn' => $inquiry['country_bn']])->getField('market_area_bn');
                $inquiry['area_name'] = $marketAreaModel->getAreaNameByBn($inquiry['area_bn'], $this->lang);
                $transMode = $transModeModel->field('bn, trans_mode')->where(['id' => $inquiry['trans_mode_bn'], 'deleted_flag' => 'N'])->find();
                $inquiry['trans_mode_bn'] = $transMode['bn'];
                $inquiry['trans_mode_name'] = $transMode['trans_mode'];
                $inquiry['contract_no'] = $inquiryOrderModel->where(['inquiry_id' => $inquiry['id']])->getField('contract_no');
            }
        }

        if ($inquiryList) {
            $res['code'] = 1;
            $res['message'] = L('SUCCESS');
            $res['data'] = $inquiryList;
            $res['count'] = $inquiryModel->getViewCount($condition);
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
                'now_agent_id' => $inquiryModel->getInquiryIssueUserId($condition['inquiry_id'], [$condition['org_id']], ['in', [$inquiryModel::inquiryIssueAuxiliaryRole, $inquiryModel::quoteIssueAuxiliaryRole]], ['in', [$inquiryModel::inquiryIssueRole, $inquiryModel::quoteIssueMainRole]], ['in', ['ub', 'erui']]),
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
        
        $data = $inquiryModel->getUserRoleByNo($this->user['role_no']);

        if ($data['is_agent'] == 'Y') {
            $orgModel = new OrgModel();

            $org = $orgModel->field('id, name, name_en, name_es, name_ru')->where(['id' => ['in', $this->user['group_id'] ?: ['-1']], 'org_node' => ['in', ['ub', 'erui']], 'deleted_flag' => 'N'])->order('id DESC')->find();

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

            $quoteId= $inquiryModel->where(['id' => $condition['inquiry_id']])->getField('quote_id');

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
    
            $agentId= $inquiryModel->where(['id' => $condition['inquiry_id']])->getField('agent_id');
    
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
    
            $inquiry = $inquiryModel->field('inflow_time, org_id, erui_id, quote_id, check_org_id, logi_org_id, logi_agent_id, logi_check_id')->where(['id' => $condition['inquiry_id']])->find();
            $inquiryCheckLog= $inquiryCheckLogModel->getDetail(['inquiry_id' => $condition['inquiry_id']], 'in_node, out_node');
            
            $error = false;
            if ($inquiryCheckLog['out_node'] == 'CLARIFY') {
                // 根据流入环节获取当前办理人
                switch ($inquiryCheckLog['in_node']) {
                    case 'BIZ_DISPATCHING' :
                        $nowAgentId = $inquiryModel->getInquiryIssueUserId($condition['inquiry_id'], [$inquiry['org_id']], ['in', [$inquiryModel::inquiryIssueAuxiliaryRole, $inquiryModel::quoteIssueAuxiliaryRole]], ['in', [$inquiryModel::inquiryIssueRole, $inquiryModel::quoteIssueMainRole]], ['in', ['ub', 'erui']]);
                        break;
                    case 'CC_DISPATCHING' :
                        $nowAgentId = $inquiryModel->getInquiryIssueUserId($condition['inquiry_id'], [$inquiry['erui_id']], $inquiryModel::inquiryIssueAuxiliaryRole, $inquiryModel::inquiryIssueRole, 'erui');
                        break;
                    case 'BIZ_QUOTING' :
                        $nowAgentId = $inquiry['quote_id'];
                        break;
                    case 'LOGI_DISPATCHING' :
                        $nowAgentId = $inquiryModel->getInquiryIssueUserId($condition['inquiry_id'], [$inquiry['logi_org_id']], $inquiryModel::logiIssueAuxiliaryRole, $inquiryModel::logiIssueMainRole, 'lg');
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
            } else $error = true;
            
            if ($error) jsonReturn('', '-101', L('INQUIRY_NODE_ERROR'));
            
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
            
            $where['inquiry_id'] = $condition['inquiry_id'];
            $where['_complex']['in_node'] = $where['_complex']['out_node'] = 'CLARIFY';
            $where['_complex']['_logic'] = 'or';
            
            $field = 'in_node, out_node, op_note, created_by, created_at';
            $clarifyList = $inquiryCheckLogModel->field($field)->where($where)->order('id ASC')->select();
        
            foreach ($clarifyList as &$clarify) {
                $clarify['created_name'] = $employeeModel->getUserNameById($clarify['created_by']);
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

        $where = $this->put_data;
        
        $inquiryStatus = $inquiry->getInquiryStatus();

        $results = $inquiry->getInfo($where);

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

        if (!empty($results['data'])) {
            $results['data']['status_name'] = $inquiryStatus[$results['data']['status']];
            $results['data']['agent_name'] = $results['data']['agent_name'] ? : L('NOTHING');
            $results['data']['obtain_name'] = $results['data']['obtain_name'] ? : L('NOTHING');
            $results['data']['current_name'] = $results['data']['current_name'] ? : L('NOTHING');
            $results['data']['quote_name'] = $results['data']['quote_name'] ? : L('NOTHING');
            $results['data']['logi_agent_name'] = $results['data']['logi_agent_name'] ? : L('NOTHING');
            $results['data']['dispatch_place'] = $results['data']['dispatch_place'] ? : L('NOTHING');
            $results['data']['inquiry_no'] = $results['data']['inquiry_no'] ? : L('NOTHING');
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

    /*public function addAction() {
        $inquiry = new InquiryModel();
        $data = $this->put_data;
        $data['agent_id'] = $this->user['id'];
        $data['created_by'] = $this->user['id'];

        $results = $inquiry->addData($data);
        $this->jsonReturn($results);
    }*/

    /*
     * 修改询价单
     * Author:张玉良
     */

    public function updateAction() {
        $inquiry = new InquiryModel();
        $data = $this->put_data;
        $data['inquiry_no'] = $data['inquiry_no'] == L('NOTHING') ? null : $data['inquiry_no'];
        $data['dispatch_place'] = $data['dispatch_place'] == L('NOTHING') ? null : $data['dispatch_place'];
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

        if ($data['status'] == 'BIZ_DISPATCHING') {
            $data['now_agent_id'] = $inquiry->getInquiryIssueUserId($data['id'], [$data['org_id']], ['in', [$inquiry::inquiryIssueAuxiliaryRole, $inquiry::quoteIssueAuxiliaryRole]], ['in', [$inquiry::inquiryIssueRole, $inquiry::quoteIssueMainRole]], ['in', ['ub', 'erui']]);
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
                if(!empty($val['attach_name'])){
                    $results['data'][$key]['attach_url'] = $val['attach_url'].'?filename='.$val['attach_name'];
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
        $this->jsonReturn($results);
    }

    /*
     * 批量修改询单sku
     * Author:张玉良
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
                if (isset($condition['qty']) && !isDecimal($condition['qty'])) {
                    $condition['qty'] = 1;
                }
                if ($condition['id'] == '') {
                    $condition['inquiry_id'] = $data['id'];
                    $condition['created_by'] = $this->user['id'];
                    $results = $Item->addData($condition);
                } else {
                    $condition['updated_by'] = $this->user['id'];
                    $results = $Item->updateData($condition);
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

    public function cleanInquiryRemind($inquiry_id){

        $inquiryModel = new InquiryModel();
        $inquiryModel->where(['id'=>$inquiry_id])->save(['remind'=>0]);

        //提醒详情标为已读
        $inquiryRemind = new InquiryRemindModel();
        $inquiryRemind->where(['inquiry_id'=>$inquiry_id])->save([
            'isread_flag' => 'Y',
            'updated_at'  => date('Y-m-d H:i:s')
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
        $inquiryInfo = $inquiryModel->where(['id'=>$data['inquiry_id']])->field('now_agent_id,serial_no')->find();

        $employeeModel = new EmployeeModel();
        $receiverInfo = $employeeModel->where(['id'=>$inquiryInfo['now_agent_id']])->field('name,mobile,email')->find();

        //QUOTE_SENT-报价单已发出 INQUIRY_CLOSED-报价关闭 状态下不发送短信
        if( !in_array($data['out_node'],['QUOTE_SENT','INQUIRY_CLOSED'])){

            $this->sendSms($receiverInfo['mobile'],$data['action'],$receiverInfo['name'],$inquiryInfo['serial_no'],$this->user['name'],$data['in_node'],$data['out_node']);

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
}
