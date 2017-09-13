<?php
/**
 * name: OrderLog.php
 * desc: 订单流程控制器
 * User: 张玉良
 * Date: 2017/9/12
 * Time: 17:10
 */
class OrderlogController extends Yaf_Controller_Abstract{
    public function init() {
        //parent::init();
    }

    /*
     * 流程列表
     * Author:张玉良
     */

    public function getListAction() {
        $OrderLog = new OrderLogModel();
        $where = json_decode(file_get_contents("php://input"), true);//$this->put_data;

        $results = $OrderLog->getList($where);
        var_dump($results);die;
        $this->jsonReturn($results);
    }

    /*
     * 询价单详情
     * Author:张玉良
     */

    public function getInfoAction() {
        $OrderLog = new OrderLogModel();
        $orderattach = new OrderAttachModel();
        $where = json_decode(file_get_contents("php://input"), true);//$this->put_data;

        $results = $OrderLog->getInfo($where);

        if($results['code'] == 1) {
            //查找有没有附件
            $attachwhere['order_id'] = $results['data']['order_id'];
            $attachwhere['attach_group'] = $results['data']['Log_group'];
            $attachwhere['log_id'] = $results['data']['id'];

            $attach = $orderattach->getlist($attachwhere);
            if($attach['code'] == 1) {
                $results['data']['attach_array'] = $attach['data'];
            }

            var_dump($results);die;
            $this->jsonReturn($results);
        }else{

            var_dump($results);die;
            $this->jsonReturn($results);
        }
    }

    /*
     * 添加询价单
     * Author:张玉良
     */

    public function addAction() {
        $OrderLog = new OrderLogModel();

        $data = json_decode(file_get_contents("php://input"), true);//$this->put_data;
        $data['created_by'] = $this->user['id'];

        $OrderLog->startTrans();
        $results = $OrderLog->addData($data);

        if($results['code'] == 1){
            //如果有附件，添加附件
            if(!empty($data['attach_array'])){
                $orderattach = new OrderAttachModel();
                $data['log_id'] = $results['data'];

                $rs = $orderattach->addAllData($data);
                if($rs['code'] == 1){
                    $OrderLog->commit();
                    echo "1";
                    var_dump($results);die;
                    $this->jsonReturn($results);
                }else{
                    $OrderLog->rollback();
                    echo "2";
                    var_dump($rs);die;
                    $this->jsonReturn($rs);
                }
            }else{
                $OrderLog->commit();
                echo "3";
                var_dump($results);die;
                $this->jsonReturn($results);
            }
        }else{
            echo "4";
            var_dump($results);die;
            $this->jsonReturn($results);
        }
    }

    /*
     * 修改询价单
     * Author:张玉良
     */

    public function updateAction() {
        $OrderLog = new OrderLogModel();

        $data = json_decode(file_get_contents("php://input"), true);//$this->put_data;

        $OrderLog->startTrans();
        $results = $OrderLog->updateData($data);

        if($results['code'] == 1){
            //如果有附件，添加附件
            if(!empty($data['attach_array'])){
                $orderattach = new OrderAttachModel();
                $data['log_id'] = $data['id'];
                $data['created_by'] = $this->user['id'];

                $rs = $orderattach->addAllData($data);
                if($rs['code'] == 1){
                    $OrderLog->commit();
                    echo "1";
                    var_dump($results);die;
                    $this->jsonReturn($results);
                }else{
                    $OrderLog->rollback();
                    echo "2";
                    var_dump($rs);die;
                    $this->jsonReturn($rs);
                }
            }else{
                $OrderLog->commit();
                echo "3";
                var_dump($results);die;
                $this->jsonReturn($results);
            }
        }else{
            echo "4";
            var_dump($results);die;
            $this->jsonReturn($results);
        }
    }

    /*
     * 删除询价单
     * Author:张玉良
     */

    public function deleteAction() {
        $OrderLog = new OrderLogModel();
        $where = json_decode(file_get_contents("php://input"), true);//$this->put_data;

        $results = $OrderLog->deleteData($where);
        var_dump($results);die;
        $this->jsonReturn($results);
    }

    /*
     * 删除工作流程附件
     * Author:张玉良
     */
    public function deleteAttachAction() {
        $orderattach = new OrderAttachModel();
        $where = json_decode(file_get_contents("php://input"), true);//$this->put_data;

        $results = $orderattach->deleteData($where);
        var_dump($results);die;
        $this->jsonReturn($results);

    }

    /*
     * 添加发货地址
     * Author:张玉良
     */
    public function addAddressAction() {
        $orderaddress = new OrderAddressModel();
        $where = json_decode(file_get_contents("php://input"), true);//$this->put_data;

        $results = $orderaddress->add($where);
        var_dump($results);die;
        $this->jsonReturn($results);

    }
}