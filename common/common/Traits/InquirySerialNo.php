<?php
/**
 * Created by PhpStorm.
 * User: 张玉良
 * Desc: 流水号工具类
 * Date: 2017/9/7
 * Time: 11:44
 */
trait InquirySerialNo{

    /**
     * 获取采购商流水号
     * @author liujf 2017-06-20
     * @return string $buyerSerialNo 采购商流水号
     */
    public static function getBuyerSerialNo() {

        $buyerSerialNo = self::getSerialNo('buyerSerialNo', 'E-B-S-');

        return $buyerSerialNo;
    }

    /**
     * 获取供应商流水号
     * @author liujf 2017-06-20
     * @return string $supplierSerialNo 供应商流水号
     */
    public static function getSupplierSerialNo() {

        $supplierSerialNo = self::getSerialNo('supplierSerialNo', 'E-S-');

        return $supplierSerialNo;
    }

    /**
     * 获取生成的询价单流水号
     * @author liujf 2017-06-20
     * @return string $inquirySerialNo 询价单流水号
     */
    public static function getInquirySerialNo() {

        //$inquirySerialNo = self::getSerialNo('inquirySerialNo', 'INQ_');
        $inquirySerialNo = self::_getInquirySerialNo('INQ_');

        return $inquirySerialNo;
    }

    /**
     * 获取生成的报价单流水号
     * @author liujf 2017-06-20
     * @return string $quoteSerialNo 报价单流水号
     */
    public static function getQuoteSerialNo() {

        $quoteSerialNo = self::getSerialNo('quoteSerialNo', 'QUO_');

        return $quoteSerialNo;
    }

    /**
     * 获取生成的报价单号
     * @author liujf 2017-06-24
     * @return string $quoteNo 报价单号
     */
    public static function getQuoteNo() {

        $quoteNo = self::getSerialNo('quoteNo', 'Q_');

        return $quoteNo;
    }
    
    /**
     * @desc 获取生成的询单流水号
     *
     * @param string $prefix 流水号前缀
     * @return string 流水号
     * @author liujf
     * @time 2017-09-08
     */
    private static function _getInquirySerialNo($prefix = '') {
        $time = date('Ymd');
        
        $inquiryModel = new InquiryModel();
        
        $inquiry = $inquiryModel->field('serial_no')->order('serial_no DESC')->find();
        
        $serialNoArr = explode('_', $inquiry['serial_no']);
        
        $step = $time > $serialNoArr[1] ? 0 : intval($serialNoArr[2]);
        
        $step ++;
        
        return self::createSerialNo($step, $prefix);
    }

    /**
     * 根据流水号名称获取流水号
     * @param string $name 流水号名称
     * @param string $prefix 前缀
     * @author liujf 2017-06-20
     * @return string $code
     */
    private static function getSerialNo($name, $prefix = '') {
        $time = date('Ymd');
        $duration = 3600 * 48;
        $createTimeName = $name . 'CreateTime';
        $stepName = $name . 'Step';
        $createTime = redisGet($createTimeName) ?: '19700101';
        if ($time > $createTime) {
            redisSet($stepName, 0, $duration);
            redisSet($createTimeName, $time, $duration);
        }
        $step = redisGet($stepName) ?: 0;
        $step ++;
        redisSet($stepName, $step, $duration);
        $code = self::createSerialNo($step, $prefix);

        return $code;
    }

    /**
     * 生成流水号
     * @param string $step 需要补零的字符
     * @param string $prefix 前缀
     * @author liujf 2017-06-19
     * @return string $code
     */
    private static function createSerialNo($step = 1, $prefix = '') {
        $time = date('Ymd');
        $pad = str_pad($step, 5, '0', STR_PAD_LEFT);
        $code = $prefix . $time . '_' . $pad;
        return $code;
    }

}