<?php

declare(strict_types=1);

namespace Yiisoft\Queue\Redis;

use BackedEnum;
use Yiisoft\Queue\Adapter\AdapterInterface;
use Yiisoft\Queue\Cli\LoopInterface;
use Yiisoft\Queue\JobStatus;
use Yiisoft\Queue\Message\IdEnvelope;
use Yiisoft\Queue\Message\MessageInterface;
use Yiisoft\Queue\Message\MessageSerializerInterface;

final class Adapter implements AdapterInterface
{
    public function __construct(
        private QueueProviderInterface $provider,
        private MessageSerializerInterface $serializer,
        private LoopInterface $loop,
        private int $timeout = 3
    ) {
    }

    public function runExisting(callable $handlerCallback): void
    {
        $result = true;
        while ($result) {
            $message = $this->reserve();
            if (null === $message) {
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
            return JobStatus::RESERVED;
        }

        if ($this->provider->existInWaiting($id)) {
            return JobStatus::WAITING;
        }

        return JobStatus::DONE;
    }

    public function push(MessageInterface $message): MessageInterface
    {
        $payload = $this->serializer->serialize($message);
        $id = $this->provider->pushMessage($payload, $message->getMetadata());
        return new IdEnvelope($message, $id);
    }

    public function subscribe(callable $handlerCallback): void
    {
        while ($this->loop->canContinue()) {
            $message = $this->reserve();
            if (null === $message) {
                continue;
            }

            $result = $handlerCallback($message);
            if ($result) {
                $this->provider->delete((string) $message->getId());
            }
        }
    }

    public function withChannel(BackedEnum|string $channel): AdapterInterface
    {
        $adapter = clone $this;
        $channelName = is_string($channel) ? $channel : (string) $channel->value;
        $adapter->provider = $this->provider->withChannelName($channelName);
        return $adapter;
    }

    private function reserve(): ?IdEnvelope
    {
        $reserve = $this->provider->reserve($this->timeout);
        if (null === $reserve) {
            return null;
        }

        $message = $this->serializer->unserialize($reserve->payload);
        return new IdEnvelope($message, $reserve->id);
    }

    public function getChannel(): string
    {
        return $this->provider->getChannelName();
    }
}
