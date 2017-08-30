<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of NewAttr
 * @author  zhongyg
 * @date    2017-8-30 16:41:10
 * @version V2.0
 * @desc
 */
class NewAttrModel extends PublicModel {

    protected $dbName = 'erui_goods'; //数据库名称
    protected $tableName = 'goods_attr'; //数据表表名

    //状态

    const STATUS_VALID = 'VALID'; //有效
    const STATUS_INVALID = 'INVALID'; //无效；
    const STATUS_DELETED = 'DELETED'; //删除；

    protected $tablePrefix = '';

}
