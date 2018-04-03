<?php
/**
 * name: InquiryAttach
 * desc: 询价单附件表
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:14
 */
class InquiryOrderModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry_order'; //数据表表名

    public function __construct() {
        parent::__construct();
    }
    /**
     * 添加数据
     * @param Array $condition
     * @return Array
     * @author jianghongwei
     */
    public function addData($condition = []) {
        $data = $this->create($condition);
        if(isset($condition['inquiry_id'])){
            $data['inquiry_id'] = $condition['inquiry_id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }
        if(isset($condition['order_id'])){
            $data['order_id'] = $condition['order_id'];
        }else{
            $results['code'] = '-103';
            $results['message'] = L('MISSING_PARAMETER');
            return $results;
        }
        $data['serial_no'] = $condition['serial_no'];
        $data['contract_no'] = $condition['contract_no'];
        $data = $this->create($data);
        try {
            $id = $this->add($data);
            if($id){
                $results['code'] = '1';
                $results['message'] = L('SUCCESS');
            }else{
                $results['code'] = '-101';
                $results['message'] = L('FAIL');
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * @desc 根据销售合同号获取询单ID
     *
     * @param string $no
     * @return mixed
     * @author liujf
     * @time 2018-03-26
     */
    public function getInquiryIdByContractNo($no) {
        return $this->where(['contract_no' => ['like', '%' . trim($no) . '%']])->getField('inquiry_id', true);
    }

    /**
     * @desc 根据是否存在销售合同号询单ID
     *
     * @param string $no
     * @return mixed
     * @author liujf
     * @time 2018-03-26
     */
    public function getInquiryIdForContractNo($no) {
        if($no == 'Y'){
            $where['contract_no'] = ['exp','is null'];
        }else{
            $where['contract_no'] = ['exp','is not null'];
        }
        return $this->where($where)->getField('inquiry_id', true);
    }
}