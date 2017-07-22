<?php

/**
 * Class ProductlinequoteContoller
 *
 * @description 产品线报价控制器
 * @author 买买提
 */
class ProductlinequoteController  extends PublicController
//class ProductlinequoteController  extends Yaf_Controller_Abstract
{
    /**
     * 构造方法
     */
    public function init()
    {
        parent::init();
    }
    /**
     * @desc 产品线报价列表接口
     * @author 买买提
     */
    public function getListAction()
    {
        $productLineQuoteModel = new ProductLineQuoteModel();
        $data = $productLineQuoteModel->getList($this->put_data) ;

        if ($data)
        {
            $this->setCode(MSG::MSG_SUCCESS);

            $response = [
                'code'=> $this->getCode(),
                'message'=> $this->getMessage(),
                'totalCount'=> intval($data['totalCount'])
            ];

            if (isset($this->put_data['currentPage']) && $this->put_data['currentPage'] && isset($this->put_data['pageSize']) && $this->put_data['pageSize'])
            {
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
    /**
     * @desc 产品线报价详情页接口
     * @author 买买提
     */
    public function getInfoAction()
    {
        echo "产品线报价详情页";
    }

    /**
     * @desc 产品线报价详情页项目信息接口
     * @author 买买提
     */
    public function getProjectInfoAction()
    {
        echo "产品线报价详情页项目信息";
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

}