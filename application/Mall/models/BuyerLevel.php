<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/8/31
 * Time: 9:25
 */
class BuyerLevelModel extends PublicModel
{

    protected $dbName = 'erui_config';
    protected $tableName = 'buyer_level';

    const STATUS_VALID = 'VALID';
    const DELETE_Y = 'Y';
    const DELETE_N = 'N';

    public function __construct(){
        parent::__construct();
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     */
    public function getlist($order=" id desc") {
        $where = [
            'status'=> self::STATUS_VALID,
            'deleted_flag'=> self::DELETE_N
        ];
        $result = $this->where($where)->order($order)->select();
        $buyerLevel = $list = [];
        if($result) {
            foreach ($result as $item) {
                $item['buyer_level'] = json_decode($item['buyer_level'], true);
                foreach($item['buyer_level'] as $value) {
                    $buyerLevel['buyer_level'][$value['lang']] = $value;
                }
                $list[] = $buyerLevel['buyer_level'];
            }
        }
        return $list;
    }
}