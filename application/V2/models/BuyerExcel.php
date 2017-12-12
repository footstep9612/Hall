<?php
/**
    下载excel位置
 */
class BuyerExcelModel extends PublicModel
{
    protected $dbName = 'erui_buyer';
    protected $tableName = 'buyer_excel';
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @excel $name
     * @excel $url
     * @操作人 $created_by
     */
    public function saveExcel($name,$url,$created_by){
        $arr['excel_name'] = $name;
        $arr['excel_url'] = $url;
        $arr['excel_time'] = date('Y-m-d H:i:s');
        $arr['created_by'] = $created_by;
        $res = $this->add($arr);
        return $res;
    }
}
