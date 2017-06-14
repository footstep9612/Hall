<?php

/**
  附件文档Controller
 */
class MaterialcatController extends Yaf_Controller_Abstract {

    public function init() {
        //  parent::init();
        $this->_model = new MaterialcatModel();
    }

    public function listAction() {
        $jsondata = json_decode(file_get_contents("php://input"), true);
        $condition['level_no'] = 0;
        $arr = $this->_model->getlist($jsondata);
        if ($arr) {
            $data['code'] = 0;
            $data['message'] = '获取成功!';
            foreach ($arr as $key => $val) {

                $arr[$key]['child'] = $this->_model->getlist(['parent_cat_no' => $val['cat_no'], 'level' => 1]);
                if ($arr[$key]['child']) {
                    foreach ($arr[$key]['child'] as $k => $item) {
                        $arr[$key]['child'][$k]['child'] = $this->_model->getlist(['parent_cat_no' => $item['cat_no'], 'level' => 2]);
                    }
                }
            }

            $data['data'] = $arr;
        } else {
            $condition['level_no'] = 1;
            $arr = $this->_model->getlist($jsondata);
            if ($arr) {
                $data['code'] = 0;
                $data['message'] = '获取成功!';


                foreach ($arr[$key]['child'] as $k => $item) {

                    $arr[$key]['child'][$k]['child'] = $this->_model->getlist(['parent_cat_no' => $item['cat_no'], 'level' => 2]);
                }
                $data['data'] = $arr;
            } else {

                $condition['level_no'] = 2;
                $arr = $this->_model->getlist($jsondata);
                if ($arr) {
                    $data['code'] = 0;
                    $data['message'] = '获取成功!';
                    $data['data'] = $arr;
                } else {
                    $data['code'] = -1;
                    $data['message'] = '数据为空!';
                }
            }
        }
        $this->jsonReturn($data);
    }

    public function getlistAction() {
        $jaondata = json_decode(file_get_contents("php://input"), true);
        $arr = $this->_model->get_list($jaondata['cat_no'], $jaondata['lang']);
        if ($arr) {
            $data['code'] = 0;
            $data['message'] = '获取成功!';

            $data['data'] = $arr;
        } else {
            $data['code'] = -1;
            $data['message'] = '数据为空!';
        }
        $this->jsonReturn($data);
    }

    public function infoAction() {
        $jaondata = json_decode(file_get_contents("php://input"), true);
        $jaondata['id'] = 1;
        $arr = $this->_model->info($jaondata['id']);
        if ($arr) {
            $data['code'] = 0;
            $data['message'] = '获取成功!';

            $data['data'] = $arr;
        } else {
            $data['code'] = -1;
            $data['message'] = '数据为空!';
        }
        $this->jsonReturn($data);
    }

    public function createAction() {

        $data = $this->_model->create_data($this->put_data, $this->user['username']);
        $this->jsonReturn($data);
    }

    public function updateAction() {

        $data = $this->_model->update_data($this->put_data, $this->user['username']);
        $this->jsonReturn($data);
    }

    public function deleteAction() {

        $data = $this->_model->delete_data($this->put_data['id']);
        $this->jsonReturn($data);
    }

    public function approvingAction() {

        $data = $this->_model->approving($this->put_data['id']);
        $this->jsonReturn($data);
    }

    protected function jsonReturn($data, $type = 'JSON') {


        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }

}
