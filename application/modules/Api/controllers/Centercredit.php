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
     * 企业/银行信息新建/编辑 - 门户通用
     * @author klp
     */
    public function createAction()
    {
        //jsonReturn(1213);
        $buyerModel = new BuyerModel();
        $result = $buyerModel->createInfo($this->user,$this->put_data);
        $this->returnInfo($result);
    }

    /**
     * 采购商企业银行信息
     * @author klp
     */
    public function getBuyerInfoAction()
    {
        $buyerModel = new BuyerModel();
        $result = $buyerModel->getBuyerInfo($this->user);
        $this->returnInfo($result);
    }

    /**
     * 查看审核信息
     * @author klp
     */
    public function getCheckInfoAction()
    {
        $BuyerappapprovalModel = new BuyerappapprovalModel();
        $result = $BuyerappapprovalModel->getCheckInfo($this->user);
        $this->returnInfo($result);
    }

    /**
     * 易瑞审核会员授信(待完善,触发中信保审核)
     * @author klp
     */
//    public function checkAction()
//    {
//        //获取当前用户信息
//        $userInfo = getLoinInfo();
//        $this->input['approved_by'] = $userInfo['name'];
//        $BuyerappapprovalModel = new BuyerappapprovalModel();
//        $result = $BuyerappapprovalModel->checkCredit($this->put_data);
//        $this->returnInfo($result);
//    }

    /**
     * 区域等级会员维护列表(未写)
     * @author klp
     */
    public function gradeListAction()
    {
        $buyerModel = new BuyerModel();
        $result = $buyerModel->getGradeList($this->put_data);
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