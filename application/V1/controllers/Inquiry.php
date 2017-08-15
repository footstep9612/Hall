<?php
/**
 * name: Inquiry.php
 * desc: 询价单控制器
 * User: zhangyuliang
 * Date: 2017/6/16
 * Time: 14:51
 */
class InquiryController extends PublicController {

    public function __init() {
        parent::__init();
    }

    //返回询价单流水号
    public function getInquiryNoAction() {
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

    //查询询单号（项目代码）是否存在
    public function checkInquiryNoAction() {
        $inquiry = new InquiryModel();
        $where = $this->put_data;

        $results = $inquiry->checkInquiryNo($where);

        $this->jsonReturn($results);
    }

    //询价单列表
    public function getListAction(){
        $inquiry = new InquiryModel();
        $user = new UserModel();
        $area = new MarketAreaModel();
        $country = new MarketAreaCountryModel();

        $where = $this->put_data;

        $results = $inquiry->getlist($where);
        if($results['code'] == '1'){
            foreach($results['data'] as $key=>$val){
                if(!empty($val['agent'])){
                    $userId = json_decode($val['agent']);
                    $userInfo = $user->where('id='.$userId['1'])->find();
                    $results['data'][$key]['agent'] = $userInfo['name'];
                }
                if(!empty($val['inquiry_region'])){
                    $areaInfo = $area->where('id='.$val['inquiry_region'])->find();
                    $results['data'][$key]['inquiry_region'] = $areaInfo['bn'];
                }
                if(!empty($val['inquiry_country'])){
                    $areaInfo = $country->where('id='.$val['inquiry_country'])->find();
                    $results['data'][$key]['inquiry_country'] = $areaInfo['country_bn'];
                }
            }
        }

        $this->jsonReturn($results);
    }

    //询价单详情
    public function getInfoAction() {
        $inquiry = new InquiryModel();
        $where = $this->put_data;

        $results = $inquiry->getinfo($where);

        $this->jsonReturn($results);
    }

    //添加询价单
    public function addAction(){
        $inquiry = new InquiryModel();
        $data =  $this->put_data;

        $results = $inquiry->add_data($data);
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

        $results = $inquiry->update_data($data);
        $this->jsonReturn($results);
    }

    //批量修改询价单状态
    public function updateStatusAction(){
        $inquiry = new InquiryModel();
        $data =  $this->put_data;

        $results = $inquiry->update_status($data);
        $this->jsonReturn($results);
    }

    //删除询价单
    public function deleteAction() {
        $inquiry = new InquiryModel();
        $where =  $this->put_data;
        //$where['inquiry_no'] = '10001';
        $results = $inquiry->delete_data($where);
        $this->jsonReturn($results);
    }

    //附件列表
    public function getListAttachAction() {
        $attach = new InquiryAttachModel();
        $where =  $this->put_data;

        $results = $attach->getlist($where);
        //var_dump($data);die;
        $this->jsonReturn($results);
    }

    //添加附件
    public function addAttachAction() {
        $attach = new InquiryAttachModel();
        $data =  $this->put_data;

        if(!empty($data['serial_no'])){
            $attach->where('serial_no='.$data['serial_no'].' and attach_group="BUYER"')->delete();
        }

        $results = $attach->add_data($data);

        $this->jsonReturn($results);
    }

    //删除附件
    public function delAttachAction() {
        $attach = new InquiryAttachModel();
        $data =  $this->put_data;

        $results = $attach->delete_data($data);
        $this->jsonReturn($results);
    }

    //明细列表
    public function getListItemAction() {
        $Item = new InquiryItemModel();

        $where =  $this->put_data;

        $results = $Item->getlist($where);
        $this->jsonReturn($results);
    }

    //明细列表
    public function getInfoItemAction() {
        $Item = new InquiryItemModel();

        $where =  $this->put_data;

        $results = $Item->getinfo($where);
        $this->jsonReturn($results);
    }

    //添加明细
    public function addItemAction() {
        $Item = new InquiryItemModel();
        $data =  $this->put_data;

        $results = $Item->add_data($data);
        $this->jsonReturn($results);
    }

    //添加明细
    public function updateItemAction() {
        $Item = new InquiryItemModel();
        $data =  $this->put_data;

        $results = $Item->update_data($data);
        $this->jsonReturn($results);
    }

    //删除明细
    public function delItemAction() {
        $Item = new InquiryItemModel();
        $data =  $this->put_data;

        $results = $Item->delete_data($data);
        $this->jsonReturn($results);
    }

    //明细附件列表
    public function getListItemAttachAction()
    {
        $ItemAttach = new InquiryItemAttachModel();

        $where =  $this->put_data;

        $results = $ItemAttach->getlist($where);
        $this->jsonReturn($results);
    }

    //添加明细附件
    public function addItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();
        $data =  $this->put_data;

        $results = $ItemAttach->add_data($data);
        $this->jsonReturn($results);
    }

    //删除明细附件
    public function delItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();
        $data =  $this->put_data;

        $results = $ItemAttach->delete_data($data);
        $this->jsonReturn($results);
    }
}