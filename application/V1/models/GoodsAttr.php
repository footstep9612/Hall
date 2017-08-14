<?php

/**
 * Class GoodsAttrModel
 *  @author  klp
 */

class GoodsAttrModel extends PublicModel{

    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'goods_attr'; //数据表表名

    //状态
    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETED = 'DELETED'; //删除；
    const STATUS_CHECKING = 'CHECKING'; //审核；

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
        if(redisHashExist('spec','spec_'.$sku.'_'.$lang)){
            //return json_decode(redisHashGet('spec', 'spec_'.$sku.'_'.$lang),true);
        }

        $field = 'attr_no,attr_name,attr_value_type,attr_value,value_unit';
        $condition = array(
            'sku' => $sku,
            'lang' => $lang,
            'goods_flag' => 'Y',
            'status' => self::STATUS_VALID
        );
        try{
            $result = $this->field($field)->where($condition)->select();
            if($result){
                redisHashSet('spec','spec_'.$sku.'_'.$lang, json_encode($result));
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
        if(!isset($condition['sku'])) {
            return array();
        }
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
            $field = 'id,lang,attr_no,attr_name,attr_value_type,attr_value,value_unit,attr_group,sort_order,goods_flag,logi_flag,hs_flag,spec_flag,status';
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
                    $item['flag'] = true;
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

    /**
     * sku属性新增（门户后台）
     * @author klp
     * @return bool
     */
    public function createAttrSku($data){
        $arr = $this->check_data($data);
        $res = $this->addAll($arr);
        if($res){
            return true;
        } else{
            return false;
        }
    }
    /**
     * sku属性更新（门户后台）
     * @author klp
     * @return bool
     */
    public function updateAttrSku($data){
        $condition = $this->check_up($data);
        if($condition){
            try{
                foreach($condition as $v){
                    $this->where(array('sku'=>$v['sku'],'lang'=>$v['lang']))->save($v);
                }
                return true;
            } catch(\Kafka\Exception $e){
                return false;
            }
        } else{
            return false;
        }
    }



    /**
     * sku属性删除[真实]（门户后台）
     * @author klp
     * @return bool
     */
    public function deleteRealAttr($delData){
        if(empty($delData)) {
            return false;
        }
        $this->startTrans();
        try{
            foreach($delData as $del){
                $where = [
                    "sku" => $del['sku'],
                    "lang" => $del['lang']
                ];
                $this->where($where)->save(['status' => self::STATUS_DELETED]);
            }
            $this->commit();
            return true;
        } catch(Exception $e){
            $this->rollback();
            return false;
        }

    }

    /**
     * sku属性参数处理（门户后台）
     * @author klp
     * @return array
     */
    public function check_data($data=[]){
        if(empty($data))
            return false;
        $condition['lang'] = isset($data['lang']) ? $data['lang']: 'en';
        $condition['required_flag'] = isset($data['required_flag']) ? $data['required_flag']: 'N';
        $condition['search_flag'] = isset($data['search_flag']) ? $data['search_flag']: 'Y';
        $condition['attr_group'] = isset($data['attr_group']) ? $data['attr_group']: '';
        $condition['sort_order'] = isset($data['sort_order']) ? $data['sort_order']: 1;
        $condition['created_at'] = isset($data['created_at']) ? $data['created_at']: date('Y-m-d H:i:s');
        if (isset($data['created_by'])) {$condition['created_by'] = $data['created_by'];}
        if (isset($data['sku'])) {
            $condition['sku'] = $data['sku'];
        } else {
            JsonReturn('','-1002','sku编号不能为空');
        }
        if (isset($data['attr_no'])) {
            $condition['attr_no'] = $data['attr_no'];
        } else {
            JsonReturn('','-1003','属性编码不能为空');
        }
        if(isset($data['status'])){
            switch ($data['status']) {
                case self::STATUS_VALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_INVALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_CHECKING:
                    $condition['status'] = $data['status'];
                    break;
            }
        } else {
            $condition['status'] = self::STATUS_VALID;
        }
        //属性组处理
        $attrs = array();
        foreach ($data['attrs'] as $k=>$v) {
            if(isset($v['goods_flag']) && 'Y' == $v['goods_flag']) {
                $condition['goods_flag'] = 'Y';
                $condition['spec_flag'] = 'N';
                $condition['logi_flag'] = 'N';
                $condition['hs_flag'] = 'N';
            }
            elseif(isset($v['spec_flag']) && 'Y' == $v['spec_flag']) {
                $condition['spec_flag'] = 'Y';
                $condition['goods_flag'] = 'N';
                $condition['logi_flag'] = 'N';
                $condition['hs_flag'] = 'N';
            }
            elseif(isset($v['logi_flag']) && 'Y' == $v['logi_flag']) {
                $condition['logi_flag'] = 'Y';
                $condition['spec_flag'] = 'N';
                $condition['goods_flag'] = 'N';
                $condition['hs_flag'] = 'N';
            }
            elseif(isset($v['hs_flag']) && 'Y' == $v['hs_flag']) {
                $condition['hs_flag'] = 'Y';
                $condition['spec_flag'] = 'N';
                $condition['logi_flag'] = 'N';
                $condition['goods_flag'] = 'N';
            }
            $condition['attr_name'] =$v['attr_name'];
            $condition['attr_value'] = isset($v['attr_value']) ? $v['attr_value']: '';
            $condition['attr_value_type'] = isset($v['attr_value_type']) ? $v['attr_value_type']: 'String';
            $condition['value_unit'] = isset($v['value_unit']) ? $v['value_unit']: ' ';
            $attrs[] = $condition;

        }
        return $attrs;
    }

    /**
     * sku属性更新参数处理（门户后台）
     * @author klp
     * @return bool
     */
    public function check_up($data){
        if(empty($data))
            return false;

        $condition = [];
        if (isset($data['sku'])) {
            $condition['sku'] = $data['sku'];
        } else {
            JsonReturn('','-1001','sku编号不能为空');
        }
        if (isset($data['lang'])) {
            $condition['lang'] = $data['lang'];
        } else {
            JsonReturn('','-1002','lang不能为空');
        }
        if (isset($data['spu'])) {$condition['spu'] = $data['spu'];}
        if (isset($data['attr_no'])) {$condition['attr_no'] = $data['attr_no'];}
        if (isset($data['value_unit'])) {$condition['value_unit'] = $data['value_unit'];}
        if (isset($data['required_flag'])) {$condition['required_flag'] = $data['required_flag'];}
        if (isset($data['search_flag'])) {$condition['search_flag'] = $data['search_flag'];}
        if (isset($data['attr_group'])) {$condition['attr_group'] = $data['attr_group'];}
        if (isset($data['sort_order'])) {$condition['sort_order'] = $data['sort_order'];}
//        if (isset($data['updated_by'])) {$condition['updated_by'] = $data['updated_by'];}
        if (isset($data['status'])) {
            switch ($data['status']) {
                case self::STATUS_VALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_INVALID:
                    $condition['status'] = $data['status'];
                    break;
                case self::STATUS_CHECKING:
                    $condition['status'] = $data['status'];
                    break;
            }
        }
        //属性组处理
        $attrs = array();
        foreach ($data['attrs'] as $k=>$v) {
            $condition['id'] = $v['id'];
            if (isset($v['attr_name'])) {$condition['attr_name'] = $v['attr_name'];}
            if (isset($v['attr_value'])) {$condition['attr_value'] = $v['attr_value'];}
            $attrs[] = $condition;
        }
        return $attrs;
    }


}
