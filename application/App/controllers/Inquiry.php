<?php

/**
 * 询报价APP 询单相关接口类
 * @desc   InquiryController
 * @Author 买买提
 */
class InquiryController extends PublicController
{

    private $inquiryModel;

    private $listAuth = [];

    public function init()
    {
        parent::init();

        $this->inquiryModel = new InquiryModel();

        $this->listAuth = [
            'role_no' => $this->user['role_no'],
            'group_id' => $this->user['group_id'],
            'user_id' => $this->user['id'],
            'list_type' => 'inquiry'
        ];

    }


    /**
     * 首页信息(统计，轮播，列表[最新3条数据])
     */
    public function homeAction()
    {

        //$request = $this->validateRequestParams();

        $data = [];

        $data['statistics'] = [
            'todayCount'  => $this->inquiryModel->getStatisticsByType('TODAY', $this->listAuth),
            'totalCount'  => $this->inquiryModel->getStatisticsByType('TOTAL', $this->listAuth),
            'quotedCount' => $this->inquiryModel->getStatisticsByType('QUOTED', $this->listAuth)
        ];

        $data['carousel'] = $this->inquiryModel->getList_($this->listAuth,"id,buyer_code",['quote_status'=>'QUOTED']);

        $data['list'] = $this->inquiryModel->getNewItems($this->listAuth,"id,serial_no,buyer_name,created_at,quote_status,status,now_agent_id");

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
        $data['status'] = 'BIZ_DISPATCHING';
        $this->jsonReturn($this->inquiryModel->updateData($data));

    }

    public function editAction()
    {

        $request = $this->validateRequestParams('id');
        $where = ['inquiry_id'=>$request['id']];

        //驳回信息
        $inquiryCheck = new InquiryCheckLogModel();
        $checkInfo = $inquiryCheck->where($where)->order('created_at DESC')->field('created_at,created_by,op_note')->find();
        $employeeModel = new EmployeeModel();
        $checkInfo['rejecter'] = $employeeModel->where(['id'=>$checkInfo['created_by']])->getField('name');

        //询单信息
        $fields = "id,buyer_name,quote_deadline,quote_notes,trade_terms_bn,payment_mode,trans_mode_bn,to_country,to_port,destination";
        $inquiryInfo = $this->inquiryModel->getDetail($request,$fields);

        //附件信息
        $inquiryAttach = new InquiryAttachModel();
        $attachList = $inquiryAttach->where($where)->field('attach_name,attach_url')->select();

        $response = [
            'rejectInfo' => [
                'reject_time' => $checkInfo['created_at'],
                'reject_name' => $checkInfo['rejecter'],
                'reject_note' => $checkInfo['op_note'],
            ],
            'basic' => [
                'buyer_name'     => $inquiryInfo['buyer_name'],
                'quote_deadline' => $inquiryInfo['quote_deadline'],
                'quote_notes'    => $inquiryInfo['quote_notes'],
            ],
            'sku' => $attachList,
            'logistics' => [
                'trade_terms_bn' => $inquiryInfo['trade_terms_bn'],
                'payment_mode'   => $inquiryInfo['payment_mode'],
                'trans_mode_bn'  => $inquiryInfo['trans_mode_bn'],
                'to_country'     => $inquiryInfo['to_country'],
                'to_port'        => $inquiryInfo['to_port'],
                'destination'    => $inquiryInfo['destination'],
            ]
        ];

        $this->jsonReturn($response);

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
        $condition = $this->validateRequestParams();

        $countryModel = new CountryModel();
        $employeeModel = new EmployeeModel();

        $condition = array_merge($condition,$this->listAuth);

        $inquiryList = $this->inquiryModel->getList_($condition, 'id,serial_no,buyer_name,now_agent_id,created_at,quote_status,status');

        foreach ($inquiryList as &$inquiry) {
            //$country = $countryModel->field('name')->where(['bn' => $inquiry['country_bn'], 'lang' => 'zh', 'deleted_flag' => 'N'])->find();
            //$inquiry['country_name'] = $country['name'];
            $nowAgent = $employeeModel->field('name')->where(['id' => $inquiry['now_agent_id']])->find();
            $inquiry['name'] = $nowAgent['name'];

            unset($inquiry['now_agent_id']);
            unset($inquiry['country_bn']);
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

    /**
     * 商品列表
     */
    public function skuAction()
    {

        $request = $this->validateRequestParams('id');

        $inquiryItem = new InquiryItemModel();
        $data = $inquiryItem->getItemWithQuote($request);

        $this->jsonReturn([
            'code'    => 1,
            'message' => '成功!',
            'count'   => $inquiryItem->getCountItemWithQuote($request),
            'data'    => $data
        ]);

    }

}

