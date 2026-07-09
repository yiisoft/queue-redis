<?php

declare(strict_types=1);

namespace Unit\Message;

use PHPUnit\Framework\TestCase;
use Yiisoft\Queue\Message\DelayEnvelope;
use Yiisoft\Queue\Redis\Message\Message;

class MessageTest extends TestCase
{
    public function testGetHandlerName(): void
    {
        $message = new Message('handler', 'data', []);
        $this->assertEquals('handler', $message->getHandlerName());
        $this->assertEquals('handler', $message->getType());
    }

    public function testGetPayload(): void
    {
        $message = new Message('handler', 'data', []);
        $this->assertEquals('data', $message->getPayload());
    }

    public function testGetMetadata(): void
    {
        $metadata = ['key' => 'value'];
        $message = new Message('handler', 'data', $metadata);
        $this->assertEquals($metadata, $message->getMeta());

        $message = new Message('handler', 'data', $metadata, 2);
        $metadata[DelayEnvelope::META_DELAY_SECONDS] = 2;
        $this->assertEquals($metadata, $message->getMeta());
    }

    public function testWithMetadata(): void
    {
        $message = new Message('handler', 'data', []);
        $messageWithMetadata = $message->withMeta(['key' => 'value']);

        $this->assertNotSame($message, $messageWithMetadata);
        $this->assertSame([], $message->getMeta());
        $this->assertSame(['key' => 'value'], $messageWithMetadata->getMeta());
    }

    public function testWithDelay(): void
    {
        $message = new Message('handler', 'data', []);
        $delayedMessage = $message->withDelay(5);

        $this->assertNotSame($message, $delayedMessage);
        $this->assertEquals(5, $delayedMessage->getMeta()[DelayEnvelope::META_DELAY_SECONDS]);
    }

    public function testFromPayload(): void
    {
        $handlerName = 'test-handler';
        $data = ['key' => 'value'];

        $message = Message::fromPayload($handlerName, $data);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals($handlerName, $message->getHandlerName());
        $this->assertEquals($data, $message->getPayload());
        $this->assertEquals([], $message->getMeta());
    }
}
