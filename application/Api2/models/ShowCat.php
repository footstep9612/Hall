<?php

/**
 * 展示分类
 * User: linkai
 * Date: 2017/6/15
 * Time: 15:52
 */
class ShowCatModel extends PublicModel {

    //状态
    const STATUS_DRAFT = 'DRAFT'; //草稿
    const STATUS_APPROVING = 'APPROVING'; //审核；
    const STATUS_VALID = 'VALID'; //生效；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    protected $dbName = 'erui_goods';
    protected $tableName = 'show_cat';

    public function __construct() {

        parent::__construct();
    }

    /*
     * 获取物料分类
     *
     */

    public function getshowcatsByshowcatnos($show_cat_nos, $lang = 'en', $page_flag = true) {

        try {

            if ($show_cat_nos) {
                $show_cat_nos = array_values($show_cat_nos);

                $where = [
                    'cat_no' => ['in', $show_cat_nos],
                    'status' => 'VALID',
                    'lang' => $lang,
                ];
                $this
                        ->where($where)
                        ->field('cat_no,name')
                        ->group('cat_no');
                if ($page_flag) {
                    $this->limit(0, 20);
                }

                $flag = $this->select();

                return $flag;
            } else {
                return [];
            }
        } catch (Exception $ex) {

            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return [];
        }
    }

}
