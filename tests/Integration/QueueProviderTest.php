<?php

declare(strict_types=1);

namespace Yiisoft\Queue\Redis\Tests\Integration;

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
}
