<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jhw
 * @desc 运输方式
 */
class TransModeModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_dict';
    protected $tableName = 'trans_mode';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data, $limit, $order = 'id desc') {
        if (!empty($limit)) {
            return $this->field('lang,bn,trans_mode,id')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        } else {
            return $this->field('lang,bn,trans_mode,id')
                            ->where($data)
                            ->order($order)
                            ->select();
        }
    }

    /**
     * 获取列表
     * @param  int  $id
     * @return array
     * @author jhw
     */
    public function detail($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            $row = $this->where($where)
                    ->field('id,lang,bn,name,time_zone,region')
                    ->find();
            return $row;
        } else {
            return false;
        }
    }

    /**
     * 删除数据
     * @param  int  $id
     * @return bool
     * @author jhw
     */
    public function delete_data($id = '') {
        $where['id'] = $id;
        if (!empty($where['id'])) {
            return $this->where($where)
                            ->save(['status' => 'DELETED', 'deleted_flag' => 'N']);
        } else {
            return false;
        }
    }

    /**
     * 修改数据
     * @param  int $id id
     * @return bool
     * @author jhw
     */
    public function update_data($data, $where) {
        if (isset($data['lang'])) {
            $arr['lang'] = $data['lang'];
        }
        if (isset($data['bn'])) {
            $arr['bn'] = $data['bn'];
        }
        if (isset($data['name'])) {
            $arr['name'] = $data['name'];
        }
        if (isset($data['time_zone'])) {
            $arr['time_zone'] = $data['time_zone'];
        }
        if (isset($data['region'])) {
            $arr['region'] = $data['region'];
        }
        $arr['deleted_flag'] = 'N';
        if (!empty($where)) {
            return $this->where($where)->save($arr);
        } else {
            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author jhw
     */
    public function create_data($create = []) {
        if (isset($create['lang'])) {
            $arr['lang'] = $create['lang'];
        }
        if (isset($create['bn'])) {
            $arr['bn'] = $create['bn'];
        }
        if (isset($create['name'])) {
            $arr['name'] = $create['name'];
        }
        if (isset($create['time_zone'])) {
            $arr['time_zone'] = $create['time_zone'];
        }
        if (isset($create['region'])) {
            $arr['region'] = $create['region'];
        }
        $data = $this->create($arr);
        return $this->add($data);
    }

    /*
     * 条件
     */

    function getCondition($condition) {
        $where = ['deleted_flag' => 'N'];
        if (isset($condition['id']) && $condition['id']) {
            $where['id'] = $condition['id'];
        }
        if (isset($condition['lang']) && $condition['lang']) {
            $where['lang'] = $condition['lang'];
        }
        if (isset($condition['bn']) && $condition['bn']) {
            $where['bn'] = $condition['bn'];
        }
        if (isset($condition['trans_mode']) && $condition['trans_mode']) {
            $where['trans_mode'] = ['like', '%' . $condition['trans_mode'] . '%'];
        }

        return $where;
    }

    /*
     * 获取数据
     */

    public function getCount($condition) {
        try {
            $data = $this->getCondition($condition);
            return $this->where($data)->count();
        } catch (Exception $ex) {

            return 0;
        }
    }

    /**
     * 配送时效列表
     * 配送时效 不区分产品，只根据目的国（起始国、地点暂定为中国、东营），根据这三个条件查询，并按贸易术语分开展示
     * @param string $to_country 目的国
     * @param string $from_country 起始国
     * @param string $warehouse 起始仓库
     * @return array
     */
    public function getListbycondition($condition = '') {
        $where = $this->getCondition($condition);
        try {
            $field = 'id,bn,trans_mode,lang';

            $pagesize = 10;
            $current_no = 1;
            if (isset($condition['current_no'])) {
                $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1;
            }
            if (isset($condition['pagesize'])) {
                $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10;
            }
            $from = ($current_no - 1) * $pagesize;
            $result = $this->field($field)
                    ->limit($from, $pagesize)
                    ->where($where)
                    ->select();
            return $result;
        } catch (Exception $e) {
            return array();
        }
    }
    
    /**
     * @desc 通过运输方式简称获取名称
     *
     * @param string $bn 运输方式简称
     * @param string $lang 语言
     * @return mixed
     * @author liujf
     * @time 2018-05-07
     */
    public function getTransModeByBn($bn, $lang = 'zh') {
        return $this->where(['bn' => $bn, 'lang' => $lang, 'deleted_flag' => 'N'])->getField('trans_mode');
    }
    public function transModeList($data){
        $lang=!empty($data['lang'])?$data['lang']:'zh';
        return $this->field('bn as trans_bn,trans_mode')->where(array('lang'=>$lang,'deleted_flag'=>'N'))->select();
    }
    public function portTypeModeList($data){
        $lang=!empty($data['lang'])?$data['lang']:'zh';
        return $this->table('erui_dict.port_type')
            ->field('port_bn,port_type')->where(array('lang'=>$lang,'deleted_flag'=>'N'))->select();
    }
}
