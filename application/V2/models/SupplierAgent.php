<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author
 */
class SupplierAgentModel extends PublicModel {
    //put your code here
    protected $tableName = 'supplier_agent';
    protected $dbName = 'erui2_supplier'; //数据库名称
    protected $g_table = 'erui2_supplier.supplier_agent';
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    //状态
    const STATUS_VALID = 'VALID'; //有效,通过
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_TEST = 'TEST'; //待报审；
    const STATUS_CHECKING = 'STATUS_CHECKING'; //审核；
    const STATUS_DELETED = 'DELETED'; //删除；

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = [],$order=" id desc") {
        return $this->where($condition)
            ->field('id,supplier_id,org_id,role,created_by,created_at')
            ->order('id desc')
            ->select();
    }

    public function create_data($create = [])
    {
        if(!empty($create['id'])) {
            $data['id'] = $create['id'];
        }else{
            return false;
        }
        if(!empty($create['org_ids'])) {
            $data['org_ids'] = $create['org_ids'];
        }else{
            return false;
        }
        $create['created_at'] = date('Y-m-d H:i:s');
        $org_arr = explode(',',$data['org_ids']);
        $this->where(['supplier_id' => $data['id']])->delete();
        for($i=0;$i<count($org_arr);$i++){
            $arr['org_id']=$org_arr[$i];
            $arr['supplier_id']=$data['id'];
            $arr['created_at']=$create['created_at'];
            $arr['created_by']=$create['created_by'];
            $datajson = $this->create($arr);
            $res = $this->add($datajson);
        }
        return true;
    }

}
