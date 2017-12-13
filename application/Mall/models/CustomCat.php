<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/12/8
 * Time: 10:17
 */
class CustomCatModel extends PublicModel
{
    protected $tableName = 'custom_cat';
    protected $dbName = 'erui_mall'; //数据库名称

    public function __construct()
    {
        parent::__construct();
    }

    //状态
    const STATUS_DRAFT= 'DRAFT'; //草稿
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETED = 'DELETED'; //删除；
    const DELETE_Y = 'Y';
    const DELETE_N = 'N';

    /**
     * 获取详情
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function info($lang, $cat_id) {
        if(isset($cat_id) && !empty($cat_id)) {
            $where["id"] = $cat_id;
        }
        if(isset($lang) && !empty($lang)) {
            $where["lang"] = $lang;
        }
        $where["deleted_flag"] =  'N';
        if ($where) {
            $customcatInfo = $this->where($where)
                                  ->order('id asc')
                                  ->select();

            return $customcatInfo ? $customcatInfo : false;
        } else {
            return false;
        }
    }

    /**
     * 新增
     */
    public function create_data($create, $where) {
        if (!isset($where['buyer_id']) || empty($where['buyer_id'])) {
            jsonReturn(null ,-201, '用户ID不能为空!');
        }
        if (isset($create['lang'])) {
            $arr['lang'] = trim($create['lang']);
        }
        if (isset($create['cat_no'])) {
            $arr['cat_no'] = trim($create['cat_no']);
        }
        if (isset($create['cat_name'])) {
            $arr['cat_name'] = trim($create['cat_name']);
        }
        if (isset($create['sort_order'])) {
            $arr['sort_order'] = trim($create['sort_order']);
        }

        $arr['created_at'] = Date("Y-m-d H:i:s");
        try {
            $arr['created_by'] = $where['buyer_id'];
            $data = $this->create($arr);
            $res = $this->add($data);
            return $res;
        } catch (Exception $e) {
            print_r($e);
            LOG::write('CLASS' . __CLASS__ . PHP_EOL . ' LINE:' . __LINE__, LOG::EMERG);
            LOG::write($e->getMessage(), LOG::ERR);
            return false;
        }
    }

    /**
     * 更新
     */
    public function update_data($data, $where) {
        if (isset($data['lang'])) {
            $arr['lang'] = strtolower(trim($data['lang']));
        }
        if (isset($data['cat_name'])) {
            $arr['cat_name'] = trim($data['cat_name']);
        }
        if (isset($data['sort_order'])) {
            $arr['sort_order'] = trim($data['sort_order']);
        }
        if ($data['status']) {
            switch (strtoupper($data['status'])) {
                case self::STATUS_VALID:
                    $arr['status'] = $data['status'];
                    break;
                case self::STATUS_DRAFT:
                    $arr['status'] = $data['status'];
                    break;
                case self::STATUS_DELETED:
                    $arr['status'] = $data['status'];
                    break;
            }
        }
        if (isset($data['updated_by'])) {
            $arr['updated_by'] = trim($data['updated_by']);
        }
        $arr['updated_at'] = Date("Y-m-d H:i:s");
        if (!empty($where)) {
            $res = $this->where($where)->save($arr);
        } else {
            return false;
        }
        if ($res !== false) {
            return true;
        }
        return false;
    }

    /**
    删除
     */
    public function delete_data($where) {
        return $this->where($where)->save(['deleted_flag'=> self::DELETE_Y]);
    }
}