<?php

$json='{
"took": 3,
"timed_out": false,
"_shards": {
"total": 24,
"successful": 24,
"failed": 0
},
"hits": {
"total": 690631,
"max_score": 1,
"hits": [
{
"_index": "es",
"_type": "demo",
"_id": "2654",
"_score": 1,
"_source": {
"id": "2654",
"content": "通三益燕窝秋梨膏"
}
}
,
{
"_index": "es",
"_type": "demo",
"_id": "2657",
"_score": 1,
"_source": {
"id": "2657",
"content": "通爽牌芦荟清畅胶囊"
}
}
,
{
"_index": "es",
"_type": "demo",
"_id": "2661",
"_score": 1,
"_source": {
"id": "2661",
"content": "娃哈哈AD钙奶饮料"
}
}
,
{
"_index": "es",
"_type": "demo",
"_id": "2664",
"_score": 1,
"_source": {
"id": "2664",
"content": "智慧超人<sup>R</sup>健忆牛奶"
}
}
,
{
"_index": "es",
"_type": "demo",
"_id": "2667",
"_score": 1,
"_source": {
"id": "2667",
"content": "娃哈哈儿童多维咀嚼片"
}
}
,
{
"_index": "es",
"_type": "demo",
"_id": "2671",
"_score": 1,
"_source": {
"id": "2671",
"content": "娃哈哈牌铁锌钙奶饮料"
}
}
,
{
"_index": "es",
"_type": "demo",
"_id": "2674",
"_score": 1,
"_source": {
"id": "2674",
"content": "万宝香菇多糖口服液"
}
}
,
{
"_index": "es",
"_type": "demo",
"_id": "2677",
"_score": 1,
"_source": {
"id": "2677",
"content": "完达山牌刺五加口服液"
}
}
,
{
"_index": "es",
"_type": "demo",
"_id": "2681",
"_score": 1,
"_source": {
"id": "2681",
"content": "完达山牌牛初乳粉"
}
}
,
{
"_index": "es",
"_type": "demo",
"_id": "2684",
"_score": 1,
"_source": {
"id": "2684",
"content": "万鹤灵芝茶"
}
}
]
}
}';
echo '<pre>';
var_dump(json_decode($json,true));
die();
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
