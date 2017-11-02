#!/usr/local/php/bin/php -f
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
$environments = ['pro', 'beta', 'dev'];
$application_path = APPLICATION_PATH . DS . 'conf' . DS . 'application.ini';
foreach ($environments as $environment) {
    if (file_exists('/var/conf/' . $environment)) {
        $application_path = APPLICATION_PATH . DS . 'conf' . DS . 'application_' . $environment . '.ini';
        break;
    }
}
$application = new Yaf_Application($application_path);
$response = $application
        ->bootstrap()/* bootstrap是可选的调用 */
        ->run()/* 执行 */;
?>
