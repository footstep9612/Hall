<?php

/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Ap调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract {

    public function _initSession($dispatcher) {
        Yaf_Session::getInstance()->start();
    }

    public function _initConfig() {
        $this->config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set("config", $this->config);
        //关闭自动加载模板
        Yaf_Dispatcher::getInstance()->autoRender(false);
        //设置默认时区
        date_default_timezone_set("PRC");
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        //注册一个插件
        $AutoloadPlugin = new AutoloadPlugin();
        $dispatcher->registerPlugin($AutoloadPlugin);
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher) {
        $router = Yaf_Dispatcher::getInstance()->getRouter();
        $dispatcher->disableView();
        $Request = $dispatcher->getRequest();
        if (!$Request->isCli()) {
            $out = null;
            preg_match('/\/([a-zA-Z0-9\_\-]+)\/([a-zA-Z0-9\_\-]+)\/(.*?)$/ie', $Request->getRequestUri(), $out);
            $ControllerName = $this->parseName($out[2], 1);
            $Request->setControllerName($ControllerName);
            $Request->setModuleName('Index');
            //创建一个路由协议实例
            $route = new Yaf_Route_Rewrite('/:module/:Controller/:Action', ['module' => 'Index',
                'controller' => $ControllerName,
                'action' => ':Action']);
            //使用路由器装载路由协议
            $router->addRoute('routes', $route);
        } else {
            global $argv;
            $uri = $argv [1];
            $out = null;
            $Request->setRequestUri($uri);
            preg_match('/\/([a-zA-Z0-9\_\-]+)\/([a-zA-Z0-9\_\-]+)\/(.*?)$/ie', $Request->getRequestUri(), $out);
            $ControllerName = $this->parseName($out[2], 1);
            $Request->setControllerName($ControllerName);
            $Request->setModuleName('Index');
            //创建一个路由协议实例
            $route = new Yaf_Route_Rewrite('/:module/:Controller/:Action', ['module' => 'Index',
                'controller' => $ControllerName,
                'action' => ':Action']);
            //使用路由器装载路由协议
            $router->addRoute('routes', $route);
        }
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string  $name 字符串
     * @param integer $type 转换类型
     * @param bool    $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    public static function parseName($name, $type = 0, $ucfirst = true) {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);
            return $ucfirst ? ucfirst($name) : lcfirst($name);
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }

}
