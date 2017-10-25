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

        $this->inquiryModel = new InquiryModel();
        $this->requestParams = json_decode(file_get_contents("php://input"), true);

    }


    /**
     * 退回市场
     */
    public function rejectToMarketAction(){

        $request = $this->validateRequests('inquiry_id');

        $inquiry = new InquiryModel();
        $response = $inquiry->where(['id'=>$request['inquiry_id']])->save([
            'status'=>'DRAFT',
            'updated_by' => $this->user['id'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->jsonReturn($response);

    }

    /**
     * 退回易瑞
     */
    public function rejectToEruiAction(){

        $request = $this->validateRequests('inquiry_id');

        $inquiry = new InquiryModel();
        $roleModel = new RoleModel();
        $roleUserModel = new RoleUserModel();
        $role_id = $roleModel->where(['role_no'=>$inquiry::inquiryIssueRole])->getField('id');
        $roleUser = $roleUserModel->where(['role_id'=>$role_id])->getField('employee_id');

        $response = $inquiry->where(['id'=>$request['inquiry_id']])->save([
            'status' => 'CC_DISPATCHING', //易瑞客户中心
            'erui_id' => $roleUser,
            'updated_by' => $this->user['id'],
            'updated_at' => date('Y-m-d H:i:s')
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
            'status'=>'BIZ_QUOTING', //事业部报价
            'quote_id' => $request['quote_id'],
            'updated_by' => $this->user['id'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $quoteModel = new QuoteModel();

        $flag = $quoteModel->where(['inquiry_id'=>$request['inquiry_id']])->find();

        if (!$flag){
            $quote_id = $quoteModel->add($quoteModel->create([
                'inquiry_id' => $request['inquiry_id'],
                'serial_no' => $request['serial_no'],
                'quote_no' => $this->getQuoteNo(),
                'created_by' => $this->user['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'BIZ_QUOTING'
            ]));

            $inquiryItemModel = new InquiryItemModel();
            $inquiryItems = $inquiryItemModel->where(['inquiry_id'=>$request['inquiry_id']])->field('id,sku,qty,unit')->select();

            $quoteItemModel = new QuoteItemModel();
            foreach ($inquiryItems as $item=>$value){
                $quoteItemModel->add($quoteItemModel->create([
                    'quote_id' => $quote_id,
                    'inquiry_id' => $request['inquiry_id'],
                    'inquiry_item_id' => $value['id'],
                    'sku' => $value['sku'],
                    'quote_qty' => $value['qty'],
                    'quote_unit' => $value['unit'],
                    'created_by' => $this->user['id'],
                    'created_at' => date('Y-m-d H:i:s')
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

}