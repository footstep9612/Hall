<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MemberCenterController
 * 2017/6/26
 * @author klp
 */
class MemberCenterController extends ShopMallController
{

    public function __init()
    {
        //   parent::__init();
    }

    /**
 * 采购商个人信息中心查询
 * @author klp
 */
    public function getUserInfoAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $buyerModel = new BuyerModel();
        $result = $buyerModel->getInfo($data);
        if(!empty($result)){
            $data = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','获取失败');
        }
        exit;
    }
    /**
     * 个人信息中心更新保存
     * @author klp
     */
    public function upUserInfoAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['customer_id'])) {
            $where['customer_id'] = $data['customer_id'];
        } else {
            jsonReturn('','-1001','参数[customer_id]不能为空');
        }
        $buyerAccount = new BuyerAccountModel();
        $result1 = $buyerAccount->update_data($data,$where);
        $buyer = new BuyerModel();
        $result2 = $buyer->update_data($data,$where);
        $buyerAddress = new BuyerAddressModel();
        $result3 = $buyerAddress->update_data($data,$where);
        if($result1 && $result2 && $result3){
            jsonReturn('',1,'保存成功');
        }else{
            jsonReturn('','-1002','保存失败');
        }
        exit;
    }
    /**
     * 源密码校验
     * @author klp
     */
    public function checkOldPwdAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $buyerAccount = new BuyerAccountModel();
        $result = $buyerAccount->checkPassword($data);
        if($result){
            jsonReturn('',1,'原密码输入正确');
        }else{
            jsonReturn('','-1003','原密码输入错误');
        }
        exit;
    }
    /**
     * 新密码校验
     * @author klp
     */
    public function checkNewPwdAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $result = preg_match("/(?![^a-zA-Z0-9]+$)(?![^a-zA-Z/D]+$)(?![^0-9/D]+$).{8,12}$/",$data['password']);
        if($result){
            jsonReturn('',1,'密码格式正确');
        }else{
            jsonReturn('','-1003','密码格式错误');
        }
        exit;
    }
    /**
     * 修改密码
     * @author klp
     */
    public function upPasswordAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $buyerAccount = new BuyerAccountModel();
        $result = $buyerAccount->update_pwd($data);
        if($result){
            jsonReturn('',1,'修改密码成功');
        }else{
            jsonReturn('','-1002','修改密码失败');
        }
        exit;
    }

    /**
     * 个人会员等级服务详情
     * @author klp
     */
    public function getServiceAction()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $BuyerModel = new BuyerModel();
        $result = $BuyerModel->getService($data);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '获取成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','获取失败');
        }
        exit;
    }

    /**
     * 会员等级服务详情列表
     * @author klp
     */
    public function ServiceInfoAction(){
        $data = json_decode(file_get_contents("php://input"), true);
        $MemberBizServiceModel = new MemberBizServiceModel();
        $result = $MemberBizServiceModel->getVipService($data);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1002','获取失败');
        }
        exit;
    }
}