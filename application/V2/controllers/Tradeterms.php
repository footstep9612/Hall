<?php

/**
 * Description of RateController
 * @author  zhongyg
 * @date    2017-8-2 13:07:21
 * @version V2.0
 * @desc   贸易术语
 */
class TradetermsController extends PublicController {

    public function init() {
        parent::init();
        $this->_model = new TradeTermsModel();
    }

    /*
     * Description of 贸易术语列表
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易术语
     */

    public function listAction() {
        $condtion = $this->getPut();
        $condtion['lang'] = $this->getPut('lang', 'zh');
        $arr = $this->_model->getList($condtion);
        if ($arr) {
            $this->setCode(MSG::MSG_SUCCESS);
            $count = $this->_model->getCount($condtion);
            echo $this->_model->_sql();
            $this->setvalue('count', $count);
            $this->jsonReturn($arr);
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * Description of 贸易术语所有
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易术语
     */

    public function listallAction() {
        $condtion = $this->getPut();
        $condtion['lang'] = $this->getPut('lang', 'zh');

        $arr = $this->_model->getall($condtion);
        if ($arr) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($arr);
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * Description of 贸易术语详情
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易术语
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

    /*
     * Description of 删除缓存
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易术语
     */

    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Tradeterms');
        $redis->delete($keys);
    }

    /*
     * Description of 新建
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易术语
     */

    public function createAction() {
        $condition = $this->getPut();
        $data = $this->_model->create($condition);
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

    /*
     * Description of 更新
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易术语
     */

    public function updateAction() {

        $condition = $this->getPut();
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

    /*
     * Description of 删除
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   贸易术语
     */

    public function deleteAction() {

        $condition = $this->getPut();
        if (isset($condition['id']) && $condition['id']) {
            if (is_string($condition['id'])) {
                $where['id'] = $condition['id'];
            } elseif (is_array($condition['id'])) {
                $where['id'] = ['in', $condition['id']];
            }
        } elseif ($condition['terms']) {
            $where['terms'] = $condition['terms'];
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
