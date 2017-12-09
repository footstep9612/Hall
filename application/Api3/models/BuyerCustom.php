<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/12/6
 * Time: 16:28
 */
class BuyerServiceModel extends PublicModel
{

    protected $tableName = 'buyer_custom';
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
    public function info($buyer_id) {

        $where = [
            "buyer_id"  => $buyer_id,
            "deleted_flag" => 'N',
        ];
        if ($where) {
            $customInfo = $this->where($where)->field('buyer.*,em.name as created_name')->select();
//            $sql = "SELECT  `id`,  `service_id`,  `attach_type`,  `attach_name`,  `default_flag`,  `attach_url`,  `status`,  `created_by`,  `created_at` FROM  `erui_buyer`.`service_attach` where deleted_flag ='N' and service_id = " . $customInfo['id'];
//            $row = $this->query($sql);     ==>>扩展加附件使用(后期加)

            return $customInfo;
        } else {
            return false;
        }
    }

    /**
     * 新增
     */
    public function create_data($create, $where) {
        if (isset($where['buyer_id']) && !empty($where['buyer_id'])) {
            $arr['buyer_id'] = trim($where['buyer_id']);
        } else {
            jsonReturn(null ,-201, '用户ID不能为空!');
        }
        if (isset($create['service_no'])) {
            $arr['service_no'] = trim($create['service_no']);
        }
        if (isset($create['title'])) {
            $arr['title'] = $create['title'];
        }
        if (isset($create['cat_id'])) {
            $arr['cat_id'] = json_encode(trim($create['cat_id']));
        }
        if (isset($create['term_id'])) {
            $arr['term_id'] = json_encode(trim($create['term_id']));
        }
        if (isset($create['content'])) {
            $arr['content'] = trim($create['content']);
        }
        if (isset($create['remarks'])) {
            $arr['remarks'] = trim($create['remarks']);
        }
        if (isset($create['add_desc'])) {
            $arr['add_desc'] = trim($create['add_desc']);
        }
        if (isset($create['email'])) {
            $arr['email'] = trim($create['email']);
        }
        if (isset($create['contact_name'])) {
            $arr['contact_name'] = trim($create['contact_name']);
        }
        if (isset($create['company'])) {
            $arr['company'] = trim($create['company']);
        }
        if (isset($create['country_bn'])) {
            $arr['country_bn'] = trim($create['country_bn']);
        }
        if (isset($create['tel'])) {
            $arr['tel'] = trim($create['tel']);
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
        $arr = [];
        if (isset($data['service_no'])) {
            $arr['service_no'] = trim($data['service_no']);
        }
        if (isset($data['title'])) {
            $arr['title'] = $data['title'];
        }
        if (isset($data['cat_id'])) {
            $arr['cat_id'] = json_encode(trim($data['cat_id']));
        }
        if (isset($data['term_id'])) {
            $arr['term_id'] = trim($data['term_id']);
        }
        if (isset($data['content'])) {
            $arr['content'] = trim($data['content']);
        }
        if (isset($data['remarks'])) {
            $arr['remarks'] = trim($data['remarks']);
        }
        if (isset($data['add_desc'])) {
            $arr['add_desc'] = trim($data['add_desc']);
        }
        if (isset($data['email'])) {
            $arr['email'] = trim($data['email']);
        }
        if (isset($data['contact_name'])) {
            $arr['contact_name'] = trim($data['contact_name']);
        }
        if (isset($data['company'])) {
            $arr['company'] = trim($data['company']);
        }
        if (isset($data['country_bn'])) {
            $arr['country_bn'] = trim($data['country_bn']);
        }
        if (isset($data['tel'])) {
            $arr['tel'] = trim($data['tel']);
        }
        if ($data['status']) {
            switch (strtoupper($data['status'])) {
                case self::STATUS_VALID:
                    $arr['status'] = $data['status'];
                    break;
                case self::STATUS_INVALID:
                    $arr['status'] = $data['status'];
                    break;
                case self::STATUS_DELETED:
                    $arr['status'] = $data['status'];
                    break;
            }
        }
        if (isset($create['updated_by'])) {
            $arr['updated_by'] = trim($create['updated_by']);
        }
        $arr['updated_at'] = Date("Y-m-d H:i:s");
        if (!empty($where['buyer_id'])) {
            $data = $this->create($arr);
            $res = $this->where($where)->save($data);
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