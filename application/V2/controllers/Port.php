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

        $this->_model = new PortModel();
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



        $arr = $this->_model->getListbycondition($condtion);
        $this->_setUserName($arr);
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
     * Description of 口岸所有
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */

    public function listAllAction() {
        $condtion = $this->getPut();
        $condtion['lang'] = $this->getPut('lang', 'zh');

        $arr = $this->_model->getAll($condtion);
        $this->_setUserName($arr);
        if ($arr) {
            foreach ($arr as $key => $item) {
                $item['port_country'] = $item['name'] . '(' . $item['country'] . ')';
                $arr[$key] = $item;
            }
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

    public function infoAction() {
        $bn = $this->getPut('bn', '');
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
    }

    /*
     * Description of 新增口岸
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */

    public function createAction() {
        $condtion = $this->getPut();

        $result = $this->_model->create_data($condtion);
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

    /*
     * Description of 删除口岸
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */

    public function deleteAction() {

        $condition = $this->getPut();
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
        $result = $this->_model->where($where)->save(['status' => 'DELETED',]);
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
