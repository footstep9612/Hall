<?php

/**
  附件文档Controller
 */
class CityController extends PublicController {

    public function init() {
         parent::init();

        $this->_model = new CityModel();
    }

    public function listAction() {
        $condtion = $this->getPut(null);

        $key = 'City_list_' . md5(json_encode($condtion));
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

    public function listallAction() {
        $condtion = $this->getPut(null);
        $condtion['lang'] = $this->getPut('lang', 'zh');

        unset($condtion['token']);
        $key = 'City_listall_' . md5(json_encode($condtion));
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

    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('City*');
        $redis->delete($keys);
    }

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

    public function updateAction() {

        $condition = $this->getPut();

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
