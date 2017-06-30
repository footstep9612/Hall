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
    public function getVipService($data)
    {
        $lang = $data['lang'] ? strtolower($data['lang']) : (browser_lang() ? browser_lang() : 'en');

        /*if(redisHashExist('services',md5(json_encode($lang)))){
            $result = json_decode(redisHashGet('services',md5(json_encode($lang))),true);
            return $result ? $result : array();
        } else {*/
        if(empty($data['email'])){
            jsonReturn('','-1001','email不能为空');
        }
        $condition = array(
            'email'=> $data['email'],
        );
        $buyer_level = $this->field('buyer_level,biz_service_bn')->where($condition)->find();
            //通过buyer_level查找biz_service_bn
            $biz_service_bn = $this->field('buyer_level,biz_service_bn')->select();

            //按等级分组
            /**
             * Ordinary - 普通会员
             * Bronze - 铜牌会员
             * Silver - 银牌会员
             * Gold - 金牌会员
             */
            $level = array();
            foreach ($biz_service_bn as $value) {
                $group1 = 'Other';
                if ($value['buyer_level'] == 'Ordinary') {
                    $group1 = 'Ordinary';
                    $level[$group1][] = $value;
                }
                if ($value['buyer_level'] == 'Bronze') {
                    $group1 = 'Bronze';
                    $level[$group1][] = $value;
                }
                if ($value['buyer_level'] == 'Silver') {
                    $group1 = 'Silver';
                    $level[$group1][] = $value;
                }
                if ($value['buyer_level'] == 'Gold') {
                    $group1 = 'Gold';
                    $level[$group1][] = $value;
                }
            }

            $data = array();
            $bizService = new BizServiceModel();
            foreach ($level as $vals) {
                foreach ($vals as $v) {
                    $info = $bizService->field('major_class,minor_class,service_name')->where(array('service_code' => $v['biz_service_bn'], 'lang' => $lang))->find();
                    $data[$v['buyer_level']][] = $info;
                }
            }// print_r($data);
            //按类型分组
            if ($data) {
                //按服务树形结构
                /**
                 * Financial Service -金融服务
                 * Logistics Service -物流服务
                 * QA -品质保障
                 * Steward Service -管家服务
                 * Other - 其他服务
                 */
                $service = array();
                if ('zh' == $lang) {
                    foreach ($data as $key => $item) {
                        foreach ($item as $r) {
                            $group = 'Other';
                            if ($r['major_class'] == '金融服务') {
                                $group = 'Financial';
                                $service[$key][$group][] = $r;
                            }
                            if ($r['major_class'] == '物流服务') {
                                $group = 'Logistics';
                                $service[$key][$group][] = $r;
                            }
                            if ($r['major_class'] == '品质保障') {
                                $group = 'QA';
                                $service[$key][$group][] = $r;
                            }
                            if ($r['major_class'] == '管家服务') {
                                $group = 'Steward';
                                $service[$key][$group][] = $r;
                            }
                            if ($r['major_class'] == '其他服务') {
                                $group = 'Other';
                                $service[$key][$group][] = $r;
                            }
                        }

                    }
                } elseif ('en' == $lang) {
                    foreach ($data as $key => $item) {
                        foreach ($item as $r) {
                            $group = 'Other';
                            if ($r['major_class'] == 'Financial Service') {
                                $group = 'Financial';
                                $service[$key][$group][] = $r;
                            }
                            if ($r['major_class'] == 'Logistics Service') {
                                $group = 'Logistics';
                                $service[$key][$group][] = $r;
                            }
                            if ($r['major_class'] == 'QA') {
                                $group = 'QA';
                                $service[$key][$group][] = $r;
                            }
                            if ($r['major_class'] == 'Steward Service') {
                                $group = 'Steward';
                                $service[$key][$group][] = $r;
                            }
                            if ($r['major_class'] == 'Other') {
                                $group = 'Other';
                                $service[$key][$group][] = $r;
                            }
                        }
                    }
                }
                $service['buyer_level'] = $buyer_level['buyer_level'];
               // redisHashSet('services', md5(json_encode($lang)), json_encode($service));
                if ($service) {
                    return $service;
                } else {
                    return false;
                }
            } else {
                return false;
            }
       // }
    }

    /**
     * 获取个人等级信息
     * @param data $data;
     * @return array
     * @author klp
     */
    public function getService($buyerLevel,$lang)
    {
        if(!empty($buyerLevel)){
            $where['buyer_level'] = ucfirst($buyerLevel);
        }
        /*if(redisHashExist('service',md5(json_encode($where)))){
            $result = json_decode(redisHashGet('service',md5(json_encode($lang))),true);
            return $result ? $result : array();
        } else {*/
            //通过buyer_level查找biz_service_bn
            $biz_service_bn = $this->field('biz_service_bn')->select();
            $data = array();
            $bizService = new BizServiceModel();
            foreach ($biz_service_bn as $vals) {
                $info = $bizService->field('major_class,minor_class,service_name')->where(array('service_code' => $vals['biz_service_bn'], 'lang' => $lang))->find();
                $data[] = $info;
            }
            //按类型分组
            if ($data) {
                //按服务树形结构
                /**
                 * Financial Service -金融服务
                 * Logistics Service -物流服务
                 * QA -品质保障
                 * Steward Service -管家服务
                 * Other - 其他服务
                 */
                $service = array();
                if ('zh' == $lang) {
                    foreach ($data as $key => $item) {
                        $group = 'Other';
                        if ($item['major_class'] == '金融服务') {
                            $group = 'Financial';
                            $service[$group][] = $item;
                        }
                        if ($item['major_class'] == '物流服务') {
                            $group = 'Logistics';
                            $service[$group][] = $item;
                        }
                        if ($item['major_class'] == '品质保障') {
                            $group = 'QA';
                            $service[$group][] = $item;
                        }
                        if ($item['major_class'] == '管家服务') {
                            $group = 'Steward';
                            $service[$group][] = $item;
                        }
                        if ($item['major_class'] == '其他服务') {
                            $group = 'Other';
                            $service[$group][] = $item;
                        }
                    }
                } elseif ('en' == $lang) {
                    foreach ($data as $key => $item) {
                        $group = 'Other';
                        if ($item['major_class'] == 'Financial Service') {
                            $group = 'Financial';
                            $service[$group][] = $item;
                        }
                        if ($item['major_class'] == 'Logistics Service') {
                            $group = 'Logistics';
                            $service[$group][] = $item;
                        }
                        if ($item['major_class'] == 'QA') {
                            $group = 'QA';
                            $service[$group][] = $item;
                        }
                        if ($item['major_class'] == 'Steward Service') {
                            $group = 'Steward';
                            $service[$group][] = $item;
                        }
                        if ($item['major_class'] == 'Other') {
                            $group = 'Other';
                            $service[$group][] = $item;
                        }
                    }
                }
                //redisHashSet('service',md5(json_encode($where)),json_encode($service));
                if ($service) {
                    return $service;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        //}
    }
}