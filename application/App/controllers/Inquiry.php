<?php

/**
 * 询报价APP 询单相关接口类
 * @desc   InquiryController
 * @Author 买买提
 */
class InquiryController extends PublicController
{

    /**
     * 首页信息(统计，轮播，列表[最新3条数据])
     */
    public function homeAction()
    {

        //$request = $this->validateRequestParams();

        $data = [];

        $data['statistics'] = [
            'todayCount'  => 12,
            'totalCount'  => 3435,
            'quotedCount' => 453
        ];

        $data['carousel'] = [
            ['id'=>1,'buyer_code'=>'BC20171107'],
            ['id'=>2,'buyer_code'=>'BC20171108']
        ];

        $data['list'] = [
            ['id'=>1,'buyer_name'=>'易瑞国家','created_at'=>'2017-10-30 01:08:49','serial_no'=>'INQ_20171030_00018','status'=>'BIZ_DISPATCHING','now_agent'=>'买买提']
        ];

        $this->jsonReturn($data);
    }


}

