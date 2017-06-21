<?php
/**
 * name: Inquiry
 * desc: 询价单表
 * User: zhangyuliang
 * Date: 2017/6/16
 * Time: 15:11
 */
class InquiryModel extends PublicModel {

    protected $dbName = 'erui_db_ddl_rfq'; //数据库名称
    protected $tableName = 'inquiry'; //数据表表名

    const STATUS_DRAFT = 'DRAFT'; //DRAFT-草稿；
    const STATUS_SENT = 'SENT'; //SENT-已发出；
    const STATUS_CANCELED = 'CANCELED'; //CANCELED-已取消；
    const STATUS_DELETE = 'DELETED'; //DISABLED-删除；
    const STATUS_INVALID = 'INVALID'; //INVALID-无效询价

    const STATUS_NOT_QUOTED = 'NOT_QUOTED'; //NOT_QUOTED-未报价；
    const STATUS_ONGOING = 'ONGOING'; //ONGOING-报价中；
    const STATUS_APPROVING = 'APPROVING'; //APPROVING-待确认；
    const STATUS_APPROVED = 'APPROVED'; //APPROVED-已报价；
    const STATUS_WITHDREW = 'WITHDREW'; //WITHDREW-撤回报价；

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    protected function getcondition($condition = []) {
        $where = [];
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        }
        if (!empty($condition['inquiry_no'])) {
            $where['inquiry_no'] = $condition['inquiry_no'];
        }
        if (!empty($condition['created_by'])) {
            $where['created_by'] = $condition['created_by'];
        }
        if (!empty($condition['inquiry_status'])) {
            $where['inquiry_status'] = $condition['inquiry_status'];
        }
        if (!empty($condition['quote_status'])) {
            $where['quote_status'] = $condition['quote_status'];
        }
        if(!empty($condition['start_time']) && !empty($condition['end_time'])){
            $where['inquiry_time'] = array(
                array('gt',$condition['start_time']),
                array('lt',$condition['end_time'])
            );
        }
        return $where;
    }

    /**
     * 获取数据条数
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function getcount($condition = []) {
        $where = $this->getcondition($condition);
        try {
            return $this->where($where)
                ->field('id,inquiry_no,customer_id,inquirer,agent,inquiry_time,inquiry_status,quote_status,created_at')
                ->count('id');
        } catch (Exception $ex) {
            Log::write($ex->getMessage(), $level);
            return false;
        }
    }

    /**
     * 获取列表
     * @param mix $condition
     * @return mix
     * @author zhangyuliang
     */
    public function getlist($condition = []) {
        $where = $this->getcondition($condition);

        if (isset($condition['page']) && isset($condition['countPerPage'])) {
            $count = $this->getcount($condition);
            return $this->where($where)
                ->limit($condition['page'] . ',' . $condition['countPerPage'])
                ->field('id,inquiry_no,customer_id,inquirer,agent,inquiry_time,inquiry_status,quote_status,created_at')
                ->select();
        } else {
            return $this->where($where)
                ->field('id,inquiry_no,customer_id,inquirer,agent,inquiry_time,inquiry_status,quote_status,created_at')
                ->select();
        }
    }

    /**
     * 获取详情信息
     * @param  int $inquiry_no 询单号
     * @return mix
     * @author zhangyuliang
     */
    public function info($inquiry_no = '') {
        $where['inquiry_no'] = $inquiry_no;
        return $this->where($where)
            ->field('id,inquiry_no,customer_id,inquirer,agent,inquiry_time,inquiry_region,inquiry_country,trade_terms,trans_mode_bn,kerui_flag,bid_flag')
            ->find();
    }

    /**
     * 添加数据
     * @return mix
     * @author zhangyuliang
     */
    public function add_data($createcondition = []) {

        $data = $this->create($createcondition);

        switch ($createcondition['inquiry_status']) {
            case self::STATUS_DRAFT:
                $data['inquiry_status'] = $createcondition['inquiry_status'];
                break;
            case self::STATUS_SENT:
                $data['inquiry_status'] = $createcondition['inquiry_status'];
                break;
            case self::STATUS_DELETED:
                $data['inquiry_status'] = $createcondition['inquiry_status'];
                break;
            case self::STATUS_CANCELED:
                $data['inquiry_status'] = $createcondition['inquiry_status'];
                break;
            case self::STATUS_INVALID:
                $data['inquiry_status'] = $createcondition['inquiry_status'];
                break;
            default : $data['inquiry_status'] = self::STATUS_DRAFT;
                break;
        }

        switch ($createcondition['quote_status']) {
            case self::STATUS_NOT_QUOTED:
                $data['quote_status'] = $createcondition['quote_status'];
                break;
            case self::STATUS_ONGOING:
                $data['quote_status'] = $createcondition['quote_status'];
                break;
            case self::STATUS_APPROVED:
                $data['quote_status'] = $createcondition['quote_status'];
                break;
            case self::STATUS_APPROVING:
                $data['quote_status'] = $createcondition['quote_status'];
                break;
            case self::STATUS_WITHDREW:
                $data['quote_status'] = $createcondition['quote_status'];
                break;
            default : $data['inquiry_status'] = self::STATUS_NOT_QUOTED;
                break;
        }

        return $this->add($data);

    }

    /**
     * 更新数据
     * @param  mix $data 更新数据
     * @param  int $inquiry_no 询单号
     * @return bool
     * @author zhangyuliang
     */
    public function update_data($inquiry_no = '',$data = []) {

        $where['inquiry_no'] = $inquiry_no;
        return $this->where($where)->save($data);

    }

    /**
     * 删除数据
     * @param  int $inquiry_no 询单号
     * @return bool
     * @author zhangyuliang
     */
    public function delete_data($inquiry_no = '') {
        $where['inquiry_no'] = $inquiry_no;
        return $this->where($where)
            ->save(['inquiry_status' => 'DELETED']);
    }
}
