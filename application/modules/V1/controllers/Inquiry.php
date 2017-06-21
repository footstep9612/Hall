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

    //返回询价单号
    public function getInquiryNoAction() {
        $data['inquiryno'] = $this->getInquirySerialNo();
        if(!empty($data)){
            $this->respon(0,$data);
        }else{
            $this->respon('-101','生成询单号错误！');
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
            $this->respon(0,$data);
        }else{
            $this->respon('-101','没有找到相关信息！');
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
            $this->respon(0,$info);
        }else{
            $this->respon('-101','没有找到相关信息！');
        }
    }

    //添加询价单
    public function addAction(){
        $inquiry = new InquiryModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $inquiry->add_data($data);
        if(!empty($id)){
            $this->respon(0,'添加成功！');
        }else{
            $this->respon('-101','添加失败！');
        }
    }

    //修改询价单
    public function updateAction(){
        $inquiry = new InquiryModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $inquiry->update_data($data);
        if(!empty($id)){
            $this->respon(0,"修改成功！");
        }else{
            $this->respon('-101','添加失败！');
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
            $this->respon(0,'删除成功！');
        }else{
            $this->respon('-101','删除失败！');
        }

    }

    //附件列表
    public function getListAttachAction() {
        $attach = new InquiryAttachModel();
        $where = json_decode(file_get_contents("php://input"), true);

        $data = $attach->getlist($where);
        //var_dump($data);die;
        if(!empty($data)){
            $this->respon(0,$data);
        }else{
            $this->respon('-101','没有找到相关信息！');
        }
    }

    //添加附件
    public function addAttachAction() {
        $attach = new InquiryAttachModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $attach->add_data($data);
        //var_dump($id);die;
        if(!empty($id)){
            $this->respon(0,$data);
        }else{
            $this->respon(0,'保存失败！');
        }

    }

    //删除附件
    public function delAttachAction() {
        $attach = new InquiryAttachModel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $attach->delete_data($data);
        if(!empty($id)){
            $this->respon(0,'删除成功！');
        }else{
            $this->respon('-101','删除失败！');
        }
    }

    //明细列表
    public function getListItemAction() {
        $Item = new InquiryItemodel();

        $where = json_decode(file_get_contents("php://input"), true);

        $data = $Item->getlist($where);
        //var_dump($data);die;
        if(!empty($data)){
            $this->respon(0,$data);
        }else{
            $this->respon('-101','没有找到相关信息！');
        }
    }

    //添加明细
    public function addItemAction() {
        $Item = new InquiryItemodel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $Item->add_data($data);
        //var_dump($rs);die;
        if(!empty($id)){
            $this->respon(0,"添加成功！");
        }else{
            $this->respon('-101','保存失败！');
        }

    }

    //删除附件
    public function delItemAction() {
        $Item = new InquiryItemodel();
        $data = json_decode(file_get_contents("php://input"), true);

        $id = $Item->delete_data($data);
        if(!empty($id)){
            $this->respon(0,'删除成功！');
        }else{
            $this->respon('-101','删除失败！');
        }
    }

    //输出JSON格式
    protected function respon($code=0, $data, $type = 'JSON') {

        $result['code'] = $code;
        if( $code == 0 ){
            $result['data'] = $data;
        }else{
            $result['message'] = $data;
        }
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($result));

    }
}