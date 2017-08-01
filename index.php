<?php

error_reporting(E_ERROR);
header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Access-Control-Allow-Methods:GET,POST,PUT,DELETE,OPTIONS;');
define('DS', DIRECTORY_SEPARATOR);
/* INI配置文件支持常量替换 */
define('MYPATH', dirname(__FILE__));
$uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_SPECIAL_CHARS);
preg_match('/\/([a-zA-Z0-9\_\-]+)\/([a-zA-Z0-9\_\-]+)([\/.*?]?)$/ie', $uri, $out);
$module = ucfirst($out[1]);
if (file_exists(MYPATH . DS . 'application' . DS . $module)) {
    define('APPLICATION_PATH', MYPATH . DS . 'application' . DS . $module);
} else {
    die('{"code":"-1","message":"系统错误!"}');
}
define('COMMON_PATH', MYPATH . DS . 'common');

/**
 * 默认的, Yaf_Application将会读取配置文件中在php.ini中设置的ap.environ的配置节
 * 另外在配置文件中, 可以替换PHP的常量, 比如此处的APPLICATION_PATH
 */
if (file_exists(MYPATH . DS . $module) && file_exists(MYPATH . DS . 'app' . $module . DS . 'conf' . DS . 'application.ini')) {
    $application = new Yaf_Application(MYPATH . DS . 'app' . $module . DS . 'conf' . DS . 'application.ini');
} else {
    $application = new Yaf_Application(MYPATH . '/conf/application.ini');
}

/* 如果打开flushIstantly, 则视图渲染结果会直接发送给请求端
 * 而不会写入Response对象
 */
//$application->getDispatcher()->flushInstantly(TRUE);
//Yaf_Dispatcher::getInstance()->catchException(TRUE);
/* 如果没有关闭自动response(通过Yaf_Dispatcher::getInstance()->returnResponse(TRUE)),
 * 则$response会被自动输出, 此处也不需要再次输出Response
 */

//error_reporting(E_ALL & E_STRICT);

$response = $application
        ->bootstrap()/* bootstrap是可选的调用 */
        ->run()/* 执行 */;
?>
