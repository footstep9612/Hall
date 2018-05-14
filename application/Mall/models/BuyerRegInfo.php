<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/2/26
 * Time: 13:53
 */
class BuyerRegInfoModel extends PublicModel
{
    protected $tableName = 'buyer_reg_info';
    protected $dbName = 'buyer_credit'; //数据库名称

    const STATUS_DRAFT = 'DRAFT'; //草稿
    const STATUS_APPROVING = 'APPROVING'; //审核；
    const STATUS_VALID = 'VALID'; //生效；
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    protected $langs = ['en', 'es', 'ru', 'zh'];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 数据字典
     * @var $data
     */
    private $_field = [
        'buyer_no',//(40)  '客户编号',
        'country_bn',//(32)  '企业所在国家简称',
        'country_code',// char(3) '国家代码',
        'area_no',// char(4) '区域代码',
        'name',//(32)  '采购商英文名称',
        'name_zh',//(32)  '采购商中文名称',
        'name_bn',//(32)  '采购商简称',
        'registered_in',//(32)  '采购商英文地址',
        'registered_in_zh',//(32)  '采购商中文地址',
        'legal_person_name',//(30)  '法人代表',
        'legal_person_gender',//int(3)  '法人性别-1:男,-2:女,-3:不明',
        'tel_code',//  '电话区号',
        'tel',//  '企业电话',
        'contact_person',//  '企业联系人',
        'fax_code',//  '企业传真区号',
        'fax',//  '企业传真',
        'CEO',//(30)  'CEO',
        'CFO',//(30)  'CFO',
        'reg_date',//(10)  '注册时间',
        'establish_data',//(10)  '成立时间',
        'expiry_date',//(10)  '注册有效期',
        'reg_address',//  '注册地址',
        'registered_no',//(200)  '注册登记号',
        'reg_capital',//decimal(20,4)  '注册资本',
        'official_website',//(255)  '官网',
        'social_credit_code',//(32)  '社会信用代码',
        // 'biz_nature',//(128)  '企业性质-私营-公营-中资-子公司-联号',
        // 'biz_scope',//(500)  '经营性质-批发-零售-生产-代理',
        'biz_type',//(30)  '企业类型',
        'gov_org',//(30)  '政府机构:1-是,2-否',
        'listed_company',//(30)  '上市企业:1-是,2-否',
        // 'stock_exchange',//(30)  '证券交易所',
        'stock_code',//(30)  '股票代码',
        'equitiy',//decimal(20,4)  '资产净值',
        'turnover',//decimal(20,4)  '年销售额',

    ];

    /**
     * 格式化数据
     * @var $data
     * @author link 2017-12-20
     */
    private function _getData($data){
        if(empty($data)){
            return [];
        }
        foreach($data as $key =>$value){
            if(!in_array($key,$this->_field)){
                unset($data[$key]);
            }
            if(empty($value)){
                $data[$key] = null;
            }
        }
        return $data;
    }

    /**
     * 新建企业信息
     */
    public function create_data($data)
    {
        $this->startTrans();
        try{

            $dataInfo = $this->_getData($data);
            $dataInfo['remarks'] = $data['remarks'];
            $dataInfo['deleted_flag'] = 'N';
            $dataInfo['status'] = 'VALID';
            $dataInfo['created_by'] = $data['buyer_id'];
            $dataInfo['created_at'] = date('Y-m-d H:i:s', time());
            $result = $this->add($this->create($dataInfo));
            if($result){
                //添加银行信息
                if($data['account_settle'] != 'OA'){
                    $bank_model = new BuyerBankInfoModel();
                    $bank_res = $bank_model->create_data($data);
                    if(!$bank_res){
                        $this->rollback();
                        jsonReturn(null, MSG::MSG_FAILED, 'failed!');//添加银行信息失败
                    }
                }
                //添加审核信息
                $credit_model = new BuyerCreditModel();
                $data['source'] = 'PORTAL';
                $credit_model->create_data($data);

                //添加申请日志
                $credit_log_model = new BuyerCreditLogModel();
                $dataArr['buyer_no'] = $data['buyer_no'];
                $dataArr['credit_apply_date'] = date('Y-m-d H:i:s',time());
                $dataArr['in_status'] = 'DRAFT';
                $dataArr['sign'] = 1;
                $credit_log_model->create_data($dataArr);
                if($data['account_settle'] != 'OA') {
                    $dataArr['sign'] = 2;
                    $credit_log_model->create_data($dataArr);
                }
                $this->commit();
                return $result;
            }
            return false;
        }catch (Exception $e){
            $this->rollback();
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerCreditModel】create_data:' . $e , Log::ERR);
            //jsonReturn($e->getMessage());
            return false;
        }
    }

    /**
     * 更新企业信息
     */
    public function update_data($data)
    {
        $this->startTrans();
        try{
            $dataInfo = $this->_getData($data);
            $dataInfo['remarks'] = $data['remarks'];
            $dataInfo['deleted_flag'] = 'N';
            $dataInfo['updated_by'] = $data['buyer_id'];
            $dataInfo['updated_at'] = date('Y-m-d H:i:s', time());
            $result = $this->where(['buyer_no' => $dataInfo['buyer_no']])->save($this->create($dataInfo));
            if ($result !== false) {
                //更新银行信息
                if($data['account_settle'] != 'OA') {
                    $bank_model = new BuyerBankInfoModel();
                    $bank_res = $bank_model->update_data($data);
                    if (!$bank_res) {
                        $this->rollback();
                        jsonReturn(null, MSG::MSG_FAILED, 'failed!');//更新银行信息失败
                    }
                }
                //更新授信状态
                $credit_model = new BuyerCreditModel();
                $uparr= [
                    'status'=>'APPROVING',
                    'nolc_granted'=>'',
                    'nolc_deadline'=>'',
                    'lc_granted'=>'',
                    'lc_deadline'=>'',
                    'credit_valid_date'=>'',
                    'approved_date'=>'',
                    'credit_apply_date'=>date('Y-m-d H:i:s', time())
                ];
                $agent_model = new BuyerAgentModel();
                $agent_id = $agent_model->field('agent_id')->where(['buyer_id'=>$data['buyer_id']])->find();
                if($agent_id){
                    $dataInfo['agent_id'] = $agent_id['agent_id'];
                    $dataInfo['status'] = 'APPROVING';
                }else{
                    $dataInfo['status'] = 'DRAFT';
                }
                $uparr['account_settle'] = $data['account_settle'];
                $uparr['buyer_no'] = $data['buyer_no'];

                //添加日志
                $check = $this->field('name,registered_in')->where(['buyer_no' => $data['buyer_no']])->find();
                if(!empty($dataInfo['name']) && $dataInfo['name'] !== $check['name'] || !empty($dataInfo['registered_in'] && $dataInfo['registered_in'] !== $check['registered_in'])){
                    $credit_log_model = new BuyerCreditLogModel();
                    $dataArr['buyer_no'] = $data['buyer_no'];
                    $dataArr['agent_by'] = $data['agent_by'];
                    $dataArr['agent_at'] = date('Y-m-d H:i:s',time());
                    $dataArr['name'] = $dataInfo['name'];
                    $dataArr['address'] = $dataInfo['registered_in'];
                    $dataArr['sign'] = 1;  //企业
                    $credit_log_model->create_data($dataArr);
                }
                //更新审核信息
                $credit_model = new BuyerCreditModel();
                $credit_res = $credit_model->update_data($data);
                /*if(!$credit_res){
                    $this->rollback();
                    jsonReturn(null, MSG::MSG_FAILED, '更新审核信息失败');
                }*/
                $this->commit();
                return $result;
            }
            return false;
        }catch (Exception $e){
            $this->rollback();
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerCreditModel】update_data:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 获取企业信息
     */
    public function getInfo($buyer_no){
        return $this->where(['buyer_no' => $buyer_no, 'deleted_flag' => 'N'])->find();
    }
}