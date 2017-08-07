<?php

trait QuoteBizlineHelper{

    static public function getInquiryInfoFields()
    {
        return [
            'id',//询单id
            'serial_no',//流程编码
            'status',//项目状态
            'agent_id',//当前经办人,这个字段跟employee表关联获取名字
            'buyer_id',//客户编码 跟采购商表关联获取相关字段信息
            'buyer_name',//客户名称
            //所属地区
            'country_bn',//国家
            'pm_id',//项目经理 这个字段跟employee表关联获取名字
            'created_by',//询单创建人
            'inquiry_no',//项目代码
            'project_name',//项目名称
            'quote_deadline',//预计报价时间
            'bid_flag',//是否投标
            'kerui_flag',//科瑞设备所用配件
            'payment_mode',//付款方式
            'trade_terms_bn',//贸易术语
            'trans_mode_bn',//运输方式
            'cur_bn',//报价币种
            'from_country',//起运国
            'from_port',//起运港
            'dispatch_place',//发运起始地
            'to_country',//目的国
            'to_port',//目的港
            'project_basic_info',//项目背景描述
            'quote_notes',//报价备注
            'adhoc_request'//客户检验要求
        ];
    }

    static public function restoreInqiryInfo(array $inquiry){
        //市场经办人
        $agent = Z('Employee')->where(['id'=>$inquiry['agent_id']])->getField('name');
        if ($agent){
            $inquiry['agent_name'] = $agent;
            unset($inquiry['agent_id']);
        }
        //项目经理
        $productManager = Z('Employee')->where(['id'=>$inquiry['pm_id']])->getField('name');
        if ($productManager){
            $inquiry['pm_name'] = $productManager;
            unset($inquiry['pm_id']);
        }

        return $inquiry;

    }

    /**
     * 重组划分产品线数据
     * @param $param    条件
     * @return array    重组后的结构
     */
    static public function setPartitionBizlineFields($param){
        $data = [];
        $data['quote_id'] = $param['quote_id'];
        $data['inquiry_id'] = $param['inquiry_id'];
        $data['bizline_id'] = $param['bizline_id'];
        $data['created_at'] = date('Y-m-d H:i:s');
        //$data['biz_agent_id'] 需要去inquiry表读取agent_id字段
        $inquiryModel = new InquiryModel();
        $data['biz_agent_id'] = $inquiryModel->where(['id'=>$param['inquiry_id']])->getField('agent_id');

        return $data;
    }
}