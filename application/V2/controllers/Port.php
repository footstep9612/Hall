<?php

/**
 * Description of PortController
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   口岸
 */
class PortController extends PublicController {

    public function init() {
        parent::init();
    }

    /*
     * Description of 口岸列表
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */

    public function listAction() {
        $condtion = $this->getPut();
        $condtion['lang'] = $this->getPut('lang', 'zh');
        $port_model = new PortModel();
        $arr = $port_model->getListbycondition($condtion);
        $this->_setUserName($arr);
        if ($arr) {

            $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
            $data['code'] = MSG::MSG_SUCCESS;
            $data['data'] = $arr;
            $data['count'] = $port_model->getCount($condtion);

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
     * Description of 口岸所有
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */

    public function listAllAction() {
        $condtion = $this->getPut();
        $condtion['lang'] = $this->getPut('lang', 'zh');
        $port_model = new PortModel();
        $arr = $port_model->getAll($condtion);

        $this->_setUserName($arr);
        if ($arr) {
//            foreach ($arr as $key => $item) {
//                //   $item['port_country'] = $item['name'] . '(' . $item['country'] . ')';
//
//                $item['name'] = $item['name'] . '(' . $item['country'] . ')';
//                $arr[$key] = $item;
//            }
            $data['message'] = MSG::getMessage(MSG::MSG_SUCCESS, 'en');
            $data['code'] = MSG::MSG_SUCCESS;
            $data['data'] = $arr;
            $this->jsonReturn($data);
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   物流费率
     */

    private function _setUserName(&$arr) {
        if ($arr) {
            $employee_model = new EmployeeModel();
            $userids = [];
            foreach ($arr as $key => $val) {
                $userids[] = $val['created_by'];
            }
            $usernames = $employee_model->getUserNamesByUserids($userids);
            foreach ($arr as $key => $val) {
                if ($val['created_by'] && isset($usernames[$val['created_by']])) {
                    $val['created_by_name'] = $usernames[$val['created_by']];
                } else {
                    $val['created_by_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 口岸详情
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */

    public function detailAction() {
        $bn = $this->getPut('bn', '');
        $lang = $this->getPut('lang', 'zh');
        $country_bn = $this->getPut('country_bn', '');
        if (!$bn) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->setMessage('口岸简码不能为空!');
            $this->jsonReturn(null);
        }
        if ($bn) {
            $country_model = new CountryModel();
            $country = $country_model->getTableName();
            $where = ['bn' => $bn, 'lang' => $lang];
            if ($country_bn) {
                $where['country_bn'] = $country_bn;
            }
            $port_model = new PortModel();
            $result = $port_model->field('country_bn,bn,port_type,trans_mode,name,remarks,'
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
     * Description of 口岸详情
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */

    public function infoAction() {
        $bn = $this->getPut('bn', '');
        if ($bn) {
            $data = [];
            $langs = ['en', 'zh', 'es', 'ru'];
            $country_model = new CountryModel();
            $country = $country_model->getTableName();
            $port_model = new PortModel();
            foreach ($langs as $lang) {
                $result = $port_model->field('country_bn,bn,port_type,trans_mode,name,remarks,'
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
     * Description of 口岸删除缓存
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */

    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Port');
        $redis->delete($keys);
        unset($redis);
        $config = Yaf_Registry::get("config");
        $rconfig = $config->redis->config->toArray();
        $rconfig['dbname'] = 3;
        $redis3 = new phpredis($rconfig);
        $keys3 = $redis3->getKeys('Port');
        $redis3->delete($keys3);
        unset($redis3);
    }

    /*
     * Description of 新增口岸
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */

    public function createAction() {
        $condition = $this->getPut();
        $port_model = new PortModel();
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
            $row = $port_model->Exits(['country_bn' => trim($condition['country_bn']), 'bn' => $newbn, 'status' => 'VALID']);

            if ($row) {
                $this->setMessage('英文');
                $this->setCode(MSG::MSG_EXIST);
                $this->jsonReturn();
            }
        }
        $result = $port_model->create_data($condition);
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
     * Description of 更新口岸
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */

    public function updateAction() {

        $condition = $this->getPut();
        $port_model = new PortModel();
        $newbn = ucwords($condition['en']['name']);

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
        } elseif ($newbn && $newbn != $condition['bn']) {
            $row = $port_model->where(['country_bn' => trim($condition['country_bn']), 'bn' => $newbn, 'status' => 'VALID'])->find();
            if ($row) {
                $this->setMessage('英文港口已存在');
                $this->setCode(MSG::MSG_EXIST);
                $this->jsonReturn();
            }
        }
        $result = $port_model->update_data($condition);
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
     * Description of 删除口岸
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */

    public function deleteAction() {

        $condition = $this->getPut();
        $port_model = new PortModel();
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
        $result = $port_model->where($where)->save(['status' => 'DELETED', 'deleted_flag' => 'Y']);
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
