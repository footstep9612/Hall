<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BoxType
 * @author  zhongyg
 * @date    2017-8-1 16:49:20
 * @version V2.0
 * @desc   发货箱型
 */
class BoxTypeModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_dict';
    protected $tableName = 'box_type';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /*
     * 自动完成
     */

    protected $_auto = array(
        array('status', 'VALID'),
    );
    /*
     * 自动表单验证
     */
    protected $_validate = array(
        array('lang', 'require', '语言不能为空'),
        array('bn', 'require', '发货箱型简称不能为空'),
        array('box_type_name', 'require', '发货箱型不能为空'),
    );

    /*
     * 获取当前时间
     */

    function getDate() {
        return date('Y-m-d H:i:s');
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    private function _getCondition(&$condition) {
        $data = [];
        $this->_getValue($data, $condition, 'lang');
        $this->_getValue($data, $condition, 'bn');
        $this->_getValue($data, $condition, 'box_type_name', 'like');
        $this->_getValue($data, $condition, 'status');
        if (!isset($data['status'])) {
            $data['status'] = 'VALID';
        }
        $this->_getValue($data, $condition, 'deleted_flag', 'bool');

        return $data;
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @date    2017-8-1 16:20:48
     * @author zyg
     */
    public function getlist($condition, $order = 'id desc') {
        try {
            $where = $this->_getCondition($condition);
            $redis_key = md5(json_encode($where) . $order);
            if (redisHashExist('BoxType', $redis_key)) {
                return json_decode(redisHashGet('BoxType', $redis_key), true);
            }
            $result = $this->field('id,bn,box_type_name')->where($where)->order($order)->select();
            redisHashSet('BoxType', $redis_key, json_encode($result));
            return $result;
        } catch (Exception $ex) {

            return [];
        }
    }
    
    /**
     * @desc 通过发货箱型简称获取名称
     *
     * @param string $bn 发货箱型简称
     * @param string $lang 语言
     * @return mixed
     * @author liujf
     * @time 2018-06-28
     */
    public function getBoxTypeNameByBn($bn, $lang = 'zh') {
        return $this->where(['bn' => $bn, 'lang' => $lang, 'deleted_flag' => 'N'])->getField('box_type_name');
    }

}
