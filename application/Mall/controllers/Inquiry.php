<?php

/**
 * User: zhangyuliang
 * desc: 询价单控制器
 * Date: 2017/6/27
 * Time: 15:30
 */
class InquiryController extends PublicController {

    public function init() {
        $this->token = false;
        parent::init();
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


    //附件列表
    public function getListAttachAction() {
        $attach = new InquiryAttachModel();
        $where = $this->getPut();

        $results = $attach->getlist($where);
        //var_dump($data);die;
        $this->jsonReturn($results);
    }

    //明细列表
    public function getListItemAction() {
        $Item = new InquiryItemModel();

        $where = $this->getPut();

        $results = $Item->getlist($where);
        $this->jsonReturn($results);
    }

    /* id转换为姓名
         * @author  zhongyg
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

}
