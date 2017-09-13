<?php
/**
 * name: Workflow.php
 * desc: 订单流程控制器
 * User: 张玉良
 * Date: 2017/9/12
 * Time: 17:10
 */
class WorkflowController extends PublicController{
    public function init() {
        parent::init();
    }

    /*
     * 流程列表
     * Author:张玉良
     */

    public function getListAction() {
        $workflow = new WorkFlowModel();
        $where = $this->put_data;

        $results = $workflow->getList($where);

        $this->jsonReturn($results);
    }

    /*
     * 询价单详情
     * Author:张玉良
     */

    public function getInfoAction() {
        $workflow = new WorkFlowModel();
        $where = $this->put_data;

        $results = $workflow->getInfo($where);

        $this->jsonReturn($results);
    }

    /*
     * 添加询价单
     * Author:张玉良
     */

    public function addAction() {
        $workflow = new WorkFlowModel();

        $data = $this->put_data;
        $data['created_by'] = $this->user['id'];

        $results = $workflow->addData($data);

        $this->jsonReturn($results);
    }

    /*
     * 修改询价单
     * Author:张玉良
     */

    public function updateAction() {
        $workflow = new WorkFlowModel();

        $data = $this->put_data;

        $results = $workflow->updateData($data);
        $this->jsonReturn($results);
    }

    /*
     * 删除询价单
     * Author:张玉良
     */

    public function deleteAction() {
        $workflow = new WorkFlowModel();
        $where = $this->put_data;

        $results = $workflow->deleteData($where);
        $this->jsonReturn($results);
    }
}