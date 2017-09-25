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
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
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
            foreach ($arr as $key => $item) {
                // $item['city_country'] = $item['name'] . '(' . $item['country'] . ')';
                $item['name'] = $item['name'] . '(' . $item['country'] . ')';
                $arr[$key] = $item;
            }
            $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
            $data['code'] = MSG::MSG_SUCCESS;
            $data['data'] = $arr;

            $this->jsonReturn($data);
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setvalue('count', 0);
            $this->jsonReturn(null);
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

    public function detailAction() {
        $bn = $this->getPut('bn', '');
        $lang = $this->getPut('lang', 'zh');
        $country_bn = $this->getPut('country_bn', '');
        if (!$bn) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('城市简码不能为空!');
            $this->jsonReturn(null);
        }
        if ($bn) {
            $country_model = new CountryModel();
            $country = $country_model->getTableName();
            $where = ['bn' => $bn, 'lang' => $lang];
            if ($country_bn) {
                $where['country_bn'] = $country_bn;
            }
            $result = $this->_model->field('lang,region_bn,country_bn,bn,'
                                    . 'name,time_zone,status,created_by,created_at,'
                                    . '(select name from ' . $country . ' where bn=country_bn and lang=port.lang) as country')
                            ->where($where)->find();

            if ($result) {
                $this->setCode(MSG::MSG_SUCCESS);
                $this->jsonReturn($result);
            } elseif ($result === []) {
                $this->setCode(MSG::ERROR_EMPTY);
                $this->jsonReturn(null);
            } else {
                $this->setCode(MSG::MSG_FAILED);
                $this->jsonReturn();
            }
        }

        exit;
    }

    /*
     * Description of 城市详情
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   城市
     */

    public function infoAction() {
        $bn = $this->getPut('bn');
        if ($bn) {
            $data = [];
            $langs = ['en', 'zh', 'es', 'ru'];
            $country_model = new CountryModel();
            $country = $country_model->getTableName();
            foreach ($langs as $lang) {
                $result = $this->_model->field('lang,region_bn,country_bn,bn,'
                                        . 'name,time_zone,status,created_by,created_at,'
                                        . '(select name from ' . $country . ' where bn=country_bn and lang=port.lang) as country')
                                ->where(['bn' => $bn, 'lang' => $lang])->find();

                if ($result) {
                    if (empty($data['bn'])) {
                        $data = $result;
                        $data['name'] = null;
                        unset($data['name']);
                        unset($data['country']);
                    }
                    $data[$lang]['country'] = $result['country'];
                    $data[$lang]['name'] = $result['name'];
                } else {
                    $data[$lang]['country'] = '';
                    $data[$lang]['name'] = '';
                }
            }
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
        if (empty($data['zh']['name']) && empty($data['en']['name']) && empty($data['es']['name']) && empty($data['ru']['name'])) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } elseif ($data) {
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
        $data['created_by'] = defined('UID') ? UID : 0;
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
