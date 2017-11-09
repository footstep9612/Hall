<?php

class SmsLogModel extends PublicModel
{
    protected $dbName = 'erui_rfq';
    protected $tableName = 'sms_log';

    public function __construct() {
        parent::__construct();
    }
}