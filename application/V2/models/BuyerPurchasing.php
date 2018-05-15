<?php
//客户业务信息 wangs
class BuyerPurchasingModel extends PublicModel
{
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $tableName = 'buyer_purchasing'; //采购商业务信息表名
    public function __construct()
    {
        parent::__construct();
    }
    public function updatePurchase($purchase,$buyer_id,$created_by){
        $cond=array(
            'buyer_id'=>$buyer_id,
//            'created_by'=>$created_by,
            'deleted_flag'=>'N'
        );
        $arrId=array();
        $existId=$this->field('id')->where($cond)->select();
        foreach($existId as $v){
            $arrId[]=$v['id'];
        }
        $inputId=array();
        $field=array(
            'id', //采购时间
            'purchasing_at', //采购时间
            'purchasing_budget', //采购时采购预算间
            'purchasing_plan' //采购计划
        );
        $arr=array();
        foreach($purchase as $key => $value){
            foreach($field as $k => $v){
                if(!empty($value[$v])){
                    $arr[$key][$v]=$value[$v];
                }else{
                    $arr[$key][$v]=null;
                }
            }
            $arr[$key]['buyer_id']=$buyer_id;
            $arr[$key]['created_by']=$created_by;
            $arr[$key]['created_at']=date('Y-m-d H:i:s');
            if(!empty($arr[$key]['id'])){
                $inputId[]=$arr[$key]['id'];
                $this->where(array('id'=>$arr[$key]['id']))->save($arr[$key]);
                $attach=array(
                    'attach_group'=>'PURCHASING',
                    'attach_name'=>$value['attach_name'],
                    'attach_url'=>$value['attach_url'],
                    'created_by'=>$created_by,
                    'created_at'=>date('Y-m-d H:i:s')
                );
                $attachModel=new PurchasingAttachModel();
                $attachModel->where(array('id'=>$value['attach_id']))->save($attach);
            }else{
                $purchaseId=$this->add($arr[$key]);
                $attach=array(
                    'buyer_id'=>$buyer_id,
                    'purchasing_id'=>$purchaseId,
                    'attach_group'=>'PURCHASING',
                    'attach_name'=>$value['attach_name'],
                    'attach_url'=>$value['attach_url'],
                    'created_by'=>$created_by,
                    'created_at'=>date('Y-m-d H:i:s')
                );
                $attachModel=new PurchasingAttachModel();
                $attachModel->add($attach);
            }
        }

        $diff=array_diff($arrId,$inputId);
        if(!empty($diff)){
            $strId=implode(',',$diff);
            $this->where("id in ($strId)")->save(array('deleted_flag'=>'Y','created_at'=>date('Y-m-d H:i:s')));
            $attachModel->where("purchasing_id in ($strId)")->save(array('deleted_flag'=>'Y','created_at'=>date('Y-m-d H:i:s')));
        }
        return true;
    }
    public function addPurchase($data,$buyer_id,$created_by){
        $purchase=array(
            'buyer_id'=>$buyer_id,
            'purchasing_at'=>$data['purchasing_at'], //采购时间
            'purchasing_budget'=>$data['purchasing_budget'], //采购时采购预算间
            'purchasing_plan'=>$data['purchasing_plan'], //采购计划
            'created_by'=>$created_by,
            'created_at'=>date('Y-m-d H:i:s')
        );
        $purchaseId=$this->add($purchase);
        $attach=array(
            'buyer_id'=>$buyer_id,
            'purchasing_id'=>$purchaseId,
            'attach_group'=>'PURCHASING',
            'attach_name'=>$data['attach_name'],
            'attach_url'=>$data['attach_url'],
            'created_by'=>$created_by,
            'created_at'=>date('Y-m-d H:i:s')
        );
        $attachModel=new PurchasingAttachModel();
        $attachModel->add($attach);
        return true;
    }
    //创建采购计划
    public function createPurchase($purchase,$buyer_id,$created_by)
    {
        $field=array(
            'purchasing_at', //采购时间
            'purchasing_budget', //采购时采购预算间
            'purchasing_plan' //采购计划
        );
        $arr=array();
        foreach($purchase as $key => $value){
            foreach($field as $k => $v){
                if(!empty($value[$v])){
                    $arr[$key][$v]=$value[$v];
                }else{
                    $arr[$key][$v]=null;
                }
            }
            $arr[$key]['buyer_id']=$buyer_id;
            $arr[$key]['created_by']=$created_by;
            $arr[$key]['created_at']=date('Y-m-d H:i:s');
            $purchaseId=$this->add($arr[$key]);
                $arrach=array(
                    'buyer_id'=>$buyer_id,
                    'purchasing_id'=>$purchaseId,
                    'attach_group'=>'PURCHASING',
                    'attach_name'=>$value['attach_name'],
                    'attach_url'=>$value['attach_url'],
                    'created_by'=>$created_by,
                    'created_at'=>date('Y-m-d H:i:s')
                );
                $attachModel=new PurchasingAttachModel();
                $attachModel->add($arrach);
        }
        return true;
    }
    //采购计划删除
    public function delPurchase($buyer_id,$created_by){
        $map = array(
            'buyer_id'=>$buyer_id,
            'created_by'=>$created_by
        );
        $res = $this -> where($map) -> delete();
        return $res;
    }
    //查询采购计划 和 采购计划附件
    public function showPurchase1($buyer_id,$created_by){
        $map = array(
            'purchasing.buyer_id'=>$buyer_id,
//            'purchasing.created_by'=>$created_by,
            'purchasing.deleted_flag'=>'N',
            'attach.attach_group'=>'PURCHASING',
            'attach.deleted_flag'=>'N',
        );
        $fieldArr = array(
            'id',   //采购计划id
            'buyer_id',   //采购商id
//            'purchasing_at',   //采购计划日期 DATE_FORMAT(purchasing_at,'%Y')
            'purchasing_budget',   //采购预算
            'purchasing_plan',   //采购计划
//            'created_by',   //创建人
//            'created_at',   //创建时间
        );
        $field = 'DATE_FORMAT(purchasing.purchasing_at,\'%Y\') as purchasing_at,';
        $field .= 'attach.id as attach_id,attach.attach_name,attach.attach_url';
        foreach($fieldArr as $v){
            $field .= ',purchasing.'.$v;
        }
        $info = $this->alias('purchasing')
            ->join('erui_buyer.purchasing_attach attach on purchasing.id=attach.purchasing_id','left')
            ->field($field)
            ->where($map)
            ->select();
        return $info;
    }
    public function showPurchaseList($data){
        if(empty($data['buyer_id'])){
            return false;
        }
        $map = array(
            'purchasing.buyer_id'=>$data['buyer_id'],
            'purchasing.deleted_flag'=>'N',
            'attach.deleted_flag'=>'N',
        );
        $fieldArr = array(
            'id',   //采购计划id
            'buyer_id',   //采购商id
//            'purchasing_at',   //采购计划日期 DATE_FORMAT(purchasing_at,'%Y')
            'purchasing_budget',   //采购预算
            'purchasing_plan',   //采购计划
//            'created_by',   //创建人
//            'created_at',   //创建时间
        );
        $field='';
        foreach($fieldArr as $v){
            $field .= 'purchasing.'.$v.',';
        }
        $field .= 'DATE_FORMAT(purchasing.purchasing_at,\'%Y\') as purchasing_at,';
        $field .= 'attach.id as attach_id,attach.attach_name,attach.attach_url';

        $info = $this->alias('purchasing')
            ->join('erui_buyer.purchasing_attach attach on purchasing.id=attach.purchasing_id','left')
            ->field($field)
            ->where($map)
            ->select();
        if(empty($info)){
            $info=[];
        }
        return $info;
    }
    public function editPurchase($data)
    {
        $arr['purchasing_at'] = isset($data['purchasing_at']) ? $data['purchasing_at'] : null;   //采购时间
        $arr['purchasing_budget'] = isset($data['purchasing_budget']) ? $data['purchasing_budget'] : null;   //采购时采购预算间
        $arr['purchasing_plan'] = isset($data['purchasing_plan']) ? $data['purchasing_plan'] : null;   //采购时间

        $arr['buyer_id'] = $data['buyer_id'];
        $arr['created_by'] = $data['created_by'];
        $arr['created_at'] = date('Y-m-d H:i:s');

        if (!empty($data['id'])) {    //编辑
            unset($arr['buyer_id']);
            $this->where(array('id' => $data['id']))->save($arr);
            return true;
        }
        $res = $this->add($arr);
        if ($res) {
            return true;
        } else {
            return false;
        }
    }
    public function showPurchase($data){
        if(empty($data['id'])){
            return false;
        }
        $map = array(
            'purchasing.id'=>$data['id'],
            'purchasing.deleted_flag'=>'N',
            'attach.deleted_flag'=>'N',
        );
        $fieldArr = array(
            'id',   //采购计划id
            'buyer_id',   //采购商id
//            'purchasing_at',   //采购计划日期 DATE_FORMAT(purchasing_at,'%Y')
            'purchasing_budget',   //采购预算
            'purchasing_plan',   //采购计划
//            'created_by',   //创建人
//            'created_at',   //创建时间
        );
        $field='';
        foreach($fieldArr as $v){
            $field .= 'purchasing.'.$v.',';
        }
        $field .= 'DATE_FORMAT(purchasing.purchasing_at,\'%Y\') as purchasing_at,';
        $field .= 'attach.id as attach_id,attach.attach_name,attach.attach_url';

        $info = $this->alias('purchasing')
            ->join('erui_buyer.purchasing_attach attach on purchasing.id=attach.purchasing_id','left')
            ->field($field)
            ->where($map)
            ->select();
        if(empty($info)){
            $info=[];
        }
        return $info;
    }
}