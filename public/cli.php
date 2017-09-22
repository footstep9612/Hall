#!/usr/bin/php/bin/php -q
<?php
error_reporting(E_ERROR);
define('DS', DIRECTORY_SEPARATOR);
if (PHP_SAPI !== 'cli') {
    die('{"code":"-1","message":"系统错误!"}');
}
/* INI配置文件支持常量替换 */
define('MYPATH', dirname(dirname(__FILE__)));
$uri = $argv [1];
preg_match('/\/([a-zA-Z0-9\.]+)\/([a-zA-Z0-9\_\-]+)([\/|\?].*?)?$/ie', $uri, $out);
$module = ucfirst($out[1]);
if (file_exists(MYPATH . DS . 'application' . DS . $module)) {
    define('APPLICATION_PATH', MYPATH . DS . 'application' . DS . $module);
    define('CONF_PATH', MYPATH . DS . 'application' . DS . $module . DS . 'conf');
} else {
    die('{"code":"-1","message":"系统错误!"}');
}
define('COMMON_PATH', MYPATH . DS . 'common');

if (file_exists(APPLICATION_PATH) && file_exists(APPLICATION_PATH . DS . 'conf' . DS . 'application.ini')) {
    $application = new Yaf_Application(APPLICATION_PATH . DS . 'conf' . DS . 'application.ini');
} else {
    die('{"code":"-1","message":"系统错误!"}');
}


$response = $application
        ->bootstrap()/* bootstrap是可选的调用 */
        ->run()/* 执行 */;
?>
