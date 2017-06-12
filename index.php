<?php

$json = '{
"code":"0",
"message":"",
"data":{
        "code":"010203",
        "level":"2",
        "sort_order":"3",
        "en":{
            "name":"一级分类",
        },
        "zh":{
            "name":"一级分类",
        },
        "es":{
            "name":"一级分类",
        },
        "ru":{
            "name":"一级分类",
        },
        "parent_code":"0102"
    }
}';
var_dump(json_decode($json));
die();
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Access-Control-Allow-Methods:POST');

/* INI配置文件支持常量替换 */
define("APPLICATION_PATH", dirname(__FILE__) . "/application");
define("MYPATH", dirname(__FILE__));
/**
 * 默认的, Yaf_Application将会读取配置文件中在php.ini中设置的ap.environ的配置节
 * 另外在配置文件中, 可以替换PHP的常量, 比如此处的APPLICATION_PATH
 */
$application = new Yaf_Application("conf/application.ini");
define('DEBUG_MODE', true);
/* 如果打开flushIstantly, 则视图渲染结果会直接发送给请求端
 * 而不会写入Response对象
 */
//$application->getDispatcher()->flushInstantly(TRUE);

/* 如果没有关闭自动response(通过Yaf_Dispatcher::getInstance()->returnResponse(TRUE)),
 * 则$response会被自动输出, 此处也不需要再次输出Response
 */

error_reporting(E_ALL & E_STRICT);
$response = $application
        ->bootstrap()/* bootstrap是可选的调用 */
        ->run()/* 执行 */;
?>
