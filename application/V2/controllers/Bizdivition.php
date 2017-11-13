<?php

/**
 * 事业部分单员相关接口
 * @desc   BizdivitionController
 * @Author 买买提
 */
class BizdivitionController extends PublicController{

    //请求参数
    private $requestParams = [];

    private $inquiryModel;

    public function init(){

        parent::init();

        $this->inquiryModel  = new InquiryModel();
        $this->requestParams = json_decode(file_get_contents("php://input"), true);

    }


    /**
     * 退回市场
     */
    public function rejectToMarketAction(){

        $request = $this->validateRequests('inquiry_id');

        $inquiry = new InquiryModel();
        $now_agent_id = $inquiry->where(['id'=>$request['inquiry_id']])->getField('agent_id');
        $response = $inquiry->where(['id'=>$request['inquiry_id']])->save([
            'status'       => 'DRAFT',
            'now_agent_id' => $now_agent_id,
            'quote_id' => '',
            'updated_by'   => $this->user['id'],
            'updated_at'   => date('Y-m-d H:i:s')
        ]);

        $this->jsonReturn($response);

    }

    /**
     * 退回易瑞
     */
    public function rejectToEruiAction(){

        $request = $this->validateRequests('inquiry_id');

        $inquiry = new InquiryModel();
        $orgModel = new OrgModel();
        $roleModel = new RoleModel();
        $roleUserModel = new RoleUserModel();
        $erui_id = $orgModel->where(['org_node'=>'erui'])->getField('id');
        $role_id = $roleModel->where(['role_no'=>$inquiry::inquiryIssueRole])->getField('id');
        $roleUser = $roleUserModel->where(['role_id'=>$role_id])->getField('employee_id');

        $response = $inquiry->where(['id'=>$request['inquiry_id']])->save([
            'status'       => 'CC_DISPATCHING', //易瑞客户中心
            'erui_id'      => $erui_id,
            'now_agent_id' => $roleUser,
            'updated_by'   => $this->user['id'],
            'updated_at'   => date('Y-m-d H:i:s')
        ]);

        $this->jsonReturn($response);

    }

    /**
     * 分配报价人
     */
    public function assignQuoterAction(){

        $request = $this->validateRequests('inquiry_id,quote_id,serial_no');

        $inquiry = new InquiryModel();

        $response = $inquiry->where(['id'=>$request['inquiry_id']])->save([
            'status'       => 'BIZ_QUOTING', //事业部报价
            'quote_status' => 'ONGOING', //报价中
            'quote_id'     => $request['quote_id'],
            'now_agent_id' => $request['quote_id'],
            'updated_by'   => $this->user['id'],
            'updated_at'   => date('Y-m-d H:i:s')
        ]);

        $quoteModel = new QuoteModel();

        $flag = $quoteModel->where(['inquiry_id'=>$request['inquiry_id']])->find();

        if (!$flag){
            $inquiryInfo = $inquiry->where(['id'=>$request['inquiry_id']])->find();
            $quote_id = $quoteModel->add($quoteModel->create([
                'inquiry_id'     => $request['inquiry_id'],
                'serial_no'      => $request['serial_no'],
                'quote_no'       => $this->getQuoteNo(),
                'created_by'     => $this->user['id'],
                'created_at'     => date('Y-m-d H:i:s'),
                'status'         => 'BIZ_QUOTING',
                'trade_terms_bn' => $inquiryInfo['trade_terms_bn'],
                'payment_mode'   => $inquiryInfo['payment_mode'],
                'trans_mode_bn'  => $inquiryInfo['trans_mode_bn'],
                'quote_cur_bn'   => $inquiryInfo['cur_bn'],
                'from_country'   => $inquiryInfo['from_country'],
                'from_port'      => $inquiryInfo['from_port'],
                'dispatch_place' => $inquiryInfo['dispatch_place'],
                'to_country'     => $inquiryInfo['to_country'],
                'to_port'        => $inquiryInfo['to_port'],
                'delivery_addr'  => $inquiryInfo['delivery_addr'],
                'payment_period' => $inquiryInfo['payment_period'],
                'delivery_addr'  => $inquiryInfo['destination']
            ]));

            $inquiryItemModel = new InquiryItemModel();
            $inquiryItems = $inquiryItemModel->where(['inquiry_id'=>$request['inquiry_id']])->field('id,sku,qty,unit')->select();

            $quoteItemModel = new QuoteItemModel();
            foreach ($inquiryItems as $item=>$value){
                $quoteItemModel->add($quoteItemModel->create([
                    'quote_id'        => $quote_id,
                    'inquiry_id'      => $request['inquiry_id'],
                    'inquiry_item_id' => $value['id'],
                    'sku'             => $value['sku'],
                    'quote_qty'       => $value['qty'],
                    'quote_unit'      => $value['unit'],
                    'created_by'      => $this->user['id'],
                    'created_at'      => date('Y-m-d H:i:s')
                ]));
            }
        }else{
            $quoteModel->where(['inquiry_id'=>$request['inquiry_id']])->save(['status'=>'BIZ_QUOTING']);
        }

        $this->jsonReturn($response);

    }

    /**
     * 验证指定参数是否存在
     * @param string $params 初始的请求字段
     * @return array 验证后的请求字段
     */
    private function validateRequests($params=''){

        $request = $this->requestParams;
        unset($request['token']);

        //判断筛选字段为空的情况
        if ($params){
            $params = explode(',',$params);
            foreach ($params as $param){
                if (empty($request[$param])) $this->jsonReturn(['code'=>'-104','message'=>'缺少参数']);
            }
        }

        return $request;

    }

    /**
     * 发送短信测试
     *
     * 参数说明
     * 收信手机号,操作类型,收信人名称,流程编吗,发送人名称,当前环节,流转环节
     * 更多说明请看Public控制sendSms()方法
     *
     */
    public function smsAction()
    {

        $this->sendSms("17326916890","SUBMIT","买买提","INQ_20171026_00001",$this->user['name'],"DRAFT","BIZ_DISPATCH");

    }
}
