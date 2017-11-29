<?php

/**
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/11/21
 * Time: 14:18
 */
class GadgetModel extends PublicModel {

    protected $tableName = 'goods';
    protected $dbName = 'erui_goods'; //数据库名称
    protected $g_table = 'erui_goods.goods';

    public function excelAll() {
        set_time_limit(0);
        ini_set("memory_limit", -1);
        $title_spu = [
            '序号',
            'spu' => 'SPU编码',
            'sku' => 'SKU编码',
            'material_cat_no' => '物料分类编码',
            'name' => '产品名称',
            'show_name' => '展示名称',
            'bizline' => '产品组',
            'brand' => '产品品牌',
            'description' => '产品介绍',
            'tech_paras' => '技术参数',
            'exe_standard' => '执行标准',
            'warranty' => '质保期',
            'keywords' => '关键字',
            'sku_name' => 'SKU名称',
            'sku_show_name' => 'SKU展示名称',
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
            '物流信息',
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
            '申报要素',
            'name_customs' => '中文品名(报关用)',
            'hs_code' => '海关编码',
            'tx_unit' => '成交单位',
            'tax_rebates_pct' => '退税率(%)',
            'regulatory_conds' => '监管条件',
            'commodity_ori_place' => '境内货源地',
        ];

        //生产存放目录
        //$dirName = MYPATH . '/public/tmp/20171128171811';
        $dirName = MYPATH . '/public/tmp/' . date('YmdH', time());
        if (!is_dir($dirName)) {
            if (!mkdir($dirName, 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                jsonReturn('', ErrorMsg::FAILED, '操作失败[创建目录]，请联系管理员');
            }
        }

        //生成attachs目录用来记录附加情况
        if (!is_dir($dirName . '/attachs')) {
            if (!mkdir($dirName . '/attachs', 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                jsonReturn('', ErrorMsg::FAILED, '操作失败[创建目录]，请联系管理员');
            }
        }

        $productM = new ProductModel();
        $goodsM = new GoodsModel();
        $goodsattrM = new GoodsAttrModel();
        $gsModel = new GoodsSupplierModel();
        $supplierM = new SupplierModel();
        $bizlineM = new BizlineModel();
        $pattactM = new ProductAttachModel();
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $field = 'id,spu,lang,material_cat_no,name,show_name,bizline_id,brand,description,tech_paras,exe_standard,warranty,keywords,created_by,created_at,updated_by,updated_at';
        $field_sku = 'sku,lang,name,show_name,model,exw_days,min_pack_naked_qty,nude_cargo_unit,min_pack_unit,min_order_qty,purchase_price,purchase_price_cur_bn,nude_cargo_l_mm,nude_cargo_w_mm,nude_cargo_h_mm,min_pack_l_mm,min_pack_w_mm,min_pack_h_mm,net_weight_kg,gross_weight_kg,compose_require_pack,pack_type,name_customs,hs_code,tx_unit,tax_rebates_pct,regulatory_conds,commodity_ori_place,source,source_detail,status,created_by,created_at';
        $condition = ['deleted_flag' => 'N', 'created_at' => ['LT', '2017-11-01 00:00:00']];
        //$condition = ['deleted_flag'=>'N', 'created_at'=>['LT', '2017-11-01 00:00:00'],'spu'=>'3503010001260000'];
        $limit = 100;
        $page = 0;
        $spucount = $skucount = 0;
        do {
            $result = $productM->field($field)->where($condition)->order('id')->limit($page * $limit, $limit)->select();
            if ($result) {
                foreach ($result as $spuInfo) {
                    try {
                        $tmpDir = $dirName . '/' . $spuInfo['spu'];
                        $spu_zh = $productM->field('name,brand')->where(['spu' => $spuInfo['spu'], 'lang' => 'zh', 'deleted_flag' => 'N'])->find();
                        if ($spu_zh) {
                            $brandzh = json_decode($spu_zh['brand'], true);
                            $spu_zh['brand'] = $brandzh['name'] ? $brandzh['name'] : '';
                            $spu_zh['brand'] = $spu_zh['brand'];
                            $brandzh_str = $this->Filerepalce($spu_zh['brand']);
                            $spu_zh['name'] = $spu_zh['name'];
                            $tmpDir = $tmpDir . '_' . $this->Filerepalce($spu_zh['name']) . '_' . $brandzh_str;
                        }

                        if (!is_dir($tmpDir)) {
                            if (!mkdir($tmpDir, 0777, true)) {
                                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $tmpDir . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                                jsonReturn('', ErrorMsg::FAILED, '操作失败[创建目录]，请联系管理员');
                            }
                        }

                        //品牌
                        $brandInfo = json_decode($spuInfo['brand'], true);
                        $spuInfo['brand'] = $brandInfo['name'];
                        $spuInfo['brand'] = self::Filerepalce($spuInfo['brand']);
                        $spuInfo['name'] = self::Filerepalce($spuInfo['name']);

                        if (!is_dir($tmpDir . '/images')) {
                            if (!mkdir($tmpDir . '/images', 0777, true)) {
                                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '/images' . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                                jsonReturn('', ErrorMsg::FAILED, '操作失败[创建目录]，请联系管理员');
                            }
                        }

                        //图片
                        $attachs = $pattactM->field('attach_url,attach_name')->where(['spu' => $spuInfo['spu'], 'deleted_flag' => 'N'])->select();
                        if ($attachs && !file_exists($dirName . '/attachs/' . $spuInfo['spu'] . '.ath')) {
                            for ($i = 0; $i < count($attachs); $i++) {
                                $img = $fastDFSServer . $attachs[$i]['attach_url'];
                                $fileInfo = pathinfo($img);
                                $imgsource = file_get_contents($img);
                                file_put_contents($tmpDir . '/images/' . $this->Filerepalce($spu_zh['name'] ? $spu_zh['name'] : $spuInfo['name']) . '_' . $this->Filerepalce($spu_zh['brand'] ? $spu_zh['brand'] : $spuInfo['brand']) . '_' . str_pad($i + 1, 3, '0', STR_PAD_LEFT) . '.' . $fileInfo['extension'], $imgsource);
                            }
                            $handle = fopen($dirName . '/attachs/' . $spuInfo['spu'] . '.ath', "w");
                            if ($handle) {
                                fclose($handle);
                            }
                        }

                        if (preg_match_all('/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg]))[\'|\"].*?[\/]?>/', $spuInfo['tech_paras'], $match)) {
                            for ($i = 1; $i < count($match[1]); $i++) {
                                $fileInfo = pathinfo($match[1][$i]);
                                $imgsource = file_get_contents($match[1][$i]);
                                file_put_contents($tmpDir . '/images/' . $this->Filerepalce($spu_zh['name'] ? $spu_zh['name'] : $spuInfo['name']) . '_' . $this->Filerepalce($spu_zh['brand'] ? $spu_zh['brand'] : $spuInfo['brand']) . '_tech' . $spuInfo['lang'] . '_' . str_pad($i, 3, '0', STR_PAD_LEFT) . '.' . $fileInfo['extension'], $imgsource);
                            }
                        }

                        //产品组
                        if (!empty($spuInfo['bizline_id'])) {
                            $bizlineInfo = $bizlineM->field('name')->where(['id' => $spuInfo['bizline_id']])->find();
                        }
                        $spuInfo['bizline'] = $bizlineInfo ? $bizlineInfo['name'] : '';

                        $data_tmp = $spec_ary = $hs_ary = [];
                        $condition_sku = [
                            'spu' => $spuInfo['spu'],
                            'deleted_flag' => 'N',
                            'lang' => $spuInfo['lang']
                        ];
                        $resultSku = $goodsM->field($field_sku)->where($condition_sku)->order('created_at DESC')->select();
                        if ($resultSku) {
                            foreach ($resultSku as $skuInfo) {
                                $condition_attr = ['sku' => $skuInfo['sku'], 'lang' => $skuInfo['lang'], 'deleted_flag' => 'N'];
                                $attrs = $goodsattrM->field('spec_attrs,ex_hs_attrs')->where($condition_attr)->find();
                                $spec = json_decode($attrs['spec_attrs'], true);
                                foreach ($spec as $ak => $av) {
                                    if (!isset($spec_ary[$ak])) {
                                        $spec_ary[$ak] = $ak;
                                    }
                                    if (!isset($spec_ary[$ak])) {
                                        $spec_ary[$ak] = $ak;
                                    }
                                    $skuInfo[$ak] = $av;
                                }

                                $hs = json_decode($attrs['ex_hs_attrs'], true);
                                foreach ($hs as $hk => $hv) {
                                    if (!isset($hs_ary[$hk])) {
                                        $hs_ary[$hk] = $hk;
                                    }
                                    if (!isset($hs_ary[$hk])) {
                                        $hs_ary[$hk] = $hk;
                                    }
                                    $skuInfo[$hk] = $hv;
                                }
                                foreach ($skuInfo as $sk => $sv) {
                                    $gsInfo = $gsModel->field('supplier_id')->where(['sku' => $skuInfo['sku'], 'deleted_flag' => 'N'])->find();
                                    $supplierInfo = $supplierM->field('name')->where(['id' => $gsInfo['supplier_id']])->find();
                                    $spuInfo['supplier'] = $supplierInfo['name'];

                                    $sk = ($sk == 'name') ? 'sku_name' : $sk;
                                    $sk = ($sk == 'show_name') ? 'sku_show_name' : $sk;
                                    $spuInfo[$sk] = $sv;
                                }
                                $data_tmp[] = $spuInfo;
                            }
                            array_splice($title_spu, 25, 0, $spec_ary);
                            $hscount = count($title_spu);
                            array_splice($title_spu, $hscount, 0, $hs_ary);
                        }

                        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));
                        $objPHPExcel = new PHPExcel();
                        $col_status = PHPExcel_Cell::stringFromColumnIndex(count($title_spu));    //状态
                        $objPHPExcel->getSheet(0)->setCellValue($col_status . '1', '审核状态');
                        //设置表头
                        $excel_index = 0;
                        foreach ($title_spu as $title_key => $title_value) {
                            $colname = PHPExcel_Cell::stringFromColumnIndex($excel_index); //由列数反转列名(0->'A')
                            $objPHPExcel->getSheet(0)->setCellValue($colname . '1', $title_value);
                            $excel_index++;
                            $row = 2;    //内容起始行
                            foreach ($data_tmp as $r) {
                                if (isset($r[$title_key])) {
                                    $objPHPExcel->getSheet(0)->setCellValue($colname . $row, ' ' . $r[$title_key]);
                                } elseif (isset($r[$title_value])) {
                                    $objPHPExcel->getSheet(0)->setCellValue($colname . $row, ' ' . $r[$title_value]);
                                } else {
                                    $objPHPExcel->getSheet(0)->setCellValue($colname . $row, '');
                                }
                                if ($excel_index == count($data_tmp)) {
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
                                $skucount++;
                            }
                            $objPHPExcel->getActiveSheet()->getColumnDimension($colname)->setAutoSize(true);    //自适应宽
                        }
                        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
                        $ename = $spu_zh['name'] ? '_' . $spu_zh['name'] : '';
                        $ename .= $spu_zh['brand'] ? '_' . $spu_zh['brand'] : '';
                        $objWriter->save($tmpDir . DS . $this->Filerepalce($spuInfo['name']) . '_' . $this->Filerepalce($spuInfo['brand']) . '_' . $spuInfo['lang'] . '.xlsx');    //文件保存
                        $spucount++;
                        echo $spucount, PHP_EOL;
                    } catch (Exception $ex) {
                        Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . $spuInfo['spu'] . '失败', Log::NOTICE);
                        Log::write($ex->getMessage(), Log::ERR);
                    }
                }
            }

            echo $page, PHP_EOL;
            $page++;
            sleep(2);
        } while ($result && count($result) == $limit);
        ZipHelper::removeDir($dirName . '/attachs');    //清除attachs目录
        echo 'ok';
        die;
    }

    public function Filerepalce($str) {
        if (empty($str)) {
            return '';
        }
        $name = str_replace("\n", '', str_replace("\r", '', $str));
        $name = str_replace('/', '', $name);
        $name = str_replace('\\', '', $name);
        $name = str_replace('*', '', $name);
        $name = str_replace(':', '', $name);
        $name = str_replace('?', '', $name);
        $name = str_replace('<', '', $name);
        $name = str_replace('>', '', $name);
        $name = str_replace('|', '', $name);
        $name = str_replace('"', '', $name);
        return trim($name);
    }

}
