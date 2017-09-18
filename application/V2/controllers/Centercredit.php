<?php

/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/7/20
 * Time: 14:46
 */
class CentercreditController extends PublicController {

//class CentercreditController extends Yaf_Controller_Abstract{
    private $input;

    public function __init() {
        parent::init();
//        $this->input = json_decode(file_get_contents("php://input"), true);
        $this->put_data = $this->put_data ? $this->put_data : json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 会员授信列表
     * @author klp
     */
    public function listAction() {
        $buyerModel = new BuyerModel();
        list($result, $count) = $buyerModel->getListCredit($this->put_data);

        if ($result) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->setvalue('count', $count);
            $this->jsonReturn($result);
        } elseif ($result === null) {

            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
        }
    }

    /**
     * 企业/银行信息新建/编辑 - 门户通用
     * @author klp
     */
    public function editAction() {
        /* $this->user['id'] = '111';
          $this->put_data=[
          'name' =>  '151y3',
          'country_code' =>  'usa',
          'province' =>  '16623',
          'social_credit_code' =>  '' ,
          'registered_in' =>  '1266s3' ,
          'official_email' =>  '' ,
          'reg_date' =>  '' ,
          'biz_nature' =>  'State-owned',
          'equitiy' =>  '' ,
          'turnover' =>  '' ,
          'legal_person_name' =>  '' ,
          'official_fax' =>  '' ,
          'official_phone' =>  '' ,
          'official_website' =>  '' ,
          'swift_code' =>  '12q2y63' ,
          'bank_name' =>  '172qw3' ,
          'bank_country_code' =>  '17y376w2' ,
          'bank_address' =>  '1773wq2' ,
          'bank_zipcode' =>  '' ,
          'bank_phone' =>  '' ,
          'bank_fax' =>  '' ,
          'bank_turnover' =>  '' ,
          'bank_profit' =>  '' ,
          'bank_assets' =>  '' ,
          'equity_ratio' =>  '' ,
          'bank_equity_capital' =>  '' ,
          'branch_count_' =>  '' ,
          'bank_employee_count' =>  '' ,
          'created_by_' =>  '' ,
          'bank_remarks' =>  'yui' ,
          'loading' =>  ''
          ]; */
        $buyerModel = new BuyerModel();
        $result = $buyerModel->editInfo($this->user, $this->put_data);
        $this->returnInfo($result);
    }

    /**
     * 采购商企业信息
     * @pararm  buyer_id(采购商编号)
     * @return array
     * @author klp
     */
    public function getBuyerInfoAction() {
        $buyerModel = new BuyerModel();
        $result = $buyerModel->buyerInfo($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * 采购商银行信息
     * @pararm  buyer_id(采购商编号)
     * @return array
     * @author klp
     */
    public function getBuyerBankInfoAction() {
        $buyerModel = new BuyerBankInfoModel();
        $result = $buyerModel->getBuyerBankInfo($this->put_data);
        if ($result === false) {
            jsonReturn('', ErrorMsg::FAILED);
        } else {
            jsonReturn($result);
        }
        //$this->returnInfo($result);
    }

    /**
     * 查看审核信息
     * @pararm  buyer_id(采购商id)
     * @author klp
     */
    public function getApprovelInfoAction() {
        $BuyerCreditLogModel = new BuyerCreditLogModel();
        $result = $BuyerCreditLogModel->getInfo($this->put_data);
        if ($result === false) {
            jsonReturn('', ErrorMsg::FAILED);
        } else {
            jsonReturn($result);
        }
        //$this->returnInfo($result);
    }

    /**
     * 审核会员授信(待完善,触发中信保审核)
     * {"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6Ijk4IiwiZXh0IjoxNDk5MjM2NTE2LCJpYXQiOjE0OTkyMzY1MTYsIm5hbWUiOiJcdTUyMThcdTY2NTYifQ.CpeZKj2ar7OradKomSuMzeIYF6M1ZcWLHw8ko81bDJo","id":"111","status_type":"approved"
      }
     * @author klp
     */
    public function updateAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $BuyerCreditLogModel = new BuyerCreditLogModel();
        if (!empty($data['buyer_id'])) {
            $array_data['buyer_id'] = $data['buyer_id'];
        }else{
            jsonReturn('', -101, '用户名不可以为空!');
        }
        if (!empty($data['credit_granted'])) {
            $array_data['credit_granted'] = $data['credit_granted'];
        }
        if (!empty($data['in_status'])) {
            $array_data['in_status'] = $data['in_status'];
        }else{
            jsonReturn('', -101, '授信状态不能为空!');
        }
        if (!empty($data['checked_at'])) {
            $array_data['checked_at'] = $data['checked_at'];
        }
        if (!empty($data['approved_at'])) {
            $array_data['approved_at'] = $data['approved_at'];
        }
        if (!empty($data['in_remarks'])) {
            $array_data['in_remarks'] = $data['in_remarks'];
        }
        $array_data['checked_by'] = $this->user['id'];
        $info = $BuyerCreditLogModel->getInfo(['buyer_id' => $data['buyer_id']]);
        if($info){
            $result = $BuyerCreditLogModel->update_data($array_data,[ 'buyer_id' => $data['buyer_id'] ]);
        }else{
            $result = $BuyerCreditLogModel->create_data($array_data);
        }
        if($result!==false){
            if($array_data['in_status']=='CREDIT_APPROVED'){
                $buyer_model = new BuyerModel();
                $buyer_data['line_of_credit'] = $data['credit_granted'];
                $buyer_data['credit_available'] = $data['credit_granted'];
                $buyer_where['id'] = $data['buyer_id'];
                $buyer_model->update_data($buyer_data,$buyer_where);
            }
            $datajson['code'] = 1;
            $datajson['message'] = '成功';
            //$result = $BuyerCreditLogModel->update($array_data);
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }
    public function approvelAction() {
//        //获取当前用户信息
//        $userInfo = getLoinInfo();
//        $this->put_data['checked_by'] = $userInfo['id'];
        $BuyerCreditLogModel = new BuyerCreditLogModel();
        $result = $BuyerCreditLogModel->checkCredit($this->put_data);
        if ($BuyerCreditLogModel::STATUS_APPROVED == $result['in_status']) {
            //易瑞通过,触发信保审核
            $SinoSure = new Edi();
            $buyerModel = new BuyerModel();          //企业信息申请
            $resultBuyer = $buyerModel->buyerInfo($this->put_data);
            $resBuyer = $SinoSure->EdiBuyerCodeApply($resultBuyer);
            if ($resBuyer['code'] != 1) {
                jsonReturn('', MSG::MSG_FAILED, MSG::getMessage(MSG::MSG_FAILED));
            }
            $buyerModel = new BuyerBankInfoModel();  //银行信息申请
            $resultBank = $buyerModel->getBuyerBankInfo($this->put_data);
            $resBank['buyer_no'] = $resultBuyer['buyer_no'];
            $resBank = $SinoSure->EdiBankCodeApply($resultBank);
            if ($resBank['code'] != 1) {
                jsonReturn('', MSG::MSG_FAILED, MSG::getMessage(MSG::MSG_FAILED));
            }
        }
        $this->returnInfo($result);
    }
    //统一回复调用方法
    function returnInfo($result) {
        if ($result && !empty($result)) {
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        } else {
            jsonReturn('', '-1002', '失败!');
        }
        exit;
    }

}
