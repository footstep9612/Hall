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

    /*
     * 返回询价单流程编码
     * Author:张玉良
     */
    public function getSerialNoAction() {
        $serial_no = $this->getInquirySerialNo();
        return $serial_no;
        /*if(!empty($data)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        }else{
            $this->setCode('-101');
            $this->setMessage('生成流水号错误!');
            $this->jsonReturn();
        }*/
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
        $data['buyer_id'] = '1';
        $data['country_bn'] = 'test';
        $data['created_by'] = $this->user['id'];

        $results = $inquiry->addData($data);

        $this->jsonReturn($results);
    }

    /*
     * 询价单列表
     * Author:张玉良
     */
    public function getListAction(){
        $inquiry = new InquiryModel();
        $employee = new EmployeeModel();

        $where = $this->put_data;
        //如果搜索条件有经办人，转换成id
        if(!empty($where['agent_name'])){
            $agent = $employee->field('id')->where('name='.$where['agent_name'])->find();
            if($agent){
                $where['agent_id']=$agent['id'];
            }
        }
        //如果搜索条件有项目经理，转换成id
        if(!empty($where['pm_name'])){
            $pm = $employee->field('id')->where('name='.$where['agent_name'])->find();
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

    /*
     * 询价单详情
     * Author:张玉良
     */
    public function getInfoAction() {
        $inquiry = new InquiryModel();
        $employee = new EmployeeModel();
        $where = $this->put_data;

        $results = $inquiry->getInfo($where);
        //经办人
        if(!empty($results['data']['agent_id'])){
            $rs1 = $employee->field('name')->where('id='.$results['data']['agent_id'])->find();
            $results['data']['agent_name'] = $rs1['name'];
        }
        //项目经理
        if(!empty($results['data']['pm_id'])){
            $rs2 = $employee->field('name')->where('id='.$results['data']['pm_id'])->find();
            $results['data']['pm_name'] = $rs2['name'];
        }

        $this->jsonReturn($results);
    }

    /*
     * 添加询价单
     * Author:张玉良
     */
    public function addAction(){
        $inquiry = new InquiryModel();
        $data =  $this->put_data;
        $data['created_by'] = $this->user['id'];

        $results = $inquiry->addData($data);

        $this->jsonReturn($results);
    }

    /*
     * 修改询价单
     * Author:张玉良
     */
    public function updateAction(){
        $inquiry = new InquiryModel();
        $data =  $this->put_data;
        $data['updated_by'] = $this->user['id'];

        $results = $inquiry->updateData($data);
        $this->jsonReturn($results);
    }

    /*
     * 批量修改询价单状态
     * Author:张玉良
     */
    public function updateStatusAction(){
        $inquiry = new InquiryModel();
        $data =  $this->put_data;
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
        $where =  $this->put_data;

        $results = $inquiry->deleteData($where);
        $this->jsonReturn($results);
    }

    /*
     * 附件列表
     * Author:张玉良
     */
    public function getAttachListAction() {
        $attach = new InquiryAttachModel();
        $where =  $this->put_data;

        $results = $attach->getList($where);

        $this->jsonReturn($results);
    }

    /*
     * 添加附件
     * Author:张玉良
     */
    public function addAttachAction() {
        $attach = new InquiryAttachModel();
        $data =  $this->put_data;
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
        $data =  $this->put_data;
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

        $where =  $this->put_data;

        $results = $Item->getList($where);
        $this->jsonReturn($results);
    }

    /*
     * 询单sku详情
     * Author:张玉良
     */
    public function getItemInfoAction() {
        $Item = new InquiryItemModel();

        $where =  $this->put_data;

        $results = $Item->getInfo($where);
        $this->jsonReturn($results);
    }

    /*
     * 添加询单sku
     * Author:张玉良
     */
    public function addItemAction() {
        $Item = new InquiryItemModel();
        $data =  $this->put_data;
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
        $data =  $this->put_data;
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
        $data =  $this->put_data;
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
        $data =  $this->put_data;

        if(isset($data['sku'])){
            $Item->startTrans();
            foreach($data['sku'] as $val){
                $condition = $val;
                $condition['updated_by'] = $this->user['id'];

                $results = $Item->updateData($condition);
                if($results['code']!=1){
                    $Item->rollback();
                    $this->jsonReturn($results);die;
                }
            }
            $Item->commit();
        }else{
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
        $data =  $this->put_data;

        $results = $Item->deleteData($data);
        $this->jsonReturn($results);
    }

    /*
     * 询单sku附件列表
     * Author:张玉良
     */
    public function getItemAttachListAction()
    {
        $ItemAttach = new InquiryItemAttachModel();

        $where =  $this->put_data;

        $results = $ItemAttach->getlist($where);
        $this->jsonReturn($results);
    }

    /*
     * 添加询单sku附件
     * Author:张玉良
     */
    public function addItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();
        $data =  $this->put_data;
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
        $data =  $this->put_data;

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
        $data =  $this->put_data;
        if(!empty($data['inquiry_id'])){
            $results = $checklog->getList($data);

            foreach($results['data'] as $key=>$val){
                $rs = $employee->field('name')->where('id='.$val['op_id'])->find();
                $results['data'][$key]['op_name'] = $rs['name'];
            }
        }else{
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
        $data =  $this->put_data;
        $data['op_id'] = $this->user['id'];
        $data['created_by'] = $this->user['id'];

        $results = $checklog->addData($data);
        $this->jsonReturn($results);
    }
}