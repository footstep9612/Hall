<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/12/6
 * Time: 16:28
 */
class BuyerLogisticsModel extends PublicModel
{

    protected $tableName = 'buyer_logistics';
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
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author klp
     */


    /**
     * 获取详情
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function info($buyer_id) {
        $where = [
            "buyer_logistics.buyer_id" => $buyer_id,
            "buyer_logistics.deleted_flag" => 'N',
        ];
        if ($where) {
            $LogisticsInfo = $this->where($where)
                                  ->field('buyer_logistics.*,em.name as created_name')
                                  ->join('erui_sys.employee em on em.id=buyer_logistics.created_by', 'left')
                                  ->select();

            return $LogisticsInfo ? $LogisticsInfo : false;
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
        if (isset($create['trade_terms_bn'])) {
            $arr['trade_terms_bn'] = trim($create['trade_terms_bn']);
        }
        if (isset($create['country_bn'])) {
            $arr['country_bn'] = trim($create['country_bn']);
        }
        if (isset($create['city'])) {
            $arr['city'] = trim($create['city']);
        }
        if (isset($create['to_country_bn'])) {
            $arr['to_country_bn'] = trim($create['to_country_bn']);
        }
        if (isset($create['to_port_bn'])) {
            $arr['to_port_bn'] = trim($create['to_port_bn']);
        }
        if (isset($create['currency_bn'])) {
            $arr['currency_bn'] = trim($create['currency_bn']);
        }
        if (isset($create['payment_mode'])) {
            $arr['payment_mode'] = trim($create['payment_mode']);
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
        if (isset($data['trade_terms_bn'])) {
            $arr['trade_terms_bn'] = trim($data['trade_terms_bn']);
        }
        if (isset($data['country_bn'])) {
            $arr['country_bn'] = trim($data['country_bn']);
        }
        if (isset($create['city'])) {
            $arr['city'] = trim($create['city']);
        }
        if (isset($data['to_country_bn'])) {
            $arr['to_country_bn'] = trim($data['to_country_bn']);
        }
        if (isset($data['to_port_bn'])) {
            $arr['to_port_bn'] = trim($data['to_port_bn']);
        }
        if (isset($data['currency_bn'])) {
            $arr['currency_bn'] = trim($data['currency_bn']);
        }
        if (isset($data['payment_mode'])) {
            $arr['payment_mode'] = trim($data['payment_mode']);
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
        if (isset($data['updated_by'])) {
            $arr['updated_by'] = trim($data['updated_by']);
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