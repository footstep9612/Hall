<?php

/**
 * Class GoodsAttrModel
 *  @author  klp
 */
class GoodsAttrModel extends PublicModel
{
    protected $dbName = 'erui_db_ddl_goods'; //数据库名称
    protected $tableName = 'goods_attr'; //数据表表名

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETE = 'DELETE'; //删除；

    /**
     * 编辑商品属性查询p
     * @param null $where string 条件
     * @return mixed
     */
    public function getAttrBySku($sku, $lang='')
    {
        if(''!=$lang){
            $where['lang'] = $lang;
        }
        $where = array(
            'sku' => $sku,
            'status' => self::STATUS_VALID
        );


        //缓存数据redis查询
        $key_redis = md5(json_encode($where.time()));
        if(redisExist($key_redis)){
            $result = redisHashGet('attrs',$key_redis);
            //判断语言,返回对应语言集
            $data = array();
            if(''!=$lang){
                foreach($result as $val) {
                    if ($val['lang'] == $lang) {
                        $data[$val['lang']] = $val;
                    }
                }
                return $data ? $data : array();
            } else{
                return $result ? $result : array();
            }
        } else {
            $field = 'lang,spu,attr_group,attr_name,attr_value_type,attr_value,value_unit,goods_flag,logi_flag,hs_flag,spec_flag';

            $gattrs = $this->field($field)
                ->where($where)
                ->select();

            //查询产品对应属性
            $productAttr = new ProductAttrModel();
            $spu = $gattrs[0]['spu'];
            $condition = array(
                'spu' => $spu,
                'lang' => $lang,
                'status' => self::STATUS_VALID
            );
            $pattrs = $productAttr->field($field)->where($condition)->select();

            $data = array_merge($pattrs, $gattrs);
            //进行属性分组
            /**
             ** 属性分类:一级
             *   goods_flag - 商品属性
             *   spec_flag - 规格型号
             *   logi_flag  - 物流属性
             *   hs_flag  - 申报要素
             *   Others - 其他　
             ** 属性分组:二级
             *   Specs - 规格
             *   Technical Parameters - 技术参数
             *   Executive Standard - 技术标准
             *   Product Information - 简要信息
             *   Quatlity Warranty - 质量保证
             *   Others - 其他属性
             *  Image - 附件
             *  Documentation - 技术文档　
             */
            $attrs = array();
            foreach ($data as $item) {
                $group1 = '';
                $group2 = 'others';
                switch ($item['attr_group']) {
                    case 'Specs':
                        $group2 = 'Specs';
                        break;
                    case 'Technical Parameters':
                        $group2 = 'Technical Parameters';
                        break;
                    case 'Executive Standard':
                        $group2 = 'Executive Standard';
                        break;
                    case 'Product Information':
                        $group2 = 'Product Information';
                        break;
                    case 'Quatlity Warranty':
                        $group2 = 'Quatlity Warranty';
                        break;
                    case 'Image':
                        $group2 = 'Image';
                        break;
                    case 'Documentation':
                        $group2 = 'Documentation';
                        break;
                }
                if ($item['goods_flag'] == 'Y') {
                    $group1 = 'goods_flag';
                    $attrs[$item['lang']][$group1][$group2][] = $item;
                }
                if ($item['logi_flag'] == 'Y') {
                    $group1 = 'logi_flag';
                    $attrs[$item['lang']][$group1][$group2][] = $item;
                }
                if ($item['hs_flag'] == 'Y') {
                    $group1 = 'hs_flag';
                    $attrs[$item['lang']][$group1][$group2][] = $item;
                }
                if ($item['spec_flag'] == 'Y') {
                    $group1 = 'spec_flag';
                    $attrs[$item['lang']][$group1][$group2][] = $item;
                }
                if ($group1 == '') {
                    $group1 = 'others';
                    $attrs[$item['lang']][$group1][$group2][] = $item;
                }
            }
        }
        if($attrs){
            redisHashSet('attrs',$key_redis,$attrs);
            return $attrs;
        } else {
            return array();
        }
    }

}