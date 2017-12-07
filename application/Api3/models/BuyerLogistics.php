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
    protected $dbName = 'erui_buyer'; //数据库名称

    public function __construct()
    {
        parent::__construct();
    }


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
    public function info($buyer_id, $lang='en') {

        $where = [
            "buyer_logistics.created_by" => $buyer_id,
            "buyer_logistics.lang"       => $lang,
        ];
        if ($where) {
            $LogisticsInfo = $this->where($where)
                                  ->field('buyer_logistics.*,em.name as created_name')
                                  ->join('erui_sys.employee em on em.id=buyer.created_by', 'left')
                                  ->select();


            return $LogisticsInfo ? $LogisticsInfo : false;
        } else {
            return false;
        }
    }


}