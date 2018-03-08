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
        'biz_nature',//(128)  '企业性质-私营-公营-中资-子公司-联号',
        'biz_scope',//(500)  '经营性质-批发-零售-生产-代理',
        'biz_type',//(30)  '企业类型',
        'gov_org',//(30)  '政府机构:1-是,2-否',
        'listed_company',//(30)  '上市企业:1-是,2-否',
        'stock_exchange',//(30)  '证券交易所',
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
        try{
            $dataInfo['remarks'] = $data['remarks'];
            $dataInfo = $this->_getData($data);
            $dataInfo['deleted_flag'] = 'N';
            $dataInfo['status'] = 'VALID';
            $dataInfo['created_by'] = $data['agent_by'];
            $dataInfo['created_at'] = date('Y-m-d H:i:s', time());
            $result = $this->add($this->create($dataInfo));
            if($result){
                //添加审核信息
                $credit_model = new BuyerCreditModel();
                $data['source'] = 'BOSS';
                $credit_model->create_data($data);
                //添加申请日志
                $credit_log_model = new BuyerCreditLogModel();
                $dataArr['buyer_no'] = $data['buyer_no'];
                $dataArr['credit_apply_date'] = date('Y-m-d H:i:s',time());
                $dataArr['in_status'] = 'DRAFT';  //草稿,银行信息提交后->状态:待审核
                $dataArr['agent_by'] = $data['agent_by'];
                $dataArr['agent_at'] = date('Y-m-d H:i:s',time());
                $dataArr['sign'] = 1;
                $credit_log_model->create_data($dataArr);
                $dataArr['sign'] = 2;
                $credit_log_model->create_data($dataArr);
                return $result;
            }
            return false;
        }catch (Exception $e){
            $this->rollback();
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerCompanyModel】create_data:' . $e , Log::ERR);
            return false;
        }
    }

    /**
     * 更新企业信息
     */
    public function update_data($data)
    {
        try{
            $dataInfo = $this->_getData($data);
            $dataInfo['deleted_flag'] = 'N';
            $dataInfo['updated_by'] = $data['agent_by'];
            $dataInfo['updated_at'] = date('Y-m-d H:i:s', time());
            $result = $this->where(['buyer_no' => $dataInfo['buyer_no']])->save($this->create($dataInfo));
            if ($result !== false) {
                return $result;
            }
            return false;
        }catch (Exception $e){
            $this->rollback();
            Log::write(__CLASS__ . PHP_EOL . __LINE__ . PHP_EOL . '【BuyerCompanyModel】update_data:' . $e , Log::ERR);
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