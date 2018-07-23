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
            $org_table = (new OrgModel)->getTableName();
            $org_member_table = (new OrgMemberModel)->getTableName();
            $condition = [
                'cm.country_bn' => $country_bn,
                'o.org_node' => ['in', ['ub','erui','eub']],
                'o.deleted_flag' => 'N',
                'om.leader_flag' => 'Y'
            ];
            $agentIds = $this->alias('cm')
                    ->join($org_member_table . ' as om on om.employee_id=cm.employee_id')
                    ->join($org_table . ' as o on o.id=om.org_id')
                    ->field('employee_id')
                    ->where($condition)
                    ->limit(0, 1)
                    ->order('cm.created_at desc')
                    ->select();
            return $agentIds ? $agentIds : [];
        } catch (Exception $e) {
            return false;
        }
    }

}
