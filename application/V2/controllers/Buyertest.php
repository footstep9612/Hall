<?php
class BuyertestController extends PublicController
{

    public function init() {
        parent::init();
    }
    //信用评价
    public function creditAction(){
        set_time_limit(0);
        $buyer=new BuyerModel();
        $fieldArr=array(
            'id as buyer_id',
            'credit_level',
            'credit_type',
            'line_of_credit',
            'credit_available',
            'payment_behind',
            'behind_time',
            'reputation',
            'violate_treaty',
            'treaty_content',
            'comments'
        );
        $info=$buyer->field(implode(',',$fieldArr))->where(array('deleted_flag'=>'N'))->select();
        $credit=new CustomerCreditModel();
        $res=$credit->addAll($info);
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }
    //客户附件类型
    public function fileTypeAction(){
        set_time_limit(0);
        $attach=new BuyerattachModel();
        $info=$attach->field('id,attach_name')->select();
        $arr=[];
        foreach($info as $k => $v){
            $site=strripos($v['attach_name'],'.');
            if($site>=0){
                $name=strtoupper(substr($v['attach_name'],$site+1));
                $arr[]=$attach->where(array('id'=>$v['id']))->save(array('attach_type'=>$name));
            }
        }
        echo count($arr);
    }
    //客户附件类型
    public function fileSizeAction(){
        $config = \Yaf_Application::app()->getConfig();
        $host=$config['fastDFSUrl'];
        set_time_limit(0);
        $attach=new BuyerattachModel();
        $info=$attach->field('id,attach_url')->select();
        $arr=[];
        foreach($info as $k => $v){
            $url=$host.$v['attach_url'];
            $file = file_get_contents($url);
            $size=strlen($file);
            $arr[]=$attach->where(array('id'=>$v['id']))->save(array('attach_size'=>$size));
        }
        echo count($arr);
    }
    public function percentTestAction(){
        set_time_limit(0);
        $buyer=new BuyerModel();
        $info=$buyer->field('id as buyer_id')->where(array('deleted_flag'=>'N'))->select();
        $arr=[];
        foreach($info as $k => $v){
            $res=$this->percentInfo(array('buyer_id'=>$v['buyer_id']));
            $arr[]=$res['code'];
        }
        print_r(count($arr));die;
    }
    public function statusTestAction(){
        $buyer=new BuyerModel();
        $info=$buyer->field('id as buyer_id,status')->where(array('deleted_flag'=>'N'))->select();
        foreach($info as $k => $v){
            $res=$buyer->table('erui_buyer.buyer_agent')->where(array('buyer_id'=>$v['buyer_id'],'deleted_flag'=>'N'))->select();
            $count=count($res);
            if($v['status']=='APPROVING'){
                if($count>0){
                    $buyer->where(array('id'=>$v['buyer_id']))->save(array('status'=>'APPROVED'));
                }else{
                    $buyer->where(array('id'=>$v['buyer_id']))->save(array('status'=>'APPROVING'));
                }
            }
            if($v['status']=='APPROVED'){
                if($count>0){
                    $buyer->where(array('id'=>$v['buyer_id']))->save(array('status'=>'APPROVED'));
                }else{
                    $buyer->where(array('id'=>$v['buyer_id']))->save(array('status'=>'APPROVING'));
                }
            }
        }
        echo 1;
        $result=$buyer->field('id')
            ->where(array('source'=>1,'status'=>'APPROVING','deleted_flag'=>'N'))
            ->select();
        if(!empty($result)){
            $arr=[];
            foreach($result as $k => $v){
                $arr[]=$v['id'];
            }
            $str=implode(',',$arr);
            $result=$buyer
                ->where("id in ($str)")
                ->save(array('status'=>'APPROVED'));
            print_r($result);die;
        }else{
            echo 0;
        }
    }
    public function percentInfo($data){
        if(empty($data['buyer_id'])){
            $dataJson=array(
                'code'=>0,
                'message'=>'参数错误'
            );
            return $this->jsonReturn($dataJson);
        }
        //客户基本信息
        $base = new BuyerModel();
        $baseInfo=$base->percentBuyer($data);
        if(empty($baseInfo)){
            $dataJson=array(
                'code'=>0,
                'message'=>'暂无信息'
            );
            return $this->jsonReturn($dataJson);
        }
        unset($baseInfo['buyer_no']);
        //信用评价信息
        $credit=new CustomerCreditModel();
        $creditInfo=$credit->percentCredit($data);
        //联系人
        $contact = new BuyercontactModel();
        $contactInfo=$contact->percentContact($data);
        //上下游-竞争对手
        $chain=new IndustrychainModel();
        $chainInfo=$chain->percentChain($data);
        //业务信息
        $business=new BuyerBusinessModel();
        $businessInfo=$business->percentBusiness($data);
        //入网主题内容
        $subject=new NetSubjectModel();
        $netInfo=$subject->percentNetSubject($data);
        //采购计划
        $purchasing=new BuyerPurchasingModel();
        $purchasingInfo=$purchasing->percentPurchase($data);
        //里程碑事件
        $milestone_event=new MilestoneEventModel();
        $eventInfo=$milestone_event->percentMilestoneEvent($data);
        //附件=财务报表-公司人员组织架构-分析报告
        $attach=new BuyerattachModel();
        $cond=array('buyer_id'=>$data['buyer_id'],'deleted_flag'=>'N');
        $attachArr=$attach->field('attach_group,attach_name,attach_url')->where($cond)->group('attach_group')->select();

        //汇总
        $attachInfo=$attachArr?$attachArr:[];   //附件
        $infoArr=array_merge($baseInfo,$creditInfo,$contactInfo,$chainInfo,$businessInfo,$netInfo,$purchasingInfo,$eventInfo);  //信息
        $infoCount=count($infoArr)+3;  //总数
        //统计数据
        $infoExist=count(array_filter($infoArr))+count($attachInfo);
        //判断
        if(!empty($infoArr['is_warehouse'])){  //仓库
            if($infoArr['is_warehouse']=='N'){
                $infoExist += 1;
            }
        }
        if($infoArr['payment_behind']){    //拖欠货款
            if($infoArr['payment_behind']=='N'){
                $infoExist += 1;
            }
        }
        if($infoArr['violate_treaty']){    //是否违约
            if($infoArr['violate_treaty']=='N'){
                $infoExist += 1;
            }
        }
        //判断end
        $percent=floor(($infoExist / $infoCount)*100);
        //更新百分比
        $base->where(array('id'=>$data['buyer_id']))->save(array('percent'=>$percent));
        $dataJson=array(
            'code'=>1,
            'message'=>'档案信息完整度',
            'data'=>$percent
        );
        return $dataJson;
    }
    //2-buyer_code
    public function buyerCodeAction(){
        set_time_limit(0);
        $sql="SELECT id,count(buyer_code) as hh ,buyer_code from erui_buyer.buyer";
        $sql.=" GROUP BY buyer_code  HAVING hh BETWEEN 2 and 10  ORDER BY id desc";
        $model=new BuyerModel();
        $info=$model->query($sql);
        $count=0;
        foreach($info as $k => $v){
            $a=$model->query("select id from erui_buyer.buyer where buyer_code='$v[buyer_code]' and id !=$v[id]");
            $strId='';
            foreach($a as $ka => $va){
                $strId.=','.$va['id'];
            }
            $strId=mb_substr($strId,1);
            //订单
            $sqlOrder="update erui_new_order.order set buyer_id=$v[id] WHERE  crm_code='$v[buyer_code]'";
            $model->query($sqlOrder);
            //询单
            $sqlInquiry="update erui_rfq.inquiry set buyer_id=$v[id] WHERE  buyer_id in ($strId)";
            $model->query($sqlInquiry);
            //账号
            $sqlAccount="update erui_buyer.buyer_account set deleted_flag='Y' WHERE  buyer_id in ($strId)";
            $model->query($sqlAccount);
            //采购计划
            $sqlP="update erui_buyer.buyer_purchasing set buyer_id=$v[id] WHERE  buyer_id in ($strId)";
            $model->query($sqlP);
            $sqlAttach="update erui_buyer.purchasing_attach set buyer_id=$v[id] WHERE  buyer_id in ($strId)";
            $model->query($sqlAttach);
            //上下游
            $sqlUp="update erui_buyer.industry_chain set buyer_id=$v[id] WHERE  buyer_id in ($strId)";
            $model->query($sqlUp);
            //拜访
            $sqlVisit="update erui_buyer.buyer_visit set buyer_id=$v[id] WHERE  buyer_id in ($strId)";
            $model->query($sqlVisit);
            //联系人
            $sqlCon="update erui_buyer.buyer_contact set buyer_id=$v[id] WHERE  buyer_id in ($strId)";
            $model->query($sqlCon);
            //客户
            $sqlC="update erui_buyer.buyer set deleted_flag='Y' WHERE  id in ($strId)";
            $model->query($sqlC);
            $count++;
            sleep(1);
        }
        echo $count;
    }
}
