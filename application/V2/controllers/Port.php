<?php

/**
 * 口岸
 */
class PortController extends PublicController {

    public function init() {
        //parent::init();

        $this->_model = new PortModel();
    }

    public function listAction() {
        $condtion = $this->put_data;
        unset($condtion['token']);
        $key = 'Port_list_' . md5(json_encode($condtion));
        $data = json_decode(redisGet($key), true);
        if (!$data) {
            $arr = $this->_model->getListbycondition($condtion);
            if ($arr) {
                $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
                $data['code'] = MSG::MSG_SUCCESS;
                $data['data'] = $arr;
                $data['count'] = $this->_model->getCount($condtion);
                redisSet($key, json_encode($data), 86400);
                $this->jsonReturn($data);
            } else {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        }
        $this->jsonReturn($data);
    }

    public function listAllAction() {
        $condtion = $this->put_data;
        $condtion['lang'] = $this->getPut('lang', 'zh');
        unset($condtion['token']);
        $key = 'Port_listall_' . md5(json_encode($condtion));
        $data = json_decode(redisGet($key), true);
        if (!$data) {
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
        $this->jsonReturn($data);
    }

    /**
     * 分类联动
     */
    public function infoAction() {
        $bn = $this->get('bn', '') ?: $this->getPut('bn', '');
        if ($bn) {
            $data = [];
            $langs = ['en', 'zh', 'es', 'ru'];
            foreach ($langs as $lang) {
                $result = $this->_model->field('country_bn,bn,port_type,trans_mode,name,remarks')
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

    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Port_*');
        $redis->delete($keys);
    }

    public function createAction() {
        $condition = $this->put_data;
        unset($condition['token']);
        $result = $this->_model->create_data($condition);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function updateAction() {

        $condition = $this->put_data;
        unset($condition['token']);
        $result = $this->_model->update_data($condition);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    public function deleteAction() {

        $condition = $this->put_data;
        if ($condition['bn']) {
            if (is_string($condition['bn'])) {
                $where['bn'] = $condition['bn'];
            } elseif (is_array($condition['id'])) {
                $where['bn'] = ['in', $condition['bn']];
            } else {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
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
