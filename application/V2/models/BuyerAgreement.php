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
    public function exportModel($sheetName,$tableheader,$data){
        //创建对象
        $excel = new PHPExcel();
        $objActSheet = $excel->getActiveSheet();
        $letter = range(A,Z);
        //设置当前的sheet
        $excel->setActiveSheetIndex(0);
        //设置sheet的name
        $objActSheet->setTitle($sheetName);
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
        $time = date('YmdHis');
        $objWriter->save($time.$sheetName.'.xlsx');    //文件保存
        //把导出的文件上传到文件服务器上
        $server = Yaf_Application::app()->getConfig()->myhost;
        $url = $server . '/V2/Uploadfile/upload';

        $data['tmp_name'] = $time.$sheetName.'.xlsx';
        $data['type'] = 'application/excel';
        $data['name'] = pathinfo($time.$sheetName.'.xlsx', PATHINFO_BASENAME);
        $fileId = postfile($data, $url);
        return $fileId;
    }
    /**
     * 数据excel导出
     * wangs
     */
    public function exportAgree($data){
        $tableheader = array('序号','框架执行单号','事业部','执行分公司','所属国家','客户名称','客户代码（CRM）','品名中文','数量/单位','项目金额（美元）','执行金额（美元）','项目开始执行时间','市场经办人','商务技术经办人');
        $arr = $this->getAgreeStatisData($data);
        $res = $this->exportModel('agreestatis',$tableheader,$arr);
        return $res;
    }
    //获取excel导出的数据
    public function getAgreeStatisData($data){
        $cond = '1=1';
        if(!empty($data['all_id'])){  //根据id导出excel
            $all_idStr = implode(',',$data['all_id']);
            $cond .= " and agree.id in ($all_idStr)";
        }
        //条件
        $totalCount = $this ->alias('agree')
            ->join('erui_buyer.buyer buyer on buyer.id=agree.buyer_id','left')
            -> where($cond)
            ->count();
        $fields = array(
            'buyer_id',
            'id',
            'execute_no',       //框架执行单号
            'org_id',           //事业部
            'execute_company',  //执行分公司
            'area_bn',          //所属地区
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
            ->select();
        $res = array(
            'info'=>$info,
            'totalCount'=>$totalCount
        );
        //整合数据
        $arr=array();
        foreach($res['info'] as $k => $v){
            $arr[$k]['id'] = $v['id'];    //序号
            $arr[$k]['execute_no'] = $v['execute_no'];    //框架执行单号
            $arr[$k]['org_name'] = $v['org_name'];    //事业部
            $arr[$k]['execute_company'] = $v['execute_company'];    //执行分公司
            $arr[$k]['area_bn'] = $v['area_bn'];    //所属地区
            $arr[$k]['buyer_name'] = $v['buyer_name'];    //客户名称
            $arr[$k]['buyer_code'] = $v['buyer_code'];    //客户代码（CRM）
            $arr[$k]['product_name'] = $v['product_name'];    //品名中文
            $arr[$k]['number'] = $v['number'].'/'.$v['unit'];    //数量/单位
            $arr[$k]['amount'] = $v['amount'];    //项目金额（美元）
            $arr[$k]['execute_amount'] = $v['amount'];    //执行金额（美元
            $arr[$k]['execute_start_at'] = $v['execute_start_at'];    //项目开始执行时间
            $arr[$k]['agent'] = $v['agent'];    //市场经办人
            $arr[$k]['technician'] = $v['technician'];    //商务技术经办人
        }
        return $arr;
    }
    //框架协议管理
    public function manageAgree($data){
        $cond = " 1=1";
        if(!empty($data['buyer_id'])){  //客户id
            $cond .= " and buyer_id='$data[buyer_id]'";
        }
        if(!empty($data['created_by'])){  //执行创建人
            $cond .= " and agree.created_by='$data[created_by]'";
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

        $page = 1;
        if(!empty($data['page']) && is_numeric($data['page']) && $data['page'] >0   ){    //事业部
            $page = ceil($data['page']);
        }
        $info = $this -> manageAgreeList($page,$cond);
        return $info;
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
        if($valid == false){
            return false;
        }
        //组装数据
        $arr = $this -> packageData($data);
        $exRes = $this -> showAgreeBrief($arr['execute_no']);
        if(!empty($exRes)){
            return false;
        }
        //添加
        $res = $this -> addAgree($arr);
        if($res){
            return $this -> getLastInsID();
        }
        return false;
    }
    //查询框架协议单号唯一
    public function showAgreeBrief($execute_no){
        return $this->where(array('execute_no'=>$execute_no))->find();
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
        $info = $this ->alias('agree')
            ->join('erui_buyer.agreement_attach attach on agree.id=attach.agreement_id','inner')
            ->join('erui_sys.org org on agree.org_id=org.id','left')
            ->field('agree.*,attach.attach_name,attach.attach_url,org.name as org_name')
            ->where(array('agree.execute_no'=>$execute_no,'attach.deleted_flag'=>'N'))
            ->find();
        $country = new CountryModel();
        $info['country_name'] = $country->getCountryByBn($info['country_bn'],'zh');
        return $info;
    }
    //添加数据
    public function addAgree($data){
        $res = $this -> add($data);
        if($res){
            return true;
        }
        return false;
    }
    //验证非空数据
    public function validData($data){
        //验证必要数据
        if(empty($data['buyer_id']) || empty($data['created_by']) || empty($data['buyer_code'])){
            return false;
        }
        if(!empty($data['token'])){
            unset($data['token']);
        }
        //限制数据的长度200
        foreach($data as $v){
            if(strlen($v) > 200){
                return false;
            }
        }
        $arr = array(
            'execute_no',   //框架执行单号
            'org_id',   //所属事业部
            'execute_company',  //执行分公司
            'country_bn',  //所属国家
//            'agent',    //市场经办人
            'technician',   //商务技术经办人
            'execute_start_at', //框架开始时间
            'execute_end_at',   //框架结束时间
            'amount',   //项目金额
        );
        foreach($arr as $v){
            if(empty($data[$v])){
                return false;
            }
        }
        if($data['execute_start_at'] >= $data['execute_end_at']){
            return false;
        }
        return true;
    }
    //框架协议编辑保存
    public function updateAgree($data){
        //验证
        $valid = $this -> validData($data);
        if($valid == false){
            return false;
        }
        //组装数据
        $arr = $this -> packageData($data);
        $exRes = $this -> showAgreeBrief($arr['execute_no']);
        if(empty($exRes)){
            return false;
        }
        //保存数据
        $res = $this ->where(array('id'=>$exRes['id']))-> save($arr);
        if($res){
            return $exRes['id'];
        }
        return false;
    }
}