<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of KafKaServer
 *
 * @author zyg
 */
class KafKaServer {

    private $queue_server = '';
    private $logger = null;
    private $config = null;

    public function __construct() {
//        $server = Yaf_Application::app()->getConfig()->queue->server;
//        $this->queue_server = $server;
//        $this->logger = new Logger('my_logger');
//        $data = date("Ymd");
//        $stream = fopen(MYPATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'kafka' . DIRECTORY_SEPARATOR . $data . '.log', 'a+');
//        $this->logger->pushHandler(new StreamHandler($stream));
    }

    public function produce() {

        $KafKa_Lite = new KafKa_Lite("localhost");
// 设置一个Topic
        $KafKa_Lite->setTopic("test");
// 单次写入效率ok  写入1w条15 毫秒
        $Producer = $KafKa_Lite->newProducer();
// 参数分别是partition,消息内容,消息key(可选)
// partition:可以设置为KAFKA_PARTITION_UA会自动分配,比如有6个分区写入时会随机选择Partition
        for ($i = 0; $i < 10000; $i++) {
            $flag = $Producer->setMessage(KAFKA_PARTITION_UA, "hello" . $i, $i);
            var_dump($flag);
        }
//        $this->config = \Kafka\ProducerConfig::getInstance();
//        $this->config->setMetadataRefreshIntervalMs(10000);
//        $this->config->setMetadataBrokerList($this->queue_server);
//        $this->config->setBrokerVersion('0.9.0.1');
//        $this->config->setRequiredAck(1);
//        $this->config->setIsAsyn(true);
//        $this->config->setProduceInterval(500);
//        $producer = new \Kafka\Producer(function() {
//            return [
//                [
//                    'topic' => 'test',
//                    'value' => 'test....message.',
//                    'key' => 'testkey',
//                ]
//            ];
//        });
//        $producer->setLogger($this->logger);
//        $producer->success(function($result) {
//
//            var_dump("success", $result);
//        });
//        $producer->error(function($errorCode) {
//            var_dump("error", $errorCode);
//        });
//        $producer->send();
    }

    public function Consumer($topic) {

// 配置KafKa集群(默认端口9092)通过逗号分隔
        $KafKa_Lite = new KafKa_Lite("localhost");
// 设置一个Topic
        $KafKa_Lite->setTopic("test");
// 设置Consumer的Group分组(不使用自动offset的时候可以不设置)
        $KafKa_Lite->setGroup("test");

// 此项设置决定 在使用一个新的group时  是从 最小的一个开始 还是从最大的一个开始  默认是最大的(或尾部)
        $KafKa_Lite->setTopicConf('auto.offset.reset', 'smallest');
// 此项配置决定在获取数据后回自动作为一家消费 成功 无需在 一定要 stop之后才会 提交 但是也是有限制的
// 时间越小提交的时间越快,时间越大提交的间隔也就越大 当获取一条数据之后就抛出异常时 更具获取之后的时间来计算是否算作处理完成
// 时间小于这个时间时抛出异常 则不会更新offset 如果大于这个时间则会直接更新offset 建议设置为 100~1000之间
        $KafKa_Lite->setTopicConf('auto.commit.interval.ms', 1000);

// 获取Consumer实例
        $consumer = $KafKa_Lite->newConsumer();

// 开启Consumer获取,参数分别为partition(默认:0),offset(默认:KAFKA_OFFSET_STORED)
        $consumer->consumerStart(0);
        for ($i = 0; $i < 100; $i++) {
            // 当获取不到数据时会阻塞默认10秒可以通过$consumer->setTimeout()进行设置
            // 阻塞后由数据能够获取会立即返回,超过10秒回返回null,正常返回格式为Kafka_Message
            $consumer->setTimeout(1);
            $message = $consumer->consume();
            print_r($message);
        }

// 关闭Consumer(不关闭程序不会停止)
        $consumer->consumerStop();
//        $consumer = new \Kafka\Consumer();
//        $config = \Kafka\ConsumerConfig::getInstance();
//        $config->setMetadataRefreshIntervalMs(10000);
//        $config->setMetadataBrokerList($this->queue_server);
//
//        $config->setGroupId("1");
//        $config->setBrokerVersion('0.9.0.1');
//        $config->setTopics($topic);
//        $consumer->setLogger($this->logger);
//        $consumer->start(function($topic, $part, $message) {
//            var_dump($message);
//            var_dump($topic);
//            var_dump($part);
//        });
    }

}
