<?php
declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Yiisoft\Queue\Cli\LoopInterface;
use Yiisoft\Queue\Message\JsonMessageSerializer;
use Yiisoft\Queue\Message\Message;
use Yiisoft\Queue\Message\MessageInterface;
use Yiisoft\Queue\Message\MessageSerializerInterface;
use Yiisoft\Queue\Redis\Adapter;
use Yiisoft\Queue\Redis\QueueProviderInterface;

class QueueTest extends TestCase
{

    public function testImmutable(): void
    {
        $queueProvider = $this->createMock(QueueProviderInterface::class);
        $adapter = new Adapter(
            $queueProvider,
            $this->createMock(MessageSerializerInterface::class),
            $this->createMock(LoopInterface::class)
        );

        self::assertNotSame($adapter, $adapter->withChannel('test'));
    }

    public function testAdapterNullMessage()
    {
        $provider = $this->createMock(QueueProviderInterface::class);
        $provider->method('reserve')->willReturn(null);

        $mockLoop = $this->createMock(LoopInterface::class);
        $mockLoop->expects($this->exactly(2))->method('canContinue')->willReturn(true, false);

        $adapter = new Adapter(
            $provider,
            new JsonMessageSerializer(),
            $mockLoop,
        );
        $notUseHandler = true;

        $adapter->runExisting(function (Message $message) use (&$notUseHandler) {
            $notUseHandler = false;
        });
        $this->assertTrue($notUseHandler);

        $adapter->subscribe(function (MessageInterface $message) use (&$notUseHandler) {
            $notUseHandler = false;
        });
        $this->assertTrue($notUseHandler);
    }
}
