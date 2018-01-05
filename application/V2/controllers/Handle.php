<?php

class HandleController extends Yaf_Controller_Abstract
{

    public function init()
    {
        header('Content-type:text/html;charset=utf8');
    }

    public function indexAction()
    {
        return true;
    }

    public function exportProductAction()
    {

        $data = $this->getProductData();

        $localFile = $this->createExcelObj($data);


    }

    private function getProductData()
    {

        $model = new ShowCatModel();

        $where = [
            'market_area_bn' => "South America",
            'country_bn' => "Argentina",
            'level_no' => 3,
            'lang' => "zh",
        ];

        $fields = 'name,cat_no,parent_cat_no,level_no';

        set_time_limit(0);

        $data = $model->where($where)->field($fields)->select();
        //p($model->getLastSql());

//        echo json_encode([
//        'total' => $model->where($where)->count('id'),
//        'data'  => count($data)
//    ]);die;

        //material_cat
        $show_material_cat = new ShowMaterialCatModel();
        $product = new ProductModel();
        $goods = new GoodsModel();

        $exportData = [];
        foreach ($data as &$item) {
            $material_cat_no = implode(',', $show_material_cat->where(['show_cat_no' => $item['cat_no']])->getField('material_cat_no', true));

            //p($item['material_cat_no']);
            if ($material_cat_no) {

                $item['product'] = $product->where('material_cat_no IN(' . $material_cat_no . ')')
                    ->where([
                        'lang' => 'zh',
                        'deleted_flag' => 'N'
                    ])
                    ->field('name,brand,spu,material_cat_no')->select();

                foreach ($item['product'] as $pro) {

                    $brand = json_decode($pro['brand'], true);

                    $sku = $goods->where(['spu' => $pro['spu']])->count('id');

                    $exportData[] = [
                        'level_three' => $item['name'],
                        'name' => $pro['name'],
                        'brand' => $brand['name'],
                        'sku' => $sku,
                        'material_cat_no' => $pro['material_cat_no']
                    ];
                }

            }
        }

        //p(count($exportData));
        return $exportData;

    }

    private function createExcelObj($data)
    {

        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle('');

        $normal_cols = ["A", "B", "C", "D", "E", "F", "G"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('20');
            $objSheet->getCell($normal_col . "1")->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        endforeach;

        /* 设置A1~R1标题并合并单元格(水平整行，垂直2列) */
        $objSheet->setCellValue("A1", '展示一级分类');
        $objSheet->setCellValue("B1", '展示二级分类');
        $objSheet->setCellValue("C1", '展示三级分类');
        $objSheet->setCellValue("D1", '物料编码');
        $objSheet->setCellValue("E1", '产品名称');
        $objSheet->setCellValue("F1", '产品品牌');
        $objSheet->setCellValue("G1", '商品数量');

        //设置全局文字居中
        $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(10);

        $objSheet->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $normal_cols = ["A", "B", "C", "D", "E", "F", "G"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('20');
            $objSheet->getCell($normal_col . "1")->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        endforeach;

        $startRow = 2;
        if (!empty($data)) {
            foreach ($data as $k => $v) {

                $objSheet->getRowDimension($startRow)->setRowHeight(30);

                $objSheet->setCellValue("A" . $startRow, '');
                $objSheet->setCellValue("B" . $startRow, '');
                $objSheet->setCellValue("C" . $startRow, $v['level_three']);
                $objSheet->setCellValue("D" . $startRow, $v['material_cat_no']);
                $objSheet->setCellValue("E" . $startRow, $v['name']);
                $objSheet->setCellValue("F" . $startRow, $v['brand']);
                $objSheet->setCellValue("G" . $startRow, $v['sku']);

                $objSheet->getCell("A" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("B" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("C" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("D" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("E" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("F" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("G" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $startRow++;
            }

        }

        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        return ExcelHelperTrait::createExcelToLocalDir($objWriter, "Goods_" . date('Ymd-His') . '.xls');

    }


    /**
     *  重置用户名并发送邮件
     *  临时解决方案
     */
    public function resetUserPasswordAction()
    {
        $userModel = new UserModel();
        $data = $userModel->count();

        $hash = md5('eruicb2b');

        //$noChangesPasswordUsersCount = $userModel->where(['password_hash' => $hash, 'deleted_flag' => 'N'])->count();

        //$status = MailHelper::sendEmail('learnfans@aliyun.com', '【询报价】办理通知', '你的密码被更改成234567654321234566543了。登录BOSS系统更改啊...','买买提');

        $str = $this->makeRandomStr(6);

        p($str);
    }

    /**
     * 形成指定长度的随机字符串(大小写字+母数字)
     *
     * @param $length
     *
     * @return string
     */
    public function makeRandomStr($length)
    {
        $str = '';

        $seed = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($seed) - 1;

        for ($i = 0; $i < $length; $i++) {
            //rand($min,$max)生成介于min和max两个数之间的一个随机整数
            $str .= $seed[rand(0, $max)];
        }

        return $str;

    }

    public function rejectedInquiryAction()
    {

        $inquiryCheckLog = new InquiryCheckLogModel();

        $field = "b.id,b.serial_no,b.agent_id,b.adhoc_request,b.now_agent_id,b.org_id,b.area_bn,b.country_bn,a.created_at,a.created_by,a.op_note,a.out_node";
        $where = "b.deleted_flag='N' AND a.action='REJECT' ";


        $data = $inquiryCheckLog->alias('a')->join('erui_rfq.inquiry b ON a.inquiry_id=b.id','LEFT')
                                ->field($field)
                                ->where($where)
                                ->select();

        $employee = new EmployeeModel();
        $org = new OrgModel();
        $region = new RegionModel();
        $country = new CountryModel();

        foreach ($data as &$item){

            $item['agent'] = $employee->where(['id'=>$item['agent_id']])->getField('name');
            $item['now_agent'] = $employee->where(['id'=>$item['now_agent_id']])->getField('name');
            $item['org_name'] = $org->getNameById($item['org_id']);
            $item['region_name'] = $region->where(['bn'=>trim($item['area_bn']),'lang'=>'zh'])->getField('name');
            $item['country_name'] = $country->where(['bn'=>trim($item['country_bn']),'lang'=>'zh'])->getField('name');

            $item['created_by'] = $employee->where(['id'=>$item['created_by']])->getField('name');
            $item['out_node'] = $this->setNode($item['out_node']);
        }
        //p(count($data));
        //p($data);
        return $data;
    }

    /**
     * 设置环节名称
     * @param $node
     *
     * @return string
     */
    private function setNode($node) {

        switch ($node) {
            case 'DRAFT' : $nodeName = '草稿'; break;
            case 'REJECT_MARKET' : $nodeName = '驳回市场'; break;
            case 'BIZ_DISPATCHING' : $nodeName = '事业部分单员'; break;
            case 'CC_DISPATCHING' : $nodeName = '易瑞客户中心分单员'; break;
            case 'BIZ_QUOTING' :  $nodeName = '事业部报价'; break;
            case 'LOGI_DISPATCHING' : $nodeName = '物流分单员'; break;
            case 'LOGI_QUOTING' : $nodeName = '物流报价'; break;
            case 'LOGI_APPROVING' :  $nodeName = '物流审核'; break;
            case 'BIZ_APPROVING' : $nodeName = '事业部核算'; break;
            case 'MARKET_APPROVING' : $nodeName = '事业部审核'; break;
            case 'MARKET_CONFIRMING' : $nodeName = '市场确认'; break;
            case 'QUOTE_SENT' : $nodeName = '报价单已发出'; break;
            case 'INQUIRY_CLOSED' : $nodeName = '报价关闭'; break;
        }

        return $nodeName;
    }

}
