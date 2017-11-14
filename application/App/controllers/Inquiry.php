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


    /**
     * 询单列表
     */
    public function listAction()
    {
        $condition = $this->put_data;

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

        //列表类型 list_type
        if (!isset($condition['list_type'])){
            $condition['list_type'] = 'inquiry';
        }

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
            $res['count'] = $this->inquiryModel->getCount_($condition);
            $res['data'] = $inquiryList;
            $this->jsonReturn($res);
        } else {
            $this->setCode('-101');
            $this->setMessage('暂无数据!');
            $this->jsonReturn();
        }
    }

    /**
     * 询单查看接口
     */
    public function detailAction()
    {

        $request = $this->validateRequestParams('id');

        $inquiryFields = 'id,serial_no,buyer_name,quote_status,quote_id,logi_agent_id,from_country,from_port,to_country,to_port';
        $inquiryDetail = $this->inquiryModel->getDetail($request,$inquiryFields);

        $employeeModel = new EmployeeModel();
        $inquiryDetail['quote_agent'] = $employeeModel->where(['id'=>$inquiryDetail['quote_id']])->getField('name');
        $inquiryDetail['logi_agent'] = $employeeModel->where(['id'=>$inquiryDetail['logi_agent_id']])->getField('name');

        $condition = ['inquiry_id'=>$request['id']];

        $quoteInfo = $this->getQuoteDetail($condition);

        $quoteDetail = $this->getRecombineQuoteDetail($quoteInfo);

        $logiDetail = $this->getQuoteLogiFeeDetail($condition);


        $this->jsonReturn([
            'code' => 1,
            'message' => '成功!',
            'data' => [
                'inquiry'   => $inquiryDetail,
                'quote'     => $quoteDetail,
                'logistics' => $logiDetail
            ]
        ]);

    }


    /**
     * 获取报价详情
     * @param $condition 条件
     * @return array 结果
     */
    private function getQuoteDetail ($condition){

        $quote = new QuoteModel();
        $quoteFields = 'id,serial_no,fund_occupation_rate,payment_period,gross_profit_rate,bank_interest,total_bank_fee,total_purchase,'.
            'total_logi_fee,total_exw_price,total_quote_price,package_mode,total_weight,package_volumn,period_of_validity,'.
            'payment_mode,trade_terms_bn,delivery_period,premium_rate,trans_mode_bn,dispatch_place,quote_remarks';

        $data = $quote->getGeneralInfo($condition,$quoteFields);

        //汇率
        $exchangeRate = new ExchangeRateModel();
        $data['exchange_rate'] = $exchangeRate->where(['cur_bn2'=>'CNY','cur_bn1'=>'USD'])->getField('rate');

        //市场报EXW合计 市场报贸易合计
        $finalQuote = new FinalQuoteModel();
        $finalData = $finalQuote->where($condition)->field('total_exw_price,total_quote_price')->find();

        $data['final_total_exw_price'] = $finalData['total_exw_price'];
        $data['final_total_quote_price'] = $finalData['total_quote_price'];

        return $data;

    }


    /**
     * 重组APP需要格式的报价详情
     * @param $data 数据
     * @return array 结果
     */
    private function getRecombineQuoteDetail ($data)
    {

        $recombine = [
            '赊销比例', $data['fund_occupation_rate'],
            '回款周期', $data['payment_period'],
            '毛利率', $data['gross_profit_rate'],
            '银行利率', $data['bank_interest'],
            '银行费用', $data['total_bank_fee'],
            '汇率', $data['exchange_rate'],
            '采购合计', $data['total_purchase'],
            '物流合计', $data['total_logi_fee'],
            '商务报EXW合计', $data['total_exw_price'],
            '商务报贸易合计', $data['total_quote_price'],
            '市场报EXW合计', $data['final_total_exw_price'],
            '市场报贸易合计', $data['final_total_quote_price'],
            '包装方式', $data['package_mode'],
            '总重（KG）', $data['total_weight'],
            '包装总体积（m³）', $data['package_volumn'],
            '报价有效期', $data['period_of_validity'],
            '付款方式', $data['payment_mode'],
            '贸易术语', $data['trade_terms_bn'],
            '工厂交货周期', $data['delivery_period'],
            '保险税率', $data['premium_rate'],
            '运输方式', $data['trans_mode_bn'],
            '发运地', $data['dispatch_place'],
            '报价备注', $data['quote_remarks'],
        ];

        return $recombine;
    }


    /**
     * 获取物流信息
     * @param $condition
     *
     * @return array
     */
    private function getQuoteLogiFeeDetail($condition)
    {

        $logi = new LogisticsController();
        $data = $logi->getQuoteLogiFeeDetailAction($condition);

        $logiDetail = [
            "起运国-港", $data['from_country'].'-'.$data['from_port'],
            "目的国-港", $data['to_country'].'-'.$data['to_port'],
            "陆运费（USD）", $data['land_freight'],
            "陆运险", $data['overland_insu'],
            "陆运险税率", $data['overland_insu_rate'],
            "港杂费（USD）", $data['port_surcharge_usd'],
            "国际运输费（USD）", $data['inter_shipping_usd'],
            "保险税率", $data['premium_rate'],
            "货物运输保险", $data['shipping_insu'],
            "货物运输险税率", $data['shipping_insu_rate'],
            "预计运输周期", $data['est_transport_cycle'],
            "商检费", $data['inspection_fee'],
            "目的地关税税率", $data['dest_tariff_rate'],
            "目的地关税", $data['dest_tariff_fee'],
            "目的地送货费", $data['dest_delivery_fee'],
            "目的地清关费", $data['dest_clearance_fee'],
            "目的地增值费", $data['dest_va_tax_fee'],
            "目的地增值税率", $data['dest_va_tax_rate'],
        ];

        return $logiDetail;

    }

}

