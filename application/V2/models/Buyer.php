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
class BuyerModel extends PublicModel {

    //put your code here
    protected $tableName = 'buyer';
    protected $dbName = 'erui_buyer'; //数据库名称
    protected $g_table = 'erui_buyer.buyer';

//    protected $autoCheckFields = false;
    public function __construct() {
        parent::__construct();
    }

    //状态

    const STATUS_APPROVING = 'APPROVING'; //待分配；
//    const STATUS_FIRST_APPROVED = 'FIRST_APPROVED'; //待报审；
//    const STATUS_FIRST_REJECTED = 'FIRST_REJECTED'; //初审驳回
    const STATUS_APPROVED = 'APPROVED'; //已分配；
    const STATUS_REJECTED = 'REJECTED'; //关闭；

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zyg
     */

    public function getlist($condition = [], $order = " id desc") {
        $sql = 'SELECT `erui_sys`.`employee`.`id` as employee_id,`erui_sys`.`employee`.`name` as employee_name,`erui_buyer`.`buyer`.`id`,`buyer_no`,`lang`,`buyer_type`,`erui_buyer`.`buyer`.`name`,`bn`,`profile`,`buyer`.`country_bn`,`erui_buyer`.`buyer`.`area_bn`,`buyer`.`province`,`buyer`.`city`,`official_email`,';
        $sql .= '`official_email`,`official_phone`,`official_fax`,`brand`,`official_website`,`logo`,`line_of_credit`,`credit_available`,`buyer_level`,`credit_level`,';
        $sql .= '`recommend_flag`,`erui_buyer`.`buyer`.`source`,`percent`,`erui_buyer`.`buyer`.`status`,`erui_buyer`.`buyer`.`remarks`,`apply_at`,`erui_buyer`.`buyer`.`created_by`,`erui_buyer`.`buyer`.`created_at`,`buyer`.`checked_by`,`buyer`.`checked_at`,';
        $sql .= '`erui_buyer`.`buyer`.address,`buyer_credit_log`.checked_by as credit_checked_by,`em`.`name` as credit_checked_name,`buyer_credit_log`.checked_at as credit_checked_at,`credit_apply_date`,`approved_at`,`buyer_credit_log`.in_status as credit_status,`buyer`.buyer_code ';
        $str = ' FROM ' . $this->g_table;
        $str .= " left Join `erui_buyer`.`buyer_agent` on `erui_buyer`.`buyer_agent`.`buyer_id` = `erui_buyer`.`buyer`.`id` ";
        $str .= " left Join `erui_sys`.`employee` on `erui_buyer`.`buyer_agent`.`agent_id` = `erui_sys`.`employee`.`id` AND `erui_sys`.`employee`.deleted_flag='N' ";
        $str .= " left Join `erui_buyer`.`buyer_account` on `erui_buyer`.`buyer_account`.`buyer_id` = `erui_buyer`.`buyer`.`id` AND `erui_buyer`.`buyer_account`.deleted_flag='N' ";
        $str .= " left Join `erui_buyer`.`buyer_credit_log` on `erui_buyer`.`buyer_credit_log`.`buyer_id` = `erui_buyer`.`buyer`.`id` ";
        $str .= " left Join `erui_sys`.`employee` as em on `erui_buyer`.`buyer_credit_log`.`checked_by` = `em`.`id` AND em.deleted_flag='N' ";
        $sql .= $str;
        $where = " WHERE buyer.deleted_flag = 'N'  ";
        if (!empty($condition['country_bn']) && !empty($condition['country_bns'])) {
            $where .= " And `buyer`.country_bn in (" . $condition['country_bn'] . ")";
            $where .= " And `buyer`.country_bn in (" . $condition['country_bns'] . ")";
        } elseif (!empty($condition['country_bn'])) {
            $where .= " And `buyer`.country_bn in (" . $condition['country_bn'] . ")";
        } elseif (!empty($condition['country_bns'])) {
            $where .= " And `buyer`.country_bn in (" . $condition['country_bns'] . ")";
        }

//        if (!empty($condition['area_bn'])) {
//            $where .= ' And `buyer`.area_bn ="' . $condition['area_bn'] . '"';
//        }
        if (!empty($condition['name'])) {   //客户或公司名称
            $where .= " And `erui_buyer`.`buyer`.name like '%" . $condition['name'] . "%'";
        }
        if (!empty($condition['employee_name'])) {
            $where .= " And `erui_sys`.`employee`.`name`  like '%" . $condition['employee_name'] . "%'";
        }
        if (!empty($condition['agent_id'])) {
            $where .= " And `erui_buyer`.`buyer_agent`.`agent_id`  in (" . $condition['agent_id'] . ")";
        }
        if (!empty($condition['official_phone'])) {
            $where .= ' And official_phone  = " ' . $condition['official_phone'] . '"';
        }
        if (!empty($condition['status'])) { //客户状态
            $where .= ' And `erui_buyer`.`buyer`.status  ="' . $condition['status'] . '"';
        }
//        if(!empty($condition['filter'])){   //过滤状态
//            $where .= ' And `erui_buyer`.`buyer`.status !=\'APPROVING\' and `erui_buyer`.`buyer`.status !=\'FIRST_REJECTED\' ';
//        }
        if(!empty($condition['create_information_buyer_name'])){   //客户档案创建时,选择客户
            $where .= ' And `erui_buyer`.`buyer`.is_build=0 and `erui_buyer`.`buyer`.deleted_flag=\'N\' and `erui_buyer`.`buyer`.status=\'APPROVED\' ';
        }

        if (!empty($condition['user_name'])) {
            $where .= ' And `erui_buyer`.`buyer_account`.`user_name`  ="' . $condition['user_name'] . '"';
        }
        if (!empty($condition['checked_at_start'])) {   //审核分配市场经办人时间
            $where .= ' And `erui_buyer`.`buyer`.checked_at  >="' . $condition['checked_at_start'] . '"';
        }
        if (!empty($condition['checked_at_end'])) { //
            $where .= ' And `erui_buyer`.`buyer`.checked_at  <="' . $condition['checked_at_end'] . '"';
        }
        if (!empty($condition['created_by'])) {
            $where .= ' And `erui_buyer`.`buyer`.created_by  ="' . $condition['created_by'] . '"';
        }
        if (!empty($condition['source'])) { //客户来源
            $where .= ' And `erui_buyer`.`buyer`.source='.$condition['source'];
        }
        if (!empty($condition['min_percent'])) { //信息完整度小
            $where .= ' And `erui_buyer`.`buyer`.percent  >=' . $condition['min_percent'];
        }
        if (!empty($condition['max_percent'])) { //信息完整度大
            $where .= ' And `erui_buyer`.`buyer`.percent  <=' . $condition['max_percent'];
        }
        if (!empty($condition['created_at_start'])) {
            $where .= ' And `erui_buyer`.`buyer`.created_at  >="' . $condition['created_at_start'] . '"';
        }
        if (!empty($condition['created_at_end'])) {
            $where .= ' And `erui_buyer`.`buyer`.created_at  <="' . $condition['created_at_end'] . '"';
        }
        if (!empty($condition['credit_checked_at_start'])) {
            $where .= ' And `erui_buyer`.`buyer_credit_log`.checked_at  >="' . $condition['credit_checked_at_start'] . '"';
        }
        if (!empty($condition['credit_checked_at_end'])) {
            $where .= ' And `erui_buyer`.`buyer_credit_log`.checked_at  <="' . $condition['credit_checked_at_end'] . '"';
        }
        if (!empty($condition['approved_at_start'])) {
            $where .= ' And `erui_buyer`.`buyer_credit_log`.approved_at  >="' . $condition['approved_at_start'] . '"';
        }
        if (!empty($condition['approved_at_end'])) {
            $where .= ' And `erui_buyer`.`buyer_credit_log`.approved_at  <="' . $condition['approved_at_end'] . '"';
        }
        if (!empty($condition['credit_checked_name'])) {
            $where .= " And `em`.`name`  like '%" . $condition['credit_checked_name'] . "%'";
        }
        if (!empty($condition['buyer_no'])) {
            $where .= ' And buyer_no  like "%' . $condition['buyer_no'] . '%"';
        }
        if (!empty($condition['buyer_code'])) {
            $where .= ' And buyer_code  like "%' . $condition['buyer_code'] . '%"';
        }
        if (!empty($condition['line_of_credit_max'])) {
            $where .= ' And `erui_buyer`.`buyer`.line_of_credit  <="' . $condition['line_of_credit_max'] . '"';
        }
        if (!empty($condition['line_of_credit_min'])) {
            $where .= ' And `erui_buyer`.`buyer`.line_of_credit  >="' . $condition['line_of_credit_min'] . '"';
        }
        if (!empty($condition['credit_status'])) {
            $where .= ' And `erui_buyer`.`buyer_credit_log`.in_status  ="' . $condition['credit_status'] . '"';
        }
        if ($where) {
            $sql .= $where;
            //  $sql_count .= $where;
        }
        $sql .= ' Group By `erui_buyer`.`buyer`.`id`';
        //$sql_count .= ' Group By `erui_buyer`.`buyer`.`id`';
        $sql .= ' Order By ' . $order;
        $res['count'] = count($this->query($sql));
//        if ($condition['num']) {
//            $sql .= ' LIMIT ' . $condition['page'] . ', 10';
//            $sql .= ' LIMIT ' . $condition['page'] . ',' . $condition['num'];
//        }
        //$count = $this->query($sql_count);
        $condition['page']=isset($condition['page'])?$condition['page']:0;
        $condition['num'] = empty($condition['num']) ? 10 : $condition['num'];
        $sql .= ' LIMIT ' . $condition['page'] . ',' . $condition['num'];
        $lang=isset($condition['lang'])?$condition['lang']:'zh';
        $info = $this->query($sql);
        foreach($info as $k => $v){
            if(!empty($v['buyer_level']) && is_numeric($v['buyer_level'])){ //客户等级
                $level = new BuyerLevelModel();
                $info[$k]['buyer_level'] = $level->getBuyerLevelById($v['buyer_level'],$lang);
            }
            if(!empty($v['percent'])){  //信息完整度
                $info[$k]['percent']=$v['percent'].'%';
            }else{
                $info[$k]['percent']='--';
            }
            if(!empty($v['country_bn'])){ //国家
                $country = new CountryModel();
                $countryInfo = $country->getCountryAndCodeByBn($v['country_bn'],$lang);
                $info[$k]['country_name'] = $countryInfo['name'];
                $info[$k]['country_code'] = $countryInfo['code'];
            }
            if(strpos($v['official_phone'],'-') !=false){
                $phoneArr=explode('-',$v['official_phone']);
            }
            if(strpos($v['official_phone'],' ') != false){
                $phoneArr=explode(' ',$v['official_phone']);
            }
            if(!empty($phoneArr)){
                $info[$k]['phone_start']=$phoneArr[0];
                $info[$k]['phone_end']=$phoneArr[1];
            }else{
                $info[$k]['phone_start']='';
                $info[$k]['phone_end']='';
            }
        }
//        $res['data'] = $this->query($sql);
        $res['data'] = $info;
        return $res;
    }
    //合并创建,和经办人-wang
    public function validAgent($createdArr,$list){
        $flag=[];
        if(empty($createdArr) && empty($list)){
            $flag=null;
        }elseif(!empty($createdArr) && empty($list)){
            $flag=$createdArr;
        }elseif(empty($createdArr) && !empty($list)){
            $flag=$list;
        }elseif(!empty($createdArr) && !empty($list)){
            $flag=array_merge($createdArr,$list);
        }
        return $flag;
    }
    /**
     * @param $data
     * 客户管理-客户统计-获取所有客户的搜索列表条件
     * wangs
     */
    public function getBuyerStatisListCond1($data,$falg=true){
        $cond = ' 1=1 and buyer.deleted_flag=\'N\'';
        if(empty($data['admin']['role'])){
            return false;
        }
        if(!in_array('CRM客户管理',$data['admin']['role'])){    //权限
            if(!in_array('201711242',$data['admin']['role']) && !in_array('A001',$data['admin']['role'])){  //不是国家负责人也不是经办人
                return false;
            }elseif(in_array('201711242',$data['admin']['role'])  && !in_array('A001',$data['admin']['role'])){   //国家负责人,不是经办人
                $cond .= ' And  `buyer`.country_bn in ('.$data['admin']['country'].')';
            }elseif(!in_array('201711242',$data['admin']['role'])  && in_array('A001',$data['admin']['role'])){   //不是国家负责人,是经办人
                $agent=new BuyerAgentModel();
                $list=$agent->field('buyer_id')->where(array('agent_id'=>$data['created_by'],'deleted_flag'=>'N'))->select();
                $created=new BuyerModel();
                $createdArr=$created->field('id as buyer_id')->where(array('created_by'=>$data['created_by'],'deleted_flag'=>'N'))->select();
                $totalList=$this->validAgent($createdArr,$list);
                $str='';
                foreach($totalList as $k => $v){
                    $str.=','.$v['buyer_id'];
                }
                $str=substr($str,1);
                if(!empty($str)){
                    $cond.= " and buyer.id in ($str) ";
                }else{
                    $cond.= " and buyer.id in ('wangs') ";
                }
            }else{  //即使国家负责人,也是市场经办人
                $cond .= ' And ( `buyer`.country_bn in ('.$data['admin']['country'].')';
                $agent=new BuyerAgentModel();
                $list=$agent->field('buyer_id')->where(array('agent_id'=>$data['created_by'],'deleted_flag'=>'N'))->select();
                $created=new BuyerModel();
                $createdArr=$created->field('id as buyer_id')->where(array('created_by'=>$data['created_by'],'deleted_flag'=>'N'))->select();
                $totalList=$this->validAgent($createdArr,$list);
//                $totalList=array_merge($createdArr,$list);
                $str='';
                foreach($totalList as $k => $v){
                    $str.=','.$v['buyer_id'];
                }
                $str=substr($str,1);
                if(!empty($str)){
                    $cond.= " or buyer.id in ($str) )";
                }else{
                    $cond.= " or buyer.id in ('wangs') )";
                }
            }
        }else{
            $cond = ' 1=1 and buyer.deleted_flag=\'N\'';
        }
        foreach($data as $k => $v){
            $data[$k]=trim($v,' ');
        }
//        if(!empty($data['customer_management']) && $data['customer_management']==true){  //点击客户管理菜单-后台新增客户
//            $cond .= " and buyer.source=1 ";
//        }
//        if(!empty($data['registered_customer']) && $data['registered_customer']==true){  //点击注册客户菜单-门户APP新增客户
//            $cond .= " and (buyer.source=2 or buyer.source=3) ";
//        }
//        if(!empty($data['country_bn'])){    //国家搜索---档案信息管理
//            $cond .= " and buyer.country_bn='$data[country_bn]'";
//        }
        if(!empty($data['country_search'])){    //国家搜索
            $cond .= " And `buyer`.country_bn='".$data['country_search']."'";
        }

        if(!empty($data['buyer_no'])){  //客户编号
            $data['buyer_no']=trim($data['buyer_no']," ");
            $cond .= " and buyer.buyer_no like '%".$data['buyer_no']."%'";
        }
        if(!empty($data['buyer_code'])){    //客户CRM代码
            $data['buyer_code']=trim($data['buyer_code']," ");
            $cond .= " and buyer.buyer_code like '%".$data['buyer_code']."%'";
        }
        if(!empty($data['name'])){    //客户名称
            $data['name']=trim($data['name']," ");
            $cond .= " and buyer.name like '%".$data['name']."%'";
        }
        if($falg===true){
            if(!empty($data['status'])){    //审核状态
                if($data['status']=='PASS'){
                    $cond .= " and buyer.is_build=1 and buyer.status='APPROVED'";
                }else{
                    $cond .= " and buyer.status='".$data['status']."'";
                }
            }
        }
        if(!empty($data['create_information_buyer_name'])){   //客户档案创建时,选择客户
            $cond .= " and buyer.is_build=0 and buyer.status='APPROVED' and buyer.deleted_flag='N'";
        }
        if (!empty($data['source'])) {
            $cond .= ' And `erui_buyer`.`buyer`.source='.$data['source'];
        }
//        if(!empty($data['buyer_level'])){  //客户等级===buy
//            $cond .= " and buyer.buyer_level='".$data['buyer_level']."'";
//        }
        if(!empty($data['buyer_level'])){
            if($data['buyer_level']=='52'){
                $cond .= " and buyer.buyer_level=52";
            }elseif($data['buyer_level']=='53'){
                $cond .= " and buyer.buyer_level=53";
            }else{
                $cond .= " and buyer.buyer_level is null";
            }
//            $cond .= " and buyer.buyer_level='$data[buyer_level]'";
        }
        if(!empty($data['employee_name'])){  //经办人名称
            $data['employee_name']=trim($data['employee_name']," ");
            $cond .= " and employee.name like '%".$data['employee_name']."%'";
        }
        if(!empty($data['created_name'])){  //创建人名称
            $data['created_name']=trim($data['created_name']," ");
            $cond .= " AND buyer.created_by=(select employee.id from erui_sys.employee employee where employee.deleted_flag='N' AND employee.name like '%".$data['created_name']."%')";
        }
        if (!empty($data['min_percent'])) { //信息完整度小
            $cond .= ' And `erui_buyer`.`buyer`.percent  >=' . $data['min_percent'];
        }
        if (!empty($data['max_percent'])) { //信息完整度大
            $cond .= ' And `erui_buyer`.`buyer`.percent  <=' . $data['max_percent'];
        }
        if(!empty($data['checked_at_start'])){  //审核时间===buy
            $cond .= " and agent.created_at >= '".$data['checked_at_start']."'";
        }
        if(!empty($data['checked_at_end'])){  //审核时间===buy
            $cond .= " and agent.created_at <= '".$data['checked_at_end']."'";
        }

        if(!empty($data['created_at_start'])){  //注册时间===buy
            $cond .= " and buyer.created_at >= '".$data['created_at_start']."'";
        }
        if(!empty($data['created_at_end'])){  //审核时间===buy
            $cond .= " and buyer.created_at <= '".$data['created_at_end']."'";
        }

        if(!empty($data['level_at_start'])){  //注册时间===buy
            $cond .= " and buyer.level_at >= '".$data['level_at_start']."'";
        }
        if(!empty($data['level_at_end'])){  //审核时间===buy
            $cond .= " and buyer.level_at <= '".$data['level_at_end']."'";
        }
        return $cond;
    }
    public function arrToStr($data,$column=''){
        $str='';
        foreach($data as $k => $v){
            if(empty($column)){
                $str.=",'".$v."'";
            }else{
                $str.=",'".$v[$column]."'";
            }
        }
        $str=mb_substr($str,1);
        return $str;
    }
    public function arrToArray($data,$column){
        $arr=[];
        foreach($data as $k => $v){
            $arr[]=$v[$column];
        }
        return $arr;
    }
    //地区-国家-权限
    public function accessCountry($data){
        $cond = ' 1=1 and buyer.deleted_flag=\'N\'';    //条件
        if(empty($data['admin']['role']) || empty($data['admin']['country'])){
            return false;
        }
        if(!is_array($data['admin']['country'])){
            $data['admin']['country']=trim($data['admin']['country'],"'");
            $data['admin']['country']=explode("','",$data['admin']['country']);
        }
        //国家
        $countryStr=$this->arrToStr($data['admin']['country']);
        //地区
        $area=new CountryModel();
        $areaArr=$area->table('erui_operation.market_area_country')
            ->field('market_area_bn as area_bn')
            ->where("country_bn in ($countryStr)")
            ->group('market_area_bn')
            ->select();
        $areaStr=$this->arrToStr($areaArr,'area_bn');   //地区
        //地区下属国家
        $countryArr=$area->table('erui_operation.market_area_country')
            ->field('country_bn')
            ->where("market_area_bn in ($areaStr)")
            ->group('country_bn')
            ->select();
        $areaCountryStr=$this->arrToStr($countryArr,'country_bn');  //地区下属国家

        if(in_array('CRM客户管理',$data['admin']['role'])){ //营运
            $cond .= '';
        }elseif(in_array('area-customers',$data['admin']['role'])){ //地区
            $cond .= " And  `buyer`.country_bn in ($areaCountryStr)";
        }elseif(in_array('201711242',$data['admin']['role'])){ //国家
            $cond .= " And  `buyer`.country_bn in ($countryStr)";
        }elseif(in_array('customer_agent',$data['admin']['role'])){ //经办人
            $agent=new BuyerAgentModel();
            $agentArr=$agent->field('buyer_id')->where(array('agent_id'=>$data['created_by'],'deleted_flag'=>'N'))->select();   //经办人管理的客户
            $agentArray=$this->arrToArray($agentArr,'buyer_id');
            $created=new BuyerModel();
            $createdArr=$created->field('id as buyer_id')->where(array('created_by'=>$data['created_by'],'deleted_flag'=>'N'))->select();   //经办人创建的客户
            $createdArray=$this->arrToArray($createdArr,'buyer_id');
            $idArr=array_merge($agentArray,$createdArray);
            $buyerArr=array_unique($idArr);
            $idStr=$this->arrToStr($buyerArr);  //经办人角色管理的客户
            if(!empty($idStr)){
                $cond.= " and buyer.id in ($idStr) ";
            }else{
                $cond.= " and buyer.id in ('error') ";
            }
        }else{
            $cond .= " And  `buyer`.country_bn in ($countryStr)";
//            return false;
        }
        return $cond;
    }
    //cond
    public function getBuyerStatisListCond($data,$falg=true,$filter=false){
//        $data=array(
//            'created_by'=>37850,
//            'admin'=>array(
//                'role'=>array(
//                    'CRM客户管理1','area-customers1','201711242','A001','A012','A013','查看客户管理所有菜单','A015'
//                ),
//                'country'=>array(
//                    'Russia','Malaysia','Myanmar','Japan','India'
//                )
//            ),
//            'lang'=>'zh',
//            'area_country'=>['South America']
//        );
        $access=$this->accessCountry($data);
        if($access==false){
            return false;
        }
        $cond=$access;  //条件
        $area=new CountryModel();   //地区-国家-搜索 : "area_country":["Asia-Pacific","China"]
        if(empty($data['area_country'][0]) && empty($data['area_country'][1])){    //全部

        }elseif(!empty($data['area_country'][0]) && empty($data['area_country'][1])){   //地区
            $area_bn=$data['area_country'][0];  //地区
            $areaArr=$area->table('erui_operation.market_area_country')
                ->field('country_bn')
                ->where("market_area_bn='$area_bn'")
                ->group('country_bn')
                ->select();
            if(in_array('area-customers',$data['admin']['role'])){  //地区负责人
                $countryStr=$this->arrToStr($areaArr,'country_bn');
            }else{  //国家权限
                $countryArray=$this->arrToArray($areaArr,'country_bn');
                $countryArr=array_intersect($countryArray,$data['admin']['country']);
                if(!empty($countryArr)){
                    $countryStr=$this->arrToStr($countryArr);
                }else{
                    $countryStr="'error'";
                }
            }
            $cond.=" and buyer.country_bn in ($countryStr)";
        }else{  //国家
            $country_bn=$data['area_country'][1];
            $cond.=" and buyer.country_bn='$country_bn'";
        }


        foreach($data as $k => $v){
            $data[$k]=trim($v);
        }
        if($falg==true){
            if(!empty($data['status'])){    //分配状态
                $cond .= " And `buyer`.status='".$data['status']."'";
            }
//            if($filter==true){
//                $cond .= " And `buyer`.status='APPROVED'";
//            }
        }

        if(!empty($data['country_search'])){    //国家搜索
            $cond .= " And `buyer`.country_bn='".$data['country_search']."'";
        }
        if(!empty($data['source'])){    //来源
            if($data['source']==='1'){
                $cond .= " And `buyer`.source=1";
            }elseif($data['source']==='2'){
                $cond .= " And `buyer`.source=2";
            }elseif($data['source']==='3'){
                $cond .= " And `buyer`.source=3";
            }else{
                $cond .= " And `buyer`.source=4";
            }
        }
        if(!empty($data['buyer_level'])){    //级别
            if($data['buyer_level']=='52'){
                $cond .= " And `buyer`.buyer_level=52";
            }elseif($data['buyer_level']=='53'){
                $cond .= " And `buyer`.buyer_level=53";
            }else{
                $cond .= " And `buyer`.buyer_level=4";
            }
        }
        if(!empty($data['buyer_no'])){  //客户编号
            $cond .= " and buyer.buyer_no like '%".$data['buyer_no']."%'";
        }
        if(!empty($data['buyer_code'])){    //客户CRM代码
            $cond .= " and buyer.buyer_code like '%".$data['buyer_code']."%'";
        }
        if(!empty($data['buyer_no_code'])){    //客户编号-代码            2合1-------------------------------
            $cond .= " and ( buyer.buyer_no like '%".$data['buyer_no_code']."%'";
            $cond .= " or buyer.buyer_code like '%".$data['buyer_no_code']."%' )";
        }

        if(!empty($data['name'])){    //客户名称
            $cond .= " and buyer.name like '%".$data['name']."%'";
        }
        if(!empty($data['percent_min'])){    //信息完整度
            $cond .= " and buyer.percent >= ".$data['percent_min'];
        }
        if(!empty($data['percent_max'])){    //信息完整度
            $cond .= " and buyer.percent <= ".$data['percent_max'];
        }
        //时间
        if(!empty($data['created_start_at'])){  //创建时间
            $cond .= " and buyer.created_at >= '".$data['created_start_at']."'";
        }
        if(!empty($data['created_end_at'])){
            $cond .= " and buyer.created_at <= '".$data['created_end_at']."'";
        }
        if(!empty($data['level_start_at'])){  //等级时间
            $data['level_start_at']=substr($data['level_start_at'],0,10);
            $cond .= " and buyer.level_at >= '".$data['level_start_at']."'";
        }
        if(!empty($data['level_end_at'])){
            $data['level_end_at']=substr($data['level_end_at'],0,10);
            $cond .= " and buyer.level_at <= '".$data['level_end_at']."'";
        }
        if(!empty($data['checked_start_at']) || !empty($data['checked_end_at'])){  //分配时间
            $map=' 1=1 ';
            if(!empty($data['checked_start_at'])){
                $map .= " and created_at >= '".$data['checked_start_at']."'";
            }
            if(!empty($data['checked_end_at'])){
                $map .= " and created_at <= '".$data['checked_end_at']."'";
            }
            $info=$this->table('erui_buyer.buyer_agent')
                ->field('buyer_id')
                ->where($map)
                ->group('buyer_id')
                ->select();
            if(!empty($info)){
                $arr=[];
                foreach($info as $k => $v){
                    $arr[]=$v['buyer_id'];
                }
                $str=implode(',',$arr);
                $cond.=" and buyer.id in ($str)";
            }else{
                $cond.=" and buyer.id in ('crm')";
            }
        }
        if(!empty($data['created_name'])){  //客户.创建人名称
            $em=$this->table('erui_sys.employee')->field('id')
                ->where(" deleted_flag='N' and name like '%$data[created_name]%'")
                ->select();
            $emStr='';
            if(!empty($em)){
                foreach($em as $k => $v){
                    $emStr.=','.$v['id'];
                }
                $emStr=mb_substr($emStr,1);
                $cond.=" and buyer.created_by in ($emStr)";
            }else{
                $cond.=" and buyer.created_by in ('crm')";
            }

        }
        if(!empty($data['employee_name']) || !empty($data['agent_name'])){  //经办人
            if(!empty($data['employee_name'])){
                $map="agent.deleted_flag='N' and employee.deleted_flag='N' AND employee.name like '%".$data['employee_name']."%'";
            }elseif(!empty($data['agent_name'])){
                $map="agent.deleted_flag='N' and employee.deleted_flag='N' AND employee.name like '%".$data['agent_name']."%'";
            }
            $info=$this->table('erui_buyer.buyer_agent')->alias('agent')
                ->join("erui_sys.employee employee on agent.agent_id=employee.id",'left')
                ->field('agent.buyer_id')
                ->where($map)
                ->group('agent.buyer_id')
                ->select();
            if(!empty($info)){
                $arr=[];
                foreach($info as $k => $v){
                    $arr[]=$v['buyer_id'];
                }
                $str=implode(',',$arr);
                $cond.=" and buyer.id in ($str)";
            }else{
                $cond.=" and buyer.id in ('crm')";
            }
        }
        return $cond;
    }
    //crm客户统计获取客户总数-wangs
    public function crmGetBuyerTotal($cond){
        $sql="SELECT count(DISTINCT buyer.id) as total_count";
        $sql.=" FROM erui_buyer.buyer buyer";   //buyer
        $sql.=" left Join `erui_buyer`.`buyer_agent` agent";  //buyer_agent
        $sql.=" on agent.buyer_id = buyer.id and agent.deleted_flag='N' ";
//        $sql.=" left Join `erui_dict`.`country` country";   //country
//        $sql.=" on buyer.`country_bn` = country.`bn` and country.lang='zh' and country.deleted_flag='N'";
//        $sql.=" left Join `erui_sys`.`employee` employee";   //经办人
//        $sql.=" on agent.`agent_id` = employee.`id` and employee.deleted_flag='N'";
        $sql.= 'where '.$cond;
        $count=$this->query($sql);
        $totalCount=$count[0]['total_count'];   //总条数
        return $totalCount;
    }
    //crm客户统计获取会员等级数量-wang
    public function crmGetBuyerLevelCount($cond){
        $sqlLevel = "select gbuyer.buyer_level,count(*) as level_count from (";
        $sqlLevel .= ' SELECT buyer.id,agent.agent_id,buyer_level';
        $sqlLevel .= ' FROM erui_buyer.buyer buyer';
        $sqlLevel.=" left Join `erui_buyer`.`buyer_agent` agent";  //buyer_agent
        $sqlLevel.=" on agent.buyer_id = buyer.id and agent.deleted_flag='N' ";
        $sqlLevel.=" left Join `erui_dict`.`country` country";   //country
        $sqlLevel.=" on buyer.`country_bn` = country.`bn` and country.lang='zh' and country.deleted_flag='N'";
        $sqlLevel.=" left Join `erui_sys`.`employee` employee";   //经办人
        $sqlLevel.=" on agent.`agent_id` = employee.`id` and employee.deleted_flag='N'";
        $sqlLevel.= 'where '.$cond;
        $sqlLevel.= ' GROUP BY buyer.id,buyer.buyer_level) gbuyer';
        $sqlLevel.= ' group by gbuyer.buyer_level';
        $level=$this->query($sqlLevel);
        $levelCount=array(
            'kong'=>0,
            'ordinary'=>0,
            'senior'=>0
        );
        foreach($level as $k => $v){
            if($v['buyer_level']==52){
                $levelCount['ordinary']=isset($v['level_count'])?$v['level_count']:0;
            }elseif($v['buyer_level']==53){
                $levelCount['senior']=isset($v['level_count'])?$v['level_count']:0;
            }else{
                $levelCount['kong']=isset($v['level_count'])?$v['level_count']:0;
            }
        }
        return $levelCount;
    }
    public function crmGetBuyerStatusCount($cond){
        $sql = "select gbuyer.status,count(*) as status_count from (";

        $sql .= ' SELECT buyer.id,buyer.status';
        $sql .= ' FROM erui_buyer.buyer buyer';
        $sql.=" left Join `erui_buyer`.`buyer_agent` agent";  //buyer_agent
        $sql.=" on agent.buyer_id = buyer.id and agent.deleted_flag='N' ";
//        $sql.=" left Join `erui_dict`.`country` country";   //country
//        $sql.=" on buyer.`country_bn` = country.`bn` and country.lang='zh' and country.deleted_flag='N'";
//        $sql.=" left Join `erui_sys`.`employee` employee";   //经办人
//        $sql.=" on agent.`agent_id` = employee.`id` and employee.deleted_flag='N'";
        $sql.= ' where '.$cond;
        $sql.= ' GROUP BY buyer.id,buyer.status) gbuyer';

        $sql.= ' group by gbuyer.status';
        $count=$this->query($sql);
        $arr=[];
        foreach($count as $k => $v){
            $arr[$v['status']]=$v['status_count'];
        }
        return $arr;
    }
    public function getAreaByCountrybn($country_bn,$lang='zh'){
        $sql='select ';
        $sql.=' name ';
        $sql.=' from erui_operation.market_area_country country';
        $sql.=' join erui_operation.market_area area ';
        $sql.=' on  country.market_area_bn=area.bn';
        $sql.=' where country.country_bn=\''.$country_bn.'\'';
        $sql.=' and area.lang=\''.$lang.'\'';
        $info=$this->query($sql);
        return $info[0]['name'];
    }
    public function buyerStatisList($data,$excel=false,$status=false){
        set_time_limit(0);
        $lang=!empty($data['lang'])?$data['lang']:'zh';
        $cond = $this->getBuyerStatisListCond($data);
//        print_r($cond);die;
        if($cond==false){   //无角色,无数据
            return false;
        }
        $currentPage = !empty($data['currentPage'])?$data['currentPage']:1;
        $pageSize = 10;
        $totalCount=$this->crmGetBuyerTotal($cond); //获取总条数
//        $totalPage = ceil($totalCount/$pageSize);
//        if(!empty($data['currentPage']) && $data['currentPage'] >0){
//            $currentPage = ceil($data['currentPage']);
//        }
        $offset = ($currentPage-1)*$pageSize;
        $field='';
        $fieldArr = array(
            'id',
            'percent',  //信息完整度
            'buyer_no',     //客户编号
            'buyer_code',   //客户CRM代码buy
            'name',   //客户名称buy
            'status',   //审核状态
            'source',   //审核状态
            'buyer_level',  //客户等级
            'level_at',  //客户等级
            'country_bn',    //国家
            'created_at',   //注册时间/创建时间
            'created_by',   //注册时间/创建时间
        );
        foreach($fieldArr as $v){
            $field .= ',buyer.'.$v;
        }
        $field=substr($field,1);
        //excel导出标识
        if($excel==true){
            $offset=0;
            $pageSize=10000;
        }
        $info = $this->alias('buyer')
            ->field($field)
            ->where($cond)
            ->group('buyer.id')
            ->order('buyer.checked_at desc,buyer.id desc')
            ->limit($offset,$pageSize)
            ->select();
        $level = new BuyerLevelModel();
        $country = new CountryModel();
        $order = new OrderModel();
        $agent = new BuyerAgentModel();
        foreach($info as $k => &$v){
            if(!empty($v['percent'])){  //信息完整度
                $v['percent']=$v['percent'].'%';
            }
            $account=$this->table('erui_buyer.buyer_account')->field('email')
                ->where(array('buyer_id'=>$v['id'],'deleted_flag'=>'N'))->find();
            $info[$k]['account_email'] = $account['email'];
            if(!empty($v['buyer_level'])){ //客户等级
                $info[$k]['buyer_level'] = $level->getBuyerLevelById($v['buyer_level'],$lang);
            }
//            else{
//                $info[$k]['buyer_level']=$lang=='zh'?'注册客户':'Registered customer';
//            }
            if(!empty($v['country_bn'])){ //国家
                $area = $country->getCountryAreaByBn($v['country_bn'],$lang);
                $info[$k]['area'] = $area['area'];
                $info[$k]['country_name'] = $area['country'];
            }

            if(!empty($data['employee_name'])){
                $agentInfo=$agent->getBuyerAgentArr($v['id'],$data['employee_name']);
            }else{
                $agentInfo=$agent->getBuyerAgentArr($v['id']);
            }
            $info[$k]['agent_id'] = $agentInfo['id'];
            $info[$k]['employee_name'] = $agentInfo['name'];
            $info[$k]['checked_at'] = $agentInfo['checked_at'];

            if($v['source']==1 && empty($info[$k]['employee_name'])){
                $name=$this->table('erui_sys.employee')->field('name')
                    ->where(array('id'=>$v['created_by'],'deleted_flag'=>'N'))->find();
//                $info[$k]['created_name']=$name['name'];
                $info[$k]['agent_id'] = $v['created_by'];
                $info[$k]['employee_name']=$name['name'];
            }
            $orderInfo=$order->statisOrder($v['id']);
            $info[$k]['mem_cate'] = $orderInfo['mem_cate'];
            if(!empty($v['created_by'])){
                $employee=$this->table('erui_sys.employee')->field('name')->where(array('id'=>$v['created_by'],'deleted_flag'=>'N'))->find();
                $v['created_name']=$employee['name'];
            }else{
                $v['created_name']='';
            }
            $info[$k]['created_at'] = substr($info[$k]['created_at'],0,10);
            $info[$k]['checked_at'] = substr($info[$k]['checked_at'],0,10);
            if($v['source']==1){
                $v['source']='BOSS';
            }elseif($v['source']==2){
                $v['source']='PORTAL';
            }elseif($v['source']==3){
                $v['source']='APP';
            }else{
                $v['source']='';
            }
//            if($status==true){  ///APPROVING,已分配经办人:APPROVED,驳回关闭:REJECTED,建立档案信息PASS',
//                if($v['status']=='APPROVING'){
//                    $v['status']=$lang=='zh'?'待分配':'APPROVING';
//                }elseif($v['status']=='APPROVED'){
//                    $v['status']=$lang=='zh'?'已分配':'APPROVED';
//                }elseif($v['status']=='REJECTED'){
//                    $v['status']=$lang=='zh'?'已关闭':'REJECTED';
//                }else{
//                    $v['status']='';
//                }
//            }
        }
        if(empty($info)){
            $info=[];
        }
        if($excel==false){
            $arr['currentPage'] = $currentPage;
//            $arr['totalPage'] = $totalPage;
            $arr['totalCount'] = $totalCount;
            $arr['info'] = $info;
            return $arr;
        }
        //整合excel导出数据
        $package=$this->packageBuyerListExcelData($info,$lang);
        $excelFile=$this->crmExportBuyerListExcel($package,$lang);
        //
        $arr['tmp_name'] = $excelFile;
        $arr['type'] = 'application/excel';
        $arr['name'] = pathinfo($excelFile, PATHINFO_BASENAME);
        //把导出的文件上传到文件服务器指定目录位置
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server . '/V2/Uploadfile/upload';
        $fileId = postfile($arr, $url);    //上传到fastDFSUrl访问地址,返回name和url
        //删除文件和目录
        if(file_exists($excelFile)){
            unlink($excelFile); //删除文件
//            ZipHelper::removeDir(dirname($excelName));    //清除目录
        }
        if ($fileId) {
            return array('url' => $fastDFSServer .'/'. $fileId['url'] . '?filename=' . $fileId['name'], 'name' => $fileId['name']);
        }
    }
    /**
     * @param $data
     * 客户管理-客户统计-所有客户的搜索列表
     * wangs
     */
    public function buyerStatisList1($data,$excel=false){
        set_time_limit(0);
        $lang=!empty($data['lang'])?$data['lang']:'zh';
        $cond = $this->getBuyerStatisListCond($data);
        if($cond==false){   //无角色,无数据
            return false;
        }
        $currentPage = 1;
        $pageSize = 10;
        $totalCount=$this->crmGetBuyerTotal($cond); //获取总条数
        $totalPage = ceil($totalCount/$pageSize);
        if(!empty($data['currentPage']) && $data['currentPage'] >0){
            $currentPage = ceil($data['currentPage']);
        }
        $offset = ($currentPage-1)*$pageSize;
        $fieldArr = array(
            'id',
            'is_build',
            'buyer_no',     //客户编号
            'buyer_code',   //客户CRM代码buy
            'percent',   //信息完整度
            'name',   //客户名称buy
            'status',   //审核状态
            'source',   //客户来源
            'buyer_level',  //客户等级
            'level_at',  //客户等级
            'country_bn',    //国家
            'created_at',   //注册时间/创建时间
//            'checked_at',   //操作
        );
        $field = 'country.name as country_name,';

        $field .= '(select employee.name from erui_sys.employee employee where employee.id=buyer.created_by) as created_name';
        foreach($fieldArr as $v){
            $field .= ',buyer.'.$v;
        }
        $field .= ' ,agent.created_at as checked_at';
//        $field .= ' ,agent.agent_id';
        $field .= ' ,account.sent_email';
        $field .= ' ,account.email as account_email';
        //excel导出标识
        if($excel==true){
            $offset=0;
            $pageSize=10000;
        }
        $info = $this->alias('buyer')
            ->join("erui_buyer.buyer_account account on buyer.id=account.buyer_id and account.deleted_flag='N'",'left')
            ->join("erui_buyer.buyer_agent agent on buyer.id=agent.buyer_id and agent.deleted_flag='N'",'left')
            ->join("erui_sys.employee employee on agent.agent_id=employee.id and employee.deleted_flag='N'",'left')
            ->join("erui_dict.country country on buyer.country_bn=country.bn and country.deleted_flag='N' and country.lang='$lang'",'left')
            ->field($field)
            ->where($cond)
            ->group('buyer.id')
            ->order('buyer.checked_at desc')
            ->limit($offset,$pageSize)
            ->select();
        echo $this->getLastSql();die;
        $level = new BuyerLevelModel();
        $country = new CountryModel();
        $order = new OrderModel();
        $agent = new BuyerAgentModel();
        foreach($info as $k => $v){
            if(!empty($v['buyer_level'])){ //客户等级
                $info[$k]['buyer_level'] = $level->getBuyerLevelById($v['buyer_level'],$lang);
            }else{
                $info[$k]['buyer_level']=$lang=='zh'?'注册客户':'Registered customer';
            }
            if(!empty($v['percent'])){  //信息完整度
                $info[$k]['percent']=$v['percent'].'%';
            }else{
                $info[$k]['percent']='--';
            }
//            if($v['is_build']==1 && $v['status']=='APPROVED'){ //国家
//                $info[$k]['status'] = 'PASS';
//            }
            unset($info[$k]['is_build']);
            if(!empty($v['country_bn'])){ //国家
                $area = $country->getCountryAreaByBn($v['country_bn'],$lang);
                $info[$k]['area'] = $area['area'];
                $info[$k]['country_name'] = $area['country'];
            }
            $agentInfo=$agent->getBuyerAgentArr($v['id']);
            $info[$k]['agent_id'] = $agentInfo['id'];
            $info[$k]['employee_name'] = $agentInfo['name'];
            $orderInfo=$order->statisOrder($v['id']);
            $info[$k]['mem_cate'] = $orderInfo['mem_cate'];

            $info[$k]['created_at'] = substr($info[$k]['created_at'],0,10);
            $info[$k]['checked_at'] = substr($info[$k]['checked_at'],0,10);
        }
        if($excel==false){
            $arr['currentPage'] = $currentPage;
            $arr['totalPage'] = $totalPage;
            $arr['totalCount'] = $totalCount;
            $arr['info'] = $info;
            return $arr;
        }
        //整合excel导出数据
        $package=$this->packageBuyerListExcelData($info,$lang);
        $excelFile=$this->crmExportBuyerListExcel($package,$lang);
        //
        $arr['tmp_name'] = $excelFile;
        $arr['type'] = 'application/excel';
        $arr['name'] = pathinfo($excelFile, PATHINFO_BASENAME);
        //把导出的文件上传到文件服务器指定目录位置
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server . '/V2/Uploadfile/upload';
        $fileId = postfile($arr, $url);    //上传到fastDFSUrl访问地址,返回name和url
        //删除文件和目录
        if(file_exists($excelFile)){
            unlink($excelFile); //删除文件
//            ZipHelper::removeDir(dirname($excelName));    //清除目录
        }
        if ($fileId) {
            return array('url' => $fastDFSServer . $fileId['url'] . '?filename=' . $fileId['name'], 'name' => $fileId['name']);
        }
    }
    //crm客户统计列表,Excel导出数据整合-王帅
    public function packageBuyerListExcelData($package,$lang){
        $arr=array();
        if($lang=='zh'){
            foreach($package as $k => $v){
                $arr[$k]['percent']=$v['percent'];  //信息完整度
                $arr[$k]['buyer_no']=$v['buyer_no'];  //客户编号
                $arr[$k]['name']=$v['name'];  //客户名称
                $arr[$k]['account_email']=$v['account_email'];  //客户邮箱
                $arr[$k]['buyer_code']=$v['buyer_code'];  //客户代码
                $arr[$k]['area']=$v['area'];  //国家
                $arr[$k]['country_name']=$v['country_name'];  //国家
                $arr[$k]['created_at']=$v['created_at'];  //客户注册时间
//            $arr[$k]['status']=$v['status'];  //客户状态
                if($v['status']=='APPROVING'){
                    $arr[$k]['status']='待分配';
                }elseif($v['status']=='APPROVED'){
                    $arr[$k]['status']='已分配';
                }elseif($v['status']=='PASS'){
                    $arr[$k]['status']='已通过';
                }elseif($v['status']=='REJECTED'){
                    $arr[$k]['status']='已关闭';
                }
                $arr[$k]['buyer_level']=$v['buyer_level'];  //客户等级
                if($v['buyer_level']==null){
                    $arr[$k]['buyer_level']='注册会员';
                }
            $arr[$k]['mem_cate']=$v['mem_cate'];  //客户来源
                if($v['source']==1){
                    $arr[$k]['source']='后台注册';
                }elseif($v['source']==2){
                    $arr[$k]['source']='门户注册';
                }elseif($v['source']==3){
                    $arr[$k]['source']='手机端注册';
                }
                $arr[$k]['level_at']=$v['level_at'];  //定级日期
            }
        }else{
            foreach($package as $k => $v){
                $arr[$k]['percent']=$v['percent'];  //信息完整度
                $arr[$k]['buyer_no']=$v['buyer_no'];  //客户编号
                $arr[$k]['name']=$v['name'];  //客户名称
                $arr[$k]['account_email']=$v['account_email'];  //客户邮箱
                $arr[$k]['buyer_code']=$v['buyer_code'];  //客户代码
                $arr[$k]['area']=$v['area'];  //国家
                $arr[$k]['country_name']=$v['country_name'];  //国家
                $arr[$k]['created_at']=$v['created_at'];  //客户注册时间
//            $arr[$k]['status']=$v['status'];  //客户状态
                if($v['status']=='APPROVING'){
                    $arr[$k]['status']='To be allocated';
                }elseif($v['status']=='APPROVED'){
                    $arr[$k]['status']='Allocated';
                }elseif($v['status']=='PASS'){
                    $arr[$k]['status']='Passed';
                }elseif($v['status']=='REJECTED'){
                    $arr[$k]['status']='Closed';
                }
                $arr[$k]['buyer_level']=$v['buyer_level'];  //客户等级
                if($v['buyer_level']==null){
                    $arr[$k]['buyer_level']='Registered member';
                }
            $arr[$k]['mem_cate']=$v['mem_cate'];  //客户来源
                if($v['source']==1){
                    $arr[$k]['source']='Registered on BOSS system';
                }elseif($v['source']==2){
                    $arr[$k]['source']='Registered on www.erui.com';
                }elseif($v['source']==3){
                    $arr[$k]['source']='Registered on APP';
                }
                $arr[$k]['level_at']=$v['level_at'];  //定级日期
            }
        }
        return $arr;
    }
    //crm客户统计Excel导出
    public function crmExportBuyerListExcel($data,$lang='zh'){
        set_time_limit(0);  # 设置执行时间最大值
        //存放excel文件目录
        $excelDir = MYPATH . DS . 'public' . DS . 'tmp' . DS . 'buyerlist';
        if (!is_dir($excelDir)) {
            mkdir($excelDir, 0777, true);
        }
        if($lang=='zh'){
            $sheetName='customer';
            $tableheader = array('客户信息完整度','客户编号','客户名称','客户邮箱','CRM客户代码','地区', '国家', '创建时间', '客户状态', '会员级别', '客户分类','用户来源','定级日期');
        }else{
            $sheetName='Customer list';
            $tableheader = array('Integrity','Customer NO','Company name','Customer email', 'Customer code','area', 'Country', 'Creation_time', 'Customer status', 'Customer level','Customer cate','Registration source of customer','Verification date');
        }
        //创建对象
        $excel = new PHPExcel();
        $objActSheet = $excel->getActiveSheet();
        $letter = range(A, Z);
        //设置当前的sheet
        $excel->setActiveSheetIndex(0);
        //设置sheet的name
        $objActSheet->setTitle($sheetName);
        //填充表头信息
        for ($i = 0; $i < count($tableheader); $i++) {
            //单独设置D列宽度为15
            $objActSheet->getColumnDimension($letter[$i])->setWidth(20);
            $objActSheet->setCellValue("$letter[$i]1", "$tableheader[$i]");
            //设置表头字体样式
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setName('微软雅黑');
            //设置表头字体大小
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setSize(10);
            //设置表头字体是否加粗
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setBold(true);
            //设置表头文字垂直居中
            $objActSheet->getStyle("$letter[$i]1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //设置文字上下居中
            $objActSheet->getStyle("$letter[$i]1")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //设置表头外的文字垂直居中
            $excel->setActiveSheetIndex(0)->getStyle($letter[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }

        //填充表格信息
        for ($i = 2; $i <= count($data) + 1; $i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $objActSheet->setCellValue("$letter[$j]$i", "$value");
                $j++;
            }
        }
        //创建Excel输入对象
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save($excelDir . '/' . $sheetName . '.xlsx');    //文件保存
        return $excelDir . DS. $sheetName . '.xlsx';
    }
    /**
     * 判断用户是否存在
     * @param  string $name 用户名
     * @param  string$enc_password 密码
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function Exist($data) {
        $sql = 'SELECT `id`,`customer_id`,`lang`,`name`,`bn`,`profile`,`country`,`province`,`city`,`reg_date`,';
        $sql .= '`logo`,`official_website`,`brand`,`bank_name`,`swift_code`,`bank_address`,`bank_account`,`buyer_level`,`credit_level`,';
        $sql .= '`status`,`remarks`,`apply_at`,`approved_at`';
        $sql .= ' FROM ' . $this->g_table;
        $where = '';
        if (!empty($data['email'])) {
            $where .= " where email = '" . $data['email'] . "'";
        }
        if (!empty($data['mobile'])) {
            if ($where) {
                $where .= " or mobile = '" . $data['mobile'] . "'";
            } else {
                $where .= " where mobile = '" . $data['mobile'] . "'";
            }
        }
        if (!empty($data['id'])) {
            if ($where) {
                $where .= " and id = '" . $data['id'] . "'";
            } else {
                $where .= " where id = '" . $data['id'] . "'";
            }
        }
        if (!empty($data['customer_id'])) {
            if ($where) {
                $where .= " and customer_id = '" . $data['customer_id'] . "'";
            } else {
                $where .= " where customer_id = '" . $data['customer_id'] . "'";
            }
        }

        if ($where) {
            $where .= " and deleted_flag = 'N'";
        } else {
            $where .= " where deleted_flag = 'N'";
        }
        if ($where) {
            $sql .= $where;
        }
        $row = $this->query($sql);
        return empty($row) ? false : $row;
    }

    public function create_data($create = []) {
        if (isset($create['buyer_no'])) {
            $data['buyer_no'] = $create['buyer_no'];
        }
        if (isset($create['buyer_code'])) {
            $data['buyer_code'] = $create['buyer_code'];    //新增CRM编码，张玉良 2017-9-27
        }
        if (isset($create['lang'])) {
            $data['lang'] = $create['lang'];
        } else {
            $data['lang'] = 'en';
        }
        if (isset($create['name'])) {
            $data['name'] = $create['name'];
        }
        if (isset($create['first_name'])) {
            $data['first_name'] = $create['first_name'];
        }
        if (isset($create['bn'])) {
            $data['bn'] = $create['bn'];
        }
        if (isset($create['profile'])) {
            $data['profile'] = $create['profile'];
        }
        if (isset($create['type_remarks'])) {
            $data['type_remarks'] = $create['type_remarks'];
        }
        if (isset($create['employee_count'])) {
            $data['employee_count'] = $create['employee_count'];
        }
        if (isset($create['reg_capital'])) {
            $data['reg_capital'] = $create['reg_capital'];
        }
        if (isset($create['reg_capital_cur'])) {
            $data['reg_capital_cur'] = $create['reg_capital_cur'];
        }
        if (isset($create['expiry_at'])) {
            $data['expiry_at'] = $create['expiry_at'];
        }
        if (isset($create['country_bn'])) {
            $data['country_bn'] = $create['country_bn'];
        }
        if (isset($create['area_bn'])) {
            $data['area_bn'] = $create['area_bn'];
        }
        if (isset($create['official_email'])) {
            $data['official_email'] = $create['official_email'];
        }
        if (isset($create['official_phone'])) {
            $data['official_phone'] = $create['official_phone'];
        }
        if (isset($create['official_fax'])) {
            $data['official_fax'] = $create['official_fax'];
        }
        if (isset($create['province'])) {
            $data['province'] = $create['province'];
        }
        if (isset($create['logo'])) {
            $data['logo'] = $create['logo'];
        }
        if (isset($create['city'])) {
            $data['city'] = $create['city'];
        }
        if (isset($create['biz_scope'])) {
            $data['biz_scope'] = $create['biz_scope'];
        }
        if (isset($create['intent_product'])) {
            $data['intent_product'] = $create['intent_product'];
        }
        if (isset($create['purchase_amount'])) {
            $data['purchase_amount'] = $create['purchase_amount'];
        }
        if (isset($create['brand'])) {
            $data['brand'] = $create['brand'];
        }
        if (isset($create['bank_name'])) {
            $data['bank_name'] = $create['bank_name'];
        }
        if (isset($create['official_website'])) {
            $data['official_website'] = $create['official_website'];
        }
        if (isset($create['remarks'])) {
            $data['remarks'] = $create['remarks'];
        }
        if (isset($create['created_by'])) {
            $time=date('Y-m-d H:i:s');
            $data['created_by'] = $create['created_by'];
            $data['checked_by'] = $create['created_by'];
            $data['created_at'] = $time;
            $data['checked_at'] = $time;
        }
        $data['status'] = 'APPROVED';  //APPROVING
//        $datajson = $this->create($data);
        $datajson = $data;
        //
        $config = \Yaf_Application::app()->getConfig();
        $myhost=$config['myhost'];
        if($myhost!="http://api.erui.com/"){
            $create['is_group_crm']=false;
        }
        //
        if(mb_substr($data['buyer_code'],3)=='KSD'){
            $create['is_group_crm']=false;
        }
        if($create['is_group_crm'] == true){
            $group_status = $this->addGroupCrm($datajson);
            $datajson['group_status'] = $group_status;
        }
        $datajson['source']=1;
        try {
            $res = $this->add($datajson);
//            if ($res) {
//                $checked_log_arr['id'] = $res;
//                $checked_log_arr['status'] = 'APPROVED';
//                $checked_log_arr['checked_by'] = $create['created_by'];
//                $checked_log = new BuyerCheckedLogModel();
//                $checked_log->create_data($checked_log_arr);
//            }
            return $res;
        } catch (Exception $ex) {
            print_r($ex);
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * @param $datajson
     * 向集团CRM添加客户信息数据
     * wangs
     */
    public function addGroupCrm($datajson){
        $country = new CountryModel();
        $name = $country->getCountryByBn($datajson['country_bn'],'zh');
        $datajson['country_name'] = $name;  //获取国家名称

        $xml = <<<EOF
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:acc="http://siebel.com/sales/account/">
<soapenv:Header/>
<soapenv:Body>
  <acc:InsertAccount>
     <name>{$datajson['name']}</name>
     <mobile>{$datajson['official_phone']}</mobile>
     <country_bn>{$datajson['country_name']}</country_bn>
     <email>{$datajson['official_email']}</email>
     <biz_scope>{$datajson['biz_scope']}</biz_scope>
     <crm_code>{$datajson['buyer_code']}</crm_code>
     <first_name>{$datajson['first_name']}</first_name>
  </acc:InsertAccount>
</soapenv:Body>
</soapenv:Envelope>
EOF;
        //请求集团CRM
        $opt = array(
            'http'=>array(
                'method'=>"POST",
                'header'=>"Content-Type: text/xml",
                'content' => $xml
            )
        );
        $context = stream_context_create($opt);
//        $url = 'http://172.16.26.152:8088/eai_anon_chs/start.swe?SWEExtSource=AnonWebService&amp;SweExtCmd=Execute';
        $url = 'http://172.16.26.154:7780/eai_anon_chs/start.swe?SWEExtSource=AnonWebService&amp;SweExtCmd=Execute';
        $str = file_get_contents($url,false,$context);  //得到客户crm数据

        $need = strstr($str,'<errorMsg>');
        $need = strstr($need,'</rpc:InsertAccountResponse>',true);
        $xml = '<root>'.$need.'</root>';
        $xmlObj = simplexml_load_string($xml);
        $arr = json_decode(json_encode($xmlObj),true);
        return $arr['status'];
    }

    /**
     * 个人信息查询
     * @param  $data 条件
     * @return
     * @author jhw
     */
    public function info($data) {
        $lang=$data['lang'];
        if ($data['id']) {
//            $field='buyer.id,buyer.name,buyer.buyer_code,buyer.biz_scope,buyer.intent_product,buyer.purchase_amount,buyer.country_bn,buyer.id,buyer.id,buyer.id';
            $field='buyer.*';
            $field.=',em.name as checked_name,';
            $field.='account.show_name as first_name,account.email';

            $buyerInfo = $this->where(array("buyer.id" => $data['id'],'buyer.deleted_flag'=>'N'))->field($field)
                    ->join('erui_buyer.buyer_account account on buyer.id=account.buyer_id and account.deleted_flag=\'N\'', 'left')
                    ->join('erui_sys.employee em on em.id=buyer.checked_by and em.deleted_flag=\'N\'', 'left')
                    ->find();
            if(!empty($buyerInfo['country_bn'])){
                $buyerInfo['country_bn']=trim($buyerInfo['country_bn']);
                $area=$this->table('erui_operation.market_area_country')->alias('country')
                    ->join("erui_operation.market_area area on country.market_area_bn=area.bn and area.deleted_flag='N' and area.lang='$lang'",'left')
                    ->field('area.name as area_name')
                    ->where("country.country_bn='$buyerInfo[country_bn]'")
                    ->find();
                $country=$this->table('erui_dict.country')
                    ->field('name as country_name')
                    ->where("bn='$buyerInfo[country_bn]' and lang='$lang'")
                    ->find();
                $buyerInfo['area_name']=$area['area_name'];
                $buyerInfo['country_name']=$country['country_name'];
            }else{
                $buyerInfo['area_name']='';
                $buyerInfo['country_name']='';
            }
            if(!empty($buyerInfo['official_phone'])){
                if(preg_match('/ /',$buyerInfo['official_phone'])){ //匹配空格
                    $buyerInfo['official_phone']=str_replace(' ','-',$buyerInfo['official_phone']);
                }elseif(!preg_match('/-/',$buyerInfo['official_phone']) && !preg_match('/ /',$buyerInfo['official_phone'])){
                    $buyerInfo['official_phone']='-'.$buyerInfo['official_phone'];
                }
            }

            return $buyerInfo;
        } else {
            return false;
        }
    }

    /**
     * 采购商个人信息更新
     * @author klp
     */
    public function update_data($create, $where) {

        if (isset($create['buyer_code'])) {
            $data['buyer_code'] = $create['buyer_code'];
        }
        if (isset($create['checked_by'])) {
            $data['checked_by'] = $create['checked_by'];
        }
        if (isset($create['checked_at'])) {
            $data['checked_at'] = $create['checked_at'];
        }
        if (isset($create['lang'])) {
            $data['lang'] = $create['lang'];
        } else {
            $data['lang'] = 'en';
        }
        if (isset($create['name'])) {
            $data['name'] = $create['name'];
        }
        if (isset($create['first_name'])) {
            $data['first_name'] = $create['first_name'];
        }
        if (isset($create['bn'])) {
            $data['bn'] = $create['bn'];
        }
        if (isset($create['profile'])) {
            $data['profile'] = $create['profile'];
        }
        if (isset($create['country_bn'])) {
            $data['country_bn'] = $create['country_bn'];
        }
        if (isset($create['official_email'])) {
            $data['official_email'] = $create['official_email'];
        }
        if (isset($create['level_at'])) {
            $data['level_at'] = $create['level_at'];
        }
        if (isset($create['official_phone'])) {
            $data['official_phone'] = $create['official_phone'];
        }
        if (isset($create['official_fax'])) {
            $data['official_fax'] = $create['official_fax'];
        }
        if (isset($create['province'])) {
            $data['province'] = $create['province'];
        }
        if (isset($create['area_bn'])) {
            $data['area_bn'] = $create['area_bn'];
        }
        if (isset($create['logo'])) {
            $data['logo'] = $create['logo'];
        }

        if (isset($create['type_remarks'])) {
            $data['type_remarks'] = $create['type_remarks'];
        }
        if (isset($create['employee_count'])) {
            $data['employee_count'] = $create['employee_count'];
        }
        if (isset($create['reg_capital'])) {
            $data['reg_capital'] = $create['reg_capital'];
        }
        if (isset($create['reg_capital_cur'])) {
            $data['reg_capital_cur'] = $create['reg_capital_cur'];
        }
        if (isset($create['expiry_at'])) {
            $data['expiry_at'] = $create['expiry_at'];
        }
        if (isset($create['city'])) {
            $data['city'] = $create['city'];
        }
        if (isset($create['line_of_credit'])) {
            $data['line_of_credit'] = $create['line_of_credit'];
        }
        if (isset($create['credit_available'])) {
            $data['credit_available'] = $create['credit_available'];
        }
        if (isset($create['brand'])) {
            $data['brand'] = $create['brand'];
        }
        if (isset($create['bank_name'])) {
            $data['bank_name'] = $create['bank_name'];
        }
        if (isset($create['official_website'])) {
            $data['official_website'] = $create['official_website'];
        }
        if (isset($create['buyer_level'])) {
            $data['buyer_level'] = $create['buyer_level'];
        }
        if (isset($create['remarks'])) {
            $data['remarks'] = $create['remarks'];
        }
        if (isset($create['checked_by'])) {
            $data['checked_by'] = $create['checked_by'];
        }
        if (isset($create['checked_at'])) {
            $data['checked_at'] = $create['checked_at'];
        }
        if (isset($create['biz_scope'])) {
            $data['biz_scope'] = $create['biz_scope'];
        }
        if (isset($create['intent_product'])) {
            $data['intent_product'] = $create['intent_product'];
        }
        if (isset($create['purchase_amount'])) {
            $data['purchase_amount'] = $create['purchase_amount'];
        }
        if (isset($create['close_info'])) {
            $data['close_info'] = $create['close_info'];
        }
        if (!empty($create['status'])) {     //关闭客户信息备注
            $data['status'] = $create['status'];
        }
        return $this->where($where)->save($data);
    }

    /**
     * 通过顾客id获取会员等级
     * @author klp
     */
    public function getService($info, $token) {
        $where = array();
        if (!empty($token['customer_id'])) {
            $where['customer_id'] = $token['customer_id'];
        } else {
            jsonReturn('', '-1001', '用户[id]不可以为空');
        }
        $lang = $info['lang'] ? strtolower($info['lang']) : (browser_lang() ? browser_lang() : 'en');
        //获取会员等级
        $buyerLevel = $this->field('buyer_level')
                ->where($where)
                ->find();
        //获取服务
        $MemberBizService = new MemberBizServiceModel();
        $result = $MemberBizService->getService($buyerLevel, $lang);
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * 获取采购商信息 NEW
     * @author klp
     */
    public function buyerCerdit($user) {
        $where = array();
        if (!empty($user['id'])) {
            $where['b.id'] = $user['id'];
        } else {
            jsonReturn('', '-1001', '用户[id]不可以为空');
        }
        if (isset($user['buyer_no'])) {
            $where['b.buyer_no'] = $user['buyer_no'];
        }
        $where['b.deleted_flag'] = 'N';

//        $buyerAccountModel = new BuyerAccountModel();
//        $tableAcco = $buyerAccountModel->getTableName();
        $BuyerreginfoModel = new BuyerreginfoModel();
        $tableReg = $BuyerreginfoModel->getTableName();
        $buyerBankInfoModel = new BuyerBankInfoModel();
        $tableBank = $buyerBankInfoModel->getTableName();
        try {
            //必填项
            $fields = 'b.id as buyer_id, b.lang, b.buyer_no, b.area_bn, b.name, bd.address, bb.country_code as bank_country_code, bb.address as bank_address, bb.bank_name';
            //基本信息-$this
            $fields .= ',b.buyer_type,b.bn,b.country_bn,b.profile,b.province,b.city,b.official_email,b.official_phone,b.official_fax,b.brand,b.official_website,b.line_of_credit,b.credit_available,b.buyer_level,b.credit_level,b.recommend_flag,b.status,b.remarks';
            //注册信息-BuyerreginfoModel
            $fields .= ',br.legal_person_name,br.legal_person_gender,br.reg_date,br.expiry_date,br.registered_in,br.reg_capital,br.social_credit_code,br.biz_nature,br.biz_scope,br.biz_type,br.service_type,br.branch_count,br.employee_count,br.equitiy,br.turnover,br.profit,br.total_assets,br.reg_capital_cur_bn,br.equity_ratio,br.equity_capital';
            //注册银行信息-BuyerBankInfoModel
            $fields .= ',bb.swift_code,bb.bank_account,bb.country_bn as bank_country_bn,bb.zipcode as bank_zipcode,bb.phone,fax,bb.turnover,bb.profit,bb.total_assets,bb.reg_capital_cur_bn,bb.equity_ratio,bb.equity_capital,bb.branch_count,bb.employee_count,bb.remarks as bank_remarks';
            $buyerInfo = $this->alias('b')
                    ->field($fields)
                    ->join($tableBank . ' as bb on bb.buyer_id=b.id ', 'left')
                    ->join($tableReg . ' as br on br.buyer_id=b.id', 'left')
                    ->where($where)
                    ->find();
            if ($buyerInfo) {
                return $buyerInfo ? $buyerInfo : array();
            }
            return array();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 获取采购商信息
     * @author klp
     */
    public function buyerInfo($user) {
        $where = array();
        if (!empty($user['id'])) {
            $where['id'] = $user['id'];
        } else {
            jsonReturn('', '-1001', '用户[id]不可以为空');
        }
        if (isset($user['buyer_no'])) {
            $where['buyer_no'] = $user['buyer_no'];
        }
        $where['deleted_flag'] = 'N';
        $field = 'id,lang,buyer_type,buyer_no,name,bn,country_bn,profile,province,city,official_email,official_phone,official_fax,brand,official_website,line_of_credit,credit_available,buyer_level,credit_level,recommend_flag,status,remarks,apply_at,created_by,created_at,checked_by,checked_at';
        try {
            $buyerInfo = $this->field($field)->where($where)->find();
            if ($buyerInfo) {
                $BuyerreginfoModel = new BuyerreginfoModel();
                $result = $BuyerreginfoModel->buyerRegInfo($where);
                return $result ? array_merge($buyerInfo, $result) : $buyerInfo;
            }
            return array();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 企业信息新建-门户
     * @author klp
     */
    public function editInfo($token, $input) {
        if (!isset($input)) {
            return false;
        }
        $this->startTrans();
        try {
            if (is_array($input)) {
                $checkout = $this->checkParam($input);
                $data = [
                    'name' => $checkout['name'],
                    'country_code' => strtoupper($checkout['country_code']),
                    'country_bn' => strtoupper($checkout['country_code']),
                    'official_email' => isset($checkout['official_email']) ? $checkout['official_email'] : '',
                    'official_phone' => isset($checkout['official_phone']) ? $checkout['official_phone'] : '',
                    'official_fax' => isset($checkout['official_fax']) ? $checkout['official_fax'] : '',
                    'official_website' => isset($checkout['official_website']) ? $checkout['official_website'] : '',
                    'province' => isset($checkout['province']) ? $checkout['province'] : '', //暂为办公地址
                    'remarks' => isset($checkout['remarks']) ? $checkout['remarks'] : '',
                    'recommend_flag' => isset($checkout['recommend_flag']) ? strtoupper($checkout['recommend_flag']) : 'N'
                ];
                //判断是新增还是编辑,如果有customer_id就是编辑,反之为新增
                $result = $this->field('id')->where(['id' => $token['id']])->find();
                if ($result) {
                    $result = $this->where(['id' => $token['id']])->save($data);
                    if (!$result) {
                        $this->rollback();
                        return false;
                    }
                } else {
                    // 生成用户编码
                    $condition['page'] = 0;
                    $condition['countPerPage'] = 1;
                    $data_t_buyer = $this->getlist($condition);
                    if ($data_t_buyer && substr($data_t_buyer[0]['buyer_no'], 1, 8) == date("Ymd")) {
                        $no = substr($data_t_buyer[0]['buyer_no'], -1, 6);
                        $no++;
                    } else {
                        $no = 1;
                    }
                    $temp_num = 1000000;
                    $new_num = $no + $temp_num;
                    $real_num = "C" . date("Ymd") . substr($new_num, 1, 6); //即截取掉最前面的“1”即为buyer_no

                    $data['buyer_no'] = $real_num;
                    $data['apply_at'] = date('Y-m-d H:i:s', time());
                    $data['created_at'] = date('Y-m-d H:i:s', time());
                    $data['status'] = self::STATUS_CHECKING; //待审状态
                    $result = $this->add($data);
                    if (!$result) {
                        $this->rollback();
                        return false;
                    }
                }
                //buyer_reg_info
                $buyerRegInfo = new BuyerreginfoModel();
                $result = $buyerRegInfo->createInfo($token, $input);
                if (!$result) {
                    $this->rollback();
                    return false;
                }
                //buyer_address
                $BuyerBankInfoModel = new BuyerBankInfoModel();
                $res = $BuyerBankInfoModel->editInfo($token, $input);
                if (!$res) {
                    $this->rollback();
                    return false;
                }
            } else {
                return false;
            }
            $this->commit();
            return $token['buyer_id'];
        } catch (Exception $e) {
            $this->rollback();
//            var_dump($e);//测试
            return false;
        }
    }

    /**
     * 参数校验-门户
     * @author klp
     */
    private function checkParam($param = []) {
        if (empty($param)) {
            return false;
        }
        $results = array();
        if (empty($param['name'])) {
            $results['code'] = -101;
            $results['message'] = '[name]不能为空!';
        }
        if (empty($param['bank_name'])) {
            $results['code'] = -101;
            $results['message'] = '[bank_name]不能为空!';
        }
        if (empty($param['bank_address'])) {
            $results['code'] = -101;
            $results['message'] = '[bank_address]不能为空!';
        }
        if (empty($param['province'])) {
            $results['code'] = -101;
            $results['message'] = '[province]不能为空!';
        }
        if ($results) {
            jsonReturn($results);
        }
        return $param;
    }

    /**
     * 获取授信的列表
     * @param  string $code 编码
     * @param  string $lang 语言
     * @return mix
     * @author klp
     */
    public function getListCredit($condition) {
        $BuyerCreditLogModel = new BuyerCreditLogModel(); //取BuyerCreditLog表名
        $creditLogtable = $BuyerCreditLogModel->getTableName();
        $where = array();
        list($from, $pagesize) = $this->_getPage($where);
        //编号
        $this->_getValue($where, $condition, 'id', 'string', 'b.id');
        //审核人
        $this->_getValue($where, $condition, 'approved_by', 'string', 'cb.approved_by');
        $this->_getUserids($where, $condition, 'approved_by_name', 'cb.approved_by');


        $this->_getUserids($where, $condition, 'checked_by_name', 'cb.checked_by');

        //审核人
        //   $this->_getValue($where, $condition, 'approved_by_name', 'array', 'cb.approved_by');
        //公司名称
        $this->_getValue($where, $condition, 'name', 'like', 'b.name');
        //审核状态
        $where['b.status'] = self::STATUS_APPROVED;


        if (isset($condition['status']) && $condition['status']) {
            switch ($condition['status']) {
                case '05'://信保通过

                    $where['cl.out_status'] = 'APPROVED';
                    break;
                case '04'://信保驳回
                    $where['cl.out_status'] = 'REJECTED';
                    break;
                case '03'://易瑞通过
                    $where['cl.in_status'] = 'APPROVED';
                    break;
                case '02'://易瑞驳回
                    $where['cl.in_status'] = 'REJECTED';
                    break;
                case '01'://待易瑞审核
                    $where['cl.in_status'] = 'APPROVING';
                    break;
                default :
                    break;
            }
        }
        //授信额度(暂无字段,待完善)
        $this->_getValue($where, $condition, 'credit', 'between', 'b.line_of_credit');
        //信保审核时间段(暂无,待完善)
        $this->_getValue($where, $condition, 'approved_at', 'between', 'cl.approved_at');
        //易瑞审核时间段
        $this->_getValue($where, $condition, 'checked_at', 'between', 'cl.checked_at');
        //字段待完善
        $field = 'b.id,b.buyer_no,b.name,b.apply_at,b.lang,cl.credit_grantor,cl.credit_granted,'
                . 'cl.in_status,cl.checked_by,cl.checked_at,cl.out_status,cl.approved_by,cl.approved_at';

        $result = $this->alias('b')->field($field)->order("id desc")
                        ->join($creditLogtable . ' as cl ON b.id = cl.id', 'INNER')
                        ->limit($from, $pagesize)->where($where)->select();

        $count = $this->alias('b')->join($creditLogtable . ' as cl ON b.id = cl.id', 'LEFT')
                        ->where($where)->count('b.id');
        $this->_setUserName($result, 'checked_by');
        $this->_setUserName($result, 'approved_by');
        return [$result, $count];
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    private function _setUserName(&$arr, $filed) {
        if ($arr) {
            $employee_model = new EmployeeModel();
            $userids = [];
            foreach ($arr as $key => $val) {
                $userids[] = $val[$filed];
            }
            $usernames = $employee_model->getUserNamesByUserids($userids);
            foreach ($arr as $key => $val) {
                if ($val[$filed] && isset($usernames[$val[$filed]])) {
                    $val[$filed . '_name'] = $usernames[$val[$filed]];
                } else {
                    $val[$filed . '_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

    private function _getUserids(&$where, &$condition, $name, $filed = 'created_by') {
        if (isset($condition[$name]) && $condition[$name]) {
            $employee_model = new EmployeeModel();
            $userids = $employee_model->getUseridsByUserName($condition[$name]);
            if ($userids) {
                $where[$filed] = ['in', $userids];
            }
        }
    }

    /*
     * 根据用户姓 获取用户ID
     * @param string $BuyerName // 客户名称
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function getBuyeridsByBuyerName($buyername) {

        try {
            $where = [];
            if ($buyername) {
                $where['name'] = ['like', '%' . trim($buyername) . '%'];
            } else {
                return false;
            }
            $buyers = $this->where($where)->field('id')->select();
            $buyerids = [];
            foreach ($buyers as $buyer) {
                $buyerids[] = $buyer['id'];
            }
            return $buyerids;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据用户ID 获取用户姓名
     * @param array $user_ids // 用户ID
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function getBuyerNamesByBuyerids($buyer_ids) {

        try {
            $where = [];

            if (is_string($buyer_ids)) {
                $where['id'] = $buyer_ids;
            } elseif (is_array($buyer_ids) && !empty($buyer_ids)) {
                $where['id'] = ['in', $buyer_ids];
            } else {
                return false;
            }
            $buyers = $this->where($where)->field('id,name,buyer_no')->select();

            $buyer_names = [];
            foreach ($buyers as $buyer) {
                $buyer_arr['buyer_names'][$buyer['id']] = $buyer['name'];
                $buyer_arr['buyer_nos'][$buyer['id']] = $buyer['buyer_no'];
            }
            return $buyer_arr;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 根据用户ID 获取用户姓名
     * @param array $user_ids // 用户ID
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function getBuyerNosByBuyerids($buyer_ids) {

        try {
            $where = [];

            if (is_string($buyer_ids)) {
                $where['id'] = $buyer_ids;
            } elseif (is_array($buyer_ids) && !empty($buyer_ids)) {
                $where['id'] = ['in', $buyer_ids];
            } else {
                return false;
            }
            $buyers = $this->where($where)->field('id,name,buyer_no')->select();

            $buyer_names = [];
            foreach ($buyers as $buyer) {
                $buyer_arr[$buyer['id']] = $buyer['buyer_no'];
            }
            return $buyer_arr;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /*
     * 更新授信金额
     * @param int $order_id // 订单ID
     * @param string  $type // 授信类型
     * @param floatval $amount // 授信金额
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function updateCredite($order_id, $type, $amount) {
        $order_model = new OrderModel();
        $orderinfo = $order_model->field('buyer_id,id')->where(['id' => $order_id])->find();

        $amount = floatval($amount);
        $buyer = $this->field('credit_available,credit_available,id')
                        ->where(['id' => $orderinfo['buyer_id']])->find();
        if ($buyer) {
            if ($type == 'REFUND') {
                $flag = $this->where(['id' => $buyer['id']])
                        ->save(['credit_available' => $buyer['credit_available'] + $amount]);
            } elseif ($type == 'SPENDING') {
                $flag = $this->where(['id' => $buyer['id']])
                        ->save(['credit_available' => $buyer['credit_available'] - $amount]);
            }
            if ($flag === false) {

                return ['code' => MSG::MSG_FAILED, 'message' => '更新授信额度错误!'];
            } else {
                return ['code' => MSG::MSG_SUCCESS, 'message' => '更新成功!'];
            }
        } else {
            return ['code' => MSG::MSG_FAILED, 'message' => '客户不存在!'];
        }
    }

    /**
     * 获取各状态下会员数量
     * @return data
     * @author jhw
     */
    public function getBuyerCountByStatus($condition) {
//        $sql = "SELECT  buyer.`status` ,COUNT(*)  as number ";
//        $sql .= ' FROM erui_buyer.buyer buyer';
//        if(!empty($condition['employee_name'])){
//            $sql .= " left Join `erui_buyer`.`buyer_agent` buyer_agent";
//            $sql .= " on buyer_agent.buyer_id = buyer.id ";
//            $sql .= " Join `erui_sys`.`employee` employee";
//            $sql .= " on buyer_agent.`agent_id` = employee.`id`";
//        }
//        $where = " WHERE buyer.deleted_flag = 'N'  AND employee.deleted_flag='N' ";
        $where = " WHERE buyer.deleted_flag = 'N'";
        $where .= " and agent.deleted_flag = 'N'";
        if (!empty($condition['country_bn'])) {
            $where .= " And buyer.country_bn=$condition[country_bn] ";
        }
        if (!empty($condition['buyer_no'])) {
            $where .= " And buyer.buyer_no like '%$condition[buyer_no]%'";
        }
        if (!empty($condition['employee_name'])) {
            $where .= " And employee.`name`  like '%" . $condition['employee_name'] . "%'";
        }
        if (!empty($condition['status'])) {
            $where .= ' And buyer.status  ="' . $condition['status'] . '"';
        }
        if (!empty($condition['checked_at_start'])) {
            $where .= ' And agent.checked_at  >="' . $condition['checked_at_start'] . '"';
        }
        if (!empty($condition['checked_at_end'])) {
            $where .= ' And agent.checked_at  <="' . $condition['checked_at_end'] . '"';
        }
        if (!empty($condition['source'])) {
            $where .= " And buyer.source=$condition[source] ";
        }
        if (!empty($condition['buyer_level'])) {    //客户等级
            $where .= ' And buyer.buyer_level=\''.$condition['buyer_level'].'\'';
        }
        if (!empty($condition['created_at_start'])) {
            $where .= ' And buyer.created_at  >="' . $condition['created_at_start'] . '"';
        }
        if (!empty($condition['created_at_end'])) {
            $where .= ' And buyer.created_at  <="' . $condition['created_at_end'] . '"';
        }
        if (!empty($condition['buyer_code'])) {
            $where .= ' And buyer.buyer_code  like "%' . $condition['buyer_code'] . '%"';
        }
        if (!empty($condition['min_percent'])) {
            $where .= ' And buyer.percent  >=' . $condition['min_percent'];
        }
        if (!empty($condition['max_percent'])) {
            $where .= ' And buyer.percent  <=' . $condition['max_percent'];
        }
//        if ($where) {
//            $sql .= $where;
//        }
//        $sql .= ' Group By buyer.status';
//
//        $statusCount = $this->query($sql);  //各状态下的客户数量
//        $field=array(
//            'APPROVED', //审核通过
//            'FIRST_APPROVED', //初审通过
//            'APPROVING', //待审核
//            'FIRST_REJECTED', //初审驳回
//            'REJECTED', //驳回
//        );
//        $statusArr = [];
//        foreach($statusCount as $key => $value){
//            $statusArr[$value['status']]=$value['number'];
//            foreach($field as $v){
//                if(empty($statusArr[$v])){
//                    $statusArr[$v]=0;
//                }
//            }
//        }
        //统计客户数量
        $sqlTotal = "SELECT  COUNT(*)  as total_count ";
        $sqlTotal .= ' FROM erui_buyer.buyer buyer';
        if(!empty($condition['employee_name'])){
            $sqlTotal .= " left Join `erui_buyer`.`buyer_agent` buyer_agent";
            $sqlTotal .= " on buyer_agent.buyer_id = buyer.id ";
            $sqlTotal .= " Join `erui_sys`.`employee` employee";
            $sqlTotal .= " on buyer_agent.`agent_id` = employee.`id`";
            $where.=" AND employee.deleted_flag='N'";
        }
        $totalCount=$this->query($sqlTotal.$where."");
        $totalCount=$totalCount[0]['total_count'];
        //统计等级-客户等级下的数量
        $sqlLevel = "SELECT  buyer.buyer_level,COUNT(*)  as level_count ";
        $sqlLevel .= ' FROM erui_buyer.buyer buyer';
        if(!empty($condition['employee_name'])){
            $sqlLevel .= " left Join `erui_buyer`.`buyer_agent` buyer_agent";
            $sqlLevel .= " on buyer_agent.buyer_id = buyer.id ";
            $sqlLevel .= " Join `erui_sys`.`employee` employee";
            $sqlLevel .= " on buyer_agent.`agent_id` = employee.`id`";
            $where.=" AND employee.deleted_flag='N'";
        }
        $level=$this->query($sqlLevel.$where." GROUP BY buyer.buyer_level");
        $arrLevel=array();
        foreach($level as $k => $v){
            $arrLevel[$v['buyer_level']]=$v['level_count'];
            if(empty($arrLevel['52'])){
                $arrLevel['52']=0;
            }
            if(empty($arrLevel['53'])){
                $arrLevel['53']=0;
            }
            if(empty($arrLevel[''])){
                $arrLevel['']=0;
            }
        }
        $buyer_level['kong']=$arrLevel[''];
        $buyer_level['ordinary']=$arrLevel['52'];
        $buyer_level['senior']=$arrLevel['53'];
        $result['status']=$statusArr;
        $result['total_count']=$totalCount;
        $result['level_count']=$buyer_level;
        return $result;
    }

    /**
     * 客户档案管理搜索列表-
     * wangs
     */
    public function buyerList1($data)
    {
////        $page = isset($data['page'])?$data['page']:1;
////        $pageSize = 10;
////        $offset = ($page-1)*$pageSize;
////        $arr = $this->getBuyerManageDataByCond($data,$offset,$pageSize);    //获取数据
//        $arr = $this->getBuyerStatisListCond($data);    //获取数据
//        print_r($arr);die;
//        $totalCount = $arr['totalCount']?$arr['totalCount']:0;
//        $totalPage = ceil($totalCount/$pageSize);
//        $info = $arr['info']?$arr['info']:[];
//        $res = array(
//            'page'=>$page,
//            'totalCount'=>$totalCount,
//            'totalPage'=>$totalPage,
//            'info' => $info
//        );
//        return $res;
    }
    public function buyerList($data,$excel=false){
        set_time_limit(0);
        $lang=!empty($data['lang'])?$data['lang']:'zh';
        $cond = $this->getBuyerStatisListCond($data,true,true);
        if($cond==false){   //无角色,无数据
            return false;
        }
        $currentPage = !empty($data['currentPage'])?$data['currentPage']:1;
        $pageSize = 10;
        $totalCount=$this->crmGetBuyerTotal($cond); //获取总条数
        $totalPage = ceil($totalCount/$pageSize);
        if(!empty($data['currentPage']) && $data['currentPage'] >0){
            $currentPage = ceil($data['currentPage']);
        }
        $offset = ($currentPage-1)*$pageSize;
        $field='';
        $fieldArr = array(
            'id',
            'percent',
            'buyer_no',     //客户编号
            'buyer_code',   //客户CRM代码buy
            'name',   //客户名称buy
            'status',   //审核状态
            'source',   //审核状态
            'buyer_level',  //客户等级
            'level_at',  //客户等级
            'country_bn',    //国家
            'created_at',   //注册时间/创建时间
            'created_by',   //注册时间/创建时间
        );
        foreach($fieldArr as $v){
            $field .= ',buyer.'.$v;
        }
        $field=substr($field,1);
        $info = $this->alias('buyer')
            ->field($field)
            ->where($cond)
            ->group('buyer.id')
            ->order('buyer.checked_at desc,buyer.id desc')
            ->limit($offset,$pageSize)
            ->select();
        $level = new BuyerLevelModel();
        $country = new CountryModel();
        $agent = new BuyerAgentModel();
        $order = new OrderModel();
        foreach($info as $k => $v){
            if(empty($v['percent'])){
                $info[$k]['percent']='--';
            }else{
                $info[$k]['percent']=$info[$k]['percent'].'%';
            }
            if(!empty($v['buyer_level'])){ //客户等级
                $info[$k]['buyer_level'] = $level->getBuyerLevelById($v['buyer_level'],$lang);
            }else{
                $info[$k]['buyer_level']=$lang=='zh'?'注册客户':'Registered customer';
            }
            if($v['source']==1){
                $info[$k]['source']=$lang=='zh'?'BOSS':'BOSS';
            }elseif($v['source']==2){
                $info[$k]['source']=$lang=='zh'?'门户':'WEB';
            }elseif($v['source']==3){
                $info[$k]['source']=$lang=='zh'?'APP':'APP';
            }
            if($v['status']=='APPROVING'){
                $info[$k]['status']=$lang=='zh'?'待分配':'APPROVING';
            }elseif($v['status']=='APPROVED'){
                $info[$k]['status']=$lang=='zh'?'已分配':'APPROVED';
            }elseif($v['status']=='REJECTED'){
                $info[$k]['status']=$lang=='zh'?'已关闭':'REJECTED';
            }
            if(!empty($v['country_bn'])){ //国家
                $area = $country->getCountryAreaByBn($v['country_bn'],$lang);
                $info[$k]['area'] = $area['area'];
                $info[$k]['country_name'] = $area['country'];
                $info[$k]['country_name'] = $area['area'].'/'.$area['country'];
            }
            if(!empty($v['created_by'])){
                $name=$this->table('erui_sys.employee')->field('name')
                    ->where(array('id'=>$v['created_by'],'deleted_flag'=>'N'))->find();
                $info[$k]['created_name']=$name['name'];
            }else{
                $info[$k]['created_name']='';
            }

            if(!empty($data['agent_name'])){
                $agentInfo=$agent->getBuyerAgentArr($v['id'],$data['agent_name']);
            }else{
                $agentInfo=$agent->getBuyerAgentArr($v['id']);
            }
            $info[$k]['agent_id'] = $agentInfo['id'];
            $info[$k]['agent_name'] = $agentInfo['name'];
            $info[$k]['agent_at'] = $agentInfo['checked_at'];
//---------------------------
            if($v['source']==1 && empty($info[$k]['agent_name'])){
                $name=$this->table('erui_sys.employee')->field('name')
                    ->where(array('id'=>$v['created_by'],'deleted_flag'=>'N'))->find();
                $info[$k]['agent_id'] = $v['created_by'];
                $info[$k]['agent_name']=$name['name'];
                $info[$k]['agent_at']=$v['created_at'];
            }

            $info[$k]['created_at'] = substr($info[$k]['created_at'],0,10);
            $info[$k]['buyer_name'] = $info[$k]['name'];
            $orderInfo=$order->statisOrder($v['id']);
            $info[$k]['mem_cate'] = $orderInfo['mem_cate'];
            unset($info[$k]['created_by']);
            unset($info[$k]['name']);
        }
        if(empty($info)){
            $info=[];
        }
        $arr['currentPage'] = $currentPage;
        $arr['totalPage'] = $totalPage;
        $arr['totalCount'] = $totalCount;
        $arr['info'] = $info;
        return $arr;
    }

    /**
     * 专用采购商客户基本创建 ----数据验证
     * wangs
     */
    public function validBuyerBaseData($arr){
        $base = $arr['base_info'];  //基本信息
        $contact = $arr['contact']; //联系人
        $baseArr = array(   //创建客户基本信息必须数据
//            'buyer_id'=>'客户id',
            'buyer_name'=>L('buyer_name'),
//            'buyer_account'=>'客户账号',
//            'buyer_code'=>'客户CRM编码',
//            'buyer_level'=>'客户级别',
//            'country_bn'=>'国家',
//            'area_bn'=>'地区',
//            'market_agent_name'=>'erui客户服务经理（市场经办人)',
//            'market_agent_mobile'=>'服务经理联系方式',
//            'level_at'=>'定级日期',
//            'expiry_at'=>'有效期',
            'is_oilgas'=>L('is_oilgas'),    //是否油气
            'company_model'=>L('company_model'),    //公司性质
            'official_phone'=>L('official_phone'),  //公司电话
            'official_email'=>L('official_email'),     //公司邮箱
            'official_website'=>L('official_website'),  //公司网址
            'company_reg_date'=>L('company_reg_date'),  //公司成立日期
            'company_address'=>L('company_address'),//  +公司地址
            'reg_capital'=>L('reg_capital'),    //注册资金
            'reg_capital_cur'=>L('reg_capital_cur'),    //注册资金货币
            'profile'=>L('profile'),    //公司其他信息
//            'is_oilgas'=>'是否油气',
//            'company_model'=>'公司性质',
//            'official_phone'=>'公司电话',
//            'official_email'=>'公司邮箱',
//            'official_website'=>'公司网址',
//            'company_reg_date'=>'公司成立日期',
//            'company_address'=>'公司地址',  //  +
//            'reg_capital'=>'注册资金',
//            'reg_capital_cur'=>'注册资金货币',
//            'profile'=>'公司其他信息',

        );
        foreach($baseArr as $k => $v){
            if(empty($base[$k])){
                return $v.L('not empty');
            }
        }
        if(!empty($base['company_reg_date'])){
            $date=explode('-',$base['company_reg_date']);
            $y=$date[0];
            $m=sprintf("%02s",intval($date[1]));
            $d=sprintf("%02s",intval($date[2]));
            if($m<0 || $m>12){
                return $baseArr['company_reg_date'].L('format_error');    //的月份错误
            }
            if($m=='00'){
                if($d != '00'){
                    return $baseArr['company_reg_date'].L('format_error');  //的月份日期错误
                }
            }else{
                if(in_array($m,['04','06','09','11'])){
                    if($d <0 || $d >30){
                        return $baseArr['company_reg_date'].L('format_error');    //的日期错误
                    }
                }
                if(in_array($m,['01','03','05','07','08','10','12'])){
                    if($d <0 || $d >31){
                        return $baseArr['company_reg_date'].L('format_error');    //的日期错误
                    }
                }
                if($m == '02'){
                    if($d <0 || $d >28){
                        return $baseArr['company_reg_date'].L('format_error');    //的日期错误
                    }
                }
            }
            $dateArr=[$y,$m,$d];
            $base['company_reg_date']=implode('-',$dateArr);
        }
//        if(!empty($base['official_phone'])){
//            if(!preg_match ("/^(\d{2,4}-)?\d{6,11}$/",$base['official_phone'])){
//                return '公司电话:(选)2~4位区号-6~11位电话号码';
//            }
//        }
        if(!preg_match ("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/",$base['official_email'])){
            return $baseArr['official_email'].L('format_error');
        }else{
            $email=$this->field('official_email')->where(array('id'=>$base['buyer_id']))->find();//默认邮箱
            if($base['official_email']!=$email['official_email']){  //修改邮箱
                $exist=$this->field('official_email')->where(array('official_email'=>trim($base['official_email'],' ')))->find();
                if($exist){
                    return $baseArr['official_email'].L('already existed');
                }
            }
        }
        if(is_numeric($base['reg_capital'])  && $base['reg_capital']>0){
        }else{
            return $baseArr['reg_capital'].L('format_error');
        }

        //基本信息可选数据
        $baseExtra = array( //创建客户基本信息可选数据
            'type_id'=>'客户类型',   //buyer_type
            'type_remarks'=>'类型备注',
            'is_oilgas'=>'是否油气',
            'employee_count'=>L('employee_count')
        );
        //联系人【contact】
//        $contactArr = array(    //创建客户信息联系人必须数据
//            'name'=>L('contact_name'),  //联系人姓名
//            'title'=>L('contact_title'),    //联系人职位
//            'phone'=>L('contact_phone'),    //联系人电话
//        );
//        $contactExtra = array(  //创建客户信息联系人可选数据
//            'role'=>'购买角色',
//            'email'=>L('contact_email'),    //联系人邮箱
//            'hobby'=>'喜好',
//            'address'=>'详细地址',
//            'experience'=>'工作经历',
//            'social_relations'=>'社会关系',
//
//            'key_concern'=>'决策主要关注点',
//            'attitude'=>'对科瑞的态度',
//            'social_place'=>'常去社交场所',
//            'relatives_family'=>'家庭亲戚相关信息',
//        );
//        $contactEmail=array();  //crm
//        foreach($contact as $value){
//            foreach($contactArr as $k => $v){
//                if(empty($value[$k]) || strlen($value[$k]) > 50){
//                    return $v.L('not empty');
//                }
////                if(!empty($value['phone'])){
////                    if(!preg_match ("/^(\d{2,4}-)?\d{6,11}$/",$value['phone'])){
////                        return '联系人电话:(选)2~4位区号-6~11位电话号码';
////                    }
////                }
//            }
//            if(!empty($value['email'])){
//                $value['email']=trim($value['email'],' ');
//                if(!preg_match ("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/",$value['email'])){
//                    return $contactExtra['email'].L('format_error');
//                }else{
//                    $buyerContact=new BuyercontactModel();
//                    if(empty($value['id'])){
//                        $email=$buyerContact->field('email')->where(array('email'=>$value['email'],'deleted_flag'=>'N'))->find();
//                        if($email){
//                            return $contactExtra['email'].L('already existed');
//                        }
//                    }else{
//                        $email=$buyerContact->field('email')->where(array('id'=>$value['id']))->find();//默认邮箱
//                        if($value['email']!=$email['email']){  //修改邮箱
//                            $exist=$buyerContact->field('email')->where(array('email'=>$value['email'],'deleted_flag'=>'N'))->find();
//                            if($exist){
//                                return $contactExtra['email'].L('already existed');
//                            }
//                        }
//                    }
//
//                }
//                $contactEmail[]=$value['email'];
//            }
//            $emailTotal=count($contactEmail);   //联系人邮箱总数
//            $validTotal=count(array_flip(array_flip($contactEmail)));   //联系人邮箱过滤重复后总数
//            if($emailTotal!=$validTotal){
//                return $contactExtra['email'].L('repeat');
//            }
//        }
        if(!empty($base['employee_count'])){
            if(is_numeric($base['employee_count']) && $base['employee_count'] > 0){
                return true;
            }else{
                return $baseExtra['employee_count'];
            }
        }
        return true;
    }
    //联系人
//    public function editContact($data){
//        //编辑联系人必填
//        $attach = new BuyercontactModel();
//        $attach -> updateBuyerContact($data['contact'],$data['buyer_id'],$data['created_by']);
//        return true;
//    }

    /**
     * 采购商客户管理，基本信息的创建
     * wangs
     */
    public function createBuyerBaseInfo($data){
        //验证数据
        $info = $this->validBuyerBaseData($data);
        if($info !== true){
            return $info;
        }
        $arr = $this -> packageBaseData($data['base_info'],$data['created_by']);    //组装基本信息数据
        $this->where(array('id'=>$arr['id']))->save($arr);  //创建或修改客户档案信息

        //编辑财务报表
        $attach = new BuyerattachModel();
        $attach -> updateBuyerFinanceTableArr($data['base_info']['finance_attach'],'FINANCE',$data['base_info']['buyer_id'],$data['created_by']);
        //公司人员组织架构
        $attach -> updateBuyerFinanceTableArr($data['base_info']['org_chart'],'ORGCHART',$data['base_info']['buyer_id'],$data['created_by']);
        //编辑联系人必填
//        $attach = new BuyercontactModel();
//        $attach -> updateBuyerContact($data['contact'],$data['base_info']['buyer_id'],$data['created_by']);
        return true;
    }
    /**
     * 组装客户基本信息创建所需数据
     * wangs
     */
    public function packageBaseData($data, $created_by) {
        //会员有效期12个月--------------1年
//        if (!empty($data['level_at'])) {
//            $level_at = $data['level_at'];
//            $year_at = substr($level_at, 0, 4);
//            $year_end = substr($level_at, 0, 4) + 1;
//            $expiry_at = str_replace($year_at, $year_end, $level_at);
//        }else{
//            $level_at=null;
//            $expiry_at=null;
//        }
        //必须数据
        $arr = array(
            'created_by'    => $created_by, //客户id
//            'created_at'    => date('Y-m-d H:i:s'), //客户id
            'build_time'    => date('Y-m-d H:i:s'), //客户档案信息创建时间---
            'build_modify_time'    => date('Y-m-d H:i:s'), //客户档案信息创建时间---
            'id'    => $data['buyer_id'], //客户id
            'name'  => $data['buyer_name'], //客户名称
            'official_phone'    => trim($data['official_phone'],' '),    //公司固话
            'official_email'    => trim($data['official_email'],' '),    //公司邮箱
            'official_website'  => trim($data['official_website'],' '),  //公司网址
            'company_reg_date'  => $data['company_reg_date'],  //成立日期
            'company_address'  => $data['company_address'],  //公司地址+
            'reg_capital'   => $data['reg_capital'],   //注册资金
            'reg_capital_cur'   => $data['reg_capital_cur'],   //注册资金货币
            'profile'   => $data['profile'],   //公司介绍txt
//            'level_at' => $level_at,  //定级日期
//            'expiry_at' =>  $expiry_at, //有效期
            'is_build' =>'1',//建立档案标识
//            'status' =>'PASS',//建立档案信息状态标识
            'is_oilgas' =>$data['is_oilgas'],   //是否油气
            'company_model' =>$data['company_model'],    //公司性质

            'dollar_rate' =>$data['dollar_rate'],    //相对美元汇率
            'dollar_amount' =>$data['dollar_amount']    //美元金额
        );
        //判断创建数据与编辑数据
        $build = $this->field('is_build,build_time')->where(array('id'=>$data['buyer_id']))->find();
        if($build['is_build'] == 1){
//            $arr['build_modify_time'] = date('Y-m-d H:i:s'); //客户档案信息修改时间---
            if($build['build_time'] !== NULL){
                unset($arr['build_time']);
            }
        }
        //非必须数据
        $baseArr = array(
            'buyer_type', //客户类型buyer_type
            'type_remarks', //客户类型备注
//            'is_oilgas', //是否油气
            'employee_count', //雇员数量
            'sub_company_name', //子公司名称
        );
        foreach ($data as $value) {
            foreach ($baseArr as $v) {
//                if (!empty($data[$v])) {
                    $arr[$v] = $data[$v];
//                }
            }
        }
        return $arr;
    }

    /**
     * 展示客户管理客户基本信息详情
     * wangs
     */
    public function showBuyerBaseInfo($data){
        $lang=isset($data['lang'])?$data['lang']:'zh';
        $cond = [];
        if(!empty($data['buyer_id'])){
            $cond['id'] = $data['buyer_id'];
        }
        $buyerArr = array(
            'id as buyer_id', //客户id
            'percent', //客户id
            'buyer_type', //客户类型
            'type_remarks', //客户类型备注
            'is_oilgas', //是否油气
            'buyer_no', //客户编码
            'buyer_code', //客户crm编码
            'name as buyer_name', //客户名称
            'profile', //公司介绍
            'employee_count', //雇员数量
            'company_reg_date', //公司注册日期
            'reg_capital', //注册资金
            'reg_capital_cur', //注册资金货币
            'dollar_rate', //注册资本相对美元汇率
            'dollar_amount', //注册资本美元金额
            'area_bn', //地区
            'country_bn', //国家
            'company_address', //公司地址
            'company_model', //公司性质
            'sub_company_name', //子公司名称
            'official_email', //公司邮箱
            'official_phone', //公司电话
            'official_website', //公司官网
            'buyer_level', //客户等级
            'level_at', //定级日期
            'expiry_at', //有效日期
            'created_by', //客户id
            'created_at', //客户id
            'deleted_flag', //客户id
        );
        $field = '';
        foreach ($buyerArr as $v) {
            $field .= ',' . $v;
        }
        $field = substr($field,1);
        $info = $this->field($field)
            ->where($cond)
            ->find();
        if(!empty($info['buyer_level'])){
            $level = new BuyerLevelModel();
            $info['buyer_level'] = $level->getBuyerLevelById($info['buyer_level'],$lang);
        }


//        if($data['is_check'] == true){
            if(!empty($info['buyer_type'])){
                $type = new BuyerTypeModel();
                $buyerType=$type->buyerTypeNameById($info['buyer_type'],$lang);
                $info['buyer_type'] = $buyerType['type_name'];
            }
//        }
        if(!empty($info['country_bn'])){
            $country = new CountryModel();
            $info['country_name'] = $country->getCountryByBn($info['country_bn'],$lang);
        }

        return $info;
    }
    public function showBuyerInfo($data){
        if(empty($data['buyer_id'])){
            return false;
        }
//        $access=$this->accessCountry($data);
//        $cond=$access;
        $lang=isset($data['lang'])?$data['lang']:'zh';
        $buyerArr = array(
            'id as buyer_id', //客户id
            'percent', //信息完整度
            'buyer_no', //客户编码
            'buyer_code', //客户crm编码
            'name as buyer_name', //客户名称
            'area_bn', //地区
            'country_bn', //国家

            'buyer_level', //客户等级
            'level_at', //定级日期
            'expiry_at', //有效日期
            'buyer_type', //客户类型
            'type_remarks', //客户类型备注
            'is_oilgas', //是否油气

            'company_reg_date', //公司注册日期
            'official_email', //公司邮箱
            'official_phone', //公司电话
            'official_website', //公司官网
            'reg_capital', //注册资金
            'reg_capital_cur', //注册资金货币
            'dollar_rate', //注册资本相对美元汇率
            'dollar_amount', //注册资本美元金额
            'sub_company_name', //子公司名称
            'employee_count', //雇员数量
            'company_model', //公司性质
            'profile', //公司介绍
            'company_address' //公司地址
        );
//        $cond.=" and id=$data[buyer_id] and status='APPROVED' and deleted_flag='N'";
        $cond=array(
            'id'=>$data['buyer_id'],
//            'status'=>'APPROVED',
            'deleted_flag'=>'N'
        );
        $reg=$this->field('id')->where($cond)->find();
        if(empty($reg)){
            return false;
        }
//        if($data['type']!='check'){
//            $config = \Yaf_Application::app()->getConfig();
//            $myhost=$config['myhost'];
////        print_r($myhost);die;
//            //percentInfo
//            $cookie=$_COOKIE;
//            $opt = array(
//                'http'=>array(
//                    'method'=>"POST",
//                    'header'=>"Cookie:_ga=$cookie[_ga];eruitoken=$cookie[eruitoken];Content-Type=application/json",
//                    'content' =>json_encode(array('buyer_id'=>$data['buyer_id']))
//                )
//            );
//            $context = stream_context_create($opt);
//            $url = $myhost.'v2/Buyerfiles/percentInfo';
////        $url = 'http://api.eruidev.com/v2/Buyerfiles/percentInfo';
//            $json = file_get_contents($url,false,$context);
//            $result=$data = json_decode($json, true);
//            if($result['code']!=1){
//                return 'info';
//            }
//        }
        $info = $this->field($buyerArr)
                    ->where($cond)
                    ->find();
        if(!empty($info['buyer_level'])){
            $level = new BuyerLevelModel();
            $info['buyer_level'] = $level->getBuyerLevelById($info['buyer_level'],$lang);
        }
        //        if($data['is_check'] == true){
        //        if(!empty($info['buyer_type'])){
        //            $type = new BuyerTypeModel();
        //            $buyerType=$type->buyerTypeNameById($info['buyer_type'],$lang);
        //            $info['buyer_type'] = $buyerType['type_name'];
        //        }
        //        }
        if(!empty($info['country_bn'])){
            $country = new CountryModel();
            $info['country_name'] = $country->getCountryByBn($info['country_bn'],$lang);
        }
        $info['reg_capital'] = floatval($info['reg_capital']);
        return $info;
}

/**
* 客户管理-客户信息的统计数据
* wangs
*/
    public function showBuyerStatis($data){
        $lang=isset($data['lang'])?$data['lang']:'zh';
        if(empty($data['buyer_id']) || empty($data['created_by'])){
            return false;
        }
        $cond = array(
            'id'=>$data['buyer_id'],
            'created_by'=>$data['created_by'],
            'deleted_flag'=>'N'
        );
        $info = $this->field('credit_level,credit_type,line_of_credit,credit_available,payment_behind,behind_time,reputation,violate_treaty,treaty_content,comments')
            ->where($cond)
            ->find();
        if(empty($info)){
            $info['credit_level'] = "";
            $info['credit_type'] = "";
            $info['payment_behind'] = "";
            $info['behind_time'] = "";
            $info['reputation'] = "";
            $info['violate_treaty'] = "";
            $info['treaty_content'] = "";
            $info['comments'] = "";

            $info['line_of_credit'] = 0;
            $info['credit_available'] = 0;
        }
        if($data['is_check']==true){
            if(!empty($info['credit_type'])){
                $level=new CreditModel();
                $levelName=$level->getCreditTpeNameById($info['credit_type'],$lang);
                $info['credit_type']=$levelName['type_name'];
            }
            if(!empty($info['credit_level'])){
                $level=new CreditModel();
                $levelName=$level->getCreditLevelNameById($info['credit_level'],$lang);
                $info['credit_level']=$levelName['type_name'];
            }
            if(!empty($info['payment_behind'])){    //是否拖欠过货款
                if($lang=='zh'){
                    $info['payment_behind']= $info['payment_behind']=="Y"?'是':'否';
                }else{
                    $info['payment_behind']= $info['payment_behind']=="Y"?'YES':'NO';
                }
            }
            if(!empty($info['violate_treaty'])){    //是否有针对KERUI/ERUI的违约
                if($lang=='zh'){
                    $info['violate_treaty']=$info['violate_treaty']=="Y"?'是':'否';
                }else{
                    $info['violate_treaty']=$info['violate_treaty']=="Y"?'YES':'NO';
                }
            }
        }
        return $info;
    }
    public function getBuyerBakInfo($data){
        $lang=$data['lang'];
        $need=$this->table('erui_buyer.buyer_bak')->where(array('lang'=>$lang))->select()->group('buyer_id');
        foreach($need as $k => &$v){
            unset($v['id']);
            unset($v['lang']);
        }
        $excelName = 'buyerlist';
        $excel = $this->exportModel($excelName,$need,$lang);  //导入excel
        $excelArr[] = $excel;
        return $excelArr;
    }
    /**
     * @param $data
     * 客户管理列表excel导出
     */
    public function exportBuyerExcel($data){
        //获取数据,上传本地
        if(in_array('CRM客户管理',$data['admin']['role'])){
            if(empty($data['created_name'])&&empty($data['percent_max'])&&empty($data['percent_min'])&&empty($data['area_country'])&&empty($data['agent_name'])&&empty($data['country_search'])&&empty($data['buyer_code'])&&empty($data['buyer_no'])&&empty($data['created_time'])&&empty($data['source'])&&empty($data['name'])&&empty($data['buyer_level'])){
                $excelArr = $this->getBuyerBakInfo($data);
            }else{
                $excelArr = $this->getBuyerManageDataByCond($data,0,10,true);
            }
        }else{
            $excelArr = $this->getBuyerManageDataByCond($data,0,10,true);
        }
        if(!is_array($excelArr)){
            return false;
        }
        if(count($excelArr)==1){    //单文件
            $excelName = $excelArr[0];
            $arr['tmp_name'] = $excelName;
            $arr['type'] = 'application/excel';
            $arr['name'] = pathinfo($excelName, PATHINFO_BASENAME);
        }else{
            $excelDir = dirname($excelArr[0]);  //获取目录,多个excel文件,压缩打包
            ZipHelper::zipDir($excelDir, $excelDir . '.zip');   //压缩文件
            $arr['tmp_name'] = $excelDir . '.zip';
            $arr['type'] = 'application/excel';
            $arr['name'] = pathinfo($excelDir . '.zip', PATHINFO_BASENAME);
        }
    //把导出的文件上传到文件服务器指定目录位置
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server . '/V2/Uploadfile/upload';
        $fileId = postfile($arr, $url);    //上传到fastDFSUrl访问地址,返回name和url
        //删除文件和目录
        if(file_exists($excelName)){
            unlink($excelName); //删除文件
            ZipHelper::removeDir(dirname($excelName));    //清除目录
        }
        if(file_exists($excelDir . '.zip')){
            unlink($excelDir . '.zip'); //删除压缩包
            ZipHelper::removeDir($excelDir);    //清除目录
        }
        if ($fileId) {

//            return array('url' => $fastDFSServer . $fileId['url'] . '?filename=' . $fileId['name'], 'name' => $fileId['name']);
            return $fileId;
        }
    }

    /**
     * 打包，客户管理数据列表
     * wangs
     */
    public function packageBuyerExcelData($data,$lang='zh'){
        $arr = [];
        foreach($data as $k => $v){
            $arr[$k]['buyer_id'] = $v['id'];  //客户id
            if(!empty($v['percent'])){
                $arr[$k]['percent'] = $v['percent'].'%';    //信息完整度百分比
            }else{
                $arr[$k]['percent'] = '--';    //信息完整度百分比
            }
            $arr[$k]['country_name'] = $v['country_name'];  //国家
            $arr[$k]['buyer_code'] = $v['buyer_code'];  //客户编码
            $arr[$k]['buyer_name'] = $v['buyer_name'];  //客户名称
            $arr[$k]['created_at'] = $v['build_time'];  //客户档案创建时间
            $arr[$k]['created_name'] = $v['created_name'];  //客户档案创建时间
            if($lang=='zh'){
                $arr[$k]['is_oilgas'] = $v['is_oilgas']=='Y'?'油气':'非油气';    //是否油气
            }else{
                $arr[$k]['is_oilgas'] = $v['is_oilgas']=='Y'?'oil gas':'Non oil gas';    //是否油气
            }

            $arr[$k]['buyer_level'] = $v['buyer_level'];    //客户等级
            if(empty($v['buyer_level']) && $lang=='zh'){
                $arr[$k]['buyer_level']='注册客户';
            }elseif(empty($v['buyer_level']) && $lang=='en'){
                $arr[$k]['buyer_level']='Registered customer';
            }

            $arr[$k]['mem_cate'] = $v['mem_cate'];  // 客户订单分类

            $arr[$k]['level_at'] = $v['level_at'];  //等级设置时间
            $arr[$k]['reg_capital'] = $v['reg_capital'];    //注册资金
            $arr[$k]['reg_capital_cur'] = $v['reg_capital_cur'];    //货币
            if($lang=='zh'){
                $arr[$k]['is_net'] = $v['is_net']==='Y'?'是':'否';  //是否入网
            }else{
                $arr[$k]['is_net'] = $v['is_net']==='Y'?'YES':'NO';  //是否入网
            }
            $arr[$k]['net_at'] = $v['net_at'];  //入网时间
            $arr[$k]['net_invalid_at'] = $v['net_invalid_at'];  //失效时间
            $arr[$k]['product_type'] = $v['product_type'];  //产品类型
            $arr[$k]['credit_level'] = $v['credit_level'];  //采购商信用等级
            $arr[$k]['credit_type'] = $v['credit_type'];    //授信类型
            $arr[$k]['line_of_credit'] = $v['line_of_credit'];  //授信额度
            if($lang=='zh'){
                $arr[$k]['is_local_settlement'] = $v['is_local_settlement']==='Y'?'是':'否';    //本地结算
            }else{
                $arr[$k]['is_local_settlement'] = $v['is_local_settlement']==='Y'?'YES':'NO';    //本地结算
            }
            if($lang=='zh'){
                $arr[$k]['is_purchasing_relationship'] = $v['is_purchasing_relationship']==='Y'?'是':'否';  //采购关系
            }else{
                $arr[$k]['is_purchasing_relationship'] = $v['is_purchasing_relationship']==='Y'?'YES':'NO';  //采购关系
            }
            $arr[$k]['market_agent'] = $v['market_agent'];  //kerui/erui客户服务经理
            $arr[$k]['total_visit'] = $v['total_visit'];    //总访问次数
//            $arr[$k]['quarter_visit'] = $v['quarter_visit'];    //季度访问次数
//            $arr[$k]['month_visit'] = $v['month_visit'];    //月访问次数
//            $arr[$k]['week_visit'] = $v['week_visit'];      //周访问次数
            $arr[$k]['inquiry_count'] = $v['inquiry_count'];    //询价数量
            $arr[$k]['quote_count'] = $v['quote_count'];    //报价金额
            $arr[$k]['inquiry_account'] = $v['inquiry_account'];    //询报价金额
            $arr[$k]['order_count'] = $v['order_count'];    //订单数量
            $arr[$k]['order_account'] = $v['order_account'];    //订单金额
            if($v['min_range']==0 && $v['max_range']==0){
                $arr[$k]['min-max_range'] = '-';    //单笔金额偏重区间
            }else{
                $arr[$k]['min-max_range'] = $v['min_range'].'-'.$v['max_range'];    //单笔金额偏重区间
            }
        }
        return $arr;
    }

    /**
     * @param $arr
     * 获取客户管理所有数据
     * wangs
     */
    public function exportBuyerListDataFull($arr){
        $info = $arr['info'];
        //客户服务经理
        $agentModel = new BuyerAgentModel();
        $agentRes = $agentModel->getMarketAgent($arr['ids']);
        foreach($info as $key => $value){
            foreach($agentRes as $k => $v){
                if($value['id']==$k){
                    $info[$key]['market_agent']=$v;
                }
            }
        }
        //访问
        $visitModel = new BuyerVisitModel();
        $visitRes = $visitModel->getVisitCount($arr['ids']);
        foreach($info as $key => $value){
            foreach($visitRes as $k => $v){
                if($value['id']==$k){
                    $info[$key]['total_visit']=$v['totalVisit'];
                    $info[$key]['week_visit']=$v['week'];
                    $info[$key]['month_visit']=$v['month'];
                    $info[$key]['quarter_visit']=$v['quarter'];
                }
            }
        }
        //询报价
        $inquiryModel = new InquiryModel();
        $inquiryRes = $inquiryModel->getInquiryStatis($arr['ids']);
        foreach($info as $key => $value){
            foreach($inquiryRes as $k => $v){
                if($value['id']==$k){
                    $info[$key]['inquiry_count']=$v['inquiry_count'];
                    $info[$key]['quote_count']=$v['quote_count'];
                    $info[$key]['inquiry_account']=$v['account'];
                }
            }
        }
        //订单
        $orderModel = new OrderModel();
        $orderRes = $orderModel->getOrderStatis($arr['ids']);
        foreach($info as $key => $value){
            foreach($orderRes as $k => $v){
                if($value['id']==$k){
                    $info[$key]['order_count']=$v['count'];
                    $info[$key]['order_account']=$v['account'];
                    $info[$key]['min_range']=$v['min'];
                    $info[$key]['max_range']=$v['max'];
                    $info[$key]['mem_cate']=$v['mem_cate'];
                }
            }
        }
        return $info;
    }

    /**
     * 客户档案管理的条件
     * wangs
     */
    public function getBuyerManageCond($data){
        //条件
        $cond=" 1=1 and buyer.is_build=1 and buyer.status='APPROVED' and buyer.deleted_flag='N'";

        if(empty($data['admin']['role'])){
            return false;
        }
        if(!in_array('CRM客户管理',$data['admin']['role']) && !in_array('客户管理员',$data['admin']['role'])){    //权限
            if(!in_array('201711242',$data['admin']['role']) && !in_array('A001',$data['admin']['role'])){  //不是国家负责人也不是经办人
                return false;
            }elseif(in_array('201711242',$data['admin']['role'])  && !in_array('A001',$data['admin']['role'])){   //国家负责人,不是经办人
                $cond .= ' And  `buyer`.country_bn in ('.$data['admin']['country'].')';
            }elseif(!in_array('201711242',$data['admin']['role'])  && in_array('A001',$data['admin']['role'])){   //不是国家负责人,是经办人
                $agent=new BuyerAgentModel();
                $list=$agent->field('buyer_id')->where(array('agent_id'=>$data['created_by'],'deleted_flag'=>'N'))->select();
                $created=new BuyerModel();
                $createdArr=$created->field('id as buyer_id')->where(array('created_by'=>$data['created_by'],'deleted_flag'=>'N'))->select();
                $totalList=$this->validAgent($createdArr,$list);
                $str='';
                foreach($totalList as $k => $v){
                    $str.=','.$v['buyer_id'];
                }
                $str=substr($str,1);
                if(!empty($str)){
                    $cond.= " and buyer.id in ($str) ";
                }else{
                    $cond.= " and buyer.id in ('wangs') ";
                }
            }else{  //即使国家负责人,也是市场经办人
                $cond .= ' And ( `buyer`.country_bn in ('.$data['admin']['country'].')';
                $agent=new BuyerAgentModel();
                $list=$agent->field('buyer_id')->where(array('agent_id'=>$data['created_by'],'deleted_flag'=>'N'))->select();
                $created=new BuyerModel();
                $createdArr=$created->field('id as buyer_id')->where(array('created_by'=>$data['created_by'],'deleted_flag'=>'N'))->select();
                $totalList=$this->validAgent($createdArr,$list);
                $str='';
                foreach($totalList as $k => $v){
                    $str.=','.$v['buyer_id'];
                }
                $str=substr($str,1);
                if(!empty($str)){
                    $cond.= " or buyer.id in ($str) )";
                }else{
                    $cond.= " or buyer.id in ('wangs') )";
                }
            }
        }else{
            $cond=" 1=1 and buyer.is_build=1 and buyer.status='APPROVED' and buyer.deleted_flag='N'";
        }
//        if(in_array('客户管理员',$data['admin']['role'])){
//            $cond=" 1=1 and buyer.is_build=1 and buyer.status='APPROVED' and buyer.deleted_flag='N'";
//        }
        foreach($data as $k => $v){
            $data[$k]=trim($v,' ');
        }
        if(!empty($data['country_search'])){    //国家搜索
            $cond .= " and buyer.country_bn='$data[country_search]'";
        }
        if(!empty($data['created_name'])){  //创建人名称
            $cond .= " and employee.name like '%".$data['created_name']."%'";
        }
//        if(!empty($data['all_id'])){
//            $str = implode(',',$data['all_id']);
//            $cond .= " and buyer.id in ($str)";
//        }
//        if(!empty($data['buyer_level'])){
//            if($data['buyer_level']=='普通会员' || $data['buyer_level']=='Member'){
//                $cond .= " and buyer.buyer_level=52";
//            }elseif($data['buyer_level']=='高级会员' || $data['buyer_level']=='Senior member'){
//                $cond .= " and buyer.buyer_level=53";
//            }elseif($data['buyer_level']=='注册会员' || $data['buyer_level']=='Registered member'){
//                $cond .= " and buyer.buyer_level is null";
//            }else{
//                $cond .= " and buyer.buyer_level='wangs'";
//            }
////            $cond .= " and buyer.buyer_level='$data[buyer_level]'";
//        }
        if(!empty($data['buyer_level'])){
            if($data['buyer_level']=='52'){
                $cond .= " and buyer.buyer_level=52";
            }elseif($data['buyer_level']=='53'){
                $cond .= " and buyer.buyer_level=53";
            }else{
                $cond .= " and buyer.buyer_level is null";
            }
//            $cond .= " and buyer.buyer_level='$data[buyer_level]'";
        }
        if(!empty($data['buyer_no'])){
            $cond .= " and buyer.buyer_no like '%$data[buyer_no]%'";
        }
        if(!empty($data['buyer_code'])){
            $cond .= " and buyer.buyer_code like '%$data[buyer_code]%'";
        }
        if(!empty($data['name'])){
            $cond .= " and buyer.name like '%$data[name]%'";
        }
        if(!empty($data['reg_capital'])){
            $cond .= " and buyer.reg_capital like '%$data[reg_capital]%'";
        }
        if(!empty($data['min_percent'])){    //信息完整度min
            $cond .= " and buyer.percent >=".$data['min_percent'];
        }
        if(!empty($data['max_percent'])){    //信息完整度max
            $cond .= " and buyer.percent <=".$data['max_percent'];
        }

        if(!empty($data['build_time_start'])){    //档案信息创建时间
            $cond .= " and buyer.build_time >='".$data['build_time_start']."'";
        }
        if(!empty($data['build_time_end'])){    //档案信息创建时间
            $cond .= " and buyer.build_time <='".$data['build_time_end']."'";
        }
        return $cond;
    }
    /**
     * 客户档案管理根据条件获取数据
     * wangs
     * @param $cond
     * @param $offset
     * @param $pageSize
     */
    public function getBuyerManageDataByCond($data,$i=0,$pageSize,$excel=false){
        $lang=isset($data['lang'])?$data['lang']:'zh';
        $cond = $this->getBuyerStatisListCond($data);
//        $cond .= " and buyer.status='APPROVED' ";
//        $cond = $this->getBuyerManageCond($data);
        if($cond==false){
            return false;
        }
        $totalCount = $this->alias('buyer')
//            ->join('erui_buyer.buyer_business business on buyer.id=business.buyer_id','left')
//            ->join('erui_sys.employee employee on buyer.created_by=employee.id','left')
            ->where($cond)
            ->count();

        if($totalCount <= 0){
            return false;   //空数据
        }
        if($excel==true){
            $pageSize=1000;
        }
        //开始----------------------------------------------------------------------------
        do{
            $field ='buyer.id'; //获取查询字段
            $fieldBuyerArr = array(
//            'id',   //客户id
//                'area_bn',   //客地区
                'percent',   //信息完整度百分比
                'country_bn',   //国家
                'buyer_code',   //客户编码
                'name as buyer_name',   //客户名称
                'created_at',   //创建时间
                'build_time',   //客户档案创建时间
                'is_oilgas',   //是否油气
                'buyer_level',   //客户等级
                'level_at',   //等级设置时间
                'reg_capital',   //注册资金
                'reg_capital_cur',   //货币
//                'credit_level',   //采购商信用等级   X
//                'credit_type',   //授信类型   X
//                'line_of_credit',   //授信额度    X
            );
            foreach($fieldBuyerArr as $v){
                $field .= ',buyer.'.$v;
            }
            $fieldBusiness = array(
//                'is_net', //是否入网
//                'net_at', //入网时间
//                'net_invalid_at', //失效时间
                'product_type', //产品类型
                'is_local_settlement', //本地结算
                'is_purchasing_relationship', //采购关系
            );
            foreach($fieldBusiness as $v){
                $field .= ',business.'.$v;
            }
            $field .= ',(select name from erui_sys.employee where id=buyer.created_by and deleted_flag=\'N\') as created_name';
            $field .= ',credit.credit_level'; //采购商信用等级
            $field .= ',credit.credit_type';  //授信类型
            $field .= ',credit.line_of_credit';   //授信额度
            $info = $this->alias('buyer')
                ->join('erui_buyer.buyer_business business on buyer.id=business.buyer_id','left')
                ->join('erui_buyer.customer_credit credit on buyer.id=credit.buyer_id and credit.deleted_flag=\'N\'','left')
//                ->join('erui_sys.employee employee on buyer.created_by=employee.id and employee.deleted_flag=\'N\'','left')
                ->field($field)
                ->where($cond)
                ->order('buyer.build_modify_time desc')
                ->limit($i,$pageSize)
                ->select();
            if(empty($info)){
               return false;
            }
            $country = new CountryModel();
            $level = new BuyerLevelModel();
            $credit = new CreditModel();
            $net=new NetSubjectModel();
            $agentModel = new BuyerAgentModel();
            $visitModel = new BuyerVisitModel();
            $inquiryModel = new InquiryModel();
            $orderModel = new OrderModel();
            foreach($info as $k => $v){
                $info[$k]['country_name'] = $country->getCountryByBn($v['country_bn'],$lang);
                if(!empty($info[$k]['buyer_level']) && is_numeric($info[$k]['buyer_level'])){
                    $info[$k]['buyer_level'] = $level->getBuyerLevelById($v['buyer_level'],$lang);
                }
//                    if(empty($v['build_time'])){
//                        $info[$k]['build_time']=$v['created_at'];
//                    }
//                    if(empty($v['level_at'])){
//                        $info[$k]['level_at']=substr($v['created_at'],0,10);
//                    }
                if(!empty($info[$k]['credit_level']) && is_numeric($info[$k]['credit_level'])){
                    $info[$k]['credit_level'] = $credit->getCreditNameById($v['credit_level'],$lang);
                }
                if(!empty($info[$k]['credit_type']) && is_numeric($info[$k]['credit_type'])){
                    $info[$k]['credit_type'] = $credit->getCreditNameById($v['credit_type'],$lang);
                }
                //获取入网管理
                $netInfo=$net->showNetSubject(array('buyer_id'=>$v['id']));
                if(empty($netInfo)){    //arr
                    $info[$k]['is_net']='N'; //是否入网
                    $info[$k]['net_at']=''; //入网时间
                    $info[$k]['net_invalid_at']=''; //失效时间
                }elseif(!empty($netInfo['erui']['net_at'])){
                    $info[$k]['is_net']='Y'; //是否入网
                    $info[$k]['net_at']=$netInfo['erui']['net_at']; //入网时间
                    $info[$k]['net_invalid_at']=$netInfo['erui']['net_invalid_at']; //失效时间
                }else{
                    $info[$k]['is_net']='Y'; //是否入网
                    $info[$k]['net_at']=$netInfo['equipment']['net_at']; //入网时间
                    $info[$k]['net_invalid_at']=$netInfo['equipment']['net_invalid_at']; //失效时间
                }

                //
                //客户服务经理
                $agentRes = $agentModel->getBuyerAgentFind($v['id']);
                $info[$k]['market_agent']=$agentRes;
                //访问
                $visitRes = $visitModel->singleVisitInfo($v['id']);
                $info[$k]['total_visit']=$visitRes['totalVisit'];
                $info[$k]['week_visit']=$visitRes['week'];
                $info[$k]['month_visit']=$visitRes['month'];
                $info[$k]['quarter_visit']=$visitRes['quarter'];
                //询报价
                $inquiryRes = $inquiryModel->statisInquiry($v['id']);
                $info[$k]['inquiry_count']=$inquiryRes['inquiry_count'];
                $info[$k]['quote_count']=$inquiryRes['quote_count'];
                $info[$k]['inquiry_account']=$inquiryRes['account'];
                //订单
                $orderRes = $orderModel->statisOrder($v['id']);
                $info[$k]['order_count']=$orderRes['count'];
                $info[$k]['order_account']=$orderRes['account'];
                $info[$k]['min_range']=$orderRes['min'];
                $info[$k]['max_range']=$orderRes['max'];
                $info[$k]['mem_cate']=$orderRes['mem_cate'];
            }
//            print_r($info);die;
//            $ids = array();
//            foreach($info as $k => $v){
//                $ids[$v['id']] = $v['id'];
//            }
//            $res = array(
//                'ids' => $ids,
//                'info' => $info,
//            );
//            $full = $this->exportBuyerListDataFull($res);
            $need = $this->packageBuyerExcelData($info,$lang);
            if($excel==false){   //excel导出
                return array('info'=>$need,'totalCount'=>$totalCount);
            }
            $excelName = 'buyerlist'.($i/$pageSize+1);
            $excel = $this->exportModel($excelName,$need,$lang);  //导入excel
            $excelArr[] = $excel;
            $i=$i+$pageSize;
            $totalCount=$totalCount-$pageSize;

            sleep(1);
        }while($totalCount>0);   //结束-----------------------------------------------------------------------------------
        return $excelArr;  //文件数组
    }
    public function exportCustomer(){
        $this->query('TRUNCATE erui_buyer.buyer_bak');
        sleep(10);
        $zh=$this->buyerBakZhEn('zh');
        $en=$this->buyerBakZhEn('en');
        echo $zh.'+'.$en;die;
    }
    public function buyerBakZhEn($lang){
        $count = $this->where("deleted_flag='N' and status !='REJECTED'")->count();
//        echo $count;die;
        $i=0;
        do{
            try{
                $this->buyerBak($i,$lang);
                $i+=200;
                $count-=200;
                sleep(5);
            }catch (Exception $e){
                print_r($e->getMessage());exit;
            }

        }while($count>=0);
        return $lang;
    }
    public function buyerBak($i,$lang){
        $info=$this->getCustomerInfo($i,$lang);
        $str='';
        foreach($info as $kk => $vv){
            foreach($vv as $k => &$v){
                $v="'".$v."'";
            }
            $str.=",(null,'$lang',".implode(',',$vv).")";
        }
        $str=mb_substr($str,1);
        $sql='insert into erui_buyer.buyer_bak ';
        $sql.=' values '.$str;
//        print_r($sql);die;
        $res=$this->query($sql);
        return true;
    }
    //备份全部有效客户数据
    public function getCustomerInfo($i,$lang='zh'){
        $cond="buyer.deleted_flag='N' and buyer.status !='REJECTED'";
        $field ='buyer.id'; //获取查询字段
        $fieldBuyerArr = array(
//            'id',   //客户id
//                'area_bn',   //客地区
            'percent',   //信息完整度百分比
            'country_bn',   //国家
            'buyer_code',   //客户编码
            'name as buyer_name',   //客户名称
            'created_at',   //创建时间
            'build_time',   //客户档案创建时间
            'is_oilgas',   //是否油气
            'buyer_level',   //客户等级
            'level_at',   //等级设置时间
            'reg_capital',   //注册资金
            'reg_capital_cur',   //货币
//                'credit_level',   //采购商信用等级   X
//                'credit_type',   //授信类型   X
//                'line_of_credit',   //授信额度    X
        );
        foreach($fieldBuyerArr as $v){
            $field .= ',buyer.'.$v;
        }
        $fieldBusiness = array(
//                'is_net', //是否入网
//                'net_at', //入网时间
//                'net_invalid_at', //失效时间
            'product_type', //产品类型
            'is_local_settlement', //本地结算
            'is_purchasing_relationship', //采购关系
        );
        foreach($fieldBusiness as $v){
            $field .= ',business.'.$v;
        }
        $field .= ',(select name from erui_sys.employee where id=buyer.created_by and deleted_flag=\'N\') as created_name';
        $field .= ',credit.credit_level'; //采购商信用等级
        $field .= ',credit.credit_type';  //授信类型
        $field .= ',credit.line_of_credit';   //授信额度

        $info = $this->alias('buyer')
            ->join('erui_buyer.buyer_business business on buyer.id=business.buyer_id','left')
            ->join('erui_buyer.customer_credit credit on buyer.id=credit.buyer_id and credit.deleted_flag=\'N\'','left')
//                ->join('erui_sys.employee employee on buyer.created_by=employee.id and employee.deleted_flag=\'N\'','left')
            ->field($field)
            ->where($cond)
            ->order('buyer.id desc')
            ->limit($i,200)
            ->select();
        $country = new CountryModel();
        $level = new BuyerLevelModel();
        $credit = new CreditModel();
        $net=new NetSubjectModel();
        $agentModel = new BuyerAgentModel();
        $visitModel = new BuyerVisitModel();
        $inquiryModel = new InquiryModel();
        $orderModel = new OrderModel();
        foreach($info as $k => $v){
            $info[$k]['country_name'] = $country->getCountryByBn($v['country_bn'],$lang);
            if(!empty($info[$k]['buyer_level']) && is_numeric($info[$k]['buyer_level'])){
                $info[$k]['buyer_level'] = $level->getBuyerLevelById($v['buyer_level'],$lang);
            }
            if(!empty($info[$k]['credit_level']) && is_numeric($info[$k]['credit_level'])){
                $info[$k]['credit_level'] = $credit->getCreditNameById($v['credit_level'],$lang);
            }
            if(!empty($info[$k]['credit_type']) && is_numeric($info[$k]['credit_type'])){
                $info[$k]['credit_type'] = $credit->getCreditNameById($v['credit_type'],$lang);
            }
            //获取入网管理
            $netInfo=$net->showNetSubject(array('buyer_id'=>$v['id']));
            if(empty($netInfo)){    //arr
                $info[$k]['is_net']='N'; //是否入网
                $info[$k]['net_at']=''; //入网时间
                $info[$k]['net_invalid_at']=''; //失效时间
            }elseif(!empty($netInfo['erui']['net_at'])){
                $info[$k]['is_net']='Y'; //是否入网
                $info[$k]['net_at']=$netInfo['erui']['net_at']; //入网时间
                $info[$k]['net_invalid_at']=$netInfo['erui']['net_invalid_at']; //失效时间
            }else{
                $info[$k]['is_net']='Y'; //是否入网
                $info[$k]['net_at']=$netInfo['equipment']['net_at']; //入网时间
                $info[$k]['net_invalid_at']=$netInfo['equipment']['net_invalid_at']; //失效时间
            }

            //
            //客户服务经理
            $agentRes = $agentModel->getBuyerAgentFind($v['id']);
            $info[$k]['market_agent']=$agentRes;
            //访问
            $visitRes = $visitModel->singleVisitInfo($v['id']);
            $info[$k]['total_visit']=$visitRes['totalVisit'];
            $info[$k]['week_visit']=$visitRes['week'];
            $info[$k]['month_visit']=$visitRes['month'];
            $info[$k]['quarter_visit']=$visitRes['quarter'];
            //询报价
            $inquiryRes = $inquiryModel->statisInquiry($v['id']);
            $info[$k]['inquiry_count']=$inquiryRes['inquiry_count'];
            $info[$k]['quote_count']=$inquiryRes['quote_count'];
            $info[$k]['inquiry_account']=$inquiryRes['account'];
            //订单
            $orderRes = $orderModel->statisOrder($v['id']);
            $info[$k]['order_count']=$orderRes['count'];
            $info[$k]['order_account']=$orderRes['account'];
            $info[$k]['min_range']=$orderRes['min'];
            $info[$k]['max_range']=$orderRes['max'];
            $info[$k]['mem_cate']=$orderRes['mem_cate'];
        }
        $need = $this->packageBuyerExcelData($info,$lang);
        return $need;
    }
//序号
//客户信息完整度
//国家
//客户代码（CRM）
//客户名称
//档案创建日期
//创建人
//是否油气
//会员级别
//客户分类
//定级日期
//注册资金
//货币
//是否已入网
//入网时间
//入网失效时间
//客户产品类型
//客户信用等级
//授信类型
//授信额度
//是否本地币结算
//是否与KERUI有采购关系
//KERUI/ERUI客户服务经理	拜访总次数
//询价数量
//报价数量
//报价金额（美元）
//订单数量
//订单金额（美元）
//单笔金额偏重区间


    /**
     * sheet名称 $sheetName
     * execl导航头 $tableheader
     * execl导出的数据 $data
     * wangs
     */
    public function exportModel($sheetName,$data,$lang='zh')
    {
        ini_set("memory_limit", "1024M"); // 设置php可使用内存
        set_time_limit(0);  # 设置执行时间最大值
        //存放excel文件目录
        $excelDir = MYPATH . DS . 'public' . DS . 'tmp' . DS . 'buyerlist';
        if (!is_dir($excelDir)) {
            mkdir($excelDir, 0777, true);
        }
        if($lang=='zh'){
            $tableheader = array('序号','客户信息完整度','国家', '客户代码（CRM）', '客户名称', '档案创建日期','创建人', '是否油气', '会员级别','客户分类', '定级日期', '注册资金', '货币', '是否已入网', '入网时间', '入网失效时间', '客户产品类型', '客户信用等级', '授信类型', '授信额度', '是否本地币结算', '是否与KERUI有采购关系', 'KERUI/ERUI客户服务经理', '拜访总次数', '询价数量','报价数量', '报价金额（美元）', '订单数量', '订单金额（美元）', '单笔金额偏重区间');
        }else{
            $tableheader = array('Serial', 'Integrity','Country', 'Customer code', 'Customer name', 'File creation date','created name', 'oil and gas industry or not', 'Customer level','Customer cate', 'Verification date', 'Registration capital', 'Currency', 'Net', 'Net time', 'Period of Validity', 'Customer product type', 'Credit level', 'Credit Type', 'Credit amount', 'Local currency settlement', 'Ever purchased from kerui', 'KERUI/ERUI CS Manager', 'Sub total', 'Qty of inquiries', 'Qty of quote', 'Total amount of quotation（USD）', 'Qty of orders', 'Order value（USD）', 'Ordered items(product type)');
        }
        //创建对象
        $excel = new PHPExcel();
        $objActSheet = $excel->getActiveSheet();
        $letter = range(A, Z);
        $letter = array_merge($letter, array('AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI'));
        //设置当前的sheet
        $excel->setActiveSheetIndex(0);
        //设置sheet的name
        $objActSheet->setTitle($sheetName);
        //填充表头信息
        for ($i = 0; $i < count($tableheader); $i++) {
            //单独设置D列宽度为15
            $objActSheet->getColumnDimension($letter[$i])->setWidth(20);
            $objActSheet->setCellValue("$letter[$i]1", "$tableheader[$i]");
            //设置表头字体样式
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setName('微软雅黑');
            //设置表头字体大小
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setSize(10);
            //设置表头字体是否加粗
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setBold(true);
            //设置表头文字垂直居中
            $objActSheet->getStyle("$letter[$i]1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //设置文字上下居中
            $objActSheet->getStyle("$letter[$i]1")->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //设置表头外的文字垂直居中
            $excel->setActiveSheetIndex(0)->getStyle($letter[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        }
        $objActSheet->getStyle('I')->getNumberFormat()->setFormatCode('0.00');
        $objActSheet->getStyle('Q')->getNumberFormat()->setFormatCode('0.00');
        $objActSheet->getStyle('W')->getNumberFormat()->setFormatCode('0.00');
        $objActSheet->getStyle('Y')->getNumberFormat()->setFormatCode('0.00');

        //填充表格信息
        for ($i = 2; $i <= count($data) + 1; $i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $objActSheet->setCellValue("$letter[$j]$i", "$value");
                $j++;
            }
        }
        //创建Excel输入对象
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save($excelDir . '/' . $sheetName . '.xlsx');    //文件保存
        return $excelDir . DS. $sheetName . '.xlsx';
    }

    /**检测输入CRM客户代码是否存在,返回数据信息
     * @param $data
     */
    public function checkBuyerCrm($data){
        $field = array(
            'id', //id
            'official_email', //邮箱
            'country_bn', //国家
            'official_phone', //区号,电话
            'first_name', //姓名

            'name', //公司名称
            'biz_scope', //经营范围
            'intent_product', //意向产品
            'purchase_amount' //预计年采购额
        );
//        $cond = array(
//            'buyer_code'=>$data['buyer_code'],
//            'deleted_flag'=>'N'
//        );
        $cond="buyer_code='$data[buyer_code]' and deleted_flag='N' and status!='REJECTED'";
        $info = $this->field($field)->where($cond)->find();
        return $info;
    }
    public function testCrm($data){
        $field = array(
            'official_email', //邮箱
            'country_bn', //国家
            'official_phone', //区号,电话
            'first_name', //姓名

            'name', //公司名称
            'biz_scope', //经营范围
            'intent_product', //意向产品
            'purchase_amount' //预计年采购额
        );
        $cond = array(
            'buyer_code'=>$data['buyer_code'],
        );
        $info = $this->field($field)->where($cond)->find();
        if(!empty($info)){
            $country = new CountryModel();
            $info['country_name'] = $country->getCountryByBn($info['country_bn'],'zh');
        }
        if(!empty($info['official_phone'])){
            $phone = explode('-',$info['official_phone']);
            if(count($phone) == 1){
                $info['areacode'] = NULL;
                $info['mobile'] = $phone[0];
            }else{
                $info['areacode'] = $phone[0];
                $info['mobile'] = $phone[1];
            }
            unset($info['official_phone']);
        }
        return $info;
    }

    /**
     * 创建业务信息,crm-信用
     */
//    public function CrmCredite($credit,$buyer_id){
//        $arr=array(
//            'line_of_credit'=>!empty($credit['line_of_credit'])?$credit['line_of_credit']:0,    //授信额度
//            'credit_available'=>!empty($credit['credit_available'])?$credit['credit_available']:0,    //可用额度
//
//            'payment_behind'=>!empty($credit['payment_behind'])?$credit['payment_behind']:null,    //是否拖欠过货款
//            'behind_time'=>!empty($credit['behind_time'])?$credit['behind_time']:null,    //拖欠货款时间
//            'reputation'=>!empty($credit['reputation'])?$credit['reputation']:null,    //业内口碑
//            'violate_treaty'=>!empty($credit['violate_treaty'])?$credit['violate_treaty']:null,  //是否有针对KERUI/ERUI的违约
//            'treaty_content'=>!empty($credit['treaty_content'])?$credit['treaty_content']:null,    //有违约内容
//            'comments'=>!empty($credit['comments'])?$credit['comments']:null,    //KERUI/ERUI、KERUI对其评价
//
//            'credit_type'=>$credit['credit_type'],    //授信类型
//            'credit_level'=>$credit['credit_level'],    //信用等级
//        );
//        $cond=array(
//            'id'=>$buyer_id,
//            'deleted_flag'=>'N'
//        );
//        return $this->where($cond)->save($arr);
//    }

    /**
     * @param $id crm-wangs
     */
//    public function showBuyerCredit($id){
//        $cond=array(
//            'id'=>$id,
//            'deleted_flag'=>'N'
//        );
//        return $this->field('credit_level,credit_type,line_of_credit,credit_available,payment_behind,behind_time,reputation,violate_treaty,treaty_content,comments')->where($cond)->find();
//    }
//    public function showCredit($data){
//        if(empty($data['buyer_id'])){
//            return false;
//        }
//        $cond=array(
//            'id'=>$data['buyer_id'],
//            'deleted_flag'=>'N'
//        );
//        return $this->field('id as buyer_id,credit_level,credit_type,line_of_credit,credit_available,payment_behind,behind_time,reputation,violate_treaty,treaty_content,comments')->where($cond)->find();
//    }
//    public function editCredit($data){
//        if(empty($data['buyer_id'])){
//            return false;
//        }
//        $arr=array(
//            'line_of_credit'=>!empty($data['line_of_credit'])?$data['line_of_credit']:0,    //授信额度
//            'credit_available'=>!empty($data['credit_available'])?$data['credit_available']:0,    //可用额度
//
//            'payment_behind'=>!empty($data['payment_behind'])?$data['payment_behind']:null,    //是否拖欠过货款
//            'behind_time'=>!empty($data['behind_time'])?$data['behind_time']:null,    //拖欠货款时间
//            'reputation'=>!empty($data['reputation'])?$data['reputation']:null,    //业内口碑
//            'violate_treaty'=>!empty($data['violate_treaty'])?$data['violate_treaty']:null,  //是否有针对KERUI/ERUI的违约
//            'treaty_content'=>!empty($data['treaty_content'])?$data['treaty_content']:null,    //有违约内容
//            'comments'=>!empty($data['comments'])?$data['comments']:null,    //KERUI/ERUI、KERUI对其评价
//
//            'credit_type'=>$data['credit_type'],    //授信类型
//            'credit_level'=>$data['credit_level'],    //信用等级
//        );
//        $cond=array(
//            'id'=>$data['buyer_id'],
//            'deleted_flag'=>'N'
//        );
//        return $this->where($cond)->save($arr);
//    }
    /**
     * @param $buyer_id
     * 验证:邮箱,手机号,公司名称
     */
    public function clickEditCheck($buyer_id){
        $arr=array('company'=>0,'email'=>0);
        $buyer=$this->field('id,name as company_name,buyer_code,official_email')->where(array('id'=>$buyer_id,'deleted_flag'=>'N'))->find();
        $company_name=$buyer['company_name'];   //公司名称
        $buyer_code=$buyer['buyer_code'];   //crmcode
        $official_email=$buyer['official_email'];   //公司邮箱
        $buyerAccountModel=new BuyerAccountModel();
        $account=$buyerAccountModel->field('email')->where(array('buyer_id'=>$buyer_id,'deleted_flag'=>'N'))->find();
        $accountEmail=$account['email'];    //账户邮箱
        //验证
        $info=$this->field('id')->where(array('name'=>$company_name,'deleted_flag'=>'N'))->select();
        if(!empty($info) && count($info)>1){
            $arr['company']=1;
        }
        if($official_email!=$accountEmail){
            $sqlEmail="select official_email as email from erui_buyer.buyer WHERE official_email='$accountEmail' union all";
            $sqlEmail.=" select email from erui_buyer.buyer_account WHERE email='$accountEmail' union ALL ";
            $sqlEmail.=" select email from erui_sys.employee WHERE email='$accountEmail'";
            $existEmail=$this->query($sqlEmail);
            if(!empty($existEmail) && count($existEmail)>1){
                $arr['email']=1;
            }
        }
        return $arr;
    }
    //crm 获取地区,国家,会员统计中使用
    private function _getCountry($lang,$area_bn='',$country_bn='',$admin){
        $access=$this->statisAdmin($admin);
        if($access===1){
            if(!empty($country_bn)){
                return [['country_bn'=>$country_bn]];
            }
            if(!empty($area_bn)){
                $country=new MarketAreaCountryModel();
                $countryArr=$country->field('country_bn')
                    ->where("market_area_bn='$area_bn'")
                    ->select();
                return $countryArr;
            }
        }else{
            if(!empty($country_bn)){
                if(preg_match("/$country_bn/i", $admin['country'])){    //国家
                    return [['country_bn'=>$country_bn]];
                }
            }
            if(!empty($area_bn)){
                if(preg_match("/$area_bn/i", $admin['area'])){    //地区下的国家
                    $country=new MarketAreaCountryModel();
                    $countryArr=$country->field('country_bn')
                        ->where("market_area_bn='$area_bn' and country_bn in ($admin[country])")
                        ->select();
                    return $countryArr;
                }
            }
        }
        return false;


    }
    //获取上周日期时间段
    public function getLastWeek(){
        $beginLastweek=mktime(0,0,0,date('m'),date('d')-date('w')+1-9,date('Y'));

        $endLastweek=mktime(23,59,59,date('m'),date('d')-date('w')+7-9,date('Y'));
        $arr['start_time']=date('Y-m-d',$beginLastweek);
        $arr['end_time']=date('Y-m-d',$endLastweek);
        return $arr;
    }
    public function statisAdmin($admin){
        if(in_array('CRM客户管理',$admin['role'])){    //运营专员,CRM客户管理所有权限
            $access=1;
        }elseif(in_array('201711242',$admin['role'])){  //市场区域国家负责人
            $access=$admin['country'];
        }else{
            $access=0;
        }
        return $access;
    }
    //获取国家权限
    public function countryAdmin($data,$column){
        $admin=$this->statisAdmin($data['admin']);
        if($admin===0){ //无权限
            return false;
        }
        if(!empty($data['area_bn']) || !empty($data['country_bn'])){   //地区国家
            $countryArr=$this->_getCountry($data['lang'],$data['area_bn'],$data['country_bn'],$data['admin']);
            if(!empty($countryArr)){
                $str='';
                foreach($countryArr as $k => $v){
                    $str.=",'".$v['country_bn']."'";
                }
                $str=substr($str,1);
                if(count($countryArr)==1){
                    $cond=' and '.$column.'.country_bn='.$str;
                }else{
                    $cond=' and '.$column.'.country_bn in ('.$str.')';
                }
            }
        }else{
            if($admin===0){  //无权限
                return false;
            }elseif($admin===1){ //所有权限
                $cond='';
            }else{  //国家负责人
                if(!empty($admin)){
                    $cond=' and '.$column.'.country_bn in ('.$admin.') ';
                }else{
                    return false;
                }
            }
        }
        return $cond;
    }
    //获取会员统计cond
    private function getStatisMemberCond($data,$time=false){
        $cond=' buyer.deleted_flag=\'N\' and buyer.status=\'APPROVED\'';  //客户状态
        $admin=$this->countryAdmin($data,'buyer');
        if($admin===false){ //无权限
           return false;
        }
        $cond.=$admin;
        if(!empty($data['source'])){    //来源
            $cond.=' and buyer.source='.$data['source'];
        }
        if(!empty($data['buyer_level'])){
            if($data['buyer_level']=='52'){
                $cond .= " and buyer.buyer_level=52";
            }elseif($data['buyer_level']=='53'){
                $cond .= " and buyer.buyer_level=53";
            }else{
                $cond .= " and buyer.buyer_level is null";
            }
//            $cond .= " and buyer.buyer_level='$data[buyer_level]'";
        }

        if(!empty($data['start_time'])){   //等级
            $data['start_time']=substr($data['start_time'],0,10);
        }
        if(!empty($data['end_time'])){   //等级
            $data['end_time']=substr($data['end_time'],0,10);
        }


        if($time==true){
            if(!empty($data['start_time'])){ //默认数据
                $cond.=' and buyer.created_at >= \''.$data['start_time'].' 00:00:00\'';
            }
            if(!empty($data['end_time'])){ //默认数据
                $cond.=' and buyer.created_at <= \''.$data['end_time'].' 23:59:59\'';
            }
        }else{
            if(empty($data['start_time']) && empty($data['end_time'])){ //默认数据
                $week=$this->getLastWeek();
                $cond.=' and buyer.created_at >= \''.$week['start_time'].' 00:00:00\'';
                $cond.=' and buyer.created_at <= \''.$week['end_time'].' 23:59:59\'';
            }elseif(!empty($data['start_time']) && !empty($data['end_time'])){   //时间段搜索
                $cond.=' and buyer.created_at >= \''.$data['start_time'].' 00:00:00\'';
                $cond.=' and buyer.created_at <= \''.$data['end_time'].' 23:59:59\'';
            }
        }
        return $cond;
    }
    //crm会员统计模块-wangs
    public function statisMemberInfo($data){
        $cond=$this->getStatisMemberCond($data);
        if($cond===false){
            return false;
        }
        $lang=$data['lang'];
        $sql='select count(source) as `count`,source from erui_buyer.buyer buyer ';
        $sql.=' where ';
        $sql.=$cond;
        $sql.=' group by source ';
        $sql.=' order by source ';
        $source=$this->query($sql);
        $arr=array(
            ['name'=>'boss','value'=>0],
            ['name'=>'website','value'=>0],
            ['name'=>'app','value'=>0]
        );
        if(!empty($source)){
            foreach($source as $k => $v){
                if($v['source']==1){
                    $arr[$k]['value']=$v['count'];
                }elseif($v['source']==2){
                    $arr[$k]['value']=$v['count'];
                }elseif($v['source']==3){
                    $arr[$k]['value']=$v['count'];
                }
            }
        }
        return $arr;
    }
    //会员增长
    public function memberSpeed($data){
        $cond=$this->getStatisMemberCond($data);
        if($cond===false){
            return false;
        }
        if(empty($data['start_time']) && empty($data['end_time'])){
            $week=$this->getLastWeek();
            $data['start_time']=$week['start_time'];
            $data['end_time']=$week['end_time'];
        }
        $lang=$data['lang'];
        $sql='select count(id) as `count`,DATE_FORMAT(created_at,\'%Y-%m-%d\') as created_at from erui_buyer.buyer buyer ';
        $sql.=' where ';
        $sql.=$cond;
        $sql.=' group by DATE_FORMAT(created_at,\'%Y-%m-%d\') ';
        $sql.=' order by created_at ';
        $member=$this->query($sql);
        $customer=$this->packDailyData($member,$data['start_time'],$data['end_time']);
        return $customer;
    }
    //整理每天的数据
    public function packDailyData($data,$start_time,$end_time){
        $days=(strtotime($end_time)-strtotime($start_time))/86400+1;
        $arr=[];
        $info=[];
        for($i=0;$i<$days;$i++){
            $arr[$i]['created_at']=date("Y-m-d",strtotime("$start_time +$i day"));
            $arr[$i]['count']=0;
        }
        foreach($arr as $key => &$value){
            foreach($data as $k => $v){
                if($v['created_at'] == $value['created_at']){
                    $arr[$key]['created_at']=$value['created_at'];
                    $arr[$key]['count']=$v['count'];
                }
            }
        }
        foreach($arr as $k => $v){
            $info['day'][]=$v['created_at'];
            $info['count'][]=intval($v['count']);
        }
        return $info;
    }
    //统计会员信息列表CRM-wangs
    public function statisMemberList($data,$order=false){
        $cond=$this->getStatisMemberCond($data,true);
        if($cond===false){
            return false;
        }
        $page=isset($data['page'])?$data['page']:1;
        $pageSize=isset($data['pageSize'])?$data['pageSize']:10;
        $offset=($page-1)*$pageSize;
        $total=$this->getStatisTotal($cond);
        $sql='select ';
        $sql.=' buyer.id as buyer_id,buyer.buyer_no,buyer.name as buyer_name,buyer.buyer_code, ';
        $sql.='(select name from erui_operation.market_area where bn=country.market_area_bn  and lang=\'zh\') as area_name ,';
        $sql.=' (select DISTINCT name from erui_dict.country where bn=buyer.country_bn and lang=\'zh\' AND deleted_flag=\'N\') as country_name ,';
        $sql.=' buyer.source,buyer.is_build,buyer.status,buyer.created_at,buyer.checked_at, ';
        $sql.=' (select buyer_level from erui_config.buyer_level where deleted_flag=\'N\' and id=buyer.buyer_level) as buyer_level, ';
        $sql.=' buyer.intent_product ';
        $sql.=' from erui_buyer.buyer buyer ';
        $sql.=' left join erui_operation.market_area_country country on buyer.country_bn=country.country_bn';
        $sql.=' where ';
        $sql.=$cond;
        $sql.=' order by buyer.created_at desc';
        $sql.=' limit '.$offset.','.$pageSize;
        $info=$this->query($sql);
        if($order==true){
            $arr['total']=$total;
            $arr['page']=$page;
            $arr['pageSize']=$pageSize;
            $arr['info']=$info;
            return $arr;
        }
        foreach($info as $k => &$v){
            $info[$k]['agent']=$this->getStatisAgent($v['buyer_id']);
            if($v['is_build']==1){
                $info[$k]['status']='PASS';
            }
            unset($v['is_build']);
            unset($v['buyer_level']);
            unset($v['intent_product']);
        }
        $arr['total']=$total;
        $arr['page']=$page;
        $arr['pageSize']=$pageSize;
        $arr['info']=$info;
        return $arr;
    }
    public function getStatisTotal($cond){
        return $this->where($cond)->count();
    }
    public function getStatisAgent($buyer_id){
        $sql_agent='select employee.name';
        $sql_agent.=' from erui_buyer.buyer_agent agent ';
        $sql_agent.=' left join erui_sys.employee employee on agent.agent_id=employee.id and employee.deleted_flag=\'N\'';
        $sql_agent.=' where agent.deleted_flag=\'N\' AND agent.buyer_id='.$buyer_id;
        $agentInfo=$this->query($sql_agent);
        $agentStr='';
        foreach($agentInfo as $k => $v){
            $agentStr.=','.$v['name'];
        }
        $agentStr=substr($agentStr,1);
        return $agentStr;
    }
    //会员属性统计
    public function statisMemberAttr($data){
        $arr=$this->statisMemberList($data,true);
        if($arr===false){
            return false;
        }
        $total=$arr['total'];
        $page=$arr['page'];
        $pageSize=$arr['pageSize'];
        $info=$arr['info'];
        $lang=$data['lang'];
        $inquiry=new InquiryModel();
        foreach($info as $key => &$value){
            if(!empty($value['buyer_level'])){
                $level_name=json_decode($value['buyer_level'],true);
                foreach($level_name as $k => $v){
                    if($lang==$v['lang']){
                        $value['buyer_level']=$v['name'];
                    }
                }
            }
            $InquiryOrder=$inquiry->getBuyerInquiry($value['buyer_id']);
            $info[$key]['inquiry_count']=$InquiryOrder['inquiry_count'];   //询单个数
            $info[$key]['quote_count']=$InquiryOrder['quote_count'];   //报价个数
            $info[$key]['order_count']=$InquiryOrder['order_count'];   //订单数
            $info[$key]['order_rate']=$InquiryOrder['order_rate'];   //成单率
            $info[$key]['order_amount_rate']=$InquiryOrder['order_amount_rate'];   //成单金额率
//            unset($info[$key]['id']);
            unset($info[$key]['is_build']);
            unset($info[$key]['status']);
//            unset($info[$key]['created_at']);
            unset($info[$key]['checked_at']);
        }
        $result['total']=$total;
        $result['page']=$page;
        $result['pageSize']=$pageSize;
        $result['info']=$info;  //数据
        return $result;
    }
    //会员行为统计列表
    public function statisMemberBehave($data){
        $cond=$this->getStatisMemberCond($data,true);
        if($cond===false){
            return false;   //无权限查看
        }
        $page=isset($data['page'])?$data['page']:1;
        $pageSize=isset($data['pageSize'])?$data['pageSize']:10;
        $offset=($page-1)*$pageSize;
        $total=$this->getStatisTotal($cond);
        $sql='select ';
        $sql.=' buyer.id as buyer_id,buyer.buyer_no,buyer.name as buyer_name,buyer.buyer_code, ';
        $sql.=' buyer.created_at ';

        $sql.=' from erui_buyer.buyer buyer ';
        $sql.=' where ';
        $sql.=$cond;
        $sql.=' order by buyer.created_at desc';
        $sql.=' limit '.$offset.','.$pageSize;
        $info=$this->query($sql);
        if(empty($info)){
            $info=array(
                'total'=>0,
                'page'=>1,
                'info'=>[],
            );
            return $info;
        }
        $lang=$data['lang'];
        $visit=new BuyerVisitModel();
        $order=new OrderModel();
        foreach($info as $key => $value){
            $visitInfo=$visit->statisVisitCount($value['buyer_id']);   //统计拜访记录
            $info[$key]['visit_count']=$visitInfo['visit_count'];
            $info[$key]['demand_count']=$visitInfo['demand_count'];
            $orderInfo=$order->statisOrderStatusCount($value['buyer_id'],$value['buyer_code']);    //统计订单各个状态的数量
            $info[$key]['order_count']=$orderInfo['total']; //该该客户订单总数
            $info[$key]['order_unconfirmed']=$orderInfo['unconfirmed']; //待确认的订单数
            $info[$key]['order_going']=$orderInfo['going']; //进行中的订单数
            $info[$key]['order_outgoing']=$orderInfo['outgoing']; //已出库的订单数
            $info[$key]['order_dispatched']=$orderInfo['dispatched']; //已发运订单数
            $info[$key]['order_completed']=$orderInfo['completed']; //已完成订单数
            $info[$key]['order_payment']=$orderInfo['payment_amount']; //订单回款金额
            $info[$key]['order_amount']=$orderInfo['amount']; //总订单销售金额
        }
        $arr['total']=$total;
        $arr['page']=$page;
        $arr['pageSize']=$pageSize;
        $arr['info']=$info;
        return $arr;
    }
    //给国家负责人消息提醒
    public function countryAccess($admin){
        if(in_array('201711242',$admin['role'])){
            return 1;   //国家权限
        }else{
            return 0;
        }
    }
    public function getBuyerCount($cond){
        return $this->where($cond)->count();
    }
    public function getBuyerCond($countryStr){
        $cond="buyer.deleted_flag='N' and buyer.country_bn in ($countryStr) and buyer.status='APPROVING'";
        return $cond;
    }
    public function getBuyerInfoByCond($cond,$lang){
        $cond.=" and country.lang='$lang'";
        $field='buyer.id,buyer.name as buyer_name,buyer.country_bn,country.name as country_name,buyer.created_at,buyer.source,buyer.is_handle';
        $field.=',buyer.is_handle,buyer.read_at,buyer.sent_at,mark_at,buyer.checked_at';
        $info=$this->alias('buyer')
            ->join('erui_dict.country country on buyer.country_bn=country.bn','left')
            ->field($field)
            ->where($cond)
            ->order('id desc')
            ->select();
        foreach($info as $k => &$v){
            if($v['source']==1){
                $v['source']='boss';
            }elseif($v['source']==2){
                $v['source']='website';
            }elseif($v['source']==3){
                $v['source']='app';
            }
        }
        return $info;
    }
    //消息提醒-wangs
    public function MessageRemind($data){
        $access=$this->countryAccess($data['admin']);
        $total_flag=isset($data['total_flag'])?$data['total_flag']:false;
        if($access !== 1){   //无国家权限
            return false;
        }
        //所负责的国家
        $countryStr=$data['admin']['country'];

        $cond=$this->getBuyerCond($countryStr); //获取条件
        $count=$this->getBuyerCount($cond); //数据数量
        if($count == 0){
            return 0;
        }
        if($total_flag===true){
            $arr['count']=$count;
            return $arr;
        }
        $sent_at=time();
        $sql = "UPDATE erui_buyer.buyer ";
        $sql .= " SET `sent_at`='$sent_at',mark_at=UNIX_TIMESTAMP(created_at)";
        $sql .= " WHERE ".$cond;
        $this->query($sql);
        $arr['count']=$count;
        $arr['info']=$this->getBuyerInfoByCond($cond,$data['lang']);;
        return $arr;
    }
    //查看已办理信息
    public function noticeMessage($data){
        $access=$this->countryAccess($data['admin']);
        if($access !== 1){   //无国家权限
            return false;
        }
        $total_flag=isset($data['total_flag'])?$data['total_flag']:false;
        //所负责的国家
        $countryStr=$data['admin']['country'];
        $cond="buyer.is_handle=1 and buyer.status='APPROVED'";
        $cond.=" and buyer.country_bn in ($countryStr) and buyer.deleted_flag='N' ";
        $count=$this->getBuyerCount($cond); //数据数量
        if($count == 0){
            return 0;
        }
        if($total_flag===true){
            $arr['count']=$count;
            return $arr;
        }
        $arr['count']=$count;
        $arr['info']=$this->getBuyerInfoByCond($cond,$data['lang']);;
        return $arr;
    }
    //国家负责人是否读取信息
    public function readMessage($data){
        $id=isset($data['id'])?$data['id']:0;
        if(empty($id)){ //缺少参数
            return 'param';
        }
        $info=$this->field('id')->where(array('id'=>$id))->find();
        if(empty($info)){
            return 'none';
        }
        $save=array(
            'read_at'=>time()
        );
        $this->where(array('id'=>$id))->save($save);
        return true;
    }
    //发送系统信息提醒
    public function sentSystemMessage($data){
        $countryStr=$data['admin']['country'];
        $cond="buyer.deleted_flag='N' and buyer.country_bn in ($countryStr) and buyer.status='APPROVING'";
        $count=$this->where($cond)->count();
        if($count == 0){
            return 0;   //暂无信息
        }
        $info=$this->getBuyerInfoByCond($cond,$data['lang']);
        $prevtime=time()-86400;   //当前时间戳-24H
        $readArr=[];
        $str='';
        foreach($info as $k => $v){
            if($v['mark_at'] <= $prevtime){ //信息未处理已超过24H
                $readArr[$k]['id']=$v['id'];
                $readArr[$k]['buyer_name']=$v['buyer_name'];
                $readArr[$k]['country_name']=$v['country_name'];
                $readArr[$k]['created_at']=$v['created_at'];
                $readArr[$k]['source']=$v['source'];
                $readArr[$k]['is_read']=$v['is_read'];
                $readArr[$k]['read_at']=$v['read_at'];
                $readArr[$k]['sent_at']=$v['sent_at'];
                $readArr[$k]['mark_at']=$v['mark_at'];
                $str.=','.$v['id'];
            }
        }
        if(!empty($readArr)){   //24H过期信息
            $str=substr($str,1);
            $sql = "UPDATE erui_buyer.buyer ";
            $sql .= " SET mark_at=mark_at+86400";
            $sql .= " WHERE id in ($str)";
            $this->query($sql);
            $arr['count']=count($readArr); //过期24H 个数
            $arr['info']=$readArr; //过期24H 客户信息
        }else{
            $arr=0;
        }
        return $arr;
    }
    //每秒请求系统
    public function requestSystem(){

        $cond="deleted_flag='N' and buyer.status='APPROVING'";
        $count=$this->where($cond)->count();
        if($count==0){  //无数据
            return 0;
        }
        $info=$this->field('id,buyer_code,mark_at')->where($cond)->select();
        $setArr=[];
        $str='';
        $prevtime=time()-86400;   //当前时间戳-24H
        foreach($info as $k => $v){
            if($v['mark_at'] <= $prevtime){ //信息未处理已超过24H
                $setArr[$k]['id']=$v['id'];
                $setArr[$k]['buyer_code']=$v['buyer_code'];
                $setArr[$k]['mark_at']=$v['mark_at'];
                $str.=','.$v['id'];
            }
        }
        if(!empty($setArr)){   //24H过期信息
            $str=substr($str,1);
            $sql = "UPDATE erui_buyer.buyer ";
            $sql .= " SET mark_at=mark_at+86400";
            $sql .= " WHERE id in ($str)";
            $this->query($sql);
            $arr['count']=count($setArr); //过期24H 个数
            $arr['info']=$setArr; //过期24H 客户信息
        }else{
            $arr=0;
        }
        return $arr;
    }
    //信息完整度统计客户基本信息
    public function percentBuyer($data){
        $cond=array('id'=>$data['buyer_id'],'deleted_flag'=>'N');
        $baseField=array(
//            'buyer_code', //客户代码
            'buyer_no', //客户编码
//            'buyer_level', //客户等级
//            'country_bn', //国家
            'buyer_type', //客户类型
            'is_oilgas', //是否油气
//            'name as company_name', //公司名称
            'official_phone', //公司电话
            'official_email', //公司邮箱
            'official_website', //公司网址
            'company_reg_date', //公司注册日期
            'reg_capital', //注册金额
            'reg_capital_cur', //注册币种
            'employee_count', //公司员工数量
            'company_model', //公司性质
            'sub_company_name', //子公司名称
            'company_address', //公司地址
            'profile as company_profile', //公司其他信息
//            'biz_scope', //公司名称
//            'intent_product', //公司名称
//            'purchase_amount', //公司名称
        );
        $info=$this->field($baseField)->where($cond)->find();
        if(!empty($info)){
            if($info['reg_capital']==0){
                $info['reg_capital']='';
            }
            if($info['employee_count']==0){
                $info['employee_count']='';
            }
        }else{
            $info=[];
            foreach($baseField as $k => $v){
                $info[$v]='';
            }
        }
        return $info;
    }
    public function validBuyerBaseArr($arr){
        $base = $arr['base_info'];  //基本信息
        $contact = $arr['contact_info']; //联系人
        $baseArr = array(   //创建客户基本信息必须数据
//            'buyer_id'=>'客户id',
            'buyer_name'=>L('buyer_name'),
//            'buyer_account'=>'客户账号',
//            'buyer_code'=>'客户CRM编码',
//            'buyer_level'=>'客户级别',
//            'country_bn'=>'国家',
//            'area_bn'=>'地区',
//            'market_agent_name'=>'erui客户服务经理（市场经办人)',
//            'market_agent_mobile'=>'服务经理联系方式',
//            'level_at'=>'定级日期',
//            'expiry_at'=>'有效期',
            'is_oilgas'=>L('is_oilgas'),    //是否油气
            'company_model'=>L('company_model'),    //公司性质
            'official_phone'=>L('official_phone'),  //公司电话
            'official_email'=>L('official_email'),     //公司邮箱
            'official_website'=>L('official_website'),  //公司网址
            'company_reg_date'=>L('company_reg_date'),  //公司成立日期
            'company_address'=>L('company_address'),//  +公司地址
            'reg_capital'=>L('reg_capital'),    //注册资金
            'reg_capital_cur'=>L('reg_capital_cur'),    //注册资金货币
            'profile'=>L('profile'),    //公司其他信息
//            'is_oilgas'=>'是否油气',
//            'company_model'=>'公司性质',
//            'official_phone'=>'公司电话',
//            'official_email'=>'公司邮箱',
//            'official_website'=>'公司网址',
//            'company_reg_date'=>'公司成立日期',
//            'company_address'=>'公司地址',  //  +
//            'reg_capital'=>'注册资金',
//            'reg_capital_cur'=>'注册资金货币',
//            'profile'=>'公司其他信息',

        );
        foreach($baseArr as $k => $v){
            if(empty($base[$k])){
                return $v.L('not empty');
            }
        }
        if(!empty($base['company_reg_date'])){
            $head=substr($base['company_reg_date'],0,4);
            if(!is_numeric($head)){
                return $baseArr['company_reg_date'].L('not empty');
            }
            $date=explode('-',$base['company_reg_date']);
            $y=$date[0];
            $m=sprintf("%02s",intval($date[1]));
            $d=sprintf("%02s",intval($date[2]));
            if($m<0 || $m>12){
                return $baseArr['company_reg_date'].L('format_error');    //的月份错误
            }
            if($m=='00'){
                if($d != '00'){
                    return $baseArr['company_reg_date'].L('format_error');  //的月份日期错误
                }
            }else{
                if(in_array($m,['04','06','09','11'])){
                    if($d <0 || $d >30){
                        return $baseArr['company_reg_date'].L('format_error');    //的日期错误
                    }
                }
                if(in_array($m,['01','03','05','07','08','10','12'])){
                    if($d <0 || $d >31){
                        return $baseArr['company_reg_date'].L('format_error');    //的日期错误
                    }
                }
                if($m == '02'){
                    if($d <0 || $d >28){
                        return $baseArr['company_reg_date'].L('format_error');    //的日期错误
                    }
                }
            }
            $dateArr=[$y,$m,$d];
            $base['company_reg_date']=implode('-',$dateArr);
        }else{
            return $baseArr['company_reg_date'].L('not empty');
        }
//        if(!empty($base['official_phone'])){
//            if(!preg_match ("/^(\d{2,4}-)?\d{6,11}$/",$base['official_phone'])){
//                return '公司电话:(选)2~4位区号-6~11位电话号码';
//            }
//        }
        if(!preg_match ("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/",$base['official_email'])){
            return $baseArr['official_email'].L('format_error');
        }else{
            $email=$this->field('official_email')
                ->where(array('id'=>$arr['buyer_id'],'deleted_flag'=>'N'))
                ->find();//默认邮箱
            if($base['official_email']!=$email['official_email']){  //修改邮箱
                $exist=$this->field('official_email')->where(array('official_email'=>trim($base['official_email'],' '),'deleted_flag'=>'N'))->find();
                if($exist){
                    return $baseArr['official_email'].L('already existed');
                }
            }
        }
        if(is_numeric($base['reg_capital'])  && $base['reg_capital']>0){
        }else{
            return $baseArr['reg_capital'].L('format_error');
        }

        //基本信息可选数据
        $baseExtra = array( //创建客户基本信息可选数据
            'type_id'=>'客户类型',   //buyer_type
            'type_remarks'=>'类型备注',
            'is_oilgas'=>'是否油气',
            'employee_count'=>L('employee_count')
        );
        //联系人【contact】
//        $contactArr = array(    //创建客户信息联系人必须数据
//            'name'=>L('contact_name'),  //联系人姓名
//            'title'=>L('contact_title'),    //联系人职位
//            'phone'=>L('contact_phone'),    //联系人电话
//        );
//        $contactExtra = array(  //创建客户信息联系人可选数据
//            'role'=>'购买角色',
//            'email'=>L('contact_email'),    //联系人邮箱
//            'hobby'=>'喜好',
//            'address'=>'详细地址',
//            'experience'=>'工作经历',
//            'social_relations'=>'社会关系',
//
//            'key_concern'=>'决策主要关注点',
//            'attitude'=>'对科瑞的态度',
//            'social_place'=>'常去社交场所',
//            'relatives_family'=>'家庭亲戚相关信息',
//        );
//        $contactEmail=array();  //crm
//        foreach($contact as $value){
//            foreach($contactArr as $k => $v){
//                if(empty($value[$k]) || strlen($value[$k]) > 50){
//                    return $v.L('not empty');
//                }
////                if(!empty($value['phone'])){
////                    if(!preg_match ("/^(\d{2,4}-)?\d{6,11}$/",$value['phone'])){
////                        return '联系人电话:(选)2~4位区号-6~11位电话号码';
////                    }
////                }
//            }
//            if(!empty($value['email'])){
//                $value['email']=trim($value['email'],' ');
//                if(!preg_match ("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/",$value['email'])){
//                    return $contactExtra['email'].L('format_error');
//                }else{
//                    $buyerContact=new BuyercontactModel();
//                    if(empty($value['id'])){
//                        $email=$buyerContact->field('email')->where(array('email'=>$value['email'],'deleted_flag'=>'N'))->find();
//                        if($email){
//                            return $contactExtra['email'].L('already existed');
//                        }
//                    }else{
//                        $email=$buyerContact->field('email')->where(array('id'=>$value['id']))->find();//默认邮箱
//                        if($value['email']!=$email['email']){  //修改邮箱
//                            $exist=$buyerContact->field('email')->where(array('email'=>$value['email'],'deleted_flag'=>'N'))->find();
//                            if($exist){
//                                return $contactExtra['email'].L('already existed');
//                            }
//                        }
//                    }
//
//                }
//                $contactEmail[]=$value['email'];
//            }
//            $emailTotal=count($contactEmail);   //联系人邮箱总数
//            $validTotal=count(array_flip(array_flip($contactEmail)));   //联系人邮箱过滤重复后总数
//            if($emailTotal!=$validTotal){
//                return $contactExtra['email'].L('repeat');
//            }
//        }
        if(!empty($base['employee_count'])){
            if(is_numeric($base['employee_count']) && $base['employee_count'] > 0){
                return true;
            }else{
                return $baseExtra['employee_count'];
            }
        }
        return true;
    }
    //新版crm
    public function editBuyerBaseInfo($data){
        $info = $this->validBuyerBaseArr($data);
        if($info !== true){
            return $info;
        }
        //基本信息
        $data['base_info']['buyer_id']=$data['buyer_id'];
        $arr = $this -> packageBaseData($data['base_info'],$data['created_by']);    //组装基本信息数据
        $this->where(array('id'=>$arr['id']))->save($arr);  //创建或修改客户档案信息
        //编辑财务报表
        $attach = new BuyerattachModel();
        $attach -> updateBuyerFinanceTableArr($data['base_info']['finance_attach'],'FINANCE',$data['buyer_id'],$data['created_by']);
        //公司人员组织架构
        $attach -> updateBuyerFinanceTableArr($data['base_info']['org_chart'],'ORGCHART',$data['buyer_id'],$data['created_by']);
        //业务信息
        $business = new BuyerBusinessModel();
        $data['business_info']['buyer_id']=$data['buyer_id'];
        $business->editBusiness($data['business_info']);
        //结算信息
        $data['settlement_info']['buyer_id']=$data['buyer_id'];
        $business->editSettlement($data['settlement_info']);
        //入网管理
        $net = new NetSubjectModel();
        $data['net_info']['buyer_id']=$data['buyer_id'];
        $net->editNetSubject($data['net_info']);
        //编辑联系人必填
//        $contact = new BuyercontactModel();
//        $contact -> updateBuyerContact($data['contact_info'],$data['buyer_id'],$data['created_by']);
        return true;
    }
    public function buyerTitleInfo($data){
        if(empty($data['buyer_id'])){
            return false;
        }
        $lang=isset($data['lang'])?$data['lang']:'zh';
        $info=$this->field('id as buyer_id,buyer_no,buyer_code,name as buyer_name,country_bn')->where(array('id'=>$data['buyer_id'],'deleted_flag'=>'N'))->find();
        if(empty($info)){
            return [];
        }
        $area=new CountryModel();
        $res=$area->getCountryAreaByBn($info['country_bn'],$lang);
        $info['area_name']=$res['area'];
        $info['country_name']=$res['country'];
        unset($info['country_bn']);
        return $info;
    }
    public function showBuyerInquiry($data){
        if(empty($data['buyer_id'])){
            return false;
        }
        $info=$this->field('id as buyer_id,buyer_no,buyer_code,name as buyer_name')->where(array('id'=>$data['buyer_id'],'deleted_flag'=>'N'))->find();
        if(empty($info)){
            $arr['info']=[];
            $arr['total_count']=0;
            $arr['page']=1;
            return $arr;
        }
        $page=!empty($data['page'])?$data['page']:1;
        $arr=$this->getInquiry($data['buyer_id'],$data['lang'],$page);
        foreach($arr['info'] as $k => &$v){
            $v['buyer_code']=$info['buyer_code'];
            $v['created_at']=substr($v['created_at'],0,16);
            switch ($v['status']){
                case 'DRAFT':
                    $v['status']='新建询单';
                    break;
                case 'REJECT_MARKET':
                    $v['status']='驳回市场';
                    break;
                case 'REJECT_CLOSE':
                    $v['status']='驳回市场关闭';
                    break;
                case 'BIZ_DISPATCHING':
                    $v['status']='事业部分单员';
                    break;
                case 'CC_DISPATCHING':
                    $v['status']='易瑞客户中心';
                    break;
                case 'BIZ_QUOTING':
                    $v['status']='事业部报价';
                    break;
                case 'REJECT_QUOTING':
                    $v['status']='事业部审核退回事业部报价';
                    break;
                case 'LOGI_DISPATCHING':
                    $v['status']='物流分单员';
                    break;
                case 'LOGI_QUOTING':
                    $v['status']='物流报价';
                    break;
                case 'LOGI_APPROVING':
                    $v['status']='物流审核';
                    break;
                case 'BIZ_APPROVING':
                    $v['status']='事业部核算';
                    break;
                case 'MARKET_APPROVING':
                    $v['status']='事业部审核';
                    break;
                case 'MARKET_CONFIRMING':
                    $v['status']='市场确认';
                    break;
                case 'QUOTE_SENT':
                    $v['status']='报价单已发出';
                    break;
                case 'INQUIRY_CLOSED':
                    $v['status']='报价单发送后关闭';
                    break;
            }
        }
        return $arr;
    }
    public function showBuyerOrder($data){
        if(empty($data['buyer_id'])){
            return false;
        }
        $info=$this->field('id as buyer_id,buyer_no,buyer_code,name as buyer_name')->where(array('id'=>$data['buyer_id'],'deleted_flag'=>'N'))->find();
        if(empty($info['buyer_code'])){
            $arr['info']=[];
            $arr['total_count']=0;
            $arr['page']=1;
            return $arr;
        }
        $page=!empty($data['page'])?$data['page']:1;
        $arr=$this->getOrder($info['buyer_code'],$page);
        return $arr;
    }
    public function getOrder($buyer_code,$page){
        $field=array(
            'id', //销售合同号
            'contract_no', //销售合同号
            'project_no', //项目号
            'inquiry_no', //询单号
            'signing_date', //订单签约日期
            'order_type', //订单类型
            'total_price', //合同总价
            'pay_status', //款项状态
            'status', //订单状态
            'process_progress' //流程进度
        );
        $pageSize=10;
        $offset=($page-1)*$pageSize;
        $total=$this->table('erui_new_order.order')
            ->field($field)
            ->where(array('crm_code'=>$buyer_code,'delete_flag'=>0))
            ->count();
        if($total==0){
            $arr['info']=[];
            $arr['total_count']=$total;
            $arr['page']=$page;
            return $arr;
        }
        $info=$this->table('erui_new_order.order')
            ->field($field)
            ->where(array('crm_code'=>$buyer_code,'delete_flag'=>0))
            ->order('signing_date desc')
            ->limit($offset,$pageSize)
            ->select();
        if(empty($info)){
            $info=[];
        }
        foreach($info as $k => &$v){
            if($v['order_type']==1){
                $v['order_type']='油气';
            }elseif($v['order_type']==2){
                $v['order_type']='非油气';
            }else{
                $v['order_type']='其他';  //null
            }
            if($v['pay_status']==2){
                $v['pay_status']='部分付款';
            }elseif($v['pay_status']==3){
                $v['pay_status']='收款完成';
            }else{
                $v['pay_status']='未付款'; //1
            }
            if($v['status']==2){
                $v['status']='未执行';
            }elseif($v['status']==3){
                $v['status']='执行中';
            }elseif($v['status']==4){
                $v['status']='完成';
            }else{
                $v['status']='待确认'; //1
            }
            if($v['process_progress']==2){
                $v['process_progress']='正常执行';
            }elseif($v['process_progress']==3){
                $v['process_progress']='采购中';
            }elseif($v['process_progress']==4){
                $v['process_progress']='已报检';
            }elseif($v['process_progress']==5){
                $v['process_progress']='质检中';
            }elseif($v['process_progress']==6){
                $v['process_progress']='已入库';
            }elseif($v['process_progress']==7){
                $v['process_progress']='出库质检';
            }elseif($v['process_progress']==8){
                $v['process_progress']='已出库';
            }elseif($v['process_progress']==9){
                $v['process_progress']='已发运';
            }else{
                $v['process_progress']='未执行';   //1
            }
        }
        $arr['info']=$info;
        $arr['total_count']=$total;
        $arr['page']=$page;
        return $arr;
    }
    public function getInquiry($buyer_id,$lang='zh',$page){
        $field='id as visit_id,serial_no,status,created_at';
        $field.=",(select name from erui_dict.country country where country.bn=country_bn and lang='$lang' and deleted_flag='N')  as country_name";
        $field.=',(select name from erui_sys.employee employee where id=now_agent_id and deleted_flag=\'N\') as operator';
        $pageSize=10;
        $offset=($page-1)*$pageSize;
        $count=$this->table('erui_rfq.inquiry')
            ->field($field)
            ->where(array('buyer_id'=>$buyer_id,'deleted_flag'=>'N'))
            ->count();
        if($count==0){
            $arr['total_count']=0;
            $arr['page']=1;
            $arr['info']=[];
        }
        $info=$this->table('erui_rfq.inquiry')
            ->field($field)
            ->where(array('buyer_id'=>$buyer_id,'deleted_flag'=>'N'))
            ->order('created_at desc')
            ->limit($offset,$pageSize)
            ->select();
        if(empty($info)){
            $info=[];
        }
        $arr['total_count']=$count;
        $arr['page']=$page;
        $arr['info']=$info;
        return $arr;
    }
}
