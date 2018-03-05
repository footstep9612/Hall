<?php
/**
 * 商品附件
 * Created by PhpStorm.
 * User: linkai
 * Date: 2017/12/20
 * Time: 22:21
 */
class GoodsAttachModel extends PublicModel {

    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'goods_attach'; //数据表表名

    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETED = 'DELETED'; //删除；
    const STATUS_CHECKING = 'CHECKING'; //审核；
    const STATUS_DRAFT = 'DRAFT';       //草稿
    const DELETE_Y = 'Y';
    const DELETE_N = 'N';

    /**
     * 获取商品附件
     * @param array $condition
     * @return array|mixed
     */
    public function getAttach($condition = []) {
        $sku = isset($condition['sku']) ? $condition['sku'] : '';
        if (empty($sku)) {
            jsonReturn('', 1000, '[sku]不可以为空');
        }
        $where = array(
            'sku' => $sku,
        );
        $type = isset($condition['attach_type']) ? strtoupper($condition['attach_type']) : '';
        if ($type) {
            if (!in_array($type, array('SMALL_IMAGE', 'MIDDLE_IMAGE', 'BIG_IMAGE', 'DOC'))) {
                jsonReturn('', 1000, '[type]不正确');
            }
            $where['attach_type'] = $type;
        }
        $status = isset($condition['status']) ? strtoupper($condition['status']) : '';
        if ($status) {
            if ($status != '' && !in_array($status, array('VALID', 'INVALID', 'DELETED'))) {
                jsonReturn('', 1000, '[status]不正确');
            }
            $where['status'] = $status;
        }

        //读取redis缓存
        if (redisHashExist('Attach', $sku . '_' . $type . '_' . $status)) {
            //return (array) json_decode(redisHashGet('Attach', $sku . '_' . $type . '_' . $status));
        }

        try {
            $field = 'id,attach_type,attach_name,attach_url,status,created_at';
            $result = $this->field($field)->where($where)->select();
            if ($result) {
                $data = array();
                //按类型分组
                if (empty($type)) {
                    foreach ($result as $item) {
                        $data[$item['attach_type']][] = $item;
                    }
                    $result = $data;
                }
                //添加到缓存
                //redisHashSet('Attach', $sku . '_' . $type . '_' . $status, json_encode($result));
                return $result;
            }
        } catch (Exception $e) {
            return array();
        }
        return array();
    }

}
