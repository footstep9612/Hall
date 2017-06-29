<?php

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Access-Control-Allow-Methods:POST,PUT,GET');

/* INI配置文件支持常量替换 */
define("APPLICATION_PATH", dirname(__FILE__) . "/application");
define("MYPATH", dirname(__FILE__));
/**
 * 默认的, Yaf_Application将会读取配置文件中在php.ini中设置的ap.environ的配置节
 * 另外在配置文件中, 可以替换PHP的常量, 比如此处的APPLICATION_PATH
 */
$application = new Yaf_Application("conf/application.ini");
Yaf_Dispatcher::getInstance()->catchException(TRUE);
/* 如果打开flushIstantly, 则视图渲染结果会直接发送给请求端
 * 而不会写入Response对象
 */
//$application->getDispatcher()->dispatch(new Yaf_Request_Simple());

/* 如果没有关闭自动response(通过Yaf_Dispatcher::getInstance()->returnResponse(TRUE)),
 * 则$response会被自动输出, 此处也不需要再次输出Response
 */

error_reporting(E_ERROR & E_STRICT);

$response = $application
        ->bootstrap()/* bootstrap是可选的调用 */
        ->run()/* 执行 */;
?>
