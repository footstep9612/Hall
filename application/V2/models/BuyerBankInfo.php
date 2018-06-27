<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/2/26
 * Time: 13:52
 */
class BuyerBankInfoModel extends PublicModel
{
    protected $tableName = 'buyer_bank_info';
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
        'buyer_no',//  '客户编号',
        'swift_code',// '银行SWIFT编码',
        'bank_name', // '开户银行英文名称',
        'bank_name_zh',// '开户银行中文名称',
        'bank_account',// '企业银行账号',
        'bank_country_code',// '银行国家代码',
        'bank_country_code',// '银行国家简称',
        'bank_address',// '银行地址',
        'bank_contact',//   '银行联系人',
        'bank_zipcode',//  '邮编',
        'tel_code_bank',//   '银行电话区号',
        'tel_bank',//   '银行电话',
        'fax_code_bank',//    '传真区号',
        'fax_bank',//    '银行传真',
        'bank_website',//   '银行官网',
        'legal_person_bank',//    '法人代表',
        'bank_reg_date',//    '注册时间',
        'early_trade_date',//   '最早成交年份(开始与保险公司往来年份)',
        'bank_turnover',// decimal(20,4)    '营业额',
        'profit',// decimal(20,4)    '利润',
        'total_assets',// decimal(20,4)    '总资产',
        'reg_capital_cur_bn',// (10)    '资金单位',
        'equity_ratio',// decimal(20,4)    '自有资产比例',
        'equity_capital',//decimal(20,4)    '自有资本',
        'branch_count',//int(11)    '分支数目',
        'employee_count',// bigint(20)    '员工数目',
        'bank_group_name',//(30)    '所属银行集团名称',
        'bank_group_swift',// (30)    '所属银行集团SWIFT',
        'intl_ranking',// (30)    '国际排名',
        'cn_ranking',//(30)    '国内排名',
        'stockholder',//(30)    '股东',
        'remarks',//(30)    '股东',
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
            $value = trim($value);
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
     * 新建银行信息
     */
    public function create_data($data) {
        try{

            $dataInfo = $this->_getData($data);
//            if(isset($data['tel_bank']) && is_numeric($data['tel_bank'])){
//                jsonReturn(null, -110, '电话应为数字!');
//            }
//            if(isset($data['fax_bank']) && is_numeric($data['fax_bank'])){
//                jsonReturn(null, -110, '传真应为数字!');
//            }
            if(isset($data['early_trade_date'])){
                $dataInfo['early_trade_date'] = date('Y',strtotime($data['early_trade_date']));
            }
            $dataInfo['deleted_flag'] = 'N';
            $dataInfo['status'] = 'VALID';
            $dataInfo['created_by'] = $data['agent_by'];
            $dataInfo['created_at'] = date('Y-m-d H:i:s',time());
            $result = $this->add($this->create($dataInfo));
            if($result){
                $credit_model = new BuyerCreditModel();
                $credit_log_model = new BuyerCreditLogModel();
                if(!empty($data['status']) && 'check' == trim($data['status'])) {
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
                    $uparr['status'] = "ERUI_APPROVING";      //提交易瑞审核
                    $uparr['buyer_no'] = $data['buyer_no'];
                    $credit_model->update_data($uparr);

                    $this->checkParam($data['buyer_no']);
                    //添加日志
                    $datalog['buyer_no'] = $data['buyer_no'];
                    $datalog['agent_by'] = $data['agent_by'];
                    $datalog['agent_at'] = date('Y-m-d H:i:s',time());
                    $datalog['in_status'] = "ERUI_APPROVING";
                    $datalog['sign'] = 1;
                    $credit_log_model->create_data($this->create($datalog));
                    $datalog['sign'] = 2;
                    $credit_log_model->create_data($this->create($datalog));
                } else{
                    //添加审核信息,状态修改
                    $credit_arr['status'] = 'APPROVING';
                    $credit_arr['buyer_no'] = $data['buyer_no'];
                    $credit_arr['credit_apply_date'] = date('Y-m-d H:i:s', time());
                    $credit_model->update_data($credit_arr);
                    //添加申请日志

                    $dataArr['buyer_no'] = $data['buyer_no'];
                    $dataArr['credit_apply_date'] = date('Y-m-d H:i:s',time());
                    $dataArr['in_status'] = 'APPROVING';
                    $dataArr['agent_by'] = $data['agent_by'];
                    $dataArr['agent_at'] = date('Y-m-d H:i:s',time());
                    $dataArr['bank_name'] = $dataInfo['bank_name'];
                    $dataArr['bank_address'] = $dataInfo['bank_address'];
                    $dataArr['sign'] = 2; //银行
                    $credit_log_model->create_data($dataArr);
                }
                return true;
            }
            return false;
        }catch (Exception $e){
            $this->rollback();
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerBankModel】create_data:' . $e , Log::ERR);
            LOG::write($e->getMessage(), LOG::ERR);
            //jsonReturn($e->getMessage());
            return false;
        }
    }

    /**
     * 更新银行信息
     */
    public function update_data($data) {
        try{
            $dataInfo = $this->_getData($data);
            $dataInfo['deleted_flag'] = 'N';
            $dataInfo['status'] = 'VALID';
            $dataInfo['updated_by'] = $data['agent_by'];
            $dataInfo['updated_at'] = date('Y-m-d H:i:s',time());
            $check = $this->field('bank_name,bank_address')->where(['buyer_no' => $data['buyer_no']])->find();
            $result = $this->where(['buyer_no' => $data['buyer_no']])->save($this->create($dataInfo));
            //添加日志
            $credit_log_model = new BuyerCreditLogModel();
            if(!empty($dataInfo['bank_name']) && $dataInfo['bank_name'] !== $check['bank_name'] || !empty($dataInfo['bank_address'] && $dataInfo['bank_address'] !== $check['bank_address'])){
                $dataArr['buyer_no'] = $data['buyer_no'];
                $dataArr['agent_by'] = $data['agent_by'];
                $dataArr['agent_at'] = date('Y-m-d H:i:s',time());
                $dataArr['bank_name'] = $dataInfo['bank_name'];
                $dataArr['bank_address'] = $dataInfo['bank_address'];
                $dataArr['sign'] = 2;
                $credit_log_model->create_data($dataArr);
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
            if(!empty($data['status']) && 'check' == trim($data['status'])) {
                $uparr['status'] = "ERUI_APPROVING";      //提交易瑞审核
                $uparr['buyer_no'] = $data['buyer_no'];
                $credit_model->update_data($uparr);

                $this->checkParam($data['buyer_no']);
                //添加日志
                $datalog['buyer_no'] = $data['buyer_no'];
                $datalog['agent_by'] = $data['agent_by'];
                $datalog['agent_at'] = date('Y-m-d H:i:s',time());
                $datalog['in_status'] = "ERUI_APPROVING";
                $datalog['sign'] = 1;
                $credit_log_model->create_data($this->create($datalog));
                $datalog['sign'] = 2;
                $credit_log_model->create_data($this->create($datalog));
            }

            //$credit_model->where(['buyer_no' => $data['buyer_no']])->save($this->create($uparr));

            if ($result !== false) {
                return $result;
            }
            return false;
        }catch (Exception $e){
            $this->rollback();
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerBankModel】update_data:' . $e , Log::ERR);
            LOG::write($e->getMessage(), LOG::ERR);
            return false;
        }
    }


    /**
     * 获取银行信息
     */
    public function getInfo($buyer_no){
        return $this->where(['buyer_no' => $buyer_no, 'deleted_flag' => 'N'])->find();
    }

    //验证信息
    public function checkParam($buyer_no){

        $buyerModel = new BuyerModel();          //企业信息
        $company_model = new BuyerRegInfoModel();
        $BuyerCodeApply = $company_model->getInfo($buyer_no);
        $lang = $buyerModel->field('lang,official_email')->where(['buyer_no'=> $buyer_no, 'deleted_flag'=>'N'])->find();
        if(!$BuyerCodeApply || !$lang){
            jsonReturn(null, -101 ,'企业信息不存在或已删除!');
        }
        $BuyerCodeApply['lang'] = $lang['lang'];
        $BuyerCodeApply['official_email'] = $lang['official_email'];
        $resBuyer = self::checkParamBuyer($BuyerCodeApply);
        if($resBuyer != 1) {
            jsonReturn('',MSG::MSG_FAILED,MSG::getMessage(MSG::MSG_FAILED));
        }
    }

    static public function checkParamBuyer(&$BuyerCodeApply){
        $results = array();
        if(!isset($BuyerCodeApply['lang'])){
            $results['code'] = -101;
            $results['message'] = '[lang]不能为空!';
        }
        if($BuyerCodeApply['lang'] == 'zh') {
            if(!isset($BuyerCodeApply['area_no']) || !is_numeric($BuyerCodeApply['area_no'])){
                $results['code'] = -101;
                $results['message'] = '[area_no]不能为空或不为整型!';
            }
            if(!isset($BuyerCodeApply['social_credit_code'])){
                $results['code'] = -101;
                $results['message'] = '[social_credit_code]社会信用代码不能为空!';
            }
        }
        if(!isset($BuyerCodeApply['buyer_no'])){
            $results['code'] = -101;
            $results['message'] = '[buyer_no]不能为空!';
        }
        if(!isset($BuyerCodeApply['country_code'])){
            $results['code'] = -101;
            $results['message'] = '[country_code]不能为空!';
        }
        if(strlen($BuyerCodeApply['country_code']) > 3){
            $results['code'] = -101;
            $results['message'] = '[country_code]不能超过三位!';
        }
        if(!isset($BuyerCodeApply['name'])){
            $results['code'] = -101;
            $results['message'] = '[name]不能为空!';
        }
        if(!isset($BuyerCodeApply['registered_in'])){
            $results['code'] = -101;
            $results['message'] = '[address]不能为空!';
        }
        if($results){
            jsonReturn($results);
        }
        return ShopMsg::CREDIT_SUCCESS;;
    }
}