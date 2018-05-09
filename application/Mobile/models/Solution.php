<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Solution
 * @author  zhongyg
 * @date    2018-5-9 11:41:42
 * @version V2.0
 * @desc
 */
class SolutionModel extends PublicModel {

    //put your code here
    protected $tableName = 'sol_content_data';
    protected $dbName = 'erui_cms';

    public function __construct() {
        parent::__construct();
    }

    public function GetList($lang = 'en') {
        $where = [];
        if ($lang) {
            $where['template'] = 'show_solution_' . $lang;
        }

        return $this->field('content,goods')->where($where)->select();
    }

}
