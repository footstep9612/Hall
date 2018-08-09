<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Brand
 *
 * @author zhongyg
 */
class BrandModel extends PublicModel {

//put your code here

    protected $tableName = 'brand';
    protected $dbName = 'erui_dict'; //数据库名称

    const STATUS_DRAFT = 'DRAFT'; //草稿
    const STATUS_APPROVING = 'APPROVING'; //审核；
    const STATUS_VALID = 'VALID'; //生效；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    protected $langs = ['en', 'es', 'ru', 'zh'];

    public function __construct() {
        parent::__construct();
    }

    /*
     * 自动完成
     */

    protected $_auto = array(
        array('status', 'VALID'),
        array('created_at', 'getDate', 1, 'callback'),
    );
    /*
     * 自动表单验证
     */
    protected $_validate = array(
        array('brand', 'require', '品牌信息不能为空'),
    );

    /*
     * 获取当前时间
     */

    function getDate() {
        return date('Y-m-d H:i:s');
    }

    /**
     * 条件解析
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    private function _getcondition($condition, $lang = '') {

        $where = ['deleted_flag' => 'N'];
        $this->_getValue($where, $condition, 'id', 'string');

        $brand_table = $this->getTableName();
        $this->_getValue($where, $condition, 'status', 'string', 'status', 'VALID');

        if (!empty($condition['name']) && $lang) {
            $name = trim($condition['name']);
            $map1[] = 'locate(\'"lang":"' . $lang . '"\',`brand`)';
            $map1[] = 'locate(\'"lang": "' . $lang . '"\',`brand`)';

            $map1['_logic'] = 'or';
            $where[]['_complex'] = $map1;
            $map2[] = 'locate(\'"name":"' . $name . '\',`brand`)';
            $map2[] = 'locate(\'"name": "' . $name . '\',`brand`)';
            $map2['_logic'] = 'or';
            $where[]['_complex'] = $map2;
        } elseif ($lang) {
            $map1[] = 'locate(\'"lang":"' . $lang . '"\',`brand`)';
            $map1[] = 'locate(\'"lang": "' . $lang . '"\',`brand`)';
            $map1['_logic'] = 'or';
            $where[]['_complex'] = $map1;
        } elseif (!empty($condition['name'])) {
            $name = trim($condition['name']);
            $map2[] = 'locate(\'"name":"' . $name . '\',`brand`)';
            $map2[] = 'locate(\'"name": "' . $name . '\',`brand`)';
            $map2['_logic'] = 'or';
            $where[]['_complex'] = $map2;
        }
        return $where;
    }

    /**
     * 获取数据条数
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    public function getCount($condition, $lang = '') {
        $where = $this->_getcondition($condition, $lang);

        $redis_key = md5(json_encode($where) . $lang) . '_COUNT';
        if (redisHashExist('Brand', $redis_key)) {
            return redisHashGet('Brand', $redis_key);
        }
        try {
            $count = $this->where($where)
                    ->count('id');

            redisHashSet('Brand', $redis_key, $count);
            return $count;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return 0;
        }
    }

    /**
     * 获取列表
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    public function getlist($condition, $lang = '') {
        $where = $this->_getcondition($condition, $lang);
        list($row_start, $pagesize) = $this->_getPage($condition);


        $redis_key = md5(json_encode($where) . $lang . $row_start . $pagesize);
        if (redisHashExist('Brand', $redis_key)) {
            return json_decode(redisHashGet('Brand', $redis_key), true);
        }
        try {
            $item = $this->where($where)
                    ->field('id, brand, status, created_by, '
                            . 'created_at, updated_by, updated_at')
                    ->order('id desc')
                    ->limit($row_start, $pagesize)
                    ->select();

            redisHashSet('Brand', $redis_key, json_encode($item));
            return $item;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

    /**
     * 获取列表
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    public function listall($condition, $lang = '', $field = 'id,brand') {
        $where = $this->_getcondition($condition, $lang);

        $redis_key = md5(json_encode($where) . $field) . $lang;
        if (redisHashExist('Brand', $redis_key)) {
            return json_decode(redisHashGet('Brand', $redis_key), true);
        }
        try {
            $item = $this->where($where)
                    ->field($field)
                    ->order('id desc')
                    ->select();
            redisHashSet('Brand', $redis_key, json_encode($item));
            return $item;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

    /**
     * 获取列表
     * @param  string $code 编码
     * @param  int $id id
     * @param  string $lang 语言
     * @return mix
     * @author zyg
     */
    public function info($id = '', $status = 'VALID') {
        if ($id) {
            $where['id'] = $id;
        } else {
            return [];
        }
        $redis_key = $id;
        if (redisHashExist('Brand', $redis_key)) {
            return json_decode(redisHashGet('Brand', $redis_key), true);
        }
        $item = $this->where($where)
                ->find();
        redisHashSet('Brand', $redis_key, json_encode($item));
        return $item;
    }

    /**
     * 判断是否存在
     * @param  mix $where 搜索条件
     * @return mix
     * @author zyg
     */
    public function Exist($where) {

        $row = $this->where($where)
                ->field('id')
                ->find();
        return empty($row) ? false : (isset($row['id']) ? $row['id'] : true);
    }

    /**
     * 删除数据
     * @param  string $id
     * @param  string $uid 用户ID
     * @return bool
     * @author zyg
     */
    public function delete_data($id = 0) {
        if (!$id) {
            return false;
        } elseif ($id) {
            $where['id'] = $id;
        }
        $flag = $this->where($where)
                ->save(['status' => self::STATUS_DELETED, 'deleted_flag' => 'Y']);

        if ($flag === false) {

            return false;
        } else {

            return true;
        }
    }

    /**
     * 删除数据
     * @param  string $brand_ids
     * @return bool
     * @author zyg
     */
    public function batchdelete_data($brand_ids = []) {
        if (!$brand_ids) {
            return false;
        } elseif ($brand_ids) {
            $where['id'] = ['in', $brand_ids];
        }
        $this->startTrans();

        $flag = $this->where($where)
                ->save(['status' => self::STATUS_DELETED, 'deleted_flag' => 'Y']);

        if ($flag) {
            $this->commit();

            return true;
        } else {
            $this->rollback();

            return false;
        }
    }

    /*
     * 判断品牌名称是否重复
     */

    public function brandExist($name, $lang, $id = null) {
        try {
            $where = [];
            if ($id) {
                $where['id'] = ['neq', $id];
            }
            $where['deleted_flag'] = 'N';
            $where[] = 'brand like \'%"lang":"' . $this->escapeString($lang) . '"%\' and brand like \'%"name":"' . $this->db()->escapeString($name) . '"%\'';
            $flag = $this->field('id')->where($where)
                    ->find();
            return $flag;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), $level);
            return false;
        }
    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return mix
     * @author zyg
     */
    public function update_data($upcondition = []) {
        $data['brand'] = $this->_getdata($upcondition);

        if (!$upcondition['id']) {
            return false;
        } else {
            $where['id'] = $upcondition['id'];
        }
        $data['updated_by'] = defined('UID') ? UID : 0;
        $data['updated_at'] = date('Y-m-d H:i:s');
        try {
            $flag = $this->where($where)->save($data);
            return $flag;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);

            return false;
        }
    }

    /**
     * 品牌数据组合
     * @param  mix $create 品牌数据
     * @return bool
     * @author zyg
     */
    private function _getdata($create) {

        $data = [
            'style' => $create['style'],
            'label' => $create['label'],
                //   'manufacturer' => $create['manufacturer']
        ];
        $datalist = [];
        foreach ($this->langs as $lang) {
            if (isset($create[$lang]) && isset($create[$lang]['name']) && $create[$lang]['name']) {

                $data['logo'] = $create[$lang]['logo'];
                $data['lang'] = $lang;
                $data['name'] = trim($create[$lang]['name']);
            }
            $datalist[] = $data;
        }
        return json_encode($datalist, 256);
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($createcondition = []) {

        $data['brand'] = $this->_getdata($createcondition);
        unset($data['id']);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = defined('UID') ? UID : 0;
        try {
            $flag = $this->add($data);

            if (!$flag) {
                return false;
            }

            return $flag;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);

            return false;
        }
    }

    /**
     * 导出品牌
     * @param  mix $input 导出条件
     * @return bool
     * @author zyg
     */
    public function export($input, $userInfo) {
        set_time_limit(0);  # 设置执行时间最大值
        @ini_set("memory_limit", "1024M"); // 设置php可使用内存

        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip, array('memoryCacheSize' => '512MB'));
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator($userInfo['name']);
        $objPHPExcel->getProperties()->setTitle("Brand List");
        $objPHPExcel->getProperties()->setLastModifiedBy($userInfo['name']);
        $objPHPExcel->createSheet();    //创建工作表
        $objPHPExcel->setActiveSheetIndex(0);    //设置工作表
        $objSheet = $objPHPExcel->getActiveSheet();    //当前sheet
        $objSheet->getDefaultStyle()->getFont()->setName("宋体")->setSize(11);
        $objSheet->getStyle("A1:F1")
                ->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objSheet->getStyle("A1:F1")->getFont()->setSize(11)->setBold(true);    //粗体
        $column_width_25 = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($column_width_25 as $column) {
            $objSheet->getColumnDimension($column)->setWidth(25);
        }
        $objPHPExcel->getActiveSheet(0)->getStyle('B')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);

        $objSheet->setTitle('品牌');
        $objSheet->setCellValue("A1", "序号");
        $objSheet->setCellValue("B1", "品牌ID");
        $objSheet->setCellValue("C1", "品牌名称(中)");
        $objSheet->setCellValue("D1", "品牌名称(英)");
        $objSheet->setCellValue("E1", "品牌名称(西)");
        $objSheet->setCellValue("F1", "品牌名称(俄)");

        $j = 2;    //excel控制输出
        $result = $this->listall($input, null, 'id,brand,status,created_by,created_at');
        $this->_setUserName($result);
        if ($result) {
            foreach ($result as $r) {
                $brand_ary = json_decode($r['brand'], true);
                $objSheet->setCellValue("A" . $j, $j - 1, PHPExcel_Cell_DataType::TYPE_STRING);
                $objSheet->setCellValue("B" . $j, ' ' . $r['id'], PHPExcel_Cell_DataType::TYPE_STRING);
                foreach ($brand_ary as $val) {
                    if ($val['lang'] == 'zh') {
                        $objSheet->setCellValue("C" . $j, isset($val['name']) ? $val['name'] : '');
                    } elseif ($val['lang'] == 'en') {
                        $objSheet->setCellValue("D" . $j, isset($val['name']) ? $val['name'] : '');
                    } elseif ($val['lang'] == 'es') {
                        $objSheet->setCellValue("E" . $j, isset($val['name']) ? $val['name'] : '');
                    } elseif ($val['lang'] == 'ru') {
                        $objSheet->setCellValue("F" . $j, isset($val['name']) ? $val['name'] : '');
                    }
                }

                $j++;
            }
        }

        $styleArray = ['borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THICK, 'style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('argb' => '00000000'),],],];
        $objSheet->getStyle('A1:F' . $j)->applyFromArray($styleArray);
        $objSheet->freezePaneByColumnAndRow(2, 2);
//保存文件
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "Excel5");
        $localDir = ExcelHelperTrait::createExcelToLocalDir($objWriter, 'Brand_' . date('YmdHis') . '.xls');

//把导出的文件上传到文件服务器上
        $server = Yaf_Application::app()->getConfig()->myhost;
        $fastDFSServer = Yaf_Application::app()->getConfig()->fastDFSUrl;
        $url = $server . '/V2/Uploadfile/upload';
        $data['tmp_name'] = $localDir;
        $data['type'] = 'application/xls';
        $data['name'] = pathinfo($localDir, PATHINFO_BASENAME);
        $fileId = postfile($data, $url);
        if ($fileId) {
            unlink($localDir);
            return array('url' => $fastDFSServer . $fileId['url'] . '?filename=' . $fileId['name'] . '.xls', 'name' => $fileId['name']);
        }
        Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . 'Update failed:' . $localDir . ' 上传到FastDFS失败', Log::INFO);
        return false;
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   物流费率
     */

    private function _setUserName(&$arr) {
        if ($arr) {
            $employee_model = new EmployeeModel();
            $userids = [];
            foreach ($arr as $key => $val) {
                $userids[] = $val['created_by'];
            }
            $usernames = $employee_model->getUserNamesByUserids($userids);
            foreach ($arr as $key => $val) {
                if ($val['created_by'] && isset($usernames[$val['created_by']])) {
                    $val['created_by_name'] = $usernames[$val['created_by']];
                } else {
                    $val['created_by_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

}
