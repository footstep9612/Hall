<?php
/**
 * SKU附件.
 * User: linkai
 * Date: 2017/6/20
 * Time: 14:19
 */
class ProductAttachModel extends PublicModel{
    //数据库 表映射
    protected $dbName = 'erui_db_ddl_goods';
    protected $tableName = 'product_attach';

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_TEST = 'TEST'; //测试；
    const STATUS_CHECKING = 'CHECKING'; //审核中；
    const STATUS_INVALID = 'INVALID';  //无效
    const STATUS_DELETED = 'DELETED'; //DELETED-删除

    /**
     * 根据spu获取附件
     * @param $spu spu编码
     * @return array
     */
    public function getAttachBySpu($spu=''){
        if(empty($spu))
            return false;

        $field = 'attach_type,attach_name,attach_url,status,created_at';
        $condition = array(
            'spu'=>$spu
        );
        $result = $this->field($field)->where($condition)->order('sort_order DESC')->select();
        if($result){
            $data = array();
            //按类型分组
            foreach($result as $item){
                $data[$item['attach_type']][] = $item;
            }
            return $data;
        }
        return array();
    }
}