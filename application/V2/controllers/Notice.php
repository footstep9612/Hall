<?php

class NoticeController extends PublicController
{

    protected $notice;

    public function init()
    {
        parent::init();

        $this->notice = new NoticeModel();
    }

    public function listAction()
    {
        $request = $this->validateRequestParams();

        $data = $this->notice->all($request);

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'total' => count($data),
            'data' => $this->setUserName($data)
        ]);
    }

    public function createAction()
    {
        $data = $this->validateRequestParams('title,content');

        $data['created_by'] = $this->user['id'];
        $data['created_at'] = date('Y-m-d H:i:s');

        $response = $this->notice->store($data);
        $this->jsonReturn($response);
    }

    public function showAction()
    {
        $request = $this->validateRequestParams('id');
        $data = $this->notice->byId($request['id']);

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => $data
        ]);
    }

    public function updateAction()
    {
        $data = $this->validateRequestParams('title,content');

        $data['updated_by'] = $this->user['id'];
        $data['updated_at'] = date('Y-m-d H:i:s');

        $response = $response = $this->notice->upStore($data);
        $this->jsonReturn($response);

    }

    public function publishAction()
    {
        $request = $this->validateRequestParams('id');

        $request['updated_by'] = $this->user['id'];
        $request['updated_at'] = date('Y-m-d H:i:s');
        $request['status'] = 'PUBLISH';

        $response = $this->notice->upStore($request);
        $this->jsonReturn($response);
    }

    public function deleteAction()
    {
        $request = $this->validateRequestParams('id');

        $request['updated_by'] = $this->user['id'];
        $request['updated_at'] = date('Y-m-d H:i:s');
        $request['deleted_flag'] = 'Y';

        $response = $this->notice->upStore($request);
        $this->jsonReturn($response);
    }

    private function setUserName($data)
    {
        $employee = new EmployeeModel();
        foreach ($data as $key=>$item) {
            $data[$key]['created_by'] = $employee->where(['id'=> $item['created_by']])->getField('name');
            $data[$key]['updated_by'] = $employee->where(['id'=> $item['updated_by']])->getField('name');
        }

        return $data;
    }
}