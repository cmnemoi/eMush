<?php

namespace Mush\Tests\RoomLog\Listener;

use Mush\RoomLog\Listener\WeaponFiredEventSubscriber;
use Mush\RoomLog\Service\RoomLogService;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class WeaponFiredEventSubscriberTest extends TestCase
{
    private WeaponFiredEventSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->subscriber = new WeaponFiredEventSubscriber(
            new RoomLogService()
        );
    }
}
