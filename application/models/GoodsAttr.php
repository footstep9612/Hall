<?php

/**
 * Class GoodsAttrModel
 *  @author  klp
 */

class GoodsAttrModel extends PublicModel
{

    protected $dbName = 'erui_goods'; //数据库名称
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

    public function getAttrBySku($sku, $lang = '') {
        if($lang!=''){
            $where['lang'] = $lang;
        }
        $where = array(
            'sku' => $sku,
            'lang'=> $lang,
            'status' => self::STATUS_VALID
        );

        //缓存数据redis查询
        $key_redis = md5(json_encode($where));
        if(redisExist($key_redis)){
            $result = redisGet($key_redis);
            return $result ? json_decode($result,true) : array();
        } else {
            $field = 'lang,spu,attr_group,attr_name,attr_value_type,attr_value,value_unit,sort_order,goods_flag,logi_flag,hs_flag,spec_flag';

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
             * * 属性分类:一级
             *   goods_flag - 商品属性
             *   spec_flag - 规格型号
             *   logi_flag  - 物流属性
             *   hs_flag  - 申报要素
             *   Others - 其他　
             * * 属性分组:二级
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
                    case 'TechnicalParameters':
                        $group2 = 'TechnicalParameters';
                        break;
                    case 'ExecutiveStandard':
                        $group2 = 'ExecutiveStandard';
                        break;
                    case 'ProductInformation':
                        $group2 = 'ProductInformation';
                        break;
                    case 'QuatlityWarranty':
                        $group2 = 'QuatlityWarranty';
                        break;
                    case 'Image':
                        $group2 = 'Image';
                        break;
                    case 'Documentation':
                        $group2 = 'Documentation';
                        break;
                    default:
                        $group2 = 'others';
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

                if ($attrs) {
                    redisSet($key_redis, json_encode($attrs));
                    return $attrs;
                } else {
                    return array();
                }
            }
        }
    }
    /**
     * 编辑商品属性查询a
     * @param null $where string 条件
     * @return
     */
    public function attrBySku($sku='', $lang = '') {
        if($sku='') {
            return false;
        }
        if($lang='') {
            return false;
        }
        $where = array(
            'sku' => $sku,
            'lang'=> $lang,
            'status' => self::STATUS_VALID
        );

        //缓存数据redis查询
        $key_redis = md5(json_encode($where));
        if(redisExist($key_redis)){
            $result = redisGet($key_redis);
            return $result ? json_decode($result,true) : array();
        } else {
            $field = 'lang,spu,attr_group,attr_name,attr_value_type,attr_value,value_unit,sort_order,goods_flag,logi_flag,hs_flag,spec_flag';

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
             * * 属性分类:一级
             *   goods_flag - 商品属性
             *   spec_flag - 规格型号
             *   logi_flag  - 物流属性
             *   hs_flag  - 申报要素
             *   Others - 其他　
             */
            $attrs = array();
            foreach ($data as $item) {
                $group1 = '';
                if ($item['goods_flag'] == 'Y') {
                    $group1 = 'goods_flag';
                    $attrs[$group1][] = $item;
                }
                if ($item['logi_flag'] == 'Y') {
                    $group1 = 'logi_flag';
                    $attrs[$group1][] = $item;
                }
                if ($item['hs_flag'] == 'Y') {
                    $group1 = 'hs_flag';
                    $attrs[$group1][] = $item;
                }
                if ($item['spec_flag'] == 'Y') {
                    $group1 = 'spec_flag';
                    $attrs[$group1][] = $item;
                }
                if ($group1 == '') {
                    $group1 = 'others';
                    $attrs[$group1][] = $item;
                }
                if ($attrs) {
                    redisSet($key_redis,json_encode($attrs));
                    return $attrs;
                } else {
                    return array();
                }
            }
        }
    }


    /**
     * 获取sku的规格属性
     * @author link 2017-06-23
     * @param string $sku
     * @param string $lang
     * @return array|mixed
     */
    public function getSpecBySku($sku='',$lang =''){
        if(empty($sku) || empty($lang))
            return array();

        //检查redis
        /*if(redisHashExist('spec','spec_'.$sku.'_'.$lang)){
            return json_decode(redisHashGet('spec', 'spec_'.$sku.'_'.$lang),true);
        }*/

        $field = 'attr_no,attr_name,attr_value_type,attr_value,value_unit';
        $condition = array(
            'sku' => $sku,
            'lang' =>$lang,
            'spec_flag' =>'Y',
            'status' =>self::STATUS_VALID
        );
        try{
            $result = $this->field($field)->where($condition)->select();
            if($result){
                //redisHashSet('spec','spec_'.$sku.'_'.$lang, json_encode($result));
                return $result;
            }
        }catch (Exception $e){
            return array();
        }
        return array();
    }


    /**
     * 商品属性 -- 公共
     * @author link 2017-06-26
     * @param array $condition
     * @return array
     */
    public function getAttr($condition=[]){
        if(!isset($condition['sku']))
            return array();

        //组装条件
        $where = array(
            'sku' => trim($condition['sku']),
        );
        if(isset($condition['lang']) && in_array(strtolower($condition['lang']),array('zh','en','es','ru'))){
            $where['lang'] = strtolower($condition['lang']);
        }
        if(isset($condition['status']) && in_array(strtoupper($condition['status']),array('VALID','INVALID','DELETED'))){
            $where['status'] = strtoupper($condition['status']);
        }
        if(isset($condition['attr_type'])){
            switch($condition['attr_type']){
                case 'goods_flag':
                    $where['goods_flag'] = 'Y';
                    break;
                case 'spec_flag':
                    $where['spec_flag'] = 'Y';
                    break;
                case 'logi_flag':
                    $where['logi_flag'] = 'Y';
                    break;
                case 'hs_flag':
                    $where['hs_flag'] = 'Y';
                    break;
            }
        }

        //redis获取
        if(redisHashExist('Attr',md5(json_encode($where)))){
            return json_decode(redisHashGet('Attr',md5(json_encode($where))),true);
        }

        //查询
        try{
            $field = 'lang,attr_no,attr_name,attr_value_type,attr_value,value_unit,attr_group,sort_order,goods_flag,logi_flag,hs_flag,spec_flag,status';
            $result = $this->field($field)->where($where)->order('sort_order')->select();

            //根据sku获取spu
            $gmodel = new GoodsModel();
            $spu = $gmodel->getSpubySku(trim($condition['sku']));

            //获取产品属性
            $product = new ProductAttrModel();
            $pattr = $product->getAttr($spu ? $spu : '',isset($condition['lang'])?$condition['lang']:'',isset($condition['attr_type'])?$condition['attr_type']:'',isset($condition['status'])?$condition['status']:'');

            $data = $attrs = array();
            $attrs = array_merge($result,$pattr);
            if($attrs){
                foreach($attrs as $item){
                    $group1 = '';
                    if ($item['goods_flag'] == 'Y') {
                        $group1 = 'goods_flag';
                        $data[$item['lang']][$group1][] = $item;
                    }
                    if ($item['logi_flag'] == 'Y') {
                        $group1 = 'logi_flag';
                        $data[$item['lang']][$group1][] = $item;
                    }
                    if ($item['hs_flag'] == 'Y') {
                        $group1 = 'hs_flag';
                        $data[$item['lang']][$group1][] = $item;
                    }
                    if ($item['spec_flag'] == 'Y') {
                        $group1 = 'spec_flag';
                        $data[$item['lang']][$group1][] = $item;
                    }
                    if ($group1 == '') {
                        $group1 = 'others';
                        $data[$item['lang']][$group1][] = $item;
                    }
                }
            }
            redisHashSet('Attr',md5(json_encode($where)),json_encode($data));
            return $data;
        }catch (Exception $e){
            return array();
        }
    }

}
