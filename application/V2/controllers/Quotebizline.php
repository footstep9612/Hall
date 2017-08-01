<?php

/**
 * 产品线报价
 * Class QuotebizlineController
 * @author 买买提
 */
class QuotebizlineController extends PublicController
{

    /**
     * 产品线报价单模型
     * @var
     */
    protected $_quoteBizLine;

    /**
     * 构造方法
     */
    public function init()
    {
        $this->_quoteBizLine = new QuoteBizLineModel();
    }

    /**
     * @desc 产品线报价列表接口
     */
    public function listAction()
    {
        $param = ['username'=>'1235'];
        $response = $this->_quoteBizLine->getQuoteList($param);
        p($response);
    }

    /**
     * @desc 详情页询单信息接口
     */
    public function inquiryInfoAction()
    {

    }

    /**
     * @desc 详情页报价信息接口
     */
    public function quoteInfoAction()
    {

    }

    /**
     * @desc 报价办理接口
     */
    public function manageAction()
    {

    }

    /**
     * @desc 报价办理->暂存接口
     */
    public function storageAction()
    {

    }

    /**
     * @desc 报价办理->查看审核信息接口
     */
    public function verifyInfoAction()
    {
        
    }

    /**
     * @desc 报价办理->附件信息
     */
    public function attachAction()
    {

    }
}

