<?php

/**
 * Description of LogiPeriodController
 * @author  zhongyg
 * @date    2017-8-2 13:07:21
 * @version V2.0
 * @desc   贸易条款对应物流时效
 */
class LogiperiodController extends PublicController {

    public function init() {
        // parent::init();

        $this->_model = new LogiPeriodModel();
    }

    /**
     * Description of 列表
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    public function listAction() {
        $condtion = $this->put_data;
        unset($condtion['token']);
        $key = 'logi_period_list_' . $lang . md5(json_encode($condtion));
        $data = redisGet($key);
        if (!$data) {
            $arr = $this->_model->getListbycondition($condtion);

            if ($arr) {
                $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
                $data['code'] = MSG::MSG_SUCCESS;
                $data['data'] = $arr;
                $data['count'] = $this->_model->getCount($condtion);
                redisSet($key, json_encode($data), 86400);
                $this->jsonReturn($data);
            } elseif ($arr === null) {
                redisSet($key, '&&', 86400);
                $this->setvalue('count', 0);
                $this->setCode(MSG::MSG_SUCCESS);
                $this->jsonReturn(null);
            } else {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        } elseif ($data == '&&') {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn(null);
        }
        $this->setCode(MSG::MSG_SUCCESS);
        $this->jsonReturn(json_decode($data, true));
    }

    /**
     * Description of 列表
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    public function listallAction() {
        $condtion = $this->put_data;
        $condtion['lang'] = $this->getPut('lang', 'zh');
        unset($condtion['token']);
        $key = 'logi_period_listall_' . $lang . md5(json_encode($condtion));
        $data = redisGet($key);
        if (!$data) {
            $arr = $this->_model->getListbycondition($condtion, true);

            if ($arr) {
                $this->setCode(MSG::MSG_SUCCESS);
                $data['data'] = $arr;

                redisSet($key, json_encode($data), 86400);
                $this->jsonReturn($arr);
            } elseif ($arr === null) {
                redisSet($key, '&&', 86400);

                $this->setCode(MSG::MSG_SUCCESS);
                $this->jsonReturn(null);
            } else {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        } elseif ($data == '&&') {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn(null);
        }
        $this->setCode(MSG::MSG_SUCCESS);
        $this->jsonReturn(json_decode($data, true));
    }

    /**
     * Description of 详情
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    public function infoAction() {
        $id = $this->getPut('id');
        if ($id) {
            $result = $this->_model->where(['id' => $id])->find();
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
        if ($result) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($result);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
        exit;
    }

    /**
     * Description of 清空缓存
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('logi_period_list_*');
        $redis->delete($keys);
        $LogiPeriods = $redis->getKeys('LogiPeriod*');
        $redis->delete($LogiPeriods);
    }

    /**
     * Description of 新增
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    public function createAction() {
        $condition = $this->put_data;
        $data = $this->_model->create($condition);
        $data['logi_no'] = $data['warehouse'] . '_' . substr($data['trans_mode'], 0, 1) . '_' . $data['trade_terms']
                . '_' . $data['from_port'] . '_' . $data['to_port'];
        $data['created_by'] = $this->user['name'];
        $data['created_at'] = date('Y-m-d H:i:s');
        $result = $this->_model->add($data);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    public function updateAction() {

        $condition = $this->put_data;
        $data = $this->_model->create($condition);
        $where['id'] = $condition['id'];
        $result = $this->_model->where($where)->update($data);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /**
     * Description of 删除
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易条款对应物流时效
     */
    public function deleteAction() {

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
        $result = $this->_model->where($where)->delete();
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
