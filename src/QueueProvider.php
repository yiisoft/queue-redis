<?php
declare(strict_types=1);

namespace Yiisoft\Queue\Redis;

use RedisException;
use Yiisoft\Queue\Redis\Exception\NotConnectedRedisException;

class QueueProvider implements QueueProviderInterface
{
    private const DEFAULT_CHANNEL_NAME = 'yii-queue';

    /**
     * @throws RedisException
     */
    public function __construct(
        private \Redis $redis, //redis connection,
        private string $channelName = self::DEFAULT_CHANNEL_NAME
    )
    {
        if (!$this->redis->isConnected()) {
            throw new NotConnectedRedisException('Redis is not connected');
        }
    }

    /**
     * @throws RedisException
     */
    public function pushMessage(string $message, array $metadata = []): int
    {
        $id = $this->getId();
        $this->redis->hset("$this->channelName.messages", (string) $id, $message);
        $this->redis->lpush("$this->channelName.waiting", $id);
        return $id;
    }

    /**
     * @throws RedisException
     */
    public function existInWaiting(int $id): bool
    {
        $exist = $this->redis->hexists("$this->channelName.messages", (string) $id);
        return is_bool($exist) ? $exist : false;
    }

    /**
     * @throws RedisException
     */
    public function existInReserved(int $id): bool
    {
        $exist = $this->redis->hexists("$this->channelName.attempts", (string) $id);
        return is_bool($exist) ? $exist : false;
    }

    /**
     * @throws RedisException
     */
    public function reserve(int $timeout = 0): ?Reserve
    {
        // Moves delayed and reserved jobs into waiting list with lock for one second
        try {
            if ($this->redis->set("$this->channelName.moving_lock", 'true', ['NX', 'EX', 1])) {
                $this->moveExpired("$this->channelName.delayed");
                $this->moveExpired("$this->channelName.reserved");
            }
        } finally {
            $this->redis->del("$this->channelName.moving_lock");
        }

        $result = $this->redis->brpop("$this->channelName.waiting", $timeout);
        if (is_null($result) || !isset($result[1])) {
            return null;
        }
        $id = $result[1];
        if (!is_string($id)) {
            return null;
        }

        $payload = $this->redis->hget("$this->channelName.messages", $id);
        if (!is_string($payload)) {
            return null;
        }
        $this->redis->zRem("$this->channelName.reserved", time(), $id);
        $this->redis->hincrby("$this->channelName.attempts", $id, 1);
        return new Reserve((int) $id, $payload);
    }

    public function delete(string $id): void
    {
        $this->redis->zrem("$this->channelName.reserved", $id);
        $this->redis->hdel("$this->channelName.messages", $id);
        $this->redis->hdel("$this->channelName.attempts", $id);
    }

    /**
     * @throws RedisException
     */
    private function moveExpired(string $from): void
    {
        $now = time();
        $expired = $this->redis->zrevrangebyscore($from, (string) $now, '-inf');
        if (is_array($expired)) {
            $this->redis->zremrangebyscore($from, '-inf', (string) $now);
            /** @var string $id */
            foreach ($expired as $id) {
                $this->redis->rpush("$this->channelName.waiting", $id);
            }
        }
    }

    /**
     * @throws RedisException
     */
    public function getId(): int
    {
        $id = $this->redis->incr("$this->channelName.message_id");
        if (is_int($id)) {
            return $id;
        }

        throw new \RuntimeException('Unable to get message id');
    }

    public function withChannelName(string $channelName): QueueProviderInterface
    {
        if ($this->channelName === $channelName) {
            return $this;
        }

        return new self($this->redis, $channelName);
    }
}
