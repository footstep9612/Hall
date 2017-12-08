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
    protected $dbName = 'erui_buyer';
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

    /*
     * 创建财务报表
     * attach_name,attach_url
     * wangs
     */
    public function createBuyerFinanceTable($attach_name,$attach_url,$buyer_id,$created_by){
        $cond = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by,
            'attach_group'=>'FINANCE',
            'deleted_flag'=>'N',
        );
        $this->startTrans();    //开启事务
        $exist = $this->where($cond)->find();
        if(!empty($exist)){
            $this->where($cond)->save(array('deleted_flag'=>'Y'));
        }
        $arr = array(
            'buyer_id'=>$buyer_id,
            'attach_group'=>'FINANCE',
            'attach_name'=>$attach_name,
            'attach_url'=>$attach_url,
            'created_by'=>$created_by,
            'created_at'=>date('Y-m-d H:i:s'),
        );
        $res = $this -> add($arr);
        if($res){
            $this->commit();
            return true;
        }else{
            $this->rollback();
            return false;
        }
    }

    /**
     * 创建采购计划报表-
     * -wangs
     */
    public function createBuyerPurchaseTable($attach,$buyer_id,$created_by){
        $info = array();
        foreach($attach as $key => $value){
            if(!empty($value)){
                $info[$key] = $value;
            }
        }
        $arr = array(
            'buyer_id'=>$buyer_id,
            'attach_group'=>'PURCHASING',  //附件分组，PURCHASING，采购计划，
            'created_by'=>$created_by,
            'created_at'=>date('Y-m-d H:i:s'),
        );
        $flag = true;
        $this->startTrans();    //开启事物
        //如数据存在，则删除，重新添加
        $exist = $this->showBuyerAttach($buyer_id,$created_by);
        if(!empty($exist)){
            $this->delBuyerAttach($buyer_id,$created_by);
        }
        foreach($info as $k => $v){
            if(!empty($v['attach_name'])){
                $arr['attach_name'] = $v['attach_name'];
            }
            if(!empty($v['attach_url'])){
                $arr['attach_url'] = $v['attach_url'];
            }
            $arr['purchasing_id'] = $k;
            $res = $this->add($arr);
            if(!$res && $flag){
                $flag = false;
            }
        }
        if($flag){
            $this->commit();
        }else{
            $this->rollback();
        }
        return $flag;
    }
    //按条件客户id，创建人删除附件
    public function delBuyerAttach($buyer_id,$created_by){
        return $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by))->delete();
    }
    //按条件客户id，创建人,查询附件
    public function showBuyerAttach($buyer_id,$created_by){
        return $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by))->select();
    }
    //按条件客户id，创建人,删除表示，查询附件
    public function showBuyerExistAttach($buyer_id,$created_by,$deleted_flag='N'){
        $cond = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by,
            'deleted_flag'=>$deleted_flag,
            'attach_group'=>FINANCE
        );
        return $this->field('attach_name,attach_url')
            ->where($cond)
            ->find();
    }
}
