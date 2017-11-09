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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($postData)
            )
        );
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        var_dump(curl_errno($ch));
        $output = curl_exec( $ch );
        var_dump($output);
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