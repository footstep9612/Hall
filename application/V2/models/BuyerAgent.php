<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author
 */
class BuyerAgentModel extends PublicModel {
    //put your code here
    protected $tableName = 'buyer_agent';
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $g_table = 'erui_buyer.buyer_agent';
//    protected $autoCheckFields = false;
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    //状态
    const STATUS_VALID = 'VALID'; //有效,通过
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_TEST = 'TEST'; //待报审；
    const STATUS_CHECKING = 'STATUS_CHECKING'; //审核；
    const STATUS_DELETED = 'DELETED'; //删除；

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */
    public function getlist($condition = [],$order=" id desc") {
        $counrty=$condition['country_bn'];
        $lang=isset($condition['lang'])?$condition['lang']:'zh';
        $field='buyer_agent.id,em.show_name,buyer_agent.buyer_id,buyer_agent.agent_id,em.name as agent_name,em.mobile,em.email,em.user_no as user_no,group_concat(`org`.`name`) as group_name,buyer_agent.role,buyer_agent.created_by,buyer_agent.created_at';
        if($lang=='en'){
            $field='buyer_agent.id,em.show_name,buyer_agent.buyer_id,buyer_agent.agent_id,em.name as agent_name,em.mobile,em.email,em.user_no as user_no,group_concat(`org`.`name_en`) as group_name,buyer_agent.role,buyer_agent.created_by,buyer_agent.created_at';
        }
        unset($condition['lang']);
        $condition['org.deleted_flag']='N';
        $condition['buyer_agent.deleted_flag']='N';
        if(!empty($counrty)){
            unset($condition['country_bn']);
            $field .= ',country_member.country_bn';
            return $this->where($condition)
                ->field($field)
                ->join('erui_sys.employee em on em.id=buyer_agent.agent_id', 'left')
                ->join('erui_sys.org_member on org_member.employee_id=buyer_agent.agent_id', 'left')
                ->join('erui_sys.org on org.id=org_member.org_id', 'left')
                ->join('erui_sys.country_member on buyer_agent.agent_id=country_member.employee_id', 'left')
                ->where("country_member.country_bn in ($counrty)")
                ->group('em.id')
                ->order('buyer_agent.id desc')
                ->select();
        }else{
            $info= $this->field($field)
                ->join('erui_sys.employee em on em.id=buyer_agent.agent_id and em.deleted_flag=\'N\'', 'left')
                ->join('erui_sys.org_member on org_member.employee_id=buyer_agent.agent_id', 'left')
                ->join('erui_sys.org on org.id=org_member.org_id and org.deleted_flag=\'N\'', 'left')
                ->where(array('buyer_agent.buyer_id'=>$condition['buyer_id'],'buyer_agent.deleted_flag'=>'N'))
                ->group('em.id')
                ->order('buyer_agent.id desc')
                ->select();
            return $info;
        }
    }

    public function create_data($create = [])
    {
        if(!empty($create['id'])) {
            $data['id'] = $create['id'];
        }else{
            return false;
        }
        if(!empty($create['user_ids'])) {
            $data['user_ids'] = $create['user_ids'];
        }else{
            return false;
        }
        $create['created_at'] = date('Y-m-d H:i:s');
        $user_arr = explode(',',$data['user_ids']);

        $this->where(['buyer_id' => $data['id']])->delete();
        for($i=0;$i<count($user_arr);$i++){
            $arr['agent_id']=$user_arr[$i];
            $arr['buyer_id']=$data['id'];
            $arr['created_at']=$create['created_at'];
            $arr['created_by']=$create['created_by'];
            $datajson = $this->create($arr);
            $res = $this->add($datajson);
        }
        return true;
    }
    /**
     * 个人信息查询
     * @param  $data 条件
     * @return
     * @author jhw
     */
    public function info($data)
    {
        if($data['id']) {
            $buyerInfo = $this->where(array("id" => $data['id']))
                              ->find();
            $sql ="SELECT  `id`,  `buyer_id`,  `attach_type`,  `attach_name`,  `attach_code`,  `attach_url`,  `status`,  `created_by`,  `created_at` FROM  `erui_buyer`.`buyer_attach` where deleted_flag ='N'";
            $row = $this->query( $sql );
            $buyerInfo['attach`']=$row;
            return $buyerInfo;
        } else{
            return false;
        }
    }

    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($create,$where){

        if(isset($create['buyer_no'])){
            $data['buyer_no'] = $create['buyer_no'];
        }
        if(isset($create['serial_no'])){
            $data['serial_no'] = $create['serial_no'];
        }
        if(isset($create['lang'])){
            $data['lang'] = $create['lang'];
        }else{
            $data['lang'] = 'en';
        }
        if(isset($create['name'])){
            $data['name'] = $create['name'];
        }
        if(isset($create['bn'])){
            $data['bn'] = $create['bn'];
        }
        if(isset($create['profile'])){
            $data['profile'] = $create['profile'];
        }
        if(isset($create['country_code'])){
            $data['country_code'] = $create['country_code'];
        }
        if(isset($create['country_bn'])){
            $data['country_bn'] = $create['country_bn'];
        }
        if(isset($create['official_email'])){
            $data['official_email'] = $create['official_email'];
        }
        if(isset($create['official_phone'])){
            $data['official_phone'] = $create['official_phone'];
        }
        if(isset($create['official_fax'])){
            $data['official_fax'] = $create['official_fax'];
        }
        if(isset($create['first_name'])){
            $data['first_name'] = $create['first_name'];
        }
        if(isset($create['last_name'])){
            $data['last_name'] = $create['last_name'];
        }
        if(isset($create['province'])){
            $data['province'] = $create['province'];
        }
        if(isset($create['logo'])){
            $data['logo'] = $create['logo'];
        }
        if(isset($create['city'])){
            $data['city'] = $create['city'];
        }

        if(isset($create['brand'])){
            $data['brand'] = $create['brand'];
        }
        if(isset($create['bank_name'])){
            $data['bank_name'] = $create['bank_name'];
        }
        if(isset($create['official_website'])){
            $data['official_website'] = $create['official_website'];
        }
        if(isset($create['remarks'])){
            $data['remarks'] = $create['remarks'];
        }
        if(isset($create['checked_by'])){
            $data['checked_by'] = $create['checked_by'];
        }
        if(isset($create['checked_by'])){
            $data['checked_by'] = $create['checked_by'];
        }
        if($create['status']){
            switch ($create['status']) {
                case self::STATUS_VALID:
                    $data['status'] = $create['status'];
                    break;
                case self::STATUS_INVALID:
                    $data['status'] = $create['status'];
                    break;
                case self::STATUS_DELETE:
                    $data['status'] = $create['status'];
                    break;
            }
        }

        return $this->where($where)->save($data);

    }

    /**
     * 通过顾客id获取会员等级
     * @author klp
     */
    public function getService($info,$token)
    {
        $where=array();
        if(!empty($token['customer_id'])){
            $where['customer_id'] = $token['customer_id'];
        } else{
            jsonReturn('','-1001','用户[id]不可以为空');
        }
        $lang = $info['lang'] ? strtolower($info['lang']) : (browser_lang() ? browser_lang() : 'en');
        //获取会员等级
        $buyerLevel =  $this->field('buyer_level')
                            ->where($where)
                            ->find();
        //获取服务
        $MemberBizService = new MemberBizServiceModel();
        $result = $MemberBizService->getService($buyerLevel,$lang);
        if($result){
            return $result;
        } else{
            return array();
        }
    }

    /**
     * 获取企业信息(数据表信息不全,待完善)_
     * @author klp
     */
    public function getBuyerInfo($info)
    {
        //$info['customer_id'] = '20170630000001'; $info['lang']='en';//测试
        $where=array();
        if(!empty($info['customer_id'])){
            $where['customer_id'] = $info['customer_id'];
        } else{
            jsonReturn('','-1001','用户[customer_id]不可以为空');
        }
        if (isset($info['lang']) && in_array($info['lang'], array('zh', 'en', 'es', 'ru'))) {
            $where['lang'] = strtolower($info['lang']);
        } else{
            $where['lang'] = 'en';
        }
        $field = 'lang,serial_no,name,bn,country,profile,reg_date,bank_name,swift_code,bank_address,bank_account,listed_flag,official_address,reg_date,capital_account,sales,official_phone,fax,official_website,employee_count,credit_total,credit_available,apply_at,approved_at,remarks';
        try{
            $buyerInfo =  $this->field($field)->where($where)->find();
            if($buyerInfo){
                //获取国家代码与企业邮箱与邮箱
                $BuyerAddressModel = new BuyerAddressModel();
                $addressInfo = $BuyerAddressModel->field('tel_country_code,official_email,zipcode')->where($where)->find();
                $buyerInfo['tel_country_code'] = $buyerInfo['official_email'] = $buyerInfo['zipcode'] = '';
                if($addressInfo){
                    $buyerInfo['tel_country_code'] = $$addressInfo['tel_country_code'];
                    $buyerInfo['official_email'] = $$addressInfo['official_email'];
                    $buyerInfo['zipcode'] = $$addressInfo['zipcode'];
                }
                $buyerRegInfo = new BuyerreginfoModel();
                $result = $buyerRegInfo->getBuyerRegInfo($where);
                return $result ? array_merge($buyerInfo,$result) : $buyerInfo;
            }
            return array();
        }catch (Exception $e){
            return array();
        }
    }

    /**
     * 企业信息新建-门户
     * @author klp
     */
    public function editInfo($token,$input)
    {
        if (!isset($input))
            return false;
        $this->startTrans();
        try {
            foreach ($input as $key => $item) {
                $arr = ['zh', 'en', 'ru', 'es'];
                if (in_array($key, $arr)) {
                    $checkout = $this->checkParam($item);
                    $data = [
                        'lang' => $key,
                        'customer_id' => $token['customer_id'],
                        'serial_no' => $token['customer_id'],
                        'name' => $checkout['name'],
//                        'country' => $checkout['country'],
                        'bank_name' => $checkout['bank_name'],
                        'bank_address' =>  $checkout['bank_address'],
                        'official_address' =>  $checkout['official_address'],
                        'bn' => isset($checkout['bn']) ? $checkout['bn'] : '',
                        'bank_account' => isset($checkout['bank_account']) ? $checkout['bank_account'] : '',
                        'profile' => isset($checkout['profile']) ? $checkout['profile'] : '',
                        'province' => isset($checkout['province']) ? $checkout['province'] : '',
                        'city' => isset($checkout['city']) ? $checkout['city'] : '',
                        'reg_date' => isset($checkout['reg_date']) ? $checkout['reg_date'] : '',
                        'swift_code' => isset($checkout['swift_code']) ? $checkout['swift_code'] : '',
                        'listed_flag' => isset($checkout['listed_flag']) ? $checkout['listed_flag'] : 'N',
                        'capital_account' => isset($checkout['capital_account']) ? (int)$checkout['capital_account'] : 0,
                        'sales' => isset($checkout['sales']) ? (int)$checkout['sales'] : 0,
                        'official_phone' => isset($checkout['official_phone']) ? $checkout['official_phone'] : '',
                        'fax' => isset($checkout['fax']) ? (int)$checkout['fax'] : '',
                        'official_website' => isset($checkout['official_website']) ? $checkout['official_website'] : '',
                        'employee_count' => isset($checkout['employee_count']) ? $checkout['employee_count'] : '',
                        'remarks' => isset($checkout['remarks']) ? $checkout['remarks'] : ''
                    ];
                    //判断是新增还是编辑,如果有customer_id就是编辑,反之为新增
                    $result = $this->field('customer_id')->where(['customer_id' => $token['customer_id'], 'lang' => $key])->find();
                    if ($result) {
                        $this->where(['customer_id' => $token['customer_id'], 'lang' => $key])->save($data);
                    } else {
                        $data['apply_at'] = date('Y-m-d H:i:s', time());
                        $data['status'] = self::STATUS_CHECKING;//待审状态
                        $this->add($data);
                    }
                    //t_buyer_reg_info
                    $buyerRegInfo = new BuyerreginfoModel();
                    $result = $buyerRegInfo->createInfo($token,$input);
                    if(!$result){
                        return false;
                    }
                    //t_buyer_address
                    $buyerAddressMode = new BuyerAddressModel();
                    $res = $buyerAddressMode->createInfo($token,$input);
                    if(!$res){
                        return false;
                    }
                }
            }
            $this->commit();
            return $token['customer_id'];
        } catch(\Kafka\Exception $e){
            $this->rollback();
            return false;
        }
    }
    /**
     * 参数校验-门户
     * @author klp
     */
    private function checkParam($param = []) {
        if (empty($param))
            return false;
        if(!isset($param['name']) && empty($param['name'])) { jsonReturn('','-1002','[name]不能为空');}
//        if(!isset($param['country']) && empty($param['country'])) { jsonReturn('','-1002','[country]不能为空');}
        if(!isset($param['bank_name']) && empty($param['bank_name'])) { jsonReturn('','-1002','[bank_name]不能为空');}
        if(!isset($param['bank_address']) && empty($param['bank_address'])) { jsonReturn('','-1002','[bank_address]不能为空');}
        if(!isset($param['official_address']) && empty($param['official_address'])) { jsonReturn('','-1002','[official_address]不能为空');}
        return $param;
    }

    /**
     * 提交易瑞   -- 待审核
     * @author klp
     */
    public function subCheck($data)
    {
        if (empty($data)) {
            return false;
        }
        //新状态可以补充
        $status = [];
        switch ($data['status_type']) {
            case 'check':    //审核
                $status['status'] = self::STATUS_CHECKING;
                break;

        }
        $result = $this->where($token['customer_id'])->save(['status' => $status['status']]);
        return $result ? true : false;
    }
    /**
     * 框架协议-客户市场经办人-列表
     * wangs
     */
    public function buyerMarketAgent($data){
        if(empty($data['buyer_id'])){
            return false;
        }
        if(!empty($data['page'])){
            $page = $data['page'];
        }
        $cond = "buyer_id=$data[buyer_id]";
        if(!empty($data['name'])){
            $cond .= " and name like '%$data[name]%'";
        }
        if(!empty($data['user_no'])){
            $cond .= " and user_no like '%$data[user_no]%'";
        }
        $info = $this -> MarketAgentlist($page,$cond);
        return $info;
    }

    /**
     * 框架协议-经办人-获取列表
     * wangs
     */
    public function MarketAgentlist($page,$cond=[]){
        $page = 1;
        if(isset($page) && is_numeric($page) && $page >0){
            $page = ceil($page);
        }
        $size = 10;
        $totalCount = $this -> alias('agent')
            ->join('erui_sys.employee em on em.id=agent.agent_id', 'left')
            -> where($cond)
            -> count();
        $totalPage = ceil($totalCount / $size);
        if($page > $totalPage && $totalPage > 0){
            $page = $totalPage;
        }
        $offset = ($page-1)*$size;
        $field = 'agent.buyer_id,agent.agent_id';
        $field .= ',em.name,em.user_no,em.mobile';
        $info = $this -> alias('agent')
            -> field($field)
            ->join('erui_sys.employee em on em.id=agent.agent_id', 'left')
            -> where($cond)
            ->group('em.id')
            ->order('agent.id desc')
            ->limit($offset,$size)
            ->select();
        $arr = array(
            'info'=>$info,
            'page'=>$page,
            'totalCount'=>$totalCount,
            'totalPage'=>$totalPage
        );
        return $arr;
    }
    /**
     * 客户管理首页-获取客户经理
     * wangs
     */
    public function getMarketAgent($ids){
        $arr = [];
        foreach($ids as $k => $v){
            $arr[$k]=$this->getBuyerAgentFind($v);
        }
        return $arr;
    }
    //buyer_id 获取 客户经理wangs
    public function getBuyerAgentFind($buyer_id){
        $info = $this->alias('agent')
            ->join('erui_sys.employee employee on agent.agent_id=employee.id')
            ->field('employee.name')
            ->where(array('agent.buyer_id'=>$buyer_id,'agent.deleted_flag'=>'N'))
            ->select();
        $str='';
        if(!empty($info)){
            foreach($info as $k=> $v){
                $str.=','.$v['name'];
            }
            $str=substr($str,1);
        }
        return $str;
    }
    //buyerList 获取经办人信息
    public function getBuyerAgentArr($buyer_id,$agent_name=''){
        $agent_name=trim($agent_name);
        $cond=array('agent.buyer_id'=>$buyer_id,'agent.deleted_flag'=>'N','employee.deleted_flag'=>'N');
        $info = $this->alias('agent')
            ->join('erui_sys.employee employee on agent.agent_id=employee.id')
            ->field('agent.agent_id,agent.created_at,employee.name')
            ->where($cond)
            ->select();
        $nameStr='';
        $idStr='';
        $arr=[
            'id'=>'',
            'name'=>''
        ];
        if(empty($agent_name)){
            if(!empty($info)){
                foreach($info as $k=> $v){
                    $idStr.=','.$v['agent_id'];
                    $nameStr.=','.$v['name'];
                }
                $arr['id']=substr($idStr,1);
                $arr['name']=substr($nameStr,1);
            }else{
                $arr['id']='';
                $arr['name']='';
            }
        }else{
            if(!empty($info)){
                foreach($info as $k=> $v){
                    if($v['name']==$agent_name){
                        $re[]=$v;
                        unset($info[$k]);
                    }
                }
                $test = array_merge($re, $info);
                foreach($test as $k=> $v){
                    $idStr.=','.$v['agent_id'];
                    $nameStr.=','.$v['name'];
                }
                $arr['id']=substr($idStr,1)?substr($idStr,1):'';
                $arr['name']=substr($nameStr,1)?substr($nameStr,1):'';
            }else{
                $arr['id']='';
                $arr['name']='';
            }
        }
        $arr['checked_at']=end($info)['created_at'];
        return $arr;
    }
    //crm 更新市场经办人-wangs
    public function crmUpdateAgent($data=[]){
        if(empty($data['id']) || empty($data['user_ids'])){
            return false;
        }
        $created_by=$data['created_by'];
        $time = date('Y-m-d H:i:s');
        $buyer_id=$data['id'];
        $agent_arr = explode(',', $data['user_ids']);
        $agent=$this->field('agent_id')->where(array('buyer_id'=>$buyer_id,'deleted_flag'=>'N'))->select();
        // 更改询单经办人
        if (!empty($agent_arr[0])) {
            (new InquiryModel())->where(['buyer_id' => $buyer_id])->save(['agent_id' => $agent_arr[0], 'updated_by' => $created_by, 'updated_at' => $time]);
        }
        if(empty($agent)){ //
            $agentArr=array();
            foreach($agent_arr as $k => $v){
                $agentArr[$k]['buyer_id']=$buyer_id;
                $agentArr[$k]['agent_id']=$v;
                $agentArr[$k]['created_by']=$created_by;
                $agentArr[$k]['created_at']=$time;
            }
            $res=$this->addAll($agentArr);
            (new BuyerModel())->where(['id' => $buyer_id])
                ->save(['agent_at' => $time,'agent_name'=>$created_by]);    //分配时间加入buyer
            return $res;
        }
        $this->where("buyer_id=$buyer_id")->delete();

        $agentArr=array();
        foreach($agent_arr as $k => $v){
            $agentArr[$k]['buyer_id']=$buyer_id;
            $agentArr[$k]['agent_id']=$v;
            $agentArr[$k]['created_by']=$created_by;
            $agentArr[$k]['created_at']=$time;
        }
        $res=$this->addAll($agentArr);
        return $res;
    }
    //buyer_id 获取 客户的经办人list
    public function getBuyerAgentList($buyer_id){
        $sql="SELECT agent.agent_id,";
        $sql.=" employee.user_no as user_no,";
        $sql.=" employee.name as agent_name,";
        $sql.=" employee.email as agent_email,employee.mobile as agent_emobile,";
        $sql.=" (select `name` from erui_sys.employee where id =agent.created_by) as created_name,";
        $sql.=" agent.created_by,agent.created_at";
        $sql.=" FROM erui_buyer.buyer_agent agent";
        $sql.=" left join  erui_sys.employee employee";
        $sql.=" on agent.agent_id=employee.id and employee.deleted_flag='N'";

        $sql.=" WHERE agent.buyer_id=$buyer_id and agent.deleted_flag='N'";
        $sql.=" order by agent.id";


        $info=$this->query($sql);
        $agentArr=[];
        foreach($info as $k => $v){
            $agentArr[$k]['user_no']=$v['user_no'];
            $agentArr[$k]['name']=$v['agent_name'];
            $agentArr[$k]['id']=$v['agent_id'];
            $agentArr[$k]['agent_email']=$v['agent_email'];
            $agentArr[$k]['agent_emobile']=$v['agent_emobile'];
        }
        $buyerField="agent_name,agent_at,";
        $buyerField.="(select name from erui_sys.employee where id=agent_name and deleted_flag='N') as name";
        $buyerInfo=$this->table('erui_buyer.buyer')
            ->field($buyerField)
            ->where(array('id'=>$buyer_id))
            ->find();
        $agent=[];
        $agent['created_by']=$buyerInfo['agent_name'];
        $agent['created_name']=$buyerInfo['name'];
        $agent['created_at']=$buyerInfo['agent_at'];
        if(!empty($agentArr)){
            $agent['update_at']=end($info)['created_at'];
            $agent['agent_info']=$agentArr;
        }else{
            $agent['update_at']=null;
            $agent['agent_info']=[];
        }

        return $agent;
    }
}
