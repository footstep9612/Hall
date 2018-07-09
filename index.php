<?php

error_reporting(E_ERROR);

$origin = empty($_SERVER['HTTP_ORIGIN']) ? '*' : $_SERVER['HTTP_ORIGIN'];
header('Access-Control-Allow-Origin:' . $origin);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers:x-requested-with,content-type,token,lang');
header('Access-Control-Allow-Methods:GET,POST,PUT,DELETE,OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    die('{"code":"200","message":"OK"}');
}
define('DS', DIRECTORY_SEPARATOR);
/* INI配置文件支持常量替换 */
define('MYPATH', dirname(__FILE__));
$uri = filter_input(INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_SPECIAL_CHARS);
preg_match('/\/([a-zA-Z0-9\.]+)\/([a-zA-Z0-9\_\-]+)([\/|\?].*?)?$/ie', $uri, $out);

$module = ucfirst($out[1]);

if (!in_array(strtolower($module), ['v2', 'v3', 'api2', 'app', 'api3', 'mall', 'api', 'mobile'])) {
    die('{"code":"-1","message":"模块不存在!"}');
}

if (file_exists(MYPATH . DS . 'application' . DS . $module) && $module) {
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
        define('CONFBDP', $environment);
        $application_path = APPLICATION_PATH . DS . 'conf' . DS . 'application_' . $environment . '.ini';
        break;
    }
}

if (!defined('CONFBDP')) {
    define('CONFBDP', 'local');
}

#added by liujf, 2018-01-25
define('COMMON_CONF_PATH', COMMON_PATH . DS . 'conf');
define('CONTROLLER_NAME', ucfirst($out[2]));

/**
 * 默认的, Yaf_Application将会读取配置文件中在php.ini中设置的ap.environ的配置节
 * 另外在配置文件中, 可以替换PHP的常量, 比如此处的APPLICATION_PATH
 */
$application = new Yaf_Application($application_path);

#SSO登陆验证 added by zhengkq
if ($module == 'V2' || $module == 'App' || $module == 'V3') {
    $config = $application->getConfig();
    require_once('common/library/Erui/Common/SSOClient.php');
    Erui\Common\SSOClient::Start($config->ssoServer);
}
/* 如果打开flushIstantly, 则视图渲染结果会直接发送给请求端
 * 而不会写入Response对象
 */
//$application->getDispatcher()->flushInstantly(TRUE);
Yaf_Dispatcher::getInstance()->catchException(TRUE);
/* 如果没有关闭自动response(通过Yaf_Dispatcher::getInstance()->returnResponse(TRUE)),
 * 则$response会被自动输出, 此处也不需要再次输出Response
 */

//error_reporting(E_ALL & E_STRICT);
$response = $application
        ->bootstrap()/* bootstrap是可选的调用 */
        ->run()/* 执行 */;
?>
