<?php

declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Message;

use Yiisoft\Queue\Message\DelayEnvelope;
use Yiisoft\Queue\Message\MessageInterface;

/**
 * @psalm-import-type MessagePayload from MessageInterface
 * @psalm-import-type MessageMeta from MessageInterface
 */
final class Message implements MessageInterface
{
    /**
     * @psalm-param MessagePayload $payload
     * @psalm-param MessageMeta $meta
     */
    public function __construct(
        private string $handlerName,
        private bool|int|float|string|array|null $payload,
        private array $meta,
        private int $delay = 0 //delay in seconds
    ) {
        if ($this->delay > 0) {
            $this->meta[DelayEnvelope::META_DELAY_SECONDS] = $delay;
        }
    }

    public function withDelay(int $delay): self
    {
        $message = clone $this;
        $message->meta[DelayEnvelope::META_DELAY_SECONDS] = $delay;
        return $message;
    }

    public function getHandlerName(): string
    {
        return $this->handlerName;
    }

    public function getType(): string
    {
        return $this->handlerName;
    }

    /**
     * @psalm-return MessagePayload
     */
    public function getPayload(): bool|int|float|string|array|null
    {
        return $this->payload;
    }

    /**
     * @psalm-return MessageMeta
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @psalm-param MessageMeta $meta
     */
    public function withMeta(array $meta): static
    {
        $message = clone $this;
        $message->meta = $meta;
        return $message;
    }

    /**
     * @psalm-param MessagePayload $payload
     */
    public static function fromPayload(string $type, bool|int|float|string|array|null $payload): static
    {
        return new self($type, $payload, []);
    }
}
