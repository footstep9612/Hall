<?php

class NoticeModel extends PublicModel
{

    protected $dbName = 'erui_sys';
    protected $tableName = 'notice';

    public function __construct() {
        parent::__construct();
    }

    public function store($attributes)
    {
        try{
            $result = $this->add($this->create($attributes));
            if ($result) {
                return ['code'=> 1, 'message' => '成功'];
            }
        }catch (Exception $exception){
            return ['code'=> $exception->getCode(), 'message' => $exception->getMessage()];
        }
    }

    public function byId($id)
    {
        return $this->where(['id'=> $id, 'deleted_flag'=> 'N'])->find();
    }

    public function upStore($attributes)
    {
        try{
            $result = $this->save($this->create($attributes));
            if ($result) {
                return ['code'=> 1, 'message' => '成功'];
            }
        }catch (Exception $exception){
            return ['code'=> $exception->getCode(), 'message' => $exception->getMessage()];
        }
    }

    public function all($request)
    {
        $page = !empty($request['currentPage']) ? $request['currentPage'] : 1;
        $pagesize = !empty($request['pageSize']) ? $request['pageSize'] : 10;

        if (!empty($request['status'])) {
            return $this->where(['deleted_flag' => 'N', 'status'=> $request['status']])->order('id DESC')->page($page, $pagesize)->select();
        }

        return $this->where(['deleted_flag' => 'N'])->order('id DESC')->page($page, $pagesize)->select();
    }

    public function counter($request)
    {
        if (!empty($request['status'])) {
            return $this->where(['deleted_flag' => 'N', 'status'=> $request['status']])->count();
        }

        return $this->where(['deleted_flag' => 'N'])->order('id DESC')->count();
    }
}