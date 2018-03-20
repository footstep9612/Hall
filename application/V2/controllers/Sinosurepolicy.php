<?php

/*
 * @desc 信保政策控制器
 *
 * @author liujf
 * @time 2018-03-19
 */

class SinosurepolicyController extends PublicController {

    public function init() {
        parent::init();
        $this->put_data = dataTrim($this->put_data);
        
        $this->sinosurePolicyModel = new SinosurePolicyModel();
        $this->employeeModel = new EmployeeModel();
        $this->countryModel = new CountryModel();
        
        $this->time = date('Y-m-d H:i:s');
    }
    
    /**
     * @desc 保存信保政策数据接口
     *
     * @author liujf
     * @time 2018-03-20
     */
    public function saveSinosurePolicyDataAction() {
        $condition = $this->put_data;
        if ($condition['country_bn'] == '' || $condition['items'] == '') {
            jsonReturn('', -101, L('MISSING_PARAMETER'));
        }
        $saveData = [];
        foreach ($condition['items'] as $item) {
            $data['country_bn'] = $condition['country_bn'];
            $data['type'] = $item['type'];
            $data['company'] = $item['company'];
            $data['sign_flag'] = $item['sign_flag'];
            $data['start_settle_period'] = $item['start_settle_period'];
            $data['end_settle_period'] = $item['end_settle_period'];
            $data['remarks'] = $item['remarks'];
            $data['tax_rate'] = $item['tax_rate'];
            $data['created_by'] = $this->user['id'];
            $data['created_at'] = $this->time;
            $saveData[] = $data;
        }
        if ($saveData) {
            $this->sinosurePolicyModel->delRecord(['country_bn' => $condition['country_bn']]);
            $res = $this->sinosurePolicyModel->addAll($saveData);
            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }
    
    /**
     * @desc 获取信保政策列表接口
     *
     * @author liujf
     * @time 2018-03-20
     */
    public function getSinosurePolicyListAction() {
        $condition = $this->put_data;
        $sinosurePolicyList = $this->sinosurePolicyModel->getListGroupByCountry($condition, 'country_bn, created_by, created_at');
        foreach ($sinosurePolicyList as &$sinosurePolicy) {
            $sinosurePolicy['created_name'] = $this->employeeModel->getUserNameById($sinosurePolicy['created_by']);
            $sinosurePolicy['country_name'] = $this->countryModel->where(['bn' => $sinosurePolicy['country_bn'], 'lang' => $this->lang, 'deleted_flag' => 'N'])->getField('name');
            $groupList = $this->sinosurePolicyModel->getGroupList(['country_bn' => $sinosurePolicy['country_bn']], 'type, company', 'type, company, start_settle_period, end_settle_period');
            foreach ($groupList as $item) {
                $type = $item['type'];
                unset($item['type']);
                $sinosurePolicy[$type][] = $item;
            }
        }
        if ($sinosurePolicyList) {
            $res['code'] = 1;
            $res['message'] = L('SUCCESS');
            $res['data'] = $sinosurePolicyList;
            $res['count'] = $this->sinosurePolicyModel->getCountGroupByCountry($condition);
            $this->jsonReturn($res);
        } else {
            $this->jsonReturn(false);
        }
    }
    
    /**
     * @desc 获取信保政策详情接口
     *
     * @author liujf
     * @time 2018-03-20
     */
    public function getSinosurePolicyDetailAction() {
        $condition = $this->put_data;
        $sinosurePolicyList = $this->sinosurePolicyModel->getList($condition);
        $this->_handleList($this->sinosurePolicyModel, $sinosurePolicyList, $condition);
    }
    
    /**
     * @desc 删除信保政策数据接口
     *
     * @author liujf
     * @time 2018-03-20
     */
    public function delSinosurePolicyDataAction() {
        $condition = $this->put_data;
        if ($condition['country_bn'] == '') {
            jsonReturn('', -101, L('MISSING_PARAMETER'));
        }
        $res = $this->sinosurePolicyModel->delRecord($condition);
        $this->jsonReturn($res);
    }

    /**
     * @desc 对获取列表数据的处理
     *
     * @author liujf
     * @time 2017-11-10
     */
    private function _handleList($model, $data = [], $condition = [], $join = false) {
        if ($data) {
            $res['code'] = 1;
            $res['message'] = L('SUCCESS');
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
     * @time 2017-11-10
     */
    public function jsonReturn($data = [], $type = 'JSON') {
        if ($data) {
            $this->setCode('1');
            $this->setMessage(L('SUCCESS'));
            parent::jsonReturn($data, $type);
        } else {
            $this->setCode('-101');
            $this->setMessage(L('FAIL'));
            parent::jsonReturn();
        }
    }

}
