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
    public function industryChainList($data){
        if(empty($data['buyer_id']) || $data['type']){
            return false;
        }
        if($data['type']=='up'){

        }elseif($data['type']=='down'){

        }elseif($data['type']=='competitor'){

        }
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
        $arr = array(
            'name'=>'客户名称',
            'cooperation'=>'合作情况',
            'business_type'=>'业务类型',
            'scale'=>'规模',
            'settlement'=>'结算方式',
            'marketing_network'=>'营销网络',
//            'buyer_type_name'=>'客户的客户类型名称',
            'buyer_project'=>'客户参与的项目',
            'buyer_problem'=>'客户遇到过的困难',
            'solve_problem'=>'客户如何解决的困难',
            'profile'=>'简介',
            'goods'=>'商品',
            'warranty_terms'=>'保质条款',
            'relationship'=>'供应商与客户关系如何',
            'analyse'=>'与KERUI/ERUI的对标分析',
            'dynamic'=>'供应商动态',
            'competitor_name'=>'竞争对手名称',
            'competitor_area'=>'竞争领域',
            'company_compare'=>'两公司优劣势对比',
            'what_plan'=>'KERUI/ERUI可以做什么'
        );
        foreach($data as $key => $value){
            foreach($arr as $k => $v){
                if(!empty($value[$k])){
                    if(strlen($value[$k]) > 1500){
                        return $v;
                    }
                }
            }
        }
        return true;
    }
    //chain数据详情
    public function chainList($buyer_id,$created_by){
        $chainExist = $this -> showChain($buyer_id,$created_by);
        $up = array(
            "buyer_type_name",
            "profile",
            "goods",
            "warranty_terms",
            "relationship",
            "analyse",
            "dynamic",
            "competitor_name",
            "competitor_area",
            "company_compare",
            "what_plan"
        );
        $down = array(
            "business_type",
            "scale",
            "marketing_network",
            "buyer_type_name",
            "buyer_project",
            "buyer_problem",
            "solve_problem",
            "competitor_name",
            "competitor_area",
            "company_compare",
            "what_plan"
        );
        $competitor = array(
            "name",
            "cooperation",
            "business_type",
            "scale",
            "settlement",
            "marketing_network",
            "buyer_type_name",
            "buyer_project",
            "buyer_problem",
            "solve_problem",
            "profile",
            "goods",
            "warranty_terms",
            "relationship",
            "analyse",
            "dynamic"
        );
        $arr = array();
        foreach($chainExist as $key => $value){
            if($value['industry_group']=='up'){
                foreach($up as $kup => $vup){
                    unset($value[$vup]);
                }
                $arr['up'][]=$value;
            }elseif($value['industry_group']=='down'){
                foreach($down as $kdown => $vdown){
                    unset($value[$vdown]);
                }
                $arr['down'][] = $value;
            }elseif($value['industry_group']=='competitor'){
                foreach($competitor as $kcompetitor => $vcompetitor){
                    unset($value[$vcompetitor]);
                }
                $arr['competitor'][] = $value;
            }
        }
        return $arr;
    }
    //查询上下游,竞争对手的详情2
    public function showChain($buyer_id,$created_by){
        $cond = array(
            'buyer_id'=>$buyer_id,
//            'created_by'=>$created_by,
            'deleted_flag'=>'N'
        );
        $res = $this->where($cond)->select();
        return $res;
    }

    /**
     * 编辑上下游,竞争对手数据--王帅
     */
    public function updateIndustryChaindata($industry_group,$data,$buyer_id,$created_by){
        $cond=array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by,
            'deleted_flag'=>'N'
        );
        $arrId=array();
        $existId=$this->field('id')->where($cond)->select();
        foreach($existId as $k => $v){
            if(!empty($v['id'])){
                $arrId[]=$v['id'];
            }
        }
        foreach($data as $k => $v){
            if(!empty($v['id'])){
                if(in_array($v['id'],$arrId)){
                    $v['id'] = $v['id'];
                    $v['buyer_id'] = $buyer_id;
                    $v['industry_group'] = $industry_group;
                    $v['created_by'] = $created_by;
                    $v['created_at'] = date('Y-m-d H:i:s');
                    $this->where(array('id'=>$v['id']))->save($v);
                }else{

                }
            }else{
                $v['buyer_id'] = $buyer_id;
                $v['industry_group'] = $industry_group;
                $v['created_by'] = $created_by;
                $v['created_at'] = date('Y-m-d H:i:s');
                $res = $this -> add($v); //一条
            }

        }
    }
    /**
     * @param $data
     * 上下游编辑数据-wangs
     */
    public function updateChain($data){
        $buyer_id = $data['buyer_id'];
        $created_by = $data['created_by'];
        if(!empty($data['up']) || !empty($data['down']) || !empty($data['competitor'])){    //验证数据的有效性
            $up=$this->checkedSize($data['up']);
            if($up !== true){
                return $up;
            }
            $down=$this->checkedSize($data['down']);
            if($down !== true){
                return $down;
            }
            $com=$this->checkedSize($data['competitor']);
            if($com !== true){
                return $com;
            }
        }
        $cond=array(    //查询数据
            'buyer_id'=>$buyer_id,
//            'created_by'=>$created_by,
            'deleted_flag'=>'N'
        );
        $arrId=array();
        $inputId=array();
        $existId=$this->field('id')->where($cond)->select();
        foreach($existId as $k => $v){
            if(!empty($v['id'])){
                $arrId[]=$v['id'];  //存在的id
            }
        }
        $info=array_merge($data['up'],$data['down'],$data['competitor']);
        foreach($info as $k => $v){
            if(!empty($v['id'])) {
                $inputId[] = $v['id'];    //编辑的id
            }
        }
        $diffArr=array_diff($arrId,$inputId);  //删除
        $diffId=implode(',',$diffArr);
        foreach($info as $k => $v){
            if(!empty($v['id'])){
                $v['buyer_id']=$buyer_id;
//                $v['created_by']=$created_by;
                $v['created_at']=date('Y-m-d H:i:s');
                $this->where(array('id'=>$v['id']))->save($v);  //编辑
                if(!empty($diffId)){
                    $this->where("id in ($diffId)")->save(array('deleted_flag'=>'Y','created_at'=>date('Y-m-d H:i:s')));    //删除
                }
            }
        }
        if(!empty($data['up'])){    //up添加数据
            $this->createIndustryChaindata('up',$data['up'],$buyer_id,$created_by);
        }
        if(!empty($data['down'])){  //down添加数据
            $this->createIndustryChaindata('down',$data['down'],$buyer_id,$created_by);
        }
        if(!empty($data['competitor'])){  //competitor添加数据
            $this->createIndustryChaindata('competitor',$data['competitor'],$buyer_id,$created_by);
        }
        return true;
    }
    //上下游创建数据
    public function createChain($data)
    {
        $buyer_id = $data['buyer_id'];
        $created_by = $data['created_by'];
        if($data['is_edit'] == true){   //编辑上下游,竞争对手
            $update=$this->updateChain($data);
            return $update;
        }
        //添加创建空数据
        if(empty($data['up']) && empty($data['down']) && empty($data['competitor'])){
            $null = $this->addNullData($buyer_id,$created_by);
            if($null){
                return 'nullData';
            }
        }
        if(!empty($data['up']) || !empty($data['down']) || !empty($data['competitor'])){
            $up=$this->checkedSize($data['up']);
            if($up !== true){
                return $up;
            }
            $down=$this->checkedSize($data['down']);
            if($down !== true){
                return $down;
            }
            $com=$this->checkedSize($data['competitor']);
            if($com !== true){
                return $com;
            }
        }
        if(!empty($data['up'])){    //up添加数据
            $upRes = $this->createIndustryChaindata('up',$data['up'],$buyer_id,$created_by);
        }
        if(!empty($data['down'])){  //down添加数据
            $downRes = $this->createIndustryChaindata('down',$data['down'],$buyer_id,$created_by);
        }
        if(!empty($data['competitor'])){  //competitor添加数据
            $competitorRes = $this->createIndustryChaindata('competitor',$data['competitor'],$buyer_id,$created_by);
        }
        if($upRes || $downRes || $competitorRes){
            return true;
        }
        return false;
    }

    /**
     * @param $industry_group   up上游,down下游,competitor竞争对手
     * @param $data 创建的数据arr
     * @param $buyer_id 客户id
     * @param $created_by   创建人
     * @return bool true 成功
     * 创建上游,下游,竞争对手数据-王帅
     */
    public function createIndustryChaindata($industry_group,$data,$buyer_id,$created_by){
        $flag=true;
        foreach($data as $k => $v){
            if(empty($v['id'])){
                $v['buyer_id'] = $buyer_id;
                $v['industry_group'] = $industry_group;
                $v['created_by'] = $created_by;
                $v['created_at'] = date('Y-m-d H:i:s');
                $res = $this -> add($v); //一条
                if(!$res && $flag){
                    $flag=false;
                }
            }
        }
        return $flag;
    }
    //创建上下游,竞争对手信息空数据-王帅
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
            array(
                'buyer_id' => $buyer_id,
                'industry_group' => 'competitor',
                'created_by' => $created_by,
                'created_at' => date('Y-m-d H:i:s'),
            )
        );
        $resNull = $this->addAll($arrNull); //返回添加第一条数据的id
        if($resNull){
            return true;
        }else{
            return false;
        }
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