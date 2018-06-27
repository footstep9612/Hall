<?php

/**
 * 导出相关接口
 * @desc   DownloadController
 * @Author 买买提
 */
class DownloadController extends PublicController {


    public function __init() {
        parent::init();
    }
    private function crmUserRole($user_id){
        $role=new RoleUserModel();
        $arr=$role->crmGetUserRole($user_id);
        if(in_array('crm市场专员',$arr)){
            $admin=1;   //市场专员
        }else{
            $admin=0;
        }
        return $admin;
    }
    /**
     * 通用边框
     * @var array
     */
    private $borderStyle = [
            'borders' => [
                'outline' => [
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => ['rgb' => '333333'],
                ],
            ],
    ];

    /**
     * 会员列表
     */
    public function buyerListAction()
    {
        set_time_limit(0);
        $data = json_decode(file_get_contents("php://input"), true);
        $admin=$this->crmUserRole($this->user['id']);   //=1市场专员
        $where = [];

        if($admin!=1){
            if (!empty($data['country_bn'])) {
                $pieces = explode(",", $data['country_bn']);
                for ($i = 0; $i < count($pieces); $i++) {
                    $where['country_bn'] = $where['country_bn'] . "'" . $pieces[$i] . "',";
                }
                $where['country_bn'] = rtrim($where['country_bn'], ",");
            }
        }
        $buyerList = $this->getBuyerList($where);
        $this->_setCountry($buyerList, 'country');
        if (!$buyerList){
            $this->jsonReturn([
                'code' => -104,
                'message' => '没有会员数据!'
            ]);
        }
        $localFile = $this->createExcelObjWithData($buyerList);
        $compressedFile = $this->compresFile($localFile);
        $remoteFile = $this->upload2FileServer($compressedFile);
        if (!$remoteFile['code']=='1'){
            $this->jsonReturn([
                'code' => -104,
                'message' => '导出失败!'
            ]);
        }

        $this->jsonReturn([
            'code' => 1,
            'message' => '导出成功!',
            'data' => [
                'url' => $remoteFile
            ]
        ]);

    }

    /**
     * 压缩
     * @param $localFile
     * @return string
     */
    private function compresFile($localFile)
    {
        $zipFileName = "BY_".date('Ymd-His').'.zip';
        $filePath = $_SERVER['DOCUMENT_ROOT'] . "/public/tmp/".$zipFileName;

        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZIPARCHIVE::CREATE|ZIPARCHIVE::OVERWRITE);
        if ($result){
            $zip->addFile($localFile,basename($localFile));
            $zip->close();
            @unlink($localFile);
            return $filePath;
        }
    }

    /**
     * 导出的文件上传到文件服务器
     * @param $localFile 本地临时文件
     * @return array|mixed 文件服务器上的文件信息
     */
    private function upload2FileServer($localFile)
    {

        $server = Yaf_Application::app()->getConfig()->myhost;
        $url = $server. '/V2/Uploadfile/upload';
        $data['tmp_name']=$localFile;
        $data['type']='application/zip';
        $data['name']=basename($localFile);
        $excel = new ExcelmanagerController();
        $result = $excel->postfile($data,$url);
        if ($result['code']=='1'){
            @unlink($localFile);
        }
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        return $fastDFSServer.$result['url'];
    }

    /*
         * Description of 获取国家
         * @param array $arr
         * @author  zhongyg
         * @date    2017-8-2 13:07:21
         * @version V2.0
         * @desc
         */

    private function _setCountry(&$arr, $filed) {
        if ($arr) {
            $country_model = new CountryModel();
            $country_bns = [];
            foreach ($arr as $key => $val) {
                $country_bns[] = trim($val[$filed . '_bn']);
            }
            $countrynames = $country_model->getNamesBybns($country_bns, 'zh');
            foreach ($arr as $key => $val) {
                if (trim($val[$filed . '_bn']) && isset($countrynames[trim($val[$filed . '_bn'])])) {
                    $val[$filed . '_name'] = $countrynames[trim($val[$filed . '_bn'])];
                } else {
                    $val[$filed . '_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }
    /**
     * 获取会员列表
     * @return mixed
     */
    private function getBuyerList($where)
    {
        $buyerModel = new BuyerModel();
        //会员编号,会员名称,注册地,会员等级,用户来源,注册时间,审核状态
        $map['buyer.deleted_flag'] = 'N';
        if ($where['is_agent']=='Y') {
            $map1['buyer.created_by'] = $where['agent']['user_id'];
            $map1['agent_id'] = $where['agent']['agent_id'];
            $map1['_logic'] = 'or';
            $map['_complex'] = $map1;
        }
        if (!empty($where['country_bn'])) {
            $map['buyer.country_bn'] = ['in', $where['country_bn'] ];
        }
        $data = $buyerModel->field('buyer_no,`erui_buyer`.`buyer`.buyer_code,`erui_buyer`.`buyer`.country_bn,buyer_level,`erui_buyer`.`buyer`.source,`erui_buyer`.`buyer`.created_at,`erui_buyer`.`buyer`.status,`erui_buyer`.`buyer`.percent')
            ->join('`erui_buyer`.`buyer_agent` on `erui_buyer`.`buyer_agent`.`buyer_id` = `erui_buyer`.`buyer`.`id`', 'left')
            ->join('`erui_sys`.`employee` em on em.id=erui_buyer.buyer_agent.agent_id', 'left')
            ->where($map)
            ->group('`erui_buyer`.`buyer`.`id`')
            ->order('`erui_buyer`.`buyer`.`id` DESC')
            ->select();
        foreach ($data as $k=>$v){
            //会员等级
            if (empty($v['buyer_level'])){
                $data[$k]['buyer_level'] = '注册会员';
            }else{
                $level=new BuyerLevelModel();
                $name=$level->getBuyerLevelById($v['buyer_level'],'zh');
                $data[$k]['buyer_level']=$name;
            }
            //用户来源
            if ($v['source']==1){
                $data[$k]['source'] = '后台注册';
            }elseif($v['source']==2){
                $data[$k]['source'] = '门户注册';
            }elseif($v['source']==3){
                $data[$k]['source'] = '手机端注册';
            }
            //审核状态
            switch ($v['status']){
                case 'APPROVING' : $data[$k]['status'] = '待审核'; break;
                case 'APPROVED' : $data[$k]['status'] = '已通过'; break;
                case 'REJECTED' : $data[$k]['status'] = '已驳回'; break;
                case 'FIRST_APPROVED' : $data[$k]['status'] = '初审通过'; break;
                case 'FIRST_REJECTED' : $data[$k]['status'] = '初审驳回'; break;
            }
        }
        return $data;

    }

    /**
     * 创建Excel文件并填充数据
     * @param array $data
     *
     * @return string
     */
    private function createExcelObjWithData(array $data)
    {
        //创建Excel对象
        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle('会员列表');

        //列表头
        $objSheet->setCellValue("A1","完整度")->getColumnDimension("A")->setWidth('24');
        $objSheet->setCellValue("B1","会员编号")->getColumnDimension("B")->setWidth('24');
        $objSheet->setCellValue("C1","CRM客户代码")->getColumnDimension("C")->setWidth('24');
        $objSheet->setCellValue("D1","国家")->getColumnDimension("D")->setWidth('24');
        $objSheet->setCellValue("E1","注册时间")->getColumnDimension("E")->setWidth('24');
        $objSheet->setCellValue("F1","审核状态")->getColumnDimension("F")->setWidth('24');
        $objSheet->setCellValue("G1","客户等级")->getColumnDimension("G")->setWidth('24');
        $objSheet->setCellValue("H1","用户来源")->getColumnDimension("H")->setWidth('24');

        //设置边框
        $objSheet->getStyle("A1:G1")->applyFromArray($this->borderStyle);

        //写入数据
        $rowNum = 2;
        foreach ($data as $v){
            if(!empty($v['percent'])){
                $v['percent']=$v['percent'].'%';
            }else{
                $v['percent']='--';
            }
            $objSheet->setCellValue("A" . $rowNum, $v['percent']);
            $objSheet->setCellValue("B" . $rowNum, $v['buyer_no']);
            $objSheet->setCellValue("C" . $rowNum, $v['buyer_code']);
            $objSheet->setCellValue("D" . $rowNum, $v['country_name']);
            $objSheet->setCellValue("E" . $rowNum, $v['created_at']);
            $objSheet->setCellValue("F" . $rowNum, $v['status']);
            $objSheet->setCellValue("G" . $rowNum, $v['buyer_level']);
            $objSheet->setCellValue("H" . $rowNum, $v['source']);

            $objSheet->getStyle("A2:H" . $rowNum)->applyFromArray($this->borderStyle);

            $rowNum++;

        }
        $path = "public/tmp/";
        if(!file_exists($path)){
            mkdir($path,0777,true);
        }
        //保存文件
        ob_end_clean();
        ob_start();
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        $fileName = "BL_".date('Ymd-His'). '.xls';
        $fullFileName = $_SERVER['DOCUMENT_ROOT'] .'/'.$path .$fileName;
        $objWriter->save($fullFileName);
        return $fullFileName;

    }

}
