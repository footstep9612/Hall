<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/12/8
 * Time: 10:18
 */
class CustomCatItemModel extends PublicModel
{
    protected $tableName = 'custom_cat_item';
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

    /**
     * 获取详情
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function info($lang,$cat_id, $item_id) {
        if(isset($cat_id) && !empty($cat_id)) {
            $where["custom_cat_item.cat_id"] = $cat_id;
        }
        if(isset($item_id) && !empty($item_id)) {
            $where["custom_cat_item.id"] = $item_id;
        }
        if(isset($lang) && !empty($lang)) {
            $where["custom_cat_item.lang"] = $lang;
        }
        $where = [
            "custom_cat_item.deleted_flag" =>  'N',
        ];
        if ($where) {
            $customitemInfo = $this->where($where)
                                   ->field('custom_cat_item.*,em.name as created_name')
                                   ->join('erui_sys.employee em on em.id=custom_cat_item.created_by', 'left')
                                   ->group('custom_cat_item.item_name')
                                   ->order('custom_cat_item.id asc')
                                   ->select();
            $data = array();
            if($customitemInfo) {
                $j = 0;
                for($i=0; $i<=count($customitemInfo)-1;) {
                    $data[$j][0] = $customitemInfo[$i];
                    //$data[$j][1] = $customitemInfo[$i+1];
                    if($customitemInfo[$i+1]) {
                        $data[$j][1] = $customitemInfo[$i+1];
                    }
                    $j++;
                    $i +=2;
                }
                return $data;
            } else{
                return  false;
            }
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
        if (isset($create['cat_id']) && !empty($create['cat_id'])) {
            $arr['cat_id'] = trim($create['cat_id']);
        } else{
            jsonReturn(null ,-202, 'cat_id不能为空!');
        }
        if (isset($create['sort_order'])) {
            $arr['sort_order'] = trim($create['sort_order']);
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
        if (isset($data['cat_id'])) {
            $arr['cat_id'] = trim($data['cat_id']);
        }
        if (isset($data['sort_order'])) {
            $arr['sort_order'] = trim($data['sort_order']);
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
        return $this->where($where)->save(['deleted_flag'=> 'Y']);
    }
}