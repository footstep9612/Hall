<?PHP

class V1Controller extends Yaf_Controller_Abstract {

    private $url = 'http://172.18.18.99:8080';

    protected function jsonReturn($data, $type = 'JSON') {


        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }

    function __call($functionName, $args) {

        $call = json_decode(file_get_contents("php://input"), true);
        $this->Write(var_export($call, true), 'call');
        $json = $this->post($call, $_SERVER['PATH_INFO'], 30, true);
        $re = json_decode($json, true);
        $_SESSION['token'] = $re['obj']['token'];
        $this->jsonReturn($re);
    }

    public function loginAction() {
        $user = json_decode('{"password":"123456","userName":"86-15901193403"}', true); // json_decode(file_get_contents("php://input"), true);

        $userName = $user['userName'];
        $password = $user['password'];

        $salt = Yaf_Application::app()->getConfig()->saltkey;

        $password = crypt($password, $salt);


        $model = new UsermainModel();

        $userinfo = $model->Userinfo("user_main_id,username", array("username" => $userName, 'password' => $password));

        $data = [];
        if ($userinfo) {
            $data['success'] = 1;
            $data['msg'] = '登录成功!';
            $jwtclient = new JWTClient();
            $jwt['uid'] = md5($userinfo['user_main_id']);
            $jwt['ext'] = time();
            $jwt['iat'] = time();
            $jwt['account'] = $userinfo['username'];
            $data['obj'] = ['token' => $jwtclient->encode($jwt)]; //加密
            $data['jsonStr'] = json_decode($data);
        } else {
            $data['success'] = 0;
            $data['msg'] = '登录失败!';
            $data['obj'] = [];
            $data['jsonStr'] = json_decode($data);
        }

//        $json = $this->post($user, '/api/v1/login');
//        $re = json_decode($json, true);
//
        $this->jsonReturn($data);
    }

    function Write($str, $name = 'test') {
        $file = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . $name . date('Y-m-d') . '.log';

        $this->RecursiveMkdir(dirname($file));
        $fp = fopen($file, "a+");
        flock($fp, LOCK_EX);
        fwrite($fp, "\r\n" . date("Y-m-d H:i:s") . "\t" . $str);
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    public function RecursiveMkdir($path) {
        if (!file_exists($path)) {
            $this->RecursiveMkdir(dirname($path));
            mkdir($path);
        }
    }

    public function menuAction() {

        $menu = json_decode(file_get_contents("php://input"), true);


        $json = '{"jsonStr":"{\"msg\":\"操作成功\",\"success\":true,\"obj\":{\"menu\":[{\"name\":\"boss后台\",\"id\":\"2c9292c75bed2acb015bed4bef030016\",\"child\":[{\"src\":\"\",\"name\":\"项目管理\",\"id\":\"2c9292c75bed2acb015bed50651f0028\",\"iconStyle\":\"el-icon-message\",\"child\":[{\"src\":\"/demand/list\",\"name\":\"需求列表\",\"id\":\"2c9292c75bed2acb015bed50c26c002a\",\"iconStyle\":\"\"},{\"src\":\"/demand/ulitem\",\"name\":\"项目列表\",\"id\":\"2c9292c75bed2acb015bed513230002c\",\"iconStyle\":\"\"}]},{\"src\":\"\",\"name\":\"标签管理\",\"id\":\"2c9292c75bed2acb015bed4fb8e50024\",\"iconStyle\":\"el-icon-setting\",\"child\":[{\"src\":\"/label\",\"name\":\"标签列表\",\"id\":\"2c9292c75bed2acb015bed5022b90026\",\"iconStyle\":\"\"}]},{\"src\":\"\",\"name\":\"话题文章\",\"id\":\"2c9292c75bed2acb015bed4f13080020\",\"iconStyle\":\"el-icon-menu\",\"child\":[{\"src\":\"/topic\",\"name\":\"话题列表\",\"id\":\"2c9292c75bed2acb015bed4f6e1a0022\",\"iconStyle\":\"\"}]},{\"src\":\"\",\"name\":\"任务管理\",\"id\":\"2c9292c75bed2acb015bed4c37e40018\",\"iconStyle\":\"el-icon-time\",\"child\":[{\"src\":\"/newjob\",\"name\":\"任务列表\",\"id\":\"2c9292c75bed2acb015bed4ca253001a\",\"iconStyle\":\"\"}]},{\"src\":\"\",\"name\":\"意见反馈\",\"id\":\"2c9292c75c15c1e2015c1540ceb90005\",\"iconStyle\":\"el-icon-date\",\"child\":[{\"src\":\"/feedback\",\"name\":\"反馈列表\",\"id\":\"2c9292c75c15c1e2015c154158940007\",\"iconStyle\":\"\"}]},{\"src\":\"\",\"name\":\"文档管理\",\"id\":\"2c9292c75bed2acb015bed4d14f8001c\",\"iconStyle\":\"el-icon-picture\",\"child\":[{\"src\":\"/archive\",\"name\":\"文档列表\",\"id\":\"2c9292c75bed2acb015bed4d85ba001e\",\"iconStyle\":\"\"}]},{\"src\":\"\",\"name\":\"商品管理\",\"id\":\"2c9292c75c20ac88015c23a73b020006\",\"iconStyle\":\"el-icon-upload\",\"child\":[{\"src\":\"/matter\",\"name\":\"素材列表\",\"id\":\"2c9292c75c20ac88015c23a7912f0008\",\"iconStyle\":\"\"},{\"src\":\"/celdes\",\"name\":\"正式商品\",\"id\":\"2c9292c75c20ac88015c25c22a6d001c\",\"iconStyle\":\"\"}]}]}]}}","msg":"操作成功","success":true,"obj":{"menu":[{"name":"boss后台","id":"2c9292c75bed2acb015bed4bef030016","child":[{"src":"","name":"项目管理","id":"2c9292c75bed2acb015bed50651f0028","iconStyle":"el-icon-message","child":[{"src":"/demand/list","name":"需求列表","id":"2c9292c75bed2acb015bed50c26c002a","iconStyle":""},{"src":"/demand/ulitem","name":"项目列表","id":"2c9292c75bed2acb015bed513230002c","iconStyle":""}]},{"src":"","name":"标签管理","id":"2c9292c75bed2acb015bed4fb8e50024","iconStyle":"el-icon-setting","child":[{"src":"/label","name":"标签列表","id":"2c9292c75bed2acb015bed5022b90026","iconStyle":""}]},{"src":"","name":"话题文章","id":"2c9292c75bed2acb015bed4f13080020","iconStyle":"el-icon-menu","child":[{"src":"/topic","name":"话题列表","id":"2c9292c75bed2acb015bed4f6e1a0022","iconStyle":""}]},{"src":"","name":"任务管理","id":"2c9292c75bed2acb015bed4c37e40018","iconStyle":"el-icon-time","child":[{"src":"/newjob","name":"任务列表","id":"2c9292c75bed2acb015bed4ca253001a","iconStyle":""}]},{"src":"","name":"意见反馈","id":"2c9292c75c15c1e2015c1540ceb90005","iconStyle":"el-icon-date","child":[{"src":"/feedback","name":"反馈列表","id":"2c9292c75c15c1e2015c154158940007","iconStyle":""}]},{"src":"","name":"文档管理","id":"2c9292c75bed2acb015bed4d14f8001c","iconStyle":"el-icon-picture","child":[{"src":"/archive","name":"文档列表","id":"2c9292c75bed2acb015bed4d85ba001e","iconStyle":""}]},{"src":"","name":"商品管理","id":"2c9292c75c20ac88015c23a73b020006","iconStyle":"el-icon-upload","child":[{"src":"/matter","name":"素材列表","id":"2c9292c75c20ac88015c23a7912f0008","iconStyle":""},{"src":"/celdes","name":"正式商品","id":"2c9292c75c20ac88015c25c22a6d001c","iconStyle":""}]}]}]},"attributes":null}'; // $this->post($menu, '/api/v1/menu');
        $re = json_decode($json, true);

        $this->jsonReturn($re);
    }

    public function role_usersAction() {

        $menu = json_decode(file_get_contents("php://input"), true);

        $json = $this->post($menu, '/api/v1/role/users');
        $re = json_decode($json, true);

        $this->jsonReturn($re);
    }

    private function post($data, $action, $timeout = 30, $regi = false) {
        if (!$regi) {
            $url = $this->url . $action;
        } else {

            $url = 'http://local.regi.com' . $action;
        }

        $header = array(
            'Content-type: application/json;charset=UTF-8',
            'Accept: */*',
            'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
            'Connection: Keep-Alive',
        );
        $formdata = json_encode($data);
        $this->Write($url);
        $this->Write($formdata);
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
        // curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookiejar);
        $response = curl_exec($ch);


        if (curl_errno($ch)) {
            print curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }

}
