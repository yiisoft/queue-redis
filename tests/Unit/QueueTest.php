<?php
declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Tests\Unit;

use Yiisoft\Queue\Adapter\AdapterInterface;
use Yiisoft\Queue\Cli\LoopInterface;
use Yiisoft\Queue\Enum\JobStatus;
use Yiisoft\Queue\Message\JsonMessageSerializer;
use Yiisoft\Queue\Message\Message;
use Yiisoft\Queue\Message\MessageInterface;
use Yiisoft\Queue\Message\MessageSerializerInterface;
use Yiisoft\Queue\Queue;
use Yiisoft\Queue\Redis\Adapter;
use Yiisoft\Queue\Redis\QueueProvider;
use Yiisoft\Queue\Redis\QueueProviderInterface;
use Yiisoft\Queue\Redis\Tests\Support\FileHelper;

class QueueTest extends UnitTestCase
{
    public function testRunExisting()
    {
        $time = time();
        $fileName = 'test-run' . $time;
        $fileHelper = new FileHelper();

        $queue = $this->getDefaultQueue($this->getAdapter());

        $queue->push(
            new Message('ext-simple', ['file_name' => $fileName, 'payload' => ['time' => $time]])
        );

        self::assertNull($fileHelper->get($fileName));

        $queue->run();

        $result = $fileHelper->get($fileName);
        self::assertNotNull($result);
        self::assertEquals($time, $result);
    }

    public function testStatus(): void
    {
        $adapter = $this->getAdapter();

        $queue = $this->getDefaultQueue($adapter);

        $message = new Message('ext-simple', null);
        $message = $queue->push(
            $message,
        );

        $status = $adapter->status($message->getId());
        $this->assertEquals(JobStatus::waiting(), $status);

        $queue->run();

        $status = $adapter->status($message->getId());
        $this->assertEquals(JobStatus::done(), $status);

        $mockReserved = $this->createMock(QueueProviderInterface::class);
        $mockReserved->method('existInReserved')->willReturn(true);
        $adapter = new Adapter($mockReserved, new JsonMessageSerializer(), $this->getLoop());
        $queue = $this->getDefaultQueue($adapter);

        $status = $adapter->status('1');
        $this->assertEquals(JobStatus::reserved(), $status);
    }

    public function testListen(): void
    {
        $time = time();
        $mockLoop = $this->createMock(LoopInterface::class);
        $mockLoop->expects($this->exactly(3))->method('canContinue')->willReturn(true, true, false);

        $queueProvider = new QueueProvider(
            $this->createConnection()
        );
        $adapter = new Adapter(
            $queueProvider
                ->withChannelName('yii-queue'),
            new JsonMessageSerializer(),
            $mockLoop,
        );
        $queue = $this->getDefaultQueue($adapter);

        $queue->push(
            new Message('ext-simple', ['file_name' => 'test-listen' . $time, 'payload' => ['time' => $time]])
        );
        $queue->push(
            new Message('ext-simple', ['file_name' => 'test-listen' . $time, 'payload' => ['time' => $time]])
        );
        $queue->listen();
    }

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

    private function getDefaultQueue(AdapterInterface $adapter): Queue
    {
        return $this
            ->getQueue()
            ->withAdapter($adapter);
    }

    public function testAdapterStatusException()
    {
        $adapter = $this->getAdapter();
        $this->expectException(\InvalidArgumentException::class);
        $adapter->status(-1);
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
