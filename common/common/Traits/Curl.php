<?php

Trait Curl
{

    /**
     * 请求服务器
     * @param $url 请求地址
     * @author 买买提
     * @return string
     */
    public static function get( $url )
    {

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );

        if ( ! curl_exec( $ch ) ) {
            $data = '';
        } else {
            $data = curl_multi_getcontent( $ch );
        }
        curl_close( $ch );
        return $data;

    }

    /**
     * 提交POST数据
     * @param $url 提交地址
     * @param $postData 提交数据
     * @author 买买提
     * @return string
     */
    public static function post( $url, $postData ) {

        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData );

        if ( ! curl_exec( $ch ) ) {
            $data = '';
        } else {
            $data = curl_multi_getcontent( $ch );
        }
        curl_close( $ch );
        return $data;

    }

    /**
     * 提交JSON数据
     * @param $url 提交地址
     * @param $postData 提交数据
     * @author 买买提
     * @return string
     */
    public static function postJson( $url, $postData ) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData))
        );
        var_dump(curl_error($ch));
        if ( ! curl_exec( $ch ) ) {
            $data = 'curl not response';
        } else {
            $data = curl_multi_getcontent( $ch );
        }
        curl_close( $ch );
        p($data);
        return $data;

    }
}