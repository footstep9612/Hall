<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Showmaterialcat
 *
 * @author zhongyg
 */
class ShowMaterialCatModel extends PublicModel {

    //put your code here
    //put your code here
    protected $tableName = 'show_material_cat';
    protected $dbName = 'erui2_goods'; //数据库名称

    public function __construct($str = '') {
        parent::__construct($str);
    }

    /*
     * 获取物料分类
     *
     */

    public function getmaterialcatnobyshowcatno($showcatno, $lang = 'en') {

        try {
            return $this->alias('ms')
                            ->join('erui2_goods.show_cat s on s.cat_no=ms.show_cat_no ')
                            ->where(['ms.show_cat_no' => $showcatno
                                , 'ms.status' => 'VALID',
                                's.status' => 'VALID',
                                's.lang' => $lang,
                            ])
                            ->field('ms.material_cat_no,ms.show_cat_no,ms.status,s.name,s.parent_cat_no')
                            ->find();
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return [];
        }
    }

    /*
     * 获取物料分类
     *
     */

    public function getshowcatsBymaterialcatno($material_cat_nos, $lang = 'en', $show_cat_nos = []) {

        try {


            if ($material_cat_nos) {
                $material_cat_nos = array_values($material_cat_nos);
                $where = ['ms.material_cat_no' => ['in', $material_cat_nos]
                    , 'ms.status' => 'VALID',
                    's.status' => 'VALID',
                    's.lang' => $lang,
                ];
                if ($show_cat_nos) {
                    $where['ms.show_cat_no'] = ['in', $show_cat_nos];
                }

                $flag = $this->alias('ms')
                        ->join('erui2_goods.show_cat s on s.cat_no=ms.show_cat_no ', 'left')
                        ->where($where)
                        ->field('ms.material_cat_no,ms.show_cat_no as cat_no,'
                                . 'ms.status,s.name')
                        ->group('cat_no')
                        ->limit(0, 20)
                        ->select();

                return $flag;
            } else {
                return [];
            }
        } catch (Exception $ex) {
            var_dump($ex);
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return [];
        }
    }

    /*
     * 获取展示分类编号
     *
     */

    public function getshowcatnosBymatcatno($material_cat_no, $lang = 'en') {

        try {
            return $this->alias('ms')
                            ->join('erui2_goods.show_cat s on s.cat_no=ms.show_cat_no ', 'left')
                            ->where(['ms.material_cat_no' => $material_cat_no
                                , 'ms.status' => 'VALID',
                                's.status' => 'VALID',
                                's.lang' => $lang,
                            ])
                            ->field('ms.show_cat_no as cat_no')
                            ->group('ms.show_cat_no')
                            ->select();
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return [];
        }
    }

    /*
     * 获取物料分类
     *
     */

    public function getshowcatsBycatno($show_cat_nos, $lang = 'en') {

        try {

            if (!$show_cat_nos) {

                return [];
            }

            return $this->Table('erui2_goods.show_cat')
                            ->where(['cat_no' => ['in', $show_cat_nos]
                                , 'status' => 'VALID',
                                'lang' => $lang,
                            ])
                            ->field('name,cat_no,parent_cat_no')
                            ->select();
        } catch (Exception $ex) {
            Log::write(__CLASS__ . PHP_EOL . __FUNCTION__, Log::INFO);
            Log::write($ex->getMessage());
            return [];
        }
    }

    /*
     * 根据分类编码数组获取物料分类信息
     * @param mix $cat_nos // 物料分类编码数组
     * @param string $lang // 语言 zh en ru es
     * @return mix  规格信息
     * @author  zhongyg
     * @date    2017-8-1 16:50:09
     * @version V2.0
     * @desc   ES 产品
     */

    public function getshow_material_cats($cat_nos, $lang = 'en') {
        if (!$cat_nos || !is_array($cat_nos)) {
            return [];
        }
        try {

            $show_material_cats = $this->alias(smc)
                    ->join('erui2_goods.show_cat sc on smc.show_cat_no=sc.cat_no')
                    ->field('show_cat_no,material_cat_no')
                    ->where([
                        'smc.material_cat_no' => ['in', $cat_nos],
                        'sc.status' => 'VALID',
                        'sc.lang' => $lang,
                        'sc.id>0',
                        'smc.status' => 'VALID'])
                    ->select();
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
        $ret = [];
        if ($show_material_cats) {
            foreach ($show_material_cats as $item) {
                $ret[$item['material_cat_no']][$item['show_cat_no']] = $item['show_cat_no'];
            }
        }
        return $ret;
    }

    /**
     * 根据条件查询
     * @author link 2017-08-04
     * @param array $condition
     * @param string $field
     * @return array|bool
     */
    public function findByCondition($condition = [], $field = '') {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }

        if (is_array($field)) {
            $field = implode(',', $field);
        } elseif (empty($field)) {
            $field = 'show_cat_no,material_cat_no,status,created_by,created_at,updated_by,updated_at,checked_by,checked_at';
        }

        /**
         * 取缓存
         */
        if (redisHashExist('show_material_cat', md5(serialize($condition) . serialize($field)))) {
            return json_decode(redisHashGet('show_material_cat', md5(serialize($condition) . serialize($field))), true);
        }

        try {
            $result = $this->field($field)->where($condition)->select();
            if ($result) {
                redisHashSet('show_material_cat', md5(serialize($condition) . serialize($field)), json_encode($result));
                return $result;
            }
        } catch (Exception $e) {
            return false;
        }
        return array();
    }

}
