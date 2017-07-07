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
class MembercenterController extends ShopMallController
{

    /**
 * 采购商个人信息查询
 * @author klp
 */
    public function getUserInfoAction(){

        $buyerModel = new BuyerModel();
        $result = $buyerModel->getInfo($this->user);
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
        if (!empty($this->user['customer_id'])) {
            $where['customer_id'] = $this->user['customer_id'];
        } else {
            jsonReturn('','-1001','参数[customer_id]不能为空');
        }
        $buyerAccount = new BuyerAccountModel();
        $result1 = $buyerAccount->update_data($this->put_data,$where);
        $buyer = new BuyerModel();
        $result2 = $buyer->update_data($this->put_data,$where);
        $buyerAddress = new BuyerAddressModel();
        $result3 = $buyerAddress->update_data($this->put_data,$where);
        if($result1 || $result2 || $result3){
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

        $buyerAccount = new BuyerAccountModel();
        $result = $buyerAccount->checkPassword($this->put_data,$this->user);
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

        $result = preg_match("/(?![^a-zA-Z0-9]+$)(?![^a-zA-Z/D]+$)(?![^0-9/D]+$).{8,12}$/",$this->user['password']);
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
        $buyerAccount = new BuyerAccountModel();
        $result = $buyerAccount->update_pwd($this->put_data,$this->user);
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
        $BuyerModel = new BuyerModel();
        $result = $BuyerModel->getService($this->put_data,$this->user);
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
    public function listServiceAction(){

        $MemberBizServiceModel = new MemberBizServiceModel();
        $result = $MemberBizServiceModel->getVipService($this->put_data,$this->user);
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

    /**
     * 获取付款方式
     * @author klp
     */
    public function payMethodAction(){
        $CurrencyModel = new CurrencyModel();
        $result = $CurrencyModel->getPayMethod();
        if($result){
            $data = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1003','失败');
        }
        exit;
    }

    /**
     * 询单信息国家简称,地区简称
     * @author klp
     */
    public function getInquiryBnAction(){
        $BuyerModel = new BuyerModel();
        $result = $BuyerModel->getInquiryInfo($this->user);
        if($result){
            $data = array(
                'code' => 1,
                'message' => '数据获取成功',
                'data' => $result
            );
            jsonReturn($data);
        }else{
            jsonReturn('','-1003','失败');
        }
        exit;
    }

}