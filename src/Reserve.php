<?php

declare(strict_types=1);

namespace Yiisoft\Queue\Redis;

final class Reserve
{
    public function __construct(
        readonly int $id,
        readonly string $payload
    ) {
    }
}
