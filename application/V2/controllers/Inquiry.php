<?php
/**
 * name: Inquiry.php
 * desc: 询价单控制器
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:45
 */
class InquiryController extends PublicController {

    public function __init() {
        parent::__init();
    }

    //返回询价单流水号
    public function getSerialNoAction() {
        $data['serial_no'] = $this->getInquirySerialNo();
        if(!empty($data)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        }else{
            $this->setCode('-101');
            $this->setMessage('生成流水号错误!');
            $this->jsonReturn();
        }
    }

    //查询询单流程编码是否存在
    public function checkSerialNoAction() {
        $inquiry = new InquiryModel();
        $where = $this->put_data;

        $results = $inquiry->checkSerialNo($where);

        $this->jsonReturn($results);
    }

    //询价单列表
    public function getListAction(){
        $inquiry = new InquiryModel();
        $employee = new EmployeeModel();

        $where = $this->put_data;
        //如果搜索条件有经办人，转换成id
        if($where['agent_name']){
            $agent = $employee-file('id')->where('name='.$where['agent_name'])->find();
            if($agent){
                $where['agent_id']=$agent['id'];
            }
        }
        //如果搜索条件有项目经理，转换成id
        if($where['pm_name']){
            $pm = $employee-file('id')->where('name='.$where['agent_name'])->find();
            if($agent){
                $where['pm_id']=$pm['id'];
            }
        }

        $results = $inquiry->getList($where);
        //把经办人和项目经理转换成名称显示
        if($results['code'] == '1'){
            foreach($results['data'] as $key=>$val){
                //经办人
                if(!empty($val['agent_id'])){
                    $rs1 = $employee->where('id='.$val['agent_id'])->find();
                    $results['data'][$key]['agent_name'] = $rs1['name'];
                }
                //项目经理
                if(!empty($val['pm_id'])){
                    $rs2 = $employee->where('id='.$val['pm_id'])->find();
                    $results['data'][$key]['pm_name'] = $rs2['name'];
                }
            }
        }

        $this->jsonReturn($results);
    }

    //询价单详情
    public function getInfoAction() {
        $inquiry = new InquiryModel();
        $employee = new EmployeeModel();
        $where = $this->put_data;

        $results = $inquiry->getInfo($where);
        //经办人
        if(!empty($results['data']['agent_id'])){
            $rs1 = $employee->where('id='.$results['data']['agent_id'])->find();
            $results['data']['agent_name'] = $rs1['name'];
        }
        //项目经理
        if(!empty($results['data']['pm_id'])){
            $rs2 = $employee->where('id='.$results['data']['pm_id'])->find();
            $results['data']['pm_name'] = $rs2['name'];
        }

        $this->jsonReturn($results);
    }

    //添加询价单
    public function addAction(){
        $inquiry = new InquiryModel();
        $data =  $this->put_data;
        $data['created_by'] = $this->user['id'];

        $results = $inquiry->addData($data);
        if($results['code'] == '1'){
            $approveLog['inquiry_no'] = $results['data']['inquiry_no'];
            $approveLog['type'] = '创建市场报价单';
            $this->addApproveLog($approveLog);
        }
        $this->jsonReturn($results);
    }

    //修改询价单
    public function updateAction(){
        $inquiry = new InquiryModel();
        $data =  $this->put_data;
        $data['updated_by'] = $this->user['id'];

        $results = $inquiry->updateData($data);
        $this->jsonReturn($results);
    }

    //批量修改询价单状态
    public function updateStatusAction(){
        $inquiry = new InquiryModel();
        $data =  $this->put_data;
        $data['updated_by'] = $this->user['id'];

        $results = $inquiry->updateStatus($data);
        $this->jsonReturn($results);
    }

    //删除询价单
    public function deleteAction() {
        $inquiry = new InquiryModel();
        $where =  $this->put_data;

        $results = $inquiry->deleteData($where);
        $this->jsonReturn($results);
    }

    //附件列表
    public function getListAttachAction() {
        $attach = new InquiryattachModel();
        $where =  $this->put_data;

        $results = $attach->getList($where);

        $this->jsonReturn($results);
    }

    //添加附件
    public function addAttachAction() {
        $attach = new InquiryattachModel();
        $data =  $this->put_data;
        $data['created_by'] = $this->user['id'];

        $results = $attach->addData($data);

        $this->jsonReturn($results);
    }

    //删除附件
    public function delAttachAction() {
        $attach = new InquiryattachModel();
        $data =  $this->put_data;
        $data['updated_by'] = $this->user['id'];

        $results = $attach->deleteData($data);
        $this->jsonReturn($results);
    }

    //明细列表
    public function getListItemAction() {
        $Item = new InquiryitemModel();

        $where =  $this->put_data;

        $results = $Item->getList($where);
        $this->jsonReturn($results);
    }

    //明细详情
    public function getInfoItemAction() {
        $Item = new InquiryitemModel();

        $where =  $this->put_data;

        $results = $Item->getInfo($where);
        $this->jsonReturn($results);
    }

    //添加明细
    public function addItemAction() {
        $Item = new InquiryitemModel();
        $data =  $this->put_data;
        $data['created_by'] = $this->user['id'];

        $results = $Item->addData($data);
        $this->jsonReturn($results);
    }

    //修改明细
    public function updateItemAction() {
        $Item = new InquiryitemModel();
        $data =  $this->put_data;
        $data['updated_by'] = $this->user['id'];

        $results = $Item->updateData($data);
        $this->jsonReturn($results);
    }

    //删除明细
    public function delItemAction() {
        $Item = new InquiryitemModel();
        $data =  $this->put_data;

        $results = $Item->deleteData($data);
        $this->jsonReturn($results);
    }

    //明细附件列表
    public function getListItemAttachAction()
    {
        $ItemAttach = new InquiryitemattachModel();

        $where =  $this->put_data;

        $results = $ItemAttach->getlist($where);
        $this->jsonReturn($results);
    }

    //添加明细附件
    public function addItemAttachAction() {
        $ItemAttach = new InquiryitemattachModel();
        $data =  $this->put_data;
        $data['created_by'] = $this->user['id'];

        $results = $ItemAttach->addData($data);
        $this->jsonReturn($results);
    }

    //删除明细附件
    public function delItemAttachAction() {
        $ItemAttach = new InquiryitemattachModel();
        $data =  $this->put_data;

        $results = $ItemAttach->deleteData($data);
        $this->jsonReturn($results);
    }
}