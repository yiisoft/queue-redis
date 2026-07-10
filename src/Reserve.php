<?php

declare(strict_types=1);

namespace Yiisoft\Queue\Redis;

final class Reserve
{
    public function __construct(
        public readonly int $id,
        public readonly string $payload,
    ) {}
}
