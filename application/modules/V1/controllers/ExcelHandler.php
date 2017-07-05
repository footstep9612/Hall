<?php

 class ExcelHandlerController extends PublicController
 {
     /**
      * 导入询价单接口
      */
     public function importInquryAction()
     {
        $response = [
            'code'=>1,
            'message'=>'成功',
            'data'=>[
                'imported_at'=>date('Y-m-d H:i:s')
            ]
        ];
        $this->jsonReturn($response,"JSON");
     }
 }