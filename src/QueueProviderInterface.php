<?php
declare(strict_types=1);

namespace Yiisoft\Queue\Redis;

interface QueueProviderInterface
{


    public function pushMessage(string $message, array $metadata = []): int;

    /**
     * @return null|Reserve payload and id, or null if queue is empty
     */
    public function reserve(int $timeout = 0): ?Reserve;

    public function delete(string $id): void;

    public function existInWaiting(int $id): bool;

    public function existInReserved(int $id): bool;

    public function withChannelName(string $channelName): QueueProviderInterface;
}
