<?php
declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Exception;

use Yiisoft\FriendlyException\FriendlyExceptionInterface;

class NotConnectedRedisException extends \RuntimeException implements FriendlyExceptionInterface
{

    public function getName(): string
    {
        return 'Not connected to Redis.';
    }

    public function getSolution(): ?string
    {
        return 'Check your Redis configuration and run $redis->connect() before using it.';
    }
}
