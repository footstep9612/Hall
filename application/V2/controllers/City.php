<?php

/**
 * Description of CityController
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   城市
 */
class CityController extends PublicController {

    public function init() {
        parent::init();

        $this->_model = new CityModel();
    }

    /*
     * Description of 城市列表
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
     */

    public function listAction() {
        $condtion = $this->getPut(null);
        $condtion['lang'] = $this->getPut('lang', 'zh');
        $arr = $this->_model->getListbycondition($condtion);
        if ($arr) {
            $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
            $data['code'] = MSG::MSG_SUCCESS;
            $data['data'] = $arr;
            $data['count'] = $this->_model->getCount($condtion);

            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * Description of 城市列表 所有
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
     */

    public function listallAction() {
        $condtion = $this->getPut(null);
        $condtion['lang'] = $this->getPut('lang', 'zh');

        $arr = $this->_model->getAll($condtion);
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

    /*
     * Description of 城市详情
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
     */

    public function infoAction() {
        $bn = $this->get('bn') ?: $this->getPut('bn');
        if ($bn) {
            $data = [];
            $langs = ['en', 'zh', 'es', 'ru'];
            foreach ($langs as $lang) {
                $result = $this->_model->field('lang,region_bn,country_bn,bn,'
                                        . 'name,time_zone,status,created_by,created_at')
                                ->where(['bn' => $bn, 'lang' => $lang])->find();

                if ($result) {
                    if (!$data) {
                        $data = $result;
                        $data['name'] = null;
                        unset($data['name']);
                    }

                    $data[$lang]['name'] = $result['name'];
                }
            }
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
        if ($data) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } elseif ($data === []) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
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
     * @desc   城市
     */

    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('City');
        $redis->delete($keys);
    }

    /*
     * Description of 新增城市
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
     */

    public function createAction() {
        $condition = $this->getPut();
        $data = $this->_model->create($condition);
        $data['created_by'] = $this->user['id'];
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

    /*
     * Description of 更新城市
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
     */

    public function updateAction() {

        $condition = $this->getPut();

        $result = $this->_model->update_data($condition, $this->user['id']);
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
     * Description of 删除城市
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
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

        $result = $this->_model->where($where)->save(['status' => 'DELETED']);
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