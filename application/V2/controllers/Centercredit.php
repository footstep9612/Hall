<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/7/20
 * Time: 14:46
 */

class CentercreditController extends PublicController{
//class CentercreditController extends Yaf_Controller_Abstract{
    private $input;
    public function __init(){
//        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 会员授信列表
     * @author klp
     */
    public function listAction(){
        $buyerModel = new BuyerModel();
        $result = $buyerModel->getListCredit($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * 企业/银行信息新建/编辑 - 门户通用
     * @author klp
     */
    public function editAction(){
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
        ];*/
        $buyerModel = new BuyerModel();
        $result = $buyerModel->editInfo($this->user,$this->put_data);
        $this->returnInfo($result);
    }

    /**
     * 采购商企业信息
     * @pararm  buyer_id(采购商编号)
     * @return array
     * @author klp
     */
    public function getBuyerInfoAction(){
        $buyerModel = new BuyerModel();
        $result = $buyerModel->buyerInfo();
        $this->returnInfo($result);
    }
    /**
     * 采购商银行信息
     * @pararm  buyer_id(采购商编号)
     * @return array
     * @author klp
     */
    public function getBuyerBankInfoAction(){
        $buyerModel = new BuyerBankInfoModel();
        $result = $buyerModel->getBuyerBankInfo();
        $this->returnInfo($result);
    }

    /**
     * 查看审核信息
     * @pararm  buyer_id(采购商编号)
     * @author klp
     */
    public function getApprovelInfoAction(){
        $BuyerCreditLogModel = new BuyerCreditLogModel();
        $result = $BuyerCreditLogModel->getInfo();
        $this->returnInfo($result);
    }

    /**
     * 审核会员授信(待完善,触发中信保审核)
     * @author klp
     */
    public function checkAction(){
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $this->put_data['checked_by'] = $userInfo['id'];
        $BuyerCreditLogModel = new BuyerCreditLogModel();
        $result = $BuyerCreditLogModel->checkCredit($this->put_data);
        if($BuyerCreditLogModel::STATUS_APPROVED == $result['in_status']){
            //易瑞通过,触发信保审核
            $SinoSure = new Edi();
            $buyerModel = new BuyerModel();          //企业信息申请
            $resultBuyer = $buyerModel->buyerInfo($this->put_data);
            $resBuyer = $SinoSure->EdiBuyerCodeApply($resultBuyer);
            if($resBuyer['code'] != 1) {
                jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
            }
            $buyerModel = new BuyerBankInfoModel();  //银行信息申请
            $resultBank = $buyerModel->getBuyerBankInfo($this->put_data);
            $resBank['buyer_no'] = $resultBuyer['buyer_no'];
            $resBank = $SinoSure->EdiBankCodeApply($resultBank);
            if($resBank['code'] != 1) {
                jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
            }
        }
        $this->returnInfo($result);

    }



    //统一回复调用方法
    function returnInfo($result){
        if($result && !empty($result)){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
        }
        exit;
    }
}