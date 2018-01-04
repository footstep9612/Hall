<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/1/4
 * Time: 14:56
 */
class BuyerContactModel extends PublicModel
{

    protected $tableName = 'buyer_contact';
    protected $dbName = 'erui_buyer'; //数据库名称

    public function __construct($str = '')
    {

        parent::__construct();
    }

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；
    const STATUS_DRAFT = 'DRAFT'; //草稿；
    const DELETE_Y = 'Y';
    const DELETE_N = 'N';

    /**
     * 获取详情
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function info($data) {
        if (!empty($data['buyer_id'])) {
            $row = $this->where(['buyer_id' => $data['buyer_id'], 'deleted_flag' => 'N'])
                        ->order('id desc')
                        ->limit(1)
                        ->select();

        } elseif (!empty($data['id'])) {
            $row = $this->where(['id' => $data['id'], 'deleted_flag' => 'N'])
                        ->order('id desc')
                        ->limit(1)
                        ->select();
        }
        $data = [];
        if(!$row){
            $buyaccont_model = new BuyerAccountModel();
            $account_info = $buyaccont_model->getinfo($data);
            if($account_info){
                $data['name'] = $account_info['show_name'] ? $account_info['show_name'] : $account_info['user_name'];
                $data['phone'] = $account_info['official_phone'];
                $data['email'] = $account_info['official_email'];
                $data['country_bn'] = $account_info['country_bn'];
                $data['city'] = $account_info['city'];
                $data['address'] = $account_info['address'];
                $data['zipcode'] = '';
                return $data;
            }
            return false;
        }
        return false;
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
        if (isset($create['first_name']) && !empty($create['first_name'])) {
            $arr['first_name'] = trim($create['first_name']);
        }
        if (isset($create['last_name']) && !empty($create['last_name'])) {
            $arr['last_name'] = trim($create['last_name']);
        }
        if (isset($create['name']) && !empty($create['name'])) {
            $arr['name'] = trim($create['name']);
        }
        if (isset($create['gender']) && !empty($create['gender'])) {
            $arr['gender'] = trim($create['gender']);
        }
        if (isset($create['title']) && !empty($create['title'])) {
            $arr['title'] = trim($create['title']);
        }
        if (isset($create['phone']) && !empty($create['phone'])) {
            $arr['phone'] = trim($create['phone']);
        }
        if (isset($create['fax']) && !empty($create['fax'])) {
            $arr['fax'] = trim($create['fax']);
        }
        if (isset($create['email']) && !empty($create['email'])) {
            $arr['email'] = trim($create['email']);
        }
        if (isset($create['area_bn']) && !empty($create['area_bn'])) {
            $arr['area_bn'] = trim($create['area_bn']);
        }
        if (isset($create['country_code']) && !empty($create['country_code'])) {
            $arr['country_code'] = trim($create['country_code']);
        }
        if (isset($create['email']) && !empty($create['email'])) {
            $arr['email'] = trim($create['email']);
        }
        if (isset($create['country_bn']) && !empty($create['v'])) {
            $arr['country_bn'] = trim($create['country_bn']);
        }
        if (isset($create['province']) && !empty($create['province'])) {
            $arr['province'] = trim($create['province']);
        }
        if (isset($create['city']) && !empty($create['city'])) {
            $arr['city'] = trim($create['city']);
        }
        if (isset($create['address']) && !empty($create['address'])) {
            $arr['address'] = trim($create['address']);
        }
        if (isset($create['zipcode']) && !empty($create['zipcode'])) {
            $arr['zipcode'] = trim($create['zipcode']);
        }
        if (isset($create['hobby']) && !empty($create['hobby'])) {
            $arr['hobby'] = trim($create['hobby']);
        }
        if (isset($create['experience']) && !empty($create['experience'])) {
            $arr['experience'] = trim($create['experience']);
        }
        if (isset($create['role']) && !empty($create['role'])) {
            $arr['role'] = trim($create['role']);
        }
        if (isset($create['social_relations']) && !empty($create['social_relations'])) {
            $arr['social_relations'] = trim($create['social_relations']);
        }
        if (isset($create['remarks']) && !empty($create['remarks'])) {
            $arr['remarks'] = trim($create['remarks']);
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
        $data = $this->create($data);
        if (isset($where['buyer_id']) && !empty($where['buyer_id'])) {
            $arr['buyer_id'] = trim($where['buyer_id']);
        } else {
            jsonReturn(null ,-201, '用户ID不能为空!');
        }
        if (isset($data['first_name']) && !empty($data['first_name'])) {
            $arr['first_name'] = trim($data['first_name']);
        }
        if (isset($data['last_name']) && !empty($data['last_name'])) {
            $arr['last_name'] = trim($data['last_name']);
        }
        if (isset($data['name']) && !empty($data['name'])) {
            $arr['name'] = trim($data['name']);
        }
        if (isset($data['gender']) && !empty($data['gender'])) {
            $arr['gender'] = trim($data['gender']);
        }
        if (isset($data['title']) && !empty($data['title'])) {
            $arr['title'] = trim($data['title']);
        }
        if (isset($data['phone']) && !empty($data['phone'])) {
            $arr['phone'] = trim($data['phone']);
        }
        if (isset($data['fax']) && !empty($data['fax'])) {
            $arr['fax'] = trim($data['fax']);
        }
        if (isset($data['email']) && !empty($data['email'])) {
            $arr['email'] = trim($data['email']);
        }
        if (isset($data['area_bn']) && !empty($data['area_bn'])) {
            $arr['area_bn'] = trim($data['area_bn']);
        }
        if (isset($data['country_code']) && !empty($data['country_code'])) {
            $arr['country_code'] = trim($data['country_code']);
        }
        if (isset($data['email']) && !empty($data['email'])) {
            $arr['email'] = trim($data['email']);
        }
        if (isset($data['country_bn']) && !empty($data['v'])) {
            $arr['country_bn'] = trim($data['country_bn']);
        }
        if (isset($data['province']) && !empty($data['province'])) {
            $arr['province'] = trim($data['province']);
        }
        if (isset($data['city']) && !empty($data['city'])) {
            $arr['city'] = trim($data['city']);
        }
        if (isset($data['address']) && !empty($data['address'])) {
            $arr['address'] = trim($data['address']);
        }
        if (isset($data['zipcode']) && !empty($data['zipcode'])) {
            $arr['zipcode'] = trim($data['zipcode']);
        }
        if (isset($data['hobby']) && !empty($data['hobby'])) {
            $arr['hobby'] = trim($data['hobby']);
        }
        if (isset($data['experience']) && !empty($data['experience'])) {
            $arr['experience'] = trim($data['experience']);
        }
        if (isset($data['role']) && !empty($data['role'])) {
            $arr['role'] = trim($data['role']);
        }
        if (isset($data['social_relations']) && !empty($data['social_relations'])) {
            $arr['social_relations'] = trim($data['social_relations']);
        }
        if (isset($data['remarks']) && !empty($data['remarks'])) {
            $arr['remarks'] = trim($data['remarks']);
        }
        $arr['updated_at'] = Date("Y-m-d H:i:s");
        if (!empty($where['buyer_id'])) {
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