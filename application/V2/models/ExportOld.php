<?php

/* To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EsProduct
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   ES 产品
 */
class ExportOldModel extends Model {
    /*
     * 对应表
     *
     */

    protected $tableName = 'goods';
    protected $dbName = 'erui_goods'; //数据库名称
    protected $g_table = 'erui_goods.goods';

    /**
     * 产品导出
     * @return string
     */
    public function export($condition = [], $process = '', $lang = '') {
        /** 返回导出进度start */
        $progress_key = 'processed_' . md5(json_encode($condition));
        if (!empty($process)) {
            if (redisExist($progress_key)) {
                $progress_redis = json_decode(redisGet($progress_key), true);
                return $progress_redis['processed'] < $progress_redis['total'] ?
                        ceil($progress_redis['processed'] / $progress_redis['total'] * 100) : 100;
            } else {
                return 100;
            }
        }
        $progress_redis = array('start_time' => time());    //用来记录导入进度信息
        /** 导入进度end */
        set_time_limit(0);  # 设置执行时间最大值


        $count = $this->getCount($condition, $lang);

        $progress_redis['total'] = $count;
        if ($count <= 0) {
            jsonReturn('', ErrorMsg::FAILED, '无数据可导出');
        }

        //单excel显示条数
        //excel输出的起始行
        try {

            for ($p = 0; $p < $count / 500; $p++) {
                $this->Createxls($condition, $lang, $p);
            }
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Export failed:' . $e, Log::ERR);
            return false;
        }
    }

    /*
     * 生成excel
     */

    public function Createxls($condition = [], $lang = '', $xlsNum = 1) {
        //存储目录

        $tmpDir = MYPATH . DS . 'public' . DS . 'tmp' . DS;
        rmdir($tmpDir);
        $dirName = $tmpDir . date('YmdH', time());
        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                jsonReturn('', ErrorMsg::FAILED, '操作失败，请联系管理员');
            }
        }
        $diriamgesName = $tmpDir . date('YmdH', time()) . DS . 'images';
        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                jsonReturn('', ErrorMsg::FAILED, '操作失败，请联系管理员');
            }
        }

        $es = new ESClient();
        $result = $this->getList($condition, ['spu', 'material_cat_no', 'name', 'show_name', 'brand', 'keywords', 'exe_standard', 'tech_paras', 'description', 'warranty', 'status', 'bizline', 'attachs'], $lang, $xlsNum * self::$xlsSize, self::$xlsSize);

        $spus = [];
        foreach ($result as $j => $item) {
            $name = $this->Filerepalce($item['name']);

            $brand_name = $this->Filerepalce($item['brand']['name']);
            $dir = $dirName . DS . $name . '_' . $brand_name . '_' . $item['spu'];

            $dirimg = $diriamgesName . DS . $name . '_' . $brand_name . '_' . $item['spu'];
            $this->_save($item, $dir, $dirimg, $lang);
            $p_en = $es->get('erui_goods', 'product_en', $item['spu']);
            $this->_save($p_en['_source'], $dir, $dirimg, 'en', false);
            $spus[] = $item['spu'];
        }

        $this->exportSku(['spus' => $spus, $xlsNum, 'lang' => 'en'], $dir);
        $this->exportSku(['spus' => $spus, $xlsNum, 'lang' => 'zh'], $dir);

        return true;
    }

    public function _save($item, $dir, $dirimg, $lang, $save_sku = true) {
        $keys = $this->_getKeys();
        $localFile = MYPATH . "/public/file/spuTemplate.xls";    //模板
        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));
        $fileType = PHPExcel_IOFactory::identify($localFile);    //获取文件类型
        $objReader = PHPExcel_IOFactory::createReader($fileType);    //创建PHPExcel读取对象
        $objPHPExcel = $objReader->load($localFile);    //加载文件
        $objSheet = $objPHPExcel->setActiveSheetIndex(0);    //当前sheet
        $objSheet->setTitle('spu_' . $lang);
        $objSheet->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
        $objSheet->getStyle("N1")->getFont()->setBold(true);    //粗体
        $objSheet->setCellValue("N1", '审核状态');
        foreach ($keys as $letter => $key) {

            if ($key === 'brand' && isset($item['brand']['name']) && $item['brand']['name']) {

                $objSheet->setCellValue($letter . ($j + 2), ' ' . $item['brand']['name']);
            } elseif ($key === 'bizline' && isset($item['bizline']['name']) && $item['bizline']['name']) {

                $objSheet->setCellValue($letter . ($j + 2), ' ' . $item['bizline']['name']);
            } elseif (isset($item[$key]) && $item[$key]) {

                $objSheet->setCellValue($letter . ($j + 2), ' ' . $item[$key]);
            } else {
                $objSheet->setCellValue($letter . ($j + 2), ' ');
            }
        }

        $status = '';
        switch ($item['status']) {
            case 'VALID':
                $status = '通过';
                break;
            case 'INVALID':
                $status = '驳回';
                break;
            case 'CHECKING':
                $status = '待审核';
                break;
            case 'DRAFT':
                $status = '草稿';
                break;
            default:
                $status = $item['status'];
                break;
        }


        $objSheet->setCellValue("N" . ($j + 2), ' ' . $status);
        if ($save_sku) {

            $attachs = json_decode($item['attachs'], true);
            if (!empty($attachs)) {
                $this->exportimg($attachs['BIG_IMAGE'], $dirimg);
            }
        }
        if (!empty($item['tech_paras'])) {
            $this->TechParasImg($item['tech_paras'], $lang, $dirimg);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        $objWriter->save($dir . DS . 'spu_' . $lang . '.xls');

        unset($objPHPExcel, $objSheet);
    }

    /**
     * sku导出
     */
    public function exportSku($input = [], $xlsNum = 1, $dir = null) {
        ini_set("memory_limit", "1024M"); // 设置php可使用内存
        set_time_limit(0);  # 设置执行时间最大值

        if (empty($input['lang'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM, '请传递语言');
        }
        if (empty($input['spus']) || !is_array($input['spus'])) {
            jsonReturn('', ErrorMsg::ERROR_PARAM, '请选择要导出的SPU');
        }

        //目录
        $tmpDir = MYPATH . '/public/tmp/';
        $dirName = $tmpDir . time();
        if ($dir) {
            $dirName = $dir;
        }
        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
            }
        }
        $goods_model = new GoodsModel();
        $attrModel = new GoodsAttrModel();
        $gsModel = new GoodsSupplierModel();
        $supplierModel = new SupplierModel();
        $productModel = new ProductModel();
        $condition = array('lang' => $input['lang']);
        foreach ($input['spus'] as $spu) {
            $condition['spu'] = $spu;
            if (isset($input['skus']) && !empty($input['skus']) && is_array($input['skus'])) {    //勾选了sku
                $condition['sku'] = array('in', $input['skus']);
            } else {
                if (isset($input['sku']) && !empty($input['sku']) && is_string($input['sku'])) {    //sku编码
                    $condition['sku'] = $input['sku'];
                }
                if (isset($input['name']) && !empty($input['name'])) {    //名称
                    $condition['name'] = array('like', '%' . $input['name'] . '%');
                }

                if (isset($input['type']) && $input['type'] == 'CHECKING') {    //类型：CHECKING->审核不取草稿状态。
                    $condition['status'] = array('neq', 'DRAFT');
                }

                if (isset($input['status']) && !empty($input['status'])) {
                    $condition['status'] = $input['status'];
                }

                if (isset($input['created_by']) && !empty($input['created_by'])) {    //创建人
                    if (is_numeric($input['created_by'])) {
                        $created_by = intval($input['created_by']);
                    } else {
                        $empModel = new EmployeeModel();
                        $userInfo = $empModel->field('id')->where(['name' => trim($input['created_by'])])->find();
                        $created_by = $userInfo['id'];
                    }
                    $condition['created_by'] = $created_by;
                }

                if (isset($input['created_at']) && !empty($input['created_at'])) {    //创建时间段，注意格式：2017-09-08 00:00:00 - 2017-09-08 00:00:00
                    $time_ary = explode(' - ', $input['created_at']);
                    $condition['created_at'] = array('between', $time_ary);
                    unset($time_ary);
                }

                if (isset($input['supplier']) && !empty($input['supplier'])) {    //供应商
                    $supplierInfo = $supplierModel->field('id')->where(['name' => trim($input['supplier']), 'deleted_flag' => 'N'])->find();
                    $skuAry = [];
                    if ($supplierInfo) {
                        $gskus = $gsModel->field('sku')->where(['supplier_id' => $supplierInfo['id']])->select();
                        if ($gskus) {
                            foreach ($gskus as $r) {
                                $skuAry[] = $r['sku'];
                            }
                        }
                    }
                    $condition['sku'] = $skuAry ? array('in', $skuAry) : false;
                }
            }

            $data_title = [
                [
                    'item' => '序号',
                    'sku' => '订货号',
                    'spu' => 'SPU编码',
                    'spu_showname' => 'SPU展示名称(中文)',
                    'brand' => '品牌(中文)',
                    'name' => '名称',
                    'model' => '型号',
                    'supplier' => '供应商名称',
                    'exw_days' => '出货周期(天)',
                    'min_pack_naked_qty' => '最小包装内裸货商品数量',
                    'nude_cargo_unit' => '商品裸货单位',
                    'min_pack_unit' => '最小包装单位',
                    'min_order_qty' => '最小订货数量',
                    'purchase_price' => '供应商供货价',
                    'price_validity' => '有效期',
                    'purchase_price_cur_bn' => '币种',
                    1 => '物流信息',
                    'nude_cargo_l_mm' => '裸货尺寸长(mm)',
                    'nude_cargo_w_mm' => '裸货尺寸宽(mm)',
                    'nude_cargo_h_mm' => '裸货尺寸高(mm)',
                    'min_pack_l_mm' => '最小包装后尺寸长(mm)',
                    'min_pack_w_mm' => '最小包装后尺寸宽(mm)',
                    'min_pack_h_mm' => '最小包装后尺寸高(mm)',
                    'net_weight_kg' => '净重(kg)',
                    'gross_weight_kg' => '毛重(kg)',
                    'compose_require_pack' => '仓储运输包装及其他要求',
                    'pack_type' => '包装类型',
                    2 => '申报要素',
                    'name_customs' => '中文品名(报关用)',
                    'hs_code' => '海关编码',
                    'tx_unit' => '成交单位',
                    'tax_rebates_pct' => '退税率(%)',
                    'regulatory_conds' => '监管条件',
                    'commodity_ori_place' => '境内货源地',
                ],
                [
                    'item' => '',
                    'sku' => 'Item No.',
                    'spu' => 'SPU',
                    'spu_showname' => 'Spu show Name',
                    'brand' => 'Brand',
                    'name' => 'name',
                    'model' => 'Model',
                    'supplier' => 'Supplier',
                    'exw_days' => 'EXW(day)',
                    'min_pack_naked_qty' => 'Minimum packing Naked quantity',
                    'nude_cargo_unit' => 'Goods nude cargo units',
                    'min_pack_unit' => 'Minimum packing unit',
                    'min_order_qty' => 'Minimum order quantity',
                    'purchase_price' => 'Supply price',
                    'price_validity' => 'Price validity',
                    'purchase_price_cur_bn' => 'Currency',
                    1 => '',
                    'nude_cargo_l_mm' => 'Length of nude cargo(mm)',
                    'nude_cargo_w_mm' => 'Width of nude cargo(mm)',
                    'nude_cargo_h_mm' => 'Height of nude cargo(mm)',
                    'min_pack_l_mm' => 'Minimum packing Length size (mm)',
                    'min_pack_w_mm' => 'Minimum packing Width size (mm)',
                    'min_pack_h_mm' => 'Minimum packing Height size (mm)',
                    'net_weight_kg' => 'Net Weight(kg)',
                    'gross_weight_kg' => 'Gross Weight(kg)',
                    'compose_require_pack' => 'Compose Require',
                    'pack_type' => 'Packing type',
                    2 => '',
                    'name_customs' => 'Name (customs)',
                    'hs_code' => 'HS CODE',
                    'tx_unit' => 'Transaction Unit',
                    'tax_rebates_pct' => 'Tax rebates(%)',
                    'regulatory_conds' => 'Regulatory conditions',
                    'commodity_ori_place' => 'Domestic supply of goods to',
                ]
            ];
            $i = 0;
            // $length = 5000;    //分页取
            $spec_ary = $hs_ary = [];
            $goods_val = [];
            do {
                $field = 'spu,sku,lang,name,model,show_name,description,exw_days,min_pack_naked_qty,nude_cargo_unit,min_pack_unit,min_order_qty,purchase_price,purchase_price_cur_bn,nude_cargo_l_mm,nude_cargo_w_mm,nude_cargo_h_mm,min_pack_l_mm,min_pack_w_mm,min_pack_h_mm,net_weight_kg,gross_weight_kg,compose_require_pack,pack_type,name_customs,hs_code,tx_unit,tax_rebates_pct,regulatory_conds,commodity_ori_place,source,source_detail,status,created_by,created_at';
                $result = $goods_model->field($field)->where($condition)->select();
                if (empty($result)) {
                    return;
                    //   jsonReturn('', ErrorMsg::FAILED, '无数据可导');
                }

                foreach ($result as $r) {
                    $productInfo = $productModel->field('show_name,brand')->where(['spu' => $r['spu'], 'lang' => $r['lang'], 'deleted_flag' => 'N'])->find();
                    $r['spu_showname'] = $productInfo ? $productInfo['show_name'] : '';
                    $brandInfo = $productInfo ? json_decode($productInfo['brand'], true) : '';
                    $r['brand'] = $brandInfo ? $brandInfo['name'] : '';
                    $condition_attr = ['sku' => $r['sku'], 'lang' => $r['lang'], 'deleted_flag' => 'N'];
                    $attrs = $attrModel->field('spec_attrs,ex_hs_attrs')->where($condition_attr)->find();
                    $spec = json_decode($attrs['spec_attrs'], true);
                    foreach ($spec as $ak => $av) {
                        if (!isset($spec_ary[0][$ak])) {
                            $spec_ary[0][$ak] = $ak;
                        }
                        if (!isset($spec_ary[1][$ak])) {
                            $spec_ary[1][$ak] = $ak;
                        }
                        $r[$ak] = $av;
                    }
                    $hs = json_decode($attrs['ex_hs_attrs'], true);
                    foreach ($hs as $hk => $hv) {
                        if (!isset($hs_ary[0][$hk])) {
                            $hs_ary[0][$hk] = $hk;
                        }
                        if (!isset($hs_ary[1][$hk])) {
                            $hs_ary[1][$hk] = $hk;
                        }
                        $r[$hk] = $hv;
                    }

                    //查询供应商 - 这里暂时随机取一个 - 后期根据需求可能需要改
                    $gsInfo = $gsModel->field('supplier_id')->where(['sku' => $r['sku']])->find();
                    if ($gsInfo) {
                        $supplierInfo = $supplierModel->field('name')->where(array('deleted_flag' => 'N', 'id' => $gsInfo['supplier_id']))->find();
                        if ($supplierInfo) {
                            $r['supplier'] = $supplierInfo['name'];
                        }
                    }
                    $goods_val[] = $r;
                }
            } while (count($result) >= $length);
            array_splice($data_title[0], 16, 0, $spec_ary[0]);
            array_splice($data_title[1], 16, 0, $spec_ary[1]);
            $hscount = count($data_title[0]);
            array_splice($data_title[0], $hscount, 0, $hs_ary[0]);
            array_splice($data_title[1], $hscount, 0, $hs_ary[1]);

            PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));
            $objPHPExcel = new PHPExcel();
            $col_status = PHPExcel_Cell::stringFromColumnIndex(count($data_title[0]));    //状态
            $objPHPExcel->getSheet(0)->setCellValue($col_status . '1', '审核状态');
            //设置表头
            $excel_index = 0;
            foreach ($data_title[0] as $title_key => $title_value) {
                $colname = PHPExcel_Cell::stringFromColumnIndex($excel_index); //由列数反转列名(0->'A')
                $objPHPExcel->getSheet(0)->setCellValue($colname . '1', $title_value);
                $objPHPExcel->getSheet(0)->setCellValue($colname . '2', $data_title[1][$title_key]);
                $excel_index++;
                $row = 3;    //内容起始行
                foreach ($goods_val as $r) {
                    if (isset($r[$title_key])) {
                        $objPHPExcel->getSheet(0)->setCellValue($colname . $row, ' ' . $r[$title_key]);
                    } elseif (isset($r[$title_value])) {
                        $objPHPExcel->getSheet(0)->setCellValue($colname . $row, ' ' . $r[$title_value]);
                    } else {
                        $objPHPExcel->getSheet(0)->setCellValue($colname . $row, '');
                    }
                    if ($excel_index == count($data_title[0])) {
                        $status = '';
                        switch ($r['status']) {
                            case 'VALID':
                                $status = '通过';
                                break;
                            case 'CHECKING':
                                $status = '报审';
                                break;
                            case 'DRAFT':
                                $status = '暂存';
                                break;
                            case 'INVALID':
                                $status = '驳回';
                                break;
                        }
                        $objPHPExcel->getSheet(0)->setCellValue($col_status . $row, $status);
                    }
                    $row++;
                }
                $objPHPExcel->getActiveSheet()->getColumnDimension($colname)->setAutoSize(true);    //自适应宽
            }
            $objPHPExcel->getActiveSheet()->getStyle("A1:" . $col_status . "2")->getFont()->setSize(11)->setBold(true);    //粗体
            $objPHPExcel->getActiveSheet()->getStyle("A1:" . $colname . "2")->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('3D9140');
            $objPHPExcel->getActiveSheet()->getStyle($col_status . '1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('ff6600');
            $styleArray = ['borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THICK, 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '00000000'),],],];
            $objPHPExcel->getActiveSheet()->getStyle("A1:$col_status" . ($row - 1))->applyFromArray($styleArray);
            $objPHPExcel->getActiveSheet()->freezePaneByColumnAndRow(2, 3);
            $objPHPExcel->getActiveSheet()->getStyle("A1:$col_status" . ($row - 1))->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $objPHPExcel->getActiveSheet()->getStyle("A1:$col_status" . ($row - 1))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save($dirName . '/skus_' . $xlsNum . '_' . $input['lang'] . '.xls');    //文件保存
        }
    }

    public function Filerepalce($str) {
        $name = str_replace("\n", '', str_replace("\r", ' ', $str));
        $name = str_replace('/', '_', $name);
        $name = str_replace('\\', '_', $name);
        $name = str_replace('*', '', $name);
        $name = str_replace(':', '', $name);
        $name = str_replace('?', '', $name);
        $name = str_replace('<', '【', $name);
        $name = str_replace('>', '】', $name);
        $name = str_replace('|', '_', $name);
        $name = str_replace('"', '', $name);
        return $name;
    }

    //获取后缀
    function getImageName($imgesName) {
        preg_match('/.*?.(\w+)?$/', $imgesName, $matchs);
        return isset($matchs[1]) ? $matchs[1] : '';
    }

    public function exportimg($images = [], $dir = null) {

        foreach ($images as $img) {


            $imgname = $this->getImageName($img['attach_url']);

            $img_data = null;
            $img_data = file_get_contents(Yaf_Application::app()->getConfig()->fastDFSUrl . $img['attach_url']);
            if ($img_data) {
                file_put_contents($dir . '_' . $imgname, $img_data);
            }
        }
    }

    //获取后缀
    function TechParasImg($TechParas, $lang, $dir = null) {

        preg_match_all('/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/', $TechParas, $images);
        if ($images) {
            foreach ($images[1] as $img) {


                $imgname = $this->getImageName($img);

                $img_data = null;
                $img_data = file_get_contents($img);
                if ($img_data) {
                    file_put_contents($dir . '_tech_' . $lang . '_' . $imgname, $img_data);
                }
            }
        }
    }

}
