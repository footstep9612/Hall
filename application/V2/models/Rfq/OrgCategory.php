<?php

/**
 * name: org_category
 * desc: 事业部与分类映射关系
 * User: 张玉良
 * Date: 2018/8/9
 * Time: 14:11
 */
class Rfq_OrgCategoryModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'org_category'; //事业部与分类映射关系

    public function __construct() {
        parent::__construct();
    }

    /**
     * 条件解析
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    private function _getcondition($condition) {

        $where = ['deleted_flag' => 'N'];

        if (!empty($condition['org_id']) && is_numeric($condition['org_id'])) {
            $where['org_id'] = trim($condition['org_id']);
        } elseif (!empty($condition['org_id']) && is_array($condition['org_id'])) {
            $org_ids = $this->SetTrimData($condition['org_id']);
            $where['org_id'] = ['in', $org_ids];
        } else {
            $where['org_id'] = 0;
        }

        if (!empty($condition['oil_flag'])) {
            $where['oil_flag'] = $condition['oil_flag'] == 'Y' ? 'Y' : 'N';
        }
        if (!empty($condition['name'])) {
            $name = trim($condition['name']);
            $where['name'] = ['like', '%' . $name . '%'];
        }

        return $where;
    }

    /**
     * 获取列表
     * @param mix $condition 搜索条件
     * @param string $lang 语言
     * @return mix
     * @author zyg
     */
    public function getlist($condition) {
        $where = $this->_getcondition($condition);
        $redis_key = md5($where);
        if (redisHashExist('org_category', $redis_key)) {
            return json_decode(redisHashGet('org_category', $redis_key), true);
        }
        try {
            $list = $this->where($where)
                    ->field('id,org_id,name')
                    ->order('id desc')
                    ->select();
            redisHashSet('org_category', $redis_key, json_encode($list));
            return $list;
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
    public function info($name = '') {
        if ($name) {
            $where['name'] = $name;
        } else {
            return [];
        }
        $redis_key = $name;
        if (redisHashExist('org_category', $redis_key)) {
            return json_decode(redisHashGet('Brand', $redis_key), true);
        }
        $item = $this->where($where)
                ->find();
        redisHashSet('org_category', $redis_key, json_encode($item));
        return $item;
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
        } elseif (is_numeric($id)) {
            $where['id'] = $id;
        } elseif (is_array($id)) {
            $where['id'] = ['in', $this->SetTrimData($id)];
        }
        $flag = $this->where($where)
                ->save(['deleted_flag' => 'Y']);
        if ($flag === false) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 更新数据
     * @param  mix $upcondition 更新条件
     * @return mix
     * @author zyg
     */
    public function update_data($upcondition = []) {
        if (!$upcondition['id']) {
            return false;
        } else {
            $where['id'] = $upcondition['id'];
        }
        $data = $this->create($upcondition);
        try {
            $flag = $this->where($where)->save($data);
            return $flag;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);

            return false;
        }
    }

    /**
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function create_data($createcondition = []) {

        $data = $this->create($createcondition);
        unset($data['id']);
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
     * 新增数据
     * @param  mix $createcondition 新增条件
     * @return bool
     * @author zyg
     */
    public function batchcreate_data($createcondition = []) {

        if (empty($createcondition['org_id'])) {
            $org_id = 0;
        } else {
            $org_id = intval($createcondition['org_id']);
        }
        if (empty($createcondition['oil_flag'])) {
            $oil_flag = 'N';
        } else {
            $oil_flag = $createcondition['oil_flag'] == 'Y' ? 'Y' : 'N';
        }


        try {
            $add_datas = [];
            foreach ($createcondition['name'] as $k => $name) {
                $data = [];
                $data['org_id'] = $org_id;
                $data['name'] = trim($name);
                $data['oil_flag'] = $oil_flag;
                $add_datas[$k] = $this->create($createcondition);
            }
            $flag = $this->addAll($add_datas);
            if (!$flag) {
                return false;
            }
            return $flag;
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), Log::ERR);
            return false;
        }
    }

}
