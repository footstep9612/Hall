<?php

/**
 * 产品线报价
 * Class QuotebizlineController
 * @author 买买提
 */
class QuoteBizLineController extends PublicController
{

    /**
     * 产品线报价单模型
     * @var
     */
    private $_quoteBizLine;

    private $_requestParams = [];
    /**
     * 构造方法
     */
    public function init()
    {
        parent::init();

        $this->_quoteBizLine = new QuoteBizLineModel();

        $this->_requestParams = json_decode(file_get_contents("php://input"),true);
    }

    /**
     * @desc 产品线报价列表接口
     */
    public function listAction()
    {
        $data = $this->_quoteBizLine->getQuoteList($this->_requestParams);

        if (!$data){
            $this->jsonReturn([
                'code' => -101,
                'message' => '失败',
                'data' => ''
            ]);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $data
        ]);

    }

    /**
     * @desc 详情页询单信息接口
     */
    public function inquiryInfoAction()
    {
        //TODO 这里可以用公用接口来获取询单信息
        $data = [];
        if (!$data){
            $this->jsonReturn([
                'code' => -101,
                'message' => '失败',
                'data' => ''
            ]);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $data
        ]);
    }

    /**
     * @desc 详情页报价信息接口
     */
    public function quoteInfoAction()
    {

        $data = $this->_quoteBizLine->getQuoteInfo($this->_requestParams['quote_id']);

        if (!$data){
            $this->jsonReturn([
                'code' => -101,
                'message' => '失败',
                'data' => ''
            ]);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $data
        ]);
    }

    /**
     * @desc 报价办理接口
     */
    public function manageAction()
    {
        /*
        |--------------------------------------------------------------------------
        | Application Locale Configuration
        |--------------------------------------------------------------------------
        |
        | The application locale determines the default locale that will be used
        | by the translation service provider. You are free to set this value
        | to any of the locales which will be supported by the application.
        |
        */
    }

    /**
     * @desc 报价办理->暂存接口
     */
    public function storageQuoteAction()
    {
        /*
        |--------------------------------------------------------------------------
        | 报价单信息暂存   角色:产品线负责人
        |--------------------------------------------------------------------------
        |
        | 操作说明
        | 提交暂存后，不做校验，市场的进度为待提交
        |
        */

        $this->_quoteBizLine->storageQuote($this->_requestParams['quote_id']);

    }

    /**
     * @desc 报价办理->查看审核信息接口
     */
    public function verifyInfoAction()
    {
        $quoteItem = new QuoteItemBizLineModel();

        $data = $quoteItem->getVerifyInfo($this->_requestParams['quote_id']);

        if (!$data){
            $this->jsonReturn([
                'code' => -101,
                'message' => '失败',
                'data' => ''
            ]);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $data
        ]);
    }

    /**
     * @desc 报价办理->附件信息
     */
    public function attachAction()
    {
        $quoteAttach = new QuoteAttachModel();

        $attachList = $quoteAttach->where(['quote_id'=>$this->_requestParams['quote_id']])->select();

        if (!$attachList){
            $this->jsonReturn([
                'code' => -101,
                'message' => '失败',
                'data' => ''
            ]);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $attachList
        ]);
    }
}

