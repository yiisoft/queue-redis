<?php
declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Tests;

use PHPUnit\Framework\TestCase;
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
     * @param QueueProvider $provider
     * @return void
     * @depends test__construct
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testImmutable(QueueProvider $provider): void
    {
        self::assertNotSame($provider, $provider->withChannelName('new'));
    }
}
