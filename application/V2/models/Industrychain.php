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
//        }//end验证
        $buyer_id = $data['buyer_id'];
        $created_by = $data['created_by'];
        if(empty($data['up']) && empty($data['down'])){ //添加空数据
            $null = $this->addNullData($buyer_id,$created_by);
            if($null){
                return 'nullData';
            }
        }
        if(!empty($data['up'])){    //up添加数据
            $upRes = $this->handleUpDown($industry_group='up',$data['up'],$buyer_id,$created_by);
        }
        if(!empty($data['down'])){  //down添加数据
            $downRes = $this->handleUpDown($industry_group='down',$data['down'],$buyer_id,$created_by);
        }
        if($upRes || $downRes){
            return true;
        }
        return false;
    }
    //添加空数据
    public function addNullData($buyer_id,$created_by){
        $arrNull = array(
            array(
                'buyer_id' => $buyer_id,
                'industry_group' => 'up',
                'created_by' => $created_by,
                'created_at' => date('Y-m-d H:i:s'),
            ),
            array(
                'buyer_id' => $buyer_id,
                'industry_group' => 'down',
                'created_by' => $created_by,
                'created_at' => date('Y-m-d H:i:s'),
            ),
        );
        $exist = $this->showNulldel($buyer_id,$created_by);
        if($exist){
            $resNull = $this->addAll($arrNull);
            if($resNull){
                return 'nullData';
            }
        }
        return false;
    }
    //创建up  和 down
    public function handleUpDown($industry_group='up',$data,$buyer_id,$created_by){
        $showDelRes = $this -> showgroupDel($industry_group,$buyer_id,$created_by);
        if($showDelRes == false){
            return false;
        }
        $checked = $this -> checkedSize($data);
        if($checked == false){
            return false;
        }
        foreach($data as $k => $v){
            $v['buyer_id'] = $buyer_id;
            $v['industry_group'] = $industry_group;
            $v['created_by'] = $created_by;
            $v['created_at'] = date('Y-m-d H:i:s');
            $res = $this -> add($v); //一条
            if($res == false){
                return false;
            }
        }
        return true;
    }
    //null-------删除up和down空数据
    public function showNulldel($buyer_id,$created_by){
        $null = $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by))->select();
        if(!empty($null)){
            $del = $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by))->delete();
            if(!$del){
                return false;
            }
        }
        return true;
    }
    //查看up和down删除
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
        return $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by,'industry_group'=>$industry_group))->delete();
    }
    //查询
    public function showgroupChain($industry_group,$buyer_id,$created_by){
        return $this->where(array('buyer_id'=>$buyer_id,'created_by'=>$created_by,'industry_group'=>$industry_group))->select();
    }
}