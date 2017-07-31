<?php

/**
 * @description 产品线报价控制器
 * @file ProductlinequoteContoller.php
 * @author 买买提
 */
class ProductlinequoteController  extends PublicController
{

    /**
     * 产品线报价模型
     * @var
     */
    private $productLineQuoteModel;

    /**
     * 产品线报价详情模型
     * @var
     */
    private $productLineQuoteItemModel;

    /**
     * 构造方法
     */
    public function init()
    {
        parent::init();
        $this->productLineQuoteModel = new ProductLineQuoteModel();
        $this->productLineQuoteItemModel = new ProductLineQuoteItemModel();
    }

    /**
     * @desc 产品线报价列表接口
     * @author 买买提
     */
    public function getListAction()
    {

        $data = $this->productLineQuoteModel->getList($this->put_data) ;

        if ($data){
            $this->setCode(MSG::MSG_SUCCESS);
            $response = [
                'code'=> $this->getCode(),
                'message'=> $this->getMessage(),
                'totalCount'=> intval($data['totalCount'])
            ];

            if (isset($this->put_data['currentPage']) && $this->put_data['currentPage'] && isset($this->put_data['pageSize']) && $this->put_data['pageSize']) {
                $response['currentPage'] = isset($data['currentPage']) ? intval($data['currentPage']) : 1 ;
                $response['pageSize'] = isset($data['pageSize']) ? intval($data['pageSize']) : 10 ;
            }

            $response['data'] = $data['data'] ;
            $this->jsonReturn($response);

        }else{
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /**
     * @desc 产品线报价详情页询单信息接口
     * @author 买买提
     */
    public function getInquiryInfoAction()
    {
        if (empty($this->put_data['inquiry_no'])){
            $this->jsonReturn([
                'code'=> '-101',
                'message'=> '缺少参数'
            ]);
        }

        //询单本身信息
        $data = $this->productLineQuoteModel->getInquiryInfo($this->put_data);

        //询单sku列表信息
        $data['sku_list'] = $this->productLineQuoteItemModel->getSkuList($data['serial_no']);

        $this->jsonReturn([
            'code' => '1',
            'message' => '成功',
            'data'=> $data
        ]);
    }

    /**
     * @desc 产品线报价详情页询单信息保存接口
     * 产品线报价详情页询单信息保存的时候不做校验，市场的进度更改为待提交
     * @author 买买提
     */
    public function saveInquiryInfoAction()
    {
        $response = $this->productLineQuoteModel->saveInquiryInfo($this->put_data);
        if ($response){
            $this->jsonReturn();
        }

        $this->jsonReturn([
            'code'=>'-101',
            'message'=>'保存失败！'
        ]);
    }

    /**
     * @desc 产品线报价详情页->询单信息->删除sku
     * @author 买买提
     */
    public function deleteInquirySkuAction()
    {
        //这里的删除就是把状态改为DELETED
        $response = $this->productLineQuoteModel->deleteInquirySku($this->put_data);
        $this->jsonReturn($response);
    }

    /**
     * @desc 产品线报价详情页商品信息接口
     * @author 买买提
     */
    public function getGoodsInfoAction()
    {
        echo "产品线报价详情页商品信息";
    }

    /**
     * @desc 产品线报价审核列表接口
     * @author 买买提
     */
    public function getCheckListAction()
    {
        echo "产品线报价审核列表";
    }

    /**
     * @desc 产品线报价审核详情页接口
     * @author 买买提
     */
    public function getCheckInfoAction()
    {
        echo "产品线报价审核详情页";
    }

    /**
     * mongo测试
     */
    public function mongoTestAction()
    {
        $server = "mongodb://localhost:27017";
        $options = ['connect'=>TRUE];
        $mongo = new MongoClient($server,$options);
        $db = $mongo->oyghan->user;
        //$doc = ['name'=>'php','description'=>'this is php insert data test'];
        //$db->insert($doc);
        $obj = $db->find();
        $obj = $db->find()->count();
        p($obj);
        foreach ($obj as $key=>$value)
        {
            echo "<pre>";
            var_dump($value);
        }
    }
}