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

    //询价单列表
    public function getListAction(){
        $inquiry = new InquiryModel();
        $where = json_decode(file_get_contents("php://input"), true);
        //var_dump($where);die;
        //$where['created_by'] = 'zyl';
        $data = $inquiry->getlist($where);
        //var_dump($data);die;
        if(!empty($data)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        }else{
            $this->setCode('-101');
            $this->setMessage('没有找到相关信息!');
            $this->jsonReturn();
        }
    }

    //询价单详情
    public function getInfoAction() {
        $inquiry = new InquiryModel();
        $where = json_decode(file_get_contents("php://input"), true);
        //$where['inquiry_no'] = '10001';
        $info = $inquiry->getinfo($where);
        //var_dump($info);die;
        if(!empty($info)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($info);
        }else{
            $this->setCode('-101');
            $this->setMessage('没有找到相关信息!');
            $this->jsonReturn();
        }
    }

    //添加询价单
    public function addAction(){
        $inquiry = new InquiryModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $inquiry->add_data($data);
        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('添加失败!');
            $this->jsonReturn();
        }
    }

    //修改询价单
    public function updateAction(){
        $inquiry = new InquiryModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $inquiry->update_data($data);
        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('修改失败!');
            $this->jsonReturn();
        }
    }

    //删除询价单
    public function deleteAction() {
        $inquiry = new InquiryModel();
        $where = json_decode(file_get_contents("php://input"), true);
        //$where['inquiry_no'] = '10001';
        $id = $inquiry->delete_data($where);
        //var_dump($id);die;
        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('删除失败!');
            $this->jsonReturn();
        }

    }

    //附件列表
    public function getListAttachAction() {
        $attach = new InquiryAttachModel();
        $where = json_decode(file_get_contents("php://input"), true);

        $data = $attach->getlist($where);
        //var_dump($data);die;
        if(!empty($data)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        }else{
            $this->setCode('-101');
            $this->setMessage('没有找到相关信息!');
            $this->jsonReturn();
        }
    }

    //添加附件
    public function addAttachAction() {
        $attach = new InquiryAttachModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $attach->add_data($data);
        //var_dump($id);die;
        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('保存失败!');
            $this->jsonReturn();
        }

    }

    //删除附件
    public function delAttachAction() {
        $attach = new InquiryAttachModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $attach->delete_data($data);
        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('删除失败!');
            $this->jsonReturn();
        }
    }

    //明细列表
    public function getListItemAction() {
        $Item = new InquiryItemModel();

        $where = json_decode(file_get_contents("php://input"), true);

        $data = $Item->getlist($where);
        //var_dump($data);die;
        if(!empty($data)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        }else{
            $this->setCode('-101');
            $this->setMessage('没有找到相关信息!');
            $this->jsonReturn();
        }
    }

    //添加明细
    public function addItemAction() {
        $Item = new InquiryItemModel();
        $data = json_decode(file_get_contents("php://input"), true);

        //$data['inquiry_no'] = 'INQ_20170607_00003';
        //$data['quantity'] = 100;

        $id = $Item->add_data($data);

        //var_dump($id);die;

        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('保存失败!');
            $this->jsonReturn();
        }

    }

    //删除附件
    public function delItemAction() {
        $Item = new InquiryItemModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $Item->delete_data($data);
        if(!empty($id)){
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn();
        }else{
            $this->setCode('-101');
            $this->setMessage('删除失败!');
            $this->jsonReturn();
        }
    }

}