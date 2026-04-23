<?php

declare(strict_types=1);

use function Hyperf\Support\env;

return [
    'default' => [
        'connect_timeout' => -1,
        'send_timeout' => -1,
        'recv_timeout' => -1,
        'client_id' => '',
        'max_write_attempts' => 3,
        'brokers' => [
            env('KAFKA_BROKERS', 'kafka:9092'),
        ],
        'bootstrap_servers' => env('KAFKA_BROKERS', 'kafka:9092'),
        'update_brokers' => true,
        'acks' => 0,
        'producer_id' => -1,
        'producer_epoch' => -1,
        'partition_leader_epoch' => -1,
        'interval' => null,
        'session_timeout' => 60,
        'rebalance_timeout' => 60,
        'replica_id' => -1,
        'rack_id' => '',
        'group_retry' => 5,
        'group_retry_sleep' => 1,
        'group_heartbeat' => 3,
        'offset_retry' => 5,
        'auto_create_topic' => true,
        'partition_assignment_strategy' => \LongLang\PhpKafka\Consumer\Assignor\RangeAssignor::class,
        'compression' => \LongLang\PhpKafka\Message\Codec\NoneCodec::class,
        'sasl' => null,
        'ssl' => null,
    ],
];
