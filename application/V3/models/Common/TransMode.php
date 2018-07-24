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
class Common_TransModeModel extends PublicModel {

    //put your code here
    protected $dbName = 'erui_dict';
    protected $tableName = 'trans_mode';

    public function __construct() {
        parent::__construct();
    }

    /*
     * Description of 获取创建人姓名
     * @param array $arr
     * @author  zhongyg
     * @date    2017-8-2 13:07:21
     * @version V2.0
     * @desc
     */

    public function setTransModeName(&$arr) {
        if ($arr) {

            $trans_mode_bns = [];
            foreach ($arr as $key => $val) {
                if (isset($val['trans_mode_bn']) && $val['trans_mode_bn']) {
                    $trans_mode_bns[] = $val['trans_mode_bn'];
                }
            }
            $trans_mode_names = [];
            if ($trans_mode_bns) {
                $trans_modes = $this->where(['bn' => ['in', $trans_mode_bns], 'lang' => $this->lang, 'deleted_flag' => 'N'])
                                ->field('bn,trans_mode')->select();
                foreach ($trans_modes as $trans_mode) {
                    $trans_mode_names[$trans_mode['bn']] = $trans_mode['trans_mode'];
                }
            }
            foreach ($arr as $key => $val) {
                if ($val['trans_mode_bn'] && isset($trans_mode_names[$val['trans_mode_bn']])) {
                    $val['trans_mode_name'] = $trans_mode_names[$val['trans_mode_bn']];
                } else {
                    $val['trans_mode_name'] = '';
                }
                $arr[$key] = $val;
            }
        }
    }

}
