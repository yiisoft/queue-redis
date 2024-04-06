<?php
declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Message;

use Yiisoft\Queue\Message\MessageInterface;

final class Message implements MessageInterface
{
    public function __construct(
        private string $handlerName,
        private mixed  $data,
        private array  $metadata,
        private int    $delay = 0 //delay in seconds
    )
    {
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

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }
}
