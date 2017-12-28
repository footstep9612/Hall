<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Org
 * @author  zhongyg
 * @date    2017-8-5 9:54:14
 * @version V2.0
 * @desc
 */
class OrgModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_sys';
    protected $tableName = 'org';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Description of 获取组织名称
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   组织
     */
    public function getNameById($id) {
        if (!$id) {
            return '';
        }
        $where['id'] = $id;
        $org = $this->field('name')->where($where)->find();
        if ($org) {
            return $org['name'];
        } else {
            return '';
        }
    }

    /**
     * @desc 获取询单办理部门组ID
     *
     * @param array $groupId 当前用户的全部组ID
     * @param string $membership 是否属于erui
     * @param string $org_node 部门节点
     * @return array
     * @author liujf
     * @time 2017-10-20
     */
    public function getOrgIdByName($membership = 'ERUI', $org_node = 'erui') {

        $where['org_node'] = $org_node;
        $where['membership'] = $membership;
        $where['deleted_flag'] = 'N';
        $orgId = $this->field('id')->where($where)->order('id desc')->find();

        return $orgId ? $orgId : '';
    }

}