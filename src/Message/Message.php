<?php

declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Message;

use Yiisoft\Queue\Message\MessageInterface;

/**
 * @psalm-import-type MessagePayload from MessageInterface
 * @psalm-import-type MessageMeta from MessageInterface
 */
final class Message implements MessageInterface
{
    public function __construct(
        private string $handlerName,
        /**
         * @psalm-var MessagePayload
         */
        private bool|int|float|string|array|null $data,
        /**
         * @psalm-var MessageMeta
         */
        private array $metadata,
        int $delay = 0 //delay in seconds
    ) {
        if ($delay > 0) {
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

    public function getPayload(): bool|int|float|string|array|null
    {
        return $this->data;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @psalm-return MessageMeta
     */
    public function getMeta(): array
    {
        return $this->metadata;
    }

    /**
     * @psalm-return MessageMeta
     */
    public function getMetadata(): array
    {
        return $this->getMeta();
    }

    /**
     * @psalm-param MessageMeta $meta
     */
    public function withMeta(array $meta): static
    {
        $message = clone $this;
        $message->metadata = $meta;
        return $message;
    }

    /**
     * @psalm-param MessageMeta $metadata
     */
    public function withMetadata(array $metadata): static
    {
        return $this->withMeta($metadata);
    }

    /**
     * @psalm-param MessagePayload $payload
     */
    public static function fromPayload(string $type, bool|int|float|string|array|null $payload): static
    {
        return new self($type, $payload, []);
    }

    /**
     * @psalm-param MessagePayload $data
     */
    public static function fromData(string $type, bool|int|float|string|array|null $data): self
    {
        return new self($type, $data, []);
    }
}
