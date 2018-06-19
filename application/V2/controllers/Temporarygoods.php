<?php

/**
 * 临时商品(SKU)相关接口类
 * Class TemporarygoodsController
 * @author 买买提
 */
class TemporarygoodsController extends PublicController
{
    public function init()
    {
        parent::init();
    }


    /**
     * 同步临时商品
     * 定时任务接口
     * 定时从询报价的SKU导入到临时商品库(Temporarygoods)
     */
    public function syncAction()
    {
        $response = (new TemporaryGoodsModel)->sync();
        $this->jsonReturn($response);
    }

    /**
     * 临时商品列表
     */
    public function listAction()
    {
        $request = $this->validateRequestParams();

        $response = (new TemporaryGoodsModel)->getList($request);

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'total' => 0,
            'data' => $response
        ]);
    }

    /**
     * 关联/取消关联(纠错)正式SKU
     */
    public function relationAction()
    {

    }

}