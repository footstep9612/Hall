<?php

/**
 * Description of MarketAreaModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   营销区域
 */
class MarketareaController extends PublicController {

    public function init() {
        // parent::init();
        ini_set("display_errors", "On");
        error_reporting(E_ERROR | E_STRICT);
        $this->_model = new MarketAreaModel();
    }

    private function _init() {
        parent::init();
    }

    /**
     * Description of MarketAreaModel
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    public function listAction() {
        $data = $this->getPut();
        $data['lang'] = $this->getPut('lang', '');
        $market_area_model = new MarketAreaModel();

        $arr = $market_area_model->getlist($data, false);
        $this->_setUserName($arr);


        if (!empty($arr)) {
            $this->setCode(MSG::MSG_SUCCESS);
        } elseif ($arr === null) {
            $this->setCode(MSG::ERROR_EMPTY);
        } else {
            $this->setCode(MSG::MSG_FAILED);
        }
        $this->jsonReturn($arr);
    }

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

    /**
     * Description of 详情
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    public function infoAction() {
        $bn = $this->getPut('bn', '');
        if (!$bn) {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
        $data = [];
        $langs = ['en', 'zh', 'es', 'ru'];
        $market_area_model = new MarketAreaModel();
        foreach ($langs as $lang) {

            $result = $market_area_model->info($bn, $lang);
            if ($result) {
                $data['bn'] = $result['bn'];
                $data[$lang]['name'] = $result['name'];
            } else {
                $data[$lang]['name'] = '';
            }
        }
        $this->_getTeams($data);
        if ($data['bn']) {
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn($data);
        } elseif (empty($data['bn'])) {
            $this->setCode(MSG::ERROR_EMPTY);
            $this->jsonReturn(null);
        } else {
            $this->setCode(MSG::MSG_FAILED);

            $this->jsonReturn();
        }
        exit;
    }

    /**
     * Description of 获取营销团队信息
     * @param array $data 详情
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    private function _getTeams(&$data) {
        $team_keys = ['market_org_id', 'market_org_name',
            'biz_tech_org_id', 'biz_tech_org_name',
            'logi_check_org_id', 'logi_check_org_name',
            'logi_quote_org_id', 'logi_quote_org_name'];
        if (isset($data['bn']) && $data['bn']) {
            $market_area_team_model = new MarketAreaTeamModel();
            $team = $market_area_team_model->getTeamByMarketAreaBn($data['bn']);
            foreach ($team_keys as $team_key) {
                if ($team[$team_key]) {
                    $data[$team_key] = $team[$team_key];
                } else {
                    $data[$team_key] = '';
                }
            }
        } else {
            foreach ($team_keys as $team_key) {
                $data[$team_key] = '';
            }
        }
    }

    /**
     * Description of 删除缓存
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    private function delcache() {
        $redis = new phpredis();
        $keys = $redis->getKeys('Market_Area*');
        $redis->delete($keys);
    }

    /**
     * Description of 新增营销区域
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    public function createAction() {
        $this->_init();
        $data = $this->getPut();
        $market_area_model = new MarketAreaModel();
        if (!isset($data['en']['name']) || !isset($data['zh']['name'])) {
            $this->setCode(MSG::ERROR_PARAM);
            $this->jsonReturn();
        } else {
            $newbn = ucwords($data['en']['name']);
            $row = $market_area_model->Exits(['bn' => $newbn, 'status' => 'VALID']);

            if ($row) {

                $this->setCode(MSG::MSG_EXIST);
                $this->jsonReturn();
            }
        }
        $result = $market_area_model->create_data($data);
        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /**
     * Description of 更新营销区域
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    public function updateAction() {
        $this->_init();
        $data = $this->getPut();
        $market_area_model = new MarketAreaModel();
        $newbn = ucwords($data['en']['name']);
        if ($newbn != $data['bn']) {
            $row = $market_area_model->Exits(['bn' => $newbn, 'status' => 'VALID']);
            if ($row) {

                $this->setCode(MSG::MSG_EXIST);
                $this->jsonReturn();
            }
        }
        $result = $market_area_model->update_data($data);

        if ($result) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

    /**
     * Description of 删除营销区域
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   营销区域
     */
    public function deleteAction() {
        $this->_init();
        $bn = $this->getPut('bn');
        if ($bn) {
            $bns = explode(',', $bn);
            if (is_array($bns)) {
                $where['bn'] = ['in', $bns];
            } else {
                $where['bn'] = $bn;
            }
        }
        $market_area_model = new MarketAreaModel();
        $result = $market_area_model->delete_data($bn);

        if ($result !== false) {
            $this->delcache();
            $this->setCode(MSG::MSG_SUCCESS);
            $this->jsonReturn();
        } else {
            $this->setCode(MSG::MSG_FAILED);
            $this->jsonReturn();
        }
    }

}
