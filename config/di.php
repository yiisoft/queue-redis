<?php
declare(strict_types=1);

return [
    \Yiisoft\Queue\Message\MessageSerializerInterface::class => \Yiisoft\Queue\Message\JsonMessageSerializer::class,
    \Yiisoft\Queue\Redis\QueueProviderInterface::class => \Yiisoft\Queue\Redis\QueueProvider::class,
    \Yiisoft\Queue\Cli\LoopInterface::class => \Yiisoft\Queue\Cli\SignalLoop::class
];
