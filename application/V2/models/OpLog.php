<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OpLogModel
 * @author  zhongyg
 * @date    2017-8-3 13:38:48
 * @version V2.0
 * @desc   
 */
class OpLogModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui2_sys';
    protected $tableName = 'op_log';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = [], $uid = 0) {
        try {
            $create['op_id'] = $uid;
            $create['created_at'] = $create['op_at'] = date('Y-m-d H:i:s');
            if (is_array($create['op_note'])) {
                $create['op_note'] = json_encode($create['op_note']);
            } else {
                $create['op_note'] = $create['op_note'];
            }

            $create['op_id'] = $uid;
            $data = $this->create($create);
            return $this->add($data);
        } catch (Exception $ex) {
            
        }
    }

}
