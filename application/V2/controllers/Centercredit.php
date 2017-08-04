<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/7/20
 * Time: 14:46
 */

//class CentercreditController extends PublicController
class CentercreditController extends Yaf_Controller_Abstract
{
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
     * 采购商企业信息
     * @pararm  buyer_id(采购商编号)
     * @return array
     * @author klp
     */
    public function getBuyerInfoAction(){
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
    public function getBuyerBankInfoAction(){
        $buyerModel = new BuyerBankInfoModel();
        $result = $buyerModel->getBuyerBankInfo($this->put_data);
        $this->returnInfo($result);
    }

    /**
     * 查看审核信息
     * @pararm  buyer_id(采购商编号)
     * @author klp
     */
    public function getApprovelInfoAction(){
        $BuyerCreditLogModel = new BuyerCreditLogModel();
        $result = $BuyerCreditLogModel->getInfo($this->put_data);
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
            if(!is_object($resBuyer)) {
                jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
            }
            $buyerModel = new BuyerBankInfoModel();  //银行信息申请
            $resultBank = $buyerModel->getBuyerBankInfo($this->put_data);
            $resBank = $SinoSure->EdiBankCodeApply($resultBank);
            if(!is_object($resBank)) {
                jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
            }
        }
        $this->returnInfo($result);

    }

    /**
     * 区域等级会员维护列表(未写)
     * @author klp
     */
    public function gradeListAction(){
        $buyerModel = new BuyerModel();
        $result = $buyerModel->getGradeList($this->put_data);
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