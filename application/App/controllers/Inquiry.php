<?php

/**
 * 询报价APP 询单相关接口类
 * @desc   InquiryController
 * @Author 买买提
 */
class InquiryController extends PublicController
{

    private $inquiryModel;

    public function init()
    {
        parent::init();

        $this->inquiryModel = new InquiryModel();
    }

    /**
     * 首页信息(统计，轮播，列表[最新3条数据])
     */
    public function homeAction()
    {

        //$request = $this->validateRequestParams();

        $data = [];

        $data['statistics'] = [
            'todayCount'  => $this->inquiryModel->getStatisticsByType('TODAY'),
            'totalCount'  => $this->inquiryModel->getStatisticsByType('TOTAL'),
            'quotedCount' => $this->inquiryModel->getStatisticsByType('QUOTED')
        ];

        $data['carousel'] = [
            ['id'=>1,'buyer_code'=>'BC20171107'],
            ['id'=>2,'buyer_code'=>'BC20171108']
        ];

        $data['list'] = $this->inquiryModel->getNewItems($this->user['id']);

        $this->jsonReturn($data);
    }

    /*
     * 创建询价单流程编码
     */

    public function createSerialNoAction()
    {

        $data['serial_no'] = InquirySerialNo::getInquirySerialNo();
        $data['created_by'] = $this->user['id'];
        $this->jsonReturn($this->inquiryModel->addData($data));

    }

    /**
     * 创建询单
     */
    public function updateAction()
    {

        $data = $this->validateRequestParams();
        $data['updated_by'] = $this->user['id'];
        $this->jsonReturn($this->inquiryModel->updateData($data));

    }

    /**
     * 文件上传接口测试
     */
    public function uploadAction()
    {
        $this->display('upload');
    }


    public function listAction()
    {
        $condition = $this->put_data;

        $inquiryModel = new InquiryModel();
        $quoteModel = new QuoteModel();
        $userModel = new UserModel();
        $countryModel = new CountryModel();
        $employeeModel = new EmployeeModel();

        // 市场经办人
        if (!empty($condition['agent_name'])) {
            $agent = $userModel->where(['name' => $condition['agent_name']])->find();
            $condition['agent_id'] = $agent['id'];
        }

        // 当前用户的所有角色编号
        $condition['role_no'] = $this->user['role_no'];

        // 当前用户的所有组织ID
        $condition['group_id'] = $this->user['group_id'];

        $condition['user_id'] = $this->user['id'];

        $inquiryList = $this->inquiryModel->getList_($condition, 'id,serial_no,buyer_name,country_bn,agent_id,quote_id,now_agent_id,created_at,quote_status');

        foreach ($inquiryList as &$inquiry) {
            $country = $countryModel->field('name')->where(['bn' => $inquiry['country_bn'], 'lang' => 'zh', 'deleted_flag' => 'N'])->find();
            $inquiry['country_name'] = $country['name'];
            $agent = $employeeModel->field('name')->where(['id' => $inquiry['agent_id']])->find();
            $inquiry['agent_name'] = $agent['name'];
            $quoter = $employeeModel->field('name')->where(['id' => $inquiry['quote_id']])->find();
            $inquiry['quote_name'] = $quoter['name'];
            $nowAgent = $employeeModel->field('name')->where(['id' => $inquiry['now_agent_id']])->find();
            $inquiry['now_agent_name'] = $nowAgent['name'];
            $quote = $quoteModel->field('logi_quote_flag')->where(['inquiry_id' => $inquiry['id']])->find();
            $inquiry['logi_quote_flag'] = $quote['logi_quote_flag'];
        }

        if ($inquiryList) {
            $res['code'] = 1;
            $res['message'] = '成功!';
            $res['data'] = $inquiryList;
            $res['count'] = $inquiryModel->getCount_($condition);
            $this->jsonReturn($res);
        } else {
            $this->setCode('-101');
            $this->setMessage('失败!');
            $this->jsonReturn();
        }
    }

}

