<?php

declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Yiisoft\Queue\Redis\Message\Message;
use Yiisoft\Queue\Redis\QueueProvider;
use Yiisoft\Queue\Redis\QueueProviderInterface;

class QueueProviderTest extends TestCase
{
    /**
     * @depends test__construct
     */
    public function testGetId(QueueProvider $provider)
    {
        $id = $provider->getId();
        $this->assertGreaterThan(0, $id);
    }

    public function test__construct(): QueueProvider
    {
        $redis = new \Redis();
        $connected = $redis->connect('redis');
        $this->assertTrue($connected);
        $provider = new QueueProvider($redis, 'test');
        $this->assertInstanceOf(QueueProviderInterface::class, $provider);
        return $provider;
    }

    /**
     * @depends test__construct
     */
    public function testDelay(QueueProvider $provider): void
    {
        $message = new Message('test', ['key' => 'value'], [], 2);
        $id = $provider->pushMessage(json_encode($message->getData(), JSON_THROW_ON_ERROR), $message->getMetadata());
        $this->assertGreaterThan(0, $id);
        $reserv = $provider->reserve($id);
        $this->assertNull($reserv);
        sleep(3);
        $reserv = $provider->reserve($id);
        $this->assertNotNull($reserv);
    }
}
