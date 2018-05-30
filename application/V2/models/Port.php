<?php

/**
 * Description of PortModel
 * @author  zhongyg
 * @date    2017-8-1 16:50:09
 * @version V2.0
 * @desc   口岸
 */
class PortModel extends PublicModel {

    protected $dbName = 'erui_dict'; //数据库名称
    protected $tableName = 'port'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取港口
     * @param string $lang
     * @param string $country
     * @return array|mixed
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    public function getPort($lang = '', $country = '') {
        $condition = array(
            'lang' => $lang,
            'deleted_flag' => 'N'
        );
        if (!empty($country)) {
            $condition['country_bn'] = $country;
        }
        if (redisHashExist('Port', md5(json_encode($condition)))) {
            return json_decode(redisHashGet('Port', md5(json_encode($condition))), true);
        }
        try {
            $field = 'lang,country_bn,bn,name,port_type,trans_mode,remarks,address,longitude,latitude';
            $result = $this->field($field)->where($condition)->order('bn')->select();
            if ($result) {
                redisHashSet('Port', md5(json_encode($condition)), json_encode($result));
                return $result;
            }
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * Description of 条件处理
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */
    private function _getCondition($condition) {
        $where = ['deleted_flag' => 'N'];
//        if (isset($condition['id']) && $condition['id']) {
//            $where['id'] = $condition['id'];
//        }
        if (isset($condition['lang']) && $condition['lang']) {
            $where['lang'] = $condition['lang'];
        }
        if (isset($condition['bn']) && $condition['bn']) {
            $where['bn'] = $condition['bn'];
        }
        if (isset($condition['country_bn']) && $condition['country_bn']) {
            $where['country_bn'] = $condition['country_bn'];
        }
        if (isset($condition['port_type']) && $condition['port_type']) {
            $where['port_type'] = $condition['port_type'];
        }
        if (isset($condition['trans_mode']) && $condition['trans_mode']) {
            $where['trans_mode'] = str_replace("+", " ", $condition['trans_mode']);
        }
        $this->_getValue($where, $condition, 'status', 'string', 'VALID');
        if (isset($condition['name']) && $condition['name']) {
            $where['name'] = ['like', '%' . $condition['name'] . '%'];
        }

        return $where;
    }

    /**
     * Description of 获取总数
     * @param array $condition 条件
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */
    public function getCount($condition) {
        try {
            $where = $this->_getCondition($condition);
            $redis_key = md5(json_encode($where)) . '_COUNT';
            if (redisHashExist('Port', $redis_key)) {
                return redisHashGet('Port', $redis_key);
            }
            $count = $this->where($where)->count();
            redisHashSet('Port', $redis_key, $count);
            return $count;
        } catch (Exception $ex) {

            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
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
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */
    public function getListbycondition($condition = '') {
        $where = $this->_getCondition($condition);



        list($from, $pagesize) = $this->_getPage($condition);
        $redis_key = md5(json_encode($where) . '_' . $from . '_' . $pagesize);
        if (redisHashExist('Port', $redis_key)) {
            return json_decode(redisHashGet('Port', $redis_key), true);
        }
        try {
            $field = 'id,bn,country_bn,name,lang,port_type,trans_mode,address,longitude,latitude';


            $result = $this->field($field)
                    ->limit($from, $pagesize)
                    ->where($where)
                    ->select();
            if ($result) {
                redisHashSet('Port', $redis_key, json_encode($result));
            }
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 配送时效列表
     * 配送时效 不区分产品，只根据目的国（起始国、地点暂定为中国、东营），根据这三个条件查询，并按贸易术语分开展示
     * @param string $to_country 目的国
     * @param string $from_country 起始国
     * @param string $warehouse 起始仓库
     * @return array
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */
    public function getAll($condition = '') {
        $where = $this->_getCondition($condition);

        $redis_key = md5(json_encode($where));
        if (redisHashExist('Port', $redis_key)) {
            return json_decode(redisHashGet('Port', $redis_key), true);
        }
        $country_model = new CountryModel();
        $country = $country_model->getTableName();
        try {
            $field = 'id,bn,country_bn,name,lang,port_type,trans_mode,address,longitude,latitude,'
                    . '(select name from ' . $country . ' where bn=country_bn and lang=port.lang and deleted_flag=\'N\' group by `name`) as country';
            $result = $this->field($field)
                    ->where($where)
                    ->select();
            if ($result) {
                redisHashSet('Port', $redis_key, json_encode($result));
            }
            return $result;
        } catch (Exception $ex) {
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($ex->getMessage(), LOG::ERR);
            return [];
        }
    }

    /**
     * 修改数据
     * @param  int $id id
     * @return bool
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */
    public function update_data($data, $uid = 0) {
        if (!isset($data['bn']) || !$data['bn']) {
            return false;
        }
        $newbn = ucwords($data['en']['name']);
        $data['en']['name'] = ucwords($data['en']['name']);
        $this->startTrans();
        $langs = ['en', 'zh', 'es', 'ru'];
        foreach ($langs as $lang) {
            $flag = $this->_updateandcreate($data, $lang, $newbn, defined('UID') ? UID : 0);
            if (!$flag) {
                $this->rollback();
                return false;
            }
        }
        $this->commit();
        return true;
    }

    /**
     * 存在修改或者不存在新增
     * @param  int $id id
     * @return bool
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */
    private function _updateandcreate($data, $lang, $newbn, $uid) {
        if (isset($data[$lang]['name'])) {
            $where['lang'] = $lang;
            $where['bn'] = $data['bn'];
            $arr['bn'] = $newbn;
            $arr['lang'] = $lang;
            $arr['name'] = $data[$lang]['name'];
            $arr['country_bn'] = $data['country_bn'];
            $arr['trans_mode'] = $data['trans_mode'];
            $arr['port_type'] = $data['port_type'];
            $arr['remarks'] = $data['remarks'];
            $arr['deleted_flag'] = 'N';
            if ($this->Exits($where)) {
                $flag = $this->where($where)->save($arr);
                return $flag;
            } else {
                $data = $arr;
                $data['status'] = 'VALID';
                $data['created_by'] = defined('UID') ? UID : 0;
                $data['created_at'] = date('Y-m-d H:i:s');
                $flag = $this->add($data);
                return $flag;
            }
        } else {
            return true;
        }
    }

    public function Exits($where) {

        return $this->where($where)->find();
    }

    /**
     * 新增数据
     * @param  mix $create 新增条件
     * @return bool
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc   口岸
     */
    public function create_data($create = [], $uid = 0) {
        if (isset($create['en']['name']) && isset($create['zh']['name'])) {
            $datalist = [];
            $arr['bn'] = ucwords($create['en']['name']);
            $create['en']['name'] = ucwords($create['en']['name']);
            $arr['country_bn'] = $create['country_bn'];
            $arr['trans_mode'] = $create['trans_mode'];
            $arr['port_type'] = $create['port_type'];
            $arr['remarks'] = $create['remarks'];
            $arr['created_by'] = defined('UID') ? UID : 0;
            $arr['status'] = 'VALID';
            $arr['created_at'] = date('Y-m-d H:i:s');
            $langs = ['en', 'zh', 'es', 'ru'];
            foreach ($langs as $lang) {
                if (isset($create[$lang]['name'])) {
                    $arr['lang'] = $lang;
                    $arr['name'] = $create[$lang]['name'];
                    $datalist[] = $arr;
                }
            }
            return $this->addAll($datalist);
        } else {
            return false;
        }
    }
    public function portList($data){
        $page=isset($data['current_page'])?$data['current_page']:1;
        $offsize=($page-1)*10;
        $field='port.id,port.country_bn,port.bn as port_bn,port.name port_name_zh,port.name_en as port_name_en,port.port_type,port.trans_mode,';
        $field.=" (select DISTINCT name from erui_dict.country country where bn=port.country_bn and country.lang='zh' and country.deleted_flag='N') as country_name";
        $count=$this->alias('port')
            ->where(array('port.lang'=>'zh','port.deleted_flag'=>'N'))->count();
        $info=$this->alias('port')
            ->field($field)
            ->where(array('port.lang'=>'zh','port.deleted_flag'=>'N'))
            ->order('port.id desc')
            ->limit($offsize,10)
            ->select();
        if(empty($info)){
            $info=[];
        }
        $arr['current_page']=$page;
        $arr['total_count']=$count;
        $arr['info']=$info;
        return $arr;
    }
    public function portTest(){
        $info=$this->field('bn,name')->where(array('lang'=>'en'))->select();
        foreach($info as $k => $v){
            $this->where(array('lang'=>'zh','bn'=>$v['bn']))->save(array('name_en'=>$v['name']));
        }
        print_r(1);die;
    }
}
