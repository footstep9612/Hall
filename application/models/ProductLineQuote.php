<?php

/**
 * Class ProductLineQuoteModel
 * @desc 产品线报价模型
 * @auhtor 买买提
 */
class ProductLineQuoteModel extends PublicModel
{
    protected $dbName = 'erui_rfq' ; //数据库名称
    protected $tableName = 'quote' ; //数据表名称

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 根据条件获取产品线报价列表
     */
    public function getList(array $condition, $order='id desc', $type=true)
    {
        //过滤掉token
        unset($condition['token']);

        //默认分页参数与
        $current_no = 1;//当前页码
        $pagesize = 10;//每页显示数量

        $where = $this->getCondition($condition);

        //有分页的情况
        if ($type)
        {
            //分页->当前页码
            if (isset($condition['current_no']) && $condition['current_no'])
            {
                $current_no = intval($condition['current_no']) > 0 ? intval($condition['current_no']) : 1 ;
            }

            //分页->每页显示数量
            if (isset($condition['pagesize']) && $condition['pagesize'])
            {
                $pagesize = intval($condition['pagesize']) > 0 ? intval($condition['pagesize']) : 10 ;
            }

            //获取区间
            $limit = ($current_no - 1) * $pagesize ;

            return $this->order($order)->where($where)->limit($limit)->select();
        }

        //默认情况
        return $this->where($where)->order($order)->select();

    }

    /**
     * 设置条件
     * @param array $condition 条件数据
     * @return array 重组后的条件
     */
    protected function getCondition(array $condition=[])
    {
        $data = [] ;
        return $data;
    }


}