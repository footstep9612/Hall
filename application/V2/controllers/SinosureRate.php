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

        $this->_handleList($this->sinosureRateModel, $data, $condition, true);
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
        $condition = $this->put_data;

        $condition['creator_by'] = $this->user['id'];
        $condition['created_at'] = date('Y-m-d H:i:s');

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

        if (!empty($condition['r_id'])) {
            $where['id'] = $condition['r_id'];
            unset($condition['r_id']);
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
        } else {
            $this->setCode('-101');
            $this->setMessage('失败!');
            parent::jsonReturn();
        }
    }

}
