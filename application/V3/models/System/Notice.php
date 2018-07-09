<?php

class System_NoticeModel extends PublicModel {

    protected $dbName = 'erui_sys';
    protected $tableName = 'notice';

    public function __construct() {
        parent::__construct();
    }

    public function getList($request) {
        $page = !empty($request['currentPage']) ? $request['currentPage'] : 1;
        $pagesize = !empty($request['pageSize']) ? $request['pageSize'] : 10;

        $where = ['deleted_flag' => 'N',];
        if (!empty($request['status'])) {

            $where['status'] = trim($request['status']);
        }
        if (!empty($request['id'])) {

            $where['id'] = trim($request['id']);
        }
        return $this->field('id,title,created_at')
                        ->where($where)
                        ->order('id DESC')
                        ->page($page, $pagesize)
                        ->select();
    }

    public function getCount($request = null) {
        $where = ['deleted_flag' => 'N',];
        if (!empty($request['status'])) {

            $where['status'] = trim($request['status']);
        }
        if (!empty($request['id'])) {

            $where['id'] = trim($request['id']);
        }
        return $this->where($where)->count();
    }

}
