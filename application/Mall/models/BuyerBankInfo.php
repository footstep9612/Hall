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
     * 新建银行信息
     */
    public function create_data($data) {


        $dataInfo = $this->_getData($data);
        $dataInfo['remarks'] = $data['bank_remarks'];
        $dataInfo['deleted_flag'] = 'N';
        $dataInfo['status'] = 'VALID';
        $dataInfo['created_by'] = $data['buyer_id'];
        $dataInfo['created_at'] = date('Y-m-d H:i:s',time());
        $result = $this->add($this->create($dataInfo));
        if($result){
            return true;
        }
        return false;
    }

    /**
     * 更新银行信息
     */
    public function update_data($data) {

        $dataInfo = $this->_getData($data);
        $dataInfo['deleted_flag'] = 'N';
        $dataInfo['updated_by'] = $data['buyer_id'];
        $dataInfo['updated_at'] = date('Y-m-d H:i:s',time());
        $result = $this->where(['buyer_no' => $dataInfo['buyer_no']])->save($this->create($dataInfo));
        //添加日志
        $check = $this->field('bank_name,bank_address')->where(['buyer_no' => $data['buyer_no']])->find();
        if(!empty($dataInfo['bank_name']) && $dataInfo['bank_name'] !== $check['bank_name'] || !empty($dataInfo['bank_address'] && $dataInfo['bank_address'] !== $check['bank_address'])) {
            $credit_log_model = new BuyerCreditLogModel();
            $dataArr['buyer_no'] = $data['buyer_no'];
            $dataArr['agent_by'] = $data['agent_by'];
            $dataArr['agent_at'] = date('Y-m-d H:i:s', time());
            $dataArr['bank_name'] = $dataInfo['bank_name'];
            $dataArr['bank_address'] = $dataInfo['bank_address'];
            $dataArr['sign'] = 2;
            $credit_log_model->create_data($this->create($dataArr));
        }
        if ($result !== false) {
            return true;
        }
        return false;
    }

    /**
     * 获取银行信息
     */
    public function getInfo($buyer_no){
        return $this->where(['buyer_no' => $buyer_no, 'deleted_flag' => 'N'])->find();
    }
}