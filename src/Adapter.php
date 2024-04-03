<?php
declare(strict_types=1);

namespace Yiisoft\Queue\Redis;

use Yiisoft\Queue\Adapter\AdapterInterface;
use Yiisoft\Queue\Cli\LoopInterface;
use Yiisoft\Queue\Enum\JobStatus;
use Yiisoft\Queue\Message\IdEnvelope;
use Yiisoft\Queue\Message\MessageInterface;
use Yiisoft\Queue\Message\MessageSerializerInterface;

class Adapter implements AdapterInterface
{
    public function __construct(
        private QueueProviderInterface     $provider,
        private MessageSerializerInterface $serializer,
        private LoopInterface              $loop,
        private int                        $timeout = 3
    )
    {
    }

    public function runExisting(callable $handlerCallback): void
    {
        $result = true;
        while ($result) {
            $message = $this->reserve();
            if (is_null($message)) {
                break;
            }

            $result = $handlerCallback($message);
            if ($result) {
                $this->provider->delete((string) $message->getId());
            }
        }
    }

    public function status(int|string $id): JobStatus
    {
        $id = (int) $id;
        if ($id <= 0) {
            throw new \InvalidArgumentException('This adapter IDs start with 1.');
        }

        if ($this->provider->existInReserved($id)) {
            return JobStatus::reserved();
        }

        if ($this->provider->existInWaiting($id)) {
            return JobStatus::waiting();
        }

        return JobStatus::done();
    }

    public function push(MessageInterface $message): MessageInterface
    {
        $payload = $this->serializer->serialize($message);
        $id = $this->provider->pushMessage($payload, $message->getMetadata());
        $envelope = IdEnvelope::fromMessage($message);
        $envelope->setId($id);
        return $envelope;
    }

    public function subscribe(callable $handlerCallback): void
    {
        while ($this->loop->canContinue()) {
            $message = $this->reserve();
            if (is_null($message)) {
                continue;
            }

            $result = $handlerCallback($message);
            if ($result) {
                $this->provider->delete((string) $message->getId());
            }
        }
    }

    public function withChannel(string $channel): AdapterInterface
    {
        $adapter = clone $this;
        $adapter->provider = $this->provider->withChannelName($channel);
        return $adapter;
    }

    private function reserve(): ?MessageInterface
    {
        $reserve = $this->provider->reserve($this->timeout);
        if (is_null($reserve)) {
            return null;
        }

        $message = $this->serializer->unserialize($reserve->payload);
        $envelope = IdEnvelope::fromMessage($message);
        $envelope->setId($reserve->id);

        return $envelope;
    }

}
