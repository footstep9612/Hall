<?php
//客户档案信息管理-wangs
class BuyerFileController extends PublicController
{
    public function __init()
    {
        parent::__init();
    }
    /*
     * 客户管理列表
     * */
    public function buyerListAction()
    {
        $created_by = '39305';
//        $created_by = $this -> user['id'];
        $data = json_decode(file_get_contents("php://input"), true);
        $data['created_by'] = $created_by;
        $model = new BuyerBusinessModel();
        $res = $model->buyerList($data);
        if(!$res){
            echo json_encode(array("code" => "0", "data" => $res, "message" => "空数据"));
            exit();
        }
        echo json_encode(array("code" => "1", "data" => $res, "message" => "返回数据"));
    }
}