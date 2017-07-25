<?php

/**
 * Class ProductLineQuoteModel
 * @desc 产品线报价模型
 * @auhtor 买买提
 */
class ProductLineQuoteModel extends PublicModel
{
    protected $dbName = 'erui_rfq' ; //数据库名称
    protected $tableName = 'inquiry' ; //数据表名称

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据条件获取产品线报价列表
     */
    public function getList(array $condition, $order='id desc')
    {
        //过滤掉token
        unset($condition['token']);

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
        if (isset($condition['currentPage']) && $condition['currentPage'] && isset($condition['pageSize']) && $condition['pageSize'])
        {
            //默认分页参数与
            $currentPage = 1;//当前页码
            $pageSize = 10;//每页显示数量

            //分页->当前页码
            if (isset($condition['currentPage']) && $condition['currentPage'])
            {
                $currentPage = intval($condition['currentPage']) > 0 ? intval($condition['currentPage']) : 1 ;
            }

            //分页->每页显示数量
            if (isset($condition['pageSize']) && $condition['pageSize'])
            {
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
     * @desc 获取询单信息
     * @param array $condition
     * @author  买买提
     */
    public function getInquiryInfo(array $condition)
    {
        $where = $this->getCondition($condition);
        $inquiryFields = ['serial_no','inquiry_no','inquiry_name','payment_mode'];

        //询单本身信息
        return  $this->where($where)->field($inquiryFields)->find();

    }
    /**
     * @desc 删除询单信息sku
     * @param array $condition
     */
    public function deleteInquirySku(array $condition)
    {
        $where = $this->getCondition($condition);

        if(empty($where['inquiry_no']))
        {
            return $response = [
                'code'=> '-101' ,
                'message'=> '缺少询单号参数'
            ];
        }

        try{

            $modify = $this->where($where)->save([
                'inquiry_status'=> 'DELETED'
            ]);
            if (isset($modify))
            {
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
        }catch (Exception $exception)
        {
            return $result = [
                'code' => $exception->getCode(),
                'message' => $exception->getMessage()
            ] ;
        }
    }
    /**
     * 设置条件
     * @param array $condition 条件数据
     * @return array 重组后的条件
     */
    protected function getCondition(array $condition=[])
    {

        $where = [] ;

        //按时间
        if(!empty($condition['start_time']) && !empty($condition['end_time']))
        {
            $where['created_at'] = array(
                array('gt',$condition['start_time']),
                array('lt',$condition['end_time'])
            );
        }
        //删除条件
        if(!empty($condition['inquiry_no']))
        {
            $where['inquiry_no'] = $condition['inquiry_no'];
        }
        //p($where);
        return $where;
    }

    /**
     * 根据条件获取总数
     * @param array $condition
     * @return mixed
     */
    protected function getTotalCount(array $condition = [])
    {
        return $this->where($condition)->count('id');
    }

}