<?php

/**
 * 询单提醒记录模型
 * @desc   InquiryRemindModel
 * @Author 买买提
 */
class InquiryRemindModel extends PublicModel
{

    protected $dbName = 'erui_rfq';
    protected $tableName = 'inquiry_remind';

    public function __construct() {
        parent::__construct();
    }

}

