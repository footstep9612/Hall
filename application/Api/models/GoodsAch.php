<?php
/**
* Description of GoodsAchModel
*
 * @author  klp
*/
class GoodsAchModel extends PublicModel
{
    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'goods_attach'; //数据表表名

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 根据条件获取商品附件
     * @param null $where string 条件
     * @return mixed
     */
    public function getInfoByAch($sku)
    {
        $condition = array(
            'sku'     => $sku,
            'status'  => self::STATUS_VALID
        );
        $field = 'attach_type,attach_name,attach_url,sort_order';

        //根据缓存读取,没有则查找数据库并缓存
        $key_redis = md5(json_encode($condition));
        if(redisExist($key_redis)) {
            $result = json_decode(redisGet($key_redis));
            return $result ? $result : array();
        }
        try {
            $result = $this->field($field)->where($condition)->select();
            if ($result) {
                $data = array();
                //附件分组
                /**
                 * SMALL_IMAGE-小图；
                 * MIDDLE_IMAGE-中图；
                 * BIG_IMAGE-大图；
                 * DOC-文档（包括图片和各种文档类型）
                 * */
                foreach ($result as $val) {
                    $group = 'OTHERS';
                    switch ($val['attach_type']) {
                        case 'SMALL_IMAGE':
                            $group = 'SMALL_IMAGE';
                            break;
                        case 'MIDDLE_IMAGE':
                            $group = 'MIDDLE_IMAGE';
                            break;
                        case 'BIG_IMAGE':
                            $group = 'BIG_IMAGE';
                            break;
                        case 'DOC':
                            $group = 'DOC';
                            break;
                        default:
                            $group = 'OTHERS';
                            break;
                    }
                    $data[$group][] = $val;
                }
                redisSet($key_redis,json_encode($data));
                return $data;
            }
        }catch (Exception $e){
            return array();
        }
    }


}