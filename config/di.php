<?php

declare(strict_types=1);

use Yiisoft\Queue\Redis\QueueProvider;
use Yiisoft\Queue\Redis\QueueProviderInterface;

return [
    QueueProviderInterface::class => QueueProvider::class,
];
