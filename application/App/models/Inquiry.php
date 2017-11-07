<?php

class InquiryModel extends PublicModel
{

    protected $dbName = 'erui_rfq';
    protected $tableName = 'inquiry';

    public function __construct() {
        parent::__construct();
    }

    public function getStatisticsByType($type)
    {
        switch ($type)
        {
            case 'TODAY' :
                $where = "DATE_FORMAT(created_at,'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')";
                $data = $this->where($where)->count('id');
                break;
            case 'TOTAL' :
                $data = $this->count('id');
                break;
            case 'QUOTED' :
                $data = $this->where(['quote_status'=>'QUOTED'])->count('id');
                break;
        }
        return $data;
    }


    public function getNewItems($uid, $limit=3)
    {
        $where = [];
        return $this->alias('a')
                    ->join("erui_sys.employee e ON a.now_agent_id=e.id","LEFT")
                    ->where($where)
                    ->field('a.id,a.buyer_name,a.created_at,a.serial_no,a.quote_status,e.name')
                    ->limit($limit)
                    ->order('a.created_at DESC')
                    ->select();
    }
}
