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
                    if(strlen($v)>300){
                        return false;
                    }
                }else{
                    if(strlen($v)>150){
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
    //查询up,down
    public function showChain($buyer_id,$created_by){
        $res = $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by))->select();
        return $res;
    }
    //上下游创建数据
    public function createChain($data)
    {
//        $checkedUp = $this -> checkedUp($up);
//        $checkedDown = $this -> checkedDown($down);
//        if($checkedUp == false || $checkedDown == false){
//            return false;
//        }
        //end验证
        $buyer_id = $data['buyer_id'];
        $created_by = $data['created_by'];
        //重新保存,删除之前
//        $showDelRes = $this -> showDel($buyer_id,$created_by);
//        if($showDelRes == false){
//            return false;
//        }
        if(!empty($data['up'])){
            $showDelResUP = $this -> showgroupDel($industry_group='up',$buyer_id,$created_by);
            if($showDelResUP == false){
                return false;
            }
            $up = $data['up'];
            $checkedUp = $this -> checkedSize($up);
            if($checkedUp == false){
                return false;
            }
            foreach($up as $k => $v){
                $v['buyer_id'] = $buyer_id;
                $v['industry_group'] = 'up';
                $v['created_by'] = $created_by;
                $v['created_at'] = date('Y-m-d H:i:s');
                $upRes = $this -> add($v); //一条
                if($upRes == false){
                    return false;
                }
            }
        }
        if(!empty($data['down'])){
            $showDelResDown = $this -> showgroupDel($industry_group='down',$buyer_id,$created_by);
            if($showDelResDown == false){
                return false;
            }
            $down = $data['down'];
            $checkedDown = $this -> checkedSize($down);
            if($checkedDown == false){
                return false;
            }
            foreach($down as $k => $v){
                $v['buyer_id'] = $buyer_id;
                $v['industry_group'] = 'down';
                $v['created_by'] = $created_by;
                $v['created_at'] = date('Y-m-d H:i:s');
                $downRes = $this -> add($v);
                if($downRes == false){
                    return false;
                }
            }
        }
        if($upRes || $downRes){
            return true;
        }
    }
    //查看删除
    public function showgroupDel($industry_group,$buyer_id,$created_by){
        $chainInfo = $this -> showgroupChain($industry_group,$buyer_id,$created_by);
        if(!empty($chainInfo)){
            $chainDel = $this -> delgroupChain($industry_group,$buyer_id,$created_by);
            if(!$chainDel){
                return false;
            }
        }
        return true;
    }
    //删除
    public function delgroupChain($industry_group,$buyer_id,$created_by){
        $res = $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by,'industry_group'=>$industry_group))->delete();
        if($res){
            return true;
        }
    }
    //查询
    public function showgroupChain($industry_group,$buyer_id,$created_by){
        $res = $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by,'industry_group'=>$industry_group))->select();
        return $res;
    }
}