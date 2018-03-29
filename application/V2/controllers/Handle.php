<?php

class HandleController extends PublicController
{

    /**
     *
     *  db : rm-erui123.mysql.rds.aliyuncs.com
     *  user : mmt
     *  pwd : kRui#rds8
     */


    public function init()
    {
        parent::init();

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

    /**
     * 导出驳回的询单
     * @return mixed
     */
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

    /**
     * 测试方法
     */
    public function testAction(){}

    /**
     * 导出指定供应商的SKU信息
     */
    public function exportAction()
    {
        /*
         *  德州博儒石油机械制造有限公司  N
            济南万齐石油装备有限公司 N
            青岛海科石油装备有限公司 Y
            济南隆超石油机械锻造有限公司 Y
            青岛双圣海新能源科技有限公司 Y
            淄博华创精细陶瓷有限公司 Y
            安平县星火金属网厂 Y
            青岛天时油气装备服务集团股份有限公司 Y
            成都西部石油装备股份有限公司 Y
            河北宇通特种胶管有限公司 y
            山东龙口特种胶管有限公司 y
            河北景渤石油机械有限公司 y
            东营欧菲德石油技术有限公司 y
            北京普世科石油机械新技术有限公司 y
            宝鸡市工程液压件厂 y
            江苏如通石油机械股份有限公司 y
            江苏如石机械股份有限公司 y
            南京安佰科照明科技有限公司 y
            湖北江汉石油仪器仪表股份有限公司 y
         */
        set_time_limit(0);

        $condition = $this->validateRequestParams();

        $supplierName = '青岛海科石油装备有限公司';
        $supplierId = (new SupplierModel)->where(['name' => $supplierName, 'status' => 'APPROVED'])->getField('id');

        if (!$supplierId) die(json_encode([ 'code'=> -1, 'message'=> '供应商不存在或未审核!']));

        //$data = $this->getSpuBySupplier($supplierId);
        //$data = $this->getSpuByBrandAction('济柴');
        //$data = (new EsproductController)->exportListAction($condition);

        $sku = $this->getOnShelfSkuBy($supplierId);
        //p(count($sku));

        //导出供应商的已上架的SKU
        $file = SupplierHelper::SupplierOnShelfSku($sku, $supplierName);
        p($file);

        (new GoodsModel)->exportAll([
            'spus' => $data,
            //'lang' => 'zh'
        ]);

        //$data = $this->getSkuDataBySupplierID($supplierId);
        //p($condition);
        $localFile = $this->createSupplierExcel($data, $supplierName);

        p($localFile);
    }

    /**
     * 获取供应商的已上架的SKU
     * @param $supplier 供应商id
     * @return array
     */
    public function getOnShelfSkuBy($supplier)
    {
        //供应商的sku
        $goodsSupplier = new GoodsSupplierModel();
        $data = $goodsSupplier->where(['supplier_id' =>$supplier, 'deleted_flag' => 'N'])->field('sku,pn')->select();

        //是否已上架
        foreach ($data as $key=>$value) {
            $isOnShelf = (new ShowCatGoodsModel)->where(['sku' => $value['sku'], 'onshelf_flag' => 'Y'])->count();
            if (!$isOnShelf) {
                unset($data[$key]);
            }

            $price = (new GoodsCostPriceModel)->where(['sku' => $value['sku'], 'deleted_flag' => 'N'])->field('price,price_validity')->find();
            $sku_data[] = [
                'sku' => $value['sku'],
                'pn' => $value['pn'],
                'price' => $price['price'],
                'price_validity' => $price['price_validity'],
            ];
        }

        return $sku_data;
    }

    public function spuWithSkuAction()
    {
        //57 56 55 54 53
        $data = $this->getSpuByBizline('55');

        //p($data);

        (new GoodsModel)->exportAll([
            'spus' => $data,
            //'lang' => 'zh'
        ]);

    }

    private function getSpuByBizline($bizline_id)
    {
        return (new ProductModel)->where(['bizline_id' => $bizline_id, 'deleted_flag'=> 'N'])->getField('spu',true);
    }

    /**
     * 根据供应商获取SPU
     * @param $supplierId 供应商id
     *
     * @return mixed SPU
     */
    private function getSpuBySupplier($supplierId)
    {
         return (new ProductSupplierModel)->where(['supplier_id' => $supplierId])->getField('spu',true);
    }


    /**
     * 根据品牌获取SPU
     * @param $brand 品牌
     *
     * @return array SPU
     */
    public function getSpuByBrandAction($brand)
    {

        $data = (new ProductModel)->where(['deleted_flag' => 'N', 'lang'=> 'zh'])->field('spu,brand')->select();

        $spus = [];
        foreach ($data as $item){
            $brand_info = json_decode($item['brand'],true);
            if ($brand_info['name'] == $brand){
                $spus[] = $item['spu'];
            }
        }
        return $spus;
    }

    /**
     * 获取指定供应商对应的SKU信息
     * @param $supplierId 供应商id
     *
     * @return mixed
     */
    private function getSkuDataBySupplierID ($supplierId)
    {

        $model = new GoodsSupplierModel();

        $where = [
            'a.supplier_id' => $supplierId,
            //'b.lang'         => 'zh',
        ];

        $field = 'b.id,c.spu,c.show_name,c.brand,b.name,b.model,b.exw_days,b.min_pack_naked_qty,b.nude_cargo_unit,
                b.min_pack_unit,b.min_order_qty,d.price,d.price_cur_bn,d.price_validity,a.supplier_id';

        $data = $model->alias('a')->join('erui_goods.goods b ON a.sku=b.sku')
                                ->join('erui_goods.product c ON a.spu=c.spu')
                                ->join('erui_goods.goods_cost_price d ON a.sku=d.sku')
                                ->field($field)
                                ->where($where)
                                ->select();

        foreach ($data as &$value){
            $brand = json_decode($value['brand'],true);
            $value['brand'] =$brand['name'];
            $value['supplier_name'] = (new SupplierModel)->where(['id'=> $value['supplier_id']])->getField('name');
        }

        //p(count($data));
        return $data;

    }

    /**
     * 形成供应商数据Excel对象
     * @param $data 数据对象
     * @param $supplierName 供应商名称
     *
     * @return string 本地文件路径
     */
    private function createSupplierExcel($data, $supplierName)
    {
        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle($supplierName);

        /* 设置A1~R1标题并合并单元格(水平整行，垂直2列) */
        $objSheet->setCellValue("A1", '序号');
        $objSheet->setCellValue("B1", '订货号');
        $objSheet->setCellValue("C1", 'SPU编码');
        $objSheet->setCellValue("D1", 'SPU展示名称(中文)');
        $objSheet->setCellValue("E1", '品牌(中文)');
        $objSheet->setCellValue("F1", '名称');
        $objSheet->setCellValue("G1", '型号');
        $objSheet->setCellValue("H1", '供应商名称');
        $objSheet->setCellValue("I1", '出货周期(天)');
        $objSheet->setCellValue("J1", '最小包装内裸货商品数量');
        $objSheet->setCellValue("K1", '商品裸货单位');
        $objSheet->setCellValue("L1", '最小包装单位');
        $objSheet->setCellValue("M1", '最小订货数量');
        $objSheet->setCellValue("N1", '供应商供货价');
        $objSheet->setCellValue("O1", '有效期');
        $objSheet->setCellValue("P1", '币种');
        $objSheet->setCellValue("Q1", '扩展属性');

        $objSheet->setCellValue("A2", '');
        $objSheet->setCellValue("B2", 'Item No.');
        $objSheet->setCellValue("C2", 'SPU');
        $objSheet->setCellValue("D2", 'Spu show name');
        $objSheet->setCellValue("E2", 'Brand');
        $objSheet->setCellValue("F2", 'Name');
        $objSheet->setCellValue("G2", 'Model');
        $objSheet->setCellValue("H2", 'Supplier');
        $objSheet->setCellValue("I2", 'EXW(day)');
        $objSheet->setCellValue("J2", 'Minimum packing Naked quantity');
        $objSheet->setCellValue("K2", 'Goods nude cargo units');
        $objSheet->setCellValue("L2", 'Minimum packing unit');
        $objSheet->setCellValue("M2", 'Minimum order quantity');
        $objSheet->setCellValue("N2", 'Supply price');
        $objSheet->setCellValue("O2", 'Price validity');
        $objSheet->setCellValue("P2", 'Currency');
        $objSheet->setCellValue("Q2", '');

        //设置全局文字居中
        $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(10);

        $objSheet->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $normal_cols = ["B", "C", "D", "E", "F", "G", "H", "J", "K", "L", "M", "N", "O", "P", "Q"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('20');
            $objSheet->getCell($normal_col . "1")->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objSheet->getCell($normal_col . "2")->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        endforeach;

        $objSheet->getColumnDimension("H")->setWidth('30');

        $startRow = 3;
        if (!empty($data)) {
            foreach ($data as $k => $v) {

                $objSheet->getRowDimension($startRow)->setRowHeight(30);

                $objSheet->setCellValue("A" . $startRow, $k);
                $objSheet->setCellValue("B" . $startRow, '');
                $objSheet->setCellValue("C" . $startRow, $v['spu']);
                $objSheet->setCellValue("D" . $startRow, $v['show_name']);
                $objSheet->setCellValue("E" . $startRow, $v['brand']);
                $objSheet->setCellValue("F" . $startRow, $v['name']);
                $objSheet->setCellValue("G" . $startRow, $v['model']);
                $objSheet->setCellValue("H" . $startRow, $v['supplier_name']);
                $objSheet->setCellValue("I" . $startRow, $v['exw_days']);
                $objSheet->setCellValue("J" . $startRow, $v['min_pack_naked_qty']);
                $objSheet->setCellValue("K" . $startRow, $v['nude_cargo_unit']);
                $objSheet->setCellValue("L" . $startRow, $v['min_pack_unit']);
                $objSheet->setCellValue("M" . $startRow, $v['min_order_qty']);
                $objSheet->setCellValue("N" . $startRow, $v['price']);
                $objSheet->setCellValue("O" . $startRow, $v['price_validity']);
                $objSheet->setCellValue("P" . $startRow, $v['price_cur_b']);
                $objSheet->setCellValue("Q" . $startRow, '');

                $objSheet->getCell("A" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("B" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("C" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("D" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("E" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("F" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("G" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("H" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("I" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("J" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("K" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("L" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("M" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("N" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("O" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("P" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("Q" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $startRow++;
            }

        }

        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        return ExcelHelperTrait::createExcelToLocalDir($objWriter, "SUPPLIER_" . date('Ymd-His') . '.xls');
    }

    /**
     * 导出无供应商编号的供应商
     * 2018/01/16
     */
    public function supplierAction()
    {
        $data = $this->supplierData();

        $localFile = $this->createSupplier($data);
        p($localFile);
    }

    /**
     * 无供应商编号的供应商数据
     * @return mixed
     */
    private function supplierData()
    {
        //没有供应商编码的数据
        $data = (new SupplierModel)->where('supplier_no is null')->field('id,supplier_no,name,name_en,country_bn')->select();
        foreach ($data as &$item){
            $item['country_bn'] = (new CountryModel)->where(['bn'=>$item['country_bn'],'lang'=>'zh'])->getField('name');
        }
        return $data;
    }

    private function createSupplier($data)
    {
        $objPHPExcel = new PHPExcel();
        $objSheet = $objPHPExcel->getActiveSheet();
        $objSheet->setTitle('');

        /* 设置A1~R1标题并合并单元格(水平整行，垂直2列) */
        $objSheet->setCellValue("A1", 'ID');
        $objSheet->setCellValue("B1", '供应商编号');
        $objSheet->setCellValue("C1", '供应商名称');
        $objSheet->setCellValue("D1", '供应商名称(英文)');
        $objSheet->setCellValue("E1", '国家');


        //设置全局文字居中
        $objSheet->getDefaultStyle()->getFont()->setName("微软雅黑")->setSize(10);

        $objSheet->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $normal_cols = ["B", "D", "E"];
        foreach ($normal_cols as $normal_col):
            $objSheet->getColumnDimension($normal_col)->setWidth('20');
            $objSheet->getCell($normal_col . "1")->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        endforeach;

        $objSheet->getColumnDimension("C")->setWidth('30');

        $startRow = 2;
        if (!empty($data)) {
            foreach ($data as $k => $v) {

                $objSheet->getRowDimension($startRow)->setRowHeight(30);

                $objSheet->setCellValue("A" . $startRow, $v['id']);
                $objSheet->setCellValue("B" . $startRow, $v['supplier_no']);
                $objSheet->setCellValue("C" . $startRow, $v['name']);
                $objSheet->setCellValue("D" . $startRow, $v['name_en']);
                $objSheet->setCellValue("E" . $startRow, $v['country_bn']);


                $objSheet->getCell("A" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("B" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("C" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("D" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objSheet->getCell("E" . $startRow)->getStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

                $startRow++;
            }

        }

        //4.保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        return ExcelHelperTrait::createExcelToLocalDir($objWriter, "SUPPLIER_" . date('Ymd-His') . '.xls');
    }

}
