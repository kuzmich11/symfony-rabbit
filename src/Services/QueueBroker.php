<?php

namespace App\Services;

use AMQPConnection;
use AMQPChannel;
use AMQPChannelException;
use AMQPConnectionException;
use AMQPExchange;
use AMQPExchangeException;
use AMQPQueue;
use AMQPQueueException;

class QueueBroker
{

    protected AMQPConnection $connection;
    protected AMQPChannel $channel;
    protected ?AMQPExchange $exchange = null;
    protected ?AMQPQueue $queue = null;

    /**
     * @throws AMQPConnectionException
     */
    public function __construct()
    {
        $connection_params = array(
            'host' => 'localhost',
            'port' => '15672',
            'vhost' => '/',
            'login' => 'guest',
            'password' => 'guest'
        );

        $this->connection = new AMQPConnection ($connection_params);

        $this->connection->connect();

        $this->channel = new AMQPChannel($this->connection);

        return $this;
    }

    /**
     * @throws AMQPExchangeException
     * @throws AMQPChannelException
     * @throws AMQPConnectionException
     */
    public function getExchange (): AMQPExchange
    {
        $exchange = new AMQPExchange($this->channel);
        $exchange->setName('test_exchange');
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declare();

        return $this->exchange = $exchange;
    }

    /**
     * @throws AMQPChannelException
     * @throws AMQPQueueException
     * @throws AMQPConnectionException
     */
    public function getQueue (): AMQPQueue
    {
        $queue = new AMQPQueue($this->channel);
        $queue->setName('test_queue');
        $queue->setFlags(AMQP_IFUNUSED | AMQP_AUTODELETE);
        $queue->declare();
        $this->queue->bind($this->exchange->getName(), 'test_key');

        return $this->queue = $queue;
    }

//    public function bind ()
//    {
//        $this->queue->bind($this->exchange->getName(), 'test_key');
//        return;
//    }

    public function getMsgToQueue ()
    {
        while (true) {
            if ($envelope = $this->queue->get(AMQP_AUTOACK)) {
                return json_decode($envelope->getBody());
            }
        }
    }

    public function setMsgFromQueue (string $message)
    {
        $this->exchange->publish(json_encode($message), "test_key");
        $this->connection->disconnect();

        return 'Сообщение отправлено';
    }
}