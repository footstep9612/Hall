<?php

/**
 * Class TemporarySupplier
 * @desc 临时供应API
 */
class TemporarySupplierController extends PublicController
{

    private $temporarySupplier;

    public function init()
    {
        //parent::init();
        $this->temporarySupplier = new TemporarySupplierModel();
    }

    public function listAction()
    {

        $request = $this->validateRequestParams();
        $list = $this->temporarySupplier->getList($request);

        $this->jsonReturn([
            'code' => 1,
            'message' => '成功',
            'data' => [
                'data' => $list,
                'total' => $this->temporarySupplier->getCount($request),
                'relations_count' => $this->temporarySupplier->getCount(['is_relation' => 'Y', 'deleted_flag' => 'N'], false),
                'un_relations_count' => $this->temporarySupplier->getCount(['is_relation' => 'N', 'deleted_flag' => 'N'], false),
            ]
        ]);

    }

    public function detailAction()
    {
        
    }

    public function relationAction()
    {

        //code
    }
}