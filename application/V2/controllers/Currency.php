<?php

/**
 * 币种
 * @author  zhongyg
 */
class CurrencyController extends PublicController {

    public function init() {
        parent::init();
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

        $status = $this->getPut('status', 'VALID');
        if (!$data) {
            $arr = $model->getlist($status);

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

    /*
     * Description of 关闭币种
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   币种
     */

    public function CloseCurrencyAction() {

        $condition = $this->put_data;
        if (isset($condition['id']) && $condition['id']) {
            if (is_string($condition['id'])) {
                $where['id'] = $condition['id'];
            } elseif (is_array($condition['id'])) {
                $where['id'] = ['in', $condition['id']];
            }
        } elseif ($condition['bn']) {
            $where['bn'] = $condition['bn'];
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $currency_model = new CurrencyModel();
        $result = $currency_model->where($where)->save(['status' => 'DELETED', 'deleted_flag' => 'Y']);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * Description of 删除缓存
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
     */

    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Currency_listall');
        $redis->delete($keys);
    }

    /*
     * Description of 开启币种
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   币种
     */

    public function OpenCurrencyAction() {

        $condition = $this->put_data;
        if (isset($condition['id']) && $condition['id']) {
            if (is_string($condition['id'])) {
                $where['id'] = $condition['id'];
            } elseif (is_array($condition['id'])) {
                $where['id'] = ['in', $condition['id']];
            }
        } elseif ($condition['bn']) {
            $where['bn'] = $condition['bn'];
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $currency_model = new CurrencyModel();
        $result = $currency_model->where($where)->save(['status' => 'VALID', 'deleted_flag' => 'N']);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
