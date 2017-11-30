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

        $es_product_model = new EsProductModel();
        $count = $es_product_model->getCount($condition, $lang);


        $progress_redis['total'] = $count;
        if ($count <= 0) {
            jsonReturn('', ErrorMsg::FAILED, '无数据可导出');
        }

        //单excel显示条数
        //excel输出的起始行
        try {
            $this->Createxls($condition, $lang);
        } catch (Exception $e) {
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Export failed:' . $e, Log::ERR);
            return false;
        }
    }

    private function _getKeys() {

        return [
            '序号',
            'SPU编码',
            'SKU编码',
            '产品名称',
            '展示名称',
            '产品组',
            '产品品牌',
            '产品介绍',
            '技术参数',
            '执行标准',
            '质保期',
            '关键字',
            '型号',
            '供应商名称',
            '出货周期(天)',
            '最小包装内裸货商品数量',
            '商品裸货单位',
            '最小包装单位',
            '最小订货数量',
            '供应商供货价',
            '币种',
            '非固定属性',
            '物流信息',
            '裸货尺寸长(mm)',
            '裸货尺寸宽(mm)',
            '裸货尺寸高(mm)',
            '最小包装后尺寸长(mm)',
            '最小包装后尺寸宽(mm)',
            '最小包装后尺寸高(mm)',
            '净重(kg)',
            '毛重(kg)',
            '仓储运输包装及其他要求',
            '包装类型',
            '申报要素',
            '中文品名(报关用)',
            '海关编码',
            '成交单位',
            '退税率(%)',
            '监管条件',
            '境内货源地'
        ];
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
        if (!is_dir($diriamgesName)) {
            if (!mkdir($diriamgesName, 0777, true)) {
                Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirName . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                jsonReturn('', ErrorMsg::FAILED, '操作失败，请联系管理员');
            }
        }
        $keys = $this->_getKeys();
        $es = new ESClient();

        $es_product_model = new EsProductModel();
        for ($i = 0; $i < 4000; $i += 2000) {
            $result = $es_product_model->getList($condition, ['spu', 'material_cat_no', 'name', 'show_name', 'brand', 'keywords', 'exe_standard', 'tech_paras', 'profile', 'description', 'warranty', 'status', 'bizline', 'attachs'], $lang, $i, 2000);

            PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));

            $objPHPExcel = new PHPExcel();
            $objSheet = $objPHPExcel->setActiveSheetIndex(0);    //当前sheet
            $objSheet->setTitle('商品模板');
            $objSheet->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
            foreach ($keys as $k => $key) {
                $ColumnIndex = PHPExcel_Cell::stringFromColumnIndex($k);
                $objSheet->setCellValue($ColumnIndex . 1, $key);
            }
            $objSheet->getStyle("AO")->getFont()->setBold(true);    //粗体
            $objSheet->setCellValue("AO1", '审核状态');
            $spus = [];

            foreach ($result as $j => $item) {
                $name = $this->Filerepalce($item['name']);

                $brand_name = $this->Filerepalce($item['brand']['name']);

                $dirimg = $diriamgesName . DS . $name . '_' . $brand_name . '_' . $item['spu'];

                if (!is_dir($dirimg)) {
                    if (!mkdir($dirimg, 0777, true)) {
                        Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Notice:' . $dirimg . '创建失败，如影响后面流程，请尝试手动创建', Log::NOTICE);
                        jsonReturn('', ErrorMsg::FAILED, '操作失败，请联系管理员');
                    }
                }
                $this->_save($item, $dirimg, $lang, $objSheet);
                $spus[] = $item['spu'];
            }
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel2007");
            $objWriter->save($dirName . DS . '商品模板_' . $i . '.xlsx');

            unset($objPHPExcel, $objSheet);
        }
        return true;
    }

    public static $j = 2;
    public static $i = 1;

    public function _save($item, $dirimg, $lang, $objSheet, $save_sku = true) {
        $es_goods_model = new EsGoodsModel();
        $es = new ESClient();
        $goods = $es_goods_model->getgoods(['spu' => $item['spu'],
            'status' => 'ALL',
            'onshelf_flag' => 'A',
            'pagesize' => 10000,
                ], null, 'zh');
        $ret_en = $es->get('erui_goods', 'product_en', $item['spu']);
        $item_en = $ret_en['_source'];
        $attachs = json_decode($item['attachs'], true);


        if (!empty($attachs)) {
            $this->exportimg($attachs['BIG_IMAGE'], $dirimg);
        }

        if (!empty($item['tech_paras'])) {
            $this->TechParasImg($item['tech_paras'], $lang, $dirimg);
        }
        if (!empty($item_en['tech_paras'])) {
            $this->TechParasImg($item_en['tech_paras'], $lang, $dirimg);
        }
        foreach ($goods[0]['hits']['hits'] as $good) {
            $ret_goods_en = $es->get('erui_goods', 'goods_en', $good['_id']);

            $goods_en = $ret_goods_en['_source'];
            $this->_savesku($item, $good['_source'], $objSheet);

            if (!empty($goods_en)) {
                $this->_savesku($item_en, $goods_en, $objSheet);
            }
            self::$i++;
        }
    }

    public function _savesku($product, $goods, $objSheet) {
        $objSheet->setCellValue("A" . (self::$j ), ' ' . self::$i);
        $objSheet->setCellValue("B" . (self::$j ), ' ' . $product['spu']);
        $objSheet->setCellValue("C" . (self::$j ), ' ' . $goods['sku']);
        $objSheet->setCellValue("D" . (self::$j ), ' ' . $product['name']);
        $objSheet->setCellValue("E" . (self::$j ), ' ' . $product['show_name']);
        $objSheet->setCellValue("F" . (self::$j ), ' ' . $product['bizline']['name']);
        $objSheet->setCellValue("G" . (self::$j ), ' ' . $product['brand']['name']);
        if ($product['profile']) {
            $objSheet->setCellValue("H" . (self::$j ), ' ' . $product['profile']);
        } else {
            $objSheet->setCellValue("H" . (self::$j ), ' ' . $product['description']);
        }
        $objSheet->setCellValue("I" . (self::$j ), ' ' . $product['tech_paras']);
        $objSheet->setCellValue("J" . (self::$j ), ' ' . $product['exe_standard']);
        $objSheet->setCellValue("K" . (self::$j ), ' ' . $product['warranty']);
        $objSheet->setCellValue("L" . (self::$j ), ' ' . $product['keywords']);
        $objSheet->setCellValue("M" . (self::$j ), ' ' . $goods['model']);
        $objSheet->setCellValue("N" . (self::$j ), ' ' . $goods['suppliers'][0]['name']);
        $objSheet->setCellValue("O" . (self::$j ), ' ' . $goods['exw_days']);
        $objSheet->setCellValue("P" . (self::$j ), ' ' . $goods['min_pack_naked_qty']);
        $objSheet->setCellValue("Q" . (self::$j ), ' ' . $goods['nude_cargo_unit']);
        $objSheet->setCellValue("R" . (self::$j ), ' ' . $goods['min_pack_unit']);
        $objSheet->setCellValue("S" . (self::$j ), ' ' . $goods['min_order_qty']);
        $objSheet->setCellValue("T" . (self::$j ), ' ' . $goods['purchase_price']);
        $objSheet->setCellValue("U" . (self::$j ), ' ' . $goods['purchase_price_cur_bn']);
        $spec_attrs = [];
        if (isset($goods['attrs']['spec_attrs']) && $goods['attrs']['spec_attrs']) {

            foreach ($goods['attrs']['spec_attrs'] as $spec) {
                $spec_attrs[$spec['name']] = $spec['value'];
            }
        }

        $objSheet->setCellValue("V" . (self::$j ), ' ' . json_encode($spec_attrs, 256));
        $objSheet->setCellValue("W" . (self::$j ), ' ');
        $objSheet->setCellValue("X" . (self::$j ), ' ' . $goods['nude_cargo_l_mm']);
        $objSheet->setCellValue("Y" . (self::$j ), ' ' . $goods['nude_cargo_w_mm']);
        $objSheet->setCellValue("Z" . (self::$j ), ' ' . $goods['nude_cargo_h_mm']);
        $objSheet->setCellValue("AA" . (self::$j ), ' ' . $goods['min_pack_l_mm']);
        $objSheet->setCellValue("AB" . (self::$j ), ' ' . $goods['min_pack_w_mm']);
        $objSheet->setCellValue("AC" . (self::$j ), ' ' . $goods['min_pack_h_mm']);
        $objSheet->setCellValue("AD" . (self::$j ), ' ' . $goods['net_weight_kg']);
        $objSheet->setCellValue("AE" . (self::$j ), ' ' . $goods['gross_weight_kg']);
        $objSheet->setCellValue("AF" . (self::$j ), ' ' . $goods['compose_require_pack']);
        $objSheet->setCellValue("AG" . (self::$j ), ' ' . $goods['pack_type']);
        $objSheet->setCellValue("AH" . (self::$j ), ' ');
        $objSheet->setCellValue("AI" . (self::$j ), ' ' . $goods['name_customs']);
        $objSheet->setCellValue("AJ" . (self::$j ), ' ' . $goods['hs_code']);
        $objSheet->setCellValue("AK" . (self::$j ), ' ' . $goods['tx_unit']);
        $objSheet->setCellValue("AL" . (self::$j ), ' ' . $goods['tax_rebates_pct']);
        $objSheet->setCellValue("AM" . (self::$j ), ' ' . $goods['regulatory_conds']);
        $objSheet->setCellValue("AN" . (self::$j ), ' ' . $goods['commodity_ori_place']);


        $status = '';
        switch ($goods['status']) {
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
                $status = $goods['status'];
                break;
        }

        $objSheet->setCellValue("AO" . (self::$j ), ' ' . $status);
        echo self::$j, PHP_EOL;
        self::$j++;
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
        preg_match('/(\w+\.\w+)?$/', $imgesName, $matchs);
        return isset($matchs[1]) ? $matchs[1] : '';
    }

    public function exportimg($images = [], $dir = null) {

        foreach ($images as $img) {


            $imgname = $this->getImageName($img['attach_url']);

            $img_data = null;
            $img_data = file_get_contents(Yaf_Application::app()->getConfig()->fastDFSUrl . $img['attach_url']);
            if ($img_data) {
                file_put_contents($dir . DS . $imgname, $img_data);
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
                    file_put_contents($dir . DS . 'tech_' . $lang . '_' . $imgname, $img_data);
                }
            }
        }
    }

}
