<?php

/**
 * @file    ProductLineQuoteModel
 * @desc    产品线报价模型
 * @auhtor    买买提
 */
class ProductLineQuoteModel extends PublicModel
{

    protected $dbName = 'erui_rfq' ; //数据库名称
    protected $tableName = 'inquiry' ; //数据表名称

    /**
     * ProductLineQuoteModel constructor.
     * @desc    构造方法
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @desc     根据条件获取产品线报价列表
     * @param    array    $condition    条件
     * @param    string    $order    排序方式
     * @return    mixed
     */
    public function getList(array $condition, $order='id desc')
    {
        //获取条件
        $where = $this->getCondition($condition);

        //筛选字段
        $fields = [
            'id',
            'serial_no',//流程编码
            'inquiry_country',//国家
            'buyer_name',//客户名称
            'agent',//市场经办人
            'created_at',//询价时间
            'inquiry_status',//项目状态
        ] ;

        //总记录数
        $totalCount = $this->getTotalCount($where);

        //有分页的情况
        if (isset($condition['currentPage']) && $condition['currentPage'] && isset($condition['pageSize']) && $condition['pageSize']) {

            //默认分页参数与
            $currentPage = 1;//当前页码
            $pageSize = 10;//每页显示数量

            //分页->当前页码
            if (isset($condition['currentPage']) && $condition['currentPage']){
                $currentPage = intval($condition['currentPage']) > 0 ? intval($condition['currentPage']) : 1 ;
            }

            //分页->每页显示数量
            if (isset($condition['pageSize']) && $condition['pageSize']){
                $pageSize = intval($condition['pageSize']) > 0 ? intval($condition['pageSize']) : 10 ;
            }

            //获取区间
            $limit = ($currentPage - 1) * $pageSize ;

            $data['totalCount'] = $totalCount ;
            $data['currentPage'] = $currentPage ;
            $data['pageSize'] = $pageSize ;
            $data['data'] = $this->order($order)->where($where)->limit($limit.','.$pageSize)->field($fields)->select();

            return $data;
        }

        //默认情况
        $data['totalCount'] = $totalCount ;
        $data['data'] = $this->where($where)->order($order)->field($fields)->select() ;
        return $data;
    }

    /**
     * @desc    获取询单信息
     * @param    array    $condition    条件
     * @return    mixed
     */
    public function getInquiryInfo(array $condition)
    {
        $where = $this->getCondition($condition);

        //筛选字段
        $inquiryFields = [
            'serial_no',
            'inquiry_no',
            'agent',//经办人
            'inquiry_region',//所属地区
            'inquiry_country',//国家
            'kerui_flag',//手否科瑞设备所用配件
            'bid_flag',//是否投标
            'inquiry_name',
            'payment_mode',//付款方式
            'trade_terms',//贸易术语
            'trans_mode',//运输方式
            'from_port',//起运港
            'from_country',//起运国
            'to_port',//目的港
            'to_country',//目的国
            'project_basic_info',//项目背景描述
            'quote_notes',//报价备注
            'adhoc_request',//客户检验要求
        ];

        //询单本身信息
        return  $this->where($where)->field($inquiryFields)->find();

    }

    /**
     * @desc    保存询单
     * @param    array    $data
     * @return    bool
     */
    public function saveInquiryInfo(array $data)
    {
        $where = ['inquiry_no'=>$data['inquiry_no']];
        $update = $this->filterInquiryFields($data);
        return $this->where($where)->save($update);
    }

    /**
     * 重组前段提交的询单数据
     * @param    array    $data    前段提交的字段数组
     * @return    array    重组后的字段数组
     */
    private function filterInquiryFields(array $data)
    {
        $fields =[];

        if (isset($data['payment_mode']) && $data['payment_mode']){
            $fields['payment_mode'] = $data['payment_mode'] ;
        }

        //市场状态改为待提交
        $fields['inquiry_status'] = 'TOSUBMIT' ;

        return $fields;
    }

    /**
     * @desc    删除询单信息sku
     * @param    array    $condition    条件
     * @return    array
     */
    public function deleteInquirySku(array $condition)
    {
        $where = $this->getCondition($condition);

        if(empty($where['inquiry_no'])){
            return $response = [
                'code'=> '-101' ,
                'message'=> '缺少询单号参数'
            ];
        }

        try{
            $modify = $this->where($where)->save(['inquiry_status'=> 'DELETED']);
            if (isset($modify)){
                return $result = [
                    'code' => '1',
                    'message' => '成功!'
                ] ;
            }else{
                return $result = [
                    'code' => '-101',
                    'message' => '删除失败!'
                ] ;
            }
        }catch (Exception $exception){
            return $result = [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ] ;
        }
    }

    /**
     * @desc    设置条件
     * @param    array    $condition    条件
     * @return    array    重组后的条件
     */
    protected function getCondition(array $condition=[])
    {

        $where = [] ;

        //按时间
        if(!empty($condition['start_time']) && !empty($condition['end_time'])){

            $where['created_at'] = array(
                array('gt',$condition['start_time']),
                array('lt',$condition['end_time'])
            );
        }
        //删除条件
        if(!empty($condition['inquiry_no'])){
            $where['inquiry_no'] = $condition['inquiry_no'];
        }

        return $where;
    }

    /**
     * @desc    根据条件获取总数
     * @param    array    $condition    条件
     * @return    mixed
     */
    protected function getTotalCount(array $condition = [])
    {
        return $this->where($condition)->count('id');
    }

}