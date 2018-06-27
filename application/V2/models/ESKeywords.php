<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ESKeyword
 * @author  zhongyg
 * @date    2018-5-11 12:38:15
 * @version V2.0
 * @desc
 */
class ESKeywordsModel extends PublicModel {

    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'show_cat_keywords';
    protected $es_index = 'erui_keyword';

//put your code here

    public function __construct() {
        parent::__construct();
    }

    /*
     * 批量导入产品数据到ES
     * @author zyg 2017-07-31
     * @param string $lang // 语言 zh en ru es
     * @return mix
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function import($lang = 'en') {
        try {
            $max_id = 0;
            $where = ['lang' => $lang, 'id' => ['gt', 0]];

            $count = $this->where($where)->count('id');

            $es = new ESClient();
            echo '共有', $count, '条记录需要导入!', PHP_EOL;
// die;
            ob_flush();

            flush();
            $k = 1;
            for ($i = 0; $i < $count; $i += 100) {
                $keywords = $this->field('id,lang,cat_no,cat_name,country_bn,market_area_bn,name,created_by,created_at,updated_by,updated_at,checked_by,checked_at,deleted_flag')
                                ->where($where)->limit($i, 100)
                                ->order('id ASC')->select();
                $time1 = microtime(true);
                foreach ($keywords as $key => $item) {

                    $body = $item;
                    foreach ($body as $k => $val) {
                        $body[$k] = !empty($val) ? trim($val) : '';
                    }
                    $flag = $es->add_document($this->es_index, 'keyword_' . $lang, $body, $item['id']);

                    if ($key === 99) {
                        $max_id = $item['id'];
                    }
                    var_dump($flag);
                }
                echo microtime(true) - $time1, "\r\n";
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return false;
        }
    }

}
