<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of SpecialGoods
 * @author  zhongyg
 * @date    2018-05-17 13:38:48
 * @version V2.0
 * @desc
 */
class SpecialGoodsModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_mall';
    protected $tableName = 'special_goods';

    public function __construct() {
        parent::__construct();
    }

    public function createData($input=[]){
        if(!isset($input['spu']) || empty($input['spu'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择spu');
        }
        if(!isset($input['special_id']) || empty($input['special_id'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择专题');
        }
        if(!isset($input['lang']) || empty($input['lang'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择语言');
        }
        try{
            $data = [
                'special_id' => intval($input['special_id']),
                'cat_id' => isset($input['cat_id']) ? intval($input['cat_id']) : 0,
                'keyword_id' => isset($input['keyword_id']) ? intval($input['keyword_id']) : 0,
                'lang' => isset($input['lang']) ? strtolower($input['lang']) : 'en',
                'created_by' => defined('UID') ? UID : 0,
                'created_at' => date('Y-m-d H:i:s', time())
            ];
            $where = [
                'special_id' => $data['special_id'],
                'cat_id' => $data['cat_id'],
                'keyword_id' => $data['keyword_id'],
                'deleted_at' => ['exp', 'is null']
            ];
            $dataAll = [];
            if(is_array($input['spu'])){
                $input['spu'] = array_unique($input['spu']);
                foreach($input['spu'] as $spu){
                    $data['spu'] = $spu;
                    //$data['sku'] = substr($data['spu'],0,-1).'1';
                    $where['spu'] = $data['spu'];
                    //$where['sku'] = $data['sku'];
                    if(self::exist($where)){
                        jsonReturn('', MSG::MSG_FAILED,'已经存在');
                    }
                    $dataAll[] = $data;
                    unset($data['spu'],$where['spu']);
                }
            }else{
                $data['spu'] = trim($input['spu']);
                //$data['sku'] = isset($input['sku']) ? trim($input['sku']) : substr($data['spu'],0,-1).'1';
                $where['spu'] = $data['spu'];
                //$where['sku'] = $data['sku'];
                if(self::exist($where)){
                    jsonReturn('', MSG::MSG_FAILED,'已经存在');
                }
                $dataAll[] = $data;
                unset($data['spu'],$where['spu']);
            }
            return $this->addAll($dataAll);
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 修改
     * @param array $input
     * @return bool
     */
    public function updateData($input=[]){
        if(!isset($input['id']) || empty($input['id'])){
            jsonReturn('', MSG::MSG_FAILED,'请选择id');
        }
        try{

            $data = [
                'updated_by' => defined('UID') ? UID : 0,
                'updated_at' => date('Y-m-d H:i:s', time())
            ];

            if(isset($input['special_id'])){
                $data['special_id'] = intval($input['special_id']);
            }
            if(isset($input['cat_id'])){
                $data['cat_id'] = intval($input['cat_id']);
            }
            if(isset($input['keyword_id'])){
                $data['keyword_id'] = intval($input['keyword_id']);
            }
            if(isset($input['lang'])){
                $data['lang'] = trim($input['lang']);
            }
            if(isset($input['spu'])){
                $data['spu'] = trim($input['spu']);
            }

            return $this->where(['id'=>intval($input['id'])])->save($data);
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 删除
     * @param array $input
     * @return bool
     */
    public function deleteData($input=[]){
        if(empty($input)){
            return false;
        }
        try{
            if(isset($input['id'])){
                if(is_array($input['id'])){
                    $where['id'] = ['in', $input['id']];
                }else{
                    $where['id'] = intval($input['id']);
                }
            }else{
                if(isset($input['special_id'])){
                    $where['special_id'] = intval($input['special_id']);
                }
                if(isset($input['cat_id'])){
                    $where['cat_id'] = intval($input['cat_id']);
                }
                if(isset($input['keyword_id'])){
                    $where['keyword_id'] = intval($input['keyword_id']);
                }
                if(isset($input['spu'])){
                    if(is_array($input['spu'])){
                        $where['spu'] = ['in', $input['spu']];
                    }else{
                        $where['spu'] = trim($input['spu']);
                    }
                }
            }

            $data=[
                'deleted_by' => defined('UID') ? UID : 0,
                'deleted_at' => date('Y-m-d H:i:s', time())
            ];
            return $this->where($where)->save($data);
        }catch (Exception $e){
            return false;
        }
    }

    /**
     * 检测是否存在
     * @param array $where
     * @return bool|mixed
     */
    private function exist($where=[]){
        if(empty($where)){
            return true;
        }
        try{
            return $this->field('id')->where($where)->find();
        }catch (Exception $e){
            return true;
        };
    }

    /*
     * 根据PSU 获取专题 关键字信息
     */

    public function getSpecialsBySpu($spus, $lang = 'en') {


        if (empty($lang)) {
            return [];
        }
        if (empty($spus)) {
            return [];
        }
        $special_table = (new SpecialModel())->getTableName();

        $special_keyword_table = (new SpecialKeywordModel())->getTableName();
        $where = ['s.lang' => $lang, 'sg.spu' => ['in', $spus], 's.deleted_at is null',];

        $list = $this->alias('sg')
                ->field('sg.spu,sg.special_id,sg.keyword_id,s.name as special_name,sk.keyword,s.country_bn')
                ->join($special_table . ' s on s.id=sg.special_id', 'left')
                ->join($special_keyword_table . ' sk on sk.id=sg.keyword_id', 'left')
                ->where($where)
                ->group('sg.spu,sg.special_id,sg.keyword_id')
                ->select();
        $ret = [];

        foreach ($list as $special_goods) {
            $spu = $special_goods['spu'];
            unset($special_goods['spu']);
            $ret[$spu][] = $special_goods;
        }

        return $ret;
    }

}
