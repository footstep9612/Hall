<?php

/**
 * 临时商品(SKU)相关接口类
 * Class TemporarygoodsController
 * @author 买买提
 */
class TemporarygoodsController extends PublicController {

    public function init() {
        parent::init();
    }

    /**
     * 同步临时商品
     * 定时任务接口
     * 定时从询报价的SKU导入到临时商品库(Temporarygoods)
     */
    public function syncAction() {

        set_time_limit(0);
        ini_set('memory_limi', '1G');
        $response = (new TemporaryGoodsModel)->sync();
        if ($response !== false) {

            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn(null);
        }
    }

    /**
     * 临时商品列表
     */
    public function listAction() {
        $request = $this->validateRequestParams();
        $model = new TemporaryGoodsModel();
        $response = $model->getList($request);


        $this->_setUname($response);
        $this->_setQuoteFlag($response);
        $count = $model->getCount($request);
        $this->setvalue('count', $count);
        if ($response !== false) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($response);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn(null);
        }
    }

    /*
     * 获取审核人 名称
     */

    private function _setUname(&$response) {

        $inquiry = new InquiryModel();
        $employye = new EmployeeModel();

        foreach ($response as $key=>$val) {
            $check_org_id = $inquiry->where(['id' => $val['inquiry_id']])->getField('check_org_id');
            $val['checked_by_name'] = $employye->getNameByid($check_org_id)['name'];
            $response[$key] = $val;
        }
    }

    /*
     * 是否已报价
     */

    private function _setQuoteFlag(&$response) {
        foreach ($response as $item) {
            if ($item['inquiry_id']) {
                $inquiry_ids[] = $item['inquiry_id'];
            }
        }
        if (!empty($inquiry_ids)) {
            $inquiry_model = new InquiryModel();
            $inquirys = $inquiry_model
                    ->where([
                        'id' => ['in', $inquiry_ids],
                        'deleted_flag' => 'N',
                        'quote_status' => ['in', ['QUOTED', 'COMPLETED']]
                    ])
                    ->getField('id', true);
            foreach ($response as $key => $val) {
                if (!empty($val['inquiry_id']) && in_array($val['inquiry_id'], $inquirys)) {
                    $val['quote_flag'] = 'Y';
                } else {
                    $val['quote_flag'] = 'N';
                }

                $response[$key] = $val;
            }
        } else {
            foreach ($response as $key => $val) {
                $val['quote_flag'] = 'N';
                $response[$key] = $val;
            }
        }
    }

    /**
     * 关联/取消关联(纠错)正式SKU
     */
    public function relationAction() {
        $sku = $this->getPut('sku');
        $id = $this->getPut('id');


        if (empty($id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择临时SKU!');
            $this->jsonReturn();
        }
        if (empty($sku)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择需要关联的SKU!');
            $this->jsonReturn();
        }
        $error = null;
        $model = new TemporaryGoodsModel();
        $result = $model->Relation($id, $sku);

        if ($result !== false) {
            $this->setCode(MSG::MSG_SUCCESS);

            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage($error);
            $this->jsonReturn();
        }
    }

    /**
     * 获取详情
     */
    public function InfoAction() {

        $id = $this->getPut('id');


        if (empty($id)) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择临时商品ID!');
            $this->jsonReturn();
        }

        $error = null;
        $model = new TemporaryGoodsModel();
        $result = $model->Info($id, $error);

        if ($result) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->setMessage($error);
            $this->jsonReturn();
        }
    }

}
