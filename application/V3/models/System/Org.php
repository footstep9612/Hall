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
class System_OrgModel extends PublicModel {

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
    public function getNameById($id, $lang) {
        if (!$id) {
            return '';
        }
        $where['id'] = $id;
        $org = $this->field('name')->where($where)->find();
        if ($lang == 'en') {
            $org = $this->field('name_en as name')->where($where)->find();
        }
        if ($org) {
            return $org['name'];
        } else {
            return '';
        }
    }

    /**
     * Description of 获取组织名称
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   组织
     */
    public function getIsEruiById($id) {
        if (!$id) {
            return 'N';
        }
        $where['id'] = $id;
        $where['org_node'] = ['in', ['erui', 'eub', 'elg']];
        $count = $this->where($where)->count();


        if ($count) {
            return 'Y';
        } else {
            return 'N';
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
    public function getOrgIdsById($groupId, $org_node = 'ub') {
        $where = [
            'id' => ['in', $groupId ?: ['-1']],
        ];
        if ($org_node) {
            $where['org_node'] = $org_node;
        }
        $orgList = $this
                        ->field('id')
                        ->where($where)->select();
        $orgIds = [];
        if ($orgList) {
            // 用户所在部门的组ID

            foreach ($orgList as $org) {
                $orgIds[] = $org['id'];
            }
        }
        if ($orgIds) {
            return $orgIds;
        } else {
            return ['-1'];
        }
    }

    public function getList($condition) {
        $where = ['deleted_flag' => 'N'];
        if (!empty($condition['org_node']) && is_string($condition['org_node'])) {
            $where['org_node'] = trim($condition['org_node']);
        } elseif (!empty($condition['org_node']) && is_array($condition['org_node'])) {
            $where['org_node'] = ['in', $condition['org_node']];
        }

        if (!empty($condition['parent_id'])) {
            $where['parent_id'] = trim($condition['parent_id']);
        } else {
            $where['parent_id'] = 0;
        }
        $list = $this
                ->field('id,name,name_en,name_es,name_ru')
                ->where($where)
                ->select();
        return $list;
    }

    public function getParentid($org_id) {
        $where = ['deleted_flag' => 'N'];

        if ($org_id) {
            $where = ['id' => $org_id];
        } else {
            return '';
        }
        $info = $this
                ->field('parent_id,org_node')
                ->where($where)
                ->find();

        if (empty($info)) {
            return '';
        } elseif (empty($info['parent_id']) && !empty($info['org_node'])) {
            return in_array($info['org_node'], ['erui', 'eub', 'elg']) ? 'ERUI' : '';
        } elseif (!empty($info['parent_id'])) {
            return $info['parent_id'];
        } else {
            return '';
        }
    }

    public function getCount($condition) {
        $where = ['deleted_flag' => 'N'];
        if (!empty($condition['org_node']) && is_string($condition['org_node'])) {
            $where['org_node'] = trim($condition['org_node']);
        } elseif (!empty($condition['org_node']) && is_array($condition['org_node'])) {
            $where['org_node'] = ['in', $condition['org_node']];
        }
        $count = $this
                ->where($where)
                ->count();
        return $count;
    }

}
