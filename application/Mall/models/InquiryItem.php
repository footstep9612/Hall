<?php

/**
 * name: InquiryItem
 * desc: 询单明细表
 * User: 张玉良
 * Date: 2017/8/2
 * Time: 10:54
 */
class InquiryitemModel extends PublicModel {

    protected $dbName = 'erui_rfq'; //数据库名称
    protected $tableName = 'inquiry_item'; //数据表表名

    public function __construct() {
        parent::__construct();
    }

    /**
     * 根据条件获取查询条件
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     *
     */
    protected function getCondition($condition = []) {
        $where = [];
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];    //明细id
        }
        if (!empty($condition['inquiry_id'])) {
            $where['inquiry_id'] = $condition['inquiry_id'];    //询单id
        }
        if (!empty($condition['sku'])) {
            $where['sku'] = $condition['sku'];  //商品SKU
        }
        if (!empty($condition['brand'])) {
            $where['brand'] = $condition['brand'];  //品牌
        }
        $where['deleted_flag'] = !empty($condition['deleted_flag']) ? $condition['deleted_flag'] : 'N';
        return $where;
    }

    /**
     * 获取数据条数.
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getCount($condition = []) {
        $where = $this->getCondition($condition);
        return $this->where($where)->count('id');
    }

    /**
     * 获取sku总数.
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getSkusCount($condition = []) {
        $where = $this->getCondition($condition);
        $res = $this->where($where)->field('sku,qty')->select();
        $counts = 0;
        if($res) {
            foreach($res as $item) {
                $counts += $item['qty'];
            }
        }
        return $counts;

    }

    /**
     * 获取列表.
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getList($condition = []) {
        if (isset($condition['inquiry_id']) && !empty($condition['inquiry_id'])) {
            $where['t.inquiry_id'] = $condition['inquiry_id'];    //询单id
        } else {
            return [];
        }

        $goods_model = new GoodsModel();
        $goods_table = $goods_model->getTableName();

        $InquiryItemAttach_model = new InquiryItemAttachModel();
        $item_attach_table = $InquiryItemAttach_model->getTableName();

        try {
            $list = $this->field('t.*,g.exw_days,g.min_pack_naked_qty,it.*')
                            ->alias('t')
                            ->join($goods_table . ' as g on g.sku=t.sku and g.lang=\'en\'', 'left')
                            ->join($item_attach_table . ' as it on it.inquiry_item_id = t.id', 'left')
                            ->where($where)->order('t.id asc')->select();

            if ($list) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $list;
            } else {
                $results['code'] = '-101';
                $results['message'] = '没有找到相关信息!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 获取详情信息.
     * @param Array $condition
     * @return Array
     * @author zhangyuliang
     */
    public function getInfo($condition = []) {
        if (!empty($condition['id'])) {
            $where['id'] = $condition['id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有ID!';
            return $results;
        }

        try {
            $info = $this->where($where)->find();

            if ($info) {
                $results['code'] = '1';
                $results['message'] = '成功！';
                $results['data'] = $info;
            } else {
                $results['code'] = '-101';
                $results['message'] = '没有找到相关信息!';
            }
            return $results;
        } catch (Exception $e) {
            $results['code'] = $e->getCode();
            $results['message'] = $e->getMessage();
            return $results;
        }
    }

    /**
     * 添加数据.
     * @param Array $condition
     * @return Array
     * @author link
     */
    public function addData($condition = []) {
        if (!empty($condition['inquiry_id'])) {
            $data['inquiry_id'] = $condition['inquiry_id'];
        } else {
            $results['code'] = '-103';
            $results['message'] = '没有询单ID!';
            return $results;
        }
        //询单item附件
        $attach = [];
        if(isset($condition['attach'])){
            if( !empty($condition['attach']['attach_url'])){
                $attach = $condition['attach'];
            }
            unset($condition['attach']);
        }

        $data = $this->create($condition);
        $data['created_at'] = $this->getTime();
        try {
            $id = $this->add($data);
            if ($id) {
                if($attach){
                    $attach['inquiry_id'] = $data['inquiry_id'];
                    $attach['inquiry_item_id'] = $id;
                    $attach['created_at'] = $this->getTime();
                    $iiaModel = new InquiryItemAttachModel();
                    $iiaModel->add($iiaModel->create($attach));
                }
                $results['code'] = '1';
                $results['message'] = '成功！';
            } else {
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

    /**
     * 返回格式化时间
     * @author zhangyuliang
     */
    public function getTime() {
        return date('Y-m-d h:i:s', time());
    }

}
