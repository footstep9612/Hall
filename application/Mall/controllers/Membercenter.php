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
class MembercenterController extends PublicController {

    public function init() {
        //$this->token = false;
        parent::init();
    }

    /**
     * 采购商个人信息查询
     * @author klp
     */
    public function getUserInfoAction() {
        $buyer_data = $this->getPut();
        $buyerModel = new BuyerAccountModel();
        $this->user['buyer_id'] = 1;//测试  后期去掉哦
        $result = $buyerModel->getinfo($this->user);
        if (!empty($result)) {
            jsonReturn($result, 1, 'success!');
        } else {
            jsonReturn('', '-1002', 'failed!');
        }
        exit;
    }

    /**
     * 个人信息中心更新保存
     * @author klp
     */
    public function upUserInfoAction() {
        $buyer_data = $this->getPut();
        $where['id'] = $this->user['buyer_id'];
        $lang = $buyer_data['lang'] ? $buyer_data['lang'] : 'en';
        $buyerModel = new BuyerModel();
        if(isset($buyer_data['name']) && !empty($buyer_data['name'])) {
            $checkname = $buyerModel->where("name='" . $buyer_data['name'] . "' AND deleted_flag='N' AND id != ".$where['id'])->find();
            if ($checkname) {
                jsonReturn('', -125,  ShopMsg::getMessage('-125',$lang));
            }
        }
        $result = $buyerModel->update_data($buyer_data, $where);

        if ($result !==false) {
            jsonReturn('', 1, 'success!');
        } else {
            jsonReturn('', '-1002', 'failed!');
        }
        exit;
    }



    /**
     * 源密码校验
     * @author klp
     */
    public function checkOldPwdAction() {
        $buyerAccount = new BuyerAccountModel();
        $result = $buyerAccount->checkPassword($this->getPut());
        if ($result) {
            jsonReturn('', 1, '原密码输入正确!');
        } else {
            jsonReturn('', '-1003', '原密码输入错误!');
        }
        exit;
    }

    /**
     * 修改密码
     * @author klp
     */
    public function upPasswordAction() {
        $data = $this->getPut();
        $buyerAccount = new BuyerAccountModel();
        $data['buyer_id'] = $this->user['buyer_id'];
        $result = $buyerAccount->checkPassword($data);
        if ($result) {
            $res = $buyerAccount->update_pwd($data);
            if ($res) {
                jsonReturn('', 1, '修改密码成功!');
            } else {
                jsonReturn('', '-1002', '修改密码失败!');
            }
        } else {
            jsonReturn('', '-1003', '原密码输入错误!');
        }
    }

    /**
     * 采购商授信信息详情
     * @time 2017-9-8
     * @author klp
     */
    public function buyerCerditInfoAction() {
        $BuyerModel = new BuyerModel();
        $result = $BuyerModel->buyerCerdit($this->user);
        if ($result) {
            $data = array(
                'code' => 1,
                'message' => '获取成功',
                'data' => $result
            );
            jsonReturn($data);
        } else {
            jsonReturn('', '-1002', '获取失败');
        }
        exit;
    }

    /**
     * 采购商授信额度信息列表
     * @time 2017-9-14
     * @author klp
     */
    public function OrderCreditListAction() {
        $data = json_decode(file_get_contents("php://input"), true);

        list($start_no, $pagesize) = $this->_getPage($data);
        $OrderLog = new OrderLogModel();
        list($result, $count) = $OrderLog->CerditList($this->user, $start_no, $pagesize);
        if (!empty($result)) {
            $datajson['code'] = 1;
            $datajson['count'] = $count;
            $datajson['data'] = $result;
        } elseif ($result === null) {
            $datajson['code'] = -1002;
            $datajson['count'] = 0;
            $datajson['message'] = '参数错误!';
        } else {
            $datajson['code'] = -104;
            $datajson['count'] = 0;
            $datajson['message'] = '失败!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 采购商负责人
     * @time 2017-9-14
     * @author klp
     */
    public function agentlistAction() {
        $where['buyer_id'] = $this->user['buyer_id'];
        $model = new BuyerAgentModel();
        $res = $model->getlist($where);
        if (!empty($res)) {
            $datajson['code'] = 1;
            $datajson['data'] = $res;
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据获取失败!';
        }
        $this->jsonReturn($datajson);
    }

    /**
     * 会员等级列表
     * @time 2017-10-25
     * @author klp
     */
    public function listLevelAction() {

        $model = new BuyerLevelModel();
        $res = $model->getlist();
        if (!empty($res)) {
            $datajson['code'] = 1;
            $datajson['data'] = $res;
            $datajson['message'] = '成功';
        } else {
            $datajson['code'] = -104;
            $datajson['data'] = "";
            $datajson['message'] = '数据操作失败!';
        }
        $this->jsonReturn($datajson);
    }

    // 发送激活邮件
    public function sendActiveEmailAction() {

        $data = json_decode(file_get_contents("php://input"), true);
        if (!empty($data['email'])) {
            $arr['email'] = $data['email'];
        } else {
            jsonReturn('', -101, '邮箱不可以为空!');
        }
        $arr['key'] = md5(uniqid());
        redisHashSet('mall_active_email', $arr['key'], $this->user['buyer_id'], 86400);
        $config_obj = Yaf_Registry::get("config");
        $config_shop = $config_obj->shop->toArray();
        $email_arr['url'] = $config_shop['url'];
        $email_arr['key'] = $arr['key'];
        $body = $this->getView()->render('login/active_email_en.html', $email_arr);
        $title = 'Erui.com';
        $res = send_Mail($arr['email'], $title, $body);
        if ($res['code'] == 1) {
            jsonReturn('', 1, '发送成功');
        } else {
            jsonReturn('', -104, $res['msg']);
        }
    }

    //验证邮件
    public function checkActiveEmailAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $lang = $data['lang'] ? $data['lang'] : 'en';
        if (empty($data['key'])) {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang));
        }
        if (redisHashExist('mall_active_email', $data['key'])) {
            $buyer_id = redisHashGet('mall_active_email', $data['key']);
            $buyer_account_model = new BuyerAccountModel();
            $user_arr['status'] = 'VALID';
            $check = $buyer_account_model->update_data($user_arr, ['buyer_id' => $buyer_id]);
            if ($check) {
                redisHashDel('mall_active_email', $data['key']);
                jsonReturn('', 1, 'success!');
            } else {
                jsonReturn('', -131, 'failed!');
            }
        } else {
            jsonReturn('', -121, ShopMsg::getMessage('-121', $lang));
        }
    }


    /**
     * 分页处理
     * @param array $condition 条件
     * @return array
     * @author zyg
     *
     */
    protected function _getPage($condition) {
        $pagesize = 10;
        $start_no = 0;
        if (isset($condition['pagesize'])) {
            $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
        }
        if (isset($condition['current_no'])) {
            $start_no = intval($condition['current_no']) > 0 ? (intval($condition['current_no']) * $pagesize - $pagesize) : 0;
        }
        return [$start_no, $pagesize];
    }


}
