<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MarketAreaModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   营销区域
 */
class MarketAreaModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_operation';
    protected $tableName = 'market_area';

    public function __construct() {
        parent::__construct();
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    private function _getCondition($condition) {
        $data = ['zh.deleted_flag' => 'N'];
        $data['zh.lang'] = 'zh';
        //$this->_getValue($data, $condition, 'lang', 'string');
        $this->_getValue($data, $condition, 'bn', 'string', 'zh.bn');
        $this->_getValue($data, $condition, 'parent_bn', 'string', 'zh.parent_bn');
        $this->_getValue($data, $condition, 'name', 'like', 'zh.name');
        $this->_getValue($data, $condition, 'status', 'string', 'zh.status');
        if (!$data['zh.status']) {
            $data['zh.status'] = 'VALID';
        }
        $this->_getValue($data, $condition, 'url', 'like', 'zh.url');

        return $data;
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @param string $order 排序
     * @param bool $type 是否分页
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    public function getlist($condition, $order = 'zh.id desc') {
        try {
            $data = $this->_getCondition($condition);
            $redis_key = md5(json_encode($data));
            if (redisHashExist('Market_Area', $redis_key)) {
                return json_decode(redisHashGet('Market_Area', $redis_key), true);
            }
            $result = $this->alias('zh')
                            ->join('erui_operation.market_area as en on '
                                    . 'en.bn=zh.bn and en.lang=\'en\' and en.`status` = \'VALID\' ', 'inner')
                            ->field('zh.bn,zh.parent_bn,zh.name as zh_name,zh.url,en.name as en_name ')
                            ->where($data)->order($order)->select();
            redisHashSet('Market_Area', $redis_key, json_encode($result));

            return $result;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return [];
        }
    }

    /**
     * Description of 获取总数
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    public function getCount($condition) {
        try {
            $data = $this->getCondition($condition);
            $redis_key = md5(json_encode($data)) . '_COUNT';
            if (redisHashExist('Market_Area', $redis_key)) {
                return redisHashGet('Market_Area', $redis_key);
            }
            $count = $this->where($data)->count();

            redisHashSet('Market_Area', $redis_key, $count);

            return $count;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return 0;
        }
    }

    /**
     * Description of 详情
     * @param string $bn 区域简码
     * @param string $lang 语言 默认英文
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    public function info($bn = '', $lang = 'en') {
        $where['bn'] = $bn;
        $where['lang'] = $lang;
        $redis_key = md5(json_encode($where));
        if (redisHashExist('Market_Area', $redis_key)) {
            return json_decode(redisHashGet('Market_Area', $redis_key), true);
        }
        if (!empty($where)) {
            $row = $this->where($where)
                    ->field('id,lang,bn,name,url')
                    ->find();
            redisHashSet('Market_Area', $redis_key, json_encode($row));
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int  $bn
     * @return bool
     * @author jhw
     */
    public function delete_data($bn = '') {
        if ($bn) {
            $bns = explode(',', $bn);
            if (is_array($bns)) {
                $where['bn'] = ['in', $bns];
            } else {
                $where['bn'] = $bn;
            }
        }
        if (!empty($where['bn'])) {
            try {

                $flag = $this->where($where)->save(['status' => 'DELETED', 'deleted_flag' => 'Y']);

                return $flag;
            } catch (Exception $ex) {
                Log::write($ex->getMessage(), Log::ERR);
            }
        } else {


            return false;
        }
    }

    /**
     * Description of 判断数据是否存在
     * @param array $where 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    public function Exits($where) {

        $row = $this->where($where)
                ->field('id,status')
                ->find();
        return empty($row) ? false : $row;
    }

    /**
     * Description of 增
     * @param array $create 新增的数据
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   营销区域
     */
    public function create_data($create = []) {
        if (isset($create['en']['name']) && isset($create['zh']['name'])) {
            $newbn = ucwords($create['en']['name']);
            $create['en']['name'] = ucwords($create['en']['name']);
            $langs = ['en', 'zh', 'es', 'ru'];
            $this->startTrans();
            foreach ($langs as $lang) {
                $create['bn'] = $newbn;
                $flag = $this->_updateandcreate($create, $lang, $newbn);
                if (!$flag) {
                    $this->rollback();
                    return false;
                }
            }
            $market_area_team_model = new MarketAreaTeamModel();
            $market_area_team_model->updateandcreate($create, $newbn);
            $this->commit();

            return true;
        } else {
            return false;
        }
    }

    /**
     * 修改数据
     * @param  int $data id
     * @return bool
     * @author jhw
     */
    public function update_data($data) {
        if (!isset($data['bn']) || !$data['bn']) {
            return false;
        }
        $newbn = trim(ucwords($data['en']['name']));
        $data['en']['name'] = trim(ucwords($data['en']['name']));
        $this->startTrans();
        $langs = ['en', 'zh', 'es', 'ru'];
        foreach ($langs as $lang) {
            $flag = $this->_updateandcreate($data, $lang, $newbn);
            if (!$flag) {
                $this->rollback();

                return false;
            }
        }
        $market_area_team_model = new MarketAreaTeamModel();
        $market_area_team_model->updateandcreate($data, $newbn);
        $this->commit();

        return true;
    }

    private function _updateandcreate($data, $lang, $newbn) {
        if (isset($data[$lang]['name'])) {
            $where['lang'] = $lang;
            $where['bn'] = trim($data['bn']);
            $arr['bn'] = $newbn;
            $arr['lang'] = $lang;
            $arr['name'] = trim($data[$lang]['name']);
            $arr['status'] = 'VALID';
            if ($this->Exits($where)) {
                $arr['updated_at'] = date('Y-m-d H:i:s');
                $arr['updated_by'] = defined('UID') ? UID : 0;
                $arr['deleted_flag'] = 'N';

                $flag = $this->where($where)->save($arr);
                return $flag;
            } else {
                $arr['updated_at'] = date('Y-m-d H:i:s');
                $arr['updated_by'] = defined('UID') ? UID : 0;

                $arr['created_at'] = date('Y-m-d H:i:s');
                $arr['created_by'] = defined('UID') ? UID : 0;

                $flag = $this->add($arr);
                return $flag;
            }
        } else {
            return true;
        }
    }

    /*
     * 根据用户ID 获取用户姓名
     * @param array $user_ids // 用户ID
     * @return mix
     * @author  zhongyg
     *  @date    2017-8-5 15:39:16
     * @version V2.0
     * @desc   ES 产品
     */

    public function getNamesBybns($bns) {

        try {
            $where = ['deleted_flag' => 'N'];

            if (is_string($bns)) {
                $where['bn'] = $bns;
            } elseif (is_array($bns)) {
                $where['bn'] = ['in', $bns];
            } else {
                return false;
            }
            $where['lang'] = LANG_SET;
            $areas = $this->where($where)->field('bn,name')->select();


            $area_names = [];
            foreach ($areas as $area) {
                $area_names[trim($area['bn'])] = $area['name'];
            }

            return $area_names;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 根据bn获取信息，通过语言分组显示
     * @author link 2017-10-31
     * @param string $bn
     * @return array|bool
     */
    public function getInfoByBn($bn = '') {
        if (empty($bn)) {
            return false;
        }

        try {
            $where = ['bn' => $bn, 'deleted_flag' => 'N'];
            $areas = $this->field('lang,bn,name')->where($where)->select();
            $area_names = [];
            foreach ($areas as $area) {
                $area_names[$area['lang']] = $area;
            }
            return $area_names;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * @desc 通过区域简称获取名称
     *
     * @param string $bn 区域简称
     * @param string $lang 语言
     * @return mixed
     * @author liujf
     * @time 2018-03-21
     */
    public function getAreaNameByBn($bn, $lang = 'zh') {
        return $this->where(['bn' => $bn, 'lang' => $lang, 'deleted_flag' => 'N'])->getField('name');
    }

    /*
     * Description of 获取营销区域
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    public function setArea(&$arr) {
        if ($arr) {

            $area_bns = [];
            foreach ($arr as $key => $val) {

                if (isset($val['area_bn']) && $val['area_bn']) {
                    $area_bns[] = trim($val['area_bn']);
                }
            }
            if ($area_bns) {
                $area_names = $this->getNamesBybns($area_bns);
                foreach ($arr as $key => $val) {
                    if (trim($val['area_bn']) && isset($area_names[trim($val['area_bn'])])) {
                        $val['area_name'] = $area_names[trim($val['area_bn'])];
                    } else {
                        $val['area_name'] = '';
                    }
                    $arr[$key] = $val;
                }
            }
        }
    }

}
