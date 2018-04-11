<?php

/*
 * @desc 供应商控制器
 *
 * @author liujf
 * @time 2017-11-10
 */

class SuppliersController extends PublicController {

    public function init() {
        parent::init();
        $this->put_data = dataTrim($this->put_data);

        $this->suppliersModel = new SuppliersModel();
        $this->supplierBankInfoModel = new SupplierBankInfoModel();
        $this->supplierExtraInfoModel = new SupplierExtraInfoModel();
        $this->supplierContactModel = new SupplierContactModel();
        $this->supplierMaterialCatModel = new SupplierMaterialCatModel();
        $this->supplierAgentModel = new SupplierAgentModel();
        $this->supplierQualificationModel = new SupplierQualificationModel();
        $this->supplierCheckLogsModel = new SupplierCheckLogsModel();
        $this->supplierAgentModel = new SupplierAgentModel();
        $this->inquiryModel = new InquiryModel();
        $this->employeeModel = new EmployeeModel();
        $this->orgModel = new OrgModel();

        $this->time = date('Y-m-d H:i:s');
    }

    /**
     * @desc 新增供应商记录接口
     *
     * @author liujf
     * @time 2017-11-10
     */
    public function addSupplierRecordAction() {

        $this->suppliersModel->startTrans();

        $condition['status'] = 'DRAFT';
        $condition['deleted_flag'] = 'Y';
        $condition['created_by'] = $this->user['id'];
        $condition['created_at'] = $this->time;

        $res1 = $this->suppliersModel->addRecord($condition);

        if ($res1)
            $condition['supplier_id'] = $res1;

        $res2 = $this->supplierContactModel->addRecord($condition);

        $res3 = $this->supplierQualificationModel->addRecord($condition);

        if ($res1 && $res2 && $res3) {
            $this->suppliersModel->commit();
            $res['supplier_id'] = $res1;
            $res['contact_id'] = $res2;
            $res['qualification_id'] = $res3;
        } else {
            $this->suppliersModel->rollback();
            $res = false;
        }

        $this->jsonReturn($res);
    }

    /**
     * @desc 修改供应商信息接口
     *
     * @author liujf
     * @time 2017-11-10
     */
    public function updateSupplierInfoAction() {
        $condition = $this->put_data;

        if ($condition['supplier_id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        if ($condition['status'] == '')
            jsonReturn('', -101, '状态不能为空!');

        if ($condition['items'] == '')
            jsonReturn('', -101, '缺少items参数!');

        if ($condition['status'] != 'DRAFT' && $condition['supplier_type'] == '')
            jsonReturn('', -101, '企业类型不能为空!');

        if ($condition['status'] != 'DRAFT' && $condition['name'] == '')
            jsonReturn('', -101, '公司名称不能为空!');

        if (strlenUtf8($condition['name']) > 100 || strlenUtf8($condition['name_en']) > 100)
            jsonReturn('', -101, '您输入的公司名称超出长度!');

        if ($condition['status'] != 'DRAFT' && $condition['country_bn'] == '')
            jsonReturn('', -101, '国家不能为空!');

        if ($condition['status'] != 'DRAFT' && $condition['address'] == '')
            jsonReturn('', -101, '公司地址不能为空!');

        if (strlenUtf8($condition['address']) > 100)
            jsonReturn('', -101, '您输入的公司地址大于100字!');

        if ($condition['status'] != 'DRAFT' && $condition['social_credit_code'] == '')
            jsonReturn('', -101, '营业执照（统一社会信用代码）编码不能为空!');

        if (strlenUtf8($condition['social_credit_code']) > 20)
            jsonReturn('', -101, '您输入的营业执照（统一社会信用代码）编码有误!');

        if ($condition['status'] != 'DRAFT' && $condition['reg_capital'] == '')
            jsonReturn('', -101, '注册资本不能为空!');

        if (strlenUtf8($condition['reg_capital']) > 20)
            jsonReturn('', -101, '请输入正确的注册资本!');

        if (strlenUtf8($condition['profile']) > 500)
            jsonReturn('', -101, '您输入的企业简介大于500字!');

        if ($condition['status'] != 'DRAFT' && $condition['bank_name'] == '')
            jsonReturn('', -101, '开户行名称不能为空!');

        if (strlenUtf8($condition['bank_name']) > 60)
            jsonReturn('', -101, '您输入的开户行名称大于60字!');

        if ($condition['status'] != 'DRAFT' && $condition['bank_account'] == '')
            jsonReturn('', -101, '开户账号不能为空!');

        if ($condition['bank_account'] != '' && !is_numeric($condition['bank_account']))
            jsonReturn('', -101, '开户账号只能输入数字!');

        //if (strlenUtf8($condition['bank_account']) > 20)
            //jsonReturn('', -101, '您输入的开户账号超过20位!');

        if (strlenUtf8($condition['bank_address']) > 100)
            jsonReturn('', -101, '开户地址最多输入100字!');

        if ($condition['org_id'] == '')
            jsonReturn('', -101, '所属事业部不能为空!');

        if ($condition['status'] != 'DRAFT' && $condition['sign_agreement_flag'] == '')
            jsonReturn('', -101, '是否签订合作协议不能为空!');

        if ($condition['status'] != 'DRAFT' && $condition['sign_agreement_flag'] == 'Y' && $condition['sign_agreement_time'] == '')
            jsonReturn('', -101, '签订协议时间不能为空!');

        if ($condition['status'] != 'DRAFT' && $condition['providing_sample_flag'] == '')
            jsonReturn('', -101, '是否提供样品不能为空!');

        if (strlenUtf8($condition['distribution_products']) > 200)
            jsonReturn('', -101, '您输入的铺货产品大于200字!');

        if ($condition['distribution_amount'] != '' && preg_match('/[^\.\d]/i', $condition['distribution_amount']))
            jsonReturn('', -101, '易瑞铺货金额只能输入正数 0 和点（.）!');

        if (strlenUtf8($condition['stocking_place']) > 40)
            jsonReturn('', -101, '备货地点长度不超过40个字!');
        
        if ($condition['status'] != 'DRAFT') {
            $hasDeveloper = $this->supplierAgentModel->where(['supplier_id' => $condition['supplier_id'], 'agent_type' => 'DEVELOPER'])->getField('agent_id');
            if (!$hasDeveloper) 
                jsonReturn('', -101, '开发人不能为空!');
            
            $hasSupplierName = $this->suppliersModel->where(['id' => ['neq', $condition['supplier_id']], 'name' => $condition['name']])->getField('id');
            if ($hasSupplierName)
                jsonReturn('', -101, '此公司名称已经存在!');
            
            $hasCreditCode = $this->suppliersModel->where(['id' => ['neq', $condition['supplier_id']], 'social_credit_code' => $condition['social_credit_code']])->getField('id');
            if ($hasCreditCode)
                jsonReturn('', -101, '此营业执照编码已经存在!');
        }

        $this->suppliersModel->startTrans();

        $flag = true;
        $change = false;
        $count = count($condition['items']);

        // 供应商联系人校验字段
        $checkContactFields = ['contact_name', 'phone', 'email'];

        foreach ($condition['items'] as $item) {
            if ($condition['status'] != 'DRAFT' && $item['contact_name'] == '')
                jsonReturn('', -101, '联系人姓名不能为空!');

            if (strlenUtf8($item['contact_name']) > 40)
                jsonReturn('', -101, '您输入的联系人姓名过长!');

            if ($condition['status'] != 'DRAFT' && $item['phone'] == '')
                jsonReturn('', -101, '联系方式不能为空!');

            if (strlenUtf8($item['phone']) > 20)
                jsonReturn('', -101, '您输入的联系方式不正确!');

            if ($condition['status'] != 'DRAFT' && $item['email'] == '')
                jsonReturn('', -101, '邮箱不能为空!');

            if ($item['email'] != '' && !preg_match('/^[a-zA-Z0-9_\-.]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/', $item['email']))
                jsonReturn('', -101, '您输入的邮箱格式不正确!');

            if (strlenUtf8($item['email']) > 50)
                jsonReturn('', -101, '您输入的邮箱大于50字!');

            if (strlenUtf8($item['title']) > 20)
                jsonReturn('', -101, '您输入的职位大于20字!');

            if (strlenUtf8($item['station']) > 20)
                jsonReturn('', -101, '您输入的岗位大于20字!');

            if (strlenUtf8($item['remarks']) > 100)
                jsonReturn('', -101, '您输入的负责产品大于100字!');
            
            if ($count == 1 && !isset($item['id'])) {
                $item['supplier_id'] = $condition['supplier_id'];
                $item['created_by'] = $this->user['id'];
                $item['created_at'] = $this->time;
            
                $resContact = $this->supplierContactModel->addRecord($item);
            } else {
                if ($item['id'] == '')
                    jsonReturn('', -101, '缺少供应商联系人主键id参数!');
            
                $contactWhere['id'] = $item['id'];
                
                // 审核通过状态下校验必填字段是否修改
                if ($condition['status'] == 'APPROVED' && !$change) {
                    $supplierContact = $this->supplierContactModel->getDetail($contactWhere);
                
                    $change = $this->_checkFieldsChange($supplierContact, $checkContactFields, $item);
                }
        
                $item['updated_by'] = $this->user['id'];
                $item['updated_at'] = $this->time;
        
                $resContact = $this->supplierContactModel->updateInfo($contactWhere, $item);
            }

            if (!$resContact && $flag)
                $flag = false;
        }

        // 供应商基本信息
        $supplierData = [
            'status' => $condition['status'],
            'erui_status' => 'CHECKING',
            'supplier_type' => $condition['supplier_type'],
            'name' => $condition['name'],
            'name_en' => $condition['name_en'],
            'country_bn' => $condition['country_bn'],
            'address' => $condition['address'],
            'social_credit_code' => $condition['social_credit_code'],
            'reg_capital' => $condition['reg_capital'],
            'logo' => $condition['logo'],
            'profile' => $condition['profile'],
            'org_id' => $condition['org_id'] == '' ? null : $condition['org_id'],
            'deleted_flag' => 'N', // 非删除
            'updated_by' => $this->user['id'],
            'updated_at' => $this->time
        ];

        $supplierWhere['id'] = $condition['supplier_id'];

        if ($condition['status'] == 'APPROVED' && !$change) {
            // 校验字段
            $checkFields = ['supplier_type', 'name', 'country_bn', 'address', 'social_credit_code', 'reg_capital'];

            $supplier = $this->suppliersModel->getDetail($supplierWhere);

            $change = $this->_checkFieldsChange($supplier, $checkFields, $condition);
        }

        if ($change) {
            $supplierData['status'] = 'APPROVING';
            $supplierData['erui_status'] = 'CHECKING';
        }

        $res1 = $this->suppliersModel->updateInfo($supplierWhere, $supplierData);

        $where['supplier_id'] = $condition['supplier_id'];

        $hasBank = $this->supplierBankInfoModel->field('id')->where($where)->find();

        // 供应商银行账户信息
        $brandData = [
            'bank_name' => $condition['bank_name'],
            'bank_account' => $condition['bank_account'],
            'address' => $condition['bank_address']
        ];

        if ($hasBank) {
            $brandData['updated_by'] = $this->user['id'];
            $brandData['updated_at'] = $this->time;
            $res2 = $this->supplierBankInfoModel->where($where)->save($brandData);
        } else {
            $brandData['supplier_id'] = $condition['supplier_id'];
            $brandData['created_by'] = $this->user['id'];
            $brandData['created_at'] = $this->time;
            $res2 = $this->supplierBankInfoModel->add($brandData);
        }

        $hasExtra = $this->supplierExtraInfoModel->field('id')->where($where)->find();

        // 供应商其他信息
        $extraData = [
            'sign_agreement_flag' => $condition['sign_agreement_flag'],
            'sign_agreement_time' => $condition['sign_agreement_time'] == '' ? null : $condition['sign_agreement_time'],
            'sign_agreement_end_time' => $condition['sign_agreement_end_time'] == '' ? null : $condition['sign_agreement_end_time'],
            'est_time_arrival' => $condition['est_time_arrival'] == '' ? null : $condition['est_time_arrival'],
            'distribution_amount' => $condition['distribution_amount'] == '' ? null : $condition['distribution_amount'],
            'providing_sample_flag' => $condition['providing_sample_flag'],
            'distribution_products' => $condition['distribution_products'],
            'stocking_place' => $condition['stocking_place'],
            'info_upload_flag' => $condition['info_upload_flag'],
            'photo_upload_flag' => $condition['photo_upload_flag']
        ];

        //协议到期时间用该大于现在的时间

        if ($hasExtra) {
            $extraData['updated_by'] = $this->user['id'];
            $extraData['updated_at'] = $this->time;
            $res3 = $this->supplierExtraInfoModel->updateInfo($where, $extraData);
        } else {
            $extraData['supplier_id'] = $condition['supplier_id'];
            $extraData['created_by'] = $this->user['id'];
            $extraData['created_at'] = $this->time;
            $res3 = $this->supplierExtraInfoModel->addRecord($extraData);
        }

        if ($flag && $res1 && $res2 && $res3) {
            $this->suppliersModel->commit();
            $res = true;
        } else {
            $this->suppliersModel->rollback();
            $res = false;
        }

        $this->jsonReturn($res);
    }

    /**
     * @desc 获取供应商列表接口
     *
     * @author liujf
     * @time 2017-11-10
     */
    public function getSupplierListAction() {
        $condition = $this->put_data;
        $isErui = $this->inquiryModel->getDeptOrgId($this->user['group_id'], 'erui');
        
        if (!$isErui) {
            // 非易瑞事业部门的看他所在事业部和易瑞的
            $orgUb = $this->inquiryModel->getDeptOrgId($this->user['group_id'], 'ub');
            $condition['org_id'] = $orgUb ? array_merge($this->orgModel->where(['org_node' => 'erui', 'deleted_flag' => 'N'])->getField('id', true), $orgUb) : [];
        }
        
        // 开发人
        if ($condition['developer'] != '') {
            $condition['agent_ids'] = $this->employeeModel->getUserIdByName($condition['developer']) ? : [];
        }
        
        // 创建人
        if ($condition['created_name'] != '') {
            $condition['created_ids'] = $this->employeeModel->getUserIdByName($condition['created_name']) ? : [];
        }
        
        // 供货范围
        if ($condition['cat_name'] != '') {
            $condition['supplier_ids'] = $this->supplierMaterialCatModel->getSupplierIdsByCat($condition['cat_name']) ? : [];
        }

        $supplierList = $this->suppliersModel->getJoinList($condition);

        foreach ($supplierList as &$supplier) {
            $count = $this->supplierQualificationModel->getExpiryDateCount($supplier['id']);
            $supplier['expiry_date'] = $count > 0 && $count <= 30 ? "剩{$count}天到期" : '';
            // 开发人
            $supplier['dev_name'] = $this->employeeModel->getUserNameById($supplier['agent_id']);
            // 供货范围
            $supplier['material_cat'] = $this->supplierMaterialCatModel->getCatBySupplierId($supplier['id']);
            //协议到期时间
            $supplier['sign_agreement_end_date'] = $this->supplierExtraInfoModel->getSignAgreementEndDateBy($supplier['id']);
        }

        $this->_handleList($this->suppliersModel, $supplierList, $condition, true);
    }

    /**
     * @desc 获取供应商详情接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function getSupplierDetailAction() {
        $condition = $this->put_data;

        if ($condition['id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        $res = $this->suppliersModel->getJoinDetail($condition);

        $this->jsonReturn($res);
    }

    /**
     * @desc 新增供应商联系人记录接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function addSupplierContactRecordAction() {
        $condition = $this->put_data;


        if ($condition['supplier_id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        $condition['created_by'] = $this->user['id'];
        $condition['created_at'] = $this->time;

        $res = $this->supplierContactModel->addRecord($condition);

        $this->jsonReturn($res);
    }

    /**
     * @desc 删除供应商联系人记录接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function delSupplierContactRecordAction() {
        $condition = $this->put_data;

        if ($condition['id'] == '')
            jsonReturn('', -101, '缺少供应商联系人主键id参数!');

        $res = $this->supplierContactModel->delRecord(['id' => $condition['id']]);

        $this->jsonReturn($res);
    }

    /**
     * @desc 批量修改供应商联系人信息接口
     *
     * @author liujf
     * @time 2017-11-16
     */
    public function batchUpdateSupplierContactInfoAction() {
        $condition = $this->put_data;
        
        if ($condition['supplier_id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        if ($condition['items'] == '')
            jsonReturn('', -101, '缺少items参数!');

        $flag = true;
        $data = [];
        $count = count($condition['items']);

        foreach ($condition['items'] as $item) {
            if ($item['contact_name'] == '')
                jsonReturn('', -101, '联系人姓名不能为空!');

            if (strlenUtf8($item['contact_name']) > 40)
                jsonReturn('', -101, '您输入的联系人姓名过长!');

            if ($item['phone'] == '')
                jsonReturn('', -101, '联系方式不能为空!');

            if (strlenUtf8($item['phone']) > 20)
                jsonReturn('', -101, '您输入的联系方式不正确!');

            if ($item['email'] == '')
                jsonReturn('', -101, '邮箱不能为空!');

            if ($item['email'] != '' && !preg_match('/^[a-zA-Z0-9_\-.]+@[a-zA-Z0-9_-]+(\.[a-zA-Z0-9_-]+)+$/', $item['email']))
                jsonReturn('', -101, '您输入的邮箱格式不正确!');

            if (strlenUtf8($item['email']) > 50)
                jsonReturn('', -101, '您输入的邮箱大于50字!');

            if (strlenUtf8($item['title']) > 20)
                jsonReturn('', -101, '您输入的职位大于20字!');

            if (strlenUtf8($item['station']) > 20)
                jsonReturn('', -101, '您输入的岗位大于20字!');

            if (strlenUtf8($item['remarks']) > 100)
                jsonReturn('', -101, '您输入的负责产品大于100字!');

            if ($count == 1 && !isset($item['id'])) {
                $item['supplier_id'] = $condition['supplier_id'];
                $item['created_by'] = $this->user['id'];
                $item['created_at'] = $this->time;
                
                $res = $this->supplierContactModel->addRecord($item);
            } else {
                if ($item['id'] == '')
                    jsonReturn('', -101, '缺少供应商联系人主键id参数!');
                
                $where['id'] = $item['id'];
                
                $item['updated_by'] = $this->user['id'];
                $item['updated_at'] = $this->time;
                
                $res = $this->supplierContactModel->updateInfo($where, $item);
            }

            if (!$res) {
                $data[] = $where['id'];
                if ($flag)
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
    }

    /**
     * @desc 获取供应商联系人列表接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function getSupplierContactListAction() {
        $condition = $this->put_data;

        if ($condition['supplier_id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        $data = $this->supplierContactModel->getList($condition);

        $this->_handleList($this->supplierContactModel, $data, $condition);
    }

    /**
     * @desc 新增供应商供货范围记录接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function addSupplierSupplyRecordAction() {
        $condition = $this->put_data;

        if ($condition['supplier_id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        if ($condition['material_cat_no1'] == '')
            jsonReturn('', -101, '一级物料分类编码不能为空!');
        
        if ($condition['material_cat_no2'] == '')
            jsonReturn('', -101, '二级物料分类编码不能为空!');
        
        if ($condition['material_cat_name3'] == '')
            jsonReturn('', -101, '请输入三级物料分类名称!');
        
        $exist = $this->supplierMaterialCatModel->Exist($condition);

        if (!$exist) {
            $condition['created_by'] = $this->user['id'];
            $condition['created_at'] = $this->time;
            
            $res = $this->supplierMaterialCatModel->addRecord($condition);

            $this->jsonReturn($res);
        } else {
            jsonReturn('', -101, '已经选择输入过相同的供货范围!');
        }
    }

    /**
     * @desc 删除供应商供货范围记录接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function delSupplierSupplyRecordAction() {
        $condition = $this->put_data;

        if ($condition['id'] == '')
            jsonReturn('', -101, '缺少供应商供货范围主键id参数!');

        $res = $this->supplierMaterialCatModel->delRecord(['id' => $condition['id']]);

        $this->jsonReturn($res);
    }

    /**
     * @desc 获取供应商供货范围列表接口
     *
     * @author liujf
     * @time 2017-11-14
     */
    public function getSupplierSupplyListAction() {
        $condition = $this->put_data;

        if ($condition['supplier_id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        $data = $this->supplierMaterialCatModel->getJoinList($condition);

        $this->_handleList($this->supplierMaterialCatModel, $data, $condition, true);
    }

    /**
     * @desc 新增供应商开发负责人记录接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function addSupplierAgentRecordAction() {
        $condition = $this->put_data;

        if ($condition['supplier_id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        if ($condition['agent_type'] == '')
            jsonReturn('', -101, '开发责任人类别不能为空!');

        if ($condition['agent_id'] == '')
            jsonReturn('', -101, '开发责任人不能为空!');

        $hasAgent = false;

        // 判断开发人和责任人是否存在，如果存在就替换
        if ($condition['agent_type'] == 'DEVELOPER' || $condition['agent_type'] == 'PERSON_LIABLE') {
            $where = [
                'supplier_id' => $condition['supplier_id'],
                'agent_type' => $condition['agent_type']
            ];

            $hasAgent = $this->supplierAgentModel->field('id')->where($where)->find();
        }

        if ($hasAgent) {
            $condition['updated_by'] = $this->user['id'];
            $condition['updated_at'] = $this->time;

            $res = $this->supplierAgentModel->updateInfo(['id' => $hasAgent['id']], $condition);
        } else {
            $condition['created_by'] = $this->user['id'];
            $condition['created_at'] = $this->time;

            $res = $this->supplierAgentModel->addRecord($condition);
        }

        $this->jsonReturn($res);
    }

    /**
     * @desc 删除供应商开发负责人记录接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function delSupplierAgentRecordAction() {
        $condition = $this->put_data;

        if ($condition['id'] == '')
            jsonReturn('', -101, '缺少供应商开发负责人主键id参数!');

        $res = $this->supplierAgentModel->delRecord(['id' => $condition['id']]);

        $this->jsonReturn($res);
    }

    /**
     * @desc 获取供应商开发负责人列表接口
     *
     * @author liujf
     * @time 2017-11-14
     */
    public function getSupplierAgentListAction() {
        $condition = $this->put_data;

        if ($condition['supplier_id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        $supplierAgentList = $this->supplierAgentModel->getJoinList($condition);

        $data = [];

        foreach ($supplierAgentList as $supplierAgent) {
            switch ($supplierAgent['agent_type']) {
                // 开发人
                case 'DEVELOPER' :
                    $data['developer'][] = $supplierAgent;
                    break;
                // 责任人
                case 'PERSON_LIABLE' :
                    $data['person_liable'][] = $supplierAgent;
                    break;
                // 考察人
                case 'INVESTIGATOR' :
                    $data['investigator'][] = $supplierAgent;
            }
        }

        $this->jsonReturn($data);
    }

    /**
     * @desc 新增供应商资质记录接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function addSupplierQualificationRecordAction() {
        $condition = $this->put_data;

        if ($condition['supplier_id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        $condition['created_by'] = $this->user['id'];
        $condition['created_at'] = $this->time;

        $res = $this->supplierQualificationModel->addRecord($condition);

        $this->jsonReturn($res);
    }

    /**
     * @desc 删除供应商资质记录接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function delSupplierQualificationRecordAction() {
        $condition = $this->put_data;

        if ($condition['id'] == '')
            jsonReturn('', -101, '缺少供应商资质主键id参数!');

        $res = $this->supplierQualificationModel->delRecord(['id' => $condition['id']]);

        $this->jsonReturn($res);
    }

    /**
     * @desc 批量修改供应商资质信息接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function batchUpdateSupplierQualificationInfoAction() {
        $condition = $this->put_data;
        
        if ($condition['supplier_id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        if ($condition['items'] == '')
            jsonReturn('', -101, '缺少items参数!');

        if ($condition['status'] == '')
            jsonReturn('', -101, '状态不能为空!');

        $flag = true;
        $data = [];
        $count = count($condition['items']);

        foreach ($condition['items'] as $item) {
            if (strlenUtf8($item['name']) > 50)
                jsonReturn('', -101, '您输入的资质名称长度超过限制!');

            if ($condition['status'] != 'DRAFT' && $item['code'] == '')
                jsonReturn('', -101, '资质编码不能为空!');

            if (strlenUtf8($item['code']) > 50)
                jsonReturn('', -101, '您输入的资质编码长度超过限制!');

            if ($condition['status'] != 'DRAFT' && $item['issue_date'] == '')
                jsonReturn('', -101, '发证日期不能为空!');

            if ($item['issue_date'] == '')
                $item['issue_date'] = null;
            
            if ($condition['status'] != 'DRAFT' && $item['expiry_date'] == '')
                jsonReturn('', -101, '到期时间不能为空!');

            if (strlenUtf8($item['issuing_authority']) > 50)
                jsonReturn('', -101, '您输入的发证机构长度超过限制!');

            if (strlenUtf8($item['remarks']) > 100)
                jsonReturn('', -101, '您输入的认证产品长度超过限制!');
            
            if ($count == 1 && !isset($item['id'])) {
                $item['supplier_id'] = $condition['supplier_id'];
                $item['created_by'] = $this->user['id'];
                $item['created_at'] = $this->time;
            
                $res = $this->supplierQualificationModel->addRecord($item);
            } else {
                if ($item['id'] == '')
                    jsonReturn('', -101, '缺少供应商资质主键id参数!');
                
                $where['id'] = $item['id'];
            
                $item['updated_by'] = $this->user['id'];
                $item['updated_at'] = $this->time;
            
                $res = $this->supplierQualificationModel->updateInfo($where, $item);
            }

            if (!$res) {
                $data[] = $where['id'];
                if ($flag)
                    $flag = false;
            }
        }
        
        if ($condition['status'] != 'DRAFT') {
            // 如果剩余资质过期时间大于30天，修改供应商状态为审核中
            $expiryDateCount= $this->supplierQualificationModel->getExpiryDateCount($condition['supplier_id']);
            if ($expiryDateCount > 30) {
                $this->suppliersModel->updateInfo(['id' => $condition['supplier_id']], ['status' => 'APPROVING']);
            }
        }

        if ($flag) {
            $this->jsonReturn($flag);
        } else {
            $this->setCode('-101');
            $this->setMessage('失败!');
            parent::jsonReturn($data);
        }
    }

    /**
     * @desc 获取供应商资质列表接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function getSupplierQualificationListAction() {
        $condition = $this->put_data;

        if ($condition['supplier_id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        $data = $this->supplierQualificationModel->getList($condition);

        $this->_handleList($this->supplierQualificationModel, $data, $condition);
    }

    /**
     * @desc 获取供应商审核日志列表接口
     *
     * @author liujf
     * @time 2017-11-11
     */
    public function getSupplierCheckLogListAction() {
        $condition = $this->put_data;

        if ($condition['supplier_id'] == '')
            jsonReturn('', -101, '缺少供应商id参数!');

        $data = $this->supplierCheckLogsModel->getJoinList($condition);

        $this->_handleList($this->supplierCheckLogsModel, $data, $condition, true);
    }
    
    /**
     * @desc 获取供应商资质过期列表接口
     *
     * @author liujf
     * @time 2018-03-05
     */
    public function getSupplierQualificationOverdueListAction() {
        $condition = $this->put_data;
    
        $isErui = $this->inquiryModel->getDeptOrgId($this->user['group_id'], 'erui');
    
        if (!$isErui) {
            // 事业部门的看他所在事业部的
            $orgUb = $this->inquiryModel->getDeptOrgId($this->user['group_id'], 'ub');
            $condition['org_id'] = $orgUb ? : [];
        }
        
        if ($condition['expiry_start_date'] != '' && $condition['expiry_end_date'] != '') {
            $condition['supplier_ids'] = $this->supplierQualificationModel->getOverduePeriodSupplierIds($condition['expiry_start_date'], $condition['expiry_end_date']);
        }
        
        // 供应商状态为资质过期
        $condition['qualification_status'] = 'OVERDUE';
    
        $supplierList = $this->suppliersModel->getJoinList($condition);
    
        foreach ($supplierList as &$supplier) {
            $supplier['expiry_date'] = $this->supplierQualificationModel->getExpiryDate($supplier['id']);
        }
    
        $this->_handleList($this->suppliersModel, $supplierList, $condition, true);
    }

    /**
     * @desc 校验字段值是否改变
     *
     * @param array $data 校验数据
     * @param mixed $checkFields 校验字段
     * @param array $condition 条件参数
     * @author liujf
     * @time 2017-11-16
     */
    private function _checkFieldsChange($data = [], $checkFields = [], $condition = []) {
        $change = false;
        if (is_string($checkFields)) {
            $checkFields = dataTrim(explode(',', $checkFields));
        }
        if ($data && $checkFields && $condition) {
            foreach ($data as $k => $v) {
                if (in_array($k, $checkFields) && $v != $condition[$k]) {
                    $change = true;
                    break;
                }
            }
        }
        return $change;
    }

    /**
     * @desc 对获取列表数据的处理
     *
     * @author liujf
     * @time 2017-11-10
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
     * @time 2017-11-10
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

    /**
     * @desc 导出供应商数据
     * @author 买买提
     * @time 2018-03-20
     */
    public function exportAction()
    {

        $data = $this->getExportData($this->put_data);

        $localFile = SupplierHelper::createSupplierExcelWithData($data);

        $response = SupplierHelper::upload2FastDFS($localFile);

        if ($response['code']) {
            $this->jsonReturn([
                'code' => 1,
                'message' => '成功!',
                'data' => [
                    'url' => $response['url'],
                    'name' => $response['name']
                ]
            ]);
        }

        $this->jsonReturn(['code' => -1, 'message' => '失败!']);

    }

    /**@desc 获取导出的数据
     * @param $condition
     * @return mixed
     */
    private function getExportData($condition)
    {
        // 开发人
        if ($condition['developer'] != '') {
            $condition['agent_ids'] = $this->employeeModel->getUserIdByName($condition['developer']) ? : [];
        }

        // 创建人
        if ($condition['created_name'] != '') {
            $condition['created_ids'] = $this->employeeModel->getUserIdByName($condition['created_name']) ? : [];
        }

        // 供货范围
        if ($condition['cat_name'] != '') {
            $condition['supplier_ids'] = $this->supplierMaterialCatModel->getSupplierIdsByCat($condition['cat_name']) ? : [];
        }

        $supplierList = $this->suppliersModel->getJoinListForExport($condition);

        foreach ($supplierList as &$supplier) {
            $count = $this->supplierQualificationModel->getExpiryDateCount($supplier['id']);
            $supplier['expiry_date'] = $count > 0 && $count <= 30 ? "剩{$count}天到期" : '';
            $supplier['material_cat'] = $this->supplierMaterialCatModel->getMaterialCatNameBy($supplier['id']);
            $supplier['developer'] = $this->supplierAgentModel->getDeveloperNameBy($supplier['id']);
            $supplier['status'] = $this->setStatusName($supplier['erui_status']);

            $supplier['en_spu_count'] = $this->getCountBy('PRODUCT', $supplier['id']);
            $supplier['zh_spu_count'] = $this->getCountBy('PRODUCT', $supplier['id'], 'zh');
            $supplier['en_sku_count'] = $this->getCountBy('GOODS', $supplier['id']);
            $supplier['zh_sku_count'] = $this->getCountBy('GOODS', $supplier['id'], 'zh');

            $supplier['created_by'] = $this->setUserName($supplier['created_by']);
            $supplier['checked_by'] = $this->setUserName($supplier['checked_by']);
        }

        return $supplierList;
    }

    /**
     * 获取供应商的SKU/SPU总数
     * @param $model SPU/SKU
     * @param $supplier_id
     * @param string $lang
     * @return mixed
     * @author 买买提
     */
    private function getCountBy($model, $supplier_id, $lang='en')
    {
        if ($model == 'GOODS') {
            $GoodsSupplierModel = new GoodsSupplierModel();
            return $GoodsSupplierModel->alias('a')
                ->join('erui_goods.goods b ON a.sku=b.sku', 'LEFT')
                ->where(['a.supplier_id' => $supplier_id, 'b.lang'=> $lang])
                ->count();
        }else{
            $ProductSupplierModel = new ProductSupplierModel();
            return $ProductSupplierModel->alias('a')
                ->join('erui_goods.product b ON a.spu=b.spu', 'LEFT')
                ->where(['a.supplier_id' => $supplier_id, 'b.lang'=> $lang])
                ->count();
        }
    }

    /**
     * @desc 设置用户名
     * @param $user_id
     * @return mixed
     */
    private function setUserName($user_id)
    {
        return (new EmployeeModel)->where(['id' => $user_id])->getField('name');
    }

    /**
     * 设置状态名称
     * @param $status
     * @return string
     * @author 买买提
     */
    private function setStatusName($status)
    {
        switch ($status)
        {
            case 'CHECKING' :
                return '审核中';
                break;
            case 'VALID' :
                return '审核通过';
                break;
        }
    }
}
