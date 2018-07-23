<?php

/*
 * @desc 物流报价控制器
 *
 * @author liujf
 * @time 2017-08-02
 */

class LogisticsController extends PublicController {

    public function init() {
        parent::init();

        $this->inquiryModel = new InquiryModel();
        $this->quoteModel = new QuoteModel();
        $this->quoteItemModel = new QuoteItemModel();
        $this->quoteLogiFeeModel = new QuoteLogiFeeModel();
        $this->quoteItemLogiModel = new QuoteItemLogiModel();
        $this->userModel = new UserModel();
        $this->inquiryCheckLogModel = new InquiryCheckLogModel();
        $this->quoteLogiQwvModel = new QuoteLogiQwvModel();
        $this->marketAreaTeamModel = new MarketAreaTeamModel();
        $this->orgMemberModel = new OrgMemberModel();

        $this->time = date('Y-m-d H:i:s');
    }

    /**
     * @desc 获取报价单项物流报价列表接口
     *
     * @author liujf
     * @time 2017-08-02
     */
    public function getQuoteItemLogiListAction() {
        $condition = $this->put_data;

        if (empty($condition['inquiry_id']))
            $this->jsonReturn(false);

        $data = $this->quoteItemLogiModel->getJoinList($condition);

        $this->_handleList($this->quoteItemLogiModel, $data, $condition, true);
    }

    /**
     * @desc 获取报价单项物流报价接口
     *
     * @author liujf
     * @time 2017-08-02
     */
    public function getQuoteItemLogiDetailAction() {
        $condition = $this->put_data;

        if (!empty($condition['r_id'])) {
            $condition['id'] = $condition['r_id'];
            unset($condition['r_id']);
            $res = $this->quoteItemLogiModel->getJoinDetail($condition);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 修改报价单项物流报价信息接口
     *
     * @author liujf
     * @time 2017-08-08
     */
    public function updateQuoteItemLogiInfoAction() {
        $condition = $this->put_data;

        if (!empty($condition['items'])) {

            $flag = true;
            $data = [];

            //$this->quoteItemLogiModel->startTrans();

            foreach ($condition['items'] as $item) {
                $where['id'] = $item['id'];
                unset($item['id']);

                $item['updated_by'] = $this->user['id'];
                $item['updated_at'] = $this->time;

                $res = $this->quoteItemLogiModel->updateInfo($where, $item);

                /* if (!$res) {
                  $this->quoteItemLogiModel->rollback();
                  $flag = false;
                  break;
                  } */

                if (!$res) {
                    $data[] = $where['id'];
                    $flag = false;
                }
            }

            // if ($flag) $this->quoteItemLogiModel->commit();

            if ($flag) {
                $this->jsonReturn($flag);
            } else {
                $this->setCode('-101');
                $this->setMessage('失败!');
                parent::jsonReturn($data);
            }
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 获取报价单列表接口
     *
     * @author liujf
     * @time 2017-08-07
     */
    /* public function getQuoteLogiListAction() {
      $condition = $this->put_data;

      if (!empty($condition['agent_name'])) {
      $agent = $this->userModel->where(['name' => $condition['agent_name']])->find();
      $condition['agent_id'] = $agent['id'];
      }

      if (!empty($condition['pm_name'])) {
      $pm = $this->userModel->where(['name' => $condition['pm_name']])->find();
      $condition['pm_id'] = $pm['id'];
      }

      $isGroup = ['in', $this->user['group_id']];

      $where['_complex'] = [
      'logi_check_org_id' => $isGroup,
      'logi_quote_org_id' => $isGroup,
      '_logic' => 'or',
      ];

      $res1 = $this->marketAreaTeamModel->where(['logi_check_org_id' => $isGroup])->find();

      $res2 = $this->marketAreaTeamModel->where(['logi_quote_org_id' => $isGroup])->find();

      $marketAreaTeamList = $this->marketAreaTeamModel->where($where)->select();

      $marketOrgArr = [];

      foreach ($marketAreaTeamList as $marketAreaTeam) {
      $marketOrgArr[] = $marketAreaTeam['market_org_id'];
      }

      $orgMemberList = $this->orgMemberModel->getList(['org_id' => array_unique($marketOrgArr)]);

      $employeeArr = [];

      foreach ($orgMemberList as $orgMember) {
      $employeeArr[] = $orgMember['employee_id'];
      }

      $condition['market_agent_id'] = array_unique($employeeArr) ? : ['-1'];

      $condition['user_id'] = $this->user['id'];

      $quoteLogiFeeList = $this->quoteLogiFeeModel->getJoinList($condition);

      foreach ($quoteLogiFeeList as &$quoteLogiFee) {
      $userAgent = $this->userModel->info($quoteLogiFee['agent_id']);
      $userPm = $this->userModel->info($quoteLogiFee['pm_id']);
      $quoteLogiFee['agent_name'] = $userAgent['name'];
      $quoteLogiFee['pm_name'] = $userPm['name'];
      }

      if ($quoteLogiFeeList) {
      $res['code'] = 1;
      $res['message'] = '成功!';
      $res['data'] = $quoteLogiFeeList;
      $res['is_checker'] = $res1 ? 'Y' : 'N';
      $res['is_quoter'] = $res2 ? 'Y' : 'N';
      $res['count'] = $this->quoteLogiFeeModel->getJoinCount($condition);
      $this->jsonReturn($res);
      } else {
      $this->jsonReturn(false);
      }
      } */

    /**
     * @desc 获取报价单物流费用详情接口
     *
     * @author liujf
     * @time 2017-08-03
     */
    public function getQuoteLogiFeeDetailAction($condition) {

        if (!empty($condition['quote_id']) || !empty($condition['inquiry_id'])) {

            $quoteLogiFeeModel = new QuoteLogiFeeModel();
            $quoteLogiFee = $quoteLogiFeeModel->getJoinDetail($condition);

            if ($quoteLogiFee) {
                $quoteLogiFee['land_freight_usd'] = round($quoteLogiFee['land_freight'] / $this->_getRateUSD($quoteLogiFee['land_freight_cur']), 8);
                $quoteLogiFee['port_surcharge_usd'] = round($quoteLogiFee['port_surcharge'] / $this->_getRateUSD($quoteLogiFee['port_surcharge_cur']), 8);
                $quoteLogiFee['inspection_fee_usd'] = round($quoteLogiFee['inspection_fee'] / $this->_getRateUSD($quoteLogiFee['inspection_fee_cur']), 8);
                $quoteLogiFee['inter_shipping_usd'] = round($quoteLogiFee['inter_shipping'] / $this->_getRateUSD($quoteLogiFee['inter_shipping_cur']), 8);

                $quoteLogiFee['dest_delivery_fee_usd'] = round($quoteLogiFee['dest_delivery_fee'] / $this->_getRateUSD($quoteLogiFee['dest_delivery_fee_cur']), 8);
                $quoteLogiFee['dest_clearance_fee_usd'] = round($quoteLogiFee['dest_clearance_fee'] / $this->_getRateUSD($quoteLogiFee['dest_clearance_fee_cur']), 8);

                $overlandInsuFee = $this->_getOverlandInsuFee($quoteLogiFee['total_exw_price'], $quoteLogiFee['overland_insu_rate']);
                $quoteLogiFee['overland_insu'] = $overlandInsuFee['CNY'];
                $shippingInsuFee = $this->_getShippingInsuFee($quoteLogiFee['total_exw_price'], $quoteLogiFee['shipping_insu_rate']);
                $quoteLogiFee['shipping_insu'] = $shippingInsuFee['CNY'];

                $tmpTotalFee = $quoteLogiFee['total_exw_price'] + $quoteLogiFee['land_freight_usd'] + $overlandInsuFee['USD'] + $quoteLogiFee['port_surcharge_usd'] + $quoteLogiFee['inspection_fee_usd'] + $quoteLogiFee['inter_shipping_usd'];

                $quoteLogiFee['dest_tariff_fee'] = round($tmpTotalFee * $quoteLogiFee['dest_tariff_rate'] / 100, 8);
                $quoteLogiFee['dest_va_tax_fee'] = round($tmpTotalFee * (1 + $quoteLogiFee['dest_tariff_rate'] / 100) * $quoteLogiFee['dest_va_tax_rate'] / 100, 8);

                $countryModel = New CountryModel();
                $quoteLogiFee['trans_mode_bn'] = $quoteLogiFee['trans_mode_bn'] ?: '暂无';
                $quoteLogiFee['package_mode'] = $quoteLogiFee['package_mode'] ?: '暂无';
                $quoteLogiFee['dispatch_place'] = $quoteLogiFee['dispatch_place'] ?: '暂无';
                if (empty($quoteLogiFee['from_country'])) {
                    $quoteLogiFee['from_country'] = '暂无'; //如果是空值赋值暂无
                } else {
                    $from_country_name = $countryModel->field('name')->where(['bn' => $quoteLogiFee['from_country'], 'lang' => 'zh', 'deleted_flag' => 'N'])->find();
                    $quoteLogiFee['from_country'] = $from_country_name['name']; //否则改成中文
                }
                $quoteLogiFee['from_port'] = $quoteLogiFee['from_port'] ?: '暂无';
                if (empty($quoteLogiFee['to_country'])) {
                    $quoteLogiFee['to_country'] = '暂无'; //如果是空值赋值暂无
                } else {
                    $from_country_name = $countryModel->field('name')->where(['bn' => $quoteLogiFee['to_country'], 'lang' => 'zh', 'deleted_flag' => 'N'])->find();
                    $quoteLogiFee['to_country'] = $from_country_name['name']; //否则改成中文
                }
                $quoteLogiFee['to_port'] = $quoteLogiFee['to_port'] ?: '暂无';
                $quoteLogiFee['delivery_addr'] = $quoteLogiFee['delivery_addr'] ?: '暂无';
                $quoteLogiFee['logi_trans_mode_bn'] = $quoteLogiFee['logi_trans_mode_bn'] ?: '暂无';
                $quoteLogiFee['logi_from_port'] = $quoteLogiFee['logi_from_port'] ?: '暂无';
                $quoteLogiFee['logi_to_port'] = $quoteLogiFee['logi_to_port'] ?: '暂无';
            }

            return $quoteLogiFee;
        } else {
            return false;
        }
    }

    /**
     * @desc 修改报价单物流费用信息接口
     *
     * @author liujf
     * @time 2017-08-10
     */
    public function updateQuoteLogiFeeInfoAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {

            $data = $condition;

            unset($data['from_port']);
            unset($data['to_port']);
            unset($data['trans_mode_bn']);
            unset($data['box_type_bn']);
            unset($data['quote_remarks']);

            $where['inquiry_id'] = $condition['inquiry_id'];

            $quoteLogiFee = $this->quoteLogiFeeModel->getDetail($where);
            $data['premium_rate'] = $quoteLogiFee['premium_rate'];

            $quote = $this->quoteModel->where($where)->find();
            $data['trade_terms_bn'] = $quote['trade_terms_bn'];
            $data['payment_period'] = $quote['payment_period'];
            $data['fund_occupation_rate'] = $quote['fund_occupation_rate'];
            $data['bank_interest'] = $quote['bank_interest'];
            $data['total_exw_price'] = $quote['total_exw_price'];

            $data = $this->calcuTotalLogiFee($data);

            //if ($quoteLogiFee['logi_agent_id'] == '') {
            //    $data['logi_agent_id'] = $this->user['id'];
            //}

            if ($quoteLogiFee['logi_from_port'] != $condition['logi_from_port'])
                $data['logi_from_port'] = $condition['logi_from_port'];
            if ($quoteLogiFee['logi_to_port'] != $condition['logi_to_port'])
                $data['logi_to_port'] = $condition['logi_to_port'];
            if ($quoteLogiFee['logi_trans_mode_bn'] != $condition['logi_trans_mode_bn'])
                $data['logi_trans_mode_bn'] = $condition['logi_trans_mode_bn'];
            if ($quoteLogiFee['logi_box_type_bn'] != $condition['logi_box_type_bn'])
                $data['logi_box_type_bn'] = $condition['logi_box_type_bn'];

            $data['updated_by'] = $this->user['id'];
            $data['updated_at'] = $this->time;

            $this->quoteLogiFeeModel->startTrans();
            $res1 = $this->quoteLogiFeeModel->updateInfo($where, $data);

            $quoteData = [];

            if ($quote['quote_remarks'] != $condition['quote_remarks'])
                $quoteData['quote_remarks'] = $condition['quote_remarks'];

            if ($data['total_logi_fee'] != $quote['total_logi_fee'])
                $quoteData['total_logi_fee'] = $data['total_logi_fee'];
            if ($data['total_quote_price'] != $quote['total_quote_price'])
                $quoteData['total_quote_price'] = $data['total_quote_price'];
            if ($data['total_bank_fee'] != $quote['total_bank_fee'])
                $quoteData['total_bank_fee'] = $data['total_bank_fee'];
            if ($data['total_insu_fee'] != $quote['total_insu_fee'])
                $quoteData['total_insu_fee'] = $data['total_insu_fee'];

            if ($quoteData) {
                $this->quoteModel->startTrans();
                $res2 = $this->quoteModel->where($where)->save($quoteData);
            }

            $quoteItemList = $this->quoteItemModel->where($where)->select();

            $res3 = true;
            $this->quoteItemModel->startTrans();
            foreach ($quoteItemList as $quoteItem) {
                $quoteUnitPrice = $data['total_exw_price'] > 0 ? round($data['total_quote_price'] * $quoteItem['exw_unit_price'] / $data['total_exw_price'], 8) : 0;

                if ($quoteItem['quote_unit_price'] != $quoteUnitPrice) {
                    $tmpRes = $this->quoteItemModel->where(['id' => $quoteItem['id']])->save(['quote_unit_price' => $quoteUnitPrice]);
                    if (!$tmpRes) {
                        $res3 = false;
                        break;
                    }
                }
            }

            if (isset($res2)) {
                if ($res1 && $res2 && $res3) {
                    $this->quoteLogiFeeModel->commit();
                    $this->quoteModel->commit();
                    $this->quoteItemModel->commit();
                    $res = true;
                } else {
                    $this->quoteLogiFeeModel->rollback();
                    $this->quoteModel->rollback();
                    $this->quoteItemModel->rollback();
                    $res = false;
                }
            } else {
                if ($res1 && $res3) {
                    $this->quoteLogiFeeModel->commit();
                    $this->quoteItemModel->commit();
                    $res = true;
                } else {
                    $this->quoteLogiFeeModel->rollback();
                    $this->quoteItemModel->rollback();
                    $res = false;
                }
            }

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 获取物流报价件重尺列表接口
     *
     * @author liujf
     * @time 2017-08-17
     */
    public function getQuoteLogiQwvListAction() {
        $condition = $this->put_data;

        if (empty($condition['inquiry_id']))
            $this->jsonReturn(false);

        $data = $this->quoteLogiQwvModel->getList($condition);

        $this->_handleList($this->quoteLogiQwvModel, $data, $condition);
    }

    /**
     * @desc 新增物流报价件重尺记录接口
     *
     * @author liujf
     * @time 2017-08-17
     */
    public function addQuoteLogiQwvRecordAction() {
        $condition = $this->put_data;

        if (empty($condition['inquiry_id']))
            $this->jsonReturn(false);

        $volumn = $condition['length'] * $condition['width'] * $condition['height'];
        $condition['volumn'] = $volumn > 0 ? $volumn : 0;

        $condition['created_by'] = $this->user['id'];
        $condition['created_at'] = $this->time;
        $condition['updated_by'] = $this->user['id'];
        $condition['updated_at'] = $this->time;

        $res = $this->quoteLogiQwvModel->addRecord($condition);

        $this->jsonReturn($res);
    }

    /**
     * @desc 修改物流报价件重尺信息接口
     *
     * @author liujf
     * @time 2017-08-17
     */
    public function updateQuoteLogiQwvInfoAction() {
        $condition = $this->put_data;

        if (!empty($condition['r_id'])) {
            $where['id'] = $condition['r_id'];
            unset($condition['r_id']);

            $volumn = $condition['length'] * $condition['width'] * $condition['height'];
            $condition['volumn'] = $volumn > 0 ? $volumn : 0;

            $condition['updated_by'] = $this->user['id'];
            $condition['updated_at'] = $this->time;

            $res = $this->quoteLogiQwvModel->updateInfo($where, $condition);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 批量修改物流报价件重尺信息接口
     *
     * @author liujf
     * @time 2017-08-21
     */
    public function batchUpdateQuoteLogiQwvInfoAction() {
        $condition = $this->put_data;

        if (!empty($condition['items'])) {

            $flag = true;
            $data = [];

            foreach ($condition['items'] as $item) {
                $where['id'] = $item['id'];
                unset($item['id']);

                $volumn = $item['length'] * $item['width'] * $item['height'];
                $item['volumn'] = $volumn > 0 ? $volumn : 0;

                $item['updated_by'] = $this->user['id'];
                $item['updated_at'] = $this->time;

                $res = $this->quoteLogiQwvModel->updateInfo($where, $item);

                if (!$res) {
                    $data[] = $where['id'];
                    $flag = false;
                }
            }

            if ($flag) {
                $this->jsonReturn($flag);
            } else {
                $this->setCode('-101');
                $this->setMessage('失败!');
                parent::jsonReturn($data);
            }
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 删除物流报价件重尺记录接口
     *
     * @author liujf
     * @time 2017-08-17
     */
    public function delQuoteLogiQwvRecordAction() {
        $condition = $this->put_data;

        if (!empty($condition['r_id'])) {
            $res = $this->quoteLogiQwvModel->delRecord($condition);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 分配物流报价人接口
     *
     * @author liujf
     * @time 2017-08-03
     */
    public function assignLogiAgentAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id']) && !empty($condition['logi_agent_id'])) {
            /* $where['inquiry_id'] = $condition['inquiry_id'];

              $data = [
              'logi_agent_id' => $condition['logi_agent_id'],
              'updated_at' => $this->time
              ];

              $res = $this->quoteLogiFeeModel->updateInfo($where, $data); */

            $this->inquiryModel->startTrans();
            $this->quoteModel->startTrans();

            $data = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $condition['logi_agent_id'],
                'logi_agent_id' => $condition['logi_agent_id'],
                'status' => 'LOGI_QUOTING',
                'updated_by' => $this->user['id']
            ];

            $res1 = $this->inquiryModel->updateData($data);

            // 更改报价单状态
            $res2 = $this->quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->save(['status' => 'LOGI_QUOTING']);

            if ($res1['code'] == 1 && $res2) {
                $this->inquiryModel->commit();
                $this->quoteModel->commit();
                $res = true;
            } else {
                $this->inquiryModel->rollback();
                $this->quoteModel->rollback();
                $res = false;
            }

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 提交物流审核接口
     *
     * @author liujf
     * @time 2017-08-08
     */
    public function submitLogiCheckAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            /* $where['inquiry_id'] = $condition['inquiry_id'];

              $this->quoteLogiFeeModel->startTrans();

              $data = [
              'status' => 'QUOTED',
              'checked_by' => $condition['checked_by'],
              'updated_at' => $this->time
              ];

              $res = $this->quoteLogiFeeModel->updateInfo($where, $data); */

            $inquiryModel = $this->inquiryModel;

            $logiCheckId = $condition['logi_check_id'];

            $this->inquiryModel->startTrans();
            $this->quoteModel->startTrans();

            $data = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $logiCheckId,
                'logi_check_id' => $logiCheckId,
                'status' => 'LOGI_APPROVING',
                'updated_by' => $this->user['id']
            ];

            $res1 = $this->inquiryModel->updateData($data);

            // 更改报价单状态
            $res2 = $this->quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->save(['status' => 'LOGI_APPROVING']);

            if ($res1['code'] == 1 && $res2) {
                $this->inquiryModel->commit();
                $this->quoteModel->commit();
                $res = true;
            } else {
                $this->inquiryModel->rollback();
                $this->quoteModel->rollback();
                $res = false;
            }

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 通过物流审核接口
     *
     * @author liujf
     * @time 2017-10-23
     */
    public function passLogiCheckAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $this->inquiryModel->startTrans();
            $this->quoteModel->startTrans();

            $inquiry = $this->inquiryModel->field('quote_id')->where(['id' => $condition['inquiry_id']])->find();

            $data = [
                'id' => $condition['inquiry_id'],
                'now_agent_id' => $inquiry['quote_id'],
                'status' => 'BIZ_APPROVING',
                'updated_by' => $this->user['id']
            ];

            $res1 = $this->inquiryModel->updateData($data);

            // 更改报价单状态
            $res2 = $this->quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->save(['status' => 'BIZ_APPROVING']);

            if ($res1['code'] == 1 && $res2) {
                $this->inquiryModel->commit();
                $this->quoteModel->commit();
                $res = true;
            } else {
                $this->inquiryModel->rollback();
                $this->quoteModel->rollback();
                $res = false;
            }

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 提交项目经理审核接口
     *
     * @author liujf
     * @time 2017-08-08
     */
    /* public function submitProjectCheckAction() {
      $condition = $this->put_data;

      if (!empty($condition['inquiry_id'])) {
      $where['inquiry_id'] = $condition['inquiry_id'];

      $quoteLogiFee = $this->quoteLogiFeeModel->where($where)->find();

      $this->quoteLogiFeeModel->startTrans();
      $this->quoteModel->startTrans();
      $this->inquiryModel->startTrans();
      $this->inquiryCheckLogModel->startTrans();

      $quoteLogiFeeData = [
      'status' => 'APPROVED',
      'updated_at' => $this->time,
      'checked_at' => $this->time
      ];

      $res1 = $this->quoteLogiFeeModel->updateInfo($where, $quoteLogiFeeData);

      $res2 = $this->quoteModel->where(['id' => $condition['quote_id']])->save(['status' => 'QUOTED_BY_LOGI']);

      $res3 = $this->inquiryModel->updateStatus(['id' => $quoteLogiFee['inquiry_id'], 'status' => 'QUOTED_BY_LOGI', 'updated_by' => $this->user['id']]);

      $checkLog= [
      'inquiry_id' => $quoteLogiFee['inquiry_id'],
      'quote_id' => $condition['quote_id'],
      'category' => 'LOGI',
      'action' => 'APPROVING',
      'op_result' => 'APPROVED'
      ];

      $res4 = $this->addCheckLog($checkLog, $this->inquiryCheckLogModel);

      if ($res1 && $res2 && $res3 && $res4) {
      $this->quoteLogiFeeModel->commit();
      $this->quoteModel->commit();
      $this->inquiryModel->commit();
      $this->inquiryCheckLogModel->commit();
      $res = true;
      } else {
      $this->quoteLogiFeeModel->rollback();
      $this->quoteModel->rollback();
      $this->inquiryModel->rollback();
      $this->inquiryCheckLogModel->rollback();
      $res = false;
      }

      $this->jsonReturn($res);
      } else {
      $this->jsonReturn(false);
      }
      } */

    /**
     * @desc 物流退回接口
     *
     * @author liujf
     * @time 2017-10-21
     */
    public function rejectLogiAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id']) && !empty($condition['current_node'])) {
            $this->inquiryModel->startTrans();
            $this->quoteModel->startTrans();

            $inquiry = $this->inquiryModel->field('quote_id, logi_agent_id')->where(['id' => $condition['inquiry_id']])->find();

            $data = [
                'id' => $condition['inquiry_id'],
                'updated_by' => $this->user['id']
            ];

            switch ($condition['current_node']) {
                case 'issue' :
                    $status = 'BIZ_QUOTING';
                    $data['now_agent_id'] = $inquiry['quote_id'];
                    break;
                case 'quote' :
                    $status = 'LOGI_DISPATCHING';
                    $inquiryModel = $this->inquiryModel;
                    $data['now_agent_id'] = $this->inquiryModel->getRoleUserId($this->user['group_id'], $inquiryModel::logiIssueMainRole, ['in', ['lg', 'elg']]);
                    break;
                case 'check' :
                    $status = 'LOGI_QUOTING';
                    $data['now_agent_id'] = $inquiry['logi_agent_id'];
            }

            $data['status'] = $status;

            // 更改询单状态
            $res1 = $this->inquiryModel->updateData($data);

            // 更改报价单状态
            $res2 = $this->quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->save(['status' => $status]);

            if ($res1['code'] == 1 && $res2) {
                $this->inquiryModel->commit();
                $this->quoteModel->commit();
                $res = true;
            } else {
                $this->inquiryModel->rollback();
                $this->quoteModel->rollback();
                $res = false;
            }

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 获取陆运险费用接口
     *
     * @author liujf
     * @time 2017-09-21
     */
    public function getOverlandInsuFeeAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $quote = $this->quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->find();

            $overlandInsuFee = $this->_getOverlandInsuFee($quote['total_exw_price'], $condition['overland_insu_rate']);
            $res['overland_insu'] = $overlandInsuFee['CNY'];

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 获取国际运输险费用接口
     *
     * @author liujf
     * @time 2017-09-21
     */
    public function getShippingInsuFeeAction() {
        $condition = $this->put_data;

        if (!empty($condition['inquiry_id'])) {
            $quote = $this->quoteModel->where(['inquiry_id' => $condition['inquiry_id']])->find();

            $shippingInsuFee = $this->_getShippingInsuFee($quote['total_exw_price'], $condition['shipping_insu_rate']);
            $res['shipping_insu'] = $shippingInsuFee['CNY'];

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 计算物流合计
     *
     * @param array $condition
     * --------------------------------------------------------------
     *     trade_terms_bn 贸易术语简称
     *     total_exw_price 报出EXW合计
     *     premium_rate 保险税率
     *     payment_period 回款周期(天)
     *     bank_interest 银行利息
     *     fund_occupation_rate 资金占用比例
     *     inspection_fee 商检费
     *     inspection_fee_cur 商检费币种
     *     land_freight 陆运费
     *     land_freight_cur 陆运费币种
     *     port_surcharge 港杂费
     *     port_surcharge_cur 港杂费币种
     *     inter_shipping 国际运费
     *     inter_shipping_cur 国际运费币种
     *     dest_delivery_fee 目的地配送费
     *     dest_delivery_fee_cur 目的地配送费币种
     *     dest_clearance_fee 目的地清关费
     *     dest_clearance_fee_cur 目的地清关费币种
     *     overland_insu_rate 陆运险率
     *     shipping_insu_rate 国际运输险率
     *     dest_tariff_rate 目的地关税税率
     *     dest_va_tax_rate 目的地增值税率
     * --------------------------------------------------------------
     * @return mixed
     * @author liujf
     * @time 2017-08-18
     */
    public function calcuTotalLogiFee($condition = []) {

        if (empty($condition['trade_terms_bn'])) {
            return false;
        } else {
            $trade = $condition['trade_terms_bn'];
        }

        $data = $condition;

        $data['inspection_fee'] = 0;
        $data['land_freight'] = 0;
        $data['port_surcharge'] = 0;
        $data['inter_shipping'] = 0;
        $data['dest_delivery_fee'] = 0;
        $data['dest_clearance_fee'] = 0;
        $data['overland_insu_rate'] = 0;
        $data['shipping_insu_rate'] = 0;
        $data['dest_tariff_rate'] = 0;
        $data['dest_va_tax_rate'] = 0;

        $data['inspection_fee'] = $condition['inspection_fee'] > 0 ? $condition['inspection_fee'] : 0;

        switch (true) {
            case $trade == 'EXW' :
                break;
            case $trade == 'FCA' || $trade == 'FAS' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                break;
            case $trade == 'FOB' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                break;
            case $trade == 'CPT' || $trade == 'CFR' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
                break;
            case $trade == 'CIF' || $trade == 'CIP' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
                break;
            case $trade == 'DAP' || $trade == 'DAT' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
                $data['dest_delivery_fee'] = $condition['dest_delivery_fee'] > 0 ? $condition['dest_delivery_fee'] : 0;
                break;
            case $trade == 'DDP' || $trade == '快递' :
                $data['land_freight'] = $condition['land_freight'] > 0 ? $condition['land_freight'] : 0;
                $data['overland_insu_rate'] = $condition['overland_insu_rate'] > 0 ? $condition['overland_insu_rate'] : 0;
                $data['port_surcharge'] = $condition['port_surcharge'] > 0 ? $condition['port_surcharge'] : 0;
                $data['inter_shipping'] = $condition['inter_shipping'] > 0 ? $condition['inter_shipping'] : 0;
                $data['shipping_insu_rate'] = $condition['shipping_insu_rate'] > 0 ? $condition['shipping_insu_rate'] : 0;
                $data['dest_delivery_fee'] = $condition['dest_delivery_fee'] > 0 ? $condition['dest_delivery_fee'] : 0;
                $data['dest_clearance_fee'] = $condition['dest_clearance_fee'] > 0 ? $condition['dest_clearance_fee'] : 0;
                $data['dest_tariff_rate'] = $condition['dest_tariff_rate'] > 0 ? $condition['dest_tariff_rate'] : 0;
                $data['dest_va_tax_rate'] = $condition['dest_va_tax_rate'] > 0 ? $condition['dest_va_tax_rate'] : 0;
        }

        $inspectionFeeUSD = round($data['inspection_fee'] / $this->_getRateUSD($data['inspection_fee_cur']), 8);
        $landFreightUSD = round($data['land_freight'] / $this->_getRateUSD($data['land_freight_cur']), 8);
        $overlandInsuFee = $this->_getOverlandInsuFee($data['total_exw_price'], $data['overland_insu_rate']);
        $overlandInsuUSD = $overlandInsuFee['USD'];
        $portSurchargeUSD = round($data['port_surcharge'] / $this->_getRateUSD($data['port_surcharge_cur']), 8);
        $interShippingUSD = round($data['inter_shipping'] / $this->_getRateUSD($data['inter_shipping_cur']), 8);
        $destDeliveryFeeUSD = round($data['dest_delivery_fee'] / $this->_getRateUSD($data['dest_delivery_fee_cur']), 8);
        $destClearanceFeeUSD = round($data['dest_clearance_fee'] / $this->_getRateUSD($data['dest_clearance_fee_cur']), 8);
        $sumUSD = $data['total_exw_price'] + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $inspectionFeeUSD + $interShippingUSD;
        $destTariffUSD = round($sumUSD * $data['dest_tariff_rate'] / 100, 8);
        $destVaTaxUSD = round($sumUSD * (1 + $data['dest_tariff_rate'] / 100) * $data['dest_va_tax_rate'] / 100, 8);

        $tmpRate1 = 1 - $data['premium_rate'] - round($data['payment_period'] * $data['bank_interest'] * $data['fund_occupation_rate'] / 365, 8);
        $tmpRate2 = $tmpRate1 - 1.1 * $data['shipping_insu_rate'] / 100;

        switch (true) {
            case $trade == 'EXW' :
                $totalQuotePrice = round(($data['total_exw_price'] + $inspectionFeeUSD) / $tmpRate1, 8);
                break;
            case $trade == 'FCA' || $trade == 'FAS' :
                $totalQuotePrice = round(($data['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD) / $tmpRate1, 8);
                break;
            case $trade == 'FOB' :
                $totalQuotePrice = round(($data['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD) / $tmpRate1, 8);
                break;
            case $trade == 'CPT' || $trade == 'CFR' :
                $totalQuotePrice = round(($data['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD) / $tmpRate1, 8);
                break;
            case $trade == 'CIF' || $trade == 'CIP' :
                $tmpCaFee = $data['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD;
                $totalQuotePrice = $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2);
                break;
            case $trade == 'DAP' || $trade == 'DAT' :
                $tmpCaFee = $data['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD + $destDeliveryFeeUSD;
                $totalQuotePrice = $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2);
                break;
            case $trade == 'DDP' || $trade == '快递' :
                $tmpCaFee = ($data['total_exw_price'] + $inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD) * (1 + $data['dest_tariff_rate'] / 100) * (1 + $data['dest_va_tax_rate'] / 100) + $destDeliveryFeeUSD + $destClearanceFeeUSD;
                $totalQuotePrice = $this->_getTotalQuotePrice($tmpCaFee, $data['shipping_insu_rate'], $tmpRate2);
        }

        $shippingInsuFee = $this->_getShippingInsuFee($data['total_exw_price'], $data['overland_insu_rate']);
        $shippingInsuUSD = $shippingInsuFee['USD'];
        $totalBankFeeUSD = round($totalQuotePrice * $data['bank_interest'] * $data['fund_occupation_rate'] * $data['payment_period'] / 365, 8);
        $totalInsuFeeUSD = round($totalQuotePrice * $data['premium_rate'], 8);

        $data['overland_insu'] = $overlandInsuUSD;
        $data['shipping_insu'] = $shippingInsuUSD;
        $data['dest_tariff_fee'] = $destTariffUSD;
        $data['dest_va_tax_fee'] = $destVaTaxUSD;

        // 物流费用合计
        $data['total_logi_fee'] = round($inspectionFeeUSD + $landFreightUSD + $overlandInsuUSD + $portSurchargeUSD + $interShippingUSD + $shippingInsuUSD + $destDeliveryFeeUSD + $destClearanceFeeUSD + $destTariffUSD + $destVaTaxUSD, 8);

        $data['shipping_charge_cny'] = round(($data['inspection_fee_cur'] == 'CNY' ? $data['inspection_fee'] : 0) + ($data['land_freight_cur'] == 'CNY' ? $data['land_freight'] : 0) + ($data['port_surcharge_cur'] == 'CNY' ? $data['port_surcharge'] : 0) + ($data['inter_shipping_cur'] == 'CNY' ? $data['inter_shipping'] : 0) + ($data['dest_delivery_fee_cur'] == 'CNY' ? $data['dest_delivery_fee'] : 0), 8);
        $data['shipping_charge_ncny'] = round(($data['inspection_fee_cur'] == 'USD' ? $data['inspection_fee'] : 0) + ($data['land_freight_cur'] == 'USD' ? $data['land_freight'] : 0) + ($data['port_surcharge_cur'] == 'USD' ? $data['port_surcharge'] : 0) + ($data['inter_shipping_cur'] == 'USD' ? $data['inter_shipping'] : 0) + ($data['dest_delivery_fee_cur'] == 'USD' ? $data['dest_delivery_fee'] : 0), 8);

        $data['total_quote_price'] = $totalQuotePrice;
        $data['total_bank_fee'] = $totalBankFeeUSD;
        $data['total_insu_fee'] = $totalInsuFeeUSD;

        return $data;
    }

    /**
     * @desc 获取报出价格合计
     *
     * @param float $calcuFee, $shippingInsuRate, $calcuRate
     * @return float
     * @author liujf
     * @time 2017-08-10
     */
    private function _getTotalQuotePrice($calcuFee, $shippingInsuRate, $calcuRate) {

        $tmpIfFee = round($calcuFee * 1.1 * $shippingInsuRate / 100 / $calcuRate, 8);

        if ($tmpIfFee >= 8 || $tmpIfFee == 0) {
            $totalQuotePrice = round($calcuFee / $calcuRate, 8);
        } else {
            $totalQuotePrice = round(($calcuFee + 8) / $calcuRate, 8);
        }

        return $totalQuotePrice;
    }

    /**
     * @desc 获取陆运险费用
     *
     * @param float $totalExwPrice exw价格合计
     * @param float $overlandInsuRate 陆运险率
     * @return float
     * @author liujf
     * @time 2017-09-20
     */
    private function _getOverlandInsuFee($totalExwPrice = 0, $overlandInsuRate = 0) {

        // 美元兑人民币汇率
        $rate = $this->_getRateUSD('CNY');

        $tmpPrice = $totalExwPrice * $overlandInsuRate / 100;

        $overlandInsuCNY = round($tmpPrice * $rate, 8);

        if ($overlandInsuCNY < 50) {
            $overlandInsuUSD = round($rate > 0 ? 50 / $rate : 0, 8);
            $overlandInsuCNY = 50;
        } else {
            $overlandInsuUSD = round($tmpPrice, 8);
        }

        return ['USD' => $overlandInsuUSD, 'CNY' => $overlandInsuCNY];
    }

    /**
     * @desc 获取国际运输险费用
     *
     * @param float $totalExwPrice exw价格合计
     * @param float $shippingInsuRate 国际运输险率
     * @return float
     * @author liujf
     * @time 2017-09-20
     */
    private function _getShippingInsuFee($totalExwPrice = 0, $shippingInsuRate = 0) {

        // 美元兑人民币汇率
        $rate = $this->_getRateUSD('CNY');

        $tmpPrice = $totalExwPrice * 1.1 * $shippingInsuRate / 100;

        $shippingInsuCNY = round($tmpPrice * $rate, 8);

        if ($shippingInsuCNY < 50) {
            $shippingInsuUSD = round($rate > 0 ? 50 / $rate : 0, 8);
            $shippingInsuCNY = 50;
        } else {
            $shippingInsuUSD = round($tmpPrice, 8);
        }

        return ['USD' => $shippingInsuUSD, 'CNY' => $shippingInsuCNY];
    }

    /**
     * @desc 获取人民币兑换汇率
     *
     * @param string $cur 币种
     * @return float
     * @author liujf
     * @time 2017-08-03
     */
    private function _getRateCNY($cur) {

        if (empty($cur)) {
            return 1;
        } else {
            return $this->_getRate('CNY', $cur);
        }
    }

    /**
     * @desc 获取美元兑换汇率
     *
     * @param string $cur 币种
     * @return float
     * @author liujf
     * @time 2017-08-03
     */
    private function _getRateUSD($cur) {

        if (empty($cur)) {
            return 1;
        } else {
            return $this->_getRate('USD', $cur);
        }
    }

    /**
     * @desc 获取币种兑换汇率
     *
     * @param string $holdCur 持有币种
     * @param string $exchangeCur 兑换币种
     * @return float
     * @author liujf
     * @time 2017-08-03
     */
    private function _getRate($holdCur, $exchangeCur = 'CNY') {

        if (!empty($holdCur)) {
            if ($holdCur == $exchangeCur)
                return 1;

            $exchangeRateModel = new ExchangeRateModel();
            $exchangeRate = $exchangeRateModel->field('rate')->where(['cur_bn1' => $holdCur, 'cur_bn2' => $exchangeCur])->order('created_at DESC')->find();

            return $exchangeRate['rate'];
        } else {
            return false;
        }
    }

    /**
     * @desc 获取用户所在指定组织的id集合串
     *
     * @param string $employeeId 员工id
     * @param array $findFields 查找的组织字段
     * @param string $outField 输出的组织字段
     * @return string 组织id集合串
     * @author liujf
     * @time 2017-08-31
     */
    private function _getOrgIds($employeeId = '-1', $findFields = ['logi_quote_org_id'], $outField = 'logi_check_org_id') {

        $orgMemberList = $this->orgMemberModel->getList(['employee_id' => $employeeId]);

        $orgArr = [];

        foreach ($orgMemberList as $orgMember) {
            $orgArr[] = $orgMember['org_id'];
        }

        $where = [];
        $condition = $orgArr ? ['in', $orgArr] : '-1';

        foreach ($findFields as $findField) {
            $where['_complex'][$findField] = $condition;
        }

        if ($where)
            $where['_complex']['_logic'] = 'or';

        $marketAreaTeamList = $this->marketAreaTeamModel->where($where)->select();

        $appointOrgArr = [];

        foreach ($marketAreaTeamList as $marketAreaTeam) {
            $appointOrgArr[] = $marketAreaTeam[$outField];
        }

        return implode(',', $appointOrgArr);
    }

    /**
     * @desc 对获取列表数据的处理
     *
     * @author liujf
     * @time 2017-08-02
     */
    private function _handleList($model, $data = [], $condition = [], $join = false) {
        if ($data) {
            $res['code'] = 1;
            $res['message'] = '成功!';
            $res['data'] = $data;
            $res['count'] = $join ? $model->getJoinCount($condition) : $model->getCount($condition);
            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 重写jsonReturn方法
     *
     * @author liujf
     * @time 2017-08-02
     */
    public function jsonReturn($data = [], $type = 'JSON') {
        if ($data) {
            $this->setCode('1');
            $this->setMessage('成功!');
            parent::jsonReturn($data, $type);
        } else {
            $this->setCode('-101');
            $this->setMessage('失败!');
            parent::jsonReturn();
        }
    }

}
