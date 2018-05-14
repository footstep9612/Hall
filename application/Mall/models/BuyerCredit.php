<?php
/**
 * Created by PhpStorm.
 * User: klp
 * Date: 2018/2/27
 * Time: 9:47
 */
class BuyerCreditModel extends PublicModel
{
    protected $tableName = 'buyer_credit';
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
     * 获取详情
     */
    public function getInfo($buyer_no){
        return $this->where(['buyer_no' => $buyer_no])->find();
    }

    /**
     * 新建信息
     */
    public function create_data($data) {

        if(isset($data['name']) && !empty($data['name'])){
            $dataInfo['name'] = trim($data['name']);
        }
        if(isset($data['buyer_no']) && !empty($data['buyer_no'])){
            $dataInfo['buyer_no'] = trim($data['buyer_no']);
        }
        if(isset($data['source']) && !empty($data['source'])){
            $dataInfo['source'] = trim($data['source']);
        } else{
            $dataInfo['source'] = 'PORTAL';
        }
        $agent_model = new BuyerAgentModel();
        $agent_id = $agent_model->field('agent_id')->where(['buyer_id'=>$data['buyer_id']])->find();
        if($agent_id){
            $dataInfo['agent_id'] = $agent_id['agent_id'];
            $dataInfo['status'] = 'APPROVING';
        }else{
            $dataInfo['status'] = 'DRAFT';
        }
        $dataInfo['credit_apply_date'] = date('Y-m-d H:i:s',time());
        $result = $this->add($this->create($dataInfo));
        if($result){
            return true;
        }
        return false;
    }

    /**
     * 更新信息
     */
    public function update_data($data) {

        if(isset($data['name']) && !empty($data['name'])){
            $dataInfo['name'] = trim($data['name']);
        }
        if(isset($data['buyer_no']) && !empty($data['buyer_no'])){
            $dataInfo['buyer_no'] = trim($data['buyer_no']);
        }
        if(isset($data['sinosure_no']) && !empty($data['sinosure_no'])){
            $dataInfo['sinosure_no'] = trim($data['sinosure_no']);
        }
        if(isset($data['nolc_granted']) && !empty($data['nolc_granted'])){
            $dataInfo['nolc_granted'] = trim($data['nolc_granted']);
        }
        if(isset($data['nolc_deadline']) && !empty($data['nolc_deadline'])){
            $dataInfo['nolc_deadline'] = trim($data['nolc_deadline']);
        }
        if(isset($data['lc_granted']) && !empty($data['lc_granted'])){
            $dataInfo['lc_granted'] = trim($data['lc_granted']);
        }
        if(isset($data['lc_deadline']) && !empty($data['lc_deadline'])){
            $dataInfo['lc_deadline'] = trim($data['lc_deadline']);
        }
        if(isset($data['deadline_cur_unit']) && !empty($data['deadline_cur_unit'])){
            $dataInfo['deadline_cur_unit'] = trim($data['deadline_cur_unit']);
        }
        if(isset($data['credit_invalid_date']) && !empty($data['credit_invalid_date'])){
            $dataInfo['credit_invalid_date'] = trim($data['credit_invalid_date']);
        }
        if(isset($data['status']) && !empty($data['status'])){
            $dataInfo['status'] = strtoupper($data['status']);
        }
        $agent_model = new BuyerAgentModel();
        $agent_id = $agent_model->field('agent_id')->where(['buyer_id'=>$data['buyer_id']])->find();
        if($agent_id){
            $dataInfo['agent_id'] = $agent_id['agent_id'];
        }
        $result = $this->where(['buyer_no' => $dataInfo['buyer_no']])->save($this->create($dataInfo));
        if ($result !== false) {
            return true;
        }
        return false;
    }

    /**
     * 通过客户编码获取结算方式等字段
     * @author
     */
    public function getAccountSettleByNo($buyer_no,$name) {
        if(!$buyer_no || !$name) return false;
        $credit_model = new BuyerCreditModel();
        $creditInfo = $credit_model->getInfo($buyer_no);
        if($creditInfo){
            return $creditInfo[$name];
        }
        return false;
    }

}