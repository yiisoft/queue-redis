<?php

declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Message;

use Yiisoft\Queue\Message\MessageInterface;

/**
 * @psalm-import-type MessageMeta from MessageInterface
 * @psalm-import-type MessagePayload from MessageInterface
 */
final class Message implements MessageInterface
{
    /**
     * @psalm-param MessagePayload $data
     * @psalm-param MessageMeta $metadata
     */
    public function __construct(
        private string $handlerName,
        private bool|int|float|string|array|null $data,
        private array $metadata,
        private int $delay = 0 //delay in seconds
    ) {
        if ($this->delay > 0) {
            $this->metadata['delay'] = $delay;
        }
    }

    public function withDelay(int $delay): self
    {
        $message = clone $this;
        $message->metadata['delay'] = $delay;
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

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getPayload(): bool|int|float|string|array|null
    {
        return $this->data;
    }

    /**
     * @psalm-return MessageMeta
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @psalm-return MessageMeta
     */
    public function getMeta(): array
    {
        return $this->metadata;
    }

    /**
     * @psalm-param MessageMeta $metadata
     */
    public function withMetadata(array $metadata): static
    {
        $message = clone $this;
        $message->metadata = $metadata;
        return $message;
    }

    /**
     * @psalm-param MessageMeta $meta
     */
    public function withMeta(array $meta): static
    {
        return $this->withMetadata($meta);
    }

    public static function fromData(string $type, mixed $data): self
    {
        self::assertPayload($data);
        return new self($type, $data, []);
    }

    public static function fromPayload(string $type, bool|int|float|string|array|null $payload): self
    {
        return new self($type, $payload, []);
    }

    /**
     * @psalm-assert MessagePayload $payload
     */
    private static function assertPayload(mixed $payload): void
    {
        if (!self::isPayload($payload)) {
            throw new \InvalidArgumentException('Payload must contain only null, scalar values, and arrays of them.');
        }
    }

    /**
     * @psalm-assert-if-true MessagePayload $payload
     */
    private static function isPayload(mixed $payload): bool
    {
        if ($payload === null || is_scalar($payload)) {
            return true;
        }

        if (!is_array($payload)) {
            return false;
        }

        foreach ($payload as $value) {
            if (!self::isPayload($value)) {
                return false;
            }
        }

        return true;
    }
}
