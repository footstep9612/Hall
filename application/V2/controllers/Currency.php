<?php

/**
 * 币种
 * @author  zhongyg
 */
class CurrencyController extends PublicController {

    public function init() {
        //  parent::init();
    }

    /**
     * 获取所有币种
     * @param string $lang
     * @param string $country
     * @return array|mixed
     * @author  zhongyg
     */
    public function listAction() {
        $key = 'Currency_listall';
        $data = json_decode(redisGet($key), true);
        $model = new CurrencyModel();
        if (!$data) {
            $arr = $model->getlist();

            if ($arr) {
                $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
                $data['code'] = MSG::MSG_SUCCESS;
                $data['data'] = $arr;
                redisSet($key, json_encode($data), 86400);
                $this->jsonReturn($data);
            } else {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        }
        $this->jsonReturn($data);
    }

}
