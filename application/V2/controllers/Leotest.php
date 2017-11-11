<?php
/**
 * Created by PhpStorm.
 * User: zhanguliang
 * Date: 2017/6/23
 * Time: 9:11
 */

class LeotestController extends Yaf_Controller_Abstract {

    public function __init() {
        parent::__init();
    }

    public function indexAction() {
        //var_dump(md5('leo@123456'));die;
        $data = [
            /* V1 */
            //"token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjMyMCIsImV4dCI6MTUwMjUxMDE1MywiaWF0IjoxNTAyNTEwMTUzLCJuYW1lIjoiXHU1ZjkwXHU1ZmQ3XHU5NmM0In0.tdwDrpV3OmoUf9KrmLiULkSeozshqyF9ApiHejzjJAw",
            /* V2 dev */
            //刘晖
            //"user_name"=>"012926",
            //"password"=>"123456",
            //"token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjMzNjA0IiwiZXh0IjoxNTAzOTg3NDQyLCJpYXQiOjE1MDM5ODc0NDIsIm5hbWUiOiJcdTUyMThcdTY2NTYifQ.3QVlLAj43f4fX7FRZ1uUnNfrk6uvM-ogdznhGycEzs0",
            //徐志雄
            //"status"=>"BIZ_DISPATCHING",
            //"org_id"=>"9732",
            //"role_no"=>"A005",
            //"token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjM4Njk3IiwiZXh0IjoxNTA5MjQ1NTY2LCJpYXQiOjE1MDkyNDU1NjYsIm5hbWUiOiJcdTVmOTBcdTVmZDdcdTk2YzQifQ.m4Pc1qavgtOsq5KdgDNNopCFvs2XnrVJHt6vVYtTdF8",
            //张立才
            "token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjM3NzE5IiwiZXh0IjoxNTA5NzAwMzAzLCJpYXQiOjE1MDk3MDAzMDMsIm5hbWUiOiJcdTVmMjBcdTdhY2JcdTYyNGQifQ.1v0ATLw2yExYlmkrvXPcuXZ_54GlQfuTm9dwawUeBUU",
            //张玉良
            //"token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjM4ODA4IiwiZXh0IjoxNTA5NDE3NzM4LCJpYXQiOjE1MDk0MTc3MzgsIm5hbWUiOiJcdTVmMjBcdTczODlcdTgyNmYifQ.fNW4Jnwkd9wgbS3V5sHYH5Pg44E7Uv2l1Kj8I3L2Tfc",
            //"list_type"=>"logi",
            /* V2 local */
            //"token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjM2MCIsImV4dCI6MTUwMzkxMzQ5NSwiaWF0IjoxNTAzOTEzNDk1LCJuYW1lIjoiXHU1ZjIwXHU0ZWMxIn0.6SVZQaidUiF32Ayj9zqoycU0X0w9s4LRPvOAGqOaOq8",

            //"group_id"=>"1,2,3,6,9,10,11,12,13,63,64,68,85,89,9703,9722,4,5,24,26,27,28,29,30,31,32,33,34,35,36,37,38,8140,9700,9710,9711,9719,9720",
           /* "quote_id"=>"309",
            "quote_bizline_id"=>"321",
            "bizline_id"=>"1",*/
            /*"action"=>"APPROVING",
	        "op_result"=>"APPROVED",
            "op_note"=>"test",*/
            //"username"=>"徐婕",
	        //"org_id"=>"9732",
	        "sku"=>"3503010002810002",

            /* 订单工作流参数 */
            //'id' => "247",    //ID
            //'buyer_id' => "135",    //客户ID
            //'log_id' => "6",  //上级工作流ID
            //"log_group"=>"CREDIT",   //工作流分组 OUTBOUND-出库；LOGISTICS-物流；DELIVERY-交收；COLLECTION-收款；CREDIT-授信
	        //"order_id"=>"53", //订单ID
	        //"log_at"=>"2017-09-15",   //工作流分组
            //"content"=>"测试出库流程数据1",  //内容
            //"out_no"=>"out_20170913_00002" ,   //出库单号
            //"waybill_no"=>"yun_20170913_00002",   //运单号
	        //"amount"=>"200",   //金额
	        //"type"=>"REFUND",    //类型 SPENDING-支出；REFUND-还款
            //"order_address_id"=>"",    //订单地址ID
            /*"attach_array"=>[
                //["attach_url" => '测试附件地址4',"attach_name" => '测试附件名称4'],
                //["attach_url" => '测试附件地址5',"attach_name" => '测试附件名称5'],
            ],   //附件数组*/

        ];
        $re = $this->post($data, '/V2/Supplier/getSkuSupplierList');
        //var_dump($re);die;
        $results = json_decode($re,true);
        var_dump($results);
    }

    function post($data, $action, $ContentType = 'json', $timeout = 30) {

        $url = 'http://172.18.18.196:9090' . $action;
        //$url = 'boss2.erui.demo' . $action;
        if ($ContentType == "json") {
            $header = array(
                'Content-type: application/json;charset=UTF-8',
                'Accept: */*',
                'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
                'Connection: Keep-Alive',
            );
            $formdata = json_encode($data);
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $formdata);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int) $timeout);

        $response = curl_exec($ch);


        if (curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }
}