<?php
/**
 * Description of ExportTariffController
 * @author  Link
 * @date    2017-11-29
 * @desc    客户拜访记录
 */
class BuyervisitController extends PublicController {

    //put your code here
    public function init() {
        parent::init();
    }
//    private function crmUserRole($user_id,$access){ //获取角色
//        $role=new RoleUserModel();
//        $arr=$role->crmGetUserRole($user_id);
//        if(in_array($access,$arr)){
//            $admin=1;   //市场专员
//        }else{
//            $admin=0;
//        }
//        return $admin;
//    }
    public function getUserRole(){
        $arr=[];
        $data=$this->user;
        $arr['role']=$data['role_no'];
        $arr['country']=$data['country_bn'];
        return $arr;
    }
    /**
     * Description of 列表
     * @author  link
     * @date    2017-11-29
     */
    public function listAction() {
        $data = $this->getPut();
        $visit_model = new BuyerVisitModel();
        $data['admin']=$this->getUserRole();
        $data['created_by']=$this->user['id'];
        $res = $visit_model->getList($data);
        if($res===false){
            $dataJson['code'] = 1;
            $dataJson['message'] = '无市场区域国家负责人权限';
            $dataJson['data'] = array('total'=>0,'result'=>[]);
        }else{
            $dataJson['code'] = 1;
            $dataJson['message'] = '消息提醒';
            $dataJson['data'] = $res;
        }
        $this->jsonReturn($dataJson);
    }

    /**
     * Description of 详情
     * @author  link
     * @date    2017-11-29
     */
    public function infoAction() {
        $data = $this->getPut();
        $data['lang']=$this->getLang();
        if(!isset($data['id']) || empty($data['id'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'ID不能为空');
        }

        $visit_model = new BuyerVisitModel();
        $arr = $visit_model->getInfoById($data, (isset($data['show_name']) && !empty($data['show_name'])) ? true : false);
        if ($arr !== false) {
            jsonReturn($arr);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * Description of 新增
     * @author  link
     * @date    2017-11-29
     */
    public function createAction() {
        $data = $this->getPut();
        $data['created_by']=$this->user['id'];
        $visit_model = new BuyerVisitModel();
        unset($data['id']);
        $arr = $visit_model->edit($data);
        if ($arr !== false && $arr!='warn') {
            jsonReturn($arr);
        }elseif($arr=='warn'){
            jsonReturn('', 0, L('warn'));
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * Description of 修改
     * @author  link
     * @date    2017-11-29
     */
    public function updateAction() {
        $data = $this->getPut();
        $data['created_by']=$this->user['id'];
        if(!isset($data['id']) || empty($data['id'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'ID不能为空');
        }

        $visit_model = new BuyerVisitModel();
        $arr = $visit_model->edit($data);
        if ($arr !== false) {
            jsonReturn($arr);
        }elseif($arr=='warn'){
            jsonReturn('', 0, L('warn'));
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * Description of 删除级别
     * @author  link
     * @date    2017-11-29
     * @desc    删
     */
    public function deleteAction() {
        $data = $this->getPut();
        if(!isset($data['id']) || empty($data['id'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'ID不能为空');
        }

        $visit_model = new BuyerVisitModel();
        $arr = $visit_model->deleteById($data['id']);
        if ($arr !== false) {
            jsonReturn($arr);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 需求列表
     * @author  link
     * @date    2017-11-30
     */
    public function demadListAction(){
        $data = $this->getPut();
        $data['admin']=$this->user;
        $data['created_by']=$this->user['id'];
        $visit_model = new BuyerVisitModel();
        $res = $visit_model->getDemadList($data,$this->getLang());
        if($res===false){
            $dataJson['code'] = 1;
            $dataJson['message'] = '无市场区域国家负责人权限';
            $dataJson['data'] = array('total'=>0,'result'=>[]);
        }else{
            $dataJson['code'] = 1;
            $dataJson['message'] = '消息提醒';
            $dataJson['data'] = $res;
        }
        $this->jsonReturn($dataJson);
    }

    /**
     * 反馈列表
     */
    public function replyAction(){
        $data = $this->getPut();
        if(!isset($data['visit_id']) || empty($data['visit_id'])){
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'ID不能为空');
        }

        $visit_model = new BuyerVisitReplyModel();
        $arr = $visit_model->getReplyById($data);
        if ($arr !== false) {
            jsonReturn($arr);
        }else{
            jsonReturn('', ErrorMsg::FAILED);
        }
    }

    /**
     * 提交反馈内容
     */
    public function replayAddAction()
    {
        $data = $this->getPut();
        if (!isset($data['visit_id']) || empty($data['visit_id'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM, 'ID不能为空');
        }

        $visit_model = new BuyerVisitReplyModel();
        $arr = $visit_model->edit($data);
        if ($arr !== false) {
            jsonReturn($arr);
        } else {
            jsonReturn('', ErrorMsg::FAILED);
        }
    }
     /*
     * CRM模块
     * 统计:拜访记录excel 导出
     * wangs
     */
    public function exportStatisVisitAction(){
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $data['admin']=$this->getUserRole();
        $model = new BuyerVisitModel();
        $arr = $model->exportStatisVisit($data);
        if($arr === false){
            $dataJson=array(
                'code'=>0,
                'message'=>'暂无数据'
            );
            $this->jsonReturn($dataJson);
        }
        if(!empty($arr)){
            $dataJson=array(
                'code'=>1,
                'message'=>'Success',
                'name'=>$arr['name'],
                'url'=>$arr['url']
            );
            $model = new BuyerExcelModel();
            $model->saveExcel($arr['name'],$arr['url'],$created_by);
        }
        $this->jsonReturn($dataJson);
    }
    //导出客户调研报告-wangs
    public function exportStatisReportAction(){
        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $data['admin']=$this->getUserRole();
        $model = new BuyerVisitModel();
        $arr = $model->exportStatisVisit($data,true);
        if($arr === false){
            $dataJson=array(
                'code'=>0,
                'message'=>'暂无数据'
            );
            $this->jsonReturn($dataJson);
        }
        if(!empty($arr)){
            $dataJson=array(
                'code'=>1,
                'message'=>'Success',
                'name'=>$arr['name'],
                'url'=>$arr['url']
            );
            $model = new BuyerExcelModel();
            $model->saveExcel($arr['name'],$arr['url'],$created_by);
        }
        $this->jsonReturn($dataJson);
    }
    //获取用户的角色
//    public function getUserRole(){
//        $config = \Yaf_Application::app()->getConfig();
//        $ssoServer=$config['ssoServer'];
//        $token=$_COOKIE['eruitoken'];
//        $opt = array(
//            'http'=>array(
//                'method'=>"POST",
//                'header'=>"Content-Type: application/json\r\n" .
//                    "Cookie: ".$_COOKIE."\r\n",
//                'content' =>json_encode(array('token'=>$token))
//
//            )
//        );
//        $context = stream_context_create($opt);
//        $json = file_get_contents($ssoServer,false,$context);
//        $info=json_decode($json,true);
//
//        $arr['role']=$info['role_no'];
//        if(!empty($info['country_bn'])){
//            $countryArr=[];
//            foreach($info['country_bn'] as $k => $v){
//                $countryArr[]="'".$v."'";
//            }
//            $countryStr=implode(',',$countryArr);
//        }
//        $arr['country']=$countryStr;
//        return $arr;
//    }
}
