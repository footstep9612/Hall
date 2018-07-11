<?php
/**
 *
 */
class CustomerGradeModel extends PublicModel {

    protected $dbName = 'erui_buyer';
    protected $tableName = 'customer_grade';

    public function __construct() {
        parent::__construct();
    }
    public function buyerGradeList($data){
        $lang=$data['lang'];
        if(empty($data['buyer_id'])){
            return false;
        }
        $field='';
        $fieldArr=array(
            'id',   //
            'type',   //
            'customer_grade',   //客户等级
            'created_by',   //创建人
            'created_at',   //创建时间
            'updated_at',   //更新时间
            'checked_by',   //客户管理员
            'checked_at',   //审核时间
            'status',   //状态
        );
        foreach($fieldArr as $k => $v){
            $field.='grade.'.$v.',';
        }
        $field.="(select name from erui_sys.employee where id=grade.created_by and deleted_flag='N') as  created_name";
        $field.=",(select name from erui_sys.employee where id=grade.checked_by and deleted_flag='N') as customer_admin";
        $cond=array(
            'grade.buyer_id'=>$data['buyer_id'],
            'grade.deleted_flag'=>'N'
        );
        if(in_array('area-customers',$data['role'])){   //地区
            $admin_area=1;
//            $cond['grade.status']=[
//                    'in',
//                    [1,2,3,4,5]
//                ];
        }
        if(in_array('201711242',$data['role'])){    //国家
            $admin_country=1;
        }
        if(in_array('A001',$data['role'])){    //经办人
            $admin_agent=1;
        }
//        print_r($cond);die;
        $info=$this->alias('grade')
            ->field($field)
            ->where($cond)
            ->order('grade.id desc')
            ->select();
        if(empty($info)){
            return [];
        }
        $check=false;    //地区-国家
        $show=false;    //查看
        $edit=false;    //编辑
        $delete=false;    //删除
        $submit=false;    //提交
        foreach($info as $k => &$v){
            unset($v['created_by']);
            if($v['status']==0){
                $v['status']=$lang=='zh'?'新建':'NEW';
                if($admin_area===1 && $admin_country===1 && $admin_agent===1){  //地区-国家-经办人
                    $v['check']=true;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=true;
                }elseif($admin_area===1 && $admin_country!==1 && $admin_agent===1){ //地区
                    $v['check']=true;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=true;
                }elseif($admin_area!==1 && $admin_country===1 && $admin_agent===1){ //国家
                    $v['check']=true;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=true;
                }elseif($admin_area!==1 || $admin_country!==1){
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=true;
                }
            }else if($v['status']==1){
                $v['status']=$lang=='zh'?'审核中(国家)':'CHECKING(Country)';
                if($admin_area===1 && $admin_country===1 && $admin_agent===1){  //地区-国家-经办人
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=false;
                }elseif($admin_area===1 && $admin_country!==1 && $admin_agent===1){ //地区
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=false;
                }elseif($admin_area!==1 && $admin_country===1 && $admin_agent===1){ //国家
                    $v['check']=true;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=false;
                }elseif($admin_area!==1 || $admin_country!==1){
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=false;
                }
            }else if($v['status']==2){
                $v['status']=$lang=='zh'?'审核中(地区)':'CHECKING(Area)';
                if($admin_area===1 && $admin_country===1 && $admin_agent===1){  //地区-国家-经办人
                    $v['check']=true;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=false;
                }elseif($admin_area===1 && $admin_country!==1 && $admin_agent===1){ //地区
                    $v['check']=true;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=false;
                }elseif($admin_area!==1 && $admin_country===1 && $admin_agent===1){ //国家
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=false;
                }elseif($admin_area!==1 || $admin_country!==1){
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=false;
                }
            }else if($v['status']==3){
                $v['status']=$lang=='zh'?'审核通过':'PASS';
                if($admin_agent===1){
                    $v['check']=true;   $v['change']=true;
                    $v['show']=true;    $v['edit']=false;  $v['delete']=false;    $v['submit']=false;
                }else{
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=false;  $v['delete']=false;    $v['submit']=false;
                }
            }else if($v['status']==4){
                $v['status']=$lang=='zh'?'驳回(国家)':'REJECTED(Country)';
                if($admin_area===1 && $admin_country===1 && $admin_agent===1){  //地区-国家-经办人
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=true;
                }elseif($admin_area===1 && $admin_country!==1 && $admin_agent===1){ //地区
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=true;
                }elseif($admin_area!==1 && $admin_country===1 && $admin_agent===1){ //国家
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=true;
                }elseif($admin_area!==1 || $admin_country!==1){
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=true;
                }
            }else if($v['status']==5){
                $v['status']=$lang=='zh'?'驳回(地区)':'REJECTED(Area)';
                if($admin_area===1 && $admin_country===1 && $admin_agent===1){  //地区-国家-经办人
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=true;
                }elseif($admin_area===1 && $admin_country!==1 && $admin_agent===1){ //地区
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=true;
                }elseif($admin_area!==1 && $admin_country===1 && $admin_agent===1){ //国家
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=true;
                }elseif($admin_area!==1 || $admin_country!==1){
                    $v['check']=false;
                    $v['show']=true;    $v['edit']=true;  $v['delete']=true;    $v['submit']=true;
                }
            }
            if($lang=='zh'){
                $v['customer_grade']=mb_substr($v['customer_grade'],0,1);
            }else{
                $v['customer_grade']=mb_substr($v['customer_grade'],0,1);
            }
        }
        return $info;
    }
    //获取客户分级数据
    public function exportGradeData($data){
        if(!empty($data['buyer_id'])){
            $cond=array('buyer_id'=>$data['buyer_id'],'deleted_flag'=>'N');
        }else{
            $cond=array('deleted_flag'=>'N');
        }
        $lang=$data['lang'];
        $fieldArr=array(
            'id', //
            'buyer_id', //
            'type', //
            'amount', //客户历史成单金额
            'amount_score', //
            'position', //易瑞产品采购量占客户总需求量地位
            'position_score', //
            'year_keep', //连续N年及以上履约状况良好
            'keep_score', //
            're_purchase', //年复购次数
            're_score', //
            'credit_grade', //客户资信等级
            'credit_score', //
            'purchase', //零配件年采购额
            'purchase_score', //
            'enterprise', //    企业性质
            'enterprise_score', //
            'income', //营业收入
            'income_score', //
            'scale', //资产规模
            'scale_score', //
            'final_score', //综合分值
            'customer_grade' //客户等级
        );
        $info=$this->alias('grade')
            ->field($fieldArr)
            ->where($cond)
            ->order('grade.id desc')
            ->select();
        if(empty($info)){
            return [];
        }
        $area=new CountryModel();
        foreach($info as $k => &$v){
            if($v['type']==1){
                $v['type']=$lang=='zh'?'老客户':'Old customer';
            }else{
                $v['type']=$lang=='zh'?'潜在客户':'Potential customer';
            }
            $buyerInfo=$this->table('erui_buyer.buyer')->field('id,name,buyer_no,buyer_code,buyer_type,country_bn')
                ->where(array('id'=>$v['buyer_id'],'deleted_flag'=>'N'))->find();
            $info[$k]['name']=$buyerInfo['name'];
            $info[$k]['buyer_code']=$buyerInfo['buyer_code'];
            $info[$k]['buyer_no']=$buyerInfo['buyer_no'];
            if(!empty($buyerInfo['country_bn'])){
                $areaInfo=$area->getCountryAreaByBn($buyerInfo['country_bn'],$lang);
                $info[$k]['area_country']=$areaInfo['area'].'-'.$areaInfo['country'];
            }else{
                $info[$k]['area_country']='';
            }
            if(!empty($buyerInfo['buyer_type'])){
                $fieldType=$lang=='zh'?'name':'en as name';
                $typeInfo=$this->table('erui_config.buyer_type')->field($fieldType)
                    ->where(array('id'=>$buyerInfo['buyer_type'],'deleted_flag'=>'N'))->find();
                $info[$k]['customer_type']=$typeInfo['name'];
            }else{
                $info[$k]['customer_type']='';
            }
        }
        return $info;
    }
    //整理客户分级数据
    public function packExcelAllData($data){
        $info=[];
        foreach($data as $k => $v){
            $info[$k]['serial']=$k+1;
            $info[$k]['area_country']=$v['area_country'];
            $info[$k]['name']=$v['name'];
            $info[$k]['buyer_no']=$v['buyer_no'];
            $info[$k]['buyer_code']=$v['buyer_code'];

            $info[$k]['type']=$v['type'];
            $info[$k]['amount']=$v['amount'];
            $info[$k]['amount_score']=$v['amount_score'];
            $info[$k]['position']=$v['position'];
            $info[$k]['position_score']=$v['position_score'];
            $info[$k]['year_keep']=$v['year_keep'];
            $info[$k]['keep_score']=$v['keep_score'];
            $info[$k]['re_purchase']=$v['re_purchase'];
            $info[$k]['re_score']=$v['re_score'];
            $info[$k]['credit_grade']=$v['credit_grade'];
            $info[$k]['credit_score']=$v['credit_score'];
            $info[$k]['purchase']=$v['purchase'];
            $info[$k]['purchase_score']=$v['purchase_score'];
            $info[$k]['enterprise']=$v['enterprise'];
            $info[$k]['enterprise_score']=$v['enterprise_score'];
            $info[$k]['income']=$v['income'];
            $info[$k]['income_score']=$v['income_score'];
            $info[$k]['scale']=$v['scale'];
            $info[$k]['scale_score']=$v['scale_score'];

            $info[$k]['final_score']=$v['final_score'];
            $info[$k]['customer_grade']=$v['customer_grade'];
        }
        return $info;
    }
    //导出模板
    public function exportMode($data,$lang){
        set_time_limit(0);  # 设置执行时间最大值
        //存放excel文件目录
        $excelDir = MYPATH . DS . 'public' . DS . 'tmp' . DS . 'customer';
        if (!is_dir($excelDir)) {
            mkdir($excelDir, 0777, true);
        }
        $sheetName='customer';
        if($lang=='zh'){
            $tableheader = array(
                '序号','地区-国家','客户名称','客户编码','客户代码','客户类型',
                '客户历史成单金额(万美元)', '所占分值', '易瑞产品采购量占客户总需求量地位', '所占分值',
                '连续N年及以上履约状况良好', '所占分值','年复购次数','所占分值',
                '客户资信等级', '所占分值','零配件年采购额(万美元)','所占分值',
                '企业性质', '所占分值','营业收入(万美元)','所占分值',
                '资产规模(万美元)', '所占分值', '客户综合分值','客户等级'
            );
        }else{
            $tableheader = array(
                'Serial','Area-Country','Customer name','Customer No.','Customer Code','Customer type',
                'Customer Historic Order Amount(10000$)', 'Score',
                'Erui products (not limited to spare parts) purchase volume accounts for the total customer demand', 'Score',
                'The years that performance is in good condition', 'Score',
                'Annual repurchase times','Score',

                'Customer credit rating', 'Score','Annual purchase of spare parts(10000$)','Score',
                'enterprise property', 'Score','operating revenue(10000$)','Score',
                'asset size(10000$)', 'Score', 'Customer comprehensive score','Customer grade'
            );
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
    public function exportExcelGrade($data){
        $lang=$data['lang'];
        $info=$this->exportGradeData($data);    //获取客户分级数据
        if(empty($info)){
            return [];
        }
        $packData=$this->packExcelAllData($info);
        $excelFile=$this->exportMode($packData,$lang);   //模板
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
        }
        if ($fileId) {
            return array('url' =>$fileId['url'], 'name' => $fileId['name']);
        }
    }


    private function oldBuyer($data){
        $field=array(
//            'buyer_id',
            'amount'=>'客户历史成单金额', //客户历史成单金额
            'amount_score'=>'客户历史成单金额',
            'position'=>'易瑞产品采购量占客户总需求量地位', //易瑞产品采购量占客户总需求量地位
            'position_score'=>'易瑞产品采购量占客户总需求量地位分值',
            'year_keep'=>'连续N年及以上履约状况良好',   //连续N年及以上履约状况良好
            'keep_score'=>'连续N年及以上履约状况良好分值',
            're_purchase'=>'年复购次数',   //年复购次数
            're_score'=>'年复购次数分值',
            'final_score'=>'综合分值',  //综合分值
            'customer_grade'=>'客户等级',   //客户等级
            'flag'=>'提交/保存'  //提交 flag=1 保存 flag=0
        );
        $arr=['type'=>1];
        foreach($field as $k => $v){
//            if(empty($data[$k])){
//                return $v;
//            }
            $arr[$k]=$data[$k];
//            if(!empty($data[$v])){
//            }
        }
        return $arr;
    }
    private function newBuyer($data){
        $arr['type']=0;
        $field=array(
//            'buyer_id',
            'credit_grade'=>'客户资信等级', //客户资信等级
            'credit_score'=>'客户资信等级分值',
            'purchase'=>'零配件年采购额', //零配件年采购额
            'purchase_score'=>'零配件年采购额分值',
            'enterprise'=>'企业性质',   //企业性质
            'enterprise_score'=>'企业性质分值',
            'income'=>'营业收入',   //营业收入
            'income_score'=>'营业收入分值',
            'scale'=>'资产规模',    //资产规模
            'scale_score'=>'资产规模分值',
            'final_score'=>'综合分值',  //综合分值
            'customer_grade'=>'客户等级',   //客户等级
            'flag'=>'提交/保存'  //提交 flag=1 保存 flag=0
        );
        $arr=['type'=>0];
        foreach($field as $k => $v){
//            if(empty($data[$k])){
//                return $v;
//            }
            $arr[$k]=$data[$k];
//            if(!empty($data[$v])){
//            }else{
//                $arr[$v]='';
//            }
        }
        return $arr;
    }
    public function addGrade($data){
        if(empty($data['buyer_id'])){
            return false;
        }
        if($data['type']==1){
            $arr=$this->oldBuyer($data);    //老客户
        }else{
            $arr=$this->newBuyer($data);    //潜在客户
        }
        if(!is_array($arr)){
            return $arr;
        }
        $arr['buyer_id']=$data['buyer_id'];
        $arr['status']=$data['flag']==1?1:0;
        $arr['created_by']=$data['created_by'];
        $arr['created_at']=date('Y-m-d H:i:s');
        $arr['customer_grade']=mb_substr($arr['customer_grade'],0,1);
        $res=$this->add($arr);
        if($res){
            return true;
        }
        return false;
    }
    public function saveGrade($data){
        if(empty($data['id'])){
            return false;
        }
        if($data['type']==1){
            $arr=$this->oldBuyer($data);    //老客户
        }else{
            $arr=$this->newBuyer($data);    //潜在客户
        }
        if(!is_array($arr)){
            return $arr;
        }
        unset($arr['type']);
        unset($arr['buyer_id']);
        if($data['flag']==1){
            $arr['status']=1;
        }else{
            $arr['status']=0;
        }
        $arr['updated_by']=$data['created_by'];
        $arr['updated_at']=date('Y-m-d H:i:s');
        $arr['customer_grade']=mb_substr($arr['customer_grade'],0,1);
        $this->where(array('id'=>$data['id'],'deleted_flag'=>'N'))->save($arr);
        return true;
    }
    public function delGrade($data){
        if(empty($data['id'])){
            return false;
        }
        $cond=array('id'=>$data['id']);
        $save=array(
            'deleted_flag'=>'Y',
            'deleted_by'=>$data['created_by'],
            'deleted_at'=>date('Y-m-d H:i:s')
        );
        $res=$this->where($cond)->save($save);
        if($res){
            return true;
        }
        return false;
    }
    public function submitGrade($data){
        if(empty($data['id'])){
            return false;
        }
        $cond=array('id'=>$data['id'],'status'=>0,'deleted_flag'=>'N');
        $res=$this->where($cond)->save(array('status'=>1));
        if($res){
            return true;
        }
        return false;
    }
    public function infoGrade($data){
        $lang=$data['lang'];
        if(empty($data['id'])){
            return false;
        }
        $field=array(
            'id',
            'buyer_id',
            'type',
            'amount',
            'amount_score',
            'position',
            'position_score',
            'year_keep',
            'keep_score',
            're_purchase',
            're_score',
            'credit_grade',
            'credit_score',
            'purchase',
            'purchase_score',
            'enterprise',
            'enterprise_score',
            'income',
            'income_score',
            'scale',
            'scale_score',
            'final_score',
            'customer_grade',
            'flag',
            'status'
        );
        $info=$this->field($field)->where(array('id'=>$data['id']))->find();
        $info['amount']=floatval($info['amount']);
        $info['re_purchase']=floatval($info['re_purchase']);

        $info['purchase']=floatval($info['purchase']);
        $info['income']=floatval($info['income']);
        $info['scale']=floatval($info['scale']);
        if($lang=='zh'){
            $info['customer_grade']=mb_substr($info['customer_grade'],0,1).' 级';
        }else{
            $info['customer_grade']=mb_substr($info['customer_grade'],0,1).' LEVEL';
        }

        return $info;
    }
    public function checkedGrade($data){
        if(empty($data['status']) || empty($data['id'])){
            return false;
        }
        $info=$this->field('status')->where(array('id'=>$data['id'],'deleted_flag'=>'N'))->find();
        $status=$info['status'];
        //状态: 0,新建;1,审核中(国家); 2,审核中(地区),3,审核通过,4,驳回(国家),5,地区(驳回)
        if($data['status']==1 && $status==0){
            $arr['status']=1;
        }elseif($data['status']==2 && $status==1){
            $arr['status']=2;
        }elseif($data['status']==3 && $status==2){
            $arr['status']=3;
        }elseif($data['status']==4 && $status==1){
            $arr['status']=4;
        }elseif($data['status']==5 && $status==2){
            $arr['status']=5;
        }else{
            return 'error';
        }
        $arr['checked_by']=$data['created_by'];
        $arr['checked_at']=date('Y-m-d H:i:s');
        $cond=array(
            'id'=>$data['id'],
            'deleted_flag'=>'N'
        );
        $res=$this->where($cond)->save($arr);
        if($res){
            return true;
        }else{
            return false;
        }
    }
    public function changeGrade($data){
        if(empty($data['id'])){
            return false;
        }

        $arr['status']=0;
        $cond=array(
            'id'=>$data['id'],
            'deleted_flag'=>'N',
            'status'=>2,
        );
        $res=$this->where($cond)->save($arr);
        if($res){
            return true;
        }
    }
    //客户历史成单金额1
    public function amount($data){
        $arr=[];
        $arr['amount']=$data['amount'];
        $arr['amount_score']=100;
        $arr['final_score']=50;
        $arr['customer_grade']=A;
        return $arr;
    }
    //易瑞产品采购量占客户总需求量地位2
    public function position($data){
        $arr=[];
        $arr['position']=$data['position'];
        $arr['position_score']=100;
        $arr['final_score']=60;
        $arr['customer_grade']=B;
        return $arr;
    }
    //连续N年及以上履约状况良好3
    public function yearKeep($data){
        $arr=[];
        $arr['year_keep']=$data['year_keep'];
        $arr['keep_score']=100;
        $arr['final_score']=70;
        $arr['customer_grade']=C;
        return $arr;
    }
    //年复购此时4
    public function repurchase($data){
        $arr=[];
        $arr['re_purchase']=$data['re_purchase'];
        $arr['re_score']=100;
        $arr['final_score']=80;
        $arr['customer_grade']=S;
        return $arr;
    }
    //客户资信等级5
    public function creditGrade($data){
        $arr=[];
        $arr['credit_grade']=$data['credit_grade'];
        $arr['credit_score']=100;
        $arr['final_score']=50;
        $arr['customer_grade']=A;
        return $arr;
    }
    //零配件年采购额6
    public function purchase($data){
        $arr=[];
        $arr['purchase']=$data['purchase'];
        $arr['purchase_score']=100;
        $arr['final_score']=70;
        $arr['customer_grade']=B;
        return $arr;
    }
    //企业性质7
    public function enterprise($data){
        $arr=[];
        $arr['enterprise']=$data['enterprise'];
        $arr['enterprise_score']=100;
        $arr['final_score']=80;
        $arr['customer_grade']=C;
        return $arr;
    }
    //营业收入8
    public function income($data){
        $arr=[];
        $arr['income']=$data['income'];
        $arr['income_score']=100;
        $arr['final_score']=90;
        $arr['customer_grade']=D;
        return $arr;
    }
    //资产规模9
    public function scale($data){
        $arr=[];
        $arr['scale']=$data['scale'];
        $arr['scale_score']=100;
        $arr['final_score']=100;
        $arr['customer_grade']=S;
        return $arr;
    }
    //综合分值&客户等级
    public function customerGrade($data){
        $arr=[];
//        $arr['final_score']=$data['final_score'];
//        $arr['customer_grade']=$data['customer_grade'];
        $arr['final_score']=100;
        $arr['customer_grade']=S;
        return $arr;
    }
}
