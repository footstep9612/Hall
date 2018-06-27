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
        $city_model = new CityModel();
        $arr = $city_model->getListbycondition($condtion);
        if ($arr) {
            $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
            $data['code'] = MSG::MSG_SUCCESS;
            $data['data'] = $arr;
            $data['count'] = $city_model->getCount($condtion);

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
        $city_model = new CityModel();
        $arr = $city_model->getAll($condtion);
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
            $city_model = new CityModel();
            $result = $city_model->field('lang,region_bn,country_bn,bn,'
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
            $city_model = new CityModel();
            foreach ($langs as $lang) {
                $result = $city_model->field('lang,region_bn,country_bn,bn,'
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
        $city_model = new CityModel();
        $data = $city_model->create($condition);
        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        if (empty($condition['en']['name'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请输入英文');
            $this->jsonReturn();
        } elseif (empty($condition['zh']['name'])) {

            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请输入中文');
            $this->jsonReturn();
        } else {

            $newbn = ucwords($condition['en']['name']);
            $row = $city_model->Exits(['country_bn' => trim($condition['country_bn']), 'bn' => $newbn, 'status' => 'VALID']);

            if ($row) {
                $this->setMessage('英文');
                $this->setCode(MSG::MSG_EXIST);
                $this->jsonReturn();
            }
        }
        $data['created_by'] = defined('UID') ? UID : 0;
        $data['created_at'] = date('Y-m-d H:i:s');
        $result = $city_model->add($data);
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
        $city_model = new CityModel();
        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请选择国家!');
            $this->jsonReturn();
        }
        $newbn = ucwords($condition['en']['name']);
        if (empty($condition['en']['name'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请输入英文');
            $this->jsonReturn();
        } elseif (empty($condition['zh']['name'])) {

            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('请输入中文');
            $this->jsonReturn();
        } elseif ($newbn && $newbn != $condition['bn']) {
            $row = $city_model->where(['country_bn' => trim($condition['country_bn']), 'bn' => $newbn, 'status' => 'VALID'])->find();
            if ($row) {
                $this->setMessage('英文港口已存在');
                $this->setCode(MSG::MSG_EXIST);
                $this->jsonReturn();
            }
        }

        $result = $city_model->update_data($condition, $this->user['id']);
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
        $city_model = new CityModel();
        $result = $city_model->where($where)->save(['status' => 'DELETED', 'deleted_flag' => 'Y']);
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
