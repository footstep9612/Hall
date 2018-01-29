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

        //发送短信通知
        $employee = new EmployeeModel();
        $this->sendSms($employee->getMobileByUserId($now_agent_id),"REJECT",$employee->getUserNameById($now_agent_id),$inquiry->getSerialNoById($request['inquiry_id']),$this->user['name'],"BIZ_DISPATCHING","REJECT_MARKET");

        $response = $inquiry->updateData([
            'id'=>$request['inquiry_id'],
            'status'       => 'REJECT_MARKET',//改为驳回市场，我了让查看询单的饿呢看到
            'now_agent_id' => $now_agent_id,
            'inflow_time'  => date('Y-m-d H:i:s',time()),
            'quote_id' => NULL,
            'updated_by'   => $this->user['id'],
            'updated_at'   =>date('Y-m-d H:i:s',time())
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
        $erui_id = $orgModel->where(['org_node'=>'erui'])->getField('id');
        $userId = $inquiry->getInquiryIssueUserId($request['inquiry_id'], [$erui_id], $inquiry::inquiryIssueAuxiliaryRole, $inquiry::inquiryIssueRole, 'erui');

        $response = $inquiry->updateData([
            'id'=>$request['inquiry_id'],
            'status'       => 'CC_DISPATCHING', //易瑞客户中心
            'erui_id'      => $erui_id,
            'org_id'      => $erui_id,
            'now_agent_id' => $userId,
            'inflow_time'  => date('Y-m-d H:i:s',time()),
            'updated_by'   => $this->user['id'],
            'updated_at'   =>date('Y-m-d H:i:s',time())
        ]);

        $this->jsonReturn($response);

    }

    /**
     * 分配报价人
     */
    public function assignQuoterAction(){

        $request = $this->validateRequests('inquiry_id,quote_id,serial_no');

        $inquiry = new InquiryModel();

        $user_id = $request['quote_id'];

        //发送短信通知
        $employee = new EmployeeModel();
        $this->sendSms($employee->getMobileByUserId($user_id),"SUBMIT",$employee->getUserNameById($user_id),$request['serial_no'],$this->user['name'],"BIZ_DISPATCHING","BIZ_QUOTING");


        $response = $inquiry->updateData([
            'id'=>$request['inquiry_id'],
            'status'       => 'BIZ_QUOTING', //事业部报价
            'quote_status' => 'ONGOING', //报价中
            'quote_id'     => $user_id,
            'now_agent_id' => $user_id,
            'inflow_time'   => date('Y-m-d H:i:s',time()),
            'updated_by'   => $this->user['id'],
            'updated_at'   =>date('Y-m-d H:i:s',time())
        ]);

        $quoteModel = new QuoteModel();

        $flag = $quoteModel->field('id')->where(['inquiry_id'=>$request['inquiry_id']])->find();

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
            $inquiryItems = $inquiryItemModel->where(['inquiry_id'=>$request['inquiry_id'],'deleted_flag'=>'N'])->field('id,sku,qty,unit')->select();

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
            $inquiryInfo = $inquiry->where(['id'=>$request['inquiry_id']])->find();
            $quote_id = $quoteModel->where(['id'=>$flag['id']])->save($quoteModel->create([
                'inquiry_id'     => $request['inquiry_id'],
                'serial_no'      => $request['serial_no'],
                'updated_by'     => $this->user['id'],
                'updated_at'     => date('Y-m-d H:i:s'),
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
            $quoteItemModel = new QuoteItemModel();

            $where['inquiry_id'] = $request['inquiry_id'];
            $where['deleted_flag'] = 'N';
            //查询所有已经添加过的，后面判断是添加还是修改
            $quoteItems = $quoteItemModel->where(['inquiry_id'=>$request['inquiry_id'],'deleted_flag'=>'N'])->getField('inquiry_item_id',true);

            $inquiryItems = $inquiryItemModel->where($where)->field('id,sku,qty,unit')->select();   //查出来询单所有的询单SKU，循环插入到报价单
            foreach ($inquiryItems as $item=>$value){
                $data = $quoteItemModel->create([
                    'quote_id'        => $quote_id,
                    'inquiry_id'      => $request['inquiry_id'],
                    'inquiry_item_id' => $value['id'],
                    'sku'             => $value['sku'],
                    'quote_qty'       => $value['qty'],
                    'quote_unit'      => $value['unit'],
                    'created_by'      => $this->user['id'],
                    'created_at'      => date('Y-m-d H:i:s')
                ]);
                //判断是添加还是修改
                if(in_array($value['id'],$quoteItems)){
                    $quoteItemModel->where('id='.$value['id'])->save($data);
                }else{
                    $quoteItemModel->add($data);
                }
            }
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
                if (empty($request[$param])) $this->jsonReturn(['code'=>'-104','message'=> L('MISSING_PARAMETER')]);
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

    public function sendEmailAction()
    {
        $name = $this->user['name'];

        $inquiry = new InquiryModel();
        $role_name = $inquiry->setRoleName($inquiry->getUserRoleById($this->user['id']));
        $serial_no = '23456789';

        $body = <<< Stilly
        <h2>【{$role_name}】{$name}</h2>
        <p>您好！由【{$role_name}】{$name}，提交的【询单流水号：{$serial_no}】，需要您的办理，请登录BOSS系统及时进行处理。</p>
Stilly;

        MailHelper::sendEmail('learnfans@aliyun.com', '【询报价】办理通知', $body,$name);

    }
}
