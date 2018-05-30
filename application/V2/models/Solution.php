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
    protected $tableName = 'sol_content';
    protected $dbName = 'erui_cms';

    public function __construct() {
        parent::__construct();
    }

    public function UpdateData($id, $thumb) {

        return $this->where(['id' => $id])->save(['thumb' => $thumb]);
    }

}
