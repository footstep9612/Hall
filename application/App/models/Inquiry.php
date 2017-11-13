<?php

/**
 * @desc   询单模型
 * @Author 买买提
 */
class InquiryModel extends PublicModel
{

    protected $dbName = 'erui_rfq';
    protected $tableName = 'inquiry';

    public function __construct() {
        parent::__construct();
    }

    /**
     * 获取统计数据
     * @param $type 类型
     * @return mixed
     */
    public function getStatisticsByType($type)
    {
        switch ($type)
        {
            case 'TODAY' :
                $where = "DATE_FORMAT(created_at,'%Y-%m-%d') = DATE_FORMAT(NOW(),'%Y-%m-%d')";
                $data = $this->where($where)->count('id');
                break;
            case 'TOTAL' :
                $data = $this->count('id');
                break;
            case 'QUOTED' :
                $data = $this->where(['quote_status'=>'QUOTED'])->count('id');
                break;
        }
        return $data;
    }

    /**
     * 获取指定数的记录
     * @param string $uid 用户
     * @param int $limit 数量
     * @return mixed
     */
    public function getNewItems($uid, $limit=3)
    {
        $where = [];
        return $this->alias('a')
                    ->join("erui_sys.employee e ON a.now_agent_id=e.id","LEFT")
                    ->where($where)
                    ->field('a.id,a.buyer_name,a.created_at,a.serial_no,a.quote_status,e.name')
                    ->limit($limit)
                    ->order('a.created_at DESC')
                    ->select();
    }

    /**
     * 创建对象
     * @param array $condition 数据
     * @return array
     */
    public function addData($condition = [])
    {

        $data = $this->create($condition);
        $time = $this->getTime();
        $data['quote_status'] = 'NOT_QUOTED';
        $data['inflow_time'] = $time;
        $data['created_at'] = $time;

        try {
            $id = $this->add($data);
            if($id){
                $results = ['id'=>$id,'serial_no'=>$data['serial_no']];
            }else{
                $results['code'] = '-101';
                $results['message'] = '添加失败!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }

    }

    public function updateData($data = [])
    {

        if (!empty($data['status'])) $data['inflow_time'] = $time;
        if (!empty($data['agent_id'])) $data['now_agent_id'] = $data['agent_id'];

        $data['updated_at'] = $this->getTime();

        try{
            $id = $this->save($this->create($data));
            if($id){

                //处理附件
                if (isset($data['attach_url']) && !empty($data['attach_url'])){

                    $inquiryAttach = new InquiryAttachModel();
                    $inquiryAttach->add($inquiryAttach->create([
                        'inquiry_id' => $data['id'],
                        'attach_group' => 'INQUIRY_SKU',
                        'attach_name' => $data['attach_name'],
                        'attach_url' => $data['attach_url'],
                        'created_by' => $data['updated_by'],
                        'created_at' => $this->getTime()
                    ]));
                }

                $results['code'] = '1';
                $results['message'] = '成功！';

            }else{
                $results['code'] = '-101';
                $results['message'] = '修改失败!';
            }
        }catch (Exception $exception){
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
        }

        return $results;

    }

    /**
     * 格式化返回当前时间
     * @return false|string
     */
    private function getTime()
    {
        return date('Y-m-d H:i:s',time());
    }

}
