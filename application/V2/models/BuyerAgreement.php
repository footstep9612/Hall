<?php
//框架协议wangs
class BuyerAgreementModel extends PublicModel
{
    protected  $dbName= 'erui_buyer';
    protected  $tableName= 'buyer_agreement';
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * sheet名称 $sheetName
     * execl导航头 $tableheader
     * execl导出的数据 $data
     * wangs
     */
    public function exportModel($excelName,$sheetName,$data){
        $tableheader = array('序号','框架执行单号','事业部','执行分公司','所属国家','客户名称','客户代码（CRM）','品名中文','数量/单位','项目金额（美元）','项目开始执行时间','项目结束执行时间','市场经办人','商务技术经办人');
        $excelDir = MYPATH.DS.'public'.DS.'tmp'.DS.'excelagree';
        if(!is_dir($excelDir)){
            mkdir($excelDir,0777,true);
        }
        //创建对象
        $excel = new PHPExcel();
        $objActSheet = $excel->getActiveSheet();
        $letter = range(A,Z);
        //设置当前的sheet
        $excel->setActiveSheetIndex(0);
        //设置sheet的name
        $objActSheet->setTitle($sheetName);
        $objActSheet->getStyle('B')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
        $objActSheet->getStyle('J')->getNumberFormat()->setFormatCode('0.00');
        //填充表头信息
        for($i = 0;$i < count($tableheader);$i++) {
            //单独设置D列宽度为15
            $objActSheet->getColumnDimension($letter[$i])->setWidth(20);
            $objActSheet->setCellValue("$letter[$i]1","$tableheader[$i]");
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
        for ($i = 2;$i <= count($data) + 1;$i++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $objActSheet->setCellValue("$letter[$j]$i","$value");
                $j++;
            }
        }
        //创建Excel输入对象
        $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $objWriter->save($excelDir.DS.$excelName.'.xlsx');    //文件保存
        return $excelDir.DS.$excelName.'.xlsx';
    }
    /**
     * 客户管理-框架协议数据excel导出入口
     * wangs
     */
    public function exportAgree($data){
        $cond = $this->getAgreeCond($data);
        if(!empty($data['page'])){
            $page = $data['page'];
        }else{
            $page = 1;
        }
        $excelPage=false;
        if(!empty($data['excelPage'])){
            if($data['excelPage']==true){
                $excelPage=true;
            }
        }
        $pageSize = 10;
        $offset = ($page-1)*$pageSize;
        $info = $this->getAgreeStatisData($cond,$offset,$pageSize,true,$excelPage);
        if(!is_array($info) || $info==false){
            return false;   //空数据
        }
        if(count($info)==1){
            $excelPath = $info[0];
            $arr['tmp_name'] = $excelPath;
            $arr['type'] = 'application/excel';
            $arr['name'] = pathinfo($excelPath,PATHINFO_BASENAME );
        }else{
            $excelDir = pathinfo($info[0],PATHINFO_DIRNAME );
            ZipHelper::zipDir($excelDir, $excelDir . '.zip');   //压缩文件
            $arr['tmp_name'] = $excelDir . '.zip';
            $arr['type'] = 'application/excel';
            $arr['name'] = pathinfo($excelDir . '.zip',PATHINFO_BASENAME );
        }
        //把导出的文件上传到文件服务器指定目录位置
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server . '/V2/Uploadfile/upload';
        $fileId = postfile($arr, $url);    //上传到fastDFSUrl访问地址,返回name和url
        //删除文件和目录
        if(file_exists($excelPath)){
//            unlink($excelPath); //删除文件
            ZipHelper::removeDir(dirname($excelPath));    //清除目录
        }
        if(file_exists($excelDir . '.zip')){
            unlink($excelDir . '.zip'); //删除压缩包
            ZipHelper::removeDir($excelDir);    //清除目录
        }
        if ($fileId) {

            return array('url' => $fastDFSServer . $fileId['url'] ,'name'=>$fileId['name']);
//            return $fileId;
        }


    }

    /**
     * @param $data
     * $excel bool true: excel 导出数据用; false:管理列表用
     * 框架协议首页列表的条件
     */
    public function getAgreeCond($data = []){
        $cond = ' 1=1';
        if($data['is_agree'] == true){ //展示列表
            $cond .= " and buyer_id=".$data['buyer_id']." and agree.created_by=".$data['created_by'];
        }   //统计展示数据
        if(!empty($data['all_id'])){  //根据id导出excel
            $all_idStr = implode(',',$data['all_id']);
            $cond .= " and agree.id in ($all_idStr)";
        }
        if(!empty($data['country_bn'])){  //所属地区----------国家
            $cond .= " and agree.country_bn='$data[country_bn]'";
        }
        if(!empty($data['execute_start_at'])){    //执行时间
            $cond .= " and execute_start_at='$data[execute_start_at]'";
        }
        if(!empty($data['org_id'])){    //事业部
            $cond .= " and org_id='$data[org_id]'";
        }
        if(!empty($data['buyer_name'])){  //客户名称
            $cond .= " and buyer.name like '%$data[buyer_name]%'";
        }
        if(!empty($data['buyer_code'])){  //客户代码
            $cond .= " and buyer.buyer_code like '%$data[buyer_code]%'";
        }
        if(!empty($data['execute_no'])){    //执行单号txt
            $cond .= " and execute_no like '%$data[execute_no]%'";
        }
        if(!empty($data['execute_company'])){   //执行分公司txt
            $cond .= " and execute_company like '%$data[execute_company]%'";
        }
        return $cond;
    }

    /**
     * @param $data框架写列表数据
     * @return array框架协议excel导出数据
     * wangs
     */
    public function packageAgreeStatisData($data){
        //整合数据
        $arr=array();
        foreach($data as $k => $v){
            $arr[$k]['id'] = $v['id'];    //序号
            $arr[$k]['execute_no'] = $v['execute_no'];    //框架执行单号
            $arr[$k]['org_name'] = $v['org_name'];    //事业部
            $arr[$k]['execute_company'] = $v['execute_company'];    //执行分公司
            $arr[$k]['country_name'] = $v['country_name'];    //所属地区
            $arr[$k]['buyer_name'] = $v['buyer_name'];    //客户名称
            $arr[$k]['buyer_code'] = $v['buyer_code'];    //客户代码（CRM）
            $arr[$k]['product_name'] = $v['product_name'];    //品名中文
            $arr[$k]['number'] = $v['number'].'/'.$v['unit'];    //数量/单位
            $arr[$k]['amount'] = $v['amount'];    //项目金额（美元）
            $arr[$k]['execute_start_at'] = $v['execute_start_at'];    //项目开始执行时间
            $arr[$k]['execute_end_at'] = $v['execute_end_at'];    //项目结束执行时间
            $arr[$k]['agent'] = $v['agent'];    //市场经办人
            $arr[$k]['technician'] = $v['technician'];    //商务技术经办人
        }
        return $arr;
    }

    /**
     * @param $data框架协议列表展示的条件数据
     * @param int $i 偏移量/页码
     * @param int $pageSize 每页的数据条数
     * @param bool $excel true:导出excel false:框架协议列表展示
     * @param bool $excelPage true:导出当前页excel false:导出所有数据excel
     * @return array|bool
     */
    public function getAgreeStatisData($cond,$offset=0,$pageSize=10,$excel=true,$excelPage=true){
        //条件
        $totalCount = $this ->alias('agree')
            ->join('erui_buyer.buyer buyer on buyer.id=agree.buyer_id','left')
            -> where($cond)
            ->count();
        if($totalCount <= 0){
            return false;   //数据为空
        }
        $fields = array(
            'buyer_id',
            'id',
            'execute_no',       //框架执行单号
            'org_id',           //事业部
            'execute_company',  //执行分公司
            'country_bn',          //所属地区
            'product_name',     //品名中文
            'number',           // 数量
            'unit',             //单位
            'amount',           //项目金额
            'execute_start_at', //项目开始执行时间
            'execute_end_at', //项目结束执行时间
            'agent',            //市场经办人
            'technician'        //商务技术经办人
        );  //获取字段start
        $field = 'buyer.buyer_code,buyer.name as buyer_name,org.name as org_name';
        foreach($fields as $v){
            $field .= ',agree.'.$v;
        }   //获取字段end
        $i = 1;
        $length = 100;
        if($excelPage==true){  //导出当前页数据长度
            $length = $pageSize;
        }
        do{
            $info = $this ->alias('agree')
                ->field($field)
                ->join('erui_buyer.buyer buyer on buyer.id=agree.buyer_id','left')
                ->join('erui_sys.org org on agree.org_id=org.id','left')
                ->where($cond)
                ->order('agree.id desc')
                ->limit($offset,$length)
                ->select();
            if(empty($info)){
                return false;
            }
            $country = new CountryModel();
            $employee=new EmployeeModel();
            foreach($info as $k => $v){
                $info[$k]['country_name'] = $country->getCountryByBn($v['country_bn'],'zh');
                if(!empty($v['agent'])){
                    if(is_numeric($v['agent'])){
                        $em_name=$employee->getNameByid($v['agent']);
                        $info[$k]['agent']=$em_name['name'];
                    }
                }
                if(!empty($v['technician'])){
                    if(is_numeric($v['technician'])){
                        $tech_name=$employee->getNameByid($v['technician']);
                        $info[$k]['technician']=$tech_name['name'];
                    }
                }
            }
            if($excel==false){
                return array('info'=>$info,'totalCount'=>$totalCount);
            }
            $arr = $this->packageAgreeStatisData($info);    //整合框架协议excel导出的数据
            $excelName = 'agree'.($i);
            $sheetName = '框架协议统计';
            $dir = $this->exportModel($excelName,$sheetName,$arr);    //excel导入模型
            $result[]= $dir;
            $totalCount -= $length; //导出数据剩余数据量
            $offset += $length; //导出数据偏移量
            $i++;
            if($excelPage==true){
                break;  //当前页数据
            }
        }while($totalCount>0);
            return $result;
    }
    //框架协议管理入口
    public function manageAgree($data){
        $cond = $this->getAgreeCond($data);
        if(!empty($data['page'])){
            $page = $data['page'];
        }else{
            $page = 1;
        }
        $pageSize = 10;
        $offset = ($page-1)*$pageSize;
        $info = $this -> getAgreeStatisData($cond,$offset,$pageSize,$excel=false,true);
        $totalPage = ceil($info['totalCount']/$pageSize);
        $arr = array(
            'page'=>$page,
            'totalPage'=>$totalPage,
            'totalCount'=>$info['totalCount'],
            'info'=>$info['info'],
        );
        return $arr;
    }
    //框架协议管理数据列表
    public function manageAgreeList($page=1,$cond){
        //条件
        $totalCount = $this ->alias('agree')
            ->join('erui_buyer.buyer buyer on buyer.id=agree.buyer_id','left')
            -> where($cond)
            ->count();
        $pageSize = 10;
        $totalPage = ceil($totalCount/$pageSize);
        if($page > $totalPage && $totalPage > 0){
            $page = $totalPage;
        }
        $offset = ($page-1)*$pageSize;
        $fields = array(
            'buyer_id',
            'id',
            'execute_no',       //框架执行单号
            'org_id',           //事业部
            'execute_company',  //执行分公司
            'country_bn',          //所属国家
//            'name',       //客户名称  buyer
//            'buyer_code',       //客户代码  buyer
            'product_name',     //品名中文
            'number',           // 数量
            'unit',             //单位
            'amount',           //项目金额
            'execute_start_at', //项目开始执行时间
            'agent',            //市场经办人
            'technician'        //商务技术经办人
        );
        $field = 'buyer.buyer_code,buyer.name as buyer_name,org.name as org_name';
        foreach($fields as $v){
            $field .= ',agree.'.$v;
        }
        $info = $this ->alias('agree')
            ->field($field)
            ->join('erui_buyer.buyer buyer on buyer.id=agree.buyer_id','left')
            ->join('erui_sys.org org on agree.org_id=org.id','left')
            ->where($cond)
            ->order('agree.id desc')
            ->limit($offset,$pageSize)
            ->select();
        $country = new CountryModel();
        foreach($info as $k => $v){
            $info[$k]['country_name'] = $country->getCountryByBn($v['country_bn'],'zh');
        }
        $arr = array(
            'info'=>$info,
            'page'=>$page,
            'totalPage'=>$totalPage,
            'totalCount'=>$totalCount
        );
        return $arr;
    }
    //组装有效数据数据
    public function packageData($data){
        $arr = array(
            'buyer_id' => $data['buyer_id'],
            'buyer_code' => $data['buyer_code'],
            'created_by' => $data['created_by'],
            'created_at' => date('Y-m-d H:i:s'),
            'execute_no' => $data['execute_no'],
            'org_id' => $data['org_id'],
            'execute_company' => $data['execute_company'],
            'country_bn' => $data['country_bn'],
            'agent' => $data['agent'],
            'technician' => $data['technician'],
            'execute_start_at' => $data['execute_start_at'],
            'execute_end_at' => $data['execute_end_at'],
            'amount' => $data['amount']
        );
        if(!empty($data['product_name'])){  //品名中文
            $arr['product_name'] = $data['product_name'];
        }
        if(!empty($data['number'])){  //数量
            $arr['number'] = $data['number'];
        }
        if(!empty($data['unit'])){  //单位
            $arr['unit'] = $data['unit'];
        }
        if(!empty($data['payment_mode'])){  //汇款方式
            $arr['payment_mode'] = $data['payment_mode'];
        }
        return $arr;
    }
    //创建框架协议
    public function createAgree($data){
        //验证
        $valid = $this -> validData($data);
        if($valid !== true){
            return $valid;
        }
        //组装数据
        $arr = $this -> packageData($data);
        $exRes = $this -> showAgreeBrief($arr['execute_no']);
        if(!empty($exRes)){
            return 'exsit';
        }
        //添加
        $res = $this -> addAgree($arr);
        if($res){
            return $res;
        }
        return false;
    }
    //查询框架协议单号唯一添加
    public function showAgreeBrief($execute_no){
        return $this->where(array('execute_no'=>$execute_no))->find();
    }
    //查询框架协议单号唯一
    public function showAgreeBriefUpdate($id,$execute_no){
        return $this->where(array('id'=>$id,'execute_no'=>$execute_no))->find();
    }
    //查看框架协议详情
    public function showAgreeDesc($data){
        if(empty($data['execute_no'])){
            return false;
        }
        $info = $this -> showAgree($data['execute_no']);
        if(empty($info)){
            return false;
        }
        return $info;
    }
    //按单号查看数据及附件信息详情-------
    public function showAgree($execute_no){
        $agree = $this->where(array('execute_no'=>$execute_no))->find();
        if(!empty($agree)){
            //附件
            $id = $agree['id'];
            $attach = new AgreementAttachModel();
            $attachInfo = $attach->field('id,attach_name,attach_url')->where(array('agreement_id'=>$id,'deleted_flag'=>'N'))->select();
            $agree['agree_attach'] = $attachInfo;
            //组织
//            $org = new OrgModel();
//            $orgInfo = $org->getNameById($agree['org_id']);
//            $agree['org_name'] = $orgInfo;
            //
//            $country = new CountryModel();
//            $countryInfo = $country->getCountryByBn($agree['country_bn'],'zh');
//            $agree['country_name'] = $countryInfo;
        }
        //技术人员名称
        $employee=new EmployeeModel();
        if(!empty($agree['agent'])){
            if(is_numeric($agree['agent'])){
                $em_name=$employee->getNameByid($agree['agent']);
            }else{
                $em_name=$employee->getIdByName($agree['agent']);
            }
            $agree['agent']=$em_name;
        }
        if(!empty($agree['technician'])){
            if(is_numeric($agree['technician'])){
                $tech_name=$employee->getNameByid($agree['technician']);
            }else{
                $tech_name=$employee->getIdByName($agree['technician']);
            }
            $agree['technician']=$tech_name;
        }
        return $agree;
    }
    //添加数据
    public function addAgree($data){
        $res = $this -> add($data);
        if($res){
            return $res;
        }
        return false;
    }
    //验证非空数据
    public function validData($data){
        //验证必要数据
        $arr = array(
            'buyer_id'=>'客户id',
            'buyer_code'=>'客户代码',
            'execute_no'=>'框架执行单号',
            'org_id'=>'所属事业部',
            'execute_company'=>'执行分公司',
            'country_bn'=>'所属国家',
//            'agent',    //市场经办人
            'technician'=>'商务技术经办人',
            'execute_start_at'=>'框架开始时间',
            'execute_end_at'=>'框架结束时间',
            'amount'=>'项目金额'
        );
        foreach($arr as $k => $v){
            if(empty($data[$k])){
                return $v;
            }
        }
        if($data['execute_start_at'] > $data['execute_end_at']){
            return '结束时间大于等于开始时间';
        }
        return true;
    }
    //框架协议编辑保存
    public function updateAgree($data){
        //验证
        $valid = $this -> validData($data);
        if($valid !== true){
            return $valid;
        }
        //组装数据
        $arr = $this -> packageData($data);
        $exRes = $this -> showAgreeBriefUpdate($data['id'],$arr['execute_no']);
        if($exRes){
            //保存数据
            unset($arr['execute_no']);
            $this ->where(array('id'=>$exRes['id']))-> save($arr);
            return $exRes['id'];
        }else{
            return 'no_error';
        }
    }
}