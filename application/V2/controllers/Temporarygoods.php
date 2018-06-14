<?php

class TemporaryGoodsController extends PublicController
{

    public function init()
    {
        parent::init();
    }

    public function listAction()
    {
        
    }

    public function syncAction()
    {
        $data = [];

        $where = [
            'i.quote_status' => 'QUOTED',
            'i.deleted_flag' => 'N',
            'it.deleted_flag' => 'N',
        ];

        $field = '';

        $inquiry = new InquiryModel();

        $data = $inquiry->alias('i')
                        ->join('erui_rfq.inquiry_item it ON i.id=it.inquiry_id')
                        ->where($where)
                        ->select();
        p(count($data));
    }
}