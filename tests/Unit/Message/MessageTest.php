<?php

declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use Yiisoft\Queue\Redis\Message\Message;

final class MessageTest extends TestCase
{
    public function testGetHandlerName(): void
    {
        $message = new Message('handler', 'data', []);
        $this->assertEquals('handler', $message->getHandlerName());
        $this->assertEquals('handler', $message->getType());
    }

    public function testGetData(): void
    {
        $message = new Message('handler', 'data', []);
        $this->assertEquals('data', $message->getData());
        $this->assertEquals('data', $message->getPayload());
    }

    public function testGetMetadata(): void
    {
        $metadata = ['key' => 'value'];
        $message = new Message('handler', 'data', $metadata);
        $this->assertEquals($metadata, $message->getMetadata());
        $this->assertEquals($metadata, $message->getMeta());

        $message = new Message('handler', 'data', $metadata, 2);
        $metadata['delay'] = 2;
        $this->assertEquals($metadata, $message->getMetadata());
        $this->assertEquals($metadata, $message->getMeta());
    }

    public function testWithMetadata(): void
    {
        $message = new Message('handler', 'data', []);
        $messageWithMetadata = $message->withMetadata(['key' => 'value']);

        $this->assertNotSame($message, $messageWithMetadata);
        $this->assertSame([], $message->getMetadata());
        $this->assertSame(['key' => 'value'], $messageWithMetadata->getMetadata());
    }

    public function testWithMeta(): void
    {
        $message = new Message('handler', 'data', []);
        $messageWithMeta = $message->withMeta(['key' => 'value']);

        $this->assertNotSame($message, $messageWithMeta);
        $this->assertSame([], $message->getMeta());
        $this->assertSame(['key' => 'value'], $messageWithMeta->getMeta());
    }

    public function testWithDelay(): void
    {
        $message = new Message('handler', 'data', []);
        $delayedMessage = $message->withDelay(5);

        $this->assertNotSame($message, $delayedMessage);
        $this->assertEquals(5, $delayedMessage->getMetadata()['delay']);
    }

    public function testFromData(): void
    {
        $handlerName = 'test-handler';
        $data = ['key' => 'value'];

        $message = Message::fromData($handlerName, $data);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals($handlerName, $message->getHandlerName());
        $this->assertEquals($data, $message->getData());
        $this->assertEquals([], $message->getMetadata());
    }

    public function testFromPayload(): void
    {
        $handlerName = 'test-handler';
        $payload = ['key' => 'value'];

        $message = Message::fromPayload($handlerName, $payload);

        $this->assertSame($handlerName, $message->getType());
        $this->assertSame($payload, $message->getPayload());
        $this->assertSame([], $message->getMeta());
    }

    public function testFromDataFailsWithInvalidPayload(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Payload must contain only null, scalar values, and arrays of them.');

        Message::fromData('handler', new \stdClass());
    }

    public function testFromDataFailsWithNestedInvalidPayload(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Payload must contain only null, scalar values, and arrays of them.');

        Message::fromData('handler', ['nested' => new \stdClass()]);
    }
}
