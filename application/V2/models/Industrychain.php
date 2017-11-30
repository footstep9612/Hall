<?php
/**
 * name:
 * desc: 询价单表
 */
class IndustrychainModel extends PublicModel
{
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $tableName = 'industry_chain'; //数据表名
    public function __construct()
    {
        parent::__construct();
    }
    //up数据非空
    public function checkedUp($data){
        $arrUp = array('name','cooperation','business_type','scale','settlement');
        foreach($data as $key => $value){
            foreach($arrUp as $k => $v){
                if (empty($value[$v])) {
                    return false;
                }
            }
        }
        $res = $this -> checkedSize($data);
        if($res){
            return true;
        }
        return false;
    }
    //down数据非空
    public function checkedDown($data){
        $arrDown = array('name','cooperation','goods','profile','settlement','warranty_terms');
        foreach($data as $key => $value){
            foreach($arrDown as $k => $v){
                if (empty($value[$v])) {
                    return false;
                }
            }
        }
        $res = $this -> checkedSize($data);
        if($res){
            return true;
        }
        return false;
    }
    //验证输入字符长度
    public function checkedSize($data){
        $inArr = array('cooperation','goods','profile');
        foreach($data as $key => $value){
            foreach($value as $k => $v){
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
    //chain数据详情
    public function chainList($buyer_id,$created_by){
        $chainExist = $this -> showChain($buyer_id,$created_by);
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
    //上下游创建数据
    public function createChain($data)
    {
        $buyer_id = $data['buyer_id'];
        $created_by = $data['created_by'];
        if(empty($data['up']) || empty($data['down'])){
            return false;
        }
        $up = $data['up'];
        $down = $data['down'];
        $checkedUp = $this -> checkedUp($up);
        $checkedDown = $this -> checkedDown($down);
        if($checkedUp == false || $checkedDown == false){
            return false;
        }
        //end验证
        //重新保存,删除之前
        $showDelRes = $this -> showDel($buyer_id,$created_by);
        if(!$showDelRes){
            return false;
        }
        foreach($up as $k => $v){
            $up[$k]['buyer_id'] = $buyer_id;
            $up[$k]['industry_group'] = 'up';
            $up[$k]['created_by'] = $created_by;
            $up[$k]['created_at'] = date('Y-m-d H:i:s');
        }
        $upRes = $this -> addAll($up);
        foreach($up as $k => $v){
            $down[$k]['buyer_id'] = $buyer_id;
            $down[$k]['industry_group'] = 'down';
            $down[$k]['created_by'] = $created_by;
            $down[$k]['created_at'] = date('Y-m-d H:i:s');
        }
        $downRes = $this -> addAll($down);
        if($upRes && $downRes){
            return true;
        }
    }
    //查看删除
    public function showDel($buyer_id,$created_by){
        $chainInfo = $this -> showChain($buyer_id,$created_by);
        if(!empty($chainInfo)){
            $chainDel = $this -> delChain($buyer_id,$created_by);
            if(!$chainDel){
                return false;
            }
        }
        return true;
    }
    //删除
    public function delChain($buyer_id,$created_by){
        $res = $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by))->delete();
        if($res){
            return true;
        }
    }
    //查询
    public function showChain($buyer_id,$created_by){
        $res = $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by))->select();
        return $res;
    }
}