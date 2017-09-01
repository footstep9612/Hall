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
    }

    /**
     * 验证用户权限
     * Author:张玉良
     * @return string
     */
    public function checkAuthAction() {
        $groupid = $this->user['group_id'];
        if (isset($groupid)) {
            $maketareateam = new MarketAreaTeamModel();
            $users = [];

            if(is_array($groupid)){
                //查询是否方案中心，下面有多少市场人员
                $users = $maketareateam->alias('a')
                        ->field('b.employee_id')
                        ->join('`erui2_sys`.`org_member` b on a.market_org_id = b.org_id')
                        ->where('a.biz_tech_org_id in('.implode(',',$groupid).')')
                        ->select();

                //查询是否是市场人员
                $agent = $maketareateam->where('market_org_id in('.implode(',',$groupid).')')->count('id');
            }else{
                //查询是否方案中心，下面有多少市场人员
                $users = $maketareateam->alias('a')
                    ->field('b.employee_id')
                    ->join('`erui2_sys`.`org_member` b on a.market_org_id = b.org_id')
                    ->where('a.biz_tech_org_id='.$groupid)
                    ->select();

                //查询是否是市场人员
                $agent = $maketareateam->where('market_org_id='.$groupid)->count('id');
            }

            array_unique($users);

            if (!empty($users)) {
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
    }

    /*
     * 返回询价单流程编码
     * Author:张玉良
     */

    public function getSerialNoAction() {
        $serial_no = $this->getInquirySerialNo();
        return $serial_no;
        /* if(!empty($data)){
          $this->setCode('1');
          $this->setMessage('成功!');
          $this->jsonReturn($data);
          }else{
          $this->setCode('-101');
          $this->setMessage('生成流水号错误!');
          $this->jsonReturn();
          } */
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

    public function getListAction() {
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
            $where['status'] = array('NEQ', 'DRAFT');
        }

        //如果搜索条件有经办人，转换成id
        if (!empty($where['agent_name'])) {
            $agent = $employee->field('id')->where('name="' . $where['agent_name'] . '"')->find();

            if (in_array($agent['id'], $where['agent_id'])) {
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
            $pm = $employee->field('id')->where('name="' . $where['agent_name'] . '"')->find();
            if ($agent) {
                $where['pm_id'] = $pm['id'];
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
        }

        $this->jsonReturn($results);
    }

    /*
     * 询价单详情
     * Author:张玉良
     */

    public function getInfoAction() {
        $auth = $this->checkAuthAction();
        $inquiry = new InquiryModel();
        $employee = new EmployeeModel();
        $where = $this->put_data;

        $results = $inquiry->getInfo($where);

        if ( $auth['code'] == 1 ) {
			foreach($auth['data'] as $val) {
				$agent[] = $val['employee_id'];
			}
            $results['data']['agent_list'] = implode(',',$agent); //如果是方案中心角色，返回区域下全部市场人员
        } else if( $auth['code'] == 2 ) {
            $results['data']['agent_list'] = $this->user['id']; //如果是市场人员，返回自己
        } else {
            $results['data']['agent_list'] = '';
        }
        //经办人
        if (!empty($results['data']['agent_id'])) {
            $rs1 = $employee->field('name')->where('id=' . $results['data']['agent_id'])->find();
            $results['data']['agent_name'] = $rs1['name'];
        }
        //项目经理
        if (!empty($results['data']['pm_id'])) {
            $rs2 = $employee->field('name')->where('id=' . $results['data']['pm_id'])->find();
            $results['data']['pm_name'] = $rs2['name'];
        }
        //询单创建人
        if (!empty($results['data']['created_by'])) {
            $rs3 = $employee->field('name')->where('id=' . $results['data']['created_by'])->find();
            $results['data']['created_name'] = $rs3['name'];
        }

        $this->jsonReturn($results);
    }

    /*
     * 添加询价单
     * Author:张玉良
     */

    public function addAction() {
        $auth = $this->checkAuthAction();
        $inquiry = new InquiryModel();
        $data = $this->put_data;
        $data['created_by'] = $this->user['id'];

        if ($auth['code'] == 1) {
            $data['status'] = 'APPROVING_BY_SC';
        }

        $results = $inquiry->addData($data);

        $this->jsonReturn($results);
    }

    /*
     * 修改询价单
     * Author:张玉良
     */

    public function updateAction() {
        $auth = $this->checkAuthAction();
        $inquiry = new InquiryModel();
        $data = $this->put_data;
        $data['updated_by'] = $this->user['id'];

        if(empty($data['status'])){
            if ($auth['code'] == 1) {
                $data['status'] = 'APPROVING_BY_SC';
            }
        }


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
            $results['message'] = '没有ID!';
            $this->jsonReturn($results);
        }
        $data = $inquiry->field('id,serial_no,status')->where('status!="DRAFT" and id in(' . $where['id'] . ')')->select();
        if (count($data) > 0) {
            $results['code'] = '-104';
            $results['message'] = '存在不允许删除的询单!';
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
                if($val['attach_group'] == 'BUYER'){
                    $buyerdata = $buyer->field('id,name')->where('id=' . $val['created_by'])->find();
                    $results['data'][$key]['created_name'] = $buyerdata['name'];
                }else{
                    $employeedata = $employee->field('id,name')->where('id=' . $val['created_by'])->find();
                    $results['data'][$key]['created_name'] = $employeedata['name'];

                    $roledata = $roleuser->alias('a')
                        ->join('erui2_sys.role b ON a.role_id = b.id', 'LEFT')
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

        $results = $Item->getList($where);
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
            foreach ($data['sku'] as $val) {
                $condition = $val;
                $condition['updated_by'] = $this->user['id'];

                $results = $Item->updateData($condition);
                if ($results['code'] != 1) {
                    $Item->rollback();
                    $this->jsonReturn($results);
                    die;
                }
            }
            $Item->commit();
        } else {
            $results['code'] = '-101';
            $results['messaage'] = '修改失败!';
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

    /*
     * 审核日志列表
     * Author:张玉良
     */

    public function getCheckLogListAction() {
        $checklog = new CheckLogModel();
        $employee = new EmployeeModel();
        $roleuser = new RoleUserModel();
        $data = $this->put_data;
        if (!empty($data['inquiry_id'])) {
            $results = $checklog->getList($data);

            foreach ($results['data'] as $key => $val) {
                $employeedata = $employee->field('id,name')->where('id=' . $val['op_id'])->find();
                $results['data'][$key]['op_name'] = $employeedata['name'];

                $roledata = $roleuser->alias('a')
                        ->join('erui2_sys.role b ON a.role_id = b.id', 'LEFT')
                        ->where('a.employee_id=' . $val['op_id'])
                        ->field('b.name,b.name_en,b.remarks')
                        ->find();
                $results['data'][$key]['op_role'] = $roledata['name'];
            }
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有询单ID!';
        }

        $this->jsonReturn($results);
    }

    /*
     * 添加审核日志
     * Author:张玉良
     */

    public function addCheckLogAction() {
        $checklog = new CheckLogModel();
        $data = $this->put_data;
        $data['op_id'] = $this->user['id'];
        $data['created_by'] = $this->user['id'];

        $results = $checklog->addData($data);
        $this->jsonReturn($results);
    }

}
