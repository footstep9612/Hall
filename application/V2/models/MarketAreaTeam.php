<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MarketAreaTeam
 * @author  zhongyg
 * @date    2017-8-5 9:40:40
 * @version V2.0
 * @desc   
 */
class MarketAreaTeamModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui2_operation';
    protected $tableName = 'market_area_team';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Description of 判断数据是否存在
     * @param array $where 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    public function Exits($where) {

        return $this->_exist($where);
    }

    /**
     * Description of 获取营销区域运营团队   
     * @param string $market_area_bn 营销区域简称    
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    public function getTeamByMarketAreaBn($market_area_bn) {
        $where['market_area_bn'] = $market_area_bn;
        $team = $this->where($where)->find();
        $org_ids = ['market_org_id' => 'market_org_name',
            'biz_tech_org_id' => 'biz_tech_org_name',
            'logi_check_org_id' => 'logi_check_org_name',
            'logi_quote_org_id' => 'logi_quote_org_name'];
        $org_model = new OrgModel();
        foreach ($org_ids as $org_id => $org_name) {
            $team[$org_name] = $org_model->getNameById($team[$org_id]);
        }
        return $team;
    }

    /**
     * Description of 更新或新增营销区域运营团队
     * @param array $data 条件
     * @param string $market_area_bn 营销区域简称
     * @param string $uid 更新者ID
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    public function updateandcreate($data, $market_area_bn, $uid = 0) {

        $where['market_area_bn'] = $market_area_bn;
        $arr['market_area_bn'] = $market_area_bn;
        $arr['market_org_id'] = $data['market_org_id'];
        $arr['biz_tech_org_id'] = $data['biz_tech_org_id'];
        $arr['logi_check_org_id'] = $data['logi_check_org_id'];
        $arr['logi_quote_org_id'] = $data['logi_quote_org_id'];
        if ($this->Exits($where)) {
            $flag = $this->where($where)->save($arr);
            return $flag;
        } else {

            $arr['created_at'] = date('Y-m-d H:i:s');
            $arr['created_by'] = $uid;
            $flag = $this->add($arr);
            return $flag;
        }
    }

}
