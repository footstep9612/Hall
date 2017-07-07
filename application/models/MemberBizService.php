<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author klp
 */
class MemberBizServiceModel extends PublicModel {

    //put your code here
    protected $dbName='erui_config';
    protected $tableName = 'member_biz_service';
    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 获取等级分组列表
     * @param data $data;
     * @return array
     * @author klp
     */
    public function getVipService($info,$data)
    {
        $lang = $info['lang'] ? strtolower($info['lang']) : (browser_lang() ? browser_lang() : 'en');

        if(redisHashExist('services',md5(json_encode($lang)))){
            $result = json_decode(redisHashGet('services',md5(json_encode($lang))),true);
            return $result ? $result : array();
        }
        //查找会员等级
        if(empty($data['customer_id'])){
            jsonReturn('','-1001','[customer_id不能为空');
        }
        $condition = array(
            'customer_id'=> $data['customer_id'],
        );
        $levelMode = new BuyerModel();
        $buyer_level = $levelMode->field('buyer_level')->where($condition)->find();
            //通过buyer_level查找biz_service_bn
            $biz_service_bn = $this->field('buyer_level,biz_service_bn')->select();

            //按等级分组
            /**
             * Ordinary - 普通会员
             * Diamond - 钻石会员
             * Silver - 银牌会员
             * Gold - 金牌会员
             */
            $level = array();
            foreach ($biz_service_bn as $value) {
                    $level[$value['buyer_level']][] = $value;
            }
            $data = array();
            $bizService = new BizServiceModel();
            foreach ($level as $vals) {
                foreach ($vals as $v) {
                    $info = $bizService->field('major_class,minor_class,service_name')->where(array('service_code' => $v['biz_service_bn'], 'lang' => $lang))->find();
                    if(empty($info)) continue;
                    $data[$v['buyer_level']][] = $info;
                }
            }
            //按类型分组
            if ($data) {
                //按服务树形结构
                /**
                 * Financial Service -金融服务
                 * Logistics Service -物流服务
                 * Quality Assurance -品质保障
                 * Steward Service -管家服务
                 * Other - 其他服务
                 */
                $service = array();
                foreach ($data as $key => $item) {
                    foreach($item as $v){
                        $service[$key][$v['major_class']][] = $v;
                    }
                }
                $service['buyer_level'] = $buyer_level['buyer_level'];
                redisHashSet('services', md5(json_encode($lang)), json_encode($service));
                if ($service) {
                    return $service;
                } else {
                    return false;
                }
            } else {
                return false;
            }

    }

    /**
     * 获取个人等级信息
     * @param data $data;
     * @return array
     * @author klp
     */
    public function getService($buyerLevel,$lang)
    {
        $where = array();
        if(!empty($buyerLevel)){
            $where['buyer_level'] = ucwords($buyerLevel['buyer_level']);
        }
        if(redisHashExist('service',md5(json_encode($where)))){
            $result = json_decode(redisHashGet('service',md5(json_encode($where))),true);
            return $result ? $result : array();
        }
            //通过buyer_level查找biz_service_bn
            $biz_service_bn = $this->field('biz_service_bn')->where($where)->select();
            $data = array();
            $bizService = new BizServiceModel();
            foreach ($biz_service_bn as $vals) {
                $info = $bizService->field('major_class,minor_class,service_name')->where(array('service_code' => $vals['biz_service_bn'], 'lang' => $lang))->find();
                if(empty($info)) continue;
                $data[] = $info;
            }
            //按类型分组
            if ($data) {
                //按服务树形结构
                /**
                 * Financial Service -金融服务
                 * Logistics Service -物流服务
                 * Quality  Assurance-品质保障
                 * Steward Service -管家服务
                 */
                $service = array();
                foreach ($data as $key => $item) {
                    $service[$item['major_class']][] = $item;
                }

                redisHashSet('service',md5(json_encode($where)),json_encode($service));
                if ($service) {
                    return $service;
                } else {
                    return false;
                }
            } else {
                return false;
            }
    }
}