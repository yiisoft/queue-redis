<?php
declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Queue\Redis\Exception\NotConnectedRedisException;
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

    public function test__construct()
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
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testImmutable(QueueProvider $provider): void
    {
        self::assertNotSame($provider, $provider->withChannelName('new'));
    }

    public function testNotConnected(): void
    {
        $redis = new \Redis();
        try {
            $provider = new QueueProvider($redis, 'test');
            $provider->getId();
        } catch (NotConnectedRedisException $e) {
            $this->assertEquals('Not connected to Redis', $e->getName());
            $this->assertNotNull($e->getSolution());
        }
        $this->expectException(NotConnectedRedisException::class);
        $provider->reserve();
    }

    public function testRedisException(): void
    {
        $mock = $this->createMock(\Redis::class);
        $mock->method('brPop')->willReturn([1 => 1], [1 => '1']);
        $mock->method('isConnected')->willReturn(true);
        $mock->method('hget')->willReturn(null);
        $mock->method('incr')->willReturn(false);
        $mock->method('zrevrangebyscore')->willReturn(['1', '2']);
        $mock->method('zremrangebyscore')->willReturn(0);
        $mock->method('set')->willReturn(true);
        $mock->expects($this->exactly(8))->method('rpush')->willReturn(1);
        $provider = new QueueProvider($mock);
        $this->assertNull($provider->reserve());
        $this->assertNull($provider->reserve());

        $this->expectException(\RuntimeException::class);
        $provider->getId();

    }
}
