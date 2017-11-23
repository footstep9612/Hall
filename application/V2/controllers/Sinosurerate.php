<?php

/*
 * @desc 信保税率控制器
 *
 * @author liujf
 * @time 2017-08-01
 */

class SinosureRateController extends PublicController {

    public function init() {
        parent::init();
        $this->sinosureRateModel = new SinosureRateModel();
    }

    /**
     * @desc 获取信保税率列表接口
     *
     * @author liujf
     * @time 2017-08-01
     */
    public function getSinosureRateListAction() {
        $condition = $this->put_data;

        $data = $this->sinosureRateModel->getJoinList($condition);

        $this->_setCountryName($data);
        $this->_setUserName($data, ['created_by', 'updated_by', 'checked_by']);
        $this->_handleList($this->sinosureRateModel, $data, $condition, true);
    }

    private function _setCountryName(&$arr) {
        if ($arr) {
            $country_model = new CountryModel();
            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = $val['country_bn'];
            }
            $country_bns = $country_model->getNamesBybns($country_bns);
            foreach ($arr as $key => $val) {
                if ($val['country_bn'] && isset($country_bns[$val['country_bn']])) {
                    $val['country_name'] = $country_bns[$val['country_bn']];
                } else {
                    $val['country_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setUserName(&$arr, $fileds) {
        if ($arr) {
            $employee_model = new EmployeeModel();
            $userids = [];
            foreach ($arr as $key => $val) {
                foreach ($fileds as $filed) {
                    if (isset($val[$filed]) && $val[$filed]) {
                        $userids[] = $val[$filed];
                    }
                }
            }
            $usernames = $employee_model->getUserNamesByUserids($userids);
            foreach ($arr as $key => $val) {
                foreach ($fileds as $filed) {
                    if ($val[$filed] && isset($usernames[$val[$filed]])) {
                        $val[$filed . '_name'] = $usernames[$val[$filed]];
                    } else {
                        $val[$filed . '_name'] = '';
                    }
                }
                $arr[$key] = $val;
            }
        }
    }

    /**
     * @desc 获取信保税率详情接口
     *
     * @author liujf
     * @time 2017-08-01
     */
    public function getSinosureRateDetailAction() {
        $condition = $this->put_data;

        $res = $this->sinosureRateModel->getJoinDetail($condition);

        $this->jsonReturn($res);
    }

    /**
     * @desc 新增信保税率记录接口
     *
     * @author liujf
     * @time 2017-08-01
     */
    public function addSinosureRateRecordAction() {
        $condition = $this->getPut();
        $condition['created_by'] = $this->user['id'];
        $condition['created_at'] = date('Y-m-d H:i:s');

        if (empty($condition['country_bn'])) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->setMessage('请选择国家!');

            parent::jsonReturn();
        } elseif (isset($condition['country_bn']) && $condition['country_bn']) {
            $country_bn = $condition['country_bn'];
            $row = $this->sinosureRateModel->Exits(['country_bn' => $country_bn, 'status' => 'VALID']);

            if ($row) {
                $this->setCode(MSG::MSG_EXIST);
                $this->setMessage('已存在该国家的信保税率记录!');
                parent::jsonReturn();
            }
        }
        $res = $this->sinosureRateModel->addRecord($condition);

        $this->jsonReturn($res);
    }

    /**
     * @desc 修改信保税率信息接口
     *
     * @author liujf
     * @time 2017-08-01
     */
    public function updateSinosureRateInfoAction() {
        $condition = $this->put_data;

        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
            unset($condition['id']);
            if (isset($condition['country_bn']) && $condition['country_bn']) {
                $country_bn = $condition['country_bn'];
                $row = $this->sinosureRateModel->Exits(['country_bn' => $country_bn, 'status' => 'VALID']);

                if ($row && $row['id'] != $where['id']) {
                    $this->setCode(MSG::MSG_EXIST);
                    $this->jsonReturn();
                }
            }

            $res = $this->sinosureRateModel->updateInfo($where, $condition);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 删除信保税率记录接口
     *
     * @author liujf
     * @time 2017-08-01
     */
    public function delSinosureRateRecordAction() {
        $condition = $this->put_data;

        if (!empty($condition['r_id']) || !empty($condition['id'])) {
            $res = $this->sinosureRateModel->delRecord($condition);

            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 对获取列表数据的处理
     *
     * @author liujf
     * @time 2017-08-01
     */
    private function _handleList($model, $data = [], $condition = [], $join = false) {
        if ($data) {
            $res['code'] = 1;
            $res['message'] = '成功!';
            $res['data'] = $data;
            $res['count'] = $join ? $model->getJoinCount($condition) : $model->getCount($condition);
            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }

    /**
     * @desc 重写jsonReturn方法
     *
     * @author liujf
     * @time 2017-08-01
     */
    public function jsonReturn($data = [], $type = 'JSON') {
        if ($data) {
            $this->setCode('1');
            $this->setMessage('成功!');
            parent::jsonReturn($data, $type);
        } elseif ($data === null) {
            if (empty($this->getCode()) || $this->getCode() != 1) {
                $this->setCode(MSG::ERROR_EMPTY);
            }
            if (empty($this->getMessage())) {
                $this->setMessage(MSG::ERROR_EMPTY);
            }
            parent::jsonReturn($data);
        } else {
            if (empty($this->getCode()) || $this->getCode() != 1) {
                $this->setCode(-101);
            }
            if (empty($this->getMessage())) {
                $this->setMessage('失败!');
            }

            parent::jsonReturn();
        }
    }

}
