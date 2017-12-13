<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2017/12/6
 * Time: 16:28
 */
class BuyerServiceModel extends PublicModel
{

    protected $tableName = 'buyer_service';
    protected $dbName = 'erui_buyer'; //数据库名称

    public function __construct()
    {
        parent::__construct();
    }

    //类型
    const STATUS_COMPLEX = 'COMPLEX';   //技术咨询及综合方案解决；
    const STATUS_TRAINING = 'TRAINING'; //人才培训；
    const STATUS_DEMAND = 'DEMAND';     //人才需求；


    /**
     * 获取详情
     * @param mix $condition
     * @return mix
     * @author klp
     */
    public function info($buyer_id, $type = self::STATUS_COMPLEX, $lang='en') {

        $where = [
            "buyer_service.created_by" => $buyer_id,
            "buyer_service.custom_type" => $type,
            "buyer_service.lang"       => $lang,
        ];
        if ($where) {
            $customInfo = $this->where($where)->field('buyer.*,em.name as created_name,')
                ->join('erui_sys.employee em on em.id=buyer.created_by', 'left')
                ->find();
            $sql = "SELECT  `id`,  `service_id`,  `attach_type`,  `attach_name`,  `default_flag`,  `attach_url`,  `status`,  `created_by`,  `created_at` FROM  `erui_buyer`.`service_attach` where deleted_flag ='N' and service_id = " . $customInfo['id'];
            $row = $this->query($sql);
            if ($row) {
                $customInfo['attach'] = $row[0];
            } else {
                $customInfo['attach'] = [];
            }
            return $customInfo;
        } else {
            return false;
        }
    }




}