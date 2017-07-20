<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/7/20
 * Time: 14:46
 */
class CentercreditController extends ShopMallController
{
    private $input;
    public function __init()
    {
        $this->input = json_decode(file_get_contents("php://input"), true);
    }

    /**
     * 企业/银行信息新建 - 门户
     * @author klp
     */
    public function createAction()
    {
        $buyerModel = new BuyerModel();
        $result1 = $buyerModel->createInfo($this->user,$this->input);
        $buyerRegInfo = new BuyerreginfoModel();
        $result2 = $buyerRegInfo->createInfo($this->user,$this->input);
        if($result1 && $result2){
            $data = array(
                'code' => 1,
                'message' => '成功',
                'data' => $result2
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','失败');
        }
        exit;
    }

    /**
     * 会员授信列表
     * @author klp
     */
    public function listAction()
    {
        $buyerModel = new BuyerModel();
        $result = $buyerModel->getListCredit($this->input);
        $this->returnInfo($result);
    }

    /**
     * 采购商企业银行信息
     * @author klp
     */
    public function getBuyerInfoAction()
    {
        $buyerModel = new BuyerModel();
        $result = $buyerModel->getBuyerInfo($this->input);
        $this->returnInfo($result);
    }

    /**
     * 查看审核信息
     * @author klp
     */
    public function getCheckInfoAction()
    {
        $BuyerappapprovalModel = new BuyerappapprovalModel();
        $result = $BuyerappapprovalModel->getCheckInfo($this->input);
        $this->returnInfo($result);
    }

    /**
     * 审核会员授信(待完善,触发中信保审核)
     * @author klp
     */
    public function checkAction()
    {
        //获取当前用户信息
        $userInfo = getLoinInfo();
        $this->input['approved_by'] = $userInfo['name'];
        $BuyerappapprovalModel = new BuyerappapprovalModel();
        $result = $BuyerappapprovalModel->checkCredit($this->input);
        $this->returnInfo($result);
    }

    //统一回复调用方法
    function returnInfo($result){
        if($result){
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