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
        $this->put_data = json_decode(file_get_contents("php://input"), true);
        //   parent::__init();
    }

    //询价单列表
    public function getListAction(){
        $inquiry = new InquiryModel();
        //$data = $inquiry->getlist($this->put_data);
        //$where['start_time'] = '2017-06-15 00:00:00';
        //$where['end_time'] = '2017-06-17 00:00:00';
        $where['created_by'] = 'zyl';
        $data = $inquiry->getlist($where);
        //var_dump($data);die;
        if(!empty($data)){
            $this->respon(1,$data);
        }else{
            $this->respon(0,'读取列表为空');
        }
    }

    //询价单详情
    public function infoAction() {
        $inquiry = new InquiryModel();
        $inquiry_no = '10001';
        $info = $inquiry->info($inquiry_no);
        var_dump($info);die;
        if(!empty($info)){
            $this->respon(1,$data);
        }else{
            $this->respon(0,'没有找到相关信息！');
        }
    }

    //添加询价单
    public function addAction(){
        $inquiry = new InquiryModel();

    }

    //删除询价单
    public function deleteAction() {

        $inquiry = new InquiryModel();
        //$inquiry_no = $this->put_data['inquiry_no'];
        $inquiry_no = '10001';
        $rs = $inquiry->delete_data($inquiry_no);
        var_dump($rs);die;
        if(!empty($rs)){
            $this->respon(1,$data);
        }else{
            $this->respon(0,'没有找到相关信息！');
        }

    }

    //添加附件
    public function addAttachAction() {

        $attach = new InquiryAttachModel();
        if($_GET['inquiry_no']){
            $data['inquiry_no'] = $this->getRequest()->getQuery('inquiry_no');
        }else{
            $this->respon(0,'询单号不存在！');
        }
        if($_GET['attach_type']){
            $data['attach_type'] = $this->getRequest()->getQuery('attach_type');
        }
        if($_GET['attach_name']){
            $data['attach_name'] = $this->getRequest()->getQuery('attach_name');
        }
        if($_GET['attach_url']){
            $data['attach_url'] = $this->getRequest()->getQuery('attach_url');
        }

        $rs = $attach->add_data($data);
        //var_dump($rs);die;
        if(!empty($rs)){
            $this->respon(1,$rs);
        }else{
            $this->respon(0,'保存失败！');
        }

    }

    //删除附件
    public function delAttachAction() {

        $attach = new InquiryAttachModel();
        if($_GET['inquiry_no']){
            $data['inquiry_no'] = $this->getRequest()->getQuery('inquiry_no');
        }else{
            $this->respon(0,'询单号不存在！');
        }
        if($_GET['id']){
            $data['id'] = $this->getRequest()->getQuery('id');
        }else{
            $this->respon(0,'删除的附件不存在！');
        }

        $rs = $attach->delete_data($data);
        if(!empty($rs)){
            $this->respon(1,'删除成功！');
        }else{
            $this->respon(0,'删除失败！');
        }
    }

    //添加明细
    public function addItemAction() {

        $attach = new InquiryAttachModel();
        if($_GET['inquiry_no']){
            $data['inquiry_no'] = $this->getRequest()->getQuery('inquiry_no');
        }else{
            $this->respon(0,'询单号不存在！');
        }
        if($_GET['sku']){
            $data['sku'] = $this->getRequest()->getQuery('sku');
        }
        if($_GET['model']){
            $data['model'] = $this->getRequest()->getQuery('model');
        }
        if($_GET['spec']){
            $data['spec'] = $this->getRequest()->getQuery('spec');
        }
        if($_GET['brand']){
            $data['brand'] = $this->getRequest()->getQuery('brand');
        }
        if($_GET['quantity']){
            $data['quantity'] = $this->getRequest()->getQuery('quantity');
        }
        if($_GET['unit']){
            $data['unit'] = $this->getRequest()->getQuery('unit');
        }
        if($_GET['description']){
            $data['description'] = $this->getRequest()->getQuery('description');
        }

        $rs = $attach->add_data($data);
        //var_dump($rs);die;
        if(!empty($rs)){
            $this->respon(1,$rs);
        }else{
            $this->respon(0,'保存失败！');
        }

    }

    //删除附件
    public function delItemAction() {

        $attach = new InquiryAttachModel();
        if($_GET['inquiry_no']){
            $data['inquiry_no'] = $this->getRequest()->getQuery('inquiry_no');
        }else{
            $this->respon(0,'询单号不存在！');
        }
        if($_GET['id']){
            $data['id'] = $this->getRequest()->getQuery('id');
        }else{
            $this->respon(0,'删除的附件不存在！');
        }

        $rs = $attach->delete_data($data);
        if(!empty($rs)){
            $this->respon(1,'删除成功！');
        }else{
            $this->respon(0,'删除失败！');
        }
    }

    //添加明细

    //输出JSON格式
    protected function respon($code=0, $data, $type = 'JSON') {

        $result['error_code'] = $code;
        if( $code ){
            $result['data'] = $data;
        }else{
            $result['message'] = $data;
        }
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($result));

    }
}