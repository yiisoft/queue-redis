<?php

declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Message;

use Yiisoft\Queue\Message\MessageInterface;

final class Message implements MessageInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $metadata;

    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $handlerName,
        private mixed $data,
        array $metadata,
        private int $delay = 0 //delay in seconds
    ) {
        $this->metadata = $metadata;

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

    /**
     * @return array<string, mixed>
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function withMetadata(array $metadata): static
    {
        $message = clone $this;
        $message->metadata = $metadata;
        return $message;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public static function fromData(string $type, mixed $data, array $metadata = []): self
    {
        return new self($type, $data, $metadata);
    }
}
