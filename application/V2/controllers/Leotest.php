<?php
/**
 * Created by PhpStorm.
 * User: zhanguliang
 * Date: 2017/6/23
 * Time: 9:11
 */

class LeoTestController extends Yaf_Controller_Abstract {

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
            "token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjMzNjA0IiwiZXh0IjoxNTAzOTg3NDQyLCJpYXQiOjE1MDM5ODc0NDIsIm5hbWUiOiJcdTUyMThcdTY2NTYifQ.3QVlLAj43f4fX7FRZ1uUnNfrk6uvM-ogdznhGycEzs0",
            //徐志雄
            //"token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjI4MTg2IiwiZXh0IjoxNTA0NDk0ODQ5LCJpYXQiOjE1MDQ0OTQ4NDksIm5hbWUiOiJcdTlhNmNcdTYyMTBcdTRlNDkifQ.sRXMjXw_zs8AYXWpUoF8t6sMCDtBYcbLvlD9SC3zv5Y",
            //徐婕
            //"token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjI3NDc1IiwiZXh0IjoxNTA0NjYwNDUwLCJpYXQiOjE1MDQ2NjA0NTAsIm5hbWUiOiJcdTRmNTVcdTVjNzEifQ.bPFGHUvDRSFSHjOYukhQsMp_6iniNUENn5fojATH2rg",
            /* V2 local */
            //"token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6IjM2MCIsImV4dCI6MTUwMzkxMzQ5NSwiaWF0IjoxNTAzOTEzNDk1LCJuYW1lIjoiXHU1ZjIwXHU0ZWMxIn0.6SVZQaidUiF32Ayj9zqoycU0X0w9s4LRPvOAGqOaOq8",

            //"group_id"=>"1,2,3,6,9,10,11,12,13,63,64,68,85,89,9703,9722,4,5,24,26,27,28,29,30,31,32,33,34,35,36,37,38,8140,9700,9710,9711,9719,9720",
           /* "quote_id"=>"309",
            "quote_bizline_id"=>"321",
            "bizline_id"=>"1",*/
            /*"action"=>"APPROVING",
	        "op_result"=>"APPROVED",
            "op_note"=>"test",*/
            //"bizline_id"=>"45",
	        /*"group_id"=>"1",
	        "remarks"=>"",*/

            /* 订单工作流参数 */
            //'id' => "53",    //ID
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
        $re = $this->post($data, '/V2/Buyer/info');
        //var_dump($re);die;
        $results = json_decode($re,true);
        var_dump($results);
    }

    function post($data, $action, $ContentType = 'json', $timeout = 30) {

        $url = 'http://172.18.18.196' . $action;
        //$url = 'boss.erui.demo' . $action;
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