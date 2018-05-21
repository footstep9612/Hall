<?php

/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/30
 * Time: 11:43
 */
class CountryMemberModel extends PublicModel {

    protected $tableName = 'country_member';
    protected $dbName = 'erui_sys'; //数据库名称

    public function __construct($str = '') {

        parent::__construct();
    }

    /**
     * 根据客户id获取市场负责人id
     * @param $country_bn
     * @return array|bool|mixed
     * @author link
     */
    public function getAgentIdByCountryBn($country_bn) {
        try {
            $condition = [
                'country_bn' => $country_bn,
            ];
            $agentIds = $this->field('employee_id')
                    ->where($condition)
                    ->limit(0, 1)
                    ->select();
            return $agentIds ? $agentIds : [];
        } catch (Exception $e) {
            return false;
        }
    }

}
