<?php

class BaseQueue {

    protected static $instance;

    /**
     * @var AMQPConnection
     */
    protected $connection;

    /**
     * Construct
     */
    public function __construct() {
        $this->connection = new AMQPConnection($this->getCredentials());
    }

    public static function instance() {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Connect
     * @return Queue
     */
    public function connect() {
        $this->connection->connect();
        return $this;
    }

    /**
     * Is connected
     * @return boolean
     */
    public function isConnected() {
        return $this->connection->isConnected();
    }

    public function getQueues() {
        global $CONFIG;
        if (!is_array($CONFIG->queues)) {
            return [];
        }

        return $CONFIG->queues;
    }

    public function getCredentials() {
        $rabbitmq = Yaf_Application::app()->getConfig()->rabbitmq;

        return [
            'host' => $rabbitmq->host,
            'port' => $rabbitmq->port,
            'login' => $rabbitmq->login,
            'password' => $rabbitmq->password,
        ];
    }

    /**
     * Put message
     * @param string $name
     * @param string $message
     * @return boolean
     */
    public function put($name, $message) {
        if (!$this->isConnected()) {
            $this->connect();
        }

        $queues = $this->getQueues();
        if (!isset($queues[$name])) {
            throw new InvalidArgumentException("queue[{$name}] must be config first.");
        }

        $config = $queues[$name];
        $channel = new AMQPChannel($this->connection);
        $queue = new AMQPQueue($channel);
        $queue->setName($name);
        if (!empty($config['persist'])) {
            $queue->setFlags(AMQP_DURABLE);
        }
        $queue->declareQueue();

        $exchange = new AMQPExchange($channel);
        if (!empty($config['persist'])) {
            $exchange->setFlags(AMQP_DURABLE);
        }
        return $exchange->publish($message, $name);
    }

    /**
     * Consume message
     * @param string   $name
     * @param callback $callback
     * @return boolean
     */
    public function consume($name, $callback) {
        if (!$this->isConnected()) {
            $this->connect();
        }

        $queues = $this->getQueues();
        if (!isset($queues[$name])) {
            throw new InvalidArgumentException("queue[{$name}] must be configured first.");
        }

        $config = $queues[$name];
        $channel = new AMQPChannel($this->connection);
        $queue = new AMQPQueue($channel);
        $queue->setName($name);
        if (!empty($config['persist'])) {
            $queue->setFlags(AMQP_DURABLE);
        }
        $queue->declareQueue();
        $queue->consume(function($message, $queue) use ($callback) {
            try {
                $result = call_user_func_array($callback, ['message' => $message->getBody()]);
                $error = $result;
            } catch (Exception $e) {
                $result = false;
                $error->getMessage();
            }
            if (true === $result) {
                $queue->nack($message->getDeliveryTag());
                return true;
            }
            // 写日志
            global $CONFIG;
            $destDir = MYPATH . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . date('Y');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777);
                $destDir = $destDir . DS . date('m');
                if (!file_exists($destDir)) {
                    @mkdir($destDir, 0777);
                }
            } else {
                $destDir = $destDir . DS . date('m');
                if (!file_exists($destDir)) {
                    @mkdir($destDir, 0777);
                }
            }

            $filePath = $destDir . DS . date('d') . '_queue.log';
            $fp = fopen($filePath, 'a+');

            $info = date('Y-m-d H:i:s') . '----' . $error . " \r\n";
            fwrite($fp, $info, strlen($info));
            fclose($fp);
        });
    }

    public function __destruct() {
        $this->connection->disconnect();
    }

}
