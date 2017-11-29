<?php
/**
 * name:
 * desc: 询价单表
 */
class IndustrychainModel extends PublicModel
{
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $tableName = 'industry_chain'; //数据表名
    protected $buyer_id = 123;
    public function __construct()
    {
        parent::__construct();
    }
    //chain数据编辑
    public function chainList($created_by=''){
        $buyer_id = $this -> buyer_id;
        $chainExist = $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by))->select();
        $arr = array();
        foreach($chainExist as $key => $value){
            if($value['industry_group']=='up'){
                $arr['up'][]=$value;
            }else{
                $arr['down'][] = $value;
            }
        }
        if($arr){
            return $arr;
        }
    }
    /**
     * 验证上下游数据是否为符合非空，1000,100
     * $data  上游|下游数据
     * return true  非空
     */
    public function checkedChainData($data){
        $inArr = array('cooperation','goods','profile');
        foreach($data as $key => $value){
            foreach($value as $k => $v){
                if (empty($v)) {
                    return false;
                }
                if(in_array($k,$inArr)){
                    if(strlen($v)>1000*3){
                        return false;
                    }
                }else{
                    if(strlen($v)>100*3){
                        return false;
                    }
                }
            }
        }
        return true;
    }
    //上下游创建数据
    public function createChain($data)
    {
        $buyer_id = $this -> buyer_id;
        $created_by = $data['created_by'];

        $up = $data['up'];
        $down = $data['down'];
        $upExist = $this -> checkedChainData($up);
        $downExist = $this -> checkedChainData($down);
        if(!$upExist || !$downExist){
            return false;
        }
        //重新保存,删除之前
        $chainInfo = $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by))->select();
        if($chainInfo){
            $chainDel = $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by))->delete();
        }
        //保存
        if($upExist){
            foreach($up as $k => $v){
                $up[$k]['buyer_id'] = $buyer_id;
                $up[$k]['industry_group'] = 'up';
                $up[$k]['created_by'] = $created_by;
                $up[$k]['created_at'] = date('Y-m-d H:i:s');
            }
            $upRes = $this -> addAll($up);
        }
        if($downExist){
            foreach($up as $k => $v){
                $down[$k]['buyer_id'] = $buyer_id;
                $down[$k]['industry_group'] = 'down';
                $down[$k]['created_by'] = $created_by;
                $down[$k]['created_at'] = date('Y-m-d H:i:s');
            }
            $downRes = $this -> addAll($down);
        }
        if($upRes && $downRes){
            return true;
        }
    }
}