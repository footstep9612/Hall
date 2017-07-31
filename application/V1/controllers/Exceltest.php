<?php


/**
 * 测试控制器
 * Class Exceltest
 */
class ExceltestController extends Yaf_Controller_Abstract
{
    /**
     *
     * 获取询单详情
     *
     */
    public function getInquiryAction()
    {
        $inquiryModel = new InquiryModel();
        $inquiryItemModel = new InquiryItemModel();
        $quoteModel = new QuoteModel();

        $where = ['serial_no'=>'INQ_20170714_00164'];

        $inquiryItem = $quoteModel->where($where)->select();

        p($inquiryItem);
    }
}




