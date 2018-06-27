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
class BuyerCheckedLogModel extends PublicModel {
    //put your code here
    protected $tableName = 'buyer_checked_log';
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $g_table = 'erui_buyer.buyer_checked_log';
//    protected $autoCheckFields = false;
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = [],$order=" id desc") {
        return $this->where($condition)
            ->field('buyer_checked_log.*,em.show_name,em.name as checked_name')
            ->join('erui_sys.employee em on em.id=buyer_checked_log.checked_by', 'left')
            ->order('buyer_checked_log.id asc')
            ->select();
    }
    public function create_data($create = [])
    {
        if(!empty($create['id'])) {
            $data['buyer_id'] = $create['id'];
        }else{
            return false;
        }
        if(!empty($create['checked_by'])) {
            $data['checked_by'] = $create['checked_by'];
        }else{
            return false;
        }
        if(!empty($create['status'])) {
            $data['status'] = $create['status'];
        }else{
            return false;
        }
        if(!empty($create['remarks'])) {
            $data['remarks'] = $create['remarks'];
        }
        $data['checked_at']=Date("Y-m-d H:i:s");
        $datajson = $this->create($data);
        $res = $this->add($datajson);
        return true;
    }
}
