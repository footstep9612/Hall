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
    protected $tableName = 'customer';

//    protected $autoCheckFields = false;
    public function __construct() {
        parent::__construct();
    }
    //cond
    public function getBuyerStatisListCond($data,$falg=true,$filter=false){
//        $data=array(
//            'created_by'=>37850,
//            'admin'=>array(
//                'role'=>array(
//                    'hh'
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

    /*
     * 根据用户姓 获取用户ID
     * @param string $BuyerName // 客户名称
     * @return mix
     * @author  
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

 
    public function getBuyerBakInfo($data){
        $lang=$data['lang'];
        $need=$this->table('erui_buyer.buyer_bak')
            ->where(array('lang'=>$lang))
            ->group('buyer_id')
            ->select();
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





}
