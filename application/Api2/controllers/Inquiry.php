<?php

/**
 * User: zhangyuliang
 * desc: 询价单控制器
 * Date: 2017/6/27
 * Time: 15:30
 */
class InquiryController extends PublicController {

    public function init() {
        parent::init();
    }

    //判断采购商是否经过审核,通过返回市场经办人agent_id
    public function isBuyerApprovedAction(){
        $where['id'] = $this->user['buyer_id'];
        $buyerModel = new BuyerModel();
        $res = $buyerModel->isBuyerApproved($where);
        if($res){
            $this->setCode('1');
            $this->setMessage('通过!');
            $this->jsonReturn($res);
        }else {
            $this->setCode('-101');
            $this->setMessage('未通过!');
            $this->jsonReturn();
        }
    }

    //返回询价单流水号
    public function getInquiryNoAction() {
        $data['serial_no'] = InquirySerialNo::getInquirySerialNo();
        if (!empty($data)) {
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        } else {
            $this->setCode('-101');
            $this->setMessage('生成流水号错误!');
            $this->jsonReturn();
        }
    }

    //获取询单总数
    public function getInquiryCountAction() {
        $inquiry = new InquiryModel();
        $where['buyer_id'] = $this->user['buyer_id'];

        $data['count'] = $inquiry->getcount($where);

        if ($data['count'] > 0) {
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        } else {
            $this->setCode('-101');
            $this->setMessage('没有找到相关信息!');
            $this->jsonReturn($data);
        }
    }

    //添加询价单
    public function addAction() {
        $inquiry = new InquiryModel();
        $data = $this->getPut();

        $inquiryNo = $inquiry->checkInquiryNo($data['inquiry_no']);
        if ($inquiryNo['code'] == 1) {
            $data['buyer_id'] = $this->user['buyer_id'];
            $data['inquirer'] = $this->user['user_name'];
            $data['inquirer_email'] = $this->user['email'];
            $buyerInfo = $this->user['buyer_id'];

            $results = $inquiry->addInquiry($data, $buyerInfo);
            if (!$results) {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            } else {
                $this->setCode(MSG::MSG_SUCCESS);
                $this->jsonReturn();
            }
        } else {
            $results = $inquiryNo;
        }
        $this->jsonReturn($results);
    }

    //询价单列表
    public function getListAction() {
        $inquiry = new InquiryModel();
        $item = new InquiryItemModel();
        $where = $this->getPut();
        $where['buyer_id'] = $this->user['buyer_id'];
        $results = $inquiry->getlist($where);

        foreach ($results['data'] as $key => $val) {
            $test['inquiry_id'] = $val['id'];
            $results['data'][$key]['quantity'] = $item->getcount($test);
        }

        $this->jsonReturn($results);
    }

    //询价单详情
    public function getInfoAction() {
        $inquiry = new InquiryModel();
        $where = $this->getPut();

        $results = $inquiry->getinfo($where);

        if (isset($results['data'])) {
            $data = $results['data'];
            $this->_setAgent($data);
            $results['data'] = $data;
        }

        $this->jsonReturn($results);
    }

    //询价单审核日志详情
    public function getLogAction() {
        $inquiryLogModel = new InquiryCheckLogModel();
        $condition = $this->getPut();

        $data = $inquiryLogModel->getInfo($condition);
        if (!empty($data)) {
            $this->setCode('1');
            $this->setMessage('成功!');
            $this->jsonReturn($data);
        } else {
            $this->setCode('-101');
            $this->setMessage('没有数据!');
            $this->jsonReturn();
        }
    }

    /* id转换为姓名
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   汇率列表
     */

    private function _setAgent(&$arr) {

        if ($arr && $arr['agent_id']) {
            $buyer_model = new EmployeeModel();
            $agent_ids = $arr['agent_id'];

            $usernames = $buyer_model->getUserNamesByUserids($agent_ids, false);
            if ($arr['agent_id'] && isset($usernames[$arr['agent_id']])) {
                $arr['agent'] = $usernames[$arr['agent_id']]['name'];
                $arr['agent_email'] = strval($usernames[$arr['agent_id']]['email']);
            } else {
                $arr['agent'] = '';
                $arr['agent_email'] = '';
            }
        } else {
            $arr['agent'] = '';
            $arr['agent_email'] = '';
        }
    }

    //修改询价单
    public function updateAction() {
        $inquiry = new InquiryModel();
        $data = $this->getPut();

        $results = $inquiry->update_data($data);
        $this->jsonReturn($results);
    }

    //删除询价单
    public function deleteAction() {
        $inquiry = new InquiryModel();
        $where = $this->getPut();
        //$where['inquiry_no'] = '10001';
        $results = $inquiry->delete_data($where);
        $this->jsonReturn($results);
    }

    //附件列表
    public function getListAttachAction() {
        $attach = new InquiryAttachModel();
        $where = $this->getPut();

        $results = $attach->getlist($where);
        //var_dump($data);die;
        $this->jsonReturn($results);
    }

    //添加附件
    public function addAttachAction() {
        $attach = new InquiryAttachModel();
        $data = $this->getPut();

        $results = $attach->addData($data);

        $this->jsonReturn($results);
    }

    //删除附件
    public function delAttachAction() {
        $attach = new InquiryAttachModel();
        $data = $this->getPut();

        $results = $attach->delete_data($data);
        $this->jsonReturn($results);
    }

    //明细列表
    public function getListItemAction() {
        $Item = new InquiryItemModel();

        $where = $this->getPut();

        $results = $Item->getlist($where);
        $this->jsonReturn($results);
    }

    //明细列表
    public function getInfoItemAction() {
        $Item = new InquiryItemModel();

        $where = $this->getPut();

        $results = $Item->getinfo($where);
        $this->jsonReturn($results);
    }

    //添加明细
    public function addItemAction() {
        $Item = new InquiryItemModel();
        $data = $this->getPut();

        $results = $Item->add_data($data);
        $this->jsonReturn($results);
    }

    //删除明细
    public function delItemAction() {
        $Item = new InquiryItemModel();
        $data = $this->getPut();

        $results = $Item->delete_data($data);
        $this->jsonReturn($results);
    }

    //明细附件列表
    public function getListItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();

        $where = $this->getPut();

        $results = $ItemAttach->getlist($where);
        $this->jsonReturn($results);
    }

    //添加明细附件
    public function addItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();
        $data = $this->getPut();

        $results = $ItemAttach->add_data($data);
        $this->jsonReturn($results);
    }

    //删除明细附件
    public function delItemAttachAction() {
        $ItemAttach = new InquiryItemAttachModel();
        $data = $this->getPut();

        $results = $ItemAttach->delete_data($data);
        $this->jsonReturn($results);
    }

}
