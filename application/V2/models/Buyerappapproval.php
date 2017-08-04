<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Buyerappapproval
 *
 * @author zhongyg
 */
class BuyerappapprovalModel extends PublicModel {

    //put your code here
    protected $tableName = 'buyer_app_approval';
    protected $dbName = 'erui_buyer';
    Protected $autoCheckFields = false;

    //APPROVED-通过；PARTLY-部分通过；REJECTED-未通过
    const STATUS_PARTLY = 'PARTLY'; //部分通过
    const STATUS_CHECKING = 'CHECKING'; //CHECKING-审核
    const STATUS_APPROVED = 'APPROVED'; //CHECKING-审核通过
    const STATUS_REJECTED = 'REJECTED'; //CHECKING-审核驳回

    public function __construct($str = '') {
        parent::__construct($str = '');
    }


    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getcount($condition = []) {

    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = []) {

    }

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($code = '', $id = '', $lang = '') {

    }

    /**
     * 删除数据
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return bool
     * @author zyg
     */
    public function delete_data($code = '', $id = '', $lang = '') {

    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return bool
     * @author zyg
     */
    public function update_data($upcondition = []) {

    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($createcondition = []) {

    }
}
