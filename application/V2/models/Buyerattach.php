<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Buyerattach
 *
 * @author zhongyg
 */
class BuyerattachModel extends PublicModel {

    //put your code here
    protected $tableName = 'buyer_attach';
    protected $dbName = 'erui2_buyer';
    Protected $autoCheckFields = false;

    const STATUS_NORMAL = 'NORMAL'; //NORMAL-正常；
    const STATUS_DISABLED = 'DISABLED'; //DISABLED-禁止；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    protected function getcondition($condition = []) {

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
        if($upcondition['attach_url']&&$upcondition['buyer_id']){
            $info = $this->where(['buyer_id'=>$upcondition['buyer_id'],'deleted_flag' => 'N'])->find();
            if($info){
                $this->where(['buyer_id'=>$upcondition['buyer_id'],'deleted_flag' => 'N'])->save(['deleted_flag' => 'Y']);
            }
            $upcondition['created_at'] =date("Y-m-d H:i:s");
            $data = $this->create($upcondition);
            return $this->add($data);
        }
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($createcondition = []) {
        /**
         * @desc 添加报价单附件详情
         * @author zhangyuliang 2017-06-29
         * @param array $condition
         * @return array
         */
            $data = $this->create($createcondition);
            return $this->add($data);
    }
}
