<?php

/**
 * 用户model
 */
class UsermainModel extends ZysModel {

    private $g_table = 'user_main';

    public function __construct() {
        parent::__construct($this->g_table);
    }

    /**
     * 创建用户
     */
    public function UserCreate($data) {
        $id = $this->add($data);
        if ($id) {
            return $id;
        } else {
            return false;
        }
    }

    /*
      用户资料
      @param	$option	array	列表where条件
      @return array
     */

    public function Userinfo($field, $option) {

        return $this->field($field)->where($option)->find();
    }

}
