<?php
/**
 * name: FinalQuote.php
 * desc: 市场报价单控制器
 * User: 张玉良
 * Date: 2017/8/3
 * Time: 10:55
 */
class FinalQuoteController extends PublicController {

    public function __init()
    {
        parent::__init();
    }

    /*
     * 市场报价单详情
     * Author:张玉良
     */
    public function getInfoAction() {
        $inquiry = new InquiryModel();
        $quote = new QuoteModel();
        $finalquote = new FinalQuoteModel();
        $employee = new EmployeeModel();
        $where = $this->put_data;
        $data = [];

        //获取询单基本信息
        $inquirywhere['id'] = $where['id'];
        $inquiry_res = $inquiry->field('id,serial_no,pm_id,status')->where($inquirywhere)->find();

        //获取报价单信息
        $quotewhere['inquiry_id'] = $where['id'];
        $quote_res = $quote->where($inquirywhere)->find();

        //市场报价单信息
        $finalwhere['inquiry_id'] = $where['id'];
        $results = $finalquote->getInfo($finalwhere);



        $this->jsonReturn($results);
    }
}