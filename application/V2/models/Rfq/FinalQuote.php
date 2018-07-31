<?php

/**
 * name: FinalQuote.php
 * desc: 市场报价单控制器
 * User: 张玉良
 * Date: 2017/8/4
 * Time: 10:45
 */
class Rfq_FinalQuoteModel extends PublicModel {

    protected $dbName = 'erui_rfq';
    protected $tableName = 'final_quote';

    public function __construct() {
        parent::__construct();
    }

}
