<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author jhw
 * @desc 运输方式
 */
class TransModeModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_dict';
    protected $tableName = 'trans_mode';

    public function __construct($str = '') {
        parent::__construct($str = '');
    }

    /**
     * 获取列表
     * @param data $data;
     * @return array
     * @author jhw
     */
    public function getlist($data, $limit, $order = 'id desc') {
        if (!empty($limit)) {
            return $this->field('lang,bn,trans_mode,id')
                            ->where($data)
                            ->limit($limit['page'] . ',' . $limit['num'])
                            ->order($order)
                            ->select();
        } else {
            return $this->field('lang,bn,trans_mode,id')
                            ->where($data)
                            ->order($order)
                            ->select();
        }
    }

    /**
     * 根据简称与语言获取国家名称
     * @param array $bn 简称
     * @param string $lang 语言
     * @param string
     */
    public function getTransModeByBns($bns = [], $lang = '') {
        if (empty($bns) || empty($lang))
            return [];

        if (redisHashExist('trans_mode', implode('_', $bns) . '_' . $lang)) {
            return json_decode(redisHashGet('trans_mode', implode('_', $bns) . '_' . $lang), true);
        }
        try {
            $condition = array(
                'bn' => ['in', $bns],
                'lang' => $lang,
                    // 'status'=>self::STATUS_VALID
            );
            $field = 'bn,trans_mode';
            $data = $this->field($field)->where($condition)->select();
            $result = [];

            if ($data) {
                foreach ($data as $item) {
                    $result[$item['bn']] = $item['trans_mode'];
                }
            }
            if ($result) {
                redisHashSet('trans_mode', implode('_', $bns) . '_' . $lang, json_encode($result));
            }
            return $result;
        } catch (Exception $e) {
            return [];
        }
    }

}
