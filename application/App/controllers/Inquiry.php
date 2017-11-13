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
        //parent::init();

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

}

